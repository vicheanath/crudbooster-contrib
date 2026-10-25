<?php

namespace CrudBooster\Panel;

use Closure;
use CrudBooster\Navigation\NavigationItem;
use CrudBooster\Pages\Page;
use CrudBooster\Plugins\Plugin;
use CrudBooster\Resources\Resource;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Symfony\Component\Finder\Finder;

/**
 * The registry of the admin panel: its resources, pages, plugins, extra sidebar links and render
 * hooks. It is a singleton; configure it from a service provider's boot() method:
 *
 *     use CrudBooster\Facades\Panel;
 *
 *     Panel::resources([ProductResource::class])
 *         ->pages([SalesReport::class])
 *         ->plugin(new BlogPlugin)
 *         ->renderHook('head', fn () => '<link rel="stylesheet" href="/css/admin.css">');
 *
 * Classes in the directories listed under crudbooster.PANEL.discover (app/Admin/Resources and
 * app/Admin/Pages by default) are registered without this step.
 */
class Panel
{
    /** @var array<class-string<Resource>, class-string<Resource>> */
    protected array $resources = [];

    /** @var array<class-string<Page>, class-string<Page>> */
    protected array $pages = [];

    /** @var array<string, Plugin> */
    protected array $plugins = [];

    /** @var NavigationItem[] */
    protected array $navigationItems = [];

    /** @var array<string, array<int, Closure|string>> */
    protected array $renderHooks = [];

    protected array $navigationGroups = [];

    protected bool $booted = false;

    public function resources(array $resources): static
    {
        foreach ($resources as $resource) {
            $this->resource($resource);
        }

        return $this;
    }

    /**
     * @param  class-string<Resource>  $resource
     */
    public function resource(string $resource): static
    {
        if (! is_subclass_of($resource, Resource::class)) {
            throw new \InvalidArgumentException("[$resource] does not extend ".Resource::class.'.');
        }
        $this->resources[$resource] = $resource;

        return $this;
    }

    public function pages(array $pages): static
    {
        foreach ($pages as $page) {
            $this->page($page);
        }

        return $this;
    }

    /**
     * @param  class-string<Page>  $page
     */
    public function page(string $page): static
    {
        if (! is_subclass_of($page, Page::class)) {
            throw new \InvalidArgumentException("[$page] does not extend ".Page::class.'.');
        }
        $this->pages[$page] = $page;

        return $this;
    }

    /**
     * Registers the resources and pages found in a directory (recursively).
     */
    public function discover(string $directory, string $namespace): static
    {
        if (! is_dir($directory)) {
            return $this;
        }

        $namespace = trim($namespace, '\\');
        foreach ((new Finder)->files()->in($directory)->name('*.php')->sortByName() as $file) {
            $class = $namespace.'\\'.str_replace(['/', '.php'], ['\\', ''], $file->getRelativePathname());

            if (! class_exists($class) || (new \ReflectionClass($class))->isAbstract()) {
                continue;
            }
            if (is_subclass_of($class, Resource::class)) {
                $this->resource($class);
            } elseif (is_subclass_of($class, Page::class)) {
                $this->page($class);
            }
        }

        return $this;
    }

    public function plugin(Plugin $plugin): static
    {
        $this->plugins[$plugin->getId()] = $plugin;
        $plugin->register($this);

        if ($this->booted) {
            $plugin->boot($this);
        }

        return $this;
    }

    public function hasPlugin(string $id): bool
    {
        return isset($this->plugins[$id]);
    }

    public function getPlugin(string $id): Plugin
    {
        return $this->plugins[$id] ?? throw new \InvalidArgumentException("No CRUDBooster plugin [$id] is registered.");
    }

    public function navigationItems(array $items): static
    {
        array_push($this->navigationItems, ...$items);

        return $this;
    }

    /**
     * The order of the sidebar groups; groups not listed follow, in the order they appear.
     */
    public function navigationGroups(array $groups): static
    {
        $this->navigationGroups = $groups;

        return $this;
    }

