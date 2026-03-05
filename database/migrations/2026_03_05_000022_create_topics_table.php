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
        Schema::create('topics', function (Blueprint $table) {
            $table->bigIncrements('topic_id');
            $table->unsignedBigInteger('expertise_id');
            $table->unsignedBigInteger('lecturer_id')->nullable();
            $table->unsignedBigInteger('faculty_staff_id')->nullable();
            $table->string('title', 255)->notNull();
            $table->text('description')->nullable();
            $table->text('technologies')->notNull();
            $table->tinyInteger('is_available')->default(1);
            $table->tinyInteger('is_bank_topic')->default(1);
            $table->timestamps();

            $table->foreign('expertise_id')->references('expertise_id')->on('expertises')->onDelete('restrict');
            $table->foreign('lecturer_id')->references('lecturer_id')->on('lecturers')->onDelete('set null');
            $table->foreign('faculty_staff_id')->references('faculty_staff_id')->on('faculty_staffs')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('topics');
    }
};
