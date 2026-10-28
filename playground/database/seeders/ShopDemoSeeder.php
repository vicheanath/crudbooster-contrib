<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * More catalog, customers and orders on the shop_* tables, so the e-commerce resources and the
 * Store Overview page have something to show. Runs after EcommerceSeeder; safe to run again.
 */
class ShopDemoSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        mt_srand(11);

        foreach (['home-office' => 'Home Office', 'accessories' => 'Accessories', 'audio' => 'Audio'] as $slug => $name) {
            DB::table('shop_categories')->updateOrInsert(['slug' => $slug], ['name' => $name, 'description' => null, 'is_active' => true, 'updated_at' => $now, 'created_at' => $now]);
        }
        $categoryIds = DB::table('shop_categories')->pluck('id', 'slug');

        // [sku, name, category, price, stock, featured]
        $products = [
            ['DESK-STAND', 'Standing Desk', 'home-office', 349.00, 12, true],
            ['CHAIR-ERGO', 'Ergonomic Chair', 'home-office', 229.00, 4, false],
            ['LAMP-LED', 'LED Desk Lamp', 'home-office', 39.90, 60, false],
            ['HUB-USBC', 'USB-C Hub', 'accessories', 49.00, 0, false],
            ['MOUSE-WL', 'Wireless Mouse', 'accessories', 29.50, 85, true],
            ['PAD-XL', 'XL Mouse Pad', 'accessories', 19.00, 7, false],
            ['SPK-BT', 'Bluetooth Speaker', 'audio', 79.00, 23, false],
            ['MIC-USB', 'USB Microphone', 'audio', 99.00, 0, false],
        ];
        foreach ($products as [$sku, $name, $category, $price, $stock, $featured]) {
            DB::table('shop_products')->updateOrInsert(['sku' => $sku], [
                'shop_category_id' => $categoryIds[$category], 'name' => $name, 'price' => $price, 'stock' => $stock,
                'description' => null, 'is_featured' => $featured, 'available_on' => $now->toDateString(), 'updated_at' => $now, 'created_at' => $now,
            ]);
        }
        $catalog = DB::table('shop_products')->get(['id', 'price'])->all();

        $customers = [
            ['Olivia Turner', 'vip'], ['Mateo Garcia', 'active'], ['Zara Ahmed', 'active'], ['Felix Wagner', 'vip'],
            ['Leah Cohen', 'active'], ['Arjun Mehta', 'active'], ['Nina Rossi', 'blocked'], ['Owen Price', 'active'],
        ];
        foreach ($customers as $i => [$name, $status]) {
            DB::table('shop_customers')->updateOrInsert(['email' => strtolower(str_replace(' ', '.', $name)).'@example.com'], [
                'name' => $name, 'phone' => sprintf('+1 555 03%02d', $i + 1), 'address' => ($i + 10).' Market Street', 'status' => $status,
                'updated_at' => $now, 'created_at' => $now,
            ]);
        }
        $customerIds = DB::table('shop_customers')->where('status', '!=', 'blocked')->pluck('id')->all();

        $statuses = ['pending', 'paid', 'paid', 'shipped', 'delivered', 'delivered', 'delivered', 'cancelled'];
        for ($n = 1; $n <= 24; $n++) {
            $number = sprintf('ORD-%04d', 2000 + $n);
            $placedAt = $now->copy()->subDays(mt_rand(0, 75))->setTime(mt_rand(8, 21), mt_rand(0, 59));
            DB::table('shop_orders')->updateOrInsert(['order_number' => $number], [
                'shop_customer_id' => $customerIds[array_rand($customerIds)], 'status' => $statuses[array_rand($statuses)],
                'placed_at' => $placedAt, 'notes' => null, 'updated_at' => $now, 'created_at' => $placedAt,
            ]);
            $orderId = DB::table('shop_orders')->where('order_number', $number)->value('id');

            DB::table('shop_order_items')->where('shop_order_id', $orderId)->delete();
            foreach ((array) array_rand($catalog, mt_rand(1, 3)) as $index) {
                DB::table('shop_order_items')->insert([
                    'shop_order_id' => $orderId, 'shop_product_id' => $catalog[$index]->id, 'quantity' => mt_rand(1, 3),
                    'unit_price' => $catalog[$index]->price, 'updated_at' => $now, 'created_at' => $now,
                ]);
            }
            DB::table('shop_orders')->where('id', $orderId)->update([
                'total' => DB::table('shop_order_items')->where('shop_order_id', $orderId)->selectRaw('sum(quantity * unit_price) as total')->value('total'),
            ]);
        }

        foreach ([
            ['SPRING25', 'percentage', 25, 30, true],
            ['FREESHIP', 'fixed', 5.99, null, true],
            ['BLACKFRIDAY', 'percentage', 40, -300, true],
            ['STAFF15', 'percentage', 15, null, false],
        ] as [$code, $type, $value, $expiresIn, $active]) {
            DB::table('shop_coupons')->updateOrInsert(['code' => $code], [
                'discount_type' => $type, 'discount_value' => $value, 'is_active' => $active,
                'expires_on' => $expiresIn === null ? null : $now->copy()->addDays($expiresIn)->toDateString(),
                'updated_at' => $now, 'created_at' => $now,
            ]);
        }
    }
}
