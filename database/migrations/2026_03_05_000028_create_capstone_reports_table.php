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
        Schema::create('capstone_reports', function (Blueprint $table) {
            $table->bigIncrements('report_id');
            $table->unsignedBigInteger('capstone_id');
            $table->unsignedBigInteger('milestone_id');
            $table->string('status', 100)->default('PENDING');
            $table->string('file_path', 255)->notNull();
            $table->text('lecturer_feedback')->nullable();
            $table->dateTime('submission_date')->notNull();
            $table->timestamps();

            $table->foreign('capstone_id')->references('capstone_id')->on('capstones')->onDelete('cascade');
            $table->foreign('milestone_id')->references('milestone_id')->on('milestones')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('capstone_reports');
    }
};
