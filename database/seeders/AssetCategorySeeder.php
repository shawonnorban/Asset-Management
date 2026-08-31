<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

/**
 * The categories a corporate IT department normally starts with. Each one
 * declares the asset_type that drives its specification form.
 */
class AssetCategorySeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $categories = [
            ['Desktop',         'COMPUTER'],
            ['Laptop',          'COMPUTER'],
            ['Workstation',     'COMPUTER'],
            ['Server',          'NETWORK_DEVICE'],
            ['Monitor',         'PERIPHERAL'],
            ['Keyboard',        'PERIPHERAL'],
            ['Mouse',           'PERIPHERAL'],
            ['UPS',             'PERIPHERAL'],
            ['Docking Station', 'PERIPHERAL'],
            ['Headset',         'PERIPHERAL'],
            ['Webcam',          'PERIPHERAL'],
            ['Projector',       'PERIPHERAL'],
            ['Printer',         'PRINTER'],
            ['Scanner',         'PERIPHERAL'],
            ['Router',          'NETWORK_DEVICE'],
            ['Switch',          'NETWORK_DEVICE'],
            ['Access Point',    'NETWORK_DEVICE'],
            ['Firewall',        'NETWORK_DEVICE'],
            ['NAS',             'NETWORK_DEVICE'],
            ['Tablet',          'OTHER'],
            ['Furniture',       'OTHER'],
        ];

        $rows = [];

        foreach ($categories as [$name, $type]) {
            $rows[] = [
                'category_name' => $name,
                'asset_type'    => $type,
                'created_at'    => $now,
                'updated_at'    => $now,
            ];
        }

        DB::table('asset_categories')->insert($rows);

        $this->command->info('Seeded ' . count($rows) . ' asset categories.');
    }
}
