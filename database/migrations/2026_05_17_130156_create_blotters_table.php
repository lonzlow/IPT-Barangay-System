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
        Schema::create('blotters', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('case_number')->unique();
            // If the complainant is a resident inside barangay, leave the ID blank if not resident
            $table->uuid('complainant_id')->nullable()->constrained('residents')->nullOnDelete();
            $table->string('complainant_name')->nullable();
            $table->string('location')->nullable();
            $table->text('incident_description');
            $table->dateTime('incident_date');
            $table->foreignUuid('handled_by')->nullable()->constrained('officials'); // Still waiting for official to handle this case initially
            $table->enum('status', ['pending', 'under investigation', 'resolved', 'referred', 'dismissed'])->default('pending');
            $table->foreignUuid('filed_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('blotter_respondents', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('blotter_id')->constrained('blotters')->cascadeOnDelete();
            // If the respondent is a resident inside barangay, leave the ID blank if not resident
            $table->uuid('respondent_id')->nullable()->constrained('residents')->nullOnDelete();
            $table->string('respondent_name')->nullable();
            $table->string('role')->nullable(); // primary offender, accomplice
            $table->timestamps();
            $table->softDeletes();
        });

        // Optional
        Schema::create('blotter_witnesses', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('blotter_id')->constrained('blotters')->cascadeOnDelete();
            $table->uuid('witness_id')->nullable()->constrained('residents')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blotters');
        Schema::dropIfExists('blotter_respondents');
        Schema::dropIfExists('blotter_witnesses');
    }
};
