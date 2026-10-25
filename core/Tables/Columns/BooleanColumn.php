<?php

namespace CrudBooster\Tables\Columns;

class BooleanColumn extends Column
{
    protected string $view = 'cb::columns.boolean';

    protected function setUp(): void
    {
        $this->alignCenter();
    }
}
