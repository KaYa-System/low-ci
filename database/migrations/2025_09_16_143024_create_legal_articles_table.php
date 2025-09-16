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
        Schema::create('legal_articles', function (Blueprint $table) {
            $table->id();
            $table->string('number'); // Article 1, Article 2, etc.
            $table->string('title')->nullable();
            $table->longText('content');
            $table->longText('commentary')->nullable(); // Commentaire explicatif
            $table->unsignedBigInteger('document_id');
            $table->unsignedBigInteger('section_id')->nullable();
            $table->integer('sort_order')->default(0);
            $table->json('cross_references')->nullable(); // Références croisées
            $table->timestamps();
            
            $table->foreign('document_id')->references('id')->on('legal_documents')->onDelete('cascade');
            $table->foreign('section_id')->references('id')->on('legal_sections')->onDelete('set null');
            $table->index(['document_id', 'sort_order']);
            $table->index(['section_id']);
            $table->index(['number']);
            $table->index(['title']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('legal_articles');
    }
};
