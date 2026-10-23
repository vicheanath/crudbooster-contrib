<?php

namespace crocodicstudio\crudbooster\tests\Feature;

use App\Http\Controllers\AdminCbProductsController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Characterization tests: they pin down what a classic CBController module does today
 * (field handling, grid queries, hooks, deletes, export) so the SOLID refactor keeps it.
 */
class CrudBehaviourTest extends FeatureTestCase
{
    use CreatesProductTables;

    protected function afterRefreshingDatabase()
    {
        $this->createProductTables();
        parent::afterRefreshingDatabase();

        require_once __DIR__.'/../fixtures/AdminCbProductsController.php';
        $this->registerModule('Products', 'cb_products', 'AdminCbProductsController');
        AdminCbProductsController::$calls = [];
    }

    public function test_add_save_assigns_every_field_type_and_runs_hooks_in_order()
    {
        [$categories, $tags] = $this->seedLookups();
        $this->loginAsAdmin();

        $this->post($this->admin('cb_products/add-save'), [
            'name' => 'Drill',
            'price' => '1,250',
            'id_cb_categories' => '',
            'colors' => ['Red', 'Blue'],
            'tags' => [$tags['new'], $tags['sale']],
            'aliases' => ['driver', 'borer'],
            'address' => 'Main street',
            'input-latitude-address' => '11.5',
            'input-longitude-address' => '104.9',
            'password' => 'secret',
            'note' => '',
            'code' => '',
            'confirm' => 'ignored',
            'variants-sku' => ['D-1', 'D-2'],
            'variants-qty' => ['3', '4'],
        ])->assertRedirect()->assertSessionHas('message_type', 'success');

        $row = DB::table('cb_products')->where('name', 'Drill')->first();
        $this->assertNotNull($row);
        $this->assertSame(1250, (int) $row->price);
        $this->assertSame(0, (int) $row->id_cb_categories);
        $this->assertSame('Red;Blue', $row->colors);
        $this->assertSame('driver|borer', $row->aliases);
        $this->assertSame('Main street', $row->address);
        $this->assertSame('11.5', $row->lat);
        $this->assertSame('104.9', $row->lng);
        $this->assertTrue(Hash::check('secret', $row->password));
        $this->assertSame('[added]', $row->note);
        $this->assertSame('', $row->code);
        $this->assertNull($row->photo);
        $this->assertNotNull($row->created_at);

        $this->assertEqualsCanonicalizing([$tags['new'], $tags['sale']], DB::table('cb_product_tags')->where('cb_products_id', $row->id)->pluck('cb_tags_id')->map('intval')->all());
        $this->assertSame(['D-1' => 3, 'D-2' => 4], DB::table('cb_variants')->where('cb_products_id', $row->id)->pluck('qty', 'sku')->map(fn ($qty) => (int) $qty)->all());

        $this->assertSame(['hook_before_add', 'hook_after_add:'.$row->id], AdminCbProductsController::$calls);
    }

    public function test_add_save_skips_hidden_form_fields()
    {
        $this->loginAsAdmin();

        $this->post($this->admin('cb_products/add-save'), [
            'name' => 'Saw',
            'colors' => ['Red'],
            'note' => 'n',
            'hide_form' => serialize(['colors']),
        ])->assertRedirect();

        $this->assertDatabaseHas('cb_products', ['name' => 'Saw', 'colors' => null, 'note' => 'n[added]']);
        // A child field submitted without rows adds no child rows.
        $this->assertSame(0, DB::table('cb_variants')->count());
    }

