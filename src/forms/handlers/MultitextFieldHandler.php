<?php

namespace crocodicstudio\crudbooster\forms\handlers;

use crocodicstudio\crudbooster\forms\FormContext;

/**
 * The submitted list of texts is saved "|"-separated.
 *
 * @deprecated 7.2 Use a CheckboxList, a JSON-cast attribute with a custom field, or a child resource. See
 *     docs/en/deprecations.md.
 */
class MultitextFieldHandler extends BaseFieldHandler
{
    protected array $types = ['multitext'];

    protected function transform(array $field, mixed $value, array &$payload, FormContext $context): void
    {
        $name = $field['name'];
        $items = $payload[$name] ?? '';

        if (is_array($items)) {
            $payload[$name] = implode('|', $items);
        } elseif (! $items) {
            $payload[$name] = '';
        }
    }
}
