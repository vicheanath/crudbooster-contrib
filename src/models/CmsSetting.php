<?php

namespace crocodicstudio\crudbooster\models;

use Illuminate\Database\Eloquent\Builder;

class CmsSetting extends CrudboosterModel
{
    protected $table = 'cms_settings';

    public function scopeInGroup(Builder $query, ?string $group): Builder
    {
        return $query->where('group_setting', $group);
    }

    /**
     * The label to show: the stored one, or one derived from the name ("app_name" => "App Name").
     */
    public function displayLabel(): string
    {
        return $this->label ?: ucwords(str_replace('_', ' ', (string) $this->name));
    }
}
