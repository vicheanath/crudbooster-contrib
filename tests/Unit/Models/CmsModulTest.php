<?php

namespace crocodicstudio\crudbooster\tests\Unit\Models;

use crocodicstudio\crudbooster\models\CmsModul;
use crocodicstudio\crudbooster\tests\TestCase;

class CmsModulTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // The seeder's built-in modules are protected; start these tests from user modules only.
        CmsModul::query()->delete();
    }

    public function test_not_deleted_and_unprotected_scopes(): void
    {
        $this->module(['name' => 'Products', 'path' => 'products']);
        $this->module(['name' => 'Old', 'path' => 'old', 'deleted_at' => now()]);
        $this->module(['name' => 'Users', 'path' => 'users', 'is_protected' => 1]);

        $this->assertSame(['Products', 'Users'], CmsModul::query()->notDeleted()->orderBy('id')->pluck('name')->all());
        $this->assertSame(['Products', 'Old'], CmsModul::query()->unprotected()->orderBy('id')->pluck('name')->all());
    }

    public function test_for_path_and_for_table(): void
    {
        $this->module(['name' => 'Products', 'path' => 'products', 'table_name' => 'shop_products']);

        $this->assertSame('Products', CmsModul::query()->forPath('products')->value('name'));
        $this->assertSame('Products', CmsModul::query()->forTable('shop_products')->value('name'));
        $this->assertFalse(CmsModul::query()->forPath('missing')->exists());
    }

    public function test_routable_needs_a_path_and_a_controller(): void
    {
        $this->module(['name' => 'Full', 'path' => 'full', 'controller' => 'AdminFullController']);
        $this->module(['name' => 'No controller', 'path' => 'nc', 'controller' => '']);
        $this->module(['name' => 'Null controller', 'path' => 'nl', 'controller' => null]);
        $this->module(['name' => 'No path', 'path' => '', 'controller' => 'AdminNoPathController']);

        $this->assertSame(['Full'], CmsModul::query()->routable()->pluck('name')->all());
    }

    private function module(array $attributes): CmsModul
    {
        return CmsModul::query()->create($attributes + ['controller' => 'AdminTestController', 'table_name' => 'test', 'is_protected' => 0, 'is_active' => 1]);
    }
}
