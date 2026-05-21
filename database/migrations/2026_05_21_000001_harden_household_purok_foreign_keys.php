<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('residents', function (Blueprint $table) {
            $table->dropForeign(['household_id']);
            $table->foreign('household_id')->references('id')->on('households')->restrictOnDelete();
        });

        Schema::table('households', function (Blueprint $table) {
            $table->dropForeign(['purok_id']);
            $table->foreign('purok_id')->references('id')->on('puroks')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('residents', function (Blueprint $table) {
            $table->dropForeign(['household_id']);
            $table->foreign('household_id')->references('id')->on('households')->cascadeOnDelete();
        });

        Schema::table('households', function (Blueprint $table) {
            $table->dropForeign(['purok_id']);
            $table->foreign('purok_id')->references('id')->on('puroks')->cascadeOnDelete();
        });
    }
};
