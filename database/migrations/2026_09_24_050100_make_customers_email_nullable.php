<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Walk-in customers often have no e-mail address. The column stays UNIQUE
     * (MySQL allows multiple NULLs in a unique index) but becomes nullable so a
     * customer can be created from the walk-in Repair Order intake without one.
     *
     * Only relaxes a constraint — no data is touched.
     */
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('email')->nullable()->change();
        });
    }

    public function down(): void
    {
        // Restore NOT NULL only if there are no NULL e-mails left.
        $hasNulls = \Illuminate\Support\Facades\DB::table('customers')->whereNull('email')->exists();
        if ($hasNulls) {
            return;
        }

        Schema::table('customers', function (Blueprint $table) {
            $table->string('email')->nullable(false)->change();
        });
    }
};
