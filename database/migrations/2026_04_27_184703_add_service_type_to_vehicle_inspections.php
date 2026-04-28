<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('vehicle_inspections', 'service_type')) {
            Schema::table('vehicle_inspections', function (Blueprint $table) {
                $table->string('service_type', 50)->nullable()->after('inspection_type');
            });
        }

        // Migrate any existing inspection_type values to service_type
        DB::statement("UPDATE vehicle_inspections SET service_type = LOWER(REPLACE(inspection_type, ' ', '_')) WHERE inspection_type IS NOT NULL AND inspection_type != '' AND service_type IS NULL");
    }

    public function down(): void
    {
        Schema::table('vehicle_inspections', function (Blueprint $table) {
            $table->dropColumn('service_type');
        });
    }
};
