<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $departments = [
            'Admin & HR',
            'IT',
            'Accounts & Finance',
            'Audit',
            'Commercial',
            'Procurement',
            'Merchandising',
            'Design',
            'CAD',
            'MIS',
            'Production',
            'Admin (Assistant)',
            'Admin (Driver)',
        ];

        $rows = [];

        foreach ($departments as $name) {
            $rows[] = [
                'name'       => $name,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('departments')->insert($rows);

        $this->command->info('Seeded ' . count($rows) . ' departments.');
    }
}
