<?php

namespace crocodicstudio\crudbooster\grid;

use Illuminate\Database\Query\Builder;

/**
 * A prepared index query and its columns, each resolved with the keys the index and export
 * views read: `field`, `field_raw`, `field_with`, `type_data` and `is_subquery`.
 *
 * @deprecated 7.2 Only used by CBController modules. See docs/en/deprecations.md.
 */
final class GridQuery
{
    public function __construct(
        public readonly Builder $query,
        public readonly array $columns,
    ) {
    }
}
