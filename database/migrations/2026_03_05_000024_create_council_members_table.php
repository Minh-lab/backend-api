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
        Schema::create('council_members', function (Blueprint $table) {
            $table->unsignedBigInteger('council_id');
            $table->unsignedBigInteger('lecturer_id');
            $table->primary(['council_id', 'lecturer_id']);
            $table->string('position', 100)->nullable();
            // chairman | secretary | member | reviewer_member
            $table->timestamps();

            $table->foreign('council_id')->references('council_id')->on('councils')->onDelete('cascade');
            $table->foreign('lecturer_id')->references('lecturer_id')->on('lecturers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('council_members');
    }
};
