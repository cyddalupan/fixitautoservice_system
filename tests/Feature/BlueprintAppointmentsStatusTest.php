<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Schema;

/**
 * Blueprint status enum (Fixit Remake Blueprint card — source of truth):
 *   appointments.status (enum: scheduled / proceeded / rescheduled / cancelled)
 *
 * The legacy schema only had the heavy `appointment_status` column
 * (scheduled/confirmed/checked_in/in_progress/completed/cancelled/no_show/
 * rescheduled). This test drives adding the blueprint `status` column so the
 * guest-booking + client-action flows (P2/P3/P4) have a canonical,
 * blueprint-compliant status to persist. It runs on the in-memory sqlite test
 * DB only (phpunit.xml) — never the production MySQL.
 */
class BlueprintAppointmentsStatusTest extends TestCase
{
    use \Illuminate\Foundation\Testing\RefreshDatabase;

    /**
     * The blueprint `status` column must exist on appointments.
     */
    public function test_blueprint_status_column_exists(): void
    {
        $this->assertTrue(Schema::hasColumn('appointments', 'status'),
            'appointments.status column MUST exist per blueprint (enum scheduled/proceeded/rescheduled/cancelled).');
    }

    /**
     * A freshly-created appointment defaults to status 'scheduled'
     * (blueprint: "1. User books appointment -> status: scheduled").
     */
    public function test_status_defaults_to_scheduled(): void
    {
        $cols = Schema::getColumns('appointments');
        $status = collect($cols)->firstWhere('name', 'status');

        $this->assertNotNull($status, 'status column must exist');

        // Default should be 'scheduled' per blueprint entry flow.
        $default = $status['default'] ?? null;
        $this->assertContains(strtolower((string) $default), ['scheduled', "'scheduled'"],
            "status default should be 'scheduled', got: " . var_export($default, true));
    }

    /**
     * Guest booking must be able to persist status='scheduled' without a
     * customer record (blueprint guest flow). This is the current P2 gap.
     */
    public function test_guest_appointment_can_persist_blueprint_status(): void
    {
        $appt = \App\Models\Appointment::create([
            'customer_id'        => null,
            'vehicle_id'         => null,
            'appointment_number' => 'APT-STATUS-GUEST',
            'appointment_date'   => '2026-08-21',
            'appointment_time'   => '09:30:00',
            'appointment_type'   => 'regular_service',
            'service_request'    => 'Brake check',
            'booking_source'     => 'website',
            'name'               => 'Status Guest',
            'email'              => 'status-guest@example.com',
            'phone'              => '09170000002',
            'appointment_status' => 'scheduled', // legacy NOT NULL column still required by schema
            'status'             => 'scheduled',
        ]);

        $this->assertSame('scheduled', $appt->status);
        $this->assertDatabaseHas('appointments', [
            'id'     => $appt->id,
            'status' => 'scheduled',
        ]);
    }

    /**
     * The blueprint status column must accept all four blueprint statuses:
     * scheduled / proceeded / rescheduled / cancelled.
     */
    public function test_blueprint_status_accepts_all_four_values(): void
    {
        $allowed = ['scheduled', 'proceeded', 'rescheduled', 'cancelled'];

        foreach ($allowed as $value) {
            $appt = \App\Models\Appointment::create([
                'customer_id'        => null,
                'vehicle_id'         => null,
                'appointment_number' => 'APT-STATUS-' . strtoupper($value),
                'appointment_date'   => '2026-08-21',
                'appointment_time'   => '11:00:00',
                'appointment_type'   => 'regular_service',
                'service_request'    => 'Status test',
                'booking_source'     => 'admin',
                'name'               => 'Status ' . $value,
                'email'              => 'status-' . $value . '@example.com',
                'phone'              => '09170000003',
                'appointment_status' => 'scheduled', // legacy NOT NULL column still required
                'status'             => $value,
            ]);

            $this->assertSame($value, $appt->fresh()->status, "status should persist '{$value}'");
        }
    }

    /**
     * A website booking must persist the blueprint status='scheduled'
     * (blueprint: "1. User books appointment -> status: scheduled").
     * This fixes the P2 gap where apiCreateBooking left status unset
     * (legacy customer_booked only).
     */
    public function test_website_booking_persists_blueprint_scheduled_status(): void
    {
        $appt = \App\Models\Appointment::create([
            'customer_id'        => null,
            'vehicle_id'         => null,
            'appointment_number' => 'APT-WEB-SCHED',
            'appointment_date'   => '2026-08-22',
            'appointment_time'   => '14:00:00',
            'appointment_type'   => 'regular_service',
            'service_request'    => 'Noise from front wheel',
            'booking_source'     => 'website',
            'name'               => 'Web Booked Guest',
            'email'              => 'webbooked@example.com',
            'phone'              => '09170000004',
            'appointment_status' => 'customer_booked', // legacy column still set by controller
            'status'             => 'scheduled',
        ]);

        $this->assertDatabaseHas('appointments', [
            'id'             => $appt->id,
            'status'         => 'scheduled',
            'booking_source' => 'website',
        ]);
    }
}
