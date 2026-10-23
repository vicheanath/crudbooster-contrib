<?php

namespace crocodicstudio\crudbooster\tests\Feature;

use crocodicstudio\crudbooster\contracts\Audit\AuditLoggerInterface;
use crocodicstudio\crudbooster\contracts\Security\AuthenticationServiceInterface;
use crocodicstudio\crudbooster\contracts\Security\PermissionCheckerInterface;
use crocodicstudio\crudbooster\tests\TestCase;
use Illuminate\Support\Facades\Session;

class SolidArchitectureTest extends TestCase
{
    public function test_security_contracts_are_bound_to_replaceable_services(): void
    {
        $this->assertInstanceOf(AuthenticationServiceInterface::class, app(AuthenticationServiceInterface::class));
        $this->assertInstanceOf(PermissionCheckerInterface::class, app(PermissionCheckerInterface::class));
        $this->assertInstanceOf(AuditLoggerInterface::class, app(AuditLoggerInterface::class));
    }

    public function test_permission_manager_preserves_super_admin_and_role_permissions(): void
    {
        Session::put([
            'admin_is_superadmin' => false,
            'admin_privileges_roles' => [
                (object) [
                    'path' => 'products',
                    'is_visible' => true,
                    'is_create' => true,
                    'is_read' => true,
                    'is_edit' => false,
                    'is_delete' => false,
                ],
            ],
        ]);

        $this->get('/admin/products');

        $this->assertTrue(app(PermissionCheckerInterface::class)->canView());
        $this->assertTrue(app(PermissionCheckerInterface::class)->canCreate());
        $this->assertTrue(app(PermissionCheckerInterface::class)->canRead());
        $this->assertFalse(app(PermissionCheckerInterface::class)->canUpdate());
        $this->assertFalse(app(PermissionCheckerInterface::class)->canDelete());

        Session::put('admin_is_superadmin', true);

        $this->assertTrue(app(PermissionCheckerInterface::class)->canDelete());
    }
}
