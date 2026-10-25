<?php

namespace CrudBooster\Tables\Columns;

use Closure;
use Illuminate\Database\Eloquent\Model;

/**
 * The value in a coloured badge. colors() maps values to Bootstrap colours:
 *
 *     BadgeColumn::make('status')->colors(['published' => 'success', 'draft' => 'secondary'])
 */
class BadgeColumn extends TextColumn
{
    protected string $view = 'cb::columns.badge';

    protected array|Closure $colors = [];

    protected string $defaultColor = 'secondary';

    public function colors(array|Closure $colors, string $default = 'secondary'): static
    {
        $this->colors = $colors;
        $this->defaultColor = $default;

        return $this;
    }

    public function getColor(mixed $state, Model $record): string
    {
        if ($this->colors instanceof Closure) {
            return $this->evaluate($this->colors, ['state' => $state, 'record' => $record]) ?: $this->defaultColor;
        }

        $key = $state instanceof \BackedEnum ? $state->value : (is_scalar($state) ? (string) $state : null);

        return $this->colors[$key] ?? $this->defaultColor;
    }
}
