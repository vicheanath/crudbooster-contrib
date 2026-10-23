<?php

namespace crocodicstudio\crudbooster\tests\Unit\Models;

use crocodicstudio\crudbooster\models\CmsApicustom;
use crocodicstudio\crudbooster\models\CmsApikey;
use crocodicstudio\crudbooster\tests\TestCase;

class CmsApiModelsTest extends TestCase
{
    public function test_api_keys_by_secret_and_status(): void
    {
        CmsApikey::query()->create(['screetkey' => 'on', 'status' => CmsApikey::ACTIVE]);
        CmsApikey::query()->create(['screetkey' => 'off', 'status' => CmsApikey::INACTIVE]);

        $this->assertTrue(CmsApikey::query()->withSecret('on')->active()->exists());
        $this->assertFalse(CmsApikey::query()->withSecret('off')->active()->exists());
        $this->assertFalse(CmsApikey::query()->withSecret(null)->active()->exists());
    }

    public function test_custom_apis_are_sorted_by_name_and_found_by_permalink(): void
    {
        CmsApicustom::query()->create(['nama' => 'Beta', 'permalink' => 'beta']);
        CmsApicustom::query()->create(['nama' => 'Alpha', 'permalink' => 'alpha']);

        $this->assertSame(['Alpha', 'Beta'], CmsApicustom::query()->sorted()->pluck('nama')->all());
        $this->assertSame('Beta', CmsApicustom::query()->forPermalink('beta')->value('nama'));
    }

    public function test_parameter_and_response_lists_never_revive_objects(): void
    {
        $api = new CmsApicustom([
            'parameters' => serialize([['name' => 'id', 'type' => 'integer']]),
            'responses' => serialize([new \ArrayObject()]),
        ]);

        $this->assertSame([['name' => 'id', 'type' => 'integer']], $api->parameterList());
        $this->assertInstanceOf(\__PHP_Incomplete_Class::class, $api->responseList()[0]);
        $this->assertSame([], (new CmsApicustom(['parameters' => 'not serialized']))->parameterList());
        $this->assertSame([], (new CmsApicustom())->responseList());
    }
}
