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
        // database/migrations/2024_01_01_000003_create_companies_table.php
        Schema::create('companies', function (Blueprint $table) {
            $table->bigIncrements('company_id');
            $table->string('usercode', 50)->unique()->notNull();
            $table->string('username', 255)->unique()->notNull();
            $table->string('password', 255)->notNull();
            $table->string('email', 255)->unique()->notNull();
            $table->tinyInteger('is_active')->default(1);
            $table->tinyInteger('first_login')->default(1);
            $table->string('name', 255)->notNull();
            $table->string('address', 500)->nullable();
            $table->string('website', 255)->nullable();
            $table->tinyInteger('is_partnered')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
