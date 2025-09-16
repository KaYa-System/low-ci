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
        Schema::create('legal_sections', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('number')->nullable(); // Numéro de section (ex: "Titre I", "Chapitre II")
            $table->text('description')->nullable();
            $table->longText('content')->nullable();
            $table->unsignedBigInteger('document_id');
            $table->unsignedBigInteger('parent_section_id')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            
            $table->foreign('document_id')->references('id')->on('legal_documents')->onDelete('cascade');
            $table->foreign('parent_section_id')->references('id')->on('legal_sections')->onDelete('cascade');
            $table->index(['document_id', 'sort_order']);
            $table->index(['parent_section_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('legal_sections');
    }
};
