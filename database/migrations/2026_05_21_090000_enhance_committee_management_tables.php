<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const RECORD_TYPES = [
        'photo',
        'video',
        'activity',
        'accomplishment',
        'report',
        'attendance',
        'inventory',
        'partnership',
        'certificate',
        'blotter_incident',
        'resolution_policy',
        'training_seminar',
        'personnel_list',
        'project_proposal',
        'financial_record',
        'permit_contract',
        'driver_operator_profile',
        'emergency_log',
        'evacuation_center_record',
    ];

    public function up(): void
    {
        Schema::table('committees', function (Blueprint $table) {
            if (! Schema::hasColumn('committees', 'slug')) {
                $table->string('slug')->nullable()->unique()->after('name');
            }

            if (! Schema::hasColumn('committees', 'chair_label')) {
                $table->string('chair_label')->nullable()->after('chairperson_id');
            }

            if (! Schema::hasColumn('committees', 'allowed_record_types')) {
                $table->json('allowed_record_types')->nullable()->after('description');
            }
        });

        Schema::table('committees_records', function (Blueprint $table) {
            if (! Schema::hasColumn('committees_records', 'category')) {
                $table->string('category')->nullable()->after('record_type');
            }

            if (! Schema::hasColumn('committees_records', 'record_date')) {
                $table->date('record_date')->nullable()->after('description');
            }

            if (! Schema::hasColumn('committees_records', 'quantity')) {
                $table->integer('quantity')->nullable()->after('record_date');
            }

            if (! Schema::hasColumn('committees_records', 'amount')) {
                $table->decimal('amount', 12, 2)->nullable()->after('quantity');
            }

            if (! Schema::hasColumn('committees_records', 'partner_name')) {
                $table->string('partner_name')->nullable()->after('amount');
            }

            if (! Schema::hasColumn('committees_records', 'status')) {
                $table->string('status')->nullable()->after('partner_name');
            }

            if (! Schema::hasColumn('committees_records', 'metadata')) {
                $table->json('metadata')->nullable()->after('status');
            }

            if (! Schema::hasColumn('committees_records', 'uploaded_by_id')) {
                $table->foreignUuid('uploaded_by_id')->nullable()->after('file_path')->constrained('users')->nullOnDelete();
            }
        });

        if (DB::getDriverName() === 'mysql') {
            $types = collect(self::RECORD_TYPES)
                ->map(fn (string $type) => "'{$type}'")
                ->implode(',');

            DB::statement("ALTER TABLE committees_records MODIFY record_type ENUM({$types}) NOT NULL");
        }
    }

    public function down(): void
    {
        Schema::table('committees_records', function (Blueprint $table) {
            if (Schema::hasColumn('committees_records', 'uploaded_by_id')) {
                $table->dropConstrainedForeignId('uploaded_by_id');
            }

            foreach (['metadata', 'status', 'partner_name', 'amount', 'quantity', 'record_date', 'category'] as $column) {
                if (Schema::hasColumn('committees_records', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('committees', function (Blueprint $table) {
            foreach (['allowed_record_types', 'chair_label', 'slug'] as $column) {
                if (Schema::hasColumn('committees', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
