<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('capstone_reports', function (Blueprint $table) {
            $table->string('link', 255)->nullable()->after('file_path');
        });
    }

    public function down(): void
    {
        Schema::table('capstone_reports', function (Blueprint $table) {
            $table->dropColumn('link');
        });
    }
};
