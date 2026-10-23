<?php

namespace crocodicstudio\crudbooster\contracts\Security;

use Illuminate\Support\Collection;

/**
 * What each privilege may do in each module (cms_privileges_roles).
 */
interface PrivilegeRolesInterface
{
    /**
     * The privilege's roles with their module's name and path, as plain rows: the shape kept in the
     * "admin_privileges_roles" session key and returned by CRUDBooster::myPrivilege().
     */
    public function forPrivilege($privilegeId): Collection;

    /**
     * The privilege's roles keyed by module id, for the privilege edit form.
     */
    public function byModule($privilegeId): Collection;

    /**
     * Reloads the current admin's roles into the session after they change.
     */
    public function refreshSession(): void;

    /**
     * Replaces the privilege's roles with a submitted matrix: [moduleId => ['is_visible' => 1, ...]].
     * Missing permissions are stored as 0.
     */
    public function sync($privilegeId, array $matrix): void;

    /**
     * Gives the privilege every permission on the module.
     */
    public function grantFullAccess($privilegeId, $moduleId): void;
}
