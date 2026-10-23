<?php

namespace crocodicstudio\crudbooster\models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CmsStatisticComponent extends CrudboosterModel
{
    protected $table = 'cms_statistic_components';

    public function statistic(): BelongsTo
    {
        return $this->belongsTo(CmsStatistic::class, 'id_cms_statistics');
    }
}
