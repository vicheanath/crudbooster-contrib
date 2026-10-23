<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * The Field Showcase module: its module row, a sidebar menu and one record with every field filled.
 * Runs after EcommerceSeeder, whose categories, customers and products the lookups use.
 */
class FieldShowcaseSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $superAdminId = DB::table('cms_privileges')->where('name', 'Super Administrator')->value('id');

        DB::table('cms_moduls')->updateOrInsert(
            ['path' => 'field_showcases'],
            ['name' => 'Field Showcase', 'icon' => 'fa fa-th-list', 'table_name' => 'field_showcases', 'controller' => 'AdminFieldShowcasesController', 'is_protected' => 0, 'is_active' => 1, 'updated_at' => $now, 'created_at' => $now]
        );

        DB::table('cms_menus')->updateOrInsert(
            ['path' => 'field_showcases', 'parent_id' => 0],
            ['name' => 'Field Showcase', 'type' => 'Module', 'color' => 'normal', 'icon' => 'fa fa-th-list', 'is_active' => 1, 'is_dashboard' => 0, 'id_cms_privileges' => $superAdminId, 'sorting' => 20, 'updated_at' => $now, 'created_at' => $now]
        );
        $menuId = DB::table('cms_menus')->where('path', 'field_showcases')->where('parent_id', 0)->value('id');
        DB::table('cms_menus_privileges')->updateOrInsert(['id_cms_menus' => $menuId, 'id_cms_privileges' => $superAdminId], []);

        $categoryId = DB::table('shop_categories')->where('slug', 'electronics')->value('id');
        $customerId = DB::table('shop_customers')->where('email', 'customer@example.com')->value('id');
        $productId = DB::table('shop_products')->where('sku', 'DEMO-HEADPHONES')->value('id');

        DB::table('field_showcases')->updateOrInsert(
            ['title' => 'Every field filled in'],
            [
                'email' => 'showcase@example.com',
                'password' => Hash::make('secret'),
                'quantity' => 12,
                'price' => 1499.95,
                'aliases' => 'first|second',
                'reference' => 'PLAYGROUND',
                'birth_date' => '1990-05-17',
                'meeting_at' => '2026-12-01 09:30:00',
                'alarm_time' => '07:15:00',
                'summary' => "A seeded record that fills every column.\nUse it to check detail and edit views.",
                'body' => '<p>Rich <strong>WYSIWYG</strong> content with a <a href="https://example.com">link</a>.</p>',
                'settings' => json_encode(['theme' => 'dark', 'notify' => true, 'limit' => 25]),
                'status' => 'published',
                'priority' => 2,
                'color' => 'green',
                'shop_category_id' => $categoryId,
                'query_category_id' => $categoryId,
                'level' => 'expert',
                'shop_customer_id' => $customerId,
                'shop_product_id' => $productId,
                'platforms' => 'Android;Web',
                'category_names' => 'Electronics',
                'address' => 'Phnom Penh, Cambodia',
                'latitude' => '11.5564',
                'longitude' => '104.9282',
                'rating' => '4',
                'updated_at' => $now,
                'created_at' => $now,
            ]
        );
        $showcaseId = DB::table('field_showcases')->where('title', 'Every field filled in')->value('id');

        DB::table('field_showcase_categories')->updateOrInsert(['field_showcases_id' => $showcaseId, 'shop_categories_id' => $categoryId], []);
        DB::table('field_showcase_customers')->updateOrInsert(['field_showcases_id' => $showcaseId, 'shop_customers_id' => $customerId], []);

        foreach ([
            ['name' => 'Gift wrap', 'note' => 'Red ribbon', 'quantity' => 1, 'size' => 'M', 'kind' => 'gift'],
            ['name' => 'Sample pack', 'note' => null, 'quantity' => 3, 'size' => 'S', 'kind' => 'sample'],
        ] as $item) {
            DB::table('field_showcase_items')->updateOrInsert(
                ['field_showcase_id' => $showcaseId, 'name' => $item['name']],
                $item + ['shop_product_id' => $productId, 'shop_category_id' => $categoryId, 'source' => 'seeder', 'updated_at' => $now, 'created_at' => $now]
            );
        }
    }
}
