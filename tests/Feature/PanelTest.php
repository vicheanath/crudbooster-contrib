<?php

namespace crocodicstudio\crudbooster\tests\Feature;

use crocodicstudio\crudbooster\tests\fixtures\Panel\Category;
use crocodicstudio\crudbooster\tests\fixtures\Panel\FeedbackResource;
use crocodicstudio\crudbooster\tests\fixtures\Panel\Product;
use crocodicstudio\crudbooster\tests\fixtures\Panel\ProductResource;
use crocodicstudio\crudbooster\tests\fixtures\Panel\SalesReport;
use crocodicstudio\crudbooster\tests\fixtures\Panel\ShopPlugin;
use crocodicstudio\crudbooster\tests\fixtures\Panel\Tag;
use CrudBooster\Panel\Panel;
use CrudBooster\Panel\RouteRegistrar;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;

/**
 * The resource and page layer: one Eloquent resource (products), a read-only one (categories),
 * a custom page and a plugin that registers them.
 */
class PanelTest extends FeatureTestCase
{
    private ShopPlugin $plugin;

    protected function afterRefreshingDatabase()
    {
        parent::afterRefreshingDatabase();
        $this->createTables();

        View::addNamespace('panel-fixtures', __DIR__.'/../fixtures/Panel/views');
        ProductResource::$calls = [];
        $this->plugin = new ShopPlugin;
        app(Panel::class)->plugin($this->plugin)->page(SalesReport::class)->resource(FeedbackResource::class);
        app(RouteRegistrar::class)->register();
    }

