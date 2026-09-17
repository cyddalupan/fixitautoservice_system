<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Schema;

/**
 * Blueprint source of truth (Fixit Remake Blueprint card):
 *   appointments: id, customer_id (nullable for guest), vehicle_id (nullable),
 *   name, email, phone, service_request, appointment_date, appointment_time,
 *   status (scheduled/proceeded/rescheduled/cancelled), booking_source
 *   (website/admin), cancelled_at (nullable timestamp)
 *
 * This test drives the guest-booking schema alignment. Guests book with
 * name/email/phone and NO login, so customer_id and vehicle_id MUST be
 * nullable. booking_source must accept 'website'/'admin'. cancelled_at must
 * be a nullable timestamp.
 */
class BlueprintAppointmentsSchemaTest extends TestCase
{
    use \Illuminate\Foundation\Testing\RefreshDatabase;

    public function test_appointments_table_exists(): void
    {
        $this->assertTrue(Schema::hasTable('appointments'));
    }

    /**
     * Guest booking: appointment can be created WITHOUT a customer record.
     * customer_id MUST be nullable (blueprint: "Client (guest, no login)").
     */
    public function test_customer_id_is_nullable_for_guest_booking(): void
    {
        $cols = Schema::getColumns('appointments');
        $customerId = collect($cols)->firstWhere('name', 'customer_id');

        $this->assertNotNull($customerId, 'customer_id column must exist');
        $this->assertTrue(
            $customerId['nullable'] === true || $customerId['nullable'] === 1,
            'customer_id MUST be nullable for guest booking (no login guest can book without a Customer record).'
        );
    }

    /**
     * Guest booking: vehicle is optional at booking time.
     * vehicle_id MUST be nullable.
     */
    public function test_vehicle_id_is_nullable(): void
    {
        $cols = Schema::getColumns('appointments');
        $vehicleId = collect($cols)->firstWhere('name', 'vehicle_id');

        $this->assertNotNull($vehicleId, 'vehicle_id column must exist');
        $this->assertTrue(
            $vehicleId['nullable'] === true || $vehicleId['nullable'] === 1,
            'vehicle_id MUST be nullable (vehicle captured on booking form, may be edited later).'
        );
    }

    /**
     * Guest fields from the booking form must be persistable.
     * Blueprint: name, email, phone, service_request all on appointments.
     */
    public function test_guest_contact_and_service_columns_exist(): void
    {
        $names = array_column(Schema::getColumns('appointments'), 'name');

        foreach (['name', 'email', 'phone', 'service_request', 'appointment_date', 'appointment_time'] as $col) {
            $this->assertContains($col, $names, "Missing guest booking column: {$col}");
        }
    }

    /**
     * booking_source must exist and be nullable (accepts website/admin and
     * defaults to null for legacy rows).
     */
    public function test_booking_source_column_exists(): void
    {
        $cols = Schema::getColumns('appointments');
        $source = collect($cols)->firstWhere('name', 'booking_source');

        $this->assertNotNull($source, 'booking_source column must exist (blueprint enum website/admin)');
    }

    /**
     * cancelled_at must be a nullable timestamp so cancelled appointments can
     * be tracked in Cancel History.
     */
    public function test_cancelled_at_is_nullable_timestamp(): void
    {
        $cols = Schema::getColumns('appointments');
        $cancelledAt = collect($cols)->firstWhere('name', 'cancelled_at');

        $this->assertNotNull($cancelledAt, 'cancelled_at column must exist');
        $this->assertTrue(
            $cancelledAt['nullable'] === true || $cancelledAt['nullable'] === 1,
            'cancelled_at MUST be nullable (only set when an appointment is cancelled).'
        );
    }

    /**
     * Guest booking flow smoke test: a guest appointment can be stored with
     * customer_id NULL without violating FK constraints (no Customer created).
     */
    public function test_guest_appointment_can_be_created_without_customer(): void
    {
        $appt = \App\Models\Appointment::create([
            'customer_id'          => null,
            'vehicle_id'           => null,
            'appointment_number'   => 'APT-GUEST-TEST',
            'appointment_date'     => '2026-08-20',
            'appointment_time'     => '10:00:00',
            'appointment_type'     => 'regular_service',
            'appointment_status'   => 'scheduled',
            'service_request'      => 'Check engine light on',
            'booking_source'       => 'website',
            'name'                 => 'Guest Buyer',
            'email'                => 'guest@example.com',
            'phone'                => '09170000001',
        ]);

        $this->assertDatabaseHas('appointments', [
            'id'               => $appt->id,
            'customer_id'      => null,
            'booking_source'   => 'website',
            'appointment_status' => 'scheduled',
        ]);
    }
}
