<?php

namespace crocodicstudio\crudbooster\models;

use Illuminate\Database\Eloquent\Relations\HasMany;

class CmsPrivilege extends CrudboosterModel
{
    protected $table = 'cms_privileges';

    protected $casts = ['is_superadmin' => 'boolean'];

    public function users(): HasMany
    {
        return $this->hasMany(CmsUser::class, 'id_cms_privileges');
    }

    public function roles(): HasMany
    {
        return $this->hasMany(CmsPrivilegeRole::class, 'id_cms_privileges');
    }
}
