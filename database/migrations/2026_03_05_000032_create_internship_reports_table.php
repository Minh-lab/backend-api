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
        // database/migrations/2024_01_01_000032_create_internship_reports_table.php
        Schema::create('internship_reports', function (Blueprint $table) {
            $table->bigIncrements('report_id');
            $table->unsignedBigInteger('internship_id');
            $table->unsignedBigInteger('milestone_id');
            $table->string('status', 100)->default('PENDING'); // PENDING | APPROVED | REJECTED
            $table->text('description')->nullable();
            $table->text('lecturer_feedback')->nullable();
            $table->string('file_path', 255)->notNull();
            $table->dateTime('submission_date')->notNull();
            $table->timestamps();

            $table->foreign('internship_id')->references('internship_id')->on('internships')->onDelete('cascade');
            $table->foreign('milestone_id')->references('milestone_id')->on('milestones')->onDelete('restrict');
        });
        ;
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('internship_reports');
    }
};
