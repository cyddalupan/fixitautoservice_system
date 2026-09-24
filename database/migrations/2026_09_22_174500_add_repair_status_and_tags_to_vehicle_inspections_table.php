<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicle_inspections', function (Blueprint $table) {
            if (!Schema::hasColumn('vehicle_inspections', 'repair_status')) {
                $table->string('repair_status')->default('received')->after('inspection_status')->index();
            }
            if (!Schema::hasColumn('vehicle_inspections', 'repair_tags')) {
                $table->json('repair_tags')->nullable()->after('repair_status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('vehicle_inspections', function (Blueprint $table) {
            if (Schema::hasColumn('vehicle_inspections', 'repair_status')) {
                $table->dropColumn('repair_status');
            }
            if (Schema::hasColumn('vehicle_inspections', 'repair_tags')) {
                $table->dropColumn('repair_tags');
            }
        });
    }
};
