<?php

namespace crocodicstudio\crudbooster\schema;

use crocodicstudio\crudbooster\contracts\Schema\SchemaInspectorInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class SchemaInspector implements SchemaInspectorInterface
{
    public function columns(string $table): array
    {
        $parsed = $this->parseTable($table);

        if (! in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            return Schema::getColumnListing($parsed['table']);
        }

        return collect(DB::select(
            // Without ORDER BY, MySQL returns information_schema rows in no particular order.
            'SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = :database AND TABLE_NAME = :table ORDER BY ORDINAL_POSITION',
            ['database' => $parsed['database'], 'table' => $parsed['table']]
        ))->map(static fn (object $column): string => $column->COLUMN_NAME)->values()->all();
    }

    public function primaryKey(string $table): ?string
    {
        if ($table === '') {
            return 'id';
        }

        $parsed = $this->parseTable($table);
        $primary = collect(Schema::getIndexes($parsed['table']))
            ->first(static fn (array $index): bool => (bool) ($index['primary'] ?? false));

        return $primary['columns'][0] ?? null;
    }

    public function hasColumn(string $table, string $column): bool
    {
        return Schema::hasColumn($this->parseTable($table)['table'], $column);
    }

    public function hasTable(string $table): bool
    {
        return Schema::hasTable($this->parseTable($table)['table']);
    }

    public function columnType(string $table, string $column): string
    {
        // Same cache key as CRUDBooster 5.x, so CRUDBooster::clearCache() keeps working.
        return Cache::rememberForever('field_type_'.$table.'_'.$column, function () use ($table, $column) {
            return ($this->column($table, $column)['type_name'] ?? null) ?: 'varchar';
        });
    }

    public function isNullable(string $table, string $column): bool
    {
        return Cache::rememberForever('field_isNull_'.$table.'_'.$column, function () use ($table, $column) {
            return (bool) ($this->column($table, $column)['nullable'] ?? false);
        });
    }

    private function column(string $table, string $column): ?array
    {
        try {
            return collect(Schema::getColumns($table))->firstWhere('name', $column);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * @return array{database: string, table: string}
     */
    private function parseTable(string $table): array
    {
        $parts = explode('.', trim($table));
        $tableName = array_pop($parts);

        return [
            'database' => ($parts[0] ?? '') ?: DB::getDatabaseName(),
            'table' => $tableName,
        ];
    }
}
