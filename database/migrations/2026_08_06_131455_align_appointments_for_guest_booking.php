<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Blueprint alignment for guest booking (Fixit Remake Blueprint card).
 * Guests book with name/email/phone and NO login, so an appointment can be
 * created WITHOUT an existing Customer record:
 *   - customer_id  -> nullable (vehicle_id was already made nullable)
 *   - name/email/phone -> nullable guest contact fields captured on the form
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->unsignedBigInteger('customer_id')->nullable()->change();

            $table->string('name')->nullable()->after('customer_id');
            $table->string('email')->nullable()->after('name');
            $table->string('phone')->nullable()->after('email');
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn(['name', 'email', 'phone']);

            // Restore NOT NULL on customer_id (legacy behavior).
            $table->unsignedBigInteger('customer_id')->nullable(false)->change();
        });
    }
};
