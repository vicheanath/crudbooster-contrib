<?php

namespace crocodicstudio\crudbooster\tests\Feature;

use crocodicstudio\crudbooster\models\CmsMenu;
use crocodicstudio\crudbooster\models\CmsModul;
use crocodicstudio\crudbooster\models\CmsPrivilegeRole;
use CRUDBooster;

class ModuleAndMenuLifecycleTest extends FeatureTestCase
{
    public function test_module_add_form_grants_access_and_adds_a_visible_menu_group(): void
    {
        $this->loginAsAdmin();

        $this->post($this->admin('module_generator/add-save'), [
            'name' => 'Widgets',
            'table_name' => 'cms_logs',
            'icon' => 'fa fa-cube',
            'path' => 'widgets',
            'controller' => 'AdminWidgetsController',
        ])->assertRedirect();

        $module = CmsModul::query()->forPath('widgets')->firstOrFail();
        // The role used to be saved with a null module id, so the new module had no permissions.
        $role = CmsPrivilegeRole::query()->forPrivilege(session('admin_privileges'))->where('id_cms_moduls', $module->id)->first();
        $this->assertNotNull($role);
        $this->assertTrue($role->is_delete);
        $this->assertContains('widgets', collect(session('admin_privileges_roles'))->pluck('path')->all());

        $group = CmsMenu::query()->topLevel()->where('name', 'Widgets')->firstOrFail();
        $this->assertCount(2, $group->children);
        $visible = CRUDBooster::sidebarMenu()->firstWhere('name', 'Widgets');
        $this->assertNotNull($visible, 'The module menu group is not shown in the sidebar');
    }

    public function test_deleting_a_module_removes_its_menus_only(): void
    {
        $this->loginAsAdmin();
        $other = CmsMenu::query()->create(['name' => 'Keep me', 'type' => 'URL', 'path' => '/keep', 'parent_id' => 0, 'is_active' => 1]);
        $this->post($this->admin('module_generator/add-save'), [
            'name' => 'Widgets', 'table_name' => 'cms_logs', 'icon' => 'fa fa-cube', 'path' => 'widgets', 'controller' => 'AdminWidgetsController',
        ])->assertRedirect();
        $module = CmsModul::query()->forPath('widgets')->firstOrFail();

        $this->get($this->admin("module_generator/delete/{$module->id}"))->assertRedirect();

        $this->assertFalse(CmsMenu::query()->where('name', 'like', '%Widgets%')->exists());
        $this->assertTrue($other->exists && CmsMenu::query()->whereKey($other->id)->exists());
    }

    public function test_login_and_role_refresh_store_the_same_plain_row_shape(): void
    {
        $this->loginAsAdmin();
        $atLogin = collect(session('admin_privileges_roles'));

        $this->post($this->admin('module_generator/add-save'), [
            'name' => 'Widgets', 'table_name' => 'cms_logs', 'icon' => 'fa fa-cube', 'path' => 'widgets', 'controller' => 'AdminWidgetsController',
        ])->assertRedirect();
        $afterRefresh = collect(session('admin_privileges_roles'));

        $this->assertContainsOnlyInstancesOf(\stdClass::class, $atLogin);
        $this->assertContainsOnlyInstancesOf(\stdClass::class, $afterRefresh);
        $this->assertSame(array_keys((array) $atLogin->first()), array_keys((array) $afterRefresh->first()));
    }

    public function test_menu_management_lists_menus_with_their_privileges(): void
    {
        $this->loginAsAdmin();
        $menu = CmsMenu::query()->create(['name' => 'Reports', 'type' => 'URL', 'path' => '/reports', 'parent_id' => 0, 'is_active' => 1, 'sorting' => 1]);
        $menu->privileges()->attach(session('admin_privileges'));

        $this->get($this->admin('menu_management'))->assertOk()->assertSee('Reports')->assertSee('Super Administrator');
    }
}
