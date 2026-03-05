<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PasswordResetSeeder extends Seeder
{
    public function run(): void
    {
        // Dữ liệu mẫu - OTP đã hết hạn và đã dùng
        $rows = [
            [
                'user_id'    => 1,
                'role_id'    => 4, // student
                'otp'        => '123456',
                'expired_at' => now()->subHours(2),
                'is_used'    => 1,
                'created_at' => now()->subHours(2),
            ],
            [
                'user_id'    => 1,
                'role_id'    => 3, // lecturer
                'otp'        => '654321',
                'expired_at' => now()->subHours(1),
                'is_used'    => 1,
                'created_at' => now()->subHours(1),
            ],
        ];

        foreach ($rows as $row) {
            DB::table('password_resets')->insertOrIgnore($row);
        }
    }
}
