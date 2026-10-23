<?php

namespace crocodicstudio\crudbooster\tests\Unit\Settings;

use crocodicstudio\crudbooster\contracts\Settings\SettingsRepositoryInterface;
use crocodicstudio\crudbooster\models\CmsSetting;
use crocodicstudio\crudbooster\tests\TestCase;

class SettingsRepositoryTest extends TestCase
{
    public function test_set_stores_the_value_and_refreshes_the_cache(): void
    {
        $settings = app(SettingsRepositoryInterface::class);
        $before = $settings->get('appname');

        $settings->set('appname', 'Renamed');

        $this->assertNotSame('Renamed', $before);
        $this->assertSame('Renamed', $settings->get('appname'));
        $this->assertSame('Renamed', CmsSetting::query()->where('name', 'appname')->value('content'));
    }

    public function test_groups_are_distinct_and_in_group_returns_models(): void
    {
        $settings = app(SettingsRepositoryInterface::class);
        CmsSetting::query()->create(['name' => 'unit_one', 'group_setting' => 'Unit Group']);
        CmsSetting::query()->create(['name' => 'unit_two', 'group_setting' => 'Unit Group']);

        $groups = $settings->groups();

        $this->assertContains('Unit Group', $groups->all());
        $this->assertSame($groups->unique()->values()->all(), $groups->all());
        $this->assertSame(['unit_one', 'unit_two'], $settings->inGroup('Unit Group')->pluck('name')->all());
        $this->assertInstanceOf(CmsSetting::class, $settings->inGroup('Unit Group')->first());
    }
}
