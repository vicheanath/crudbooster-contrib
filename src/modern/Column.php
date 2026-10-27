<?php

namespace crocodicstudio\crudbooster\modern;

use Illuminate\Support\Str;

/**
 * One index column for CrudDefinition. Any 5.x column option can be set fluently:
 * Column::make('Category', 'category_id')->join('categories,name')->strLimit(30).
 *
 * @method $this join(string $tableAndColumn)
 * @method $this joinWhere(string $condition)
 * @method $this joinId(string $column)
 * @method $this callback(callable $callback)
 * @method $this callbackPhp(string $code)
 * @method $this strLimit(int $length)
 * @method $this nl2br(bool $enabled = true)
 * @method $this download(bool $enabled = true)
 * @method $this image(bool $enabled = true)
 * @method $this visible(bool $visible = true)
 *
 * @deprecated 7.2 Use CrudBooster\Tables\Columns\TextColumn, BadgeColumn, BooleanColumn or ImageColumn. See
 *     docs/en/deprecations.md.
 */
class Column
{
    protected array $attributes;

    public function __construct(string $label, string $name)
    {
        $this->attributes = ['label' => $label, 'name' => $name];
    }

    public static function make(string $label, string $name): static
    {
        return new static($label, $name);
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
