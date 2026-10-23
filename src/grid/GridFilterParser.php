<?php

namespace crocodicstudio\crudbooster\grid;

use crocodicstudio\crudbooster\contracts\Grid\GridFilterParserInterface;
use Illuminate\Database\Query\Builder;

final class GridFilterParser implements GridFilterParserInterface
{
    public function apply(Builder $query, array $filters): bool
    {
        $query->where(function (Builder $where) use ($filters): void {
            foreach ($filters as $key => $filter) {
                $value = $filter['value'] ?? null;
                $type = $filter['type'] ?? null;

                if ($type === 'empty') {
                    $where->whereNull($key)->orWhere($key, '');
                    continue;
                }

                if ($value === '' || $type === '' || $type === 'between') {
                    continue;
                }

                if (in_array($type, ['in', 'not in'], true)) {
                    $values = array_filter(explode(',', (string) $value), static fn (string $item): bool => $item !== '');
                    if ($values) {
                        $type === 'in'
                            ? $where->whereIn($key, $values)
                            : $where->whereNotIn($key, $values);
                    }
                    continue;
                }

                if (in_array($type, ['like', 'not like'], true)) {
                    $value = '%'.$value.'%';
                }

                if ($key && $type && $value) {
                    $where->where($key, $type, $value);
                }
            }
        });

        $hasSorting = false;
        foreach ($filters as $key => $filter) {
            $sorting = $filter['sorting'] ?? '';
            $type = $filter['type'] ?? '';
            $value = $filter['value'] ?? null;

            if ($sorting !== '' && $key) {
                $query->orderBy($key, $sorting);
                $hasSorting = true;
            }

            if ($type === 'between' && $key && $value) {
                $query->whereBetween($key, $value);
            }
        }

        return $hasSorting;
    }
}
