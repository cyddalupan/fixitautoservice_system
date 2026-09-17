<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Arrived tab — capture the "Date Received" of the vehicle.
 *
 * The Arrived tab is where staff talk to the customer and complete all the
 * details (additional repairs, parts, client info). Once everything is
 * gathered the appointment is started and becomes a Repair Order, so we need
 * to record the date the vehicle was actually received.
 *
 * Adds one nullable column to `appointments`:
 *  - date_received : the date the vehicle was received/arrived (defaults to
 *                    the check-in date when left blank)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            if (!Schema::hasColumn('appointments', 'date_received')) {
                $table->date('date_received')->nullable()->after('checked_in_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            if (Schema::hasColumn('appointments', 'date_received')) {
                $table->dropColumn('date_received');
            }
        });
    }
};
