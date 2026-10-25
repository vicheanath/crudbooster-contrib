<?php

namespace CrudBooster\Forms\Fields;

/**
 * A field rendered by your own Blade view, which receives $field, $state and $record:
 *
 *     ViewField::make('location')->view('admin.fields.map-picker')
 *
 * It is saved like any other input named after the field; call dehydrated(false) for display only.
 */
class ViewField extends Field
{
    protected string $view = 'cb::fields.placeholder';

    public function view(string $view): static
    {
        $this->view = $view;

        return $this;
    }

    public function render(): string
    {
        return view($this->view, ['field' => $this, 'state' => $this->getState(), 'record' => $this->record])->render();
    }
}
