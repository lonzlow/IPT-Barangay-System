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
        Schema::create('business_owners', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->enum('owner_type', ['Resident', 'Non-resident', 'Organization'])->default('Resident');
            $table->foreignUuid('resident_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('first_name')->nullable();
            $table->string('middle_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('suffix')->nullable();
            $table->string('organization_name')->nullable();
            $table->string('contact_number')->nullable();
            $table->string('email')->nullable();
            $table->mediumText('address')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_owners');
    }
};
