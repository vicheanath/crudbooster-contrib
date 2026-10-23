<?php

namespace crocodicstudio\crudbooster\tests\Unit\Security;

use crocodicstudio\crudbooster\contracts\Security\PrivilegeRolesInterface;
use crocodicstudio\crudbooster\models\CmsModul;
use crocodicstudio\crudbooster\models\CmsPrivilegeRole;
use crocodicstudio\crudbooster\tests\TestCase;
use Illuminate\Support\Facades\Session;

class PrivilegeRolesTest extends TestCase
{
    private PrivilegeRolesInterface $roles;

    private CmsModul $products;

    private CmsModul $orders;

    protected function setUp(): void
    {
        parent::setUp();

        $this->roles = app(PrivilegeRolesInterface::class);
        $this->products = CmsModul::query()->create(['name' => 'Products', 'path' => 'products', 'is_protected' => 0, 'is_active' => 1]);
        $this->orders = CmsModul::query()->create(['name' => 'Orders', 'path' => 'orders', 'is_protected' => 0, 'is_active' => 1]);
    }

    public function test_for_privilege_returns_plain_rows_with_the_module(): void
    {
        $this->roles->grantFullAccess(40, $this->products->id);

        $rows = $this->roles->forPrivilege(40);

        $this->assertCount(1, $rows);
        $this->assertInstanceOf(\stdClass::class, $rows[0]);
        $this->assertSame('products', $rows[0]->path);
        $this->assertSame('Products', $rows[0]->name);
        $this->assertEquals(1, $rows[0]->is_delete);
        $this->assertCount(0, $this->roles->forPrivilege(41));
    }

    public function test_sync_replaces_the_matrix_and_stores_missing_permissions_as_zero(): void
    {
        $this->roles->grantFullAccess(40, $this->orders->id);

        $this->roles->sync(40, [
            $this->products->id => ['is_visible' => '1', 'is_read' => 'on'],
        ]);

        $byModule = $this->roles->byModule(40);
        $this->assertSame([$this->products->id], $byModule->keys()->map('intval')->all());
        $role = $byModule->get($this->products->id);
        $this->assertTrue($role->is_visible);
        $this->assertTrue($role->is_read);
        $this->assertFalse($role->is_create);
        $this->assertFalse($role->is_edit);
        $this->assertFalse($role->is_delete);

        $this->roles->sync(40, []);
        $this->assertSame(0, CmsPrivilegeRole::query()->forPrivilege(40)->count());
    }

    public function test_grant_full_access_sets_every_permission(): void
    {
        $this->roles->grantFullAccess(40, $this->products->id);

        $role = $this->roles->byModule(40)->get($this->products->id);
        foreach (CmsPrivilegeRole::PERMISSIONS as $permission) {
            $this->assertTrue($role->{$permission}, $permission);
        }
    }

    public function test_refresh_session_reloads_the_current_privileges_roles(): void
    {
        Session::put('admin_privileges', 40);
        $this->roles->grantFullAccess(40, $this->products->id);

        $this->roles->refreshSession();

        $this->assertSame(['products'], collect(Session::get('admin_privileges_roles'))->pluck('path')->all());
    }
}
