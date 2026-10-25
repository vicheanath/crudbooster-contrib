<?php

namespace CrudBooster\Forms;

use CrudBooster\Concerns\EvaluatesClosures;
use CrudBooster\Forms\Fields\Field;

/**
 * A titled card grouping fields:
 *
 *     Section::make('Pricing')->description('Shown in the shop')->schema([...])
 */
class Section
{
    use EvaluatesClosures;

    /** @var array<Field|Section> */
    protected array $schema = [];

    protected ?string $description = null;

    final public function __construct(protected ?string $heading = null)
    {
    }

    public static function make(?string $heading = null): static
    {
        return new static($heading);
    }

    public function schema(array $components): static
    {
        $this->schema = $components;

        return $this;
    }

    public function description(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getHeading(): ?string
    {
        return $this->heading;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @return array<Field|Section>
     */
    public function getSchema(): array
    {
        return $this->schema;
    }

    public function render(): string
    {
        return view('cb::forms.section', ['section' => $this])->render();
    }
}
