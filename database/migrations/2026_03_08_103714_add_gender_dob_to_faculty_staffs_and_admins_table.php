<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('faculty_staffs', function (Blueprint $table) {
            $table->string('gender', 10)->nullable()->after('full_name');
            $table->date('dob')->nullable()->after('gender');
        });

        Schema::table('admins', function (Blueprint $table) {
            $table->string('gender', 10)->nullable()->after('full_name');
            $table->date('dob')->nullable()->after('gender');
        });
    }

    public function down(): void
    {
        Schema::table('faculty_staffs', function (Blueprint $table) {
            $table->dropColumn(['gender', 'dob']);
        });

        Schema::table('admins', function (Blueprint $table) {
            $table->dropColumn(['gender', 'dob']);
        });
    }
};