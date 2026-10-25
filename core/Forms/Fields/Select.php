<?php

namespace CrudBooster\Forms\Fields;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * A choice from fixed options or from a relationship:
 *
 *     Select::make('status')->options(['draft' => 'Draft', 'published' => 'Published'])
 *     Select::make('category_id')->relationship('category', 'name')
 *     Select::make('tags')->relationship('tags', 'name')->multiple()   // a BelongsToMany, synced on save
 *
 * Submitted values are validated against the options (or must exist in the related table).
 */
class Select extends Field
{
    protected string $view = 'cb::fields.select';

    protected array|Closure $options = [];

    protected ?string $relationship = null;

    protected ?string $titleAttribute = null;

    protected ?Closure $modifyRelationshipQueryUsing = null;

    protected bool $multiple = false;

    public function options(array|Closure $options): static
    {
        $this->options = $options;

        return $this;
    }

    /**
     * Options from the related model; a closure may constrain them: fn (Builder $query) => ...
     */
    public function relationship(string $name, string $titleAttribute, ?Closure $modifyQueryUsing = null): static
    {
        $this->relationship = $name;
        $this->titleAttribute = $titleAttribute;
        $this->modifyRelationshipQueryUsing = $modifyQueryUsing;

        return $this;
    }

    public function multiple(bool $multiple = true): static
    {
        $this->multiple = $multiple;

        return $this;
    }

    public function isMultiple(): bool
    {
        return $this->multiple || $this->isManyToMany();
    }

    protected function getRelation(): ?Relation
    {
        return $this->relationship && $this->model ? $this->model->{$this->relationship}() : null;
    }

    protected function isManyToMany(): bool
    {
        return $this->getRelation() instanceof BelongsToMany;
    }

    protected function getRelationshipQuery(): Builder
    {
        $query = $this->getRelation()->getRelated()->newQuery();
        if ($this->modifyRelationshipQueryUsing) {
            $this->evaluate($this->modifyRelationshipQueryUsing, ['query' => $query]);
        }

        return $query;
    }

    public function getOptions(): array
    {
        if ($this->relationship) {
            $related = $this->getRelation()->getRelated();

            return $this->getRelationshipQuery()->orderBy($this->titleAttribute)
                ->pluck($this->titleAttribute, $related->getKeyName())->all();
        }

        $options = $this->evaluate($this->options);

        // A list of values is its own labels.
        return array_is_list($options) ? array_combine($options, $options) : $options;
    }

    protected function getRecordState(Model $record): mixed
    {
        if ($this->isManyToMany()) {
            return $record->{$this->relationship}()->get()->modelKeys();
        }

        return parent::getRecordState($record);
    }

    public function getValueFromRequest(Request $request): mixed
    {
        $value = parent::getValueFromRequest($request);

        if ($this->isMultiple()) {
            return array_values(array_filter((array) $value, fn ($item) => $item !== null && $item !== ''));
        }

        return $value === '' ? null : $value;
    }

    public function getValidationRules(): array
    {
        $required = $this->isRequired() ? 'required' : 'nullable';
        $allowed = $this->getAllowedValuesRule();

        if ($this->isMultiple()) {
            return [
                $this->name => array_merge([$required, 'array'], $this->rules),
                $this->name.'.*' => [$allowed],
            ];
        }

        return [$this->name => array_merge([$required, $allowed], $this->rules)];
    }

    protected function getAllowedValuesRule(): mixed
    {
        if ($this->relationship) {
            $related = $this->getRelation()->getRelated();
            if ($this->modifyRelationshipQueryUsing) {
                return Rule::in($this->getRelationshipQuery()->pluck($related->getKeyName())->map(fn ($key) => (string) $key)->all());
            }

            return Rule::exists($related::class, $related->getKeyName());
        }

        return Rule::in(array_map('strval', array_keys($this->getOptions())));
    }

    public function savesToAttribute(): bool
    {
        return ! $this->isManyToMany();
    }

    public function saveRelationships(Model $record, mixed $value): void
    {
        if ($this->isManyToMany() && $this->isDehydrated()) {
            $record->{$this->relationship}()->sync((array) $value);
        }
    }
}
