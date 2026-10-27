<?php

namespace crocodicstudio\crudbooster\forms\handlers;

use crocodicstudio\crudbooster\forms\FormContext;

/**
 * A "header" field only divides the form into sections. It has no column, so it never adds to the payload,
 * even when the module gives it a name.
 *
 * @deprecated 7.2 Use CrudBooster\Forms\Section. See docs/en/deprecations.md.
 */
class HeaderFieldHandler extends BaseFieldHandler
{
    protected array $types = ['header'];

    public function beforeSave(array $field, mixed $value, array &$payload, FormContext $context): void
    {
    }
}
