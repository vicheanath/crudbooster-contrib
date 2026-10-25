<?php

namespace CrudBooster;

use CrudBooster\Auth\Authorizer;
use CrudBooster\Auth\PrivilegeAuthorizer;
use CrudBooster\Console\MakePageCommand;
use CrudBooster\Console\MakeResourceCommand;
use CrudBooster\Console\SyncPanelCommand;
use CrudBooster\Panel\Panel;
use CrudBooster\Panel\RouteRegistrar;
use Illuminate\Support\ServiceProvider;

/**
 * The resource and page layer. Registered by CRUDBoosterServiceProvider, next to the classic
 * module layer it shares the login, layout, privileges and audit log with.
 */
class PanelServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(Panel::class);
        $this->app->bind(Authorizer::class, PrivilegeAuthorizer::class);
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/resources/views', 'cb');

        if ($this->app->runningInConsole()) {
            $this->commands([MakeResourceCommand::class, MakePageCommand::class, SyncPanelCommand::class]);
            $this->publishes([__DIR__.'/resources/views' => resource_path('views/vendor/cb')], 'cb_panel_views');
        }

        // After every provider's boot(), so resources registered there are routed too.
        $this->app->booted(function () {
            $panel = $this->app->make(Panel::class);

            foreach ((array) config('crudbooster.PANEL.discover', []) as $namespace => $directory) {
                $panel->discover($directory, $namespace);
            }
            $panel->boot();

            if (! $this->app->routesAreCached()) {
                $this->app->make(RouteRegistrar::class)->register();
            }
        });
    }
}
