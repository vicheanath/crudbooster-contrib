<?php

namespace CrudBooster\Tables\Filters;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 *     SelectFilter::make('status')->options(['draft' => 'Draft', 'published' => 'Published'])
 *     SelectFilter::make('category')->relationship('category', 'name')
 */
class SelectFilter extends BaseFilter
{
    protected string $view = 'cb::filters.select';

    protected array|Closure $options = [];

    protected ?string $relationship = null;

    protected ?string $titleAttribute = null;

    public function options(array|Closure $options): static
    {
        $this->options = $options;

        return $this;
    }

    public function relationship(string $name, string $titleAttribute): static
    {
        $this->relationship = $name;
        $this->titleAttribute = $titleAttribute;

        return $this;
    }

    public function getOptions(Model $model): array
    {
        if ($this->relationship) {
            $related = $model->{$this->relationship}()->getRelated();

            return $related->newQuery()->orderBy($this->titleAttribute)
                ->pluck($this->titleAttribute, $related->getKeyName())->all();
        }

        $options = $this->evaluate($this->options);

        // A list of values is its own labels.
        return array_is_list($options) ? array_combine($options, $options) : $options;
    }

    protected function applyDefault(Builder $query, mixed $value): void
    {
        if ($this->relationship) {
            $query->whereHas($this->relationship, fn (Builder $related) => $related->whereKey($value));

            return;
        }

        $query->where($query->qualifyColumn($this->getAttribute()), $value);
    }
}
