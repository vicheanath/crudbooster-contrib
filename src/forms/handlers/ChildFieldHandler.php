<?php

namespace crocodicstudio\crudbooster\forms\handlers;

use crocodicstudio\crudbooster\forms\FormContext;
use Illuminate\Support\Facades\DB;

/**
 * A one-to-many child table edited inside the form. Rows arrive as "{slug of label}-{column}[]"
 * arrays and are saved into 'table' with 'foreign_key' pointing to the parent row. Editing
 * replaces all of the parent's child rows.
 *
 * @deprecated 7.2 Manage child rows as their own resource, filtered by the parent
 *     (SelectFilter::relationship()). See docs/en/deprecations.md.
 */
class ChildFieldHandler extends BaseFieldHandler
{
    protected array $types = ['child'];

    public function beforeSave(array $field, mixed $value, array &$payload, FormContext $context): void
    {
        // Child rows need the parent's id, so they are saved in afterSave().
    }

    public function afterSave(array $field, mixed $value, FormContext $context): void
    {
        $childTable = $this->tableName($field['table']);
        $foreignKey = $field['foreign_key'];
        $rows = $this->submittedRows($field);

        if (! $context->isEdit()) {
            $parentId = ! empty($context->recordId) ? $context->recordId : $context->insertId;
            foreach ($rows as $i => $row) {
                $rows[$i][$foreignKey] = $parentId;
            }
            DB::table($childTable)->insert($rows);

            return;
        }

        DB::table($childTable)->where($foreignKey, $context->recordId)->delete();

        // Ids are assigned explicitly, as in 5.x.
        $childKey = $this->schema->primaryKey($childTable);
        $nextId = DB::table($childTable)->max($childKey) + 1;
        foreach ($rows as $i => $row) {
            $rows[$i][$childKey] = $nextId++;
            $rows[$i][$foreignKey] = $context->recordId;
        }
        DB::table($childTable)->insert(array_reverse($rows));
    }

    /**
     * The submitted rows, without rows that have no values at all.
     */
    protected function submittedRows(array $field): array
    {
        $prefix = str_slug($field['label'], '');
        $columns = $field['columns'] ?? [];
        $firstColumn = $columns ? request($prefix.'-'.$columns[0]['name']) : null;
        $count = is_array($firstColumn) ? count($firstColumn) : 1;

        $rows = [];
        for ($i = 0; $i < $count; $i++) {
            $row = [];
            foreach ($columns as $column) {
                $values = request($prefix.'-'.$column['name']);
                // Empty inputs arrive as null; the column stays in the row so every row inserts the same columns.
                if (is_array($values) && array_key_exists($i, $values)) {
                    $row[$column['name']] = $values[$i];
                }
            }
            if (array_filter($row, static fn ($value) => $value !== null && $value !== '')) {
                $rows[] = $row;
            }
        }

        return $rows;
    }
}
