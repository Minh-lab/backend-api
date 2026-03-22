<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InternshipSeeder extends Seeder
{
    public function run(): void
    {
        // --- Dữ liệu gốc ---
        $rows = [
            ['student_id' => 4, 'lecturer_id' => 2, 'company_id' => 1, 'semester_id' => 8,
             'status' => 'COMPLETED', 'company_grade' => 8.5, 'university_grade' => 8.0,
             'company_feedback' => 'Sinh viên chăm chỉ, tiếp thu nhanh, hoàn thành tốt nhiệm vụ',
             'university_feedback' => 'Sinh viên đạt yêu cầu thực tập',
             'position' => 'Frontend Developer Intern'],

            ['student_id' => 5, 'lecturer_id' => 2, 'company_id' => 3, 'semester_id' => 8,
             'status' => 'COMPLETED', 'company_grade' => 9.0, 'university_grade' => 9.0,
             'company_feedback' => 'Sinh viên xuất sắc, tư duy tốt, làm việc nhóm hiệu quả',
             'university_feedback' => 'Sinh viên hoàn thành xuất sắc',
             'position' => 'Backend Developer Intern'],

            ['student_id' => 8, 'lecturer_id' => 4, 'company_id' => 2, 'semester_id' => 8,
             'status' => 'INTERNING', 'company_grade' => null, 'university_grade' => null,
             'company_feedback' => null, 'university_feedback' => null,
             'position' => 'Security Analyst Intern'],

            ['student_id' => 9, 'lecturer_id' => 3, 'company_id' => 5, 'semester_id' => 8,
             'status' => 'COMPANY_APPROVED', 'company_grade' => null, 'university_grade' => null,
             'company_feedback' => null, 'university_feedback' => null,
             'position' => 'Mobile Developer Intern'],
        ];

        foreach ($rows as $row) {
            DB::table('internships')->insertOrIgnore(array_merge($row, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        // --- Sinh thêm thực tập cho sinh viên 51 đến 100 ---
        $statuses  = ['PENDING', 'COMPANY_APPROVED', 'INTERNING', 'COMPLETED'];
        $positions = [
            'Frontend Developer Intern', 'Backend Developer Intern', 'Fullstack Developer Intern',
            'Mobile Developer Intern', 'Data Analyst Intern', 'UI/UX Design Intern',
            'DevOps Intern', 'QA Tester Intern', 'Security Analyst Intern', 'AI Engineer Intern',
        ];
        $feedback = [
            'Sinh viên chăm chỉ, hoàn thành tốt công việc được giao.',
            'Sinh viên có thái độ tốt, ham học hỏi.',
            'Sinh viên cần cải thiện về kỹ năng giao tiếp.',
            'Sinh viên xuất sắc, có thể nhận vào làm chính thức.',
            'Sinh viên hoàn thành đúng tiến độ, đạt yêu cầu.',
        ];

        for ($studentId = 51; $studentId <= 100; $studentId++) {
            $status      = $statuses[array_rand($statuses)];
            $isCompleted = $status === 'COMPLETED';
            $lecturerId  = rand(1, 6);
            $companyId   = rand(1, 5);

            DB::table('internships')->insertOrIgnore([
                'student_id'          => $studentId,
                'lecturer_id'         => $lecturerId,
                'company_id'          => $companyId,
                'semester_id'         => 8,
                'status'              => $status,
                'company_grade'       => $isCompleted ? round(rand(65, 100) / 10, 1) : null,
                'university_grade'    => $isCompleted ? round(rand(65, 100) / 10, 1) : null,
                'company_feedback'    => $isCompleted ? $feedback[array_rand($feedback)] : null,
                'university_feedback' => $isCompleted ? 'Sinh viên đạt yêu cầu thực tập.' : null,
                'position'            => $positions[array_rand($positions)],
                'created_at'          => now(),
                'updated_at'          => now(),
            ]);
        }
    }
}
