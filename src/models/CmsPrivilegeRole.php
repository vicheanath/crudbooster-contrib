<?php

namespace crocodicstudio\crudbooster\models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CmsPrivilegeRole extends CrudboosterModel
{
    public const PERMISSIONS = ['is_visible', 'is_create', 'is_read', 'is_edit', 'is_delete'];

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

    public function scopeForPrivilege(Builder $query, $privilegeId): Builder
    {
        return $query->where('cms_privileges_roles.id_cms_privileges', $privilegeId);
    }

    /**
     * Each role with its module's name and path: the rows kept in the "admin_privileges_roles" session key.
     */
    public function scopeWithModule(Builder $query): Builder
    {
        return $query->join('cms_moduls', 'cms_moduls.id', '=', 'cms_privileges_roles.id_cms_moduls')
            ->select(array_merge(['cms_moduls.name', 'cms_moduls.path'], self::PERMISSIONS));
    }
}
