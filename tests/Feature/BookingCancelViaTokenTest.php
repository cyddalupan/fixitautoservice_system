<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Customer;
use App\Models\Vehicle;
use App\Models\Appointment;
use App\Models\BookingToken;

/**
 * Client Cancel via /booking/{token} (blueprint requirement).
 * Red -> green TDD for the FixitRemake blueprint.
 */
class BookingCancelViaTokenTest extends TestCase
{
    private function makeAppointment(array $statusOverrides = []): array
    {
        $customer = Customer::create([
            'first_name' => 'Cancella',
            'last_name' => 'Client',
            'email' => 'cancel@example.com',
            'phone' => '09181234599',
        ]);
        $vehicle = Vehicle::create([
            'customer_id' => $customer->id,
            'vin' => 'GUEST-' . strtoupper(\Illuminate\Support\Str::random(12)),
            'make' => 'Toyota',
            'model' => 'Hilux',
            'year' => 2019,
            'license_plate' => 'TLX-999',
        ]);
        $appointment = Appointment::create(array_merge([
            'appointment_number' => Appointment::generateAppointmentNumber(),
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'appointment_date' => now()->addDays(5)->toDateString(),
            'appointment_time' => '10:00:00',
            'appointment_type' => 'general_pms',
            'appointment_status' => 'scheduled',
            'status' => 'scheduled',
            'booking_source' => 'website',
            'service_request' => 'Routine maintenance',
            'customer_notes' => 'Routine maintenance',
        ], $statusOverrides));

        return [$customer, $vehicle, $appointment];
    }

    /** @test */
    public function owner_token_can_cancel_appointment_via_booking(): void
    {
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);

        [$customer, $vehicle, $appointment] = $this->makeAppointment();

        BookingToken::create([
            'customer_id' => $customer->id,
            'appointment_id' => $appointment->id,
            'token' => 'cancel-token-abc',
            'expires_at' => now()->addHours(72),
        ]);

        $response = $this->withHeaders(['X-Booking-Token' => encrypt(json_encode([
            'customer_id' => $customer->id,
            'expires_at' => now()->addHours(1)->timestamp,
        ]))])->postJson("/api/booking/track/{$appointment->id}/cancel");

        $response->assertOk();
        $response->assertJson(['success' => true]);

        // Appointment transitioned to the blueprint 'cancelled' status + timestamp.
        $appointment->refresh();
        $this->assertSame('cancelled', $appointment->appointment_status);
        $this->assertNotNull($appointment->cancelled_at);
    }

    /** @test */
    public function non_owner_token_cannot_cancel_appointment(): void
    {
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);

        [$customer, $vehicle, $appointment] = $this->makeAppointment();

        $other = Customer::create([
            'first_name' => 'Other',
            'last_name' => 'Party',
            'email' => 'other2@example.com',
            'phone' => '09170008888',
        ]);

        $response = $this->withHeaders(['X-Booking-Token' => encrypt(json_encode([
            'customer_id' => $other->id,
            'expires_at' => now()->addHours(1)->timestamp,
        ]))])->postJson("/api/booking/track/{$appointment->id}/cancel");

        $this->assertTrue(in_array($response->status(), [401, 403, 404]),
            'Cross-customer cancel must be rejected (got ' . $response->status() . ')');

        $appointment->refresh();
        $this->assertSame('scheduled', $appointment->appointment_status);
    }

    /** @test */
    public function proceeded_appointment_cannot_be_cancelled(): void
    {
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);

        [$customer, $vehicle, $appointment] = $this->makeAppointment();

        // Mark proceeded first (i.e. already worked / in-progress).
        $appointment->update(['appointment_status' => 'proceeded', 'status' => 'proceeded']);

        BookingToken::create([
            'customer_id' => $customer->id,
            'appointment_id' => $appointment->id,
            'token' => 'cancel-token-def',
            'expires_at' => now()->addHours(72),
        ]);

        $response = $this->withHeaders(['X-Booking-Token' => encrypt(json_encode([
            'customer_id' => $customer->id,
            'expires_at' => now()->addHours(1)->timestamp,
        ]))])->postJson("/api/booking/track/{$appointment->id}/cancel");

        // A proceeded appointment is not cancellable at that stage.
        $this->assertEquals(422, $response->status());

        $appointment->refresh();
        $this->assertSame('proceeded', $appointment->appointment_status);
    }
}
