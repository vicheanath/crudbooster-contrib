<?php

namespace crocodicstudio\crudbooster\contracts\Forms;

use crocodicstudio\crudbooster\forms\FormContext;

/**
 * @deprecated 7.2 Only used by CBController modules; resource fields save themselves (dehydrateStateUsing()).
 *     See docs/en/deprecations.md.
 */
interface FormProcessorInterface
{
    /**
     * Builds the row to save from the request, adding to $payload.
     */
    public function assign(array $fields, array &$payload, FormContext $context): void;

    /**
     * Runs every field's after-save step once the row exists.
     */
    public function afterSave(array $fields, FormContext $context): void;
}
