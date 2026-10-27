<?php

namespace crocodicstudio\crudbooster\forms\handlers;

use crocodicstudio\crudbooster\contracts\Forms\FieldHandlerInterface;
use crocodicstudio\crudbooster\contracts\Schema\SchemaInspectorInterface;
use crocodicstudio\crudbooster\forms\FormContext;
use Illuminate\Support\Facades\Hash;

/**
 * The 5.x assignment every field type shares: a submitted value is saved as is; an empty one
 * is left out when its column is nullable and saved as '' otherwise; fields named like a
 * password (PASSWORD_FIELDS_CANDIDATE) are hashed, or left unchanged when empty.
 * Subclasses adjust the value in normalize() and transform().
 *
 * @deprecated 7.2 Only used by CBController modules. Extend CrudBooster\Forms\Fields\Field instead. See
 *     docs/en/deprecations.md.
 */
abstract class BaseFieldHandler implements FieldHandlerInterface
{
    /**
     * Field types this handler claims when added to the registry with add().
     */
    protected array $types = [];

    protected SchemaInspectorInterface $schema;

    public function __construct(?SchemaInspectorInterface $schema = null)
    {
        $this->schema = $schema ?? app(SchemaInspectorInterface::class);
    }

    public function supports(string $type): bool
    {
        return in_array($type, $this->types, true);
    }

    public function beforeSave(array $field, mixed $value, array &$payload, FormContext $context): void
    {
        $name = $field['name'];
        $value = $this->normalize($field, $value);

        if ($value != '') {
            $payload[$name] = $value;
        } elseif ($this->skipsEmptyNullableColumn() && $this->schema->isNullable($context->table, $name)) {
            return;
        } else {
            $payload[$name] = '';
        }

        $this->hashPassword($name, $payload);
        $this->transform($field, $value, $payload, $context);
    }

    public function afterSave(array $field, mixed $value, FormContext $context): void
    {
    }

    protected function normalize(array $field, mixed $value): mixed
    {
        return $value;
    }

    protected function transform(array $field, mixed $value, array &$payload, FormContext $context): void
    {
    }

    protected function skipsEmptyNullableColumn(): bool
    {
        return true;
    }

    protected function hashPassword(string $name, array &$payload): void
    {
        if (! in_array($name, explode(',', (string) config('crudbooster.PASSWORD_FIELDS_CANDIDATE')))) {
            return;
        }

        if (! empty($payload[$name])) {
            $payload[$name] = Hash::make($payload[$name]);
        } else {
            unset($payload[$name]);
        }
    }

    /**
     * The table part of "database.table".
     */
    protected function tableName(string $table): string
    {
        $parts = explode('.', $table);

        return end($parts);
    }

    /**
     * The column of $childTable that points to $parentTable: id_{parent} if it exists, else {parent}_id.
     */
    protected function foreignKey(string $parentTable, string $childTable): string
    {
        $parentTable = $this->tableName($parentTable);

        return $this->schema->hasColumn($this->tableName($childTable), 'id_'.$parentTable)
            ? 'id_'.$parentTable
            : $parentTable.'_id';
    }
}
