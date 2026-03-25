<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LoginSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Lấy role_id động để tránh lỗi logic nếu ID trong DB thay đổi
        $roles = DB::table('roles')->pluck('role_id', 'role_name');

        $required = ['admin', 'faculty_staff', 'lecturer', 'student', 'company'];
        foreach ($required as $r) {
            if (!isset($roles[$r])) {
                $this->command->error("Thiếu role: {$r} — Hãy chạy RoleSeeder trước!");
                return;
            }
        }

        $rows = [];

        // 2. Lấy ID của tất cả sinh viên hiện có (bao gồm cả 30 bạn mới thêm)
        $studentIds = DB::table('students')->pluck('student_id');
        foreach ($studentIds as $id) {
            $rows[] = ['user_id' => $id, 'role_id' => $roles['student']];
        }

        // 3. Lấy ID của các giảng viên hiện có
        $lecturerIds = DB::table('lecturers')->pluck('lecturer_id');
        foreach ($lecturerIds as $id) {
            $rows[] = ['user_id' => $id, 'role_id' => $roles['lecturer']];
        }

        // 4. Các role khác (Admin, VPK, Doanh nghiệp) - Lấy động tương tự
        $adminIds = DB::table('admins')->pluck('admin_id');
        foreach ($adminIds as $id) {
            $rows[] = ['user_id' => $id, 'role_id' => $roles['admin']];
        }

        $staffIds = DB::table('faculty_staffs')->pluck('faculty_staff_id');
        foreach ($staffIds as $id) {
            $rows[] = ['user_id' => $id, 'role_id' => $roles['faculty_staff']];
        }

        $companyIds = DB::table('companies')->pluck('company_id');
        foreach ($companyIds as $id) {
            $rows[] = ['user_id' => $id, 'role_id' => $roles['company']];
        }

        // 5. Insert hàng loạt để tối ưu hiệu năng
        foreach ($rows as $row) {
            DB::table('logins')->insertOrIgnore(array_merge($row, [
                'login_attempts' => 0,
                'lockout_until'  => null,
                'created_at'     => now(),
                'updated_at'     => now(),
            ]));
        }

    }
}