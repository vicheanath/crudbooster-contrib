<?php

namespace crocodicstudio\crudbooster\tests\Feature;

use crocodicstudio\crudbooster\tests\fixtures\Panel\Product;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Schema;

class PanelCommandsTest extends FeatureTestCase
{
    protected function tearDown(): void
    {
        $files = new Filesystem;
        $files->deleteDirectory(app_path('Admin'));
        $files->deleteDirectory(resource_path('views/admin/pages'));

        parent::tearDown();
    }

    public function test_make_resource_writes_columns_and_fields_from_the_table()
    {
        Schema::create('panel_products', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 8, 2)->default(0);
            $table->unsignedInteger('category_id');
            $table->boolean('is_active')->default(true);
            $table->string('photo')->nullable();
            $table->string('password')->nullable();
            $table->date('released_on')->nullable();
            $table->timestamps();
        });

        $this->artisan('crudbooster:make-resource', ['name' => 'Product', '--model' => Product::class, '--generate' => true])->assertSuccessful();

        $path = app_path('Admin/Resources/ProductResource.php');
        $source = file_get_contents($path);

        $this->assertStringContainsString('protected static string $model = Product::class;', $source);
        $this->assertStringContainsString("protected static ?string \$recordTitleAttribute = 'name';", $source);
        $this->assertStringContainsString("Columns\\TextColumn::make('name')->searchable()->sortable(),", $source);
        $this->assertStringContainsString("Columns\\BooleanColumn::make('is_active')", $source);
        $this->assertStringContainsString("Fields\\TextInput::make('name')->required(),", $source);
        $this->assertStringContainsString("Fields\\Textarea::make('description'),", $source);
        $this->assertStringContainsString("Fields\\Select::make('category_id')->relationship('category', 'name')->required(),", $source);
        $this->assertStringContainsString("Fields\\Toggle::make('is_active'),", $source);
        $this->assertStringContainsString("Fields\\FileUpload::make('photo')->image(),", $source);
        $this->assertStringContainsString("Fields\\TextInput::make('password')->password(),", $source);
        $this->assertStringContainsString("Fields\\DatePicker::make('released_on'),", $source);
        $this->assertStringNotContainsString("make('created_at')->", explode('public function form', $source)[1]);

        // The generated class is valid and is a working resource.
        require $path;
        $resource = app(\App\Admin\Resources\ProductResource::class);
        $this->assertCount(6, $resource->table(new \CrudBooster\Tables\Table)->getColumns());
        $this->assertSame('products', $resource::getSlug());

        $this->artisan('crudbooster:make-resource', ['name' => 'Product'])->assertFailed();
    }

    public function test_make_page_writes_the_class_and_its_view()
    {
        $this->artisan('crudbooster:make-page', ['name' => 'SalesReport'])->assertSuccessful();

        $this->assertStringContainsString("protected static string \$view = 'admin.pages.sales-report';", file_get_contents(app_path('Admin/Pages/SalesReport.php')));
        $this->assertFileExists(resource_path('views/admin/pages/sales-report.blade.php'));
    }

    public function test_the_admin_directories_are_discovered()
    {
        $this->artisan('crudbooster:make-page', ['name' => 'Inbox'])->assertSuccessful();
        require app_path('Admin/Pages/Inbox.php');

        $panel = app(\CrudBooster\Panel\Panel::class)->discover(app_path('Admin/Pages'), 'App\\Admin\\Pages');
        $this->assertContains('App\\Admin\\Pages\\Inbox', $panel->getPages());

        app(\CrudBooster\Panel\RouteRegistrar::class)->register();
        $this->loginAsAdmin();
        $this->get($this->admin('inbox'))->assertOk()->assertSee('resources/views/admin/pages/inbox.blade.php');
    }
}
