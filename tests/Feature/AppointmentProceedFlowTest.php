<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\BookingToken;
use App\Models\Customer;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Appointments card — "Client Proceed -> auto-populates Customer Management".
 *
 * When a client proceeds on their appointment (via /booking/{token}), their
 * data must auto-populate the Customer Management module (admin CRUD): the
 * customer record is completed (first/last name split), source is set to
 * appointment, the vehicle is linked/kept, and the appointment transitions to
 * the blueprint 'proceeded' status. The proceed action must be owner-scoped
 * (a token/customer that does not own the appointment is rejected).
 */
class AppointmentProceedFlowTest extends TestCase
{
    use RefreshDatabase;

    private function makeAppointment(array $statusOverrides = []): array
    {
        $customer = Customer::create([
            'first_name' => 'Liza GM Kho', // full name needing split into first/last on proceed
            'last_name' => '',
            'email' => 'gm@example.com',
            'phone' => '09181234567',
        ]);
        $vehicle = Vehicle::create([
            'customer_id' => $customer->id,
            'vin' => 'GUEST-' . strtoupper(\Illuminate\Support\Str::random(12)),
            'make' => 'Nissan',
            'model' => 'Navara',
            'year' => 2017,
            'license_plate' => 'NV-777',
        ]);
        $appointment = Appointment::create(array_merge([
            'appointment_number' => Appointment::generateAppointmentNumber(),
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'appointment_date' => now()->addDays(5)->toDateString(),
            'appointment_time' => '10:00:00',
            'appointment_type' => 'suspension_check',
            'appointment_status' => 'scheduled',
            'status' => 'scheduled',
            'booking_source' => 'website',
            'service_request' => 'Klunking on bumps',
            'customer_notes' => 'Klunking on bumps',
        ], $statusOverrides));

        return [$customer, $vehicle, $appointment];
    }

    public function test_proceed_auto_populates_customer_management_and_marks_proceeded(): void
    {
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);

        [$customer, $vehicle, $appointment] = $this->makeAppointment();

        // The token is the "client keychain" — add owner token for auth.
        BookingToken::create([
            'customer_id' => $customer->id,
            'appointment_id' => $appointment->id,
            'token' => 'proceed-token-abc',
            'expires_at' => now()->addHours(72),
        ]);

        $response = $this->withHeaders(['X-Booking-Token' => encrypt(json_encode([
            'customer_id' => $customer->id,
            'expires_at' => now()->addHours(1)->timestamp,
        ]))])->postJson("/api/booking/track/{$appointment->id}/proceed");

        $response->assertOk();
        $response->assertJson(['success' => true]);

        // 1) Appointment transitioned to the blueprint 'proceeded' status.
        $appointment->refresh();
        $this->assertSame('proceeded', $appointment->status);

        // 2) Customer Management auto-populated: identity completed + active.
        $customer->refresh();
        $this->assertTrue((bool) $customer->is_active);
        $this->assertSame('Liza GM', $customer->first_name);
        $this->assertSame('Kho', $customer->last_name);

        // 3) Vehicle stays linked to the managed customer.
        $this->assertDatabaseHas('vehicles', [
            'id' => $vehicle->id,
            'customer_id' => $customer->id,
            'make' => 'Nissan',
        ]);
    }

    public function test_proceed_requires_ownership_of_appointment(): void
    {
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);

        [$customer, $vehicle, $appointment] = $this->makeAppointment();

        // A DIFFERENT customer token must not be able to proceed another's appointment.
        $other = Customer::create([
            'first_name' => 'Other',
            'last_name' => 'User',
            'email' => 'other@example.com',
            'phone' => '09170009999',
        ]);

        $response = $this->withHeaders(['X-Booking-Token' => encrypt(json_encode([
            'customer_id' => $other->id,
            'expires_at' => now()->addHours(1)->timestamp,
        ]))])->postJson("/api/booking/track/{$appointment->id}/proceed");

        $this->assertTrue(in_array($response->status(), [401, 403, 404]),
            'Cross-customer proceed must be rejected (got ' . $response->status() . ')');

        // Appointment untouched.
        $appointment->refresh();
        $this->assertSame('scheduled', $appointment->status);
    }
}
