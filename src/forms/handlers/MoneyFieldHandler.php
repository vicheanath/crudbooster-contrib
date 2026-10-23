<?php

namespace crocodicstudio\crudbooster\forms\handlers;

class MoneyFieldHandler extends BaseFieldHandler
{
    protected array $types = ['money'];

    /**
     * The number in a formatted money input. Without cents (the default) everything but digits and "-" is
     * dropped, as in 5.x. With cents ('decimals' or priceformat_parameters.centsLimit) the field's prefix,
     * suffix and thousands separator are dropped and its cents separator becomes ".":
     * "$ 2,345.67" is saved as 2345.67, not 234567.
     */
    public static function clean(mixed $value, array $field = []): string
    {
        $value = (string) $value;
        $format = (array) ($field['priceformat_parameters'] ?? []);
        $centsLimit = (int) ($format['centsLimit'] ?? $field['decimals'] ?? 0);

        if ($centsLimit <= 0) {
            return preg_replace('/[^\d-]+/', '', $value);
        }

        $affixes = array_filter([(string) ($format['prefix'] ?? ''), (string) ($format['suffix'] ?? '')], 'strlen');
        $value = str_replace($affixes, '', $value);
        $centsSeparator = (string) ($format['centsSeparator'] ?? '.');
        $value = preg_replace('/[^\d\-'.preg_quote($centsSeparator, '/').']+/', '', $value);

        return str_replace($centsSeparator, '.', $value);
    }

    protected function normalize(array $field, mixed $value): mixed
    {
        return static::clean($value, $field);
    }
}
