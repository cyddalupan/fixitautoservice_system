<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Appointment;
use Illuminate\Support\Facades\Schema;

/**
 * Blueprint source of truth (FixItAutoServices Appointments truth card + FixIt
 * Contact Us truth card, Cyd 06 Aug 2026):
 *
 *   Appointment ≠ Contact Us. They are TWO separate lead forms, both on the
 *   website, both saving to the app DB. Appointment is a booking for an on-site
 *   car checkup/assessment (appointments table); Contact Us is a general
 *   inquiry/lead capture (contact_messages/ContactMessage table). They must be
 *   DISTINCT lead types.
 *
 * This test drives the "Appointment is DISTINCT lead type from Contact Us
 * (separate table/model)" acceptance item: the appointment data model must be
 * its own table + model, separate from the Contact Us lead type, and the
 * appointments schema must expose appointment-specific fields (appointment
 * number, date/time, service request, booking source, blueprint status) that a
 * Contact Us message would never carry — proving they are not co-mingled.
 */
class AppointmentDistinctLeadTypeTest extends TestCase
{
    use \Illuminate\Foundation\Testing\RefreshDatabase;

    /**
     * The Appointment model must map to its OWN table (appointments), not to
     * the Contact Us lead table. This is the "separate table/model" contract.
     */
    public function test_appointment_model_maps_to_distinct_appointments_table(): void
    {
        $model = new Appointment();
        $this->assertEquals('appointments', $model->getTable(), 'Appointment model must use the appointments table (distinct lead type).');
    }

    /**
     * The appointments table must exist as a lead type of its own.
     */
    public function test_appointments_table_exists_as_own_lead_table(): void
    {
        $this->assertTrue(Schema::hasTable('appointments'), 'A distinct appointments table must exist for the Appointment lead type.');
    }

    /**
     * Appointment lead rows carry booking-specific fields that a Contact Us
     * message would never have (appointment number, appointment date/time,
     * booking source, blueprint status enum, service request). Presence of
     * these proves it is a booking lead type, not a generic contact message.
     */
    public function test_appointments_table_has_booking_specific_fields(): void
    {
        $names = array_column(Schema::getColumns('appointments'), 'name');

        foreach ([
            'appointment_number',
            'appointment_date',
            'appointment_time',
            'booking_source',
            'status',
            'service_request',
        ] as $col) {
            $this->assertContains($col, $names, "Appointment lead table missing booking-specific column: {$col}");
        }
    }

    /**
     * Distinct-type guard: the appointment table must NOT carry Contact Us-only
     * fields (subject/message). If it ever did, the two lead types would be
     * co-mingled, violating the "DISTINCT lead type" requirement.
     */
    public function test_appointments_table_does_not_carry_contact_us_only_fields(): void
    {
        $names = array_column(Schema::getColumns('appointments'), 'name');

        $this->assertNotContains('subject', $names, 'Appointments must not carry a Contact Us "subject" field (separate lead type).');
        $this->assertNotContains('message', $names, 'Appointments must not carry a Contact Us "message" field (separate lead type).');
    }

    /**
     * End-to-end: an appointment can be persisted as a distinct lead record
     * with booking data (number, date/time, source, blueprint status) — proving
     * the appointment lead type works independently, without any contact
     * message data.
     */
    public function test_appointment_lead_can_be_persisted_with_booking_data(): void
    {
        $appt = Appointment::create([
            'customer_id'        => null,
            'vehicle_id'         => null,
            'appointment_number' => 'APT-DISTINCT-0001',
            'appointment_date'   => '2026-08-25',
            'appointment_time'   => '09:30:00',
            'appointment_type'   => 'regular_service',
            'appointment_status' => 'scheduled',
            'service_request'    => 'Brake check + tire rotation (on-site assessment)',
            'booking_source'     => 'website',
            'name'               => 'Leah Ramos',
            'email'              => 'leah@example.com',
            'phone'              => '09171234567',
            'status'             => 'scheduled',
        ]);

        $this->assertDatabaseHas('appointments', [
            'id'                 => $appt->id,
            'appointment_number' => 'APT-DISTINCT-0001',
            'booking_source'     => 'website',
            'status'             => 'scheduled',
            'appointment_status' => 'scheduled',
            'email'              => 'leah@example.com',
            'phone'              => '09171234567',
        ]);
    }
}
