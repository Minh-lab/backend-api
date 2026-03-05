<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CapstoneReviewerSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['capstone_id' => 1, 'lecturer_id' => 4, 'opponent_grade' => 7.5],
            ['capstone_id' => 2, 'lecturer_id' => 4, 'opponent_grade' => 8.0],
            ['capstone_id' => 3, 'lecturer_id' => 6, 'opponent_grade' => null],
        ];

        foreach ($rows as $row) {
            DB::table('capstone_reviewers')->insertOrIgnore(array_merge($row, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}
