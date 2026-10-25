<?php

namespace CrudBooster\Navigation;

use Closure;
use CrudBooster\Concerns\CanBeHidden;
use CrudBooster\Concerns\EvaluatesClosures;
use Illuminate\Support\Facades\Request;

/**
 * A sidebar link. Resources and pages add theirs; add others with Panel::navigationItems():
 *
 *     NavigationItem::make('Documentation')->url('https://...', openInNewTab: true)->icon('fa fa-book')
 */
class NavigationItem
{
    use CanBeHidden;
    use EvaluatesClosures;

    protected string|Closure $url = '#';

    protected bool $openUrlInNewTab = false;

    protected ?string $icon = 'fa fa-circle-o';

    protected ?string $group = null;

    protected int $sort = 0;

    protected ?string $activePattern = null;

    protected string|int|Closure|null $badge = null;

    protected string $badgeColor = 'danger';

    final public function __construct(protected string $label)
    {
    }

    public static function make(string $label): static
    {
        return new static($label);
    }

    public function url(string|Closure $url, bool $openInNewTab = false): static
    {
        $this->url = $url;
        $this->openUrlInNewTab = $openInNewTab;

        return $this;
    }

    public function icon(?string $icon): static
    {
        $this->icon = $icon;

        return $this;
    }

    public function group(?string $group): static
    {
        $this->group = $group;

        return $this;
    }

    public function sort(int $sort): static
    {
        $this->sort = $sort;

        return $this;
    }

    /**
     * A Request::is() pattern, e.g. "admin/products*". Defaults to the URL's path and below.
     */
    public function activeWhen(string $pattern): static
    {
        $this->activePattern = $pattern;

        return $this;
    }

    /**
     * A count or label next to the link: ->badge(fn () => Order::pending()->count())
     */
    public function badge(string|int|Closure|null $badge, string $color = 'danger'): static
    {
        $this->badge = $badge;
        $this->badgeColor = $color;

        return $this;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getUrl(): string
    {
        return $this->evaluate($this->url);
    }

    public function shouldOpenUrlInNewTab(): bool
    {
        return $this->openUrlInNewTab;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function getGroup(): ?string
    {
        return $this->group;
    }

    public function getSort(): int
    {
        return $this->sort;
    }

    public function getBadge(): string|int|null
    {
        return $this->evaluate($this->badge);
    }

    public function getBadgeColor(): string
    {
        return $this->badgeColor;
    }

    public function isActive(): bool
    {
        if ($this->activePattern) {
            return Request::is($this->activePattern);
        }

        $path = trim((string) parse_url($this->getUrl(), PHP_URL_PATH), '/');

        return $path !== '' && Request::is($path, $path.'/*');
    }
}
