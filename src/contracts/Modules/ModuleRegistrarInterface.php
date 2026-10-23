<?php

namespace crocodicstudio\crudbooster\contracts\Modules;

use crocodicstudio\crudbooster\models\CmsMenu;
use crocodicstudio\crudbooster\models\CmsModul;

/**
 * The sidebar menus that come with a module.
 */
interface ModuleRegistrarInterface
{
    /**
     * One top-level menu linking to the module's index, shown to the privilege. The Module Generator uses it.
     */
    public function addIndexMenu(CmsModul $module, $privilegeId): CmsMenu;

    /**
     * A top-level menu with "Add" and "List" children, all shown to the privilege. The Module Manager's
     * plain add form uses it.
     */
    public function addMenuGroup(CmsModul $module, $privilegeId): CmsMenu;

    /**
     * Deletes the menus that route to the module's controller, and a menu group left empty by that.
     */
    public function removeMenus(CmsModul $module): void;
}
