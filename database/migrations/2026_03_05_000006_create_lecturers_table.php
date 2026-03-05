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
        Schema::create('lecturers', function (Blueprint $table) {
            $table->bigIncrements('lecturer_id');
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
            $table->string('degree', 100)->nullable();
            $table->string('department', 255)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lecturers');
    }
};
