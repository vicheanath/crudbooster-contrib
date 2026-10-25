<?php

namespace CrudBooster\Tables\Filters;

use Illuminate\Database\Eloquent\Builder;

/**
 * Yes / no / all, for a boolean column.
 */
class TernaryFilter extends BaseFilter
{
    protected string $view = 'cb::filters.ternary';

    protected ?string $trueLabel = null;

    protected ?string $falseLabel = null;

    public function labels(string $true, string $false): static
    {
        $this->trueLabel = $true;
        $this->falseLabel = $false;

        return $this;
    }

    public function getTrueLabel(): string
    {
        return $this->trueLabel ?? __('Yes');
    }

    public function getFalseLabel(): string
    {
        return $this->falseLabel ?? __('No');
    }

    protected function applyDefault(Builder $query, mixed $value): void
    {
        $query->where($query->qualifyColumn($this->getAttribute()), filter_var($value, FILTER_VALIDATE_BOOLEAN));
    }
}
