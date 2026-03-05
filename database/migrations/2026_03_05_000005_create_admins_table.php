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
        Schema::create('admins', function (Blueprint $table) {
            $table->bigIncrements('admin_id');
            $table->string('usercode', 50)->unique()->notNull();
            $table->string('username', 255)->unique()->notNull();
            $table->string('password', 255)->notNull();
            $table->string('email', 255)->unique()->notNull();
            $table->string('full_name', 255)->notNull();
            $table->timestamps();
        });
        ;
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admins');
    }
};
