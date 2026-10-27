<?php

namespace crocodicstudio\crudbooster\generator;

use crocodicstudio\crudbooster\contracts\Generator\ModuleGeneratorInterface;
use crocodicstudio\crudbooster\contracts\Schema\SchemaInspectorInterface;

/**
 * Writes the classic module controller from stubs/module_controller.stub. Columns and form fields are
 * guessed from the table's schema and the *_FIELDS_CANDIDATE config lists.
 *
 * @deprecated 7.2 Use `php artisan crudbooster:make-resource Model --generate`. See docs/en/deprecations.md.
 */
final class ModuleGenerator implements ModuleGeneratorInterface
{
    private const SKIPPED_COLUMNS = ['id', 'created_at', 'updated_at', 'deleted_at'];

    private const GRID_COLUMN_LIMIT = 8;

    public function __construct(private readonly SchemaInspectorInterface $schema)
    {
    }

    public function generate(string $table, ?string $name = null): string
    {
        $path = base_path('app/Http/Controllers/');
        $controller = $this->controllerName($path, $name ?: $table);
        $columns = $this->columns($table);

        $source = strtr(file_get_contents(__DIR__.'/stubs/module_controller.stub'), [
            '{{class}}' => $controller,
            '{{table}}' => $table,
            '{{title_field}}' => $this->titleColumn($columns),
            '{{primary_key}}' => (string) $this->schema->primaryKey($table),
            '{{columns}}' => $this->gridColumns($columns),
            '{{form}}' => $this->formFields($table, $columns),
        ]);

        file_put_contents($path.$controller.'.php', trim($source));

        return $controller;
    }

    public function titleColumn(array $columns): string
    {
        $candidates = $this->candidates('NAME_FIELDS_CANDIDATE');

        foreach ($columns as $column) {
            foreach ($candidates as $candidate) {
                if (strpos($column, $candidate) !== false) {
                    return $column;
                }
            }
        }

        return 'id';
    }

    /**
     * "Admin{Studly}Controller", or "Admin{Studly}{n}Controller" with the first free n when that file exists.
     */
    private function controllerName(string $path, string $name): string
    {
        $studly = str_replace(' ', '', ucwords(str_replace(['_', '-'], ' ', $name)));
        $controller = 'Admin'.$studly.'Controller';

        for ($suffix = 1; file_exists($path.$controller.'.php'); $suffix++) {
            $controller = 'Admin'.$studly.$suffix.'Controller';
        }

        return $controller;
    }

    private function gridColumns(array $columns): string
    {
        $passwordCandidates = $this->candidates('PASSWORD_FIELDS_CANDIDATE');
        $imageCandidates = $this->candidates('IMAGE_FIELDS_CANDIDATE');
        $php = '';

        foreach (array_slice($columns, 0, self::GRID_COLUMN_LIMIT) as $field) {
            if (in_array($field, self::SKIPPED_COLUMNS) || in_array($field, $passwordCandidates)) {
                continue;
            }

            $label = str_replace('Cms ', '', $this->label($field));
            $joinTable = $this->joinTable($field);

            if ($joinTable !== null) {
                $join = $joinTable.','.$this->titleColumn($this->columns($joinTable));
                $php .= "\t\t".'$this->col[] = array("label"=>"'.$label.'","name"=>"'.$field.'","join"=>"'.$join.'");'."\n";
                continue;
            }

            $image = in_array($field, $imageCandidates) ? ',"image"=>true' : '';
            $php .= "\t\t".'$this->col[] = array("label"=>"'.$label.'","name"=>"'.$field.'" '.$image.');'."\n";
        }

        return $php;
    }

    private function formFields(string $table, array $columns): string
    {
        $php = '';

        foreach ($columns as $field) {
            if (in_array($field, self::SKIPPED_COLUMNS)) {
                continue;
            }

            [$type, $validation, $attributes] = $this->formField($table, $field);

            $php .= "\t\t".'$this->form[] = ["label"=>"'.$this->label($field).'","name"=>"'.$field.'","type"=>"'.$type.'","required"=>TRUE';
            if ($validation) {
                $php .= ',"validation"=>"'.$validation.'"';
            }
            foreach ($attributes as $key => $value) {
                $php .= ',"'.$key.'"=>'.$this->exportValue($value);
            }
            $php .= "];\n";
        }

        return $php;
    }

