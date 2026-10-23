<?php

namespace crocodicstudio\crudbooster\services;

use crocodicstudio\crudbooster\contracts\Security\PermissionCheckerInterface;
use Illuminate\Contracts\Session\Session;
use Illuminate\Http\Request;

final class PermissionManager implements PermissionCheckerInterface
{
    public function __construct(
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
        if ((bool) $this->session->get('admin_is_superadmin')) {
            return true;
        }

        $modulePath = $this->modulePath();

        foreach ($this->session->get('admin_privileges_roles', []) ?: [] as $role) {
            if (isset($role->path) && $role->path === $modulePath) {
                return (bool) $role->{$permission};
            }
        }

        return false;
    }

    private function modulePath(): ?string
    {
        $adminPath = config('crudbooster.ADMIN_PATH');
        $segments = explode('/', trim($this->request->path(), '/'));
        $adminIndex = array_search($adminPath, $segments, true);

        return $adminIndex === false
            ? ($segments[0] ?? null)
            : ($segments[$adminIndex + 1] ?? null);
    }
}
