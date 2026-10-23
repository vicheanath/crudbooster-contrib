<?php

namespace crocodicstudio\crudbooster\tests\Feature;

use crocodicstudio\crudbooster\tests\TestCase;
use CRUDBooster;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Pins the exact source the module and API generators write, since users keep and edit those files.
 * Set CB_UPDATE_GOLDEN=1 to rewrite the fixtures after an intended change.
 */
class CodeGeneratorTest extends TestCase
{
    private string $controllers;

    protected function setUp(): void
    {
        parent::setUp();

        $this->controllers = base_path('app/Http/Controllers/');
        if (! is_dir($this->controllers)) {
            mkdir($this->controllers, 0777, true);
        }
        $this->cleanUp();

        Schema::create('gen_customers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('fullname');
        });

        // One column for each branch of the generator's column and form rules.
        Schema::create('gen_orders', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->unsignedInteger('gen_customers_id');
            $table->unsignedInteger('id_cms_users');
            $table->boolean('is_paid');
            $table->string('email');
            $table->string('photo');
            $table->string('password');
            $table->string('phone');
            $table->string('link');
            $table->text('note');
            $table->double('total');
            $table->integer('qty');
            $table->date('ship_date');
            $table->dateTime('shipped_at');
            $table->time('ship_time');
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    protected function tearDown(): void
    {
        $this->cleanUp();
        Schema::dropIfExists('gen_orders');
        Schema::dropIfExists('gen_customers');

        parent::tearDown();
    }

    public function test_module_controller_source_is_unchanged(): void
    {
        $this->assertSame('AdminGenOrdersController', CRUDBooster::generateController('gen_orders', 'gen_orders'));
        $this->assertGolden('AdminGenOrdersController.php');

        // A second module on the same table gets a numbered controller instead of overwriting the first.
        $this->assertSame('AdminGenOrders1Controller', CRUDBooster::generateController('gen_orders', 'gen_orders'));
        $this->assertSame('AdminGenOrders2Controller', CRUDBooster::generateController('gen_orders'));
    }

    public function test_api_controller_source_is_unchanged(): void
    {
        CRUDBooster::generateAPI('GenOrdersList', 'gen_orders', 'gen_orders_list', 'get');

        $this->assertGolden('ApiGenOrdersListController.php');
    }

    private function assertGolden(string $file): void
    {
        $golden = __DIR__.'/../fixtures/generated/'.$file.'.txt';
        $actual = file_get_contents($this->controllers.$file);

        if (getenv('CB_UPDATE_GOLDEN')) {
            @mkdir(dirname($golden), 0777, true);
            file_put_contents($golden, $actual);
        }

        $this->assertSame(file_get_contents($golden), $actual);
    }

    private function cleanUp(): void
    {
        foreach (['AdminGenOrdersController', 'AdminGenOrders1Controller', 'AdminGenOrders2Controller', 'ApiGenOrdersListController'] as $class) {
            @unlink($this->controllers.$class.'.php');
        }
    }
}
