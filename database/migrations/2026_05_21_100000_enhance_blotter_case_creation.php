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
        Schema::table('blotters', function (Blueprint $table) {
            if (! Schema::hasColumn('blotters', 'incident_title')) {
                $table->string('incident_title')->nullable()->after('case_number');
            }
        });

        Schema::table('blotter_witnesses', function (Blueprint $table) {
            if (! Schema::hasColumn('blotter_witnesses', 'witness_name')) {
                $table->string('witness_name')->nullable()->after('witness_id');
            }
        });

        if (Schema::hasTable('blotter_evidence') && ! Schema::hasTable('blotter_evidences')) {
            Schema::rename('blotter_evidence', 'blotter_evidences');
        }

        Schema::table('blotter_evidences', function (Blueprint $table) {
            if (! Schema::hasColumn('blotter_evidences', 'file_name')) {
                $table->string('file_name')->nullable()->after('file_path');
            }

            if (! Schema::hasColumn('blotter_evidences', 'file_type')) {
                $table->string('file_type')->nullable()->after('file_name');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('blotter_evidences', function (Blueprint $table) {
            if (Schema::hasColumn('blotter_evidences', 'file_type')) {
                $table->dropColumn('file_type');
            }

            if (Schema::hasColumn('blotter_evidences', 'file_name')) {
                $table->dropColumn('file_name');
            }
        });

        if (Schema::hasTable('blotter_evidences') && ! Schema::hasTable('blotter_evidence')) {
            Schema::rename('blotter_evidences', 'blotter_evidence');
        }

        Schema::table('blotter_witnesses', function (Blueprint $table) {
            if (Schema::hasColumn('blotter_witnesses', 'witness_name')) {
                $table->dropColumn('witness_name');
            }
        });

        Schema::table('blotters', function (Blueprint $table) {
            if (Schema::hasColumn('blotters', 'incident_title')) {
                $table->dropColumn('incident_title');
            }
        });
    }
};
