<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->foreignUuid('business_id')
                ->nullable()
                ->after('document_template_id')
                ->constrained('businesses')
                ->nullOnDelete();

            $table->text('additional_notes')
                ->nullable()
                ->after('purpose');
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropForeign(['business_id']);
            $table->dropColumn('business_id');
            $table->dropColumn('additional_notes');
        });
    }
};
