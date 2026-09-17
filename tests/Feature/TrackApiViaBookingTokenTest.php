<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\BookingToken;
use App\Models\Customer;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * P2 track API — guest flow via booking *token* (blueprint magic-link flow).
 *
 * After a guest books via apiCreateBooking, they receive a raw
 * `booking_tokens.token` (64-char DB token) plus a track_url like
 * /booking/{token}. The track API endpoints (GET track, GET messages,
 * POST cancel) must accept that SAME raw DB booking token to authenticate
 * the guest, resolve their customer, and remain owner-scoped.
 *
 * RED -> GREEN: currently resolveCustomerId() only understands an ENCRYPTED
 * X-Booking-Token payload (or auth/session); a raw DB booking token in the
 * header makes decrypt() throw -> returns null -> 401. This test drives the
 * change to also resolve a raw `booking_tokens.token` via BookingToken::findValid().
 */
class TrackApiViaBookingTokenTest extends TestCase
{
    use RefreshDatabase;

    private function makeGuestAppointment(): array
    {
        $customer = Customer::create([
            'first_name' => 'Guest',
            'last_name' => 'Tracker',
            'email' => 'track-guest@example.com',
            'phone' => '09189998877',
        ]);
        $vehicle = Vehicle::create([
            'customer_id' => $customer->id,
            'vin' => 'GUEST-' . strtoupper(\Illuminate\Support\Str::random(12)),
            'make' => 'Suzuki',
            'model' => 'Swift',
            'year' => 2015,
            'license_plate' => 'TRK-123',
        ]);
        $appointment = Appointment::create([
            'appointment_number' => Appointment::generateAppointmentNumber(),
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'appointment_date' => now()->addDays(3)->toDateString(),
            'appointment_time' => '14:00:00',
            'appointment_type' => 'aircon_repair',
            'appointment_status' => 'scheduled',
            'status' => 'scheduled',
            'booking_source' => 'website',
            'service_request' => 'AC check',
            'notes' => 'AC check',
        ]);

        $token = BookingToken::generateForAppointment($appointment->id)->token;

        return [$customer, $vehicle, $appointment, $token];
    }

    public function test_guest_can_track_appointment_via_raw_booking_token(): void
    {
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);

        [$customer, $vehicle, $appointment, $token] = $this->makeGuestAppointment();

        $response = $this->withHeaders(['X-Booking-Token' => $token])
            ->getJson("/api/booking/track/{$appointment->id}");

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $json = $response->json();
        $this->assertSame($appointment->appointment_number, $json['appointment']['id'] ?? $json['data']['id'] ?? null);
        $this->assertSame('scheduled', $json['appointment']['status'] ?? $json['data']['status'] ?? null);
    }

    public function test_guest_can_read_messages_via_raw_booking_token(): void
    {
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);

        [$customer, $vehicle, $appointment, $token] = $this->makeGuestAppointment();

        $response = $this->withHeaders(['X-Booking-Token' => $token])
            ->getJson("/api/booking/track/{$appointment->id}/messages");

        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_guest_can_cancel_via_raw_booking_token(): void
    {
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);

        [$customer, $vehicle, $appointment, $token] = $this->makeGuestAppointment();

        $response = $this->withHeaders(['X-Booking-Token' => $token])
            ->postJson("/api/booking/track/{$appointment->id}/cancel");

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $appointment->refresh();
        $this->assertSame('cancelled', $appointment->appointment_status);
        $this->assertNotNull($appointment->cancelled_at);
    }

    public function test_raw_booking_token_for_different_customer_cannot_track(): void
    {
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);

        [$customer, $vehicle, $appointment] = $this->makeGuestAppointment();

        $other = Customer::create([
            'first_name' => 'Other',
            'last_name' => 'Token',
            'email' => 'other-token@example.com',
            'phone' => '09170001111',
        ]);

        // A token belonging to a DIFFERENT customer, targeted at this appointment
        // (should not allow cross-customer access; booking_tokens.appointment_id
        // and the owner scan both guard it).
        $otherAppt = Appointment::create([
            'appointment_number' => Appointment::generateAppointmentNumber(),
            'customer_id' => $other->id,
            'vehicle_id' => $vehicle->id,
            'appointment_date' => now()->addDays(5)->toDateString(),
            'appointment_time' => '09:00:00',
            'appointment_type' => 'diagnostics',
            'appointment_status' => 'scheduled',
            'status' => 'scheduled',
            'booking_source' => 'website',
        ]);
        $otherToken = BookingToken::generateForAppointment($otherAppt->id)->token;

        $response = $this->withHeaders(['X-Booking-Token' => $otherToken])
            ->getJson("/api/booking/track/{$appointment->id}");

        $this->assertTrue(in_array($response->status(), [401, 403, 404]),
            'Cross-customer raw token must be rejected (got ' . $response->status() . ')');
    }

    public function test_invalid_raw_booking_token_returns_unauthorized(): void
    {
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);

        [$customer, $vehicle, $appointment] = $this->makeGuestAppointment();

        $response = $this->withHeaders(['X-Booking-Token' => 'definitely-not-a-real-token'])
            ->getJson("/api/booking/track/{$appointment->id}");

        $response->assertStatus(401);
    }
}