    /**
     * @return array{0: string, 1: string, 2: array} The field type, its validation rules and extra attributes.
     */
    private function formField(string $table, string $field): array
    {
        $attributes = [];
        [$type, $rule] = $this->typeForColumn($this->schema->columnType($table, $field));
        $validation = ['required', $rule];

        if (substr($field, 0, 3) == 'id_') {
            $joinTable = str_replace('id_', '', $field);
            $attributes['datatable'] = $joinTable.','.$this->titleColumn($this->columns($joinTable));
            $type = 'select2';
        }

        if (substr($field, -3) == '_id') {
            $joinTable = str_replace('_id', '', $field);
            $attributes['datatable'] = $joinTable.','.$this->titleColumn($this->columns($joinTable));
            $type = 'select2';
        }

        if (substr($field, 0, 3) == 'is_') {
            $type = 'radio';
            $option = ucwords(substr($field, 3));
            $validation = ['required|integer'];
            $attributes['dataenum'] = ['1|'.$option, '0|Un-'.$option];
        }

        if (in_array($field, $this->candidates('PASSWORD_FIELDS_CANDIDATE'))) {
            $type = 'password';
            $validation = ['min:3', 'max:32'];
            $attributes['help'] = cbLang('text_default_help_password');
        }

        if (in_array($field, $this->candidates('IMAGE_FIELDS_CANDIDATE'))) {
            $type = 'upload';
            $attributes['help'] = cbLang('text_default_help_upload');
            $validation = ['required|image|max:3000'];
        }

        if ($field == 'latitude' || $field == 'longitude') {
            $type = 'hidden';
        }

        if (in_array($field, $this->candidates('PHONE_FIELDS_CANDIDATE'))) {
            $type = 'number';
            $validation = ['required', 'numeric'];
            $attributes['placeholder'] = cbLang('text_default_help_number');
        }

        if (in_array($field, $this->candidates('EMAIL_FIELDS_CANDIDATE'))) {
            $type = 'email';
            $validation[] = 'email|unique:'.$table;
            $attributes['placeholder'] = cbLang('text_default_help_email');
        }

        if ($type == 'text' && in_array($field, $this->candidates('NAME_FIELDS_CANDIDATE'))) {
            $attributes['placeholder'] = cbLang('text_default_help_text');
            $validation = ['required', 'string', 'min:3', 'max:70'];
        }

        if ($type == 'text' && in_array($field, $this->candidates('URL_FIELDS_CANDIDATE'))) {
            $validation = ['required', 'url'];
            $attributes['placeholder'] = cbLang('text_default_help_url');
        }

        return [$type, implode('|', $validation), $attributes];
    }

    /**
     * @return array{0: string, 1: string} The form type and validation rule for a database column type.
     */
    private function typeForColumn(string $columnType): array
    {
        return match ($columnType) {
            'text', 'longtext' => ['textarea', 'string|min:5|max:5000'],
            'date' => ['date', 'date'],
            'datetime', 'timestamp' => ['datetime', 'date_format:Y-m-d H:i:s'],
            'time' => ['time', 'date_format:H:i:s'],
            'double' => ['money', 'integer|min:0'],
            'int', 'integer' => ['number', 'integer|min:0'],
            default => ['text', 'min:1|max:255'],
        };
    }

    /**
     * The table an `id_{table}` or `{table}_id` column points to, or null for any other column.
     */
    private function joinTable(string $field): ?string
    {
        if (substr($field, 0, 3) == 'id_') {
            return str_replace('id_', '', $field);
        }

        if (substr($field, -3) == '_id') {
            return substr($field, 0, -3);
        }

        return null;
    }

    private function label(string $field): string
    {
        return ucwords(str_replace('_', ' ', str_replace('id_', '', $field)));
    }

    private function exportValue(mixed $value): string
    {
        if (is_bool($value)) {
            return $value ? 'TRUE' : 'FALSE';
        }

        // Option lists use the ";" separated string the form components accept.
        return '"'.(is_array($value) ? implode(';', $value) : $value).'"';
    }

    /**
     * Columns as CRUDBooster::getTableColumns() reads them: unqualified names refer to MAIN_DB_DATABASE.
     */
    private function columns(string $table): array
    {
        $database = config('crudbooster.MAIN_DB_DATABASE');

        return $this->schema->columns(strpos($table, '.') === false && $database ? $database.'.'.$table : $table);
    }

    private function candidates(string $key): array
    {
        return explode(',', (string) config('crudbooster.'.$key));
    }
}
