<?php

namespace crocodicstudio\crudbooster\contracts\Grid;

use Illuminate\Database\Query\Builder;

/**
 * @deprecated 7.2 Only used by CBController modules. Use the filters in CrudBooster\Tables\Filters. See
 *     docs/en/deprecations.md.
 */
interface GridFilterParserInterface
{
    public function apply(Builder $query, array $filters): bool;
}
