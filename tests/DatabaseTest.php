<?php

namespace crocodicstudio\crudbooster\tests;

use crocodicstudio\crudbooster\helpers\CRUDBooster;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DatabaseTest extends TestCase
{
    public function test_column_nullability_is_read_from_schema()
    {
        $this->assertTrue(CRUDBooster::isColumnNULL('cms_users', 'photo'));
        $this->assertFalse(CRUDBooster::isColumnNULL('cms_users', 'id'));
        $this->assertFalse(CRUDBooster::isColumnNULL('cms_users', 'no_such_column'));
    }

    public function test_field_type_is_read_from_schema()
    {
        $this->assertSame('varchar', CRUDBooster::getFieldType('cms_users', 'email'));
        $this->assertSame('varchar', CRUDBooster::getFieldType('cms_users', 'no_such_column'));
    }

    public function test_raw_sql_strings_run_without_db_raw()
    {
        $rows = DB::select('select count(*) as total from cms_privileges');

        $this->assertGreaterThan(0, $rows[0]->total);
    }

    public function test_smallbox_widget_runs_its_sql()
    {
        $html = view('crudbooster::statistic_builder.components.smallbox', [
            'command' => 'showFunction',
            'key' => 'sql',
            'value' => 'select count(*) as total from cms_users',
        ])->render();

        $this->assertSame((string) DB::table('cms_users')->count(), trim($html));
    }

    public function test_apicustom_columns_are_nullable_after_change_migration()
    {
        foreach (['permalink', 'tabel', 'aksi', 'kolom', 'orderby', 'sub_query_1', 'sql_where', 'nama', 'keterangan', 'parameter'] as $col) {
            $this->assertTrue(CRUDBooster::isColumnNULL('cms_apicustom', $col), "$col should be nullable");
        }
    }

    public function test_seeder_is_idempotent_and_sets_the_forgot_password_subject()
    {
        DB::table('cms_email_templates')->where('slug', 'forgot_password_backend')->update(['subject' => null]);

        $this->seed(\crocodicstudio\crudbooster\database\seeds\CBSeeder::class);

        $templates = DB::table('cms_email_templates')->where('slug', 'forgot_password_backend')->get();
        $this->assertCount(1, $templates);
        $this->assertSame('Your new password', $templates[0]->subject);
    }

    public function test_table_columns_listing_on_mysql()
    {
        if (DB::getDriverName() !== 'mysql') {
            $this->markTestSkipped('getTableColumns() queries information_schema (MySQL only).');
        }

        $this->assertContains('email', CRUDBooster::getTableColumns('cms_users'));
    }
}
