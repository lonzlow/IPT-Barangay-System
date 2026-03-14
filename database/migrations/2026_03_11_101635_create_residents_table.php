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
        Schema::create('residents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->string('suffix')->nullable();
            $table->string('email')->unique();
            $table->string('contact_number');
            $table->date('birthdate');
            $table->enum('gender', ['Male', 'Female', 'Other']);
            $table->enum('civil_status', ['Single', 'Married', 'Widowed', 'Separated', 'Divorced']);
            $table->enum('voter_status', ['Registered', 'Unregistered', 'Suspended']);
            $table->enum('residency_status', ['Active', 'Deceased', 'Transferred']);
            $table->foreignId('household_id')->constrained('households')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('residents');
    }
};
