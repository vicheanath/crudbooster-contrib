<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            \crocodicstudio\crudbooster\database\seeds\CBSeeder::class,
            EcommerceSeeder::class,
            ShopDemoSeeder::class,
            HrSeeder::class,
            CrmSeeder::class,
            FieldShowcaseSeeder::class,
        ]);

        // Lists the resources and pages on Privileges > Edit, so other admins can be given access.
        $this->command?->call('crudbooster:sync-panel');
    }
}
