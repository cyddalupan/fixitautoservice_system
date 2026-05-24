<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations — add a unique index on license_plate,
     * ensuring no two active (non-soft-deleted) vehicles share the same plate.
     *
     * MySQL allows multiple NULL values in a unique index, so
     * vehicles without a license plate recorded are unaffected.
     */
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->unique('license_plate', 'vehicles_license_plate_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropUnique('vehicles_license_plate_unique');
        });
    }
};
