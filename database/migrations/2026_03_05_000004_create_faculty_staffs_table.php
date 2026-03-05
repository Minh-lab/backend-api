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
        Schema::create('faculty_staffs', function (Blueprint $table) {
            $table->bigIncrements('faculty_staff_id');
            $table->string('usercode', 50)->unique()->notNull();
            $table->string('username', 255)->unique()->notNull();
            $table->string('password', 255)->notNull();
            $table->string('email', 255)->unique()->notNull();
            $table->string('full_name', 255)->notNull();
            $table->string('phone_number', 15)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('faculty_staff');
    }
};
