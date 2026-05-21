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
        Schema::create('signatures', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('official_id')->index();
            $table->string('label')->nullable();
            $table->string('path');
            $table->uuid('uploaded_by')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('official_id')->references('id')->on('officials')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('signatures');
    }
};
