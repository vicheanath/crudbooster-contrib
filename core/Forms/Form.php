<?php

namespace CrudBooster\Forms;

use CrudBooster\Forms\Fields\Field;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

/**
 * The create and edit form of a resource, filled in by Resource::form():
 *
 *     return $form->schema([
 *         TextInput::make('name')->required()->maxLength(255),
 *         Select::make('category_id')->relationship('category', 'name'),
 *     ]);
 *
 * Only the fields listed here are read from the request and saved, so the form is the
 * attribute whitelist; the model's $fillable is not consulted.
 */
class Form
{
    /** @var array<Field|Section> */
    protected array $schema = [];

    protected ?Model $model = null;

    protected ?Model $record = null;

    protected string $operation = 'create';

    public function schema(array $components): static
    {
        $this->schema = $components;

        return $this;
    }

    public function bind(Model $model, ?Model $record, string $operation): static
    {
        $this->model = $model;
        $this->record = $record;
        $this->operation = $operation;

        foreach ($this->getAllFields() as $field) {
            $field->bind($model, $record, $operation);
        }

        return $this;
    }

    public function getOperation(): string
    {
        return $this->operation;
    }

    public function getRecord(): ?Model
    {
        return $this->record;
    }

    /**
     * The top-level fields and sections.
     *
     * @return array<Field|Section>
     */
    public function getSchema(): array
    {
        return $this->schema;
    }

    /**
     * Every visible field, sections flattened.
     *
     * @return Field[]
     */
    public function getFields(): array
    {
        return array_values(array_filter($this->getAllFields(), fn (Field $field) => $field->isVisible()));
    }

    /**
     * @return Field[]
     */
    protected function getAllFields(?array $components = null): array
    {
        $fields = [];
        foreach ($components ?? $this->schema as $component) {
            if ($component instanceof Section) {
                array_push($fields, ...$this->getAllFields($component->getSchema()));
            } else {
                $fields[] = $component;
            }
        }

        return $fields;
    }

    public function hasFileUploads(): bool
    {
        foreach ($this->getFields() as $field) {
            if ($field instanceof Fields\FileUpload) {
                return true;
            }
        }

        return false;
    }

    public function getValidationRules(): array
    {
        $rules = [];
        foreach ($this->getFields() as $field) {
            if ($field->isDehydrated()) {
                $rules += $field->getValidationRules();
            }
        }

        return $rules;
    }

    /**
     * Field labels for validation messages ("The Category field is required.").
     */
    public function getValidationAttributes(): array
    {
        $attributes = [];
        foreach ($this->getFields() as $field) {
            $attributes[$field->getName()] = $field->getLabel();
            $attributes[$field->getName().'.*'] = $field->getLabel();
        }

        return $attributes;
    }

    /**
     * Reads the submitted values: the attributes to save, and the values the fields save
     * through relationships once the record exists (keyed by field name).
     *
     * @return array{0: array<string, mixed>, 1: array<string, mixed>}
     */
    public function getSubmittedState(Request $request): array
    {
        $attributes = [];
        $relationships = [];

        foreach ($this->getFields() as $field) {
            if (! $field->isDehydrated()) {
                continue;
            }

            $value = $field->getValueFromRequest($request);

            if (! $field->savesToAttribute()) {
                $relationships[$field->getName()] = $value;
            } elseif ($field->shouldSave($value)) {
                $attributes[$field->getName()] = $field->dehydrate($value);
            }
        }

        return [$attributes, $relationships];
    }

    public function saveRelationships(Model $record, array $values): void
    {
        foreach ($this->getFields() as $field) {
            if (array_key_exists($field->getName(), $values)) {
                $field->saveRelationships($record, $values[$field->getName()]);
            }
        }
    }
}
