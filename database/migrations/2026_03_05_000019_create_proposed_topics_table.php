<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proposed_topics', function (Blueprint $table) {
            $table->bigIncrements('proposed_topic_id');
            $table->unsignedBigInteger('expertise_id');
            $table->string('proposed_title', 255)->notNull();
            $table->text('proposed_description')->nullable();
            $table->text('technologies')->notNull();

            $table->foreign('expertise_id')
                  ->references('expertise_id')
                  ->on('expertises')
                  ->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proposed_topics');
    }
};