<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::create('response_ai_table', function (Blueprint $table) {
        $table->id();
        $table->string('chat_id'); // 🔥 Add this line
        $table->text('question');
        $table->text('ai_answer');
        $table->string('model_used')->nullable(); // Optional, since you're using it
        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('response_ai_table');
    }
};