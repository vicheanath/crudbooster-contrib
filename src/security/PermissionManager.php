<?php

namespace crocodicstudio\crudbooster\security;

use crocodicstudio\crudbooster\contracts\Security\AuthenticationServiceInterface;
use crocodicstudio\crudbooster\contracts\Security\PermissionCheckerInterface;
use Illuminate\Contracts\Session\Session;
use Illuminate\Http\Request;

/**
 * Reads the current module's permissions from the privilege roles stored in the session at login.
 */
final class PermissionManager implements PermissionCheckerInterface
{
    public function __construct(
        private readonly AuthenticationServiceInterface $auth,
        private readonly Session $session,
        private readonly Request $request,
    ) {
    }

    public function canCreate(): bool
    {
        return $this->allows('is_create');
    }

    public function canRead(): bool
    {
        return $this->allows('is_read');
    }

    public function canView(): bool
    {
        return $this->allows('is_visible');
    }

    public function canUpdate(): bool
    {
        return $this->allows('is_edit');
    }

    public function canDelete(): bool
    {
        return $this->allows('is_delete');
    }

    private function allows(string $permission): bool
    {
        if ($this->auth->isSuperadmin()) {
            return true;
        }

        $modulePath = $this->modulePath();

        foreach ($this->session->get('admin_privileges_roles') ?: [] as $role) {
            if (isset($role->path) && $role->path == $modulePath) {
                return (bool) $role->{$permission};
            }
        }

        return false;
    }

    /**
     * The URL segment after the admin path, as CRUDBooster::getModulePath() reads it.
     */
    private function modulePath(): ?string
    {
        $segments = $this->request->segments();
        $adminPath = config('crudbooster.ADMIN_PATH');

        if (! $adminPath) {
            return $segments[0] ?? null;
        }

        $index = array_search($adminPath, $segments, true);

        return $index === false ? null : ($segments[$index + 1] ?? null);
    }
}
