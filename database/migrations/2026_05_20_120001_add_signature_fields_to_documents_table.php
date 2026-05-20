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
        Schema::table('documents', function (Blueprint $table) {
            $table->uuid('signature_id')->nullable()->after('signature_path')->index();
            $table->uuid('issued_by_official_id')->nullable()->after('issued_by')->index();

            $table->foreign('signature_id')->references('id')->on('signatures')->onDelete('set null');
            $table->foreign('issued_by_official_id')->references('id')->on('officials')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropForeign(['signature_id']);
            $table->dropForeign(['issued_by_official_id']);
            $table->dropColumn(['signature_id', 'issued_by_official_id']);
        });
    }
};
