<?php

namespace crocodicstudio\crudbooster\contracts\Generator;

/**
 * @deprecated 7.2 Use `php artisan crudbooster:make-resource Model --generate`. See docs/en/deprecations.md.
 */
interface ModuleGeneratorInterface
{
    /**
     * Writes app/Http/Controllers/Admin{Name}Controller.php for `$table`, with columns and form fields
     * guessed from its schema, and returns the class name. A numbered name is used when the file exists.
     */
    public function generate(string $table, ?string $name = null): string;

    /**
     * The column a module shows as the row title: the first one matching NAME_FIELDS_CANDIDATE, else "id".
     */
    public function titleColumn(array $columns): string;
}
