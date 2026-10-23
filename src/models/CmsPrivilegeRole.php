<?php

namespace crocodicstudio\crudbooster\models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CmsPrivilegeRole extends CrudboosterModel
{
    protected $table = 'cms_privileges_roles';

    protected $casts = [
        'is_visible' => 'boolean',
        'is_create' => 'boolean',
        'is_read' => 'boolean',
        'is_edit' => 'boolean',
        'is_delete' => 'boolean',
    ];

    public function privilege(): BelongsTo
    {
        return $this->belongsTo(CmsPrivilege::class, 'id_cms_privileges');
    }

    public function modul(): BelongsTo
    {
        return $this->belongsTo(CmsModul::class, 'id_cms_moduls');
    }
}
