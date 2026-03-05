<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CapstoneSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['topic_id' => 1, 'student_id' => 1, 'lecturer_id' => 1, 'council_id' => 1, 'semester_id' => 8,
             'status' => 'COMPLETED', 'instructor_grade' => 8.5, 'council_grade' => 8.0, 'defense_order' => 1],

            ['topic_id' => 2, 'student_id' => 2, 'lecturer_id' => 1, 'council_id' => 1, 'semester_id' => 8,
             'status' => 'COMPLETED', 'instructor_grade' => 9.0, 'council_grade' => 8.5, 'defense_order' => 2],

            ['topic_id' => 6, 'student_id' => 3, 'lecturer_id' => 2, 'council_id' => 1, 'semester_id' => 8,
             'status' => 'DEFENSE_ELIGIBLE', 'instructor_grade' => null, 'council_grade' => null, 'defense_order' => 3],

            ['topic_id' => 4, 'student_id' => 6, 'lecturer_id' => 3, 'council_id' => 2, 'semester_id' => 8,
             'status' => 'REPORTING', 'instructor_grade' => null, 'council_grade' => null, 'defense_order' => null],

            ['topic_id' => 11, 'student_id' => 7, 'lecturer_id' => 5, 'council_id' => null, 'semester_id' => 8,
             'status' => 'TOPIC_APPROVED', 'instructor_grade' => null, 'council_grade' => null, 'defense_order' => null],
        ];

        foreach ($rows as $row) {
            DB::table('capstones')->insertOrIgnore(array_merge($row, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}
