<?php

namespace crocodicstudio\crudbooster\security;

use crocodicstudio\crudbooster\models\CmsPrivilege;
use crocodicstudio\crudbooster\models\CmsUser;
use Illuminate\Support\Collection;

final class AdminAuthenticationContext
{
    public function __construct(
        private readonly CmsUser $user,
        private readonly CmsPrivilege $privilege,
        private readonly Collection $roles,
    ) {
    }

    public function user(): CmsUser
    {
        return $this->user;
    }

    public function privilege(): CmsPrivilege
    {
        return $this->privilege;
    }

    public function sessionPayload(): array
    {
        return [
            'admin_id' => $this->user->id,
            'admin_is_superadmin' => $this->privilege->is_superadmin,
            'admin_name' => $this->user->name,
            'admin_photo' => $this->user->photo
                ? asset($this->user->photo)
                : asset('vendor/crudbooster/avatar.jpg'),
            'admin_privileges_roles' => $this->roles,
            'admin_privileges' => $this->user->id_cms_privileges,
            'admin_privileges_name' => $this->privilege->name,
            'admin_lock' => 0,
            'theme_color' => $this->privilege->theme_color,
            'appname' => get_setting('appname'),
        ];
    }
}
