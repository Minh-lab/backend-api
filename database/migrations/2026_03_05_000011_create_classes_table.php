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
        Schema::create('classes', function (Blueprint $table) {
            $table->bigIncrements('class_id');
            $table->unsignedBigInteger('lecturer_id');
            $table->string('class_name', 255)->notNull();
            $table->unsignedBigInteger('major_id');
            $table->timestamps();

            $table->foreign('lecturer_id')->references('lecturer_id')->on('lecturers')->onDelete('restrict');
            $table->foreign('major_id')->references('major_id')->on('majors')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('classes');
    }
};
