<?php

namespace CrudBooster\Forms\Fields;

use Closure;
use CrudBooster\Concerns\CanBeHidden;
use CrudBooster\Concerns\EvaluatesClosures;
use CrudBooster\Concerns\HasLabel;
use CrudBooster\Concerns\HasName;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * One input of a resource form. The name is the model attribute it reads and writes.
 *
 * The life of a value: getState() fills the input (old input, then the record, then the
 * default); on submit getValueFromRequest() reads it, getValidationRules() validate it and
 * dehydrate() turns it into what is saved. A custom field type extends this class, sets $view
 * and overrides the steps it needs.
 */
abstract class Field
{
    use CanBeHidden;
    use EvaluatesClosures;
    use HasLabel;
    use HasName;

    protected string $view;

    /** The React component that renders the input in the Tailwind theme; null for none. */
    protected ?string $component = null;

    protected array $rules = [];

    protected bool|Closure $required = false;

    protected mixed $default = null;

    protected ?string $helperText = null;

    protected ?string $placeholder = null;

    protected bool|Closure $disabled = false;

    protected bool $dehydrated = true;

    protected ?Closure $dehydrateStateUsing = null;

    protected ?Closure $formatStateUsing = null;

    protected int $columnSpan = 12;

    protected ?array $operations = null;

    protected array $extraAttributes = [];

    protected bool|array $unique = false;

    protected ?Model $model = null;

    protected ?Model $record = null;

    protected string $operation = 'create';

    /**
     * Sets the form the field is in. Called by Form before the field is rendered or saved.
     */
    public function bind(Model $model, ?Model $record, string $operation): static
    {
        $this->model = $model;
        $this->record = $record;
        $this->operation = $operation;

        return $this;
    }

    protected function defaultEvaluationParameters(): array
    {
        return ['record' => $this->record, 'operation' => $this->operation, 'model' => $this->model];
    }

    /**
     * Laravel validation rules, as a string or an array.
     */
    public function rules(string|array $rules): static
    {
        $this->rules = array_merge($this->rules, is_string($rules) ? explode('|', $rules) : $rules);

        return $this;
    }

    public function required(bool|Closure $required = true): static
    {
        $this->required = $required;

        return $this;
    }

    /**
     * The value must be unique in the model's table; the record being edited is ignored.
     */
    public function unique(?string $table = null, ?string $column = null): static
    {
        $this->unique = [$table, $column];

        return $this;
    }

    public function default(mixed $value): static
    {
        $this->default = $value;

        return $this;
    }

    public function helperText(?string $text): static
    {
        $this->helperText = $text;

        return $this;
    }

    public function placeholder(?string $placeholder): static
    {
        $this->placeholder = $placeholder;

        return $this;
    }

    public function disabled(bool|Closure $disabled = true): static
    {
        $this->disabled = $disabled;

        return $this;
    }

    /**
     * false shows the field but never saves it: the request value is ignored.
     */
    public function dehydrated(bool $dehydrated = true): static
    {
        $this->dehydrated = $dehydrated;

        return $this;
    }

    /**
     * Transforms the submitted value before it is saved: fn ($state) => Str::slug($state)
     */
    public function dehydrateStateUsing(Closure $callback): static
    {
        $this->dehydrateStateUsing = $callback;

        return $this;
    }

    /**
     * Transforms the stored value before it fills the input: fn ($state, $record) => ...
     */
    public function formatStateUsing(Closure $callback): static
    {
        $this->formatStateUsing = $callback;

        return $this;
    }

    /**
     * The width in a 12-column grid: columnSpan(6) puts two fields on a row.
     */
    public function columnSpan(int $span): static
    {
        $this->columnSpan = max(1, min(12, $span));

        return $this;
    }

    /**
     * Shows the field only when creating, or only when editing: ->onlyOn('create').
     */
    public function onlyOn(string ...$operations): static
    {
        $this->operations = $operations;

        return $this;
    }

    /**
     * HTML attributes added to the input: ['autocomplete' => 'off', 'data-mask' => '...'].
     */
    public function extraAttributes(array $attributes): static
    {
        $this->extraAttributes = array_merge($this->extraAttributes, $attributes);

        return $this;
    }

