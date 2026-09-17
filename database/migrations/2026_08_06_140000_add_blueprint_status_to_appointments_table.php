<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Blueprint status enum on appointments.
 *
 * Source of truth (Fixit Remake Blueprint card):
 *   appointments.status = enum(scheduled, proceeded, rescheduled, cancelled)
 *
 * The legacy schema only had the heavy `appointment_status` column
 * (scheduled/confirmed/checked_in/in_progress/completed/cancelled/no_show/
 * rescheduled). This migration adds the canonical blueprint `status` column
 * with a default of 'scheduled' (blueprint entry flow: booking -> scheduled).
 * Legacy `appointment_status` is left intact for the transitional refactor;
 * the blueprint `status` column is what guest-booking / client-action flows
 * persist.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            // MySQL: real enum with the four blueprint values.
            // SQLite (test): varchar; both accept the blueprint values.
            if (Schema::getConnection()->getDriverName() === 'mysql') {
                $table->enum('status', ['scheduled', 'proceeded', 'rescheduled', 'cancelled'])
                      ->default('scheduled')
                      ->after('booking_source');
            } else {
                $table->string('status', 32)->default('scheduled');
            }
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
