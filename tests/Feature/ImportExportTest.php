<?php

namespace crocodicstudio\crudbooster\tests\Feature;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ImportExportTest extends FeatureTestCase
{
    public function test_csv_import_inserts_rows()
    {
        $this->loginAsAdmin();

        $csv = "Template Name,Slug,Subject\nWelcome Mail,welcome_mail,Hi there\nGoodbye Mail,goodbye_mail,Bye\n";
        $upload = UploadedFile::fake()->createWithContent('templates.csv', $csv);

        $response = $this->post($this->admin('email_templates/do-upload-import-data'), ['userfile' => $upload]);
        $response->assertRedirect();
        parse_str(parse_url($response->headers->get('Location'), PHP_URL_QUERY), $query);
        $file = $query['file'];
        $this->assertTrue(Storage::exists(base64_decode($file)));

        // Step 2: headings are read and slugged like Laravel Excel 2.x did.
        $this->get($this->admin('email_templates/import-data').'?file='.urlencode($file))
            ->assertOk()
            ->assertSee('template_name')
            ->assertSee('subject');
        $this->assertSame(2, session('total_data_import'));

        // Step 3: map table columns (by index in the column listing) to file headings.
        $this->post($this->admin('email_templates/done-import').'?file='.urlencode($file).'&import=1', [
            'select_column' => [1 => 'template_name', 2 => 'slug', 3 => 'subject'],
        ])->assertOk();

        // Step 4: run the import.
        $this->post($this->admin('email_templates/do-import-chunk'), ['file' => $file])
            ->assertOk()
            ->assertJson(['status' => true]);

        $this->assertDatabaseHas('cms_email_templates', ['name' => 'Welcome Mail', 'slug' => 'welcome_mail', 'subject' => 'Hi there']);
        $this->assertDatabaseHas('cms_email_templates', ['name' => 'Goodbye Mail', 'slug' => 'goodbye_mail', 'subject' => 'Bye']);

        $this->post($this->admin('email_templates/do-import-chunk'), ['file' => $file, 'resume' => 1])
            ->assertOk()
            ->assertJson(['progress' => 100]);
    }

    public function test_export_downloads_each_format()
    {
        $this->loginAsAdmin();

        foreach (['pdf' => 'application/pdf', 'xls' => null, 'csv' => null] as $format => $type) {
            $response = $this->post($this->admin('email_templates/export-data'), [
                'fileformat' => $format,
                'filename' => 'templates',
                'limit' => 10,
                'page_size' => 'A4',
                'page_orientation' => 'potrait',
            ]);

            $response->assertOk();
            if ($type) {
                $this->assertStringStartsWith($type, $response->headers->get('Content-Type'));
            } else {
                $this->assertStringContainsString('templates.'.$format, $response->headers->get('Content-Disposition'));
            }
        }
    }
}
