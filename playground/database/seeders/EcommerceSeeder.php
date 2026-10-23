<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EcommerceSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $category = DB::table('shop_categories')->updateOrInsert(
            ['slug' => 'electronics'],
            ['name' => 'Electronics', 'description' => 'Demo products for the CRUDBooster playground.', 'is_active' => true, 'updated_at' => $now, 'created_at' => $now]
        );
        $categoryId = DB::table('shop_categories')->where('slug', 'electronics')->value('id');

        DB::table('shop_products')->updateOrInsert(
            ['sku' => 'DEMO-HEADPHONES'],
            ['shop_category_id' => $categoryId, 'name' => 'Studio Headphones', 'price' => 129.99, 'stock' => 25, 'description' => 'A complete product record demonstrating CRUD fields.', 'is_featured' => true, 'available_on' => now()->toDateString(), 'updated_at' => $now, 'created_at' => $now]
        );
        DB::table('shop_products')->updateOrInsert(
            ['sku' => 'DEMO-KEYBOARD'],
            ['shop_category_id' => $categoryId, 'name' => 'Mechanical Keyboard', 'price' => 89.00, 'stock' => 40, 'description' => 'A second product for filters, exports, and relationships.', 'is_featured' => false, 'available_on' => now()->toDateString(), 'updated_at' => $now, 'created_at' => $now]
        );

        DB::table('shop_customers')->updateOrInsert(
            ['email' => 'customer@example.com'],
            ['name' => 'Demo Customer', 'phone' => '+1 555 0100', 'address' => '1 Demo Street', 'status' => 'active', 'updated_at' => $now, 'created_at' => $now]
        );
        $customerId = DB::table('shop_customers')->where('email', 'customer@example.com')->value('id');
        $productId = DB::table('shop_products')->where('sku', 'DEMO-HEADPHONES')->value('id');

        DB::table('shop_orders')->updateOrInsert(
            ['order_number' => 'DEMO-1001'],
            ['shop_customer_id' => $customerId, 'status' => 'paid', 'total' => 129.99, 'placed_at' => $now, 'notes' => 'Seeded order for testing detail views.', 'updated_at' => $now, 'created_at' => $now]
        );
        $orderId = DB::table('shop_orders')->where('order_number', 'DEMO-1001')->value('id');
        DB::table('shop_order_items')->updateOrInsert(
            ['shop_order_id' => $orderId, 'shop_product_id' => $productId],
            ['quantity' => 1, 'unit_price' => 129.99, 'updated_at' => $now, 'created_at' => $now]
        );
        DB::table('shop_reviews')->updateOrInsert(
            ['shop_product_id' => $productId, 'shop_customer_id' => $customerId],
            ['rating' => 5, 'comment' => 'Excellent demo product.', 'is_published' => true, 'updated_at' => $now, 'created_at' => $now]
        );
        DB::table('shop_coupons')->updateOrInsert(
            ['code' => 'WELCOME10'],
            ['discount_type' => 'percentage', 'discount_value' => 10, 'expires_on' => now()->addYear()->toDateString(), 'is_active' => true, 'updated_at' => $now, 'created_at' => $now]
        );

        foreach ([
            ['path' => 'shop_categories', 'table' => 'shop_categories', 'controller' => 'AdminShopCategoriesController', 'name' => 'Shop Categories'],
            ['path' => 'shop_products', 'table' => 'shop_products', 'controller' => 'AdminShopProductsController', 'name' => 'Shop Products'],
            ['path' => 'shop_customers', 'table' => 'shop_customers', 'controller' => 'AdminShopCustomersController', 'name' => 'Shop Customers'],
            ['path' => 'shop_orders', 'table' => 'shop_orders', 'controller' => 'AdminShopOrdersController', 'name' => 'Shop Orders'],
            ['path' => 'shop_order_items', 'table' => 'shop_order_items', 'controller' => 'AdminShopOrderItemsController', 'name' => 'Order Items'],
            ['path' => 'shop_reviews', 'table' => 'shop_reviews', 'controller' => 'AdminShopReviewsController', 'name' => 'Product Reviews'],
            ['path' => 'shop_coupons', 'table' => 'shop_coupons', 'controller' => 'AdminShopCouponsController', 'name' => 'Coupons'],
        ] as $module) {
            DB::table('cms_moduls')->updateOrInsert(
                ['path' => $module['path']],
                ['name' => $module['name'], 'table_name' => $module['table'], 'controller' => $module['controller'], 'is_protected' => 0, 'is_active' => 1, 'updated_at' => $now, 'created_at' => $now]
            );
        }

        $superAdminId = DB::table('cms_privileges')
            ->where('name', 'Super Administrator')
            ->value('id');
        DB::table('cms_menus')->updateOrInsert(
            ['name' => 'E-commerce', 'parent_id' => 0],
            [
                'type' => 'URL',
                'path' => '#',
                'color' => 'blue',
                'icon' => 'fa fa-shopping-cart',
                'is_active' => 1,
                'is_dashboard' => 0,
                'id_cms_privileges' => $superAdminId,
                'sorting' => 10,
                'updated_at' => $now,
                'created_at' => $now,
            ]
        );
        $parentMenuId = DB::table('cms_menus')
            ->where('name', 'E-commerce')
            ->where('parent_id', 0)
            ->value('id');

        foreach ([
            ['path' => 'shop_categories', 'name' => 'Categories', 'icon' => 'fa fa-tags', 'sorting' => 1],
            ['path' => 'shop_products', 'name' => 'Products', 'icon' => 'fa fa-cube', 'sorting' => 2],
            ['path' => 'shop_customers', 'name' => 'Customers', 'icon' => 'fa fa-users', 'sorting' => 3],
            ['path' => 'shop_orders', 'name' => 'Orders', 'icon' => 'fa fa-shopping-bag', 'sorting' => 4],
            ['path' => 'shop_order_items', 'name' => 'Order Items', 'icon' => 'fa fa-list', 'sorting' => 5],
            ['path' => 'shop_reviews', 'name' => 'Product Reviews', 'icon' => 'fa fa-star', 'sorting' => 6],
            ['path' => 'shop_coupons', 'name' => 'Coupons', 'icon' => 'fa fa-ticket', 'sorting' => 7],
        ] as $menu) {
            DB::table('cms_menus')->updateOrInsert(
                ['path' => $menu['path'], 'parent_id' => $parentMenuId],
                [
                    'name' => $menu['name'],
                    'type' => 'Module',
                    'color' => 'normal',
                    'icon' => $menu['icon'],
                    'is_active' => 1,
                    'is_dashboard' => 0,
                    'id_cms_privileges' => $superAdminId,
                    'sorting' => $menu['sorting'],
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );

            $menuId = DB::table('cms_menus')
                ->where('path', $menu['path'])
                ->where('parent_id', $parentMenuId)
                ->value('id');

            DB::table('cms_menus_privileges')->updateOrInsert(
                ['id_cms_menus' => $menuId, 'id_cms_privileges' => $superAdminId],
                []
            );
        }

        DB::table('cms_menus_privileges')->updateOrInsert(
            ['id_cms_menus' => $parentMenuId, 'id_cms_privileges' => $superAdminId],
            []
        );

        $statisticsId = DB::table('cms_statistics')->updateOrInsert(
            ['slug' => 'shop-overview'],
            ['name' => 'Shop Overview', 'updated_at' => $now, 'created_at' => $now]
        );
        $statisticsId = DB::table('cms_statistics')->where('slug', 'shop-overview')->value('id');
        DB::table('cms_statistic_components')->updateOrInsert(
            ['componentID' => 'shop-products-count'],
            ['id_cms_statistics' => $statisticsId, 'component_name' => 'smallbox', 'area_name' => 'top', 'sorting' => 1, 'name' => 'Products', 'config' => json_encode(['sql' => 'select count(*) as total from shop_products']), 'updated_at' => $now, 'created_at' => $now]
        );
        DB::table('cms_notifications')->updateOrInsert(
            ['content' => 'Welcome to the CRUDBooster e-commerce playground.', 'id_cms_users' => 1],
            ['url' => 'admin/shop_products', 'is_read' => false, 'updated_at' => $now, 'created_at' => $now]
        );
        DB::table('cms_apicustom')->updateOrInsert(
            ['permalink' => 'shop-products'],
            ['nama' => 'Shop Products API', 'tabel' => 'shop_products', 'aksi' => 'list', 'keterangan' => 'Example public API definition for the playground.', 'updated_at' => $now, 'created_at' => $now]
        );
    }
}