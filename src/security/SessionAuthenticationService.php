<?php

namespace crocodicstudio\crudbooster\security;

use crocodicstudio\crudbooster\contracts\Security\AuthenticationServiceInterface;
use Illuminate\Contracts\Session\Session;

final class SessionAuthenticationService implements AuthenticationServiceInterface
{
    public function __construct(
        private readonly Session $session,
    ) {
    }

    public function currentUserId(): mixed
    {
        return $this->session->get('admin_id');
    }

    public function isSuperadmin(): bool
    {
        return (bool) $this->session->get('admin_is_superadmin');
    }
}
