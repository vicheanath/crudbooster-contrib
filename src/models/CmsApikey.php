<?php

namespace crocodicstudio\crudbooster\models;

use Illuminate\Database\Eloquent\Builder;

class CmsApikey extends CrudboosterModel
{
    public const ACTIVE = 'active';

    public const INACTIVE = 'non active';

    protected $table = 'cms_apikey';

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::ACTIVE);
    }

    public function scopeWithSecret(Builder $query, ?string $secret): Builder
    {
        return $query->where('screetkey', $secret);
    }
}
