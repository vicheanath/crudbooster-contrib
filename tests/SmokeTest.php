<?php

namespace crocodicstudio\crudbooster\tests;

use crocodicstudio\crudbooster\controllers\Controller;
use crocodicstudio\crudbooster\export\DefaultExportXls;
use crocodicstudio\crudbooster\helpers\CBImage;
use crocodicstudio\crudbooster\helpers\CRUDBooster;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Interfaces\ImageInterface;
use Maatwebsite\Excel\Excel as ExcelWriter;

class SmokeTest extends TestCase
{
    public function test_migrations_create_cms_tables()
    {
        foreach (['cms_users', 'cms_privileges', 'cms_moduls', 'cms_menus', 'cms_settings', 'cms_apicustom', 'cms_statistics'] as $table) {
            $this->assertTrue(Schema::hasTable($table), "Missing table $table");
        }
    }

    public function test_artisan_commands_are_registered()
    {
        $commands = array_keys(Artisan::all());

        foreach (['crudbooster:install', 'crudbooster:update', 'crudbooster:version', 'mailqueues'] as $name) {
            $this->assertContains($name, $commands);
        }
    }

    public function test_documented_seed_command_resolves_cb_seeder()
    {
        DB::table('cms_settings')->delete();

        $this->artisan('db:seed', ['--class' => 'CBSeeder'])->assertSuccessful();

        $this->assertGreaterThan(0, DB::table('cms_settings')->count());
    }

    public function test_facade_aliases_resolve()
    {
        $this->assertTrue(class_exists('PDF'));
        $this->assertTrue(class_exists('Excel'));
        $this->assertTrue(class_exists('Image'));
        $this->assertTrue(class_exists('CRUDBooster'));
        $this->assertTrue(class_exists('CB'));
    }

    public function test_login_page_renders()
    {
        $this->get(config('crudbooster.ADMIN_PATH').'/login')
            ->assertOk()
            ->assertSee('password', false);
    }

    public function test_admin_area_redirects_guests_to_login()
    {
        $this->get(config('crudbooster.ADMIN_PATH').'/api_generator')
            ->assertRedirect();
    }

    public function test_find_primary_key_without_doctrine()
    {
        $this->assertSame('id', CRUDBooster::findPrimaryKey('cms_users'));
        $this->assertSame('id', CRUDBooster::findPrimaryKey(null));
    }

    public function test_send_fcm_has_no_optional_before_required_parameter()
    {
        $params = (new \ReflectionMethod(CRUDBooster::class, 'sendFCM'))->getParameters();

        $this->assertFalse($params[0]->isOptional());
        $this->assertSame('title , content null !', CRUDBooster::sendFCM([], ['title' => '', 'content' => '']));
    }

    public function test_controllers_allow_dynamic_properties()
    {
        $controller = new class extends Controller {};

        set_error_handler(function ($errno, $errstr) {
            throw new \ErrorException($errstr, 0, $errno);
        }, E_DEPRECATED);

        try {
            $controller->some_generated_option = true;
        } finally {
            restore_error_handler();
        }

        $this->assertTrue($controller->some_generated_option);
    }

    public function test_image_alias_reads_images_with_v2_and_v3_names()
    {
        $path = $this->makePng('uploads/test/alias.png', 40, 20);

        $this->assertInstanceOf(ImageInterface::class, CBImage::read($path));
        $this->assertInstanceOf(ImageInterface::class, \Image::make($path));
    }

    public function test_uploaded_image_is_resized_and_thumbnailed()
    {
        $this->makePng('uploads/test/big.png', 2000, 1000);

        $resize = new \ReflectionMethod(CRUDBooster::class, 'resizeImage');
        $resize->invoke(null, 'uploads/test/big.png');

        $this->assertSame(1300, CBImage::read(storage_path('app/uploads/test/big.png'))->width());

        $thumb = CBImage::read(storage_path('app/uploads_thumbnail/'.date('Y-m').'/big.png'));
        $this->assertSame([350, 350], [$thumb->width(), $thumb->height()]);
    }

    public function test_upload_preview_serves_resized_image()
    {
        $this->makePng('uploads/test/preview.png', 300, 200);

        $response = $this->get('uploads/test/preview.png?w=100&h=50')->assertOk();

        $image = CBImage::read($response->getContent());
        $this->assertSame([100, 50], [$image->width(), $image->height()]);
    }

    public function test_pdf_export_renders()
    {
        $pdf = app('dompdf.wrapper');
        $pdf->loadHTML('<h1>CRUDBooster</h1>');

        $this->assertStringStartsWith('%PDF', $pdf->output());
    }

    public function test_excel_export_from_view_renders()
    {
        $raw = \Excel::raw(new DefaultExportXls([
            // As CBController builds them: optional keys such as str_limit / nl2br / image are often absent.
            'columns' => [['name' => 'name', 'label' => 'Name', 'field' => 'name']],
            'result' => [(object) ['name' => 'Alice']],
        ]), ExcelWriter::CSV);

        $this->assertStringContainsString('Alice', $raw);
    }

    public function test_legacy_notice_compat_only_covers_crudbooster_code()
    {
        $handler = new \crocodicstudio\crudbooster\helpers\LegacyNoticeHandler([__DIR__.'/../src'], function () {
            return 'passed-on';
        });
        $this->assertSame('passed-on', $handler(E_WARNING, 'Undefined array key "missing"', __FILE__, 1));
        $this->assertTrue($handler(E_WARNING, 'Undefined array key "missing"', __DIR__.'/../src/helpers/CRUDBooster.php', 1));
        $this->assertSame('passed-on', $handler(E_WARNING, 'Division by zero', __DIR__.'/../src/helpers/CRUDBooster.php', 1));
    }

    private function makePng(string $relative, int $width, int $height): string
    {
        Storage::makeDirectory(dirname($relative));
        $path = storage_path('app/'.$relative);
        CBImage::manager()->create($width, $height)->fill('ff0000')->save($path);

        return $path;
    }
}
