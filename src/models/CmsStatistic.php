<?php

namespace crocodicstudio\crudbooster\models;

use Illuminate\Database\Eloquent\Relations\HasMany;

class CmsStatistic extends CrudboosterModel
{
    protected $table = 'cms_statistics';

    public function components(): HasMany
    {
        return $this->hasMany(CmsStatisticComponent::class, 'id_cms_statistics');
    }
}
