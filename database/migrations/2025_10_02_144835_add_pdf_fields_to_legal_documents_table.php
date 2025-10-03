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
        Schema::table('legal_documents', function (Blueprint $table) {
            $table->string('pdf_url')->nullable()->after('source_url');
            $table->string('pdf_file_name')->nullable()->after('pdf_url');
            $table->unsignedBigInteger('pdf_file_size')->nullable()->after('pdf_file_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('legal_documents', function (Blueprint $table) {
            $table->dropColumn(['pdf_url', 'pdf_file_name', 'pdf_file_size']);
        });
    }
};