    public function test_edit_save_keeps_empty_password_and_existing_upload_and_replaces_children()
    {
        [$categories, $tags] = $this->seedLookups();
        $id = $this->product(['password' => Hash::make('old'), 'photo' => 'uploads/1/old.jpg', 'id_cb_categories' => $categories['tools']]);
        DB::table('cb_product_tags')->insert(['cb_products_id' => $id, 'cb_tags_id' => $tags['new']]);
        DB::table('cb_variants')->insert(['cb_products_id' => $id, 'sku' => 'OLD', 'qty' => 1]);
        $this->loginAsAdmin();

        $this->post($this->admin("cb_products/edit-save/$id"), [
            'name' => 'Hammer XL',
            'price' => '20',
            'id_cb_categories' => $categories['toys'],
            'tags' => [$tags['sale']],
            'password' => '',
            'note' => 'edited',
            '_photo' => 'uploads/1/old.jpg',
            'variants-sku' => ['NEW-1'],
            'variants-qty' => ['9'],
        ])->assertRedirect()->assertSessionHas('message_type', 'success');

        $row = DB::table('cb_products')->find($id);
        $this->assertSame('Hammer XL', $row->name);
        $this->assertSame($categories['toys'], (int) $row->id_cb_categories);
        $this->assertTrue(Hash::check('old', $row->password));
        $this->assertSame('uploads/1/old.jpg', $row->photo);
        $this->assertSame('edited', $row->note);
        $this->assertNotNull($row->updated_at);
        $this->assertSame([$tags['sale']], DB::table('cb_product_tags')->where('cb_products_id', $id)->pluck('cb_tags_id')->map('intval')->all());
        $this->assertSame(['NEW-1'], DB::table('cb_variants')->where('cb_products_id', $id)->pluck('sku')->all());

        $this->assertSame(['hook_before_edit:'.$id, 'hook_after_edit:'.$id], AdminCbProductsController::$calls);
    }

