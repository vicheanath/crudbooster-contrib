<?php

namespace crocodicstudio\crudbooster\services;

use CRUDBooster;
use crocodicstudio\crudbooster\models\CmsPrivilege;
use crocodicstudio\crudbooster\models\CmsPrivilegeRole;
use crocodicstudio\crudbooster\models\CmsUser;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Contracts\Session\Session as SessionContract;
use Illuminate\Http\Request;

class AdminAuthenticationService
{
    public function __construct(
        private readonly CmsUser $users,
        private readonly CmsPrivilege $privileges,
        private readonly CmsPrivilegeRole $privilegeRoles,
        private readonly Hasher $hasher,
        private readonly SessionContract $session,
        private readonly Request $request,
    ) {
    }

    public function lockScreen(): void
    {
        $this->session->put('admin_lock', 1);
    }

    public function unlock(?string $password): bool
    {
        $user = $this->currentUser();

        if (! $user || ! $this->hasher->check($password, $user->password)) {
            return false;
        }

        $this->session->put('admin_lock', 0);

        return true;
    }

    public function login(string $email, string $password): bool
    {
        $user = $this->users
            ->where('email', $email)
            ->first();

        if (! $user || ! $this->hasher->check($password, $user->password)) {
            return false;
        }

        $context = $this->createContext($user);
        $this->session->put($context->sessionPayload());

        CRUDBooster::insertLog(cbLang('log_login', [
            'email' => $user->email,
            'ip' => $this->request->server('REMOTE_ADDR'),
        ]));

        $this->runAfterLoginHook();

        return true;
    }

    public function logout(): void
    {
        $user = $this->currentUser();

        if ($user) {
            CRUDBooster::insertLog(cbLang('log_logout', ['email' => $user->email]));
        }

        $this->session->flush();
    }

    public function sendPasswordReset(string $email): void
    {
        $randomPassword = str_random(5);

        $user = $this->users
            ->where('email', $email)
            ->firstOrFail();

        $user->password = $this->hasher->make($randomPassword);
        $user->save();
        $user->password = $randomPassword;
        CRUDBooster::sendEmail([
            'to' => $user->email,
            'data' => $user,
            'template' => 'forgot_password_backend',
        ]);

        CRUDBooster::insertLog(cbLang('log_forgot', [
            'email' => $email,
            'ip' => $this->request->server('REMOTE_ADDR'),
        ]));
    }

    private function currentUser(): ?CmsUser
    {
        $id = CRUDBooster::myId();

        if (! $id) {
            return null;
        }

        return $this->users->where('id', $id)->first();
    }

    private function createContext(CmsUser $user): AdminAuthenticationContext
    {
        $privilege = $this->privileges
            ->where('id', $user->id_cms_privileges)
            ->firstOrFail();

        $roles = $this->privilegeRoles
            ->where('id_cms_privileges', $user->id_cms_privileges)
            ->join('cms_moduls', 'cms_moduls.id', '=', 'cms_privileges_roles.id_cms_moduls')
            ->select(
                'cms_moduls.name',
                'cms_moduls.path',
                'is_visible',
                'is_create',
                'is_read',
                'is_edit',
                'is_delete'
            )
            ->get();

        return new AdminAuthenticationContext($user, $privilege, $roles);
    }

    private function runAfterLoginHook(): void
    {
        $hook = \App\Http\Controllers\CBHook::class;

        if (class_exists($hook)) {
            app($hook)->afterLogin();
        }
    }
}
