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

        // Module routes are read from cms_moduls at boot, before the test database exists.
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
}
