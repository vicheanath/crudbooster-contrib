<?php

namespace crocodicstudio\crudbooster\services;

use CRUDBooster;
use crocodicstudio\crudbooster\models\CmsPrivilege;
use crocodicstudio\crudbooster\models\CmsPrivilegeRole;
use crocodicstudio\crudbooster\models\CmsUser;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Session;

class AdminAuthenticationService
{
    public function lockScreen(): void
    {
        Session::put('admin_lock', 1);
    }

    public function unlock(?string $password): bool
    {
        $user = $this->currentUser();

        if (! $user || ! Hash::check($password, $user->password)) {
            return false;
        }

        Session::put('admin_lock', 0);

        return true;
    }

    public function login(string $email, string $password): bool
    {
        $user = (new CmsUser)
            ->where('email', $email)
            ->first();

        if (! $user || ! Hash::check($password, $user->password)) {
            return false;
        }

        $privilege = CmsPrivilege::query()
            ->where('id', $user->id_cms_privileges)
            ->first();

        $roles = CmsPrivilegeRole::query()
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

        Session::put([
            'admin_id' => $user->id,
            'admin_is_superadmin' => $privilege->is_superadmin,
            'admin_name' => $user->name,
            'admin_photo' => $user->photo
                ? asset($user->photo)
                : asset('vendor/crudbooster/avatar.jpg'),
            'admin_privileges_roles' => $roles,
            'admin_privileges' => $user->id_cms_privileges,
            'admin_privileges_name' => $privilege->name,
            'admin_lock' => 0,
            'theme_color' => $privilege->theme_color,
            'appname' => get_setting('appname'),
        ]);

        CRUDBooster::insertLog(cbLang('log_login', [
            'email' => $user->email,
            'ip' => Request::server('REMOTE_ADDR'),
        ]));

        $this->runAfterLoginHook();

        return true;
    }

    public function logout(): void
    {
        $user = CRUDBooster::me();
        CRUDBooster::insertLog(cbLang('log_logout', ['email' => $user->email]));
        Session::flush();
    }

    public function sendPasswordReset(string $email): void
    {
        $randomPassword = str_random(5);

        (new CmsUser)
            ->where('email', $email)
            ->update(['password' => Hash::make($randomPassword)]);

        $user = CRUDBooster::first(config('crudbooster.USER_TABLE'), ['email' => $email]);
        $user->password = $randomPassword;
        CRUDBooster::sendEmail([
            'to' => $user->email,
            'data' => $user,
            'template' => 'forgot_password_backend',
        ]);

        CRUDBooster::insertLog(cbLang('log_forgot', [
            'email' => $email,
            'ip' => Request::server('REMOTE_ADDR'),
        ]));
    }

    private function currentUser()
    {
        $id = CRUDBooster::myId();

        if (! $id) {
            return null;
        }

        return (new CmsUser)->where('id', $id)->first();
    }

    private function runAfterLoginHook(): void
    {
        $hook = \App\Http\Controllers\CBHook::class;

        if (class_exists($hook)) {
            app($hook)->afterLogin();
        }
    }
}
