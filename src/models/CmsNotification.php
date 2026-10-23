<?php

namespace crocodicstudio\crudbooster\models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CmsNotification extends CrudboosterModel
{
    /** Notifications addressed to this user id are shown to every admin. */
    public const BROADCAST = 0;

    protected $table = 'cms_notifications';

    protected $casts = ['is_read' => 'boolean'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(CmsUser::class, 'id_cms_users');
    }

    /**
     * The user's own notifications and the broadcast ones.
     */
    public function scopeVisibleTo(Builder $query, $userId): Builder
    {
        return $query->where(function (Builder $owner) use ($userId) {
            $owner->where('id_cms_users', self::BROADCAST)->orWhere('id_cms_users', $userId);
        });
    }

    public function scopeUnread(Builder $query): Builder
    {
        return $query->where('is_read', 0);
    }
}
