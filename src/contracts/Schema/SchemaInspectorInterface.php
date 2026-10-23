<?php

namespace crocodicstudio\crudbooster\contracts\Schema;

interface SchemaInspectorInterface
{
    public function columns(string $table): array;

    public function primaryKey(string $table): ?string;

    public function hasColumn(string $table, string $column): bool;

    public function hasTable(string $table): bool;
}
