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
        Schema::create('legal_documents', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('summary')->nullable();
            $table->longText('content');
            $table->enum('type', ['loi', 'decret', 'arrete', 'ordonnance', 'constitution', 'code', 'autre']);
            $table->string('reference_number')->nullable(); // Numéro officiel
            $table->date('publication_date')->nullable();
            $table->date('effective_date')->nullable();
            $table->string('journal_officiel')->nullable(); // Référence JO
            $table->string('status')->default('active'); // active, abrogé, modifié
            $table->unsignedBigInteger('category_id')->nullable();
            $table->string('source_url')->nullable();
            $table->json('metadata')->nullable(); // Données supplémentaires
            $table->integer('views_count')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->timestamps();
            
            $table->foreign('category_id')->references('id')->on('legal_categories')->onDelete('set null');
            $table->index(['type', 'status']);
            $table->index(['publication_date']);
            $table->index(['category_id']);
            $table->index(['title']);
            $table->index(['status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('legal_documents');
    }
};
