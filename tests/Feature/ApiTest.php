<?php

namespace crocodicstudio\crudbooster\tests\Feature;

use Illuminate\Support\Facades\DB;

class ApiTest extends FeatureTestCase
{
    private $generated = [];

    protected function setUp(): void
    {
        parent::setUp();

        if (! is_dir(base_path('app/Http/Controllers'))) {
            mkdir(base_path('app/Http/Controllers'), 0777, true);
        }
    }

    protected function tearDown(): void
    {
        foreach ($this->generated as $file) {
            @unlink($file);
        }

        parent::tearDown();
    }

    public function test_generated_api_requires_token_and_returns_rows()
    {
        $this->loginAsAdmin();
        DB::table('cms_email_templates')->insert(['name' => 'Second', 'slug' => 'second', 'subject' => 'S', 'content' => 'C']);

        $this->post($this->admin('api_generator/save-api-custom'), [
            'nama' => 'List Templates',
            'tabel' => 'cms_email_templates',
            'aksi' => 'list',
            'permalink' => 'list_templates',
            'method_type' => 'get',
            'responses_name' => ['id', 'name', 'slug'],
            'responses_type' => ['integer', 'string', 'string'],
            'responses_subquery' => ['', '', ''],
            'responses_used' => [1, 1, 1],
            'keterangan' => 'All templates',
        ])->assertRedirect();

        $this->generated[] = $file = base_path('app/Http/Controllers/ApiListTemplatesController.php');
        $this->assertFileExists($file);
        require_once $file;
        $this->registerCrudboosterRoutes();

        $this->getJson('/api/list_templates')->assertStatus(403);

        $secret = $this->get($this->admin('api_generator/generate-screet-key'))->assertOk()->json('key');
        $token = $this->postJson('/api/get-token', ['secret' => $secret])
            ->assertOk()
            ->assertJsonPath('api_status', 1)
            ->json('data.access_token');

        $this->getJson('/api/list_templates', ['Authorization' => 'Bearer '.$token])
            ->assertOk()
            ->assertJsonPath('api_status', 1)
            ->assertJsonFragment(['slug' => 'second']);
    }

    public function test_invalid_secret_is_rejected()
    {
        $this->postJson('/api/get-token', ['secret' => 'nope'])->assertJsonPath('api_status', 0);
    }

    public function test_api_documentation_and_postman_export_render()
    {
        $this->loginAsAdmin();
        DB::table('cms_apicustom')->insert(['nama' => 'Doc', 'permalink' => 'doc', 'tabel' => 'cms_users', 'aksi' => 'list', 'method_type' => 'get', 'parameters' => serialize([]), 'responses' => serialize([])]);

        $this->get($this->admin('api_generator'))->assertOk()->assertSee('doc');
        $this->get('/api-documentation')->assertOk();
        $this->get('/download-documentation-postman')->assertOk();
    }
}
