<?php

namespace crocodicstudio\crudbooster\tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Tables for the product modules in tests/fixtures: categories, tags (many-to-many through
 * cb_product_tags) and variants (a child table).
 */
trait CreatesProductTables
{
    protected function createProductTables(): void
    {
        Schema::create('cb_categories', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
        });
        Schema::create('cb_tags', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
        });
        Schema::create('cb_products', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->integer('price')->nullable();
            $table->integer('id_cb_categories')->default(0);
            $table->string('colors')->nullable();
            $table->text('aliases')->nullable();
            $table->string('address')->nullable();
            $table->string('lat')->nullable();
            $table->string('lng')->nullable();
            $table->string('password')->nullable();
            $table->text('note');
            $table->string('code')->default('');
            $table->string('photo')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('cb_product_tags', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('cb_products_id');
            $table->integer('cb_tags_id');
        });
        Schema::create('cb_variants', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('cb_products_id');
            $table->string('sku');
            $table->integer('qty')->nullable();
        });

        // MySQL commits the open test transaction on DDL. Begin it again so the test still runs inside one
        // (rolled back afterwards) and DB::transaction() in the code under test gets a real savepoint. The
        // tables themselves stay committed, so the next test rebuilds the database, as Laravel does when it
        // finds the transaction gone.
        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true) && DB::transactionLevel() > 0) {
            DB::rollBack();
            DB::beginTransaction();
            RefreshDatabaseState::$migrated = false;
        }
    }

    protected function registerModule(string $name, string $path, string $controller): void
    {
        DB::table('cms_moduls')->insert(['name' => $name, 'path' => $path, 'table_name' => 'cb_products', 'controller' => $controller, 'is_protected' => 0, 'is_active' => 1]);
        $this->registerCrudboosterRoutes();
    }

    private function seedLookups(): array
    {
        $categories = [
            'tools' => DB::table('cb_categories')->insertGetId(['name' => 'Tools']),
            'toys' => DB::table('cb_categories')->insertGetId(['name' => 'Toys']),
        ];
        $tags = [
            'new' => DB::table('cb_tags')->insertGetId(['name' => 'New']),
            'sale' => DB::table('cb_tags')->insertGetId(['name' => 'Sale']),
        ];

        return [$categories, $tags];
    }

    private function product(array $overrides = []): int
    {
        return DB::table('cb_products')->insertGetId($overrides + ['name' => 'Hammer', 'price' => 10, 'note' => '']);
    }
}
