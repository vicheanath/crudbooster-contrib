<?php

namespace crocodicstudio\crudbooster\compatibility;

use crocodicstudio\crudbooster\controllers\CBController;

/**
 * A module's configuration as one immutable value: what classic modules set as CBController
 * properties in cbInit(), and what CrudDefinition builds for CrudController modules.
 *
 * @deprecated 7.2 Only used by CBController modules; resources are configured by their own class. See
 *     docs/en/deprecations.md.
 */
final class CrudConfig
{
    public const BUTTONS = [
        'add', 'edit', 'delete', 'detail', 'show', 'filter', 'export', 'import',
        'addmore', 'cancel', 'save', 'table_action', 'bulk_action',
    ];

    /**
     * @param  array<string, bool>  $buttons  e.g. ['export' => false] for $button_export
     * @param  array<string, mixed>  $properties  any other CBController property, by name
     */
    public function __construct(
        public readonly ?string $table = null,
        public readonly ?string $titleField = null,
        public readonly array $columns = [],
        public readonly array $forms = [],
        public readonly int $limit = 20,
        public readonly array|string|null $orderby = null,
        public readonly bool $globalPrivilege = false,
        public readonly array $buttons = [],
        public readonly array $properties = [],
    ) {
    }

    public static function fromController(CBController $controller): self
    {
        $buttons = [];
        foreach (self::BUTTONS as $button) {
            $buttons[$button] = (bool) $controller->{'button_'.$button};
        }

        return new self(
            $controller->table,
            $controller->title_field,
            $controller->col,
            $controller->form,
            (int) $controller->limit,
            $controller->orderby,
            (bool) $controller->global_privilege,
            $buttons,
        );
    }

    /**
     * Sets the controller's properties, as a classic cbInit() does.
     */
    public function applyTo(CBController $controller): void
    {
        $controller->table = $this->table;
        $controller->title_field = $this->titleField;
        $controller->col = $this->columns;
        $controller->form = $this->forms;
        $controller->limit = $this->limit;
        $controller->orderby = $this->orderby;
        $controller->global_privilege = $this->globalPrivilege;

        foreach ($this->buttons as $button => $enabled) {
            $controller->{'button_'.$button} = $enabled;
        }

        foreach ($this->properties as $property => $value) {
            $controller->{$property} = $value;
        }
    }
}
