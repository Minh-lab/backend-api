<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CapstoneSeeder extends Seeder
{
    public function run(): void
    {
        // --- Dữ liệu gốc ---
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

            ['topic_id' => 3, 'student_id' => 8, 'lecturer_id' => 2, 'council_id' => null, 'semester_id' => 8,
             'status' => 'PENDING_TEACHER', 'instructor_grade' => null, 'council_grade' => null, 'defense_order' => null],

            ['topic_id' => 5, 'student_id' => 9, 'lecturer_id' => 3, 'council_id' => null, 'semester_id' => 8,
             'status' => 'PENDING_CANCEL', 'instructor_grade' => null, 'council_grade' => null, 'defense_order' => null],
        ];

        foreach ($rows as $row) {
            DB::table('capstones')->insertOrIgnore(array_merge($row, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        // --- Sinh thêm đồ án cho sinh viên 11 đến 50 ---
        // Map student -> topic, lecturer, council, status
        $statuses     = ['PENDING_TEACHER', 'TOPIC_APPROVED', 'REPORTING', 'DEFENSE_ELIGIBLE', 'COMPLETED', 'PENDING_CANCEL'];
        $topicIds     = range(1, 12);   // 12 đề tài gốc đang có
        $lecturerIds  = range(1, 6);
        $councilIds   = [1, 2, 3, 4, null];

        for ($studentId = 11; $studentId <= 50; $studentId++) {
            $status      = $statuses[array_rand($statuses)];
            $isCompleted = $status === 'COMPLETED';
            $councilId   = $isCompleted ? array_rand(array_flip([1, 2])) : ($status === 'DEFENSE_ELIGIBLE' ? rand(1, 4) : null);

            DB::table('capstones')->insertOrIgnore([
                'topic_id'         => $topicIds[array_rand($topicIds)],
                'student_id'       => $studentId,
                'lecturer_id'      => $lecturerIds[array_rand($lecturerIds)],
                'council_id'       => $councilId,
                'semester_id'      => 8,
                'status'           => $status,
                'instructor_grade' => $isCompleted ? round(rand(65, 100) / 10, 1) : null,
                'council_grade'    => $isCompleted ? round(rand(65, 100) / 10, 1) : null,
                'defense_order'    => $isCompleted ? ($studentId - 10) : null,
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);
        }
    }
}
