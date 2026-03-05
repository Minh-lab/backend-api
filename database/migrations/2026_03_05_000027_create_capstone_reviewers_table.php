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
        Schema::create('capstone_reviewers', function (Blueprint $table) {
            $table->unsignedBigInteger('capstone_id');
            $table->unsignedBigInteger('lecturer_id');
            $table->primary(['capstone_id', 'lecturer_id']);
            $table->decimal('opponent_grade', 4, 2)->nullable();
            $table->timestamps();

            $table->foreign('capstone_id')->references('capstone_id')->on('capstones')->onDelete('cascade');
            $table->foreign('lecturer_id')->references('lecturer_id')->on('lecturers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('capstone_reviewers');
    }
};
