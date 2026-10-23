<?php

namespace crocodicstudio\crudbooster\modern;

use crocodicstudio\crudbooster\compatibility\CrudConfig;

/**
 * The fluent module definition a CrudController fills in setup().
 */
class CrudDefinition
{
    protected ?string $table = null;

    protected ?string $titleField = null;

    protected int $limit = 20;

    protected array $orderby = [];

    protected bool $globalPrivilege = false;

    protected array $columns = [];

    protected array $fields = [];

    protected array $buttons = [];

    protected array $properties = [];

    public function table(string $table): static
    {
        $this->table = $table;

        return $this;
    }

    /**
     * The column that names a row in titles, logs and lookups.
     */
    public function titleField(string $column): static
    {
        $this->titleField = $column;

        return $this;
    }

    public function limit(int $rowsPerPage): static
    {
        $this->limit = $rowsPerPage;

        return $this;
    }

    public function orderBy(string $column, string $direction = 'asc'): static
    {
        $this->orderby[$column] = $direction;

        return $this;
    }

    /**
     * Lets every logged-in admin use the module, whatever their privileges.
     */
    public function globalPrivilege(bool $enabled = true): static
    {
        $this->globalPrivilege = $enabled;

        return $this;
    }

    /**
     * @param  Column|string  $label  a Column, or the column's label
     */
    public function column(Column|string $label, ?string $name = null, array $options = []): static
    {
        $column = $label instanceof Column ? $label->toArray() : ['label' => $label, 'name' => $name] + $options;
        $this->columns[] = $column;

        return $this;
    }

    public function field(Field|array $field): static
    {
        $this->fields[] = $field instanceof Field ? $field->toArray() : $field;

        return $this;
    }

    /**
     * @param  Field|array  ...$fields
     */
    public function fields(Field|array ...$fields): static
    {
        foreach ($fields as $field) {
            $this->field($field);
        }

        return $this;
    }

    /**
     * Hides buttons by name: add, edit, delete, detail, show, filter, export, import, addmore,
     * cancel, save, table_action, bulk_action.
     */
    public function withoutButtons(string ...$buttons): static
    {
        foreach ($buttons as $button) {
            $this->button($button, false);
        }

        return $this;
    }

    public function button(string $button, bool $enabled = true): static
    {
        if (! in_array($button, CrudConfig::BUTTONS, true)) {
            throw new \InvalidArgumentException("Unknown CRUDBooster button [$button].");
        }
        $this->buttons[$button] = $enabled;

        return $this;
    }

    /**
     * Sets any other CBController property, e.g. set('sub_module', [...]) or set('show_numbering', true).
     */
    public function set(string $property, mixed $value): static
    {
        if (! property_exists(\crocodicstudio\crudbooster\controllers\CBController::class, $property)) {
            throw new \InvalidArgumentException("Unknown CBController property [$property].");
        }
        $this->properties[$property] = $value;

        return $this;
    }

    public function toConfig(): CrudConfig
    {
        if (! $this->table) {
            throw new \LogicException('A CRUDBooster module needs a table: call $crud->table(...) in setup().');
        }

        return new CrudConfig(
            $this->table,
            $this->titleField,
            $this->columns,
            $this->fields,
            $this->limit,
            $this->orderby ?: null,
            $this->globalPrivilege,
            $this->buttons,
            $this->properties,
        );
    }
}
