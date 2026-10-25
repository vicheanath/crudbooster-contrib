<?php

namespace CrudBooster\Concerns;

use Closure;

/**
 * Lets a component option be a value or a closure. Closures are called through the container,
 * so they may ask for named arguments ($record, $state, $query...) and for services alike:
 *
 *     ->visible(fn (Product $record) => $record->is_draft)
 */
trait EvaluatesClosures
{
    protected function evaluate(mixed $value, array $parameters = []): mixed
    {
        if (! $value instanceof Closure) {
            return $value;
        }

        return app()->call($value, $parameters + $this->defaultEvaluationParameters());
    }

    /**
     * Arguments every closure of this component may ask for.
     */
    protected function defaultEvaluationParameters(): array
    {
        return [];
    }
}
