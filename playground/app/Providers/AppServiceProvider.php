<?php

namespace App\Providers;

use CrudBooster\Facades\Panel;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // The order of the resource groups in the sidebar (app/Admin is discovered automatically).
        Panel::navigationGroups(['E-commerce (resources)', 'HR', 'CRM']);
    }
}
