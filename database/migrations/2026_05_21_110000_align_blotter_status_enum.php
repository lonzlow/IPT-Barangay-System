<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('blotters') || ! Schema::hasColumn('blotters', 'status')) {
            return;
        }

        if (! in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            return;
        }

        DB::statement(
            "ALTER TABLE blotters MODIFY status ENUM('pending', 'under investigation', 'resolved', 'referred', 'dismissed', 'open', 'ongoing') NOT NULL DEFAULT 'pending'"
        );

        DB::table('blotters')
            ->whereIn('status', ['open', 'Open'])
            ->update(['status' => 'pending']);

        DB::table('blotters')
            ->whereIn('status', ['ongoing', 'Ongoing'])
            ->update(['status' => 'under investigation']);

        DB::table('blotters')
            ->where('status', 'Resolved')
            ->update(['status' => 'resolved']);

        DB::table('blotters')
            ->where('status', 'Referred')
            ->update(['status' => 'referred']);

        DB::table('blotters')
            ->where('status', 'Dismissed')
            ->update(['status' => 'dismissed']);

        DB::statement(
            "ALTER TABLE blotters MODIFY status ENUM('pending', 'under investigation', 'resolved', 'referred', 'dismissed') NOT NULL DEFAULT 'pending'"
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('blotters') || ! Schema::hasColumn('blotters', 'status')) {
            return;
        }

        if (! in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            return;
        }

        DB::statement(
            "ALTER TABLE blotters MODIFY status ENUM('pending', 'under investigation', 'resolved', 'referred', 'dismissed') NOT NULL DEFAULT 'pending'"
        );
    }
};
