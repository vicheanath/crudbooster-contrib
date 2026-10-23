<?php

namespace crocodicstudio\crudbooster\tests\Feature;

use crocodicstudio\crudbooster\models\CmsApicustom;
use crocodicstudio\crudbooster\models\CmsApikey;
use crocodicstudio\crudbooster\models\CmsSetting;
use crocodicstudio\crudbooster\models\CmsStatistic;
use crocodicstudio\crudbooster\models\CmsStatisticComponent;

/**
 * The API generator, statistic builder and settings change code, keys or files, so a standard admin is
 * turned away from every endpoint that reads or writes them.
 */
class SuperadminOnlyEndpointsTest extends FeatureTestCase
{
    public function test_api_key_and_generator_endpoints_deny_standard_admins(): void
    {
        $key = CmsApikey::query()->create(['screetkey' => 'secret-key', 'status' => CmsApikey::ACTIVE]);
        $api = CmsApicustom::query()->create(['nama' => 'List', 'permalink' => 'list_things']);
        $this->loginAsStandardAdmin();

        $this->get($this->admin('api_generator/screet-key'))->assertRedirect()->assertDontSee('secret-key');
        $this->get($this->admin('api_generator/generate-screet-key'))->assertRedirect();
        $this->get($this->admin("api_generator/status-apikey?id={$key->id}&status=0"))->assertRedirect();
        $this->get($this->admin("api_generator/delete-api-key?id={$key->id}"))->assertRedirect();
        $this->get($this->admin('api_generator/column-table/cms_users'))->assertRedirect();
        $this->post($this->admin('api_generator/save-api-custom'), ['nama' => 'Hack', 'permalink' => 'hack', 'tabel' => 'cms_users'])->assertRedirect();
        $this->get($this->admin("api_generator/delete-api/{$api->id}"))->assertRedirect();

        $this->assertSame(1, CmsApikey::query()->count());
        $this->assertSame(CmsApikey::ACTIVE, $key->fresh()->status);
        $this->assertSame(['List'], CmsApicustom::query()->pluck('nama')->all());
        $this->assertFileDoesNotExist(base_path('app/Http/Controllers/ApiHackController.php'));
    }

    public function test_statistic_builder_writes_deny_standard_admins(): void
    {
        $statistic = CmsStatistic::query()->create(['name' => 'Sales', 'slug' => 'sales']);
        CmsStatisticComponent::query()->create(['id_cms_statistics' => $statistic->id, 'componentID' => 'c1', 'area_name' => 'area1', 'name' => 'Untitled']);
        $this->loginAsStandardAdmin();

        $this->post($this->admin('statistic_builder/add-component'), ['component_name' => 'smallbox', 'id_cms_statistics' => $statistic->id, 'area' => 'area1'])->assertRedirect();
        $this->post($this->admin('statistic_builder/update-area-component'), ['componentid' => 'c1', 'areaname' => 'area2', 'sorting' => 3])->assertRedirect();
        $this->post($this->admin('statistic_builder/save-component'), ['componentid' => 'c1', 'name' => 'Hacked'])->assertRedirect();

        $component = CmsStatisticComponent::query()->forComponentId('c1')->first();
        $this->assertSame(1, CmsStatisticComponent::query()->count());
        $this->assertSame(['area1', 'Untitled'], [$component->area_name, $component->name]);
    }

    public function test_deleting_a_setting_file_denies_standard_admins(): void
    {
        $setting = CmsSetting::query()->where('name', 'appname')->first();
        $this->loginAsStandardAdmin();

        $this->get($this->admin("settings/delete-file-setting?id={$setting->id}"))->assertRedirect();

        $this->assertNotNull($setting->fresh()->content);
    }

    public function test_superadmin_still_manages_api_keys(): void
    {
        $this->loginAsAdmin();

        $id = $this->get($this->admin('api_generator/generate-screet-key'))->assertOk()->json('id');
        $this->get($this->admin("api_generator/status-apikey?id=$id&status=0"))->assertRedirect();
        $this->assertSame(CmsApikey::INACTIVE, CmsApikey::query()->find($id)->status);

        $this->get($this->admin("api_generator/delete-api-key?id=$id"))->assertOk()->assertJson(['status' => 1]);
        $this->assertFalse(CmsApikey::query()->whereKey($id)->exists());
    }
}
