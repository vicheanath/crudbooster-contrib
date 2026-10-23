<?php

namespace crocodicstudio\crudbooster\modules;

use crocodicstudio\crudbooster\contracts\Modules\ModuleRegistrarInterface;
use crocodicstudio\crudbooster\models\CmsMenu;
use crocodicstudio\crudbooster\models\CmsModul;

final class ModuleRegistrar implements ModuleRegistrarInterface
{
    public function __construct(private readonly CmsMenu $menus)
    {
    }

    public function addIndexMenu(CmsModul $module, $privilegeId): CmsMenu
    {
        return $this->createMenu($privilegeId, [
            'name' => $module->name,
            'icon' => $module->icon,
            'path' => $module->controller.'GetIndex',
            'type' => 'Route',
            'sorting' => CmsMenu::nextTopLevelSorting(),
            'parent_id' => 0,
        ]);
    }

    public function addMenuGroup(CmsModul $module, $privilegeId): CmsMenu
    {
        $parent = $this->createMenu($privilegeId, [
            'name' => $module->name,
            'icon' => $module->icon,
            'path' => '#',
            'type' => 'URL External',
            'sorting' => CmsMenu::nextTopLevelSorting(),
            'parent_id' => 0,
        ]);

        $this->createMenu($privilegeId, [
            'name' => cbLang('text_default_add_new_module', ['module' => $module->name]),
            'icon' => 'fa fa-plus',
            'path' => $module->controller.'GetAdd',
            'type' => 'Route',
            'sorting' => 1,
            'parent_id' => $parent->id,
        ]);
        $this->createMenu($privilegeId, [
            'name' => cbLang('text_default_list_module', ['module' => $module->name]),
            'icon' => 'fa fa-bars',
            'path' => $module->controller.'GetIndex',
            'type' => 'Route',
            'sorting' => 2,
            'parent_id' => $parent->id,
        ]);

        return $parent;
    }

    public function removeMenus(CmsModul $module): void
    {
        if (! $module->controller) {
            return;
        }

        $menus = $this->menus->newQuery()->where('path', 'like', '%'.$module->controller.'%');
        $parentIds = (clone $menus)->where('parent_id', '!=', 0)->pluck('parent_id')->unique();
        $menus->delete();

        // A group parent links nowhere ("#"); once its children are gone it would stay as an empty entry.
        // The ids are read first: MySQL cannot delete from a table that the same statement's subquery reads.
        $emptyGroups = $this->menus->newQuery()->whereKey($parentIds)->where('path', '#')
            ->whereDoesntHave('children')->pluck('id');
        $this->menus->newQuery()->whereKey($emptyGroups)->delete();
    }

    private function createMenu($privilegeId, array $attributes): CmsMenu
    {
        $menu = $this->menus->newQuery()->create($attributes + [
            'created_at' => now()->format('Y-m-d H:i:s'),
            'is_active' => 1,
            'id_cms_privileges' => $privilegeId,
        ]);
        $menu->privileges()->attach($privilegeId);

        return $menu;
    }
}
