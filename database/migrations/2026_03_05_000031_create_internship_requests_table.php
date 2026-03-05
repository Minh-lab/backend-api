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
        Schema::create('internship_requests', function (Blueprint $table) {
            $table->bigIncrements('internship_request_id');
            $table->unsignedBigInteger('internship_id');
            $table->unsignedBigInteger('proposed_company_id')->nullable();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->string('type', 100)->notNull(); // COMPANY_REG | CANCEL_REQ
            $table->string('status', 100)->default('PENDING_TEACHER');
            // PENDING_TEACHER | PENDING_FACULTY | PENDING_COMPANY | APPROVED | REJECTED
            $table->text('student_message')->nullable();
            $table->text('feedback')->nullable();
            $table->string('file_path', 255)->nullable();
            $table->timestamps();

            $table->foreign('internship_id')->references('internship_id')->on('internships')->onDelete('cascade');
            $table->foreign('proposed_company_id')->references('proposed_company_id')->on('proposed_companies')->onDelete('set null');
            $table->foreign('company_id')->references('company_id')->on('companies')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('internship_requests');
    }
};
