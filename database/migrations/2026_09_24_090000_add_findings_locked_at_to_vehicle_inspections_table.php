<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Marks a Repair Order whose findings/prices are "fixed" — i.e. it was promoted
 * from a Repair Quotation. While set, the Findings board is read-only (server +
 * UI) so the approved amounts cannot be changed by accident.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('vehicle_inspections', 'findings_locked_at')) {
            Schema::table('vehicle_inspections', function (Blueprint $table) {
                $table->timestamp('findings_locked_at')->nullable()->after('workshop_released_at');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('vehicle_inspections', 'findings_locked_at')) {
            Schema::table('vehicle_inspections', function (Blueprint $table) {
                $table->dropColumn('findings_locked_at');
            });
        }
    }
};
