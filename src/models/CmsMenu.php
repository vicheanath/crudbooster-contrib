<?php

namespace crocodicstudio\crudbooster\models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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

    public function privileges(): BelongsToMany
    {
        return $this->belongsToMany(CmsPrivilege::class, 'cms_menus_privileges', 'id_cms_menus', 'id_cms_privileges');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * Menus granted to the privilege through cms_menus_privileges.
     */
    public function scopeVisibleTo(Builder $query, $privilegeId): Builder
    {
        return $query->whereIn('cms_menus.id', function ($granted) use ($privilegeId) {
            $granted->select('id_cms_menus')->from('cms_menus_privileges')->where('id_cms_privileges', $privilegeId);
        });
    }

    public function scopeActive(Builder $query, bool $active = true): Builder
    {
        return $query->where('is_active', $active ? 1 : 0);
    }

    public function scopeTopLevel(Builder $query): Builder
    {
        return $query->where('parent_id', 0);
    }

    public function scopeDashboard(Builder $query, bool $dashboard = true): Builder
    {
        return $query->where('is_dashboard', $dashboard ? 1 : 0);
    }

    public function scopeSorted(Builder $query): Builder
    {
        return $query->orderBy('sorting', 'asc');
    }

    /**
     * The sorting value that places a new top-level menu last.
     */
    public static function nextTopLevelSorting(): int
    {
        return (int) static::query()->topLevel()->max('sorting') + 1;
    }
}
