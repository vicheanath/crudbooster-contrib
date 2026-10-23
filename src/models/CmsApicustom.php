<?php

namespace crocodicstudio\crudbooster\models;

use Illuminate\Database\Eloquent\Builder;

class CmsApicustom extends CrudboosterModel
{
    protected $table = 'cms_apicustom';

    public function scopeSorted(Builder $query): Builder
    {
        return $query->orderBy('nama', 'asc');
    }

    public function scopeForPermalink(Builder $query, ?string $permalink): Builder
    {
        return $query->where('permalink', $permalink);
    }

    /**
     * The serialized request parameter definitions. Objects are never revived.
     */
    public function parameterList(): array
    {
        return self::unserializeList($this->parameters);
    }

    /**
     * The serialized response field definitions. Objects are never revived.
     */
    public function responseList(): array
    {
        return self::unserializeList($this->responses);
    }

    private static function unserializeList(?string $value): array
    {
        $list = @unserialize((string) $value, ['allowed_classes' => false]);

        return is_array($list) ? $list : [];
    }
}
