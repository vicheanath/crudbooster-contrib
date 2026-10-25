<?php

namespace CrudBooster\Forms\Fields;

use Illuminate\Http\Request;

/**
 * An on/off switch for a boolean attribute. Unchecked saves false.
 */
class Toggle extends Field
{
    protected string $view = 'cb::fields.toggle';

    protected mixed $default = false;

    public function getValueFromRequest(Request $request): mixed
    {
        return $request->boolean($this->name);
    }

    public function getValidationRules(): array
    {
        return [$this->name => array_merge(['nullable', 'boolean'], $this->rules)];
    }
}
