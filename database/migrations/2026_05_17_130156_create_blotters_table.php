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
            $table->id();
            $table->string('case_number')->unique();
            $table->string('complainant');
            $table->string('respondent');
            $table->text('incident_description');
            $table->dateTime('incident_date');
            $table->enum('status', ['open', 'ongoing', 'resolved', 'referred'])->default('open');
            $table->json('parties')->nullable();
            $table->foreignUuid('filed_by')->constrained('users')->cascadeOnDelete();
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
    }
};
