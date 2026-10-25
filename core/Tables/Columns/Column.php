<?php

namespace CrudBooster\Tables\Columns;

use Closure;
use CrudBooster\Concerns\CanBeHidden;
use CrudBooster\Concerns\EvaluatesClosures;
use CrudBooster\Concerns\HasLabel;
use CrudBooster\Concerns\HasName;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * One value of a record, shown in the table and on the detail page. The name is an attribute
 * or a relationship path ("category.name"); relationships are eager loaded for the table.
 *
 * A custom column type extends this class and sets $view (or overrides formatState()).
 */
abstract class Column
{
    use CanBeHidden;
    use EvaluatesClosures;
    use HasLabel;
    use HasName;

    protected string $view = 'cb::columns.text';

    protected bool $sortable = false;

    protected bool $searchable = false;

    protected ?Closure $searchUsing = null;

    protected ?Closure $sortUsing = null;

    protected ?Closure $formatStateUsing = null;

    protected ?Closure $getStateUsing = null;

    protected string|Closure|null $url = null;

    protected bool $openUrlInNewTab = false;

    protected string $placeholder = '—';

    protected ?string $alignment = null;

    public function sortable(bool|Closure $sortable = true): static
    {
        if ($sortable instanceof Closure) {
            $this->sortUsing = $sortable;
            $sortable = true;
        }
        $this->sortable = $sortable;

        return $this;
    }

    /**
     * Includes the column in the table's search box. A closure receives ($query, $search) and
     * replaces the default LIKE match.
     */
    public function searchable(bool|Closure $searchable = true): static
    {
        if ($searchable instanceof Closure) {
            $this->searchUsing = $searchable;
            $searchable = true;
        }
        $this->searchable = $searchable;

        return $this;
    }

    /**
     * Computes the value instead of reading the attribute: fn (Model $record) => ...
     */
    public function state(Closure $callback): static
    {
        $this->getStateUsing = $callback;

        return $this;
    }

    /**
     * Changes how the value is displayed: fn ($state, Model $record) => ...
     */
    public function formatStateUsing(Closure $callback): static
    {
        $this->formatStateUsing = $callback;

        return $this;
    }

    public function url(string|Closure|null $url, bool $openInNewTab = false): static
    {
        $this->url = $url;
        $this->openUrlInNewTab = $openInNewTab;

        return $this;
    }

    public function placeholder(string $placeholder): static
    {
        $this->placeholder = $placeholder;

        return $this;
    }

    public function alignEnd(): static
    {
        $this->alignment = 'end';

        return $this;
    }

    public function alignCenter(): static
    {
        $this->alignment = 'center';

        return $this;
    }

    public function isSortable(): bool
    {
        return $this->sortable && ($this->sortUsing || ! $this->getRelationshipName()) && ! $this->getStateUsing;
    }

    public function isSearchable(): bool
    {
        return $this->searchable;
    }

    public function getAlignment(): ?string
    {
        return $this->alignment;
    }

    public function getPlaceholder(): string
    {
        return $this->placeholder;
    }

    /**
     * "category" for "category.name"; null for a plain attribute.
     */
    public function getRelationshipName(): ?string
    {
        return str_contains($this->name, '.') ? Str::beforeLast($this->name, '.') : null;
    }

    public function getRelationshipAttribute(): string
    {
        return Str::afterLast($this->name, '.');
    }

    public function getState(Model $record): mixed
    {
        if ($this->getStateUsing) {
            return $this->evaluate($this->getStateUsing, ['record' => $record]);
        }

        if ($relationship = $this->getRelationshipName()) {
            $related = data_get($record, $relationship);

            // A to-many relationship yields one value per related record.
            $state = $related instanceof Collection
                ? $related->pluck($this->getRelationshipAttribute())
                : data_get($related, $this->getRelationshipAttribute());
        } else {
            $state = $record->getAttribute($this->name);
        }

        if ($state instanceof Collection) {
            $state = $state->filter(fn ($value) => $value !== null && $value !== '')->values();

            return $state->isEmpty() ? null : $state;
        }

        return $state;
    }

    /**
     * The display value. Strings are escaped by the view; return an Htmlable for raw HTML.
     */
    public function formatState(mixed $state, Model $record): mixed
    {
        if ($this->formatStateUsing) {
            return $this->evaluate($this->formatStateUsing, ['state' => $state, 'record' => $record]);
        }

        if ($state instanceof Collection) {
            return $state->map(fn ($item) => $this->formatScalar($item))->implode(', ');
        }

        return $this->formatScalar($state);
    }

    protected function formatScalar(mixed $state): mixed
    {
        if ($state instanceof \BackedEnum) {
            return $state->value;
        }
        if ($state instanceof \UnitEnum) {
            return $state->name;
        }
        if (is_bool($state)) {
            return $state ? __('Yes') : __('No');
        }
        if (is_array($state)) {
            return implode(', ', array_map('strval', $state));
        }

        return $state;
    }

    public function getUrl(Model $record): ?string
    {
        return $this->evaluate($this->url, ['record' => $record]);
    }

    public function shouldOpenUrlInNewTab(): bool
    {
        return $this->openUrlInNewTab;
    }

    public function applySearch(Builder $query, string $search): void
    {
        if ($this->searchUsing) {
            $query->orWhere(fn (Builder $query) => $this->evaluate($this->searchUsing, ['query' => $query, 'search' => $search]));

            return;
        }

        $pattern = '%'.addcslashes($search, '%_\\').'%';

        if ($relationship = $this->getRelationshipName()) {
            $query->orWhereHas($relationship, fn (Builder $query) => $query->where(
                $query->qualifyColumn($this->getRelationshipAttribute()), 'like', $pattern
            ));

            return;
        }

        $query->orWhere($query->qualifyColumn($this->name), 'like', $pattern);
    }

    public function applySort(Builder $query, string $direction): void
    {
        if ($this->sortUsing) {
            $this->evaluate($this->sortUsing, ['query' => $query, 'direction' => $direction]);

            return;
        }

        $query->orderBy($query->qualifyColumn($this->name), $direction);
    }

    public function render(Model $record): string
    {
        $state = $this->getState($record);

        return view($this->view, [
            'column' => $this,
            'record' => $record,
            'state' => $state,
            'display' => $state === null || $state === '' ? null : $this->formatState($state, $record),
            'url' => $this->getUrl($record),
        ])->render();
    }
}
