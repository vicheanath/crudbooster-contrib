<?php

namespace crocodicstudio\crudbooster\models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CmsNotification extends CrudboosterModel
{
    protected $table = 'cms_notifications';

    protected $casts = ['is_read' => 'boolean'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(CmsUser::class, 'id_cms_users');
    }
}
