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
        Schema::create('milestones', function (Blueprint $table) {
            $table->bigIncrements('milestone_id');
            $table->unsignedBigInteger('semester_id');
            $table->string('phase_name', 255)->notNull();
            $table->text('description')->nullable();
            $table->string('type', 50)->notNull(); // CAPSTONE | INTERNSHIP
            $table->dateTime('deadline')->notNull();
            $table->timestamps();

            $table->foreign('semester_id')->references('semester_id')->on('semesters')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('milestones');
    }
};
