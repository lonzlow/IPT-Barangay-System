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
        Schema::create('permit_renewals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('permit_id')->constrained('business_permits')->onDelete('cascade');
            $table->decimal('fee_paid');
            $table->date('renewal_date');
            $table->foreignUuid('processed_by')->constrained('officials')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permit_renewals');
    }
};
