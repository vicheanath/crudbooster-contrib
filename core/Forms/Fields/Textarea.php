<?php

namespace CrudBooster\Forms\Fields;

class Textarea extends Field
{
    protected string $view = 'cb::fields.textarea';

    protected int $rows = 4;

    public function rows(int $rows): static
    {
        $this->rows = $rows;

        return $this;
    }

    public function getRows(): int
    {
        return $this->rows;
    }
}
