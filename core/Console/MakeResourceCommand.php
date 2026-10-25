<?php

namespace CrudBooster\Console;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class MakeResourceCommand extends Command
{
    protected $signature = 'crudbooster:make-resource
        {name : The model name, e.g. Product}
        {--model= : The model class (default App\\Models\\{name})}
        {--generate : Write the columns and fields from the model\'s table}
        {--force : Overwrite an existing resource}';

    protected $description = 'Create a CRUDBooster resource: list, form and detail pages for a model';

    private const SKIPPED_IN_FORM = ['id', 'created_at', 'updated_at', 'deleted_at', 'remember_token', 'email_verified_at'];

    public function handle(Filesystem $files): int
    {
        $name = Str::studly(Str::beforeLast(class_basename($this->argument('name')), 'Resource') ?: $this->argument('name'));
        $model = ltrim($this->option('model') ?: 'App\\Models\\'.$name, '\\');
        $class = $name.'Resource';
        $path = app_path('Admin/Resources/'.$class.'.php');

        if ($files->exists($path) && ! $this->option('force')) {
            $this->components->error("$path already exists. Pass --force to overwrite it.");

            return self::FAILURE;
        }

        [$columns, $fields, $titleAttribute] = $this->option('generate')
            ? $this->fromSchema($model)
            : [["Columns\\TextColumn::make('id')->sortable()"], ["// Fields\\TextInput::make('name')->required()->maxLength(255),"], null];

        $stub = $files->get(__DIR__.'/stubs/resource.stub');
        $source = strtr($stub, [
            '{{ namespace }}' => 'App\\Admin\\Resources',
            '{{ modelClass }}' => $model,
            '{{ model }}' => class_basename($model),
            '{{ class }}' => $class,
            '{{ titleAttribute }}' => $titleAttribute ? "'$titleAttribute'" : 'null',
            '{{ columns }}' => $this->indent($columns, 16),
            '{{ fields }}' => $this->indent($fields, 12),
        ]);

        $files->ensureDirectoryExists(dirname($path));
        $files->put($path, $source);

        $this->components->info("Resource [$path] created.");
        $this->line('  It is discovered from app/Admin/Resources; open '.url(config('crudbooster.ADMIN_PATH').'/'.Str::kebab(Str::pluralStudly($name))));

        return self::SUCCESS;
    }

    /**
     * @return array{0: string[], 1: string[], 2: ?string}
     */
    private function fromSchema(string $model): array
    {
        if (! is_subclass_of($model, Model::class)) {
            $this->components->warn("[$model] is not an Eloquent model; writing an empty resource.");

            return [[], [], null];
        }

        $instance = new $model;
        $schemaColumns = Schema::connection($instance->getConnectionName())->getColumns($instance->getTable());
        $titleAttribute = collect($schemaColumns)->pluck('name')->first(fn ($column) => in_array($column, ['name', 'title', 'label', 'email', 'code'], true));

        $columns = [];
        $fields = [];
        foreach ($schemaColumns as $column) {
            $name = $column['name'];
            $type = strtolower($column['type_name']);

            if (count($columns) < 6 && ! in_array($type, ['text', 'mediumtext', 'longtext', 'json', 'blob'], true) && ! Str::contains($name, ['password', 'token'])) {
                $columns[] = $this->column($name, $type);
            }
            if (! in_array($name, self::SKIPPED_IN_FORM, true) && $name !== $instance->getKeyName()) {
                $fields[] = $this->field($instance, $name, $type, ! $column['nullable'] && $column['default'] === null && ! $column['auto_increment']);
            }
        }

        return [$columns, $fields, $titleAttribute];
    }

    private function column(string $name, string $type): string
    {
        $code = match (true) {
            $this->isBoolean($name, $type) => "Columns\\BooleanColumn::make('$name')",
            Str::contains($name, ['photo', 'image', 'avatar', 'thumbnail', 'logo']) => "Columns\\ImageColumn::make('$name')",
            in_array($type, ['date'], true) => "Columns\\TextColumn::make('$name')->date()",
            in_array($type, ['datetime', 'timestamp'], true) => "Columns\\TextColumn::make('$name')->dateTime()",
            in_array($type, ['decimal', 'float', 'double', 'numeric', 'real'], true) => "Columns\\TextColumn::make('$name')->numeric(2)",
            default => "Columns\\TextColumn::make('$name')",
        };

        if (in_array($type, ['varchar', 'char', 'string', 'text'], true) && ! Str::contains($name, ['photo', 'image'])) {
            $code .= '->searchable()';
        }

        return $code.'->sortable()';
    }

    private function field(Model $model, string $name, string $type, bool $required): string
    {
        $relationship = Str::endsWith($name, '_id') ? Str::camel(Str::beforeLast($name, '_id')) : null;

        $code = match (true) {
            $relationship && method_exists($model, $relationship) => "Fields\\Select::make('$name')->relationship('$relationship', 'name')",
            Str::contains($name, 'password') => "Fields\\TextInput::make('$name')->password()",
            $name === 'email' || Str::endsWith($name, '_email') => "Fields\\TextInput::make('$name')->email()",
            Str::contains($name, ['photo', 'image', 'avatar', 'thumbnail', 'logo']) => "Fields\\FileUpload::make('$name')->image()",
            $this->isBoolean($name, $type) => "Fields\\Toggle::make('$name')",
            in_array($type, ['text', 'mediumtext', 'longtext'], true) => "Fields\\Textarea::make('$name')",
            $type === 'date' => "Fields\\DatePicker::make('$name')",
            in_array($type, ['datetime', 'timestamp'], true) => "Fields\\DatePicker::make('$name')->withTime()",
            $type === 'time' => "Fields\\DatePicker::make('$name')->timeOnly()",
            in_array($type, ['int', 'integer', 'bigint', 'smallint', 'mediumint', 'tinyint', 'decimal', 'float', 'double', 'numeric', 'real'], true) => "Fields\\TextInput::make('$name')->numeric()",
            default => "Fields\\TextInput::make('$name')",
        };

        if ($required && ! Str::contains($code, ['Toggle'])) {
            $code .= '->required()';
        }

        return $code.',';
    }

    private function isBoolean(string $name, string $type): bool
    {
        return in_array($type, ['bool', 'boolean'], true) || ($type === 'tinyint' && Str::startsWith($name, ['is_', 'has_']));
    }

    private function indent(array $lines, int $spaces): string
    {
        $pad = str_repeat(' ', $spaces);

        return implode("\n", array_map(fn ($line) => $pad.(Str::endsWith($line, [',', '*/']) || Str::startsWith($line, '//') ? $line : $line.','), $lines));
    }
}
