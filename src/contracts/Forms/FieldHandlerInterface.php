<?php

namespace crocodicstudio\crudbooster\contracts\Forms;

use crocodicstudio\crudbooster\forms\FormContext;

/**
 * Turns the submitted value of one form field type into data to save.
 */
interface FieldHandlerInterface
{
    public function supports(string $type): bool;

    /**
     * Writes the field's value into the row about to be inserted or updated.
     */
    public function beforeSave(array $field, mixed $value, array &$payload, FormContext $context): void;

    /**
     * Saves data that needs the row's id, such as pivot rows and child rows.
     */
    public function afterSave(array $field, mixed $value, FormContext $context): void;
}
