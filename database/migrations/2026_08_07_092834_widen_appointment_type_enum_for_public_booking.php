<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Widen appointments.appointment_type enum.
 *
 * The public website booking flow (BookingController@apiCreateBooking) writes
 * the guest's `service_type` directly into `appointment_type`, and its
 * validation is only `required|string|max:255` (no `in:` whitelist). The
 * production MySQL enum only held the legacy internal set
 * (regular_service, emergency, inspection, ...) so any modern service type
 * submitted from the public booking form (general_checkup, preventive_maintenance,
 * engine_repair, aircon_repair, tire_wheel, ...) was truncated and caused
 * "Server Error" / "Data truncated for column 'appointment_type'".
 *
 * This adds every value present in the canonical service catalog
 * (config/service-types.php) plus the modern types the form/API emit, so the
 * column accepts the full union.
 *
 * Driver note: sqlite stores enums as varchar, so this is a MySQL-only ALTER;
 * on sqlite the column already accepts arbitrary strings, so it is a no-op.
 */
class WidenAppointmentTypeEnumForPublicBooking extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return; // sqlite/other: enum is stored as varchar, no restriction
        }

        $values = [
            'regular_service', 'emergency', 'inspection', 'diagnostic', 'repair',
            'maintenance', 'tire_service', 'oil_change', 'brake_service', 'other',
            'preventive_maintenance', 'basic_tune_up', 'egr_service', 'aircon_cleaning',
            'aircon_general_cleaning', 'aircon_service', 'underchassis_service',
            'engine_service', 'auto_parts_sales', 'home_service_request',
            'body_repair_painting',
            'engine_repair', 'engine_diagnostic', 'aircon_repair', 'electrical_repair',
            'transmission_service', 'tire_wheel', 'general_checkup', 'home_service',
        ];
        $list = implode("','", $values);
        DB::statement("ALTER TABLE appointments MODIFY appointment_type ENUM('{$list}') NOT NULL DEFAULT 'regular_service'");
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }
        DB::statement("ALTER TABLE appointments MODIFY appointment_type ENUM('regular_service','emergency','inspection','diagnostic','repair','maintenance','tire_service','oil_change','brake_service','other') NOT NULL DEFAULT 'regular_service'");
    }
}