    public function isVisible(): bool
    {
        if ($this->operations !== null && ! in_array($this->operation, $this->operations, true)) {
            return false;
        }

        return $this->evaluateVisibility();
    }

    public function isRequired(): bool
    {
        return (bool) $this->evaluate($this->required);
    }

    public function isDisabled(): bool
    {
        return (bool) $this->evaluate($this->disabled);
    }

    public function isDehydrated(): bool
    {
        return $this->dehydrated && ! $this->isDisabled();
    }

    public function getHelperText(): ?string
    {
        return $this->helperText;
    }

    public function getPlaceholder(): ?string
    {
        return $this->placeholder;
    }

    public function getColumnSpan(): int
    {
        return $this->columnSpan;
    }

    public function getOperation(): string
    {
        return $this->operation;
    }

    public function getRecord(): ?Model
    {
        return $this->record;
    }

    public function getExtraAttributes(): array
    {
        return $this->extraAttributes;
    }

    /**
     * The input's id, also used by the label.
     */
    public function getId(): string
    {
        return 'field-'.str_replace(['.', '[', ']'], '-', $this->name);
    }

    /**
     * The value the input shows.
     */
    public function getState(): mixed
    {
        $old = session()->getOldInput();
        if ($old && array_key_exists($this->name, $old)) {
            return $old[$this->name];
        }

        if ($this->record) {
            $state = $this->getRecordState($this->record);

            return $this->formatStateUsing
                ? $this->evaluate($this->formatStateUsing, ['state' => $state])
                : $state;
        }

        return $this->evaluate($this->default);
    }

    protected function getRecordState(Model $record): mixed
    {
        return $record->getAttribute($this->name);
    }

    public function getValueFromRequest(Request $request): mixed
    {
        return $request->input($this->name);
    }

    /**
     * Rules keyed by input name; a field may validate several keys (e.g. "tags" and "tags.*").
     */
    public function getValidationRules(): array
    {
        $rules = [$this->isRequired() ? 'required' : 'nullable'];

        if ($this->unique !== false) {
            [$table, $column] = $this->unique;
            $rule = Rule::unique($table ?? $this->model->getTable(), $column ?? $this->name);
            if ($this->record) {
                $rule->ignore($this->record->getKey(), $this->record->getKeyName());
            }
            $rules[] = $rule;
        }

        return [$this->name => array_merge($rules, $this->getTypeRules(), $this->rules)];
    }

    /**
     * Rules that come with the field type (an email input validates emails, ...).
     */
    protected function getTypeRules(): array
    {
        return [];
    }

    /**
     * Whether the submitted value is written at all; e.g. an empty password on edit is not.
     */
    public function shouldSave(mixed $value): bool
    {
        return true;
    }

    /**
     * The value saved to the attribute.
     */
    public function dehydrate(mixed $value): mixed
    {
        if ($this->dehydrateStateUsing) {
            return $this->evaluate($this->dehydrateStateUsing, ['state' => $value]);
        }

        return $value;
    }

    /**
     * false for fields saved through a relationship instead of an attribute.
     */
    public function savesToAttribute(): bool
    {
        return true;
    }

    /**
     * Saves what the field keeps outside the record's attributes, once the record exists.
     */
    public function saveRelationships(Model $record, mixed $value): void
    {
    }

    public function getComponent(): ?string
    {
        return $this->component;
    }

    /**
     * What the React component receives. Values are what the input shows (getState()); the
     * component submits them under getName(), like a plain input.
     */
    public function getComponentProps(): array
    {
        $state = $this->getState();

        return [
            'name' => $this->name,
            'id' => $this->getId(),
            'label' => $this->getLabel(),
            'value' => is_scalar($state) ? (string) $state : ($state === null ? '' : $state),
            'required' => $this->isRequired(),
            'disabled' => $this->isDisabled(),
            'placeholder' => $this->getPlaceholder(),
            'attributes' => (object) $this->getExtraAttributes(),
        ];
    }

    public function render(): string
    {
        return view($this->view, ['field' => $this, 'state' => $this->getState()])->render();
    }
}
