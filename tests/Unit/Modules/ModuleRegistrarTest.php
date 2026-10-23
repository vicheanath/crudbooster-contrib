<?php

namespace crocodicstudio\crudbooster\tests\Unit\Modules;

use crocodicstudio\crudbooster\contracts\Modules\ModuleRegistrarInterface;
use crocodicstudio\crudbooster\models\CmsMenu;
use crocodicstudio\crudbooster\models\CmsModul;
use crocodicstudio\crudbooster\tests\TestCase;

class ModuleRegistrarTest extends TestCase
{
    private ModuleRegistrarInterface $registrar;

    private CmsModul $module;

    protected function setUp(): void
    {
        parent::setUp();

        $this->registrar = app(ModuleRegistrarInterface::class);
        $this->module = CmsModul::query()->create(['name' => 'Products', 'icon' => 'fa fa-cube', 'path' => 'products', 'controller' => 'AdminProductsController', 'is_protected' => 0, 'is_active' => 1]);
        CmsMenu::query()->create(['name' => 'Existing', 'type' => 'URL', 'path' => '/x', 'parent_id' => 0, 'sorting' => 5, 'is_active' => 1]);
    }

    public function test_index_menu_is_last_and_shown_to_the_privilege(): void
    {
        $menu = $this->registrar->addIndexMenu($this->module, 1);

        $this->assertSame('AdminProductsControllerGetIndex', $menu->path);
        $this->assertSame('Route', $menu->type);
        $this->assertSame('fa fa-cube', $menu->icon);
        $this->assertEquals(6, $menu->sorting);
        $this->assertTrue(CmsMenu::query()->visibleTo(1)->whereKey($menu->id)->exists());
    }

    public function test_menu_group_has_add_and_list_children_all_visible(): void
    {
        $parent = $this->registrar->addMenuGroup($this->module, 1);

        $this->assertSame('#', $parent->path);
        $this->assertSame(
            ['AdminProductsControllerGetAdd', 'AdminProductsControllerGetIndex'],
            $parent->children()->sorted()->pluck('path')->all()
        );
        $this->assertSame(3, CmsMenu::query()->visibleTo(1)->count());
    }

    public function test_remove_menus_deletes_only_the_modules_menus(): void
    {
        $this->registrar->addMenuGroup($this->module, 1);

        $this->registrar->removeMenus($this->module);

        $this->assertSame(['Existing'], CmsMenu::query()->pluck('name')->all());
    }

    public function test_remove_menus_without_a_controller_deletes_nothing(): void
    {
        // A LIKE '%%' would have matched every menu.
        $this->registrar->removeMenus(new CmsModul(['controller' => '']));

        $this->assertSame(1, CmsMenu::query()->count());
    }
}
