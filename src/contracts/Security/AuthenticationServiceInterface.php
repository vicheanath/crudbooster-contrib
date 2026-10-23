<?php

namespace crocodicstudio\crudbooster\contracts\Security;

interface AuthenticationServiceInterface
{
    public function currentUserId(): mixed;
}
