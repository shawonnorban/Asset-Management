<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

/**
 * Sites the company operates. Assets sit at one of these, and every employee
 * is posted to one.
 */
class AssetLocationSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $locations = [
            'NCL',
            'NFL',
        ];

        $rows = [];

        foreach ($locations as $name) {
            $rows[] = [
                'location_name' => $name,
                'created_at'    => $now,
                'updated_at'    => $now,
            ];
        }

        DB::table('asset_locations')->insert($rows);

        $this->command->info('Seeded ' . count($rows) . ' locations.');
    }
}
