<?php

namespace crocodicstudio\crudbooster\tests;

use crocodicstudio\crudbooster\CRUDBoosterServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    use RefreshDatabase;

    protected function getPackageProviders($app)
    {
        return [
            CRUDBoosterServiceProvider::class,
            \Barryvdh\DomPDF\ServiceProvider::class,
            \Maatwebsite\Excel\ExcelServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app)
    {
        $app['config']->set('database.default', 'testing');

        // CRUDBooster reads uploads via storage_path('app/...'); Laravel 11+ apps default the
        // local disk to storage/app/private, so mirror the layout existing installs use.
        $app['config']->set('filesystems.disks.local.root', storage_path('app'));
    }

    protected function afterRefreshingDatabase()
    {
        $this->seed(\crocodicstudio\crudbooster\database\seeds\CBSeeder::class);
    }
}
