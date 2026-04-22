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
        Schema::create('business_permits', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('business_id')->constrained('businesses')->onDelete('cascade');
            $table->string('permit_number')->unique();
            $table->date('expiry_date');
            $table->enum('permit_status', ['Pending', 'Approved', 'Expired', 'Renewed', 'Revoked', 'Suspended']);
            $table->foreignUuid('issued_by')->constrained('officials')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_permits');
    }
};
