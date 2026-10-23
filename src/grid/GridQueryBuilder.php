<?php

namespace crocodicstudio\crudbooster\grid;

use crocodicstudio\crudbooster\contracts\Grid\GridFilterParserInterface;
use crocodicstudio\crudbooster\contracts\Grid\GridQueryBuilderInterface;
use crocodicstudio\crudbooster\contracts\Schema\SchemaInspectorInterface;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

final class GridQueryBuilder implements GridQueryBuilderInterface
{
    public function __construct(
        private readonly SchemaInspectorInterface $schema,
        private readonly GridFilterParserInterface $filters,
    ) {
    }

    public function buildQuery(string $table, string $primaryKey, array $columns, array $options = []): GridQuery
    {
        $query = DB::table($table)->select(DB::raw($table.'.'.$primaryKey));

        if (isset($options['scope'])) {
            // By reference, like hook_query_index(&$query) in 5.x.
            $scope = $options['scope'];
            $scope($query);
        }

        if (in_array('deleted_at', $this->schema->columns($table))) {
            $query->where($table.'.deleted_at', null);
        }

        $columns = $this->applyColumns($query, $table, $columns);

        if (! empty($options['search'])) {
            $this->applySearch($query, $columns, (string) $options['search']);
        }

        foreach ((array) ($options['where'] ?? []) as $column => $value) {
            $query->where($table.'.'.$column, $value);
        }

        $ordered = false;
        if (! empty($options['filters'])) {
            $ordered = $this->applyFilters($query, (array) $options['filters']);
        }

        if (! $ordered) {
            $this->applyOrdering($query, $table, $primaryKey, $options['orderby'] ?? null);
        }

        return new GridQuery($query, $columns);
    }

    public function applyFilters(Builder $query, array $filterParams): bool
    {
        return $this->filters->apply($query, $filterParams);
    }

    /**
     * Adds each column's select and joins, and resolves the keys the views read.
     */
    private function applyColumns(Builder $query, string $mainTable, array $columns): array
    {
        $joinAliasCount = 0;
        $joinedTables = [];
        $table = $mainTable;

        foreach ($columns as $index => $column) {
            $join = $column['join'] ?? null;
            $joinWhere = $column['join_where'] ?? null;
            $joinId = $column['join_id'] ?? null;
            $field = $column['name'] ?? null;
            $joinedTables[] = $table;

            if (! $field) {
                continue;
            }

            // A raw subquery column, e.g. "(select count(*) from ...) as total".
            if (strpos($field, ' as ') !== false) {
                $field = substr($field, strpos($field, ' as ') + 4);
                $query->addSelect(DB::raw($column['name']));
                $columns[$index]['type_data'] = 'varchar';
                $columns[$index]['field'] = $field;
                $columns[$index]['field_raw'] = $field;
                $columns[$index]['field_with'] = array_key_exists('join', $column) ? str_replace(',', '.', $column['join']) : $field;
                $columns[$index]['is_subquery'] = true;
                continue;
            }

            $query->addSelect(strpos($field, '.') !== false ? $field : $table.'.'.$field);

            $fieldParts = explode('.', $field);
            if (isset($fieldParts[1])) {
                $field = $fieldParts[1];
                $table = $fieldParts[0];
            } else {
                $table = $mainTable;
            }

            if (! $join) {
                if (isset($fieldParts[1])) {
                    $query->addSelect($table.'.'.$field.' as '.$table.'_'.$field);
                    $columns[$index]['field'] = $table.'_'.$field;
                    $columns[$index]['field_raw'] = $table.'.'.$field;
                } else {
                    $query->addSelect($table.'.'.$field);
                    $columns[$index]['field'] = $field;
                    $columns[$index]['field_raw'] = $field;
                }
                $columns[$index]['type_data'] = $this->schema->columnType($table, $field);
                $columns[$index]['field_with'] = $table.'.'.$field;
                continue;
            }

            // "join" => "table,column[,table2,column2]"
            $joinParts = explode(',', $join);
            $joinTable = $joinParts[0];
            $joinColumn = $joinParts[1] ?? null;
            $joinAlias = str_replace('.', '_', $joinTable);

            if (in_array($joinTable, $joinedTables)) {
                $joinAliasCount++;
                $joinAlias = $joinTable.$joinAliasCount;
            }
            $joinedTables[] = $joinTable;

            $joinKey = $joinAlias.'.'.($joinId ?: $this->schema->primaryKey($joinTable));
            $query->leftJoin($joinTable.' as '.$joinAlias, $joinKey, '=', DB::raw($table.'.'.$field.($joinWhere ? ' AND '.$joinWhere.' ' : '')));
            $query->addSelect($joinAlias.'.'.$joinColumn.' as '.$joinAlias.'_'.$joinColumn);

            foreach ($this->schema->columns($joinTable) as $joinTableColumn) {
                $query->addSelect($joinAlias.'.'.$joinTableColumn.' as '.$joinAlias.'_'.$joinTableColumn);
            }

            $columns[$index]['type_data'] = $this->schema->columnType($joinTable, (string) $joinColumn);
            $columns[$index]['field'] = $joinAlias.'_'.$joinColumn;
            $columns[$index]['field_with'] = $joinAlias.'.'.$joinColumn;
            $columns[$index]['field_raw'] = $joinColumn;

            $joinTable2 = $joinParts[2] ?? null;
            $joinColumn2 = $joinParts[3] ?? null;

            if ($joinTable2 && $joinColumn2) {
                $joinAlias2 = $joinTable2;
                if (in_array($joinTable2, $joinedTables)) {
                    $joinAliasCount++;
                    $joinAlias2 = $joinTable2.$joinAliasCount;
                }
                $joinedTables[] = $joinTable2;

                $query->leftJoin($joinTable2.' as '.$joinAlias2, $joinAlias2.'.'.$this->schema->primaryKey($joinTable2), '=', $joinAlias.'.'.$joinColumn);
                $query->addSelect($joinAlias2.'.'.$joinColumn2.' as '.$joinColumn2.'_'.$joinAlias2);

                $columns[$index]['type_data'] = $this->schema->columnType($joinTable2, $joinColumn2);
                $columns[$index]['field'] = $joinColumn2.'_'.$joinAlias2;
                $columns[$index]['field_with'] = $joinAlias2.'.'.$joinColumn2;
                $columns[$index]['field_raw'] = $joinColumn2;
            }
        }

        return $columns;
    }

    private function applySearch(Builder $query, array $columns, string $term): void
    {
        $query->where(function (Builder $where) use ($columns, $term) {
            foreach ($columns as $column) {
                if (empty($column['field_with']) || ! empty($column['is_subquery'])) {
                    continue;
                }
                $where->orWhere($column['field_with'], 'like', '%'.$term.'%');
            }
        });
    }

    /**
     * Applies the module's $orderby (an array of column => direction, or "column,direction;..."),
     * falling back to the newest rows first.
     */
    private function applyOrdering(Builder $query, string $table, string $primaryKey, $orderby): void
    {
        if (! $orderby) {
            $query->orderBy($table.'.'.$primaryKey, 'desc');

            return;
        }

        if (! is_array($orderby)) {
            $pairs = [];
            foreach (explode(';', $orderby) as $item) {
                $parts = explode(',', $item);
                $pairs[] = [trim($parts[0]), trim($parts[1] ?? '') ?: 'asc'];
            }
        } else {
            $pairs = array_map(null, array_keys($orderby), array_values($orderby));
        }

        foreach ($pairs as [$column, $direction]) {
            $query->orderBy(strpos($column, '.') !== false ? $column : $table.'.'.$column, $direction);
        }
    }
}
