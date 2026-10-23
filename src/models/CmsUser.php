<?php

namespace crocodicstudio\crudbooster\models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CmsUser extends CrudboosterModel
{
    protected $table = 'cms_users';

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->setTable(config('crudbooster.USER_TABLE', 'cms_users'));
    }

    public function privilege(): BelongsTo
    {
        return $this->belongsTo(CmsPrivilege::class, 'id_cms_privileges');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(CmsLog::class, 'id_cms_users');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(CmsNotification::class, 'id_cms_users');
    }
}
