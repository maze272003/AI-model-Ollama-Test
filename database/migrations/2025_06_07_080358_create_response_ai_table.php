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
            $table->text('question'); // Column para sa tanong
            $table->text('ai_answer'); // Column para sa sagot ng AI
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