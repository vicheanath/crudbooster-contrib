<?php

namespace crocodicstudio\crudbooster\tests\Feature;

use App\Http\Controllers\AdminCbProductsController;
use crocodicstudio\crudbooster\actions\IndexAction;
use crocodicstudio\crudbooster\contracts\Export\ExporterInterface;
use crocodicstudio\crudbooster\contracts\Forms\FormProcessorInterface;
use crocodicstudio\crudbooster\contracts\Forms\FormValidatorInterface;
use crocodicstudio\crudbooster\contracts\Grid\GridQueryBuilderInterface;
use crocodicstudio\crudbooster\contracts\Security\PermissionCheckerInterface;
use crocodicstudio\crudbooster\controllers\CBController;
use crocodicstudio\crudbooster\export\ExporterRegistry;
use crocodicstudio\crudbooster\forms\FieldHandlerRegistry;
use crocodicstudio\crudbooster\forms\FormContext;
use crocodicstudio\crudbooster\forms\handlers\BaseFieldHandler;
use crocodicstudio\crudbooster\forms\handlers\TextFieldHandler;
use crocodicstudio\crudbooster\modern\CrudDefinition;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class ExtensibilityTest extends FeatureTestCase
{
    use CreatesProductTables;

    protected function afterRefreshingDatabase()
    {
        $this->createProductTables();
        parent::afterRefreshingDatabase();

        require_once __DIR__.'/../fixtures/AdminCbProductsController.php';
        require_once __DIR__.'/../fixtures/AdminCbFluentProductsController.php';
        $this->registerModule('Products', 'cb_products', 'AdminCbProductsController');
        $this->registerModule('Fluent Products', 'cb_fluent_products', 'AdminCbFluentProductsController');
    }

    public function test_custom_field_types_are_registered_without_touching_the_controller()
    {
        app(FieldHandlerRegistry::class)->register('slug', new class extends BaseFieldHandler
        {
            protected function normalize(array $field, mixed $value): mixed
            {
                return str_slug((string) $value);
            }
        });

        request()->merge(['title' => 'Hello World', 'body' => 'Text']);
        $payload = [];
        app(FormProcessorInterface::class)->assign([
            ['name' => 'title', 'type' => 'slug'],
            ['name' => 'body', 'type' => 'textarea'],
        ], $payload, new FormContext('cb_products'));

        $this->assertSame(['title' => 'hello-world', 'body' => 'Text'], $payload);
        $this->assertInstanceOf(TextFieldHandler::class, app(FieldHandlerRegistry::class)->resolve('textarea'));
    }

    public function test_handlers_added_by_supports_claim_their_types()
    {
        $handler = new class extends TextFieldHandler
        {
            protected array $types = ['color', 'colour'];

            protected function normalize(array $field, mixed $value): mixed
            {
                return strtoupper((string) $value);
            }
        };
        app(FieldHandlerRegistry::class)->add($handler);

        $this->assertSame($handler, app(FieldHandlerRegistry::class)->resolve('colour'));
        $this->assertInstanceOf(TextFieldHandler::class, app(FieldHandlerRegistry::class)->resolve('unknown'));
    }

    public function test_grid_query_builder_builds_the_index_query_without_http()
    {
        DB::table('cb_categories')->insert(['id' => 1, 'name' => 'Tools']);
        DB::table('cb_products')->insert([
            ['name' => 'Hammer', 'price' => 10, 'note' => '', 'id_cb_categories' => 1, 'deleted_at' => null],
            ['name' => 'Anvil', 'price' => 500, 'note' => '', 'id_cb_categories' => 0, 'deleted_at' => null],
            ['name' => 'Gone', 'price' => 1, 'note' => '', 'id_cb_categories' => 1, 'deleted_at' => now()],
        ]);

        $grid = app(GridQueryBuilderInterface::class)->buildQuery('cb_products', 'id', [
            ['label' => 'Name', 'name' => 'name'],
            ['label' => 'Category', 'name' => 'id_cb_categories', 'join' => 'cb_categories,name'],
        ], ['search' => 'tool', 'orderby' => 'cb_products.price,desc']);

        $this->assertSame(['Hammer'], $grid->query->pluck('name')->all());
        $this->assertSame('cb_categories_name', $grid->columns[1]['field']);

        $all = app(GridQueryBuilderInterface::class)->buildQuery('cb_products', 'id', [['label' => 'Name', 'name' => 'name']], ['orderby' => 'price;name,desc']);
        $this->assertSame(['Hammer', 'Anvil'], $all->query->pluck('name')->all());
    }

    public function test_validator_completes_unique_rules()
    {
        $rules = app(FormValidatorInterface::class)->rules(
            [
                ['name' => 'name', 'type' => 'text', 'validation' => 'required|unique'],
                ['name' => 'price', 'type' => 'money', 'required' => true, 'min' => 1],
                ['name' => 'skipped', 'type' => 'text', 'required' => true],
            ],
            ['name' => 'x', 'price' => '5'],
            new FormContext('cb_products', 'id', FormContext::EDIT, 7),
        );

        $this->assertSame([
            'name' => 'required|unique:cb_products,name,7,id,deleted_at,NULL',
            'price' => 'required|min:1',
        ], $rules);
    }

    public function test_custom_export_formats_are_offered_and_used()
    {
        app(ExporterRegistry::class)->register('json', new class implements ExporterInterface
        {
            public function label(): string
            {
                return 'JSON lines';
            }

            public function export(array $data, array $options): Response
            {
                return response()->json(collect($data['result']->items())->pluck('name'));
            }
        });
        DB::table('cb_products')->insert(['name' => 'Exported', 'note' => '']);
        $this->loginAsAdmin();

        $this->get($this->admin('cb_products'))->assertSee('JSON lines')->assertSee("value='csv'", false);

        $this->post($this->admin('cb_products/export-data'), ['fileformat' => 'json', 'filename' => 'x', 'limit' => 10])
            ->assertOk()
            ->assertExactJson(['Exported']);
    }

    public function test_services_and_actions_are_replaced_through_the_container()
    {
        $this->loginAsAdmin();

        $this->app->bind(IndexAction::class, fn () => new class extends IndexAction
        {
            public function __construct()
            {
            }

            public function execute(CBController $controller, mixed ...$arguments): mixed
            {
                return 'custom index of '.$controller->table;
            }
        });
        $this->get($this->admin('cb_products'))->assertOk()->assertSeeText('custom index of cb_products');

        $this->app->bind(PermissionCheckerInterface::class, fn () => new class implements PermissionCheckerInterface
        {
            public function canCreate(): bool { return false; }
            public function canRead(): bool { return false; }
            public function canView(): bool { return false; }
            public function canUpdate(): bool { return false; }
            public function canDelete(): bool { return false; }
        });
        $this->get($this->admin('cb_products/add'))->assertRedirect();
        $this->assertSame(cbLang('denied_access'), session('message'));
        $this->assertFalse(\CRUDBooster::isCreate());
    }

    public function test_fluent_crud_controller_module_works_like_a_classic_one()
    {
        [$categories, $tags] = $this->seedLookups();
        $this->loginAsAdmin();

        $this->post($this->admin('cb_fluent_products/add-save'), [
            'name' => 'Fluent drill',
            'price' => '1,000',
            'id_cb_categories' => $categories['tools'],
            'tags' => [$tags['sale']],
            'note' => 'A very long note',
        ])->assertRedirect()->assertSessionHas('message_type', 'success');

        $row = DB::table('cb_products')->where('name', 'Fluent drill')->first();
        $this->assertSame(1000, (int) $row->price);
        $this->assertSame('FLUENT', $row->code);
        $this->assertSame([$tags['sale']], DB::table('cb_product_tags')->where('cb_products_id', $row->id)->pluck('cb_tags_id')->map(fn ($id) => (int) $id)->all());

        // The bare "unique" rule checks the module's table.
        $this->post($this->admin('cb_fluent_products/add-save'), ['name' => 'Fluent drill', 'note' => 'x'])
            ->assertSessionHas('message_type', 'warning');

        $this->get($this->admin('cb_fluent_products'))->assertOk()
            ->assertSee('Fluent drill')->assertSee('Tools')->assertSee('A very l...')
            ->assertSee('1. ', false)
            ->assertDontSee("id='btn_export_data'", false)
            ->assertDontSee("id='btn_import_data'", false);

        $controller = new \App\Http\Controllers\AdminCbFluentProductsController;
        $controller->cbInit();
        $config = $controller->crudConfig();
        $this->assertSame('cb_products', $config->table);
        $this->assertFalse($config->buttons['export']);
        $this->assertSame(['price' => 'desc'], $config->orderby);
    }

    public function test_crud_definition_rejects_unknown_buttons_and_properties()
    {
        $this->expectException(\InvalidArgumentException::class);
        (new CrudDefinition)->withoutButtons('print');
    }

    public function test_upload_fields_read_each_requests_file()
    {
        $this->loginAsAdmin();

        foreach (['first', 'second'] as $name) {
            $this->post($this->admin('cb_products/add-save'), [
                'name' => ucfirst($name),
                'note' => 'n',
                'photo' => \Illuminate\Http\UploadedFile::fake()->image($name.'.png', 20, 20),
            ])->assertRedirect()->assertSessionHas('message_type', 'success');
        }

        $photos = DB::table('cb_products')->orderBy('id')->pluck('photo')->all();
        $this->assertStringEndsWith('/first.png', $photos[0]);
        $this->assertStringEndsWith('/second.png', $photos[1]);
        $this->assertFileExists(storage_path('app/'.$photos[1]));
    }

    public function test_hide_form_input_never_unserializes_objects()
    {
        $this->loginAsAdmin();

        $this->post($this->admin('cb_products/add-save'), [
            'name' => 'Safe',
            'note' => 'n',
            'colors' => ['Red'],
            'hide_form' => serialize(new \ArrayObject(['colors'])),
        ])->assertRedirect();

        $this->assertDatabaseHas('cb_products', ['name' => 'Safe', 'colors' => 'Red']);
    }

    public function test_logs_get_a_timestamp_when_debug_logging_is_on()
    {
        DB::table('cms_settings')->updateOrInsert(['name' => 'api_debug_mode'], ['content' => 'true']);
        \Illuminate\Support\Facades\Cache::forget('setting_api_debug_mode');

        \CRUDBooster::insertLog('Something happened', null);

        $log = DB::table('cms_logs')->where('description', 'Something happened')->first();
        $this->assertNotNull($log);
        $this->assertNotNull($log->created_at);
    }
}
