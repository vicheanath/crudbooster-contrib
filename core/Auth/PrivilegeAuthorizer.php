<?php

namespace CrudBooster\Auth;

use crocodicstudio\crudbooster\contracts\Security\AuthenticationServiceInterface;
use crocodicstudio\crudbooster\models\CmsUser;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Contracts\Session\Session;
use Illuminate\Database\Eloquent\Model;

/**
 * 1. A model with a Laravel policy is authorized by the policy; the policy receives the
 *    CmsUser of the logged-in admin.
 * 2. Otherwise superadmins may do anything, and other admins what their privilege grants on the
 *    module whose path is the slug: the same matrix as classic modules (Privileges > Edit).
 *    `php artisan crudbooster:sync-panel` adds the modules for resources and pages.
 */
final class PrivilegeAuthorizer implements Authorizer
{
    private const PERMISSIONS = [
        'viewAny' => 'is_read',
        'view' => 'is_read',
        'create' => 'is_create',
        'update' => 'is_edit',
        'delete' => 'is_delete',
    ];

    public function __construct(
        private readonly AuthenticationServiceInterface $auth,
        private readonly Session $session,
        private readonly Gate $gate,
    ) {
    }

    public function allows(string $ability, string $slug, Model|string $subject): bool
    {
        $userId = $this->auth->currentUserId();
        if (! $userId) {
            return false;
        }

        $class = is_string($subject) ? $subject : $subject::class;
        if (class_exists($class) && $this->gate->getPolicyFor($class)) {
            return $this->gate->forUser(CmsUser::query()->find($userId))->allows($ability, $subject);
        }

        if ($this->auth->isSuperadmin()) {
            return true;
        }

        $permission = self::PERMISSIONS[$ability] ?? null;
        if (! $permission) {
            return false;
        }

        foreach ($this->session->get('admin_privileges_roles') ?: [] as $role) {
            if (isset($role->path) && $role->path === $slug) {
                return (bool) $role->{$permission};
            }
        }

        return false;
    }
}
