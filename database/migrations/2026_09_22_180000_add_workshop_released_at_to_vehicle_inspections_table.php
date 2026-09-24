<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicle_inspections', function (Blueprint $table) {
            if (!Schema::hasColumn('vehicle_inspections', 'workshop_released_at')) {
                $table->timestamp('workshop_released_at')->nullable()->after('repair_status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('vehicle_inspections', function (Blueprint $table) {
            if (Schema::hasColumn('vehicle_inspections', 'workshop_released_at')) {
                $table->dropColumn('workshop_released_at');
            }
        });
    }
};
