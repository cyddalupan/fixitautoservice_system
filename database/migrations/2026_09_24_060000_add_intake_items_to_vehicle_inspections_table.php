<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Intake line items for a Repair Order, captured on the Repair Order create
     * page (which mirrors the Appointment "Update Information" form):
     *   - service_types        : the checked Services
     *   - job_description_items: Job Description rows (desc/mh/unit/labor)
     *   - parts_items          : Parts / Supplies rows (desc/qty/unit/cost)
     *   - discount             : Discount (PHP)
     *
     * Historically these live on the linked Appointment. Adding them to the
     * Repair Order makes a walk-in (no appointment) self-sufficient, while
     * appointment-linked orders keep mirroring onto the appointment for the slip.
     *
     * Additive only — no existing column is modified or dropped.
     */
    public function up(): void
    {
        Schema::table('vehicle_inspections', function (Blueprint $table) {
            if (!Schema::hasColumn('vehicle_inspections', 'service_types')) {
                $table->json('service_types')->nullable()->after('service_type');
            }
            if (!Schema::hasColumn('vehicle_inspections', 'job_description_items')) {
                $table->json('job_description_items')->nullable()->after('customer_concerns');
            }
            if (!Schema::hasColumn('vehicle_inspections', 'parts_items')) {
                $table->json('parts_items')->nullable()->after('job_description_items');
            }
            if (!Schema::hasColumn('vehicle_inspections', 'discount')) {
                $table->decimal('discount', 12, 2)->default(0)->after('parts_items');
            }
        });
    }

    public function down(): void
    {
        Schema::table('vehicle_inspections', function (Blueprint $table) {
            foreach (['discount', 'parts_items', 'job_description_items', 'service_types'] as $col) {
                if (Schema::hasColumn('vehicle_inspections', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
