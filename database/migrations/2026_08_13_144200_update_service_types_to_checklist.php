<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Update service_types to EXACTLY the 8 checklist services agreed with the
 * client (2026-08-13, "Service Information" checklist on the Fixit Adjustment
 * - Create Appointment card):
 *
 *   PREVENTIVE MAINTENANCE, BASIC TUNE UP, EGR SERVICE, AIRCON CLEANING,
 *   AIRCON GENERAL CLEANING, AIRCON SERVICE, UNDERCHASSIS SERVICE,
 *   ENGINE SERVICE
 *
 * Anything not in that list must be removed (old categories like
 * AUTO-MECHANICAL, AUTO-ELECTRICAL, AUTO-ELECTRONICS, AUTO AIR-CONDITIONING,
 * BODY REPAIR AND PAINTING, AUTO PARTS SALES, HOME SERVICE REQUEST).
 */
class UpdateServiceTypesToChecklist extends Migration
{
    public function up(): void
    {
        // Replace every row so the table matches the canonical 8-item list.
        // Use delete() rather than truncate(): service_types is referenced by
        // FK constraints (e.g. service_pricings.service_type_id) and MySQL
        // rejects TRUNCATE on FK-referenced tables.
        DB::table('service_types')->delete();

        $types = [
            ['key' => 'preventive_maintenance',    'name' => 'PREVENTIVE MAINTENANCE',    'icon' => '🔧', 'sort_order' => 1],
            ['key' => 'basic_tune_up',             'name' => 'BASIC TUNE UP',             'icon' => '⚙️', 'sort_order' => 2],
            ['key' => 'egr_service',               'name' => 'EGR SERVICE',               'icon' => '🔄', 'sort_order' => 3],
            ['key' => 'aircon_cleaning',           'name' => 'AIRCON CLEANING',           'icon' => '❄️', 'sort_order' => 4],
            ['key' => 'aircon_general_cleaning',   'name' => 'AIRCON GENERAL CLEANING',   'icon' => '❄️', 'sort_order' => 5],
            ['key' => 'aircon_service',            'name' => 'AIRCON SERVICE',            'icon' => '❄️', 'sort_order' => 6],
            ['key' => 'underchassis_service',      'name' => 'UNDERCHASSIS SERVICE',      'icon' => '🔩', 'sort_order' => 7],
            ['key' => 'engine_service',            'name' => 'ENGINE SERVICE',            'icon' => '🔧', 'sort_order' => 8],
        ];

        foreach ($types as $type) {
            DB::table('service_types')->insert($type);
        }
    }

    public function down(): void
    {
        // Restore the previous catalog (best-effort).
        DB::table('service_types')->delete();

        $oldTypes = [
            ['key' => 'preventive_maintenance',    'name' => 'PREVENTIVE MAINTENANCE',    'icon' => '🔧', 'sort_order' => 1],
            ['key' => 'auto_mechanical',           'name' => 'AUTO-MECHANICAL',           'icon' => '⚙️', 'sort_order' => 2],
            ['key' => 'auto_electrical',            'name' => 'AUTO-ELECTRICAL',          'icon' => '⚡', 'sort_order' => 3],
            ['key' => 'auto_electronics',           'name' => 'AUTO-ELECTRONICS',         'icon' => '🔌', 'sort_order' => 4],
            ['key' => 'auto_air_conditioning',     'name' => 'AUTO AIR-CONDITIONING',     'icon' => '❄️', 'sort_order' => 5],
            ['key' => 'body_repair_painting',       'name' => 'BODY REPAIR AND PAINTING', 'icon' => '🎨', 'sort_order' => 6],
            ['key' => 'auto_parts_sales',           'name' => 'AUTO PARTS SALES',         'icon' => '🔩', 'sort_order' => 7],
            ['key' => 'home_service_request',       'name' => 'HOME SERVICE REQUEST',     'icon' => '🏠', 'sort_order' => 8],
        ];

        foreach ($oldTypes as $type) {
            DB::table('service_types')->insert($type);
        }
    }
}
