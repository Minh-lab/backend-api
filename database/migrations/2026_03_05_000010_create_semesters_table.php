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
        Schema::create('semesters', function (Blueprint $table) {
            $table->bigIncrements('semester_id');
            $table->unsignedBigInteger('year_id');
            $table->string('semester_name', 100)->notNull();
            $table->date('start_date')->notNull();
            $table->date('end_date')->notNull();
            $table->timestamps();

            $table->foreign('year_id')->references('year_id')->on('academic_years')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('semesters');
    }
};
