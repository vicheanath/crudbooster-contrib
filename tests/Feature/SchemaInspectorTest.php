<?php

namespace crocodicstudio\crudbooster\tests\Feature;

use crocodicstudio\crudbooster\contracts\Schema\SchemaInspectorInterface;
use crocodicstudio\crudbooster\tests\TestCase;

class SchemaInspectorTest extends TestCase
{
    public function test_schema_inspector_is_bound_and_reads_package_tables(): void
    {
        $inspector = app(SchemaInspectorInterface::class);

        $this->assertTrue($inspector->hasTable('cms_users'));
        $this->assertTrue($inspector->hasColumn('cms_users', 'email'));
        $this->assertContains('email', $inspector->columns('cms_users'));
        $this->assertSame('id', $inspector->primaryKey('cms_users'));
    }

    public function test_legacy_schema_helpers_delegate_to_inspector(): void
    {
        $this->assertSame('id', \CRUDBooster::pk('cms_users'));
        $this->assertTrue(\CRUDBooster::isColumnExists('cms_users', 'email'));
        $this->assertContains('name', \CRUDBooster::getTableColumns('cms_users'));
    }
}
