<?php

namespace CrudBooster\Tables;

use Closure;
use CrudBooster\Actions\Action;
use CrudBooster\Actions\BulkAction;
use CrudBooster\Tables\Columns\Column;
use CrudBooster\Tables\Filters\BaseFilter;
use Illuminate\Database\Eloquent\Builder;

/**
 * The list page of a resource, filled in by Resource::table():
 *
 *     return $table
 *         ->columns([TextColumn::make('name')->searchable()->sortable(), ...])
 *         ->filters([SelectFilter::make('status')->options([...])])
 *         ->actions([Action::make('publish')->action(...)])
 *         ->defaultSort('created_at', 'desc');
 */
class Table
{
    /** @var Column[] */
    protected array $columns = [];

    /** @var BaseFilter[] */
    protected array $filters = [];

    /** @var Action[] */
    protected array $actions = [];

    /** @var BulkAction[] */
    protected array $bulkActions = [];

    protected ?string $defaultSortColumn = null;

    protected string $defaultSortDirection = 'asc';

    protected int $perPage = 20;

    protected array $perPageOptions = [10, 20, 50, 100];

    protected ?Closure $modifyQueryUsing = null;

    protected bool $defaultActions = true;

    protected bool $deleteBulkAction = true;

    protected ?string $emptyStateMessage = null;

    public function columns(array $columns): static
    {
        $this->columns = $columns;

        return $this;
    }

    public function filters(array $filters): static
    {
        $this->filters = $filters;

        return $this;
    }

    public function actions(array $actions): static
    {
        $this->actions = $actions;

        return $this;
    }

    public function bulkActions(array $actions): static
    {
        $this->bulkActions = $actions;

        return $this;
    }

    public function defaultSort(string $column, string $direction = 'asc'): static
    {
        $this->defaultSortColumn = $column;
        $this->defaultSortDirection = strtolower($direction) === 'desc' ? 'desc' : 'asc';

        return $this;
    }

    public function paginate(int $perPage, ?array $options = null): static
    {
        $this->perPage = $perPage;
        if ($options !== null) {
            $this->perPageOptions = $options;
        }

        return $this;
    }

    /**
     * Constrains the list only (the resource's modifyQuery() constrains every page):
     * fn (Builder $query) => $query->where('archived', false)
     */
    public function modifyQueryUsing(Closure $callback): static
    {
        $this->modifyQueryUsing = $callback;

        return $this;
    }

    /**
     * Leaves out the view, edit and delete buttons the resource adds to each row.
     */
    public function withoutDefaultActions(): static
    {
        $this->defaultActions = false;

        return $this;
    }

    public function withoutBulkDelete(): static
    {
        $this->deleteBulkAction = false;

        return $this;
    }

    public function emptyStateMessage(string $message): static
    {
        $this->emptyStateMessage = $message;

        return $this;
    }

    /**
     * @return Column[]
     */
    public function getColumns(): array
    {
        return array_values(array_filter($this->columns, fn (Column $column) => $column->isVisible()));
    }

    public function getColumn(string $name): ?Column
    {
        foreach ($this->getColumns() as $column) {
            if ($column->getName() === $name) {
                return $column;
            }
        }

        return null;
    }

    /**
     * @return BaseFilter[]
     */
    public function getFilters(): array
    {
        return $this->filters;
    }

    /**
     * @return Action[]
     */
    public function getActions(): array
    {
        return $this->actions;
    }

    public function getAction(string $name): ?Action
    {
        foreach ($this->actions as $action) {
            if ($action->getName() === $name) {
                return $action;
            }
        }

        return null;
    }

    /**
     * @return BulkAction[]
     */
    public function getBulkActions(): array
    {
        return $this->bulkActions;
    }

    public function getBulkAction(string $name): ?BulkAction
    {
        foreach ($this->bulkActions as $action) {
            if ($action->getName() === $name) {
                return $action;
            }
        }

        return null;
    }

    public function hasDefaultActions(): bool
    {
        return $this->defaultActions;
    }

    public function hasBulkDelete(): bool
    {
        return $this->deleteBulkAction;
    }

    public function isSearchable(): bool
    {
        foreach ($this->getColumns() as $column) {
            if ($column->isSearchable()) {
                return true;
            }
        }

        return false;
    }

    public function getDefaultSort(): array
    {
        return [$this->defaultSortColumn, $this->defaultSortDirection];
    }

    public function getPerPage(): int
    {
        return $this->perPage;
    }

    public function getPerPageOptions(): array
    {
        return $this->perPageOptions;
    }

    public function getEmptyStateMessage(): string
    {
        return $this->emptyStateMessage ?? __('No records found.');
    }

    /**
     * The relationships the columns read, to eager load them.
     */
    public function getEagerLoads(): array
    {
        $relationships = [];
        foreach ($this->getColumns() as $column) {
            if ($relationship = $column->getRelationshipName()) {
                $relationships[] = $relationship;
            }
        }

        return array_values(array_unique($relationships));
    }

    /**
     * Applies search, filters and sorting from the request parameters.
     */
    public function applyToQuery(Builder $query, ?string $search, array $filters, ?string $sort, ?string $direction): Builder
    {
        if ($this->modifyQueryUsing) {
            app()->call($this->modifyQueryUsing, ['query' => $query]);
        }

        $query->with($this->getEagerLoads());

        $search = trim((string) $search);
        if ($search !== '' && $this->isSearchable()) {
            $query->where(function (Builder $query) use ($search) {
                foreach ($this->getColumns() as $column) {
                    if ($column->isSearchable()) {
                        $column->applySearch($query, $search);
                    }
                }
            });
        }

        foreach ($this->filters as $filter) {
            $filter->apply($query, $filters[$filter->getName()] ?? null);
        }

        $direction = strtolower((string) $direction) === 'desc' ? 'desc' : 'asc';
        $column = $sort ? $this->getColumn($sort) : null;

        if ($column && $column->isSortable()) {
            $column->applySort($query, $direction);
        } elseif ($this->defaultSortColumn) {
            $query->orderBy($query->qualifyColumn($this->defaultSortColumn), $this->defaultSortDirection);
        } else {
            $query->orderBy($query->getModel()->getQualifiedKeyName(), 'desc');
        }

        return $query;
    }
}
