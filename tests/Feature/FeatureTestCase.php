<?php

namespace crocodicstudio\crudbooster\tests\Feature;

use crocodicstudio\crudbooster\helpers\CBRouter;
use crocodicstudio\crudbooster\tests\TestCase;

abstract class FeatureTestCase extends TestCase
{
    protected function defineEnvironment($app)
    {
        parent::defineEnvironment($app);

        // The files `crudbooster:install` publishes into the host app.
        if (! class_exists('App\Http\Controllers\Controller')) {
            require_once __DIR__.'/../fixtures/AppController.php';
        }
        require_once __DIR__.'/../../src/userfiles/controllers/CBHook.php';
        require_once __DIR__.'/../../src/userfiles/controllers/AdminCmsUsersController.php';
    }

    protected function afterRefreshingDatabase()
    {
        parent::afterRefreshingDatabase();

        $this->registerCrudboosterRoutes();
    }

    /**
     * Module and API routes are discovered at boot (from cms_moduls and app/Http/Controllers),
     * before the test database or generated controllers exist.
     */
    protected function registerCrudboosterRoutes()
    {
        CBRouter::route();
        $routes = $this->app['router']->getRoutes();
        $routes->refreshNameLookups();
        $routes->refreshActionLookups();
    }

    protected function admin($path = '')
    {
        return '/'.trim(config('crudbooster.ADMIN_PATH').'/'.ltrim($path, '/'), '/');
    }

    protected function loginAsAdmin()
    {
        $this->post($this->admin('login'), [
            'email' => 'admin@crudbooster.com',
            'password' => '123456',
        ])->assertRedirect();

        $this->assertNotEmpty(session('admin_id'), 'Admin login failed');

        return $this;
    }

    /**
     * Logs in as a new admin whose privilege is not superadmin and has no module roles.
     */
    protected function loginAsStandardAdmin()
    {
        $privilegeId = \Illuminate\Support\Facades\DB::table('cms_privileges')->insertGetId([
            'name' => 'Staff', 'is_superadmin' => 0, 'theme_color' => 'skin-blue',
        ]);
        \Illuminate\Support\Facades\DB::table('cms_users')->insert([
            'name' => 'Staff User', 'email' => 'staff@crudbooster.com', 'password' => \Illuminate\Support\Facades\Hash::make('secret'),
            'id_cms_privileges' => $privilegeId, 'status' => 'Active',
        ]);

        $this->post($this->admin('login'), ['email' => 'staff@crudbooster.com', 'password' => 'secret'])->assertRedirect();
        $this->assertFalse((bool) session('admin_is_superadmin'), 'Standard admin login failed');

        return $this;
    }
}
