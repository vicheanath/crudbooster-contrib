<?php

namespace crocodicstudio\crudbooster\tests\Unit\Models;

use crocodicstudio\crudbooster\models\CmsMenu;
use crocodicstudio\crudbooster\models\CmsPrivilege;
use crocodicstudio\crudbooster\tests\TestCase;

class CmsMenuTest extends TestCase
{
    public function test_visible_to_only_returns_menus_granted_to_the_privilege(): void
    {
        $shop = $this->menu(['name' => 'Shop'], [7]);
        $this->menu(['name' => 'Reports'], [8]);
        $this->menu(['name' => 'Shared'], [7, 8]);

        $this->assertSame(['Shop', 'Shared'], CmsMenu::query()->visibleTo(7)->orderBy('id')->pluck('name')->all());
        $this->assertSame([7], $shop->privilegeLinks->pluck('id_cms_privileges')->map('intval')->all());
    }

    public function test_privileges_relation_reads_through_the_link_table(): void
    {
        $superadmin = CmsPrivilege::query()->where('name', 'Super Administrator')->value('id');
        $menu = $this->menu(['name' => 'Shop'], [$superadmin]);

        $this->assertSame(['Super Administrator'], $menu->privileges->pluck('name')->all());
    }

    public function test_active_top_level_dashboard_and_sorted_scopes(): void
    {
        $parent = $this->menu(['name' => 'B', 'sorting' => 2]);
        $this->menu(['name' => 'A', 'sorting' => 1]);
        $this->menu(['name' => 'Child', 'parent_id' => $parent->id]);
        $this->menu(['name' => 'Off', 'is_active' => 0, 'sorting' => 3]);
        $this->menu(['name' => 'Home', 'is_dashboard' => 1, 'sorting' => 4]);

        $this->assertSame(['A', 'B', 'Home'], CmsMenu::query()->topLevel()->active()->sorted()->pluck('name')->all());
        $this->assertSame(['Off'], CmsMenu::query()->active(false)->pluck('name')->all());
        $this->assertSame(['Home'], CmsMenu::query()->dashboard()->pluck('name')->all());
        $this->assertSame(['Child'], $parent->children->pluck('name')->all());
        $this->assertSame('B', CmsMenu::query()->where('name', 'Child')->first()->parent->name);
    }

    public function test_next_top_level_sorting_places_a_menu_last(): void
    {
        $this->assertSame(1, CmsMenu::nextTopLevelSorting() - (int) CmsMenu::query()->topLevel()->max('sorting'));

        $this->menu(['name' => 'Last', 'sorting' => 40]);
        $this->menu(['name' => 'Nested', 'sorting' => 99, 'parent_id' => 1]);

        $this->assertSame(41, CmsMenu::nextTopLevelSorting());
    }

    public function test_flags_are_cast_to_booleans(): void
    {
        $menu = $this->menu(['name' => 'Home', 'is_dashboard' => 1]);

        $this->assertTrue($menu->fresh()->is_dashboard);
        $this->assertTrue($menu->fresh()->is_active);
    }

    private function menu(array $attributes, array $privileges = []): CmsMenu
    {
        $menu = CmsMenu::query()->create($attributes + ['type' => 'URL', 'path' => '#', 'parent_id' => 0, 'is_active' => 1, 'is_dashboard' => 0]);
        $menu->privileges()->attach($privileges);

        return $menu;
    }
}
