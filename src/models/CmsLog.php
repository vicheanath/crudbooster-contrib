<?php

namespace crocodicstudio\crudbooster\models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CmsLog extends CrudboosterModel
{
    protected $table = 'cms_logs';

    public function user(): BelongsTo
    {
        return $this->belongsTo(CmsUser::class, 'id_cms_users');
    }
}
