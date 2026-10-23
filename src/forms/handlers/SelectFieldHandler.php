<?php

namespace crocodicstudio\crudbooster\forms\handlers;

use crocodicstudio\crudbooster\forms\FormContext;

class SelectFieldHandler extends BaseFieldHandler
{
    protected array $types = ['select'];

    protected function transform(array $field, mixed $value, array &$payload, FormContext $context): void
    {
        // An empty lookup (datatable) select saves 0 into a non-nullable column.
        if (! empty($field['datatable']) && $value == '') {
            $payload[$field['name']] = 0;
        }
    }
}
