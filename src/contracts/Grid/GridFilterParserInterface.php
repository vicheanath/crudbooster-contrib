<?php

namespace crocodicstudio\crudbooster\contracts\Grid;

use Illuminate\Database\Query\Builder;

interface GridFilterParserInterface
{
    public function apply(Builder $query, array $filters): bool;
}
