<?php

namespace CrudBooster\Concerns;

use Closure;
use Illuminate\Support\Str;

trait HasLabel
{
    protected string|Closure|null $label = null;

    public function label(string|Closure|null $label): static
    {
        $this->label = $label;

        return $this;
    }

    /**
     * The given label, or one derived from the name: "category.name" becomes "Category Name".
     */
    public function getLabel(): string
    {
        return (string) ($this->evaluate($this->label) ?? Str::headline(str_replace('.', ' ', $this->getName())));
    }
}
