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
        Schema::create('students', function (Blueprint $table) {
            $table->bigIncrements('student_id');
            $table->string('usercode', 50)->unique()->notNull();
            $table->string('username', 255)->unique()->notNull();
            $table->string('password', 255)->notNull();
            $table->string('email', 255)->unique()->notNull();
            $table->tinyInteger('is_active')->default(1);
            $table->tinyInteger('first_login')->default(1);
            $table->string('full_name', 255)->notNull();
            $table->string('gender', 10)->nullable();
            $table->date('dob')->nullable();
            $table->string('phone_number', 15)->nullable();
            $table->unsignedBigInteger('class_id');
            $table->decimal('gpa', 3, 2)->default(0.00);
            $table->timestamps();

            $table->foreign('class_id')->references('class_id')->on('classes')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
