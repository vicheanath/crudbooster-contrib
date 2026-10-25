<?php

namespace CrudBooster\Forms\Fields;

use Closure;

/**
 * Read-only text in the form, never saved:
 *
 *     Placeholder::make('created_at')->content(fn ($record) => $record?->created_at?->diffForHumans())
 */
class Placeholder extends Field
{
    protected string $view = 'cb::fields.placeholder';

    protected bool $dehydrated = false;

    protected mixed $content = null;

    public function content(mixed $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getContent(): mixed
    {
        return $this->content instanceof Closure ? $this->evaluate($this->content) : $this->content;
    }

    public function getValidationRules(): array
    {
        return [];
    }
}
