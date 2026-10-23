<?php

namespace crocodicstudio\crudbooster\security;

use crocodicstudio\crudbooster\contracts\Security\PrivilegeRolesInterface;
use crocodicstudio\crudbooster\models\CmsPrivilegeRole;
use Illuminate\Contracts\Session\Session;
use Illuminate\Support\Collection;

final class PrivilegeRoles implements PrivilegeRolesInterface
{
    public function __construct(
        private readonly CmsPrivilegeRole $roles,
        private readonly Session $session,
    ) {
    }

    public function forPrivilege($privilegeId): Collection
    {
        // Plain rows, not models: the session payload and myPrivilege() have always held stdClass rows.
        return $this->roles->newQuery()->forPrivilege($privilegeId)->withModule()->toBase()->get();
    }

    public function byModule($privilegeId): Collection
    {
        return $this->roles->newQuery()->forPrivilege($privilegeId)->get()->keyBy('id_cms_moduls');
    }

    public function refreshSession(): void
    {
        $this->session->put('admin_privileges_roles', $this->forPrivilege($this->session->get('admin_privileges')));
    }

    public function sync($privilegeId, array $matrix): void
    {
        $this->roles->getConnection()->transaction(function () use ($privilegeId, $matrix) {
            $this->roles->newQuery()->forPrivilege($privilegeId)->delete();

            foreach ($matrix as $moduleId => $permissions) {
                $this->create($privilegeId, $moduleId, (array) $permissions);
            }
        });
    }

    public function grantFullAccess($privilegeId, $moduleId): void
    {
        $this->create($privilegeId, $moduleId, array_fill_keys(CmsPrivilegeRole::PERMISSIONS, 1));
    }

    private function create($privilegeId, $moduleId, array $permissions): void
    {
        $attributes = ['id_cms_privileges' => $privilegeId, 'id_cms_moduls' => $moduleId];
        foreach (CmsPrivilegeRole::PERMISSIONS as $permission) {
            $attributes[$permission] = empty($permissions[$permission]) ? 0 : 1;
        }

        $this->roles->newQuery()->create($attributes);
    }
}