    private function createTables(): void
    {
        Schema::create('panel_categories', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
        });
        Schema::create('panel_tags', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
        });
        Schema::create('panel_products', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('code')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->unsignedInteger('category_id');
            $table->string('status')->default('draft');
            $table->boolean('is_active')->default(true);
            $table->date('released_on')->nullable();
            $table->string('photo')->nullable();
            $table->timestamps();
        });
        Schema::create('panel_product_tag', function (Blueprint $table) {
            $table->unsignedInteger('product_id');
            $table->unsignedInteger('tag_id');
        });

        // See CreatesProductTables: MySQL commits the test transaction on DDL.
        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true) && DB::transactionLevel() > 0) {
            DB::rollBack();
            DB::beginTransaction();
            RefreshDatabaseState::$migrated = false;
        }
    }

    protected function seedProducts(): array
    {
        $tools = Category::query()->forceCreate(['name' => 'Tools']);
        $toys = Category::query()->forceCreate(['name' => 'Toys']);
        $red = Tag::query()->forceCreate(['name' => 'Red']);
        Tag::query()->forceCreate(['name' => 'Blue']);

        $hammer = Product::query()->forceCreate(['name' => 'Hammer', 'price' => 12.5, 'category_id' => $tools->id, 'status' => 'published', 'is_active' => true]);
        $anvil = Product::query()->forceCreate(['name' => 'Anvil', 'price' => 1250, 'category_id' => $tools->id, 'status' => 'draft', 'is_active' => false]);
        $yoyo = Product::query()->forceCreate(['name' => 'Yo-yo', 'price' => 3, 'category_id' => $toys->id, 'status' => 'draft', 'is_active' => true]);
        $hammer->tags()->attach($red);

        return compact('tools', 'toys', 'red', 'hammer', 'anvil', 'yoyo');
    }

    protected function privilegeFor(string $slug, array $permissions): void
    {
        $this->artisan('crudbooster:sync-panel')->assertSuccessful();
        $moduleId = DB::table('cms_moduls')->where('path', $slug)->value('id');
        $privilegeId = DB::table('cms_users')->where('email', 'staff@crudbooster.com')->value('id_cms_privileges');
        DB::table('cms_privileges_roles')->insert(array_merge(
            ['is_visible' => 0, 'is_create' => 0, 'is_read' => 0, 'is_edit' => 0, 'is_delete' => 0],
            $permissions,
            ['id_cms_privileges' => $privilegeId, 'id_cms_moduls' => $moduleId],
        ));
        app(\crocodicstudio\crudbooster\contracts\Security\PrivilegeRolesInterface::class)->refreshSession();
    }

    public function test_guests_are_sent_to_the_login_page()
    {
        $this->get($this->admin('products'))->assertRedirect(url($this->admin('login')));
    }

    public function test_the_index_lists_sorts_and_paginates_with_relationships_eager_loaded()
    {
        $this->seedProducts();
        $this->loginAsAdmin();

        DB::enableQueryLog();
        $response = $this->get($this->admin('products'))->assertOk();
        $productQueries = collect(DB::getQueryLog())->filter(fn ($query) => str_contains($query['query'], 'panel_'));
        $this->assertLessThanOrEqual(5, $productQueries->count(), 'Relationships are not eager loaded');

        // Sorted by name, two per page.
        $response->assertSeeInOrder(['Anvil', 'Hammer'])->assertDontSee('Yo-yo')
            ->assertSee('$1,250.00')->assertSee('Tools')
            ->assertSee($this->badgeMarkup('secondary'), false)
            ->assertSee('id="shop-hook"', false)
            ->assertSee('Showing 1 to 2 of 3');

        $this->get($this->admin('products?page=2'))->assertSee('Yo-yo')->assertDontSee('Anvil');
        $this->get($this->admin('products?sort=price&direction=desc'))->assertSeeInOrder(['Anvil', 'Hammer']);
        $this->get($this->admin('products?per_page=10'))->assertSee('Yo-yo')->assertSee('Anvil');
        // Unknown page sizes and columns fall back to the defaults.
        $this->get($this->admin('products?per_page=100000&sort=password'))->assertOk()->assertDontSee('Yo-yo');
    }

    public function test_search_covers_attributes_and_relationships_and_filters_narrow_the_list()
    {
        ['toys' => $toys] = $this->seedProducts();
        $this->loginAsAdmin();

        $this->get($this->admin('products?search=ham'))->assertSee('Hammer')->assertDontSee('Anvil');
        $this->get($this->admin('products?search=toys'))->assertSee('Yo-yo')->assertDontSee('Hammer');
        // LIKE wildcards in the search are literal.
        $this->get($this->admin('products?search=%25'))->assertDontSee('Hammer');

        $this->get($this->admin('products?filters[status]=draft&per_page=10'))->assertSee('Anvil')->assertSee('Yo-yo')->assertDontSee('Hammer');
        $this->get($this->admin('products?filters[category]='.$toys->id))->assertSee('Yo-yo')->assertDontSee('Anvil');
        $this->get($this->admin('products?filters[is_active]=0'))->assertSee('Anvil')->assertDontSee('Hammer');
    }

    public function test_creating_a_record_validates_saves_syncs_relationships_and_runs_hooks()
    {
        ['tools' => $tools, 'red' => $red] = $this->seedProducts();
        $this->loginAsAdmin();

        $this->get($this->admin('products/create'))->assertOk()
            ->assertSee('name="name"', false)
            ->assertSee('name="tags[]"', false)
            ->assertSee('Publishing')
            ->assertDontSee('name="photo"', false);

        // Required, unique, and options / relationships that do not exist.
        $this->post($this->admin('products'), ['name' => 'Hammer', 'category_id' => 999, 'status' => 'deleted', 'tags' => [999]])
            ->assertSessionHasErrors(['name', 'category_id', 'status', 'tags.0']);
        $this->assertSame(3, Product::query()->count());

        $this->post($this->admin('products'), [
            'name' => 'Saw',
            'price' => '19.90',
            'category_id' => $tools->id,
            'status' => 'draft',
            'is_active' => '1',
            'tags' => [$red->id],
            'released_on' => '2026-01-31',
            'photo' => UploadedFile::fake()->image('ignored.jpg'),
            'created_at' => '2000-01-01',
        ])->assertRedirect(url($this->admin('products')))->assertSessionHas('message_type', 'success');

        $saw = Product::query()->where('name', 'Saw')->firstOrFail();
        $this->assertEquals(19.9, (float) $saw->price);
        $this->assertSame('SAW', $saw->code, 'beforeCreate() changes the saved attributes');
        $this->assertTrue($saw->is_active);
        $this->assertSame('2026-01-31', $saw->released_on->format('Y-m-d'));
        $this->assertSame([$red->id], $saw->tags->modelKeys());
        $this->assertNull($saw->photo, 'Fields hidden on create are not saved');
        $this->assertNotSame('2000-01-01', $saw->created_at->format('Y-m-d'), 'Attributes outside the form are not saved');
        $this->assertSame(['beforeCreate', 'afterCreate:1'], ProductResource::$calls, 'afterCreate() runs after relationships are saved');
        $this->assertDatabaseHas('cms_logs', ['description' => 'Add New Data Saw at Products']);

        $this->post($this->admin('products'), ['name' => 'Drill', 'category_id' => $tools->id, 'status' => 'draft', '_after' => 'create'])
            ->assertRedirect(url($this->admin('products/create')));
    }

    public function test_editing_a_record_updates_it_and_uploads_files()
    {
        Storage::fake('public');
        ['hammer' => $hammer, 'toys' => $toys] = $this->seedProducts();
        $this->loginAsAdmin();

        $this->get($this->admin("products/{$hammer->id}/edit"))->assertOk()
            ->assertSee('value="Hammer"', false)
            ->assertSee('name="photo"', false)
            ->assertSee('enctype="multipart/form-data"', false);

        $return = url($this->admin('products?search=ham'));
        $this->put($this->admin("products/{$hammer->id}"), [
            'name' => 'Hammer', // unique ignores the record itself
            'category_id' => $toys->id,
            'status' => 'published',
            'tags' => '',
            'photo' => UploadedFile::fake()->image('hammer.jpg'),
            '_return' => $return,
        ])->assertRedirect($return);

        $hammer->refresh();
        $this->assertSame($toys->id, $hammer->category_id);
        $this->assertFalse($hammer->is_active, 'An unchecked toggle saves false');
        $this->assertSame([], $hammer->tags->modelKeys(), 'Clearing a many-to-many select detaches every record');
        $this->assertStringStartsWith('products/', $hammer->photo);
        Storage::disk('public')->assertExists($hammer->photo);
        $this->assertSame(['beforeUpdate'], ProductResource::$calls);

        // A script is not an image, whatever it is called.
        $this->put($this->admin("products/{$hammer->id}"), [
            'name' => 'Hammer', 'category_id' => $toys->id, 'status' => 'draft',
            'photo' => UploadedFile::fake()->createWithContent('x.php', '<?php echo 1;'),
        ])->assertSessionHasErrors('photo');

        // An empty file input keeps the file; "Remove" clears it.
        $this->put($this->admin("products/{$hammer->id}"), ['name' => 'Hammer', 'category_id' => $toys->id, 'status' => 'draft']);
        $this->assertNotNull($hammer->refresh()->photo);
        $this->put($this->admin("products/{$hammer->id}"), ['name' => 'Hammer', 'category_id' => $toys->id, 'status' => 'draft', 'photo__remove' => '1']);
        $this->assertNull($hammer->refresh()->photo);

        // An external return URL is ignored.
        $this->put($this->admin("products/{$hammer->id}"), ['name' => 'Hammer', 'category_id' => $toys->id, 'status' => 'draft', '_return' => 'https://evil.test'])
            ->assertRedirect(url($this->admin('products')));
    }

    public function test_detail_and_delete()
    {
        ['hammer' => $hammer] = $this->seedProducts();
        $this->loginAsAdmin();

        $this->get($this->admin("products/{$hammer->id}"))->assertOk()
            ->assertSee('Hammer')->assertSee('Red')->assertSee('$12.50');

        $this->delete($this->admin("products/{$hammer->id}"))->assertRedirect(url($this->admin('products')));
        $this->assertModelMissing($hammer);
        $this->assertSame(['afterDelete:Hammer'], ProductResource::$calls);
        $this->get($this->admin("products/{$hammer->id}"))->assertNotFound();
    }

    public function test_row_actions_run_on_the_server_and_respect_their_visibility()
    {
        ['hammer' => $hammer, 'anvil' => $anvil] = $this->seedProducts();
        $this->loginAsAdmin();

        $this->get($this->admin('products'))
            ->assertSee(url($this->admin("products/{$anvil->id}/actions/publish")), false)
            ->assertDontSee(url($this->admin("products/{$hammer->id}/actions/publish")), false)
            ->assertSee('https://shop.test/p/'.$anvil->id, false);

        $this->post($this->admin("products/{$anvil->id}/actions/publish"))->assertSessionHas('message', 'Published.');
        $this->assertSame('published', $anvil->refresh()->status);

        // Hidden for a published record, so it cannot be posted either.
        $this->post($this->admin("products/{$hammer->id}/actions/publish"))->assertForbidden();
        $this->post($this->admin("products/{$hammer->id}/actions/preview"))->assertNotFound();
        $this->post($this->admin("products/{$hammer->id}/actions/missing"))->assertNotFound();
    }

    public function test_bulk_actions_and_bulk_delete()
    {
        ['hammer' => $hammer, 'anvil' => $anvil, 'yoyo' => $yoyo] = $this->seedProducts();
        $this->loginAsAdmin();

        $this->post($this->admin('products/bulk/deactivate'), ['records' => [$hammer->id, $yoyo->id]])->assertSessionHas('message', 'Deactivated.');
        $this->assertFalse($hammer->refresh()->is_active);
        $this->assertFalse($yoyo->refresh()->is_active);

        $this->post($this->admin('products/bulk/delete'), ['records' => [$hammer->id, $anvil->id]])->assertSessionHas('message', '2 records were deleted.');
        $this->assertSame(['Yo-yo'], Product::query()->pluck('name')->all());
        $this->post($this->admin('products/bulk/missing'), ['records' => [$yoyo->id]])->assertNotFound();
    }

    public function test_pages_left_out_of_a_resource_have_no_routes_or_buttons()
    {
        $category = Category::query()->forceCreate(['name' => 'Garden']);
        $this->loginAsAdmin();

        $this->get($this->admin('product-categories'))->assertOk()->assertSee('Garden')
            ->assertDontSee('id="cb-create"', false)
            ->assertDontSee("product-categories/{$category->id}/edit", false);
        $this->get($this->admin("product-categories/{$category->id}"))->assertOk();
        $this->get($this->admin("product-categories/{$category->id}/edit"))->assertNotFound();
        $this->post($this->admin('product-categories'), ['name' => 'x'])->assertStatus(405);
    }

    public function test_a_resource_without_a_list_returns_to_the_dashboard()
    {
        $this->loginAsAdmin();

        $this->get($this->admin('feedback/create'))->assertOk()->assertSee('href="'.url($this->admin()).'"', false);
        $this->post($this->admin('feedback'), ['name' => 'Great', '_return' => url($this->admin('feedback/create'))])
            ->assertRedirect(url($this->admin()));
        $this->assertDatabaseHas('panel_tags', ['name' => 'Great']);
        $this->get($this->admin('feedback'))->assertStatus(405);
    }

    public function test_standard_admins_need_a_privilege_on_the_resource()
    {
        ['hammer' => $hammer, 'tools' => $tools] = $this->seedProducts();
        $this->loginAsStandardAdmin();

        $this->get($this->admin('products'))->assertForbidden();
        $this->get($this->admin('notifications'))->assertOk()->assertDontSee('fa fa-cube', false);

        $this->privilegeFor('products', ['is_visible' => 1, 'is_read' => 1]);

        $this->get($this->admin('products'))->assertOk()
            ->assertSee('fa fa-cube', false)
            ->assertDontSee('id="cb-create"', false)
            ->assertDontSee("products/{$hammer->id}/edit", false);
        $this->get($this->admin('products/create'))->assertForbidden();
        $this->put($this->admin("products/{$hammer->id}"), ['name' => 'x', 'category_id' => $tools->id, 'status' => 'draft'])->assertForbidden();
        $this->delete($this->admin("products/{$hammer->id}"))->assertForbidden();
        $this->post($this->admin('products/bulk/delete'), ['records' => [$hammer->id]])->assertForbidden();
        $this->post($this->admin("products/{$hammer->id}/actions/publish"))->assertForbidden();
        $this->assertModelExists($hammer);
    }

    public function test_sync_panel_registers_modules_the_privilege_page_lists()
    {
        $this->artisan('crudbooster:sync-panel')->assertSuccessful();
        $this->artisan('crudbooster:sync-panel')->assertSuccessful();

        $this->assertSame(1, DB::table('cms_moduls')->where('path', 'products')->count());
        $this->assertDatabaseHas('cms_moduls', ['path' => 'product-categories', 'table_name' => 'panel_categories', 'controller' => null, 'is_protected' => 0]);
        $this->assertDatabaseHas('cms_moduls', ['path' => 'sales-report', 'name' => 'Sales Report']);

        $this->loginAsAdmin();
        $this->get($this->admin('privileges/add'))->assertOk()->assertSee('Sales Report');
    }

    public function test_a_model_policy_takes_precedence_over_privileges()
    {
        ['hammer' => $hammer, 'anvil' => $anvil] = $this->seedProducts();
        Gate::policy(Product::class, ProductPolicy::class);
        $this->loginAsAdmin();

        // Even a superadmin may not delete what the policy forbids.
        $this->delete($this->admin("products/{$hammer->id}"))->assertForbidden();
        $this->delete($this->admin("products/{$anvil->id}"))->assertRedirect();
        $this->assertModelExists($hammer);
        $this->assertModelMissing($anvil);
    }

    public function test_custom_pages_inject_their_data_and_accept_submissions()
    {
        $this->seedProducts();
        $this->loginAsAdmin();

        $this->get($this->admin('sales-report'))->assertOk()
            ->assertSee('Products in stock: 3 (Sales Report)')
            ->assertSee('fa fa-line-chart', false);

        $this->from($this->admin('sales-report'))->post($this->admin('sales-report'), [])->assertSessionHasErrors('note');
        $this->from($this->admin('sales-report'))->post($this->admin('sales-report'), ['note' => '<b>ok</b>'])
            ->assertSessionHas('message', 'Saved &lt;b&gt;ok&lt;/b&gt;');

        $this->loginAsStandardAdmin();
        $this->get($this->admin('sales-report'))->assertForbidden();
    }

    public function test_the_sidebar_groups_panel_links_after_the_classic_menus()
    {
        $this->loginAsAdmin();

        $this->get($this->admin('products'))->assertOk()
            ->assertSeeInOrder(['Categories', 'Sales Report', 'Shop', 'Products', 'Storefront', cbLang('SUPERADMIN')])
            ->assertSee($this->activeNavigationMarkup(url($this->admin('products'))), false)
            ->assertSee('target="_blank"', false);

        $this->assertTrue($this->plugin->booted, 'A plugin added after boot is booted at once');
        $this->assertSame(ProductResource::class, app(Panel::class)->getResourceForModel(Product::class));
    }

    public function test_classic_modules_render_as_before()
    {
        $this->loginAsAdmin();

        $this->get($this->admin('users'))->assertOk()->assertSee("id='btn_add_new_data'", false);
    }

    /**
     * Markup the theme under test renders for a badge of this colour.
     */
    protected function badgeMarkup(string $color): string
    {
        return 'text-bg-'.$color;
    }

    protected function activeNavigationMarkup(string $url): string
    {
        return 'href="'.$url.'" class="nav-link active"';
    }

    public function test_duplicate_slugs_are_rejected()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('both use the slug "products"');

        app(Panel::class)->resource(DuplicateProductResource::class)->assertUniqueSlugs();
    }
}

class ProductPolicy
{
    public function viewAny($user): bool
    {
        return true;
    }

    public function delete($user, Product $product): bool
    {
        return $user->email === 'admin@crudbooster.com' && $product->status === 'draft';
    }
}

class DuplicateProductResource extends ProductResource
{
}
