<?php

namespace crocodicstudio\crudbooster\tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ModuleGeneratorTest extends FeatureTestCase
{
    private $controller;

    protected function setUp(): void
    {
        parent::setUp();

        if (! is_dir(base_path('app/Http/Controllers'))) {
            mkdir(base_path('app/Http/Controllers'), 0777, true);
        }
        $this->controller = base_path('app/Http/Controllers/AdminProductsController.php');
        @unlink($this->controller);
    }

    protected function tearDown(): void
    {
        @unlink($this->controller);
        Schema::dropIfExists('products');

        parent::tearDown();
    }

    public function test_generate_module_and_use_it()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('description')->nullable();
            $table->integer('price')->nullable();
            $table->timestamps();
        });

        $this->loginAsAdmin();

        $this->post($this->admin('module_generator/step2'), [
            'name' => 'Products',
            'table' => 'products',
            'path' => 'products',
            'icon' => 'fa fa-cube',
            'create_menu' => 1,
        ])->assertRedirect();

        $this->assertFileExists($this->controller);
        $module = DB::table('cms_moduls')->where('path', 'products')->first();
        $this->assertSame('AdminProductsController', $module->controller);
        $this->assertDatabaseHas('cms_menus', ['name' => 'Products', 'path' => 'AdminProductsControllerGetIndex']);

        $generated = file_get_contents($this->controller);
        $this->assertMatchesRegularExpression('/\$this->table\s*=\s*"products";/', $generated);
        $this->assertStringContainsString('array("label"=>"Price","name"=>"price" )', $generated);

        foreach (["step2/{$module->id}", "step3/{$module->id}", "step4/{$module->id}"] as $step) {
            $this->get($this->admin("module_generator/$step"))->assertOk();
        }

        require_once $this->controller;
        $this->registerCrudboosterRoutes();

        $this->get($this->admin('products'))->assertOk();
        $this->get($this->admin('products/add'))->assertOk()->assertSee('name="name"', false);

        $this->post($this->admin('products/add-save'), ['name' => 'Widget', 'description' => 'Small', 'price' => 10])->assertRedirect();
        $id = DB::table('products')->where('name', 'Widget')->value('id');
        $this->assertNotNull($id, 'Generated module did not save');

        $this->get($this->admin('products'))->assertOk()->assertSee('Widget');
        $this->get($this->admin("products/detail/$id"))->assertOk()->assertSee('Small');
    }
}
