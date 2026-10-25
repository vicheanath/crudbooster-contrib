<?php

namespace CrudBooster\Forms\Fields;

use Illuminate\Support\Facades\Hash;

class TextInput extends Field
{
    protected string $view = 'cb::fields.text-input';

    protected string $type = 'text';

    protected ?int $maxLength = null;

    protected bool $hashed = false;

    protected ?string $prefix = null;

    protected ?string $suffix = null;

    /**
     * Any HTML input type: text, email, number, password, tel, url, color, date, ...
     */
    public function type(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function email(): static
    {
        return $this->type('email');
    }

    public function numeric(): static
    {
        return $this->type('number');
    }

    public function tel(): static
    {
        return $this->type('tel');
    }

    public function url(): static
    {
        return $this->type('url');
    }

    /**
     * A password input: the value is hashed on save, the stored hash is never shown, and an
     * empty value on edit keeps the current password.
     */
    public function password(bool $hashed = true): static
    {
        $this->hashed = $hashed;

        return $this->type('password');
    }

    public function maxLength(int $length): static
    {
        $this->maxLength = $length;

        return $this;
    }

    public function prefix(string $prefix): static
    {
        $this->prefix = $prefix;

        return $this;
    }

    public function suffix(string $suffix): static
    {
        $this->suffix = $suffix;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getMaxLength(): ?int
    {
        return $this->maxLength;
    }

    public function getPrefix(): ?string
    {
        return $this->prefix;
    }

    public function getSuffix(): ?string
    {
        return $this->suffix;
    }

    public function isPassword(): bool
    {
        return $this->type === 'password';
    }

    public function isRequired(): bool
    {
        // The current password stays when the input is left empty.
        if ($this->isPassword() && $this->record) {
            return false;
        }

        return parent::isRequired();
    }

    public function getState(): mixed
    {
        return $this->isPassword() ? null : parent::getState();
    }

    protected function getTypeRules(): array
    {
        return array_values(array_filter([
            match ($this->type) {
                'email' => 'email',
                'number' => 'numeric',
                'url' => 'url',
                default => null,
            },
            $this->maxLength ? 'max:'.$this->maxLength : null,
        ]));
    }

    public function shouldSave(mixed $value): bool
    {
        return ! ($this->isPassword() && ($value === null || $value === ''));
    }

    public function dehydrate(mixed $value): mixed
    {
        $value = parent::dehydrate($value);

        return $this->isPassword() && $this->hashed ? Hash::make($value) : $value;
    }
}
