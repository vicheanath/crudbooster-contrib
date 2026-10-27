<?php

namespace CrudBooster\Forms\Fields;

use Illuminate\Http\Request;

/**
 * An on/off switch for a boolean attribute. Unchecked saves false.
 */
class Toggle extends Field
{
    protected string $view = 'cb::fields.toggle';

    protected ?string $component = 'Toggle';

    protected mixed $default = false;

    public function getComponentProps(): array
    {
        return ['value' => filter_var($this->getState(), FILTER_VALIDATE_BOOLEAN)] + parent::getComponentProps();
    }

    public function getValueFromRequest(Request $request): mixed
    {
        return $request->boolean($this->name);
    }

    public function getValidationRules(): array
    {
        return [$this->name => array_merge(['nullable', 'boolean'], $this->rules)];
    }
}
