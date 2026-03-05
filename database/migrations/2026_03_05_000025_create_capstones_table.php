<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // database/migrations/2024_01_01_000025_create_capstones_table.php
        Schema::create('capstones', function (Blueprint $table) {
            $table->bigIncrements('capstone_id');
            $table->unsignedBigInteger('topic_id');
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('lecturer_id');
            $table->unsignedBigInteger('council_id')->nullable();
            $table->unsignedBigInteger('semester_id');
            $table->string('status', 100)->default('INITIALIZED');
            // INITIALIZED | LECTURER_APPROVED | TOPIC_APPROVED | REPORTING
            // OFFICIAL_SUBMITTED | REVIEW_ELIGIBLE | DEFENSE_ELIGIBLE
            // CANCEL | FAILED | COMPLETED
            $table->decimal('instructor_grade', 4, 2)->nullable();
            $table->decimal('council_grade', 4, 2)->nullable();
            $table->unsignedTinyInteger('defense_order')->nullable();
            $table->timestamps();

            $table->foreign('topic_id')->references('topic_id')->on('topics')->onDelete('restrict');
            $table->foreign('student_id')->references('student_id')->on('students')->onDelete('restrict');
            $table->foreign('lecturer_id')->references('lecturer_id')->on('lecturers')->onDelete('restrict');
            $table->foreign('council_id')->references('council_id')->on('councils')->onDelete('set null');
            $table->foreign('semester_id')->references('semester_id')->on('semesters')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('capstones');
    }
};
