<?php

namespace CrudBooster\Concerns;

trait HasName
{
    protected string $name;

    final public function __construct(string $name)
    {
        $this->name = $name;
        $this->setUp();
    }

    public static function make(string $name): static
    {
        return new static($name);
    }

    /**
     * Sets a subclass's defaults; called by the constructor.
     */
    protected function setUp(): void
    {
    }

    public function getName(): string
    {
        return $this->name;
    }
}