    public function test_validation_rejects_duplicates_but_ignores_the_edited_row()
    {
        $id = $this->product(['name' => 'Hammer']);
        $this->loginAsAdmin();

        $this->from($this->admin('cb_products/add'))
            ->post($this->admin('cb_products/add-save'), ['name' => 'Hammer', 'note' => 'x'])
            ->assertRedirect($this->admin('cb_products/add'))
            ->assertSessionHas('message_type', 'warning');
        $this->assertSame(1, DB::table('cb_products')->where('name', 'Hammer')->count());

        $this->post($this->admin("cb_products/edit-save/$id"), ['name' => 'Hammer', 'note' => 'same name'])
            ->assertSessionHas('message_type', 'success');

        $this->post($this->admin('cb_products/add-save'), ['name' => 'ab', 'note' => 'x'], ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk()
            ->assertJson(['message_type' => 'warning']);
    }

    public function test_index_lists_joins_subqueries_callbacks_and_hides_soft_deleted_rows()
    {
        [$categories, $tags] = $this->seedLookups();
        $hammer = $this->product(['name' => 'Hammer', 'price' => 10, 'id_cb_categories' => $categories['tools']]);
        $this->product(['name' => 'Anvil', 'price' => 500]);
        $this->product(['name' => 'Discontinued drill', 'price' => 50]);
        $this->product(['name' => 'Ghost', 'deleted_at' => now()]);
        DB::table('cb_product_tags')->insert([['cb_products_id' => $hammer, 'cb_tags_id' => $tags['new']], ['cb_products_id' => $hammer, 'cb_tags_id' => $tags['sale']]]);
        $this->loginAsAdmin();

        $response = $this->get($this->admin('cb_products'))->assertOk();
        $response->assertSeeInOrder(['Anvil', 'Discontinued drill (EOL)', 'Hammer'], false);
        $response->assertSee('Tools')->assertSee('USD 10')->assertDontSee('Ghost');
        $this->assertContains('hook_query_index', AdminCbProductsController::$calls);

        $this->get($this->admin('cb_products?q=ham'))->assertSee('Hammer')->assertDontSee('Anvil');
        $this->get($this->admin('cb_products?q=Tools'))->assertSee('Hammer')->assertDontSee('Anvil');
        $this->get($this->admin('cb_products?only_cheap=1'))->assertSee('Hammer')->assertDontSee('Anvil');
        $this->get($this->admin('cb_products?where[price]=500'))->assertSee('Anvil')->assertDontSee('Hammer');

        $this->get($this->admin('cb_products?'.http_build_query(['filter_column' => ['cb_products.price' => ['type' => 'between', 'value' => [40, 600], 'sorting' => 'desc']]])))
            ->assertSeeInOrder(['Anvil', 'Discontinued drill'], false)
            ->assertDontSee('USD 10');
    }

    public function test_index_return_exposes_resolved_column_metadata()
    {
        $this->seedLookups();
        $this->product();
        $this->loginAsAdmin();
        $this->get($this->admin('cb_products'));

        $controller = new AdminCbProductsController;
        $controller->cbLoader();
        $controller->index_return = true;
        $data = $controller->getIndex();

        $columns = collect($data['columns'])->keyBy('label');
        $this->assertSame('name', $columns['Name']['field']);
        $this->assertSame('cb_products.name', $columns['Name']['field_with']);
        $this->assertSame('cb_categories_name', $columns['Category']['field']);
        $this->assertSame('cb_categories.name', $columns['Category']['field_with']);
        $this->assertSame('tag_count', $columns['Tags']['field']);
        $this->assertTrue($columns['Tags']['is_subquery']);
        $this->assertSame(1, $data['result']->total());
    }

    public function test_delete_soft_deletes_and_bulk_actions_reach_hooks()
    {
        $a = $this->product(['name' => 'A1']);
        $b = $this->product(['name' => 'B1']);
        $c = $this->product(['name' => 'C1']);
        $this->loginAsAdmin();

        $this->from($this->admin('cb_products'))->get($this->admin("cb_products/delete/$a"))->assertRedirect();
        $this->assertNotNull(DB::table('cb_products')->find($a)->deleted_at);

        $this->from($this->admin('cb_products'))->post($this->admin('cb_products/action-selected'), ['checkbox' => [$b], 'button_name' => 'delete'])->assertRedirect();
        $this->assertNotNull(DB::table('cb_products')->find($b)->deleted_at);

        $this->from($this->admin('cb_products'))->post($this->admin('cb_products/action-selected'), ['checkbox' => [$c], 'button_name' => 'set_active'])
            ->assertRedirect()->assertSessionHas('message_type', 'success');

        $this->assertSame([
            'hook_before_delete:'.json_encode((string) $a),
            'hook_after_delete:'.json_encode((string) $a),
            'hook_before_delete:'.json_encode([$b]),
            'hook_after_delete:'.json_encode([$b]),
            'action:set_active:'.$c,
        ], AdminCbProductsController::$calls);
    }

    public function test_detail_and_edit_forms_render_the_row()
    {
        $this->seedLookups();
        $id = $this->product(['name' => 'Wrench']);
        $this->loginAsAdmin();

        $this->get($this->admin('cb_products/add'))->assertOk()->assertSee('name="name"', false);
        $this->get($this->admin("cb_products/edit/$id"))->assertOk()->assertSee('Wrench');
        $this->get($this->admin("cb_products/detail/$id"))->assertOk()->assertSee('Wrench');
    }

    public function test_export_csv_contains_the_grid_rows()
    {
        $this->product(['name' => 'Exported']);
        $this->loginAsAdmin();

        $response = $this->post($this->admin('cb_products/export-data'), [
            'fileformat' => 'csv', 'filename' => 'products', 'limit' => 10, 'page_size' => 'A4', 'page_orientation' => 'potrait',
        ])->assertOk();

        $this->assertStringContainsString('Exported', file_get_contents($response->baseResponse->getFile()->getPathname()));
    }

    public function test_lookup_endpoints_return_json()
    {
        [$categories] = $this->seedLookups();
        $this->loginAsAdmin();

        $this->get($this->admin('cb_products/find-data?table1=cb_categories&column1=name&q=too'))
            ->assertOk()
            ->assertJsonPath('items.0.text', 'Tools');

        $this->get($this->admin('cb_products/data-table?table=cb_products&label=name&fk_name=id_cb_categories&fk_value='.$categories['tools']))
            ->assertOk()
            ->assertExactJson([]);

        $this->product(['name' => 'Spanner', 'id_cb_categories' => $categories['tools']]);
        $this->get($this->admin('cb_products/data-table?table=cb_products&label=name&fk_name=id_cb_categories&fk_value='.$categories['tools']))
            ->assertOk()
            ->assertJsonPath('0.select_label', 'Spanner');
    }
}
