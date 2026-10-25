<?php

namespace CrudBooster\Forms\Fields;

/**
 * Several values as checkboxes. Saved as an array (cast the attribute to "array"), or synced
 * when it is a BelongsToMany relationship().
 */
class CheckboxList extends Select
{
    protected string $view = 'cb::fields.checkbox-list';

    protected ?string $component = 'CheckboxList';

    protected bool $multiple = true;
}
