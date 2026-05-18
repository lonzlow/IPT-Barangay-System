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
        Schema::create('official_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('official_id')->constrained('officials')->cascadeOnDelete();
            $table->foreignId('committee_id')->nullable()->constrained('committees')->cascadeOnDelete();
            $table->string('designation');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('official_assignments');
    }
};
