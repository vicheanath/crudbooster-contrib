<?php

namespace CrudBooster\Actions;

use Closure;
use CrudBooster\Concerns\CanBeHidden;
use CrudBooster\Concerns\EvaluatesClosures;
use CrudBooster\Concerns\HasLabel;
use CrudBooster\Concerns\HasName;

/**
 * A button on a table row (or, for BulkAction, on the selected rows). It is either a link:
 *
 *     Action::make('preview')->url(fn (Post $record) => route('posts.show', $record), openInNewTab: true)
 *
 * or runs on the server, after an optional confirmation:
 *
 *     Action::make('publish')->icon('fa fa-send')->color('success')
 *         ->visible(fn (Post $record) => ! $record->published_at)
 *         ->requiresConfirmation()
 *         ->action(fn (Post $record) => $record->publish())
 *         ->successMessage('Post published.')
 */
class Action
{
    use CanBeHidden;
    use EvaluatesClosures;
    use HasLabel;
    use HasName;

    protected ?string $icon = null;

    protected string $color = 'secondary';

    protected string|Closure|null $url = null;

    protected bool $openUrlInNewTab = false;

    protected ?Closure $action = null;

    protected bool|string $confirmation = false;

    protected string|Closure|null $successMessage = null;

    protected ?string $ability = null;

    protected array $evaluationParameters = [];

    public function icon(string $icon): static
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * A Bootstrap colour: primary, secondary, success, danger, warning, info.
     */
    public function color(string $color): static
    {
        $this->color = $color;

        return $this;
    }

    public function url(string|Closure $url, bool $openInNewTab = false): static
    {
        $this->url = $url;
        $this->openUrlInNewTab = $openInNewTab;

        return $this;
    }

    /**
     * What the action does. The closure may ask for $record (or $records for a bulk action),
     * $data (the request input) and any service; returning a response sends it instead of the
     * default redirect back.
     */
    public function action(Closure $callback): static
    {
        $this->action = $callback;

        return $this;
    }

    public function requiresConfirmation(bool|string $message = true): static
    {
        $this->confirmation = $message;

        return $this;
    }

    public function successMessage(string|Closure|null $message): static
    {
        $this->successMessage = $message;

        return $this;
    }

    /**
     * Shows and runs the action only for users the resource grants this ability (e.g. "update").
     */
    public function authorize(string $ability): static
    {
        $this->ability = $ability;

        return $this;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function getColor(): string
    {
        return $this->color;
    }

    public function getAbility(): ?string
    {
        return $this->ability;
    }

    public function isLink(): bool
    {
        return $this->url !== null;
    }

    public function shouldOpenUrlInNewTab(): bool
    {
        return $this->openUrlInNewTab;
    }

    public function getConfirmationMessage(): ?string
    {
        if ($this->confirmation === false) {
            return null;
        }

        return is_string($this->confirmation) ? $this->confirmation : __('Are you sure?');
    }

    /**
     * The record (or records) closures are evaluated for.
     */
    public function for(array $parameters): static
    {
        $clone = clone $this;
        $clone->evaluationParameters = $parameters;

        return $clone;
    }

    protected function defaultEvaluationParameters(): array
    {
        return $this->evaluationParameters;
    }

    public function getUrl(): ?string
    {
        return $this->evaluate($this->url);
    }

    public function run(array $data = []): mixed
    {
        if (! $this->action) {
            throw new \LogicException("Action [{$this->name}] has no action() to run.");
        }

        return $this->evaluate($this->action, ['data' => $data]);
    }

    public function getSuccessMessage(): ?string
    {
        return $this->evaluate($this->successMessage);
    }
}
