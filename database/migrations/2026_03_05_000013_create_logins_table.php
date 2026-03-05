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
        Schema::create('logins', function (Blueprint $table) {
            $table->bigIncrements('login_id');
            $table->unsignedBigInteger('user_id')->notNull();
            $table->unsignedBigInteger('role_id');
            $table->tinyInteger('login_attempts')->default(0);
            $table->timestamp('lockout_until')->nullable();
            $table->timestamps();

            $table->foreign('role_id')->references('role_id')->on('roles')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('logins');
    }
};
