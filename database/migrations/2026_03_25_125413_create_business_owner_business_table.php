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
        Schema::create('business_owner_business', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('business_owner_id')->constrained()->onDelete('cascade');
            $table->foreignUuid('business_id')->constrained()->onDelete('cascade');
            $table->enum('ownership_role', ['Owner','Co-owner','Representative'])->default('Owner');
            $table->decimal('ownership_percentage', 5, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_owner_business');
    }
};
