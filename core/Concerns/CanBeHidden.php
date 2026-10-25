<?php

namespace CrudBooster\Concerns;

use Closure;

trait CanBeHidden
{
    protected bool|Closure $visible = true;

    protected bool|Closure $hidden = false;

    public function visible(bool|Closure $condition = true): static
    {
        $this->visible = $condition;

        return $this;
    }

    public function hidden(bool|Closure $condition = true): static
    {
        $this->hidden = $condition;

        return $this;
    }

    public function isVisible(): bool
    {
        return $this->evaluateVisibility();
    }

    protected function evaluateVisibility(): bool
    {
        return $this->evaluate($this->visible) && ! $this->evaluate($this->hidden);
    }
}
