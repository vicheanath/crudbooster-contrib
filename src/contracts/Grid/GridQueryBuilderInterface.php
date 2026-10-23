<?php

namespace crocodicstudio\crudbooster\contracts\Grid;

use crocodicstudio\crudbooster\grid\GridQuery;
use Illuminate\Database\Query\Builder;

interface GridQueryBuilderInterface
{
    /**
     * Builds the index query for a module: the base select, soft deletes, joins, subquery
     * columns, search, `where` parameters, URL filters and ordering.
     *
     * Options: `scope` (callable receiving the builder before soft deletes and joins, where
     * modules apply hook_query_index), `search`, `where`, `filters` and `orderby`.
     */
    public function buildQuery(string $table, string $primaryKey, array $columns, array $options = []): GridQuery;

    /**
     * Applies CRUDBooster's `filter_column` URL filters. Returns true when they set an order.
     */
    public function applyFilters(Builder $query, array $filterParams): bool;
}
