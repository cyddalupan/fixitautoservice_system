<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('vehicle_inspections', function (Blueprint $table) {
            $table->integer('vehicle_mileage')->nullable()->after('inspection_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicle_inspections', function (Blueprint $table) {
            $table->dropColumn('vehicle_mileage');
        });
    }
};
