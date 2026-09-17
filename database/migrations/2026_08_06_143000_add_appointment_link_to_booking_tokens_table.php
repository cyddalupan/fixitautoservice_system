<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * P2 Booking API: "Generates BookingToken on booking creation".
 *
 * Blueprint guest flow: a client books with email + phone (no account), so a
 * guest appointment has no Customer record. BookingTokens must therefore be
 * linkable to an Appointment (appointment_id), with customer_id optional.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('booking_tokens', function (Blueprint $table) {
            $table->unsignedBigInteger('customer_id')->nullable()->change();
            $table->unsignedBigInteger('appointment_id')->nullable()->after('customer_id');
            $table->index('appointment_id');
        });
    }

    public function down(): void
    {
        Schema::table('booking_tokens', function (Blueprint $table) {
            $table->dropIndex(['appointment_id']);
            $table->dropColumn('appointment_id');
            $table->unsignedBigInteger('customer_id')->nullable(false)->change();
        });
    }
};
