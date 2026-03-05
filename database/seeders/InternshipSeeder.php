<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InternshipSeeder extends Seeder
{
    public function run(): void
    {
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
    }
}
