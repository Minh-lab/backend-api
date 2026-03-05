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
        Schema::create('updated_topics', function (Blueprint $table) {
            $table->bigIncrements('updated_topic_id');
            $table->unsignedBigInteger('expertise_id');
            $table->string('title', 255)->notNull();
            $table->text('description')->nullable();
            $table->text('technologies')->notNull();

            $table->foreign('expertise_id')->references('expertise_id')->on('expertises')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('updated_topics');
    }
};
