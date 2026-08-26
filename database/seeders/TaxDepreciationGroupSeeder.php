<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TaxDepreciationGroupSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        DB::table('tax_depreciation_groups')->insert([
            [
                'id' => 1,
                'name' => 'Group 1',
                'useful_life_years' => 4,
                'straight_line_rate' => 25.00,
                'declining_balance_rate' => 50.00,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 2,
                'name' => 'Group 2',
                'useful_life_years' => 8,
                'straight_line_rate' => 12.50,
                'declining_balance_rate' => 25.00,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 3,
                'name' => 'Group 3',
                'useful_life_years' => 16,
                'straight_line_rate' => 6.25,
                'declining_balance_rate' => 12.50,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 4,
                'name' => 'Group 4',
                'useful_life_years' => 20,
                'straight_line_rate' => 5.00,
                'declining_balance_rate' => 10.00,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}
