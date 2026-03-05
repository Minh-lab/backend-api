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
        Schema::create('password_resets', function (Blueprint $table) {
            $table->bigIncrements('password_reset_id');
            $table->unsignedBigInteger('user_id')->notNull();
            $table->unsignedBigInteger('role_id');
            $table->string('otp', 10)->notNull();
            $table->timestamp('expired_at')->notNull();
            $table->tinyInteger('is_used')->default(0);
            $table->timestamp('created_at')->nullable();

            $table->foreign('role_id')->references('role_id')->on('roles')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('password_resets');
    }
};
