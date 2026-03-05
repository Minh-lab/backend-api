<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_notifications', function (Blueprint $table) {
            $table->unsignedBigInteger('notification_id');
            $table->unsignedBigInteger('user_id');   // polymorphic: có thể là student_id, lecturer_id,...
            $table->unsignedBigInteger('role_id');   // dùng role_id để phân biệt loại user
            $table->primary(['notification_id', 'user_id', 'role_id']);
            $table->tinyInteger('is_read')->default(0);
            $table->timestamps();

            // Chỉ FK notification_id, KHÔNG FK user_id vì là polymorphic
            $table->foreign('notification_id')
                  ->references('notification_id')
                  ->on('notifications')
                  ->onDelete('cascade');

            $table->foreign('role_id')
                  ->references('role_id')
                  ->on('roles')
                  ->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_notifications');
    }
};