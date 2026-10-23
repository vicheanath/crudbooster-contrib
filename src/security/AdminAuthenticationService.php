<?php

namespace crocodicstudio\crudbooster\security;

use crocodicstudio\crudbooster\contracts\Audit\AuditLoggerInterface;
use crocodicstudio\crudbooster\contracts\Mail\TemplateMailerInterface;
use crocodicstudio\crudbooster\contracts\Security\AuthenticationServiceInterface;
use crocodicstudio\crudbooster\contracts\Security\PrivilegeRolesInterface;
use crocodicstudio\crudbooster\models\CmsPrivilege;
use crocodicstudio\crudbooster\models\CmsUser;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Contracts\Session\Session as SessionContract;
use Illuminate\Http\Request;

class AdminAuthenticationService implements AuthenticationServiceInterface
{
    public function __construct(
        private readonly CmsUser $users,
        private readonly CmsPrivilege $privileges,
        private readonly PrivilegeRolesInterface $privilegeRoles,
        private readonly Hasher $hasher,
        private readonly SessionContract $session,
        private readonly Request $request,
        private readonly AuditLoggerInterface $audit,
        private readonly TemplateMailerInterface $mailer,
    ) {
    }

    public function lockScreen(): void
    {
        $this->session->put('admin_lock', 1);
    }

    public function currentUserId(): mixed
    {
        return $this->session->get('admin_id');
    }

    public function isSuperadmin(): bool
    {
        return (bool) $this->session->get('admin_is_superadmin');
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

        $this->audit->log(cbLang('log_login', [
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
            $this->audit->log(cbLang('log_logout', ['email' => $user->email]));
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
        $this->mailer->send([
            'to' => $user->email,
            'data' => $user,
            'template' => 'forgot_password_backend',
        ]);

        $this->audit->log(cbLang('log_forgot', [
            'email' => $email,
            'ip' => $this->request->server('REMOTE_ADDR'),
        ]));
    }

    private function currentUser(): ?CmsUser
    {
        $id = $this->currentUserId();

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

        $roles = $this->privilegeRoles->forPrivilege($user->id_cms_privileges);

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
