<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            // reference and organisation data first - everything below depends on it
            RoleSeeder::class,
            UserSeeder::class,
            TaxDepreciationGroupSeeder::class,
            AssetCategorySeeder::class,
            AssetLocationSeeder::class,
            DepartmentSeeder::class,
            PositionSeeder::class,
            EmployeeSeeder::class,
            AssetSeeder::class,
            NotificationTemplateSeeder::class,

            // commercial lifecycle sample data: needs the assets, users, and
            // locations above, so it stays last. Marks a few assets DISPOSED.
            CommercialDemoSeeder::class,
        ]);
    }
}