    /**
     * Adds HTML at a named place in the layout or the resource pages: head, body.end,
     * sidebar.before, sidebar.after, index.before, index.after, form.before, form.after,
     * detail.before, detail.after, page.before, page.after. A closure receives the place's
     * variables ($resource, $record, $page...) and returns a string or a view.
     */
    public function renderHook(string $name, Closure|string $content): static
    {
        $this->renderHooks[$name][] = $content;

        return $this;
    }

    public function renderHooks(string $name, array $data = []): HtmlString
    {
        $html = '';
        foreach ($this->renderHooks[$name] ?? [] as $content) {
            $html .= $content instanceof Closure ? (string) app()->call($content, $data) : $content;
        }

        return new HtmlString($html);
    }

    /**
     * @return array<class-string<Resource>>
     */
    public function getResources(): array
    {
        return array_values($this->resources);
    }

    /**
     * @return array<class-string<Page>>
     */
    public function getPages(): array
    {
        return array_values($this->pages);
    }

    /**
     * @return class-string<Resource>|null
     */
    public function getResource(string $slug): ?string
    {
        foreach ($this->resources as $resource) {
            if ($resource::getSlug() === $slug) {
                return $resource;
            }
        }

        return null;
    }

    /**
     * The resource of a model, e.g. to link to a related record.
     *
     * @return class-string<Resource>|null
     */
    public function getResourceForModel(string $model): ?string
    {
        foreach ($this->resources as $resource) {
            if ($resource::getModel() === $model) {
                return $resource;
            }
        }

        return null;
    }

    public function getPath(): string
    {
        return trim((string) config('crudbooster.ADMIN_PATH', 'admin'), '/');
    }

    /**
     * The sidebar links the current admin may open, grouped and sorted. The "" group is ungrouped.
     *
     * @return array<string, NavigationItem[]>
     */
    public function getNavigation(): array
    {
        $items = [];

        foreach ($this->resources as $resource) {
            if ($resource::shouldRegisterNavigation()) {
                $items[] = NavigationItem::make($resource::getPluralLabel())
                    ->url(fn () => $resource::url())
                    ->activeWhen($this->getPath().'/'.$resource::getSlug().'*')
                    ->icon($resource::getNavigationIcon())
                    ->group($resource::getNavigationGroup())
                    ->sort($resource::getNavigationSort())
                    ->visible(fn () => app($resource)->can('viewAny'));
            }
        }

        foreach ($this->pages as $page) {
            if ($page::shouldRegisterNavigation()) {
                $items[] = NavigationItem::make($page::getTitle())
                    ->url(fn () => $page::url())
                    ->activeWhen($this->getPath().'/'.$page::getSlug().'*')
                    ->icon($page::getNavigationIcon())
                    ->group($page::getNavigationGroup())
                    ->sort($page::getNavigationSort())
                    ->visible(fn () => app($page)->can());
            }
        }

        $items = array_filter([...$items, ...$this->navigationItems], fn (NavigationItem $item) => $item->isVisible());
        usort($items, fn (NavigationItem $a, NavigationItem $b) => [$a->getSort(), $a->getLabel()] <=> [$b->getSort(), $b->getLabel()]);

        $groups = array_fill_keys(['', ...$this->navigationGroups], []);
        foreach ($items as $item) {
            $groups[(string) $item->getGroup()][] = $item;
        }

        return array_filter($groups);
    }

    /**
     * Boots the plugins. Called once, when the application has booted.
     */
    public function boot(): void
    {
        if ($this->booted) {
            return;
        }
        $this->booted = true;

        foreach ($this->plugins as $plugin) {
            $plugin->boot($this);
        }
    }

    /**
     * Throws when two resources or pages share a URL.
     */
    public function assertUniqueSlugs(): void
    {
        $owners = [];
        foreach ([...$this->getResources(), ...$this->getPages()] as $class) {
            $slug = $class::getSlug();
            if (isset($owners[$slug])) {
                throw new \LogicException("CRUDBooster: [$class] and [{$owners[$slug]}] both use the slug \"$slug\".");
            }
            if (Str::contains($slug, '/')) {
                throw new \LogicException("CRUDBooster: the slug of [$class] may not contain \"/\".");
            }
            $owners[$slug] = $class;
        }
    }
}
