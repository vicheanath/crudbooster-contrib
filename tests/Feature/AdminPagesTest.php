<?php

namespace crocodicstudio\crudbooster\tests\Feature;

use Illuminate\Support\Facades\DB;

class AdminPagesTest extends FeatureTestCase
{
    public static function modules()
    {
        return [
            'notifications' => ['notifications', 'cms_notifications'],
            'privileges' => ['privileges', 'cms_privileges'],
            'users' => ['users', 'cms_users'],
            'settings' => ['settings', 'cms_settings'],
            'module_generator' => ['module_generator', 'cms_moduls'],
            'menu_management' => ['menu_management', 'cms_menus'],
            'email_templates' => ['email_templates', 'cms_email_templates'],
            'statistic_builder' => ['statistic_builder', 'cms_statistics'],
            'logs' => ['logs', 'cms_logs'],
        ];
    }

    /**
     * @dataProvider modules
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('modules')]
    public function test_module_pages_render($path, $table)
    {
        $this->loginAsAdmin();
        $this->seedRow($table);

        $this->get($this->admin($path))->assertOk();

        $id = DB::table($table)->orderBy('id')->value('id');
        foreach (['add', "edit/$id", "detail/$id"] as $page) {
            $response = $this->get($this->admin("$path/$page"));
            $this->assertContains($response->getStatusCode(), [200, 302], "$path/$page returned ".$response->getStatusCode()."\n".$this->exceptionText($response));
        }
    }

    public static function screens()
    {
        return [
            'settings by group' => ['settings/show?group=General%20Setting'],
            'add setting' => ['settings/add'],
            'module generator step 1' => ['module_generator/step1'],
            'api generator' => ['api_generator'],
            'api generator new' => ['api_generator/generator'],
            'api keys' => ['api_generator/screet-key'],
            'statistic builder' => ['statistic_builder'],
            'profile' => ['users/profile'],
            'privileges add' => ['privileges/add'],
            'lock screen' => ['lock-screen'],
        ];
    }

    /**
     * @dataProvider screens
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('screens')]
    public function test_admin_screens_render($path)
    {
        $this->loginAsAdmin();

        $response = $this->get($this->admin($path));
        $this->assertContains($response->getStatusCode(), [200, 302], "$path returned ".$response->getStatusCode()."\n".$this->exceptionText($response));
    }

    private function seedRow($table)
    {
        $rows = [
            'cms_notifications' => ['id_cms_users' => 1, 'content' => 'Hello', 'url' => '/', 'is_read' => 0],
            'cms_statistics' => ['name' => 'Sales', 'slug' => 'sales'],
            'cms_menus' => ['name' => 'Reports', 'type' => 'URL', 'path' => 'https://example.com', 'color' => 'normal', 'icon' => 'fa fa-file', 'parent_id' => 0, 'is_active' => 1, 'is_dashboard' => 0, 'id_cms_privileges' => 1, 'sorting' => 1],
            'cms_logs' => ['ipaddress' => '127.0.0.1', 'useragent' => 'phpunit', 'url' => '/', 'description' => 'test', 'id_cms_users' => 1],
        ];
        if (isset($rows[$table]) && ! DB::table($table)->count()) {
            DB::table($table)->insert($rows[$table] + ['created_at' => now()]);
        }
    }

    private function exceptionText($response)
    {
        return $response->exception ? get_class($response->exception).': '.$response->exception->getMessage().' @ '.$response->exception->getFile().':'.$response->exception->getLine() : '';
    }
}
