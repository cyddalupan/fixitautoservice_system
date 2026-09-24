<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Origin of a Repair Order + the date the vehicle was received.
     *
     * `source` describes how the transaction entered the shop:
     *   - walk_in   : created directly on the Repair Order page (no schedule)
     *   - scheduled : linked to an Appointment (booked / checked in) or a Job Order
     *
     * Additive only — no existing column is modified or dropped.
     */
    public function up(): void
    {
        Schema::table('vehicle_inspections', function (Blueprint $table) {
            if (!Schema::hasColumn('vehicle_inspections', 'source')) {
                $table->string('source')->nullable()->after('appointment_id')->index();
            }
            if (!Schema::hasColumn('vehicle_inspections', 'date_received')) {
                $table->date('date_received')->nullable()->after('source');
            }
        });

        // Backfill existing rows: any RO already linked to an appointment or a
        // job order came from a schedule; the rest are walk-ins.
        DB::table('vehicle_inspections')
            ->whereNull('source')
            ->where(function ($q) {
                $q->whereNotNull('appointment_id')->orWhereNotNull('job_order_id');
            })
            ->update(['source' => 'scheduled']);

        DB::table('vehicle_inspections')
            ->whereNull('source')
            ->update(['source' => 'walk_in']);

        // Recover the received date from the linked appointment where available
        // (portable correlated subquery — works on MySQL and sqlite).
        DB::statement("
            UPDATE vehicle_inspections
            SET date_received = (
                SELECT a.date_received FROM appointments a
                WHERE a.id = vehicle_inspections.appointment_id
            )
            WHERE date_received IS NULL
              AND appointment_id IS NOT NULL
              AND EXISTS (
                SELECT 1 FROM appointments a
                WHERE a.id = vehicle_inspections.appointment_id
                  AND a.date_received IS NOT NULL
              )
        ");
    }

    public function down(): void
    {
        Schema::table('vehicle_inspections', function (Blueprint $table) {
            if (Schema::hasColumn('vehicle_inspections', 'date_received')) {
                $table->dropColumn('date_received');
            }
            if (Schema::hasColumn('vehicle_inspections', 'source')) {
                $table->dropColumn('source');
            }
        });
    }
};
