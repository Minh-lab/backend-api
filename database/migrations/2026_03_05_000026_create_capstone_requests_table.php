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
        Schema::create('capstone_requests', function (Blueprint $table) {
            $table->bigIncrements('capstone_request_id');
            $table->unsignedBigInteger('proposed_topic_id')->nullable();
            $table->unsignedBigInteger('capstone_id');
            $table->unsignedBigInteger('lecturer_id')->nullable();
            $table->unsignedBigInteger('topic_id')->nullable();
            $table->string('type', 100)->notNull();
            // LECTURER_REG | TOPIC_PROP | TOPIC_BANK | CANCEL_REQ
            $table->string('status', 100)->default('PENDING_TEACHER');
            // PENDING_TEACHER | PENDING_FACULTY | APPROVED | REJECTED
            $table->text('student_message')->nullable();
            $table->text('lecturer_feedback')->nullable();
            $table->string('file_path', 255)->nullable();
            $table->timestamps();

            $table->foreign('proposed_topic_id')->references('proposed_topic_id')->on('proposed_topics')->onDelete('set null');
            $table->foreign('capstone_id')->references('capstone_id')->on('capstones')->onDelete('cascade');
            $table->foreign('lecturer_id')->references('lecturer_id')->on('lecturers')->onDelete('set null');
            $table->foreign('topic_id')->references('topic_id')->on('topics')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('capstone_requests');
    }
};
