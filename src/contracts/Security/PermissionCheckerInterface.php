<?php

namespace crocodicstudio\crudbooster\contracts\Security;

interface PermissionCheckerInterface
{
    public function canCreate(): bool;

    public function canRead(): bool;

    public function canView(): bool;

    public function canUpdate(): bool;

    public function canDelete(): bool;
}
