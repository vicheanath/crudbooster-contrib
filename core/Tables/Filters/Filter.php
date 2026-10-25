<?php

namespace CrudBooster\Tables\Filters;

use Illuminate\Database\Eloquent\Builder;

/**
 * A checkbox that applies its query when ticked:
 *
 *     Filter::make('expensive')->query(fn (Builder $query) => $query->where('price', '>', 100))
 */
class Filter extends BaseFilter
{
    protected string $view = 'cb::filters.checkbox';

    public function isActive(mixed $value): bool
    {
        return (bool) $value;
    }

    protected function applyDefault(Builder $query, mixed $value): void
    {
        $query->where($query->qualifyColumn($this->getAttribute()), true);
    }
}
