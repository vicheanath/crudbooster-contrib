<?php

namespace crocodicstudio\crudbooster\contracts\Security;

/**
 * Who is the current admin user.
 */
interface AuthenticationServiceInterface
{
    public function currentUserId(): mixed;

    public function isSuperadmin(): bool;
}
