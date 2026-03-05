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
        Schema::create('lecturer_requests', function (Blueprint $table) {
            $table->bigIncrements('request_id');
            $table->unsignedBigInteger('lecturer_id');
            $table->unsignedBigInteger('updated_topic_id')->nullable();
            $table->unsignedBigInteger('topic_id')->nullable();
            $table->string('type', 100)->notNull(); // LEAVE_REQ | TOPIC_ADD | TOPIC_EDIT | TOPIC_DEL
            $table->string('status', 100)->default('PENDING'); // PENDING | APPROVED | REJECTED
            $table->string('title', 255)->notNull();
            $table->text('description')->nullable();
            $table->string('file_path', 255)->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->text('faculty_feedback')->nullable();
            $table->timestamps();

            $table->foreign('lecturer_id')->references('lecturer_id')->on('lecturers')->onDelete('cascade');
            $table->foreign('updated_topic_id')->references('updated_topic_id')->on('updated_topics')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lecturer_requests');
    }
};
