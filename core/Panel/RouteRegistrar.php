<?php

namespace CrudBooster\Panel;

use crocodicstudio\crudbooster\middlewares\CBBackend;
use CrudBooster\Http\Controllers\PageController;
use CrudBooster\Http\Controllers\ResourceController;
use Illuminate\Routing\Router;

/**
 * Routes for the panel's resources and pages, under the admin path and behind CRUDBooster's
 * login. Resource routes are named cb.resources.{slug}.{page}, page routes cb.pages.{slug}.
 */
class RouteRegistrar
{
    public function __construct(
        private readonly Panel $panel,
        private readonly Router $router,
    ) {
    }

    public function register(): void
    {
        $this->panel->assertUniqueSlugs();

        $this->router->middleware(array_merge(['web', CBBackend::class], (array) config('crudbooster.PANEL.middleware', [])))
            ->prefix($this->panel->getPath())
            ->group(function (Router $router) {
                foreach ($this->panel->getResources() as $resource) {
                    $this->registerResource($router, $resource);
                }
                foreach ($this->panel->getPages() as $page) {
                    $name = $page::getRouteName();
                    $router->get($page::getSlug(), [PageController::class, 'show'])->name($name)->defaults('page', $page);
                    if ($page::acceptsSubmissions()) {
                        $router->post($page::getSlug(), [PageController::class, 'submit'])->name($name.'.submit')->defaults('page', $page);
                    }
                }
            });

        $this->router->getRoutes()->refreshNameLookups();
        $this->router->getRoutes()->refreshActionLookups();
    }

    private function registerResource(Router $router, string $resource): void
    {
        $slug = $resource::getSlug();
        $routes = [];

        if ($resource::hasPage('index')) {
            $routes['index'] = $router->get($slug, [ResourceController::class, 'index']);
            $routes['bulk'] = $router->post($slug.'/bulk/{action}', [ResourceController::class, 'bulk']);
        }
        if ($resource::hasPage('create')) {
            $routes['create'] = $router->get($slug.'/create', [ResourceController::class, 'create']);
            $routes['store'] = $router->post($slug, [ResourceController::class, 'store']);
        }
        if ($resource::hasPage('edit')) {
            $routes['edit'] = $router->get($slug.'/{record}/edit', [ResourceController::class, 'edit']);
            $routes['update'] = $router->put($slug.'/{record}', [ResourceController::class, 'update']);
        }
        if ($resource::hasPage('view')) {
            $routes['view'] = $router->get($slug.'/{record}', [ResourceController::class, 'show']);
        }
        $routes['destroy'] = $router->delete($slug.'/{record}', [ResourceController::class, 'destroy']);
        $routes['action'] = $router->post($slug.'/{record}/actions/{action}', [ResourceController::class, 'action']);

        foreach ($routes as $page => $route) {
            $route->name($resource::getRouteName($page))->defaults('resource', $resource);
        }
    }
}
