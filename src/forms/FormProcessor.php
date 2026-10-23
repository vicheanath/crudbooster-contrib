<?php

namespace crocodicstudio\crudbooster\forms;

use crocodicstudio\crudbooster\contracts\Forms\FormProcessorInterface;

/**
 * Runs each form field through the handler registered for its type.
 */
class FormProcessor implements FormProcessorInterface
{
    public function __construct(private readonly FieldHandlerRegistry $handlers)
    {
    }

    public function assign(array $fields, array &$payload, FormContext $context): void
    {
        $hidden = $this->hiddenFields();

        foreach ($fields as $field) {
            $name = $field['name'] ?? null;

            if (! $name || ! empty($field['exception']) || $name == 'hide_form' || in_array($name, $hidden)) {
                continue;
            }

            $this->handlers->resolve($field['type'] ?? null)->beforeSave($field, request($name), $payload, $context);
        }
    }

    public function afterSave(array $fields, FormContext $context): void
    {
        foreach ($fields as $field) {
            $name = $field['name'] ?? null;

            if (! $name) {
                continue;
            }

            $this->handlers->resolve($field['type'] ?? null)->afterSave($field, request($name), $context);
        }
    }

    /**
     * Fields the form left out (the serialized "hide_form" input).
     */
    private function hiddenFields(): array
    {
        $hidden = request('hide_form');
        // Only plain values: objects in user input must never be unserialized.
        $hidden = is_string($hidden) ? @unserialize($hidden, ['allowed_classes' => false]) : null;

        return is_array($hidden) ? $hidden : [];
    }
}
