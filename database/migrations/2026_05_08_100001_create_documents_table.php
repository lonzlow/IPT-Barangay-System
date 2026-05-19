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
        Schema::create('documents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('resident_id')->index();
            $table->uuid('document_template_id')->index();
            $table->string('reference_number')->unique(); // e.g., DOC-2026-001
            $table->text('purpose')->nullable(); // Reason for issuance
            $table->longText('rendered_html'); // Final HTML after replacing placeholders
            $table->string('issued_by'); // Name of person/staff issuing the document
            $table->date('issued_date');
            $table->date('valid_until')->nullable();
            $table->string('status')->default('Issued'); // Issued, Revoked, Expired
            $table->string('seal_path')->nullable(); // Path to official seal image
            $table->string('signature_path')->nullable(); // Path to authorized signature image
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('resident_id')->references('id')->on('residents')->onDelete('cascade');
            $table->foreign('document_template_id')->references('id')->on('document_templates')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
