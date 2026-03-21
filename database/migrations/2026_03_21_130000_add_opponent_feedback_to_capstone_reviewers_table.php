<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('capstone_reviewers', function (Blueprint $table) {
            $table->text('opponent_feedback')->nullable()->after('opponent_grade');
        });
    }

    public function down(): void
    {
        Schema::table('capstone_reviewers', function (Blueprint $table) {
            $table->dropColumn('opponent_feedback');
        });
    }
};
