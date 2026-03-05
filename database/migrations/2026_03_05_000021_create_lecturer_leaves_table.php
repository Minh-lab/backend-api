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
        Schema::create('lecturer_leaves', function (Blueprint $table) {
            $table->bigIncrements('leave_id');
            $table->unsignedBigInteger('request_id')->unique();
            $table->date('start_date')->notNull();
            $table->date('end_date')->notNull();
            $table->string('status', 100)->default('APPROVED_PENDING');
            // APPROVED_PENDING | LEAVE_ACTIVE | CANCELLED | COMPLETED
            $table->tinyInteger('delegate_completed')->default(0);
            $table->timestamps();

            $table->foreign('request_id')->references('request_id')->on('lecturer_requests')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lecturer_leaves');
    }
};
