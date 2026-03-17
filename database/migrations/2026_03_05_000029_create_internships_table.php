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
        Schema::create('internships', function (Blueprint $table) {
            $table->bigIncrements('internship_id');
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('lecturer_id')->nullable();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('semester_id');
            $table->string('status', 100)->default('INITIALIZED');
            // INITIALIZED | LECTURER_APPROVED | COMPANY_APPROVED
            // INTERNING | CANCEL | FAILED | COMPLETED
            $table->decimal('company_grade', 4, 2)->nullable();
            $table->text('company_feedback')->nullable();
            $table->text('university_feedback')->nullable();
            $table->string('position', 255)->nullable();
            $table->decimal('university_grade', 4, 2)->nullable();
            $table->timestamp('created_at')->notNull();
            $table->timestamp('updated_at')->nullable();

            $table->foreign('student_id')->references('student_id')->on('students')->onDelete('restrict');
            $table->foreign('lecturer_id')->references('lecturer_id')->on('lecturers')->onDelete('restrict');
            $table->foreign('company_id')->references('company_id')->on('companies')->onDelete('restrict');
            $table->foreign('semester_id')->references('semester_id')->on('semesters')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('internships');
    }
};
