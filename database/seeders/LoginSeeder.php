<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LoginSeeder extends Seeder
{
    public function run(): void
    {
        // role_id: 1=admin, 2=faculty_staff, 3=lecturer, 4=student, 5=company
        $rows = [];

        foreach ([1, 2] as $id)          $rows[] = ['user_id' => $id, 'role_id' => 1]; // admins
        foreach ([1, 2, 3] as $id)       $rows[] = ['user_id' => $id, 'role_id' => 2]; // faculty_staffs
        foreach (range(1, 6) as $id)     $rows[] = ['user_id' => $id, 'role_id' => 3]; // lecturers
        foreach (range(1, 10) as $id)    $rows[] = ['user_id' => $id, 'role_id' => 4]; // students
        foreach (range(1, 5) as $id)     $rows[] = ['user_id' => $id, 'role_id' => 5]; // companies

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
