<?php

namespace CrudBooster\Forms\Fields;

class Radio extends Select
{
    protected string $view = 'cb::fields.radio';

    protected ?string $component = 'Radio';

    public function multiple(bool $multiple = true): static
    {
        throw new \LogicException('A Radio field picks one value; use CheckboxList for several.');
    }
}
