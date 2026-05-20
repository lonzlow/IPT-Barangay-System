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
        Schema::create('blotter_evidence', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('blotter_id')->constrained('blotters')->cascadeOnDelete();
            $table->string('file_path');
            $table->string('file_extension')->nullable(); // jpg, mp4, pdf
            $table->string('mime_type')->nullable();      // image/jpeg, video/mp4
            $table->enum('file_category', ['image','video','document'])->nullable();
            $table->string('caption')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blotter_evidence');
    }
};
