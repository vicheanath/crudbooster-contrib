<?php

namespace CrudBooster\Tables\Filters;

use Closure;
use CrudBooster\Concerns\EvaluatesClosures;
use CrudBooster\Concerns\HasLabel;
use CrudBooster\Concerns\HasName;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * A control above the table that narrows the query. Its value comes from ?filters[name]=...
 */
abstract class BaseFilter
{
    use EvaluatesClosures;
    use HasLabel;
    use HasName;

    protected string $view;

    protected ?Closure $queryUsing = null;

    protected ?string $attribute = null;

    /**
     * Replaces the default constraint: fn (Builder $query, $value) => $query->where(...)
     */
    public function query(Closure $callback): static
    {
        $this->queryUsing = $callback;

        return $this;
    }

    /**
     * The column to filter on, when it differs from the filter's name.
     */
    public function attribute(string $attribute): static
    {
        $this->attribute = $attribute;

        return $this;
    }

    public function getAttribute(): string
    {
        return $this->attribute ?? $this->name;
    }

    public function isActive(mixed $value): bool
    {
        return $value !== null && $value !== '' && $value !== [];
    }

    public function apply(Builder $query, mixed $value): void
    {
        if (! $this->isActive($value)) {
            return;
        }

        if ($this->queryUsing) {
            $this->evaluate($this->queryUsing, ['query' => $query, 'value' => $value]);

            return;
        }

        $this->applyDefault($query, $value);
    }

    abstract protected function applyDefault(Builder $query, mixed $value): void;

    public function render(Model $model, mixed $value): string
    {
        return view($this->view, ['filter' => $this, 'model' => $model, 'value' => $value])->render();
    }
}
