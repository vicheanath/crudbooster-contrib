<?php

namespace crocodicstudio\crudbooster\tests\Feature;

use crocodicstudio\crudbooster\contracts\Grid\GridFilterParserInterface;
use crocodicstudio\crudbooster\tests\TestCase;
use Illuminate\Support\Facades\DB;

class GridFilterParserTest extends TestCase
{
    public function test_filter_parser_is_bound_and_applies_filters_and_sorting(): void
    {
        DB::table('cms_settings')->insert([
            ['name' => 'Filter A', 'content' => 'alpha'],
            ['name' => 'Filter B', 'content' => 'beta'],
        ]);

        $query = DB::table('cms_settings');
        $hasSorting = app(GridFilterParserInterface::class)->apply($query, [
            'cms_settings.name' => ['type' => 'like', 'value' => 'Filter', 'sorting' => 'desc'],
        ]);

        $this->assertTrue($hasSorting);
        $this->assertSame(['Filter B', 'Filter A'], $query->pluck('name')->all());
    }
}
