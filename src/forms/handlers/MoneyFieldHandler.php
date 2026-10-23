<?php

namespace crocodicstudio\crudbooster\forms\handlers;

class MoneyFieldHandler extends BaseFieldHandler
{
    protected array $types = ['money'];

    /**
     * "1,250.00" is saved as 125000, as in 5.x: everything but digits and "-" is dropped.
     */
    public static function clean(mixed $value): string
    {
        return preg_replace('/[^\d-]+/', '', (string) $value);
    }

    protected function normalize(array $field, mixed $value): mixed
    {
        return static::clean($value);
    }
}
