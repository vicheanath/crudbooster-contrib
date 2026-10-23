<?php

namespace crocodicstudio\crudbooster\models;

use Illuminate\Database\Eloquent\Relations\HasMany;

class CmsModul extends CrudboosterModel
{
    protected $table = 'cms_moduls';

    protected $casts = [
        'is_protected' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function roles(): HasMany
    {
        return $this->hasMany(CmsPrivilegeRole::class, 'id_cms_moduls');
    }
}
