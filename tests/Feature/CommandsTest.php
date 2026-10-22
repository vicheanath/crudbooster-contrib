<?php

namespace crocodicstudio\crudbooster\tests\Feature;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class CommandsTest extends FeatureTestCase
{
    private $published;

    protected function setUp(): void
    {
        parent::setUp();

        $this->published = [
            config_path('crudbooster.php'),
            app_path('Http/Controllers/CBHook.php'),
            app_path('Http/Controllers/AdminCmsUsersController.php'),
            public_path('vendor/crudbooster'),
        ];
        $this->cleanPublished();
    }

    protected function tearDown(): void
    {
        $this->cleanPublished();

        parent::tearDown();
    }

    public function test_install_publishes_migrates_and_seeds()
    {
        DB::table('cms_users')->delete();

        $this->artisan('crudbooster:install')
            ->expectsConfirmation('Do you have setting the database configuration at .env ?', 'yes')
            ->expectsOutputToContain('Installing CRUDBooster Is Completed')
            ->assertSuccessful();

        $this->assertFileExists(config_path('crudbooster.php'));
        $this->assertFileExists(app_path('Http/Controllers/CBHook.php'));
        $this->assertFileExists(public_path('vendor/crudbooster/avatar.jpg'));
        $this->assertDatabaseHas('cms_users', ['email' => 'admin@crudbooster.com']);
    }

    public function test_install_can_be_aborted()
    {
        $this->artisan('crudbooster:install')
            ->expectsConfirmation('Do you have setting the database configuration at .env ?', 'no')
            ->expectsOutputToContain('Setup Aborted')
            ->assertSuccessful();
    }

    public function test_update_runs_without_laravel_filemanager()
    {
        $this->artisan('crudbooster:update')->assertSuccessful();

        $this->assertFileExists(public_path('vendor/crudbooster/avatar.jpg'));
        $this->assertDatabaseHas('cms_settings', ['name' => 'google_fcm_service_account']);
    }

    private function cleanPublished()
    {
        foreach ($this->published as $path) {
            is_dir($path) ? File::deleteDirectory($path) : @unlink($path);
        }
    }
}
