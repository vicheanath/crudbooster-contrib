<?php

namespace crocodicstudio\crudbooster\contracts\Forms;

use crocodicstudio\crudbooster\forms\FormContext;

/**
 * @deprecated 7.2 Only used by CBController modules; resource fields add their own rules (required(),
 *     rules(), unique()). See docs/en/deprecations.md.
 */
interface FormValidatorInterface
{
    /**
     * The Laravel validation rules for the submitted fields.
     */
    public function rules(array $fields, array $input, FormContext $context): array;

    /**
     * Validates the request and throws an HttpResponseException with CRUDBooster's
     * warning message when it fails.
     */
    public function validate(array $fields, FormContext $context): void;
}
