<?php

namespace crocodicstudio\crudbooster\models;

use Illuminate\Database\Eloquent\Builder;

class CmsEmailQueue extends CrudboosterModel
{
    protected $table = 'cms_email_queues';

    /**
     * Emails whose send_at has passed.
     */
    public function scopeDue(Builder $query, ?string $now = null): Builder
    {
        return $query->where('send_at', '<=', $now ?: now()->format('Y-m-d H:i:s'));
    }
}
