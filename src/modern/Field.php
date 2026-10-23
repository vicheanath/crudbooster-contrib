<?php

namespace crocodicstudio\crudbooster\modern;

use Illuminate\Support\Str;

/**
 * One form field for CrudDefinition: Field::{type}($name), e.g. Field::text('name') or
 * Field::select('category_id')->datatable('categories,name'). Any 5.x form option can be set
 * fluently; method names are converted to snake case (relationshipTable -> relationship_table).
 *
 * @method static static text(string $name, ?string $label = null)
 * @method static static textarea(string $name, ?string $label = null)
 * @method static static number(string $name, ?string $label = null)
 * @method static static email(string $name, ?string $label = null)
 * @method static static password(string $name, ?string $label = null)
 * @method static static money(string $name, ?string $label = null)
 * @method static static date(string $name, ?string $label = null)
 * @method static static datetime(string $name, ?string $label = null)
 * @method static static select(string $name, ?string $label = null)
 * @method static static select2(string $name, ?string $label = null)
 * @method static static radio(string $name, ?string $label = null)
 * @method static static checkbox(string $name, ?string $label = null)
 * @method static static upload(string $name, ?string $label = null)
 * @method static static hidden(string $name, ?string $label = null)
 * @method static static wysiwyg(string $name, ?string $label = null)
 * @method $this datatable(string $tableAndColumn)
 * @method $this dataenum(string|array $values)
 * @method $this relationshipTable(string $table)
 * @method $this width(string $class)
 * @method $this help(string $text)
 * @method $this placeholder(string $text)
 * @method $this readonly(bool $readonly = true)
 * @method $this disabled(bool $disabled = true)
 */
class Field
{
    protected array $attributes;

    public function __construct(string $type, string $name, ?string $label = null)
    {
        $this->attributes = [
            'label' => $label ?? Str::headline($name),
            'name' => $name,
            'type' => $type,
        ];
    }

    public static function make(string $type, string $name, ?string $label = null): static
    {
        return new static($type, $name, $label);
    }

    public static function __callStatic(string $type, array $arguments): static
    {
        return new static($type, ...$arguments);
    }

    public function label(string $label): static
    {
        return $this->set('label', $label);
    }

    /**
     * Laravel validation rules, e.g. 'required|max:255'.
     */
    public function rules(string $rules): static
    {
        return $this->set('validation', $rules);
    }

    public function required(bool $required = true): static
    {
        return $this->set('required', $required);
    }

    public function set(string $key, mixed $value): static
    {
        $this->attributes[$key] = $value;

        return $this;
    }

    public function __call(string $method, array $arguments): static
    {
        return $this->set(Str::snake($method), $arguments ? $arguments[0] : true);
    }

    public function toArray(): array
    {
        return $this->attributes;
    }
}
