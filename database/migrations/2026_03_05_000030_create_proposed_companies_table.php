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
        Schema::create('proposed_companies', function (Blueprint $table) {
            $table->bigIncrements('proposed_company_id');
            $table->string('name', 255)->notNull();
            $table->text('address')->nullable();
            $table->string('website', 255)->nullable();
            $table->string('tax_code', 50)->nullable();
            $table->string('contact_email', 255)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proposed_companies');
    }
};
