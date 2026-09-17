<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add viewed_at column to tables used by the admin sidebar.
     * Only for tables that actually exist in the current schema.
     */
    public function up(): void
    {
        $tables = ['appointments', 'vehicle_inspections', 'job_orders', 'service_records'];
        
        foreach ($tables as $table) {
            if (!Schema::hasColumn($table, 'viewed_at')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->timestamp('viewed_at')->nullable()->after('updated_at');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = ['appointments', 'vehicle_inspections', 'job_orders', 'service_records'];
        
        foreach ($tables as $table) {
            if (Schema::hasColumn($table, 'viewed_at')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->dropColumn('viewed_at');
                });
            }
        }
    }
};
