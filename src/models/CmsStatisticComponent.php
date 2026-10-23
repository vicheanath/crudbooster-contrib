<?php

namespace crocodicstudio\crudbooster\models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CmsStatisticComponent extends CrudboosterModel
{
    protected $table = 'cms_statistic_components';

    public function statistic(): BelongsTo
    {
        return $this->belongsTo(CmsStatistic::class, 'id_cms_statistics');
    }

    public function scopeForComponentId(Builder $query, ?string $componentId): Builder
    {
        return $query->where('componentID', $componentId);
    }

    /**
     * The components placed in one area of a statistic, in display order.
     */
    public function scopeInArea(Builder $query, $statisticId, ?string $area): Builder
    {
        return $query->where('id_cms_statistics', $statisticId)->where('area_name', $area)->orderBy('sorting', 'asc');
    }
}
