<?php

namespace crocodicstudio\crudbooster\tests\Unit\Models;

use crocodicstudio\crudbooster\models\CmsSetting;
use crocodicstudio\crudbooster\models\CmsStatistic;
use crocodicstudio\crudbooster\models\CmsStatisticComponent;
use crocodicstudio\crudbooster\tests\TestCase;

class CmsSettingAndStatisticTest extends TestCase
{
    public function test_setting_display_label_falls_back_to_the_name(): void
    {
        $this->assertSame('Smtp Host Name', (new CmsSetting(['name' => 'smtp_host_name']))->displayLabel());
        $this->assertSame('SMTP host', (new CmsSetting(['name' => 'smtp_host', 'label' => 'SMTP host']))->displayLabel());
    }

    public function test_settings_in_group(): void
    {
        CmsSetting::query()->create(['name' => 'unit_a', 'group_setting' => 'Unit Group']);
        CmsSetting::query()->create(['name' => 'unit_b', 'group_setting' => 'Unit Group']);

        $this->assertSame(['unit_a', 'unit_b'], CmsSetting::query()->inGroup('Unit Group')->orderBy('id')->pluck('name')->all());
    }

    public function test_statistic_components_by_area_in_order(): void
    {
        $statistic = CmsStatistic::query()->create(['name' => 'Sales', 'slug' => 'sales']);
        foreach ([['c2', 'area1', 2], ['c1', 'area1', 1], ['c3', 'area2', 1]] as [$id, $area, $sorting]) {
            CmsStatisticComponent::query()->create(['id_cms_statistics' => $statistic->id, 'componentID' => $id, 'area_name' => $area, 'sorting' => $sorting]);
        }

        $this->assertSame(['c1', 'c2'], CmsStatisticComponent::query()->inArea($statistic->id, 'area1')->pluck('componentID')->all());
        $this->assertSame('area2', CmsStatisticComponent::query()->forComponentId('c3')->value('area_name'));
        $this->assertCount(3, $statistic->components);
        $this->assertSame('Sales', CmsStatisticComponent::query()->forComponentId('c1')->first()->statistic->name);
    }
}
