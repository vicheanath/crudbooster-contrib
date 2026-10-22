<?php

namespace crocodicstudio\crudbooster\tests\Feature;

use Illuminate\Support\Facades\DB;

class DashboardAndMiscTest extends FeatureTestCase
{
    public static function components()
    {
        $sql = 'select name as label, id as value from cms_privileges';

        return [
            'smallbox' => ['smallbox', ['name' => 'Users', 'icon' => 'ion-person', 'color' => 'bg-green', 'link' => '#', 'sql' => 'select count(*) from cms_users']],
            'table' => ['table', ['name' => 'Privileges', 'sql' => 'select name from cms_privileges']],
            'chartbar' => ['chartbar', ['name' => 'Bar', 'sql' => $sql, 'area_name' => 'Total', 'goals' => '']],
            'chartline' => ['chartline', ['name' => 'Line', 'sql' => $sql, 'area_name' => 'Total', 'goals' => '']],
            'chartarea' => ['chartarea', ['name' => 'Area', 'sql' => $sql, 'area_name' => 'Total', 'goals' => '']],
            'panelcustom' => ['panelcustom', ['name' => 'Custom', 'type' => 'html', 'value' => '<b>Hello panel</b>']],
            'panelarea' => ['panelarea', ['name' => 'Panel', 'content' => 'Panel body']],
        ];
    }

    /**
     * @dataProvider components
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('components')]
    public function test_statistic_component_lifecycle($component, $config)
    {
        $this->loginAsAdmin();
        $statistic = DB::table('cms_statistics')->insertGetId(['name' => 'Overview', 'slug' => 'overview', 'created_at' => now()]);

        $componentID = $this->post($this->admin('statistic_builder/add-component'), [
            'component_name' => $component,
            'id_cms_statistics' => $statistic,
            'sorting' => 1,
            'area' => 'area1',
        ])->assertOk()->json('componentID');

        $this->get($this->admin("statistic_builder/edit-component/$componentID"))->assertOk();

        $this->post($this->admin('statistic_builder/save-component'), [
            'componentid' => $componentID,
            'name' => $config['name'],
            'config' => $config,
        ])->assertOk()->assertJson(['status' => true]);

        $layout = $this->get($this->admin("statistic_builder/view-component/$componentID"))->assertOk()->json('layout');
        $this->assertStringNotContainsString('ERROR', $layout);

        $this->get($this->admin("statistic_builder/builder/$statistic"))->assertOk();
        $this->get($this->admin('statistic_builder/show/overview'))->assertOk();
    }

    public function test_menu_order_is_saved()
    {
        $this->loginAsAdmin();
        $parent = DB::table('cms_menus')->insertGetId(['name' => 'Parent', 'type' => 'URL', 'path' => '#', 'parent_id' => 0, 'is_active' => 1, 'sorting' => 5]);
        $child = DB::table('cms_menus')->insertGetId(['name' => 'Child', 'type' => 'URL', 'path' => '#', 'parent_id' => 0, 'is_active' => 1, 'sorting' => 6]);

        $this->post($this->admin('menu_management/save-menu'), [
            'menus' => json_encode([[['id' => $parent, 'children' => [[['id' => $child]]]]]]),
            'isActive' => 1,
        ])->assertOk()->assertJson(['success' => true]);

        $this->assertDatabaseHas('cms_menus', ['id' => $child, 'parent_id' => $parent, 'sorting' => 1]);
        $this->assertDatabaseHas('cms_menus', ['id' => $parent, 'parent_id' => 0, 'sorting' => 1]);
    }

    public static function locales()
    {
        return array_map(function ($dir) {
            return [basename($dir)];
        }, glob(__DIR__.'/../../src/localization/*', GLOB_ONLYDIR));
    }

    /**
     * @dataProvider locales
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('locales')]
    public function test_locale_renders_login_and_admin($locale)
    {
        app()->setLocale($locale);
        $this->assertNotSame('crudbooster.text_dashboard', cbLang('text_dashboard'));

        $this->get($this->admin('login'))->assertOk();
        $this->loginAsAdmin();
        $this->get($this->admin('users'))->assertOk()->assertSee(cbLang('text_dashboard'));
    }

    public function test_version_command()
    {
        $this->artisan('crudbooster:version')->assertSuccessful();
    }
}
