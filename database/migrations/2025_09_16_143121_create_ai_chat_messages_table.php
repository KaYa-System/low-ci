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
        Schema::create('ai_chat_messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('session_id');
            $table->enum('role', ['user', 'assistant']);
            $table->longText('content');
            $table->json('metadata')->nullable(); // Références aux documents légaux cités
            $table->timestamp('sent_at');
            $table->timestamps();
            
            $table->foreign('session_id')->references('id')->on('ai_chat_sessions')->onDelete('cascade');
            $table->index(['session_id', 'sent_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_chat_messages');
    }
};
