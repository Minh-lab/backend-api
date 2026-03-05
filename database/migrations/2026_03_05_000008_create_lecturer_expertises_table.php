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
        Schema::create('lecturer_expertises', function (Blueprint $table) {
            $table->unsignedBigInteger('lecturer_id');
            $table->unsignedBigInteger('expertise_id');
            $table->primary(['lecturer_id', 'expertise_id']);
            $table->timestamp('created_at')->nullable();

            $table->foreign('lecturer_id')->references('lecturer_id')->on('lecturers')->onDelete('cascade');
            $table->foreign('expertise_id')->references('expertise_id')->on('expertises')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lecturer_expertises');
    }
};
