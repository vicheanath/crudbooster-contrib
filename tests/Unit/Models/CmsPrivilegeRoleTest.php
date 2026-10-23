<?php

namespace crocodicstudio\crudbooster\tests\Unit\Models;

use crocodicstudio\crudbooster\models\CmsModul;
use crocodicstudio\crudbooster\models\CmsPrivilege;
use crocodicstudio\crudbooster\models\CmsPrivilegeRole;
use crocodicstudio\crudbooster\tests\TestCase;

class CmsPrivilegeRoleTest extends TestCase
{
    public function test_with_module_adds_the_module_name_and_path(): void
    {
        $module = CmsModul::query()->create(['name' => 'Products', 'path' => 'products', 'controller' => 'AdminProductsController', 'is_protected' => 0, 'is_active' => 1]);
        CmsPrivilegeRole::query()->create(['id_cms_privileges' => 50, 'id_cms_moduls' => $module->id, 'is_visible' => 1, 'is_create' => 0, 'is_read' => 1, 'is_edit' => 0, 'is_delete' => 0]);
        CmsPrivilegeRole::query()->create(['id_cms_privileges' => 51, 'id_cms_moduls' => $module->id, 'is_visible' => 1, 'is_create' => 1, 'is_read' => 1, 'is_edit' => 1, 'is_delete' => 1]);

        $rows = CmsPrivilegeRole::query()->forPrivilege(50)->withModule()->get();

        $this->assertCount(1, $rows);
        $this->assertSame(['name', 'path', ...CmsPrivilegeRole::PERMISSIONS], array_keys($rows[0]->getAttributes()));
        $this->assertSame('products', $rows[0]->path);
        $this->assertTrue($rows[0]->is_read);
        $this->assertFalse($rows[0]->is_create);
    }

    public function test_relations(): void
    {
        $module = CmsModul::query()->create(['name' => 'Products', 'path' => 'products', 'is_protected' => 0, 'is_active' => 1]);
        $superadmin = CmsPrivilege::query()->where('name', 'Super Administrator')->value('id');
        $role = CmsPrivilegeRole::query()->create(['id_cms_privileges' => $superadmin, 'id_cms_moduls' => $module->id]);

        $this->assertSame('Products', $role->modul->name);
        $this->assertSame('Super Administrator', $role->privilege->name);
        $this->assertTrue($module->roles->contains($role));
    }
}
