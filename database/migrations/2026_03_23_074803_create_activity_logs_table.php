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
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('user_id')->constrained()->onDelete('cascade');
            $table->string('action');
            $table->enum('module', [
                'Authentication',
                'Resident Management',
                'Document Issuance',
                'Blotter Management',
                'Household Management',
                'Purok Management',
                'Business Permit Management',
                'Officials and Staff Management',
                'Committee Management',
                'User Management',
                'Reports Management',
            ]);
            $table->mediumText('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
