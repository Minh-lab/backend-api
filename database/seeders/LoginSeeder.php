<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LoginSeeder extends Seeder
{
    public function run(): void
    {
        // Lấy role_id động theo role_name → tránh hardcode
        $roles = DB::table('roles')->pluck('role_id', 'role_name');

        // Kiểm tra đủ roles chưa
        $required = ['admin', 'faculty_staff', 'lecturer', 'student', 'company'];
        foreach ($required as $r) {
            if (!isset($roles[$r])) {
                $this->command->error("Thiếu role: {$r} — chạy RoleSeeder trước!");
                return;
            }
        }

        $rows = [];

        // Admins
        foreach ([1, 2] as $id) {
            $rows[] = ['user_id' => $id, 'role_id' => $roles['admin']];
        }

        // Faculty staffs (VPK)
        foreach ([1, 2, 3] as $id) {
            $rows[] = ['user_id' => $id, 'role_id' => $roles['faculty_staff']];
        }

        // Lecturers
        foreach (range(1, 6) as $id) {
            $rows[] = ['user_id' => $id, 'role_id' => $roles['lecturer']];
        }

        // Students
        foreach (range(1, 10) as $id) {
            $rows[] = ['user_id' => $id, 'role_id' => $roles['student']];
        }

        // Companies
        foreach (range(1, 5) as $id) {
            $rows[] = ['user_id' => $id, 'role_id' => $roles['company']];
        }

        foreach ($rows as $row) {
            DB::table('logins')->insertOrIgnore(array_merge($row, [
                'login_attempts' => 0,
                'lockout_until'  => null,
                'created_at'     => now(),
                'updated_at'     => now(),
            ]));
        }

        $this->command->info('LoginSeeder hoàn thành: ' . count($rows) . ' records.');
    }
}