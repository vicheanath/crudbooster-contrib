<?php

namespace crocodicstudio\crudbooster\models;

use Illuminate\Database\Eloquent\Relations\HasMany;

class CmsMenu extends CrudboosterModel
{
    protected $table = 'cms_menus';

    protected $casts = [
        'is_active' => 'boolean',
        'is_dashboard' => 'boolean',
    ];

    public function privilegeLinks(): HasMany
    {
        return $this->hasMany(CmsMenuPrivilege::class, 'id_cms_menus');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function parent(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }
}
