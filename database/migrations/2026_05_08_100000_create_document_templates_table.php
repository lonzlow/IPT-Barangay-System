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
        Schema::create('document_templates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->unique(); // Indigency, Residency, Barangay Clearance, Good Moral Character, Business Clearance
            $table->text('description')->nullable();
            $table->longText('template_html'); // HTML template with placeholders like {{resident_name}}, {{address}}, etc.
            $table->json('fields_required')->nullable(); // Array of fields to extract from Resident model
            $table->integer('validity_days')->nullable(); // How many days the document is valid from issue date
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_templates');
    }
};
