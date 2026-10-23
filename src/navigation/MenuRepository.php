<?php

namespace crocodicstudio\crudbooster\navigation;

use crocodicstudio\crudbooster\contracts\Navigation\MenuRepositoryInterface;
use crocodicstudio\crudbooster\models\CmsMenu;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Contracts\Session\Session;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;

/**
 * Reads cms_menus as plain rows (not models), which is what sidebar.blade.php and custom themes expect.
 */
final class MenuRepository implements MenuRepositoryInterface
{
    public function __construct(
        private readonly CmsMenu $menus,
        private readonly Session $session,
        private readonly UrlGenerator $url,
    ) {
    }

    public function sidebar(): Collection
    {
        $menus = $this->visibleMenus()->where('parent_id', 0)->where('is_dashboard', 0)->orderBy('sorting', 'asc')->get();

        foreach ($menus as $menu) {
            $this->link($menu);

            $children = $this->visibleMenus()->where('is_dashboard', 0)->where('parent_id', $menu->id)->orderBy('sorting', 'asc')->get();
            if (count($children)) {
                $children->each(fn (object $child) => $this->link($child));
                $menu->children = $children;
            }
        }

        return $menus;
    }

    public function dashboard(): object
    {
        $menu = $this->visibleMenus()->where('is_dashboard', 1)->first();

        if (! $menu) {
            return (object) ['id' => null, 'color' => null, 'url' => null];
        }

        $menu->url = $this->resolveUrl($menu);

        return $menu;
    }

    /**
     * Active menus granted to the current privilege.
     */
    private function visibleMenus(): Builder
    {
        $privilegeId = $this->session->get('admin_privileges');

        return $this->menus->newQuery()->toBase()
            ->select('cms_menus.*')
            ->whereIn('cms_menus.id', function (Builder $query) use ($privilegeId) {
                $query->select('id_cms_menus')->from('cms_menus_privileges')->where('id_cms_privileges', $privilegeId);
            })
            ->where('is_active', 1);
    }

    private function link(object $menu): void
    {
        try {
            $menu->url = $this->resolveUrl($menu);
            $menu->is_broken = false;
        } catch (\Exception $e) {
            $menu->url = '#';
            $menu->is_broken = true;
        }

        $menu->url_path = trim(str_replace($this->url->to('/'), '', $menu->url), '/');
    }

    private function resolveUrl(object $menu): string
    {
        return match ($menu->type) {
            'Route' => $this->url->route($menu->path),
            'Controller & Method' => $this->url->action($menu->path),
            'Module', 'Statistic' => $this->url->to(config('crudbooster.ADMIN_PATH').'/'.$menu->path),
            default => (string) $menu->path,
        };
    }
}
