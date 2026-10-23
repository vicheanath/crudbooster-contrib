<?php

namespace crocodicstudio\crudbooster\models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * cms_moduls keeps a deleted_at column, but CBController's delete action writes it itself and some 5.x reads
 * include deleted modules, so deleted rows are excluded explicitly with notDeleted() instead of SoftDeletes.
 */
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

    public function scopeNotDeleted(Builder $query): Builder
    {
        return $query->whereNull('cms_moduls.deleted_at');
    }

    /**
     * Modules an administrator can assign privileges to; built-in protected modules are left out.
     */
    public function scopeUnprotected(Builder $query): Builder
    {
        return $query->where('is_protected', 0);
    }

    public function scopeForPath(Builder $query, ?string $path): Builder
    {
        return $query->where('path', $path);
    }

    public function scopeForTable(Builder $query, ?string $table): Builder
    {
        return $query->where('table_name', $table);
    }

    /**
     * Modules with both a path and a controller, which CBRouter registers as routes.
     */
    public function scopeRoutable(Builder $query): Builder
    {
        return $query->whereNotNull('path')->where('path', '!=', '')
            ->whereNotNull('controller')->where('controller', '!=', '');
    }
}
