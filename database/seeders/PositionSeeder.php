<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class PositionSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // "GM" appeared twice in the source list; kept once because the
        // positions.name column is unique.
        $positions = [
            'GM',
            'AGM',
            'Sr.Manager',
            'Manager (Comp)',
            'Sr.Executive',
            'Manager',
            'CFO',
            'DGM',
            'Asst.Manager',
            'Deputy Manager',
            'Executive',
            'Jr.Executive',
            'Sr.Executive (Custom Sarker)',
            'Massanger',
            'Purchase Assistant',
            'Office Assistant',
            'Sr.Merchandiser',
            'Merchandiser',
            'Asst.Merchandiser',
            'Assistant Designer',
            'Data Entry Operator',
            'Management Trainee',
            'Executive (Data Entry)',
            'GM (Production and Technical)',
            'Executive (Electrical)',
            'Peon',
            'Caretaker',
            'Cook',
            'Cleaner',
            'Driver',
            'Norban Altilium',
            'Managing Director',
            'MD Madam',
            'Finance Director',
            'Director',
            'MTO',
        ];

        $rows = [];

        foreach ($positions as $name) {
            $rows[] = [
                'name'       => $name,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('positions')->insert($rows);

        $this->command->info('Seeded ' . count($rows) . ' positions.');
    }
}
