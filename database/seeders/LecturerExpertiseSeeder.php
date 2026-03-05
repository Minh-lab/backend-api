<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LecturerExpertiseSeeder extends Seeder
{
    public function run(): void
    {
        // expertise_id: 1=Web, 2=Mobile, 3=AI/ML, 4=DataScience, 5=Security, 6=Cloud, 7=IoT, 8=SoftwareDev
        $rows = [
            ['lecturer_id' => 1, 'expertise_id' => 1],
            ['lecturer_id' => 1, 'expertise_id' => 8],
            ['lecturer_id' => 2, 'expertise_id' => 3],
            ['lecturer_id' => 2, 'expertise_id' => 4],
            ['lecturer_id' => 3, 'expertise_id' => 2],
            ['lecturer_id' => 3, 'expertise_id' => 1],
            ['lecturer_id' => 4, 'expertise_id' => 5],
            ['lecturer_id' => 4, 'expertise_id' => 6],
            ['lecturer_id' => 5, 'expertise_id' => 7],
            ['lecturer_id' => 5, 'expertise_id' => 8],
            ['lecturer_id' => 6, 'expertise_id' => 3],
            ['lecturer_id' => 6, 'expertise_id' => 4],
        ];

        foreach ($rows as $row) {
            DB::table('lecturer_expertises')->insertOrIgnore(array_merge($row, [
                'created_at' => now(),
            ]));
        }
    }
}
