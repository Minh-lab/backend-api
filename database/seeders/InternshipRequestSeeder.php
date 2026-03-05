<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InternshipRequestSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['internship_id' => 1, 'proposed_company_id' => null, 'company_id' => 1,
             'type' => 'COMPANY_REG', 'status' => 'APPROVED',
             'student_message' => 'Em xin đăng ký thực tập tại FPT Software',
             'feedback' => 'Đồng ý, công ty đối tác uy tín', 'file_path' => null],

            ['internship_id' => 2, 'proposed_company_id' => null, 'company_id' => 3,
             'type' => 'COMPANY_REG', 'status' => 'APPROVED',
             'student_message' => 'Em xin đăng ký thực tập tại NashTech',
             'feedback' => 'Chấp nhận', 'file_path' => null],

            ['internship_id' => 3, 'proposed_company_id' => null, 'company_id' => 2,
             'type' => 'COMPANY_REG', 'status' => 'APPROVED',
             'student_message' => 'Em xin đăng ký thực tập tại Viettel',
             'feedback' => 'Đồng ý', 'file_path' => null],

            ['internship_id' => 4, 'proposed_company_id' => 1, 'company_id' => null,
             'type' => 'COMPANY_REG', 'status' => 'PENDING_TEACHER',
             'student_message' => 'Em xin đề xuất công ty TechStart để thực tập',
             'feedback' => null, 'file_path' => 'requests/internship/sv9_techstart.pdf'],
        ];

        foreach ($rows as $row) {
            DB::table('internship_requests')->insertOrIgnore(array_merge($row, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}
