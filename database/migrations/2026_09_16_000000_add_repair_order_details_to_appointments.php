<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Repair Order slip — flexible line items for the Update Information page.
 *
 * Adds three nullable columns to `appointments`:
 *  - job_description_items : JSON array of {description, mh, unit_price, labor_cost}
 *  - parts_items           : JSON array of {description, qty, unit_price, cost}
 *  - discount              : manual discount amount shown on the printed slip
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            if (!Schema::hasColumn('appointments', 'job_description_items')) {
                $table->json('job_description_items')->nullable()->after('service_types');
            }
            if (!Schema::hasColumn('appointments', 'parts_items')) {
                $table->json('parts_items')->nullable()->after('job_description_items');
            }
            if (!Schema::hasColumn('appointments', 'discount')) {
                $table->decimal('discount', 12, 2)->nullable()->after('parts_items');
            }
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            foreach (['job_description_items', 'parts_items', 'discount'] as $col) {
                if (Schema::hasColumn('appointments', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
