<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\BookingToken;
use App\Models\Customer;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * P2 track API — reschedule via booking token (blueprint).
 *
 * The guest should be able to change the appointment date/time through the
 * booking API using their booking token, transitioning the appointment status
 * to 'rescheduled' (blueprint enum). Currently reschedule is web-flow only and
 * there is NO api/booking/track/{appointment}/reschedule endpoint.
 *
 * RED -> GREEN.
 */
class RescheduleViaBookingApiTest extends TestCase
{
    use RefreshDatabase;

    private function makeGuestAppointment(): array
    {
        $customer = Customer::create([
            'first_name' => 'Resched',
            'last_name' => 'Guest',
            'email' => 'resched-guest@example.com',
            'phone' => '09182223344',
        ]);
        $vehicle = Vehicle::create([
            'customer_id' => $customer->id,
            'vin' => 'GUEST-' . strtoupper(\Illuminate\Support\Str::random(12)),
            'make' => 'Mitsubishi',
            'model' => 'L300',
            'year' => 2016,
            'license_plate' => 'RSD-999',
        ]);
        $appointment = Appointment::create([
            'appointment_number' => Appointment::generateAppointmentNumber(),
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'appointment_date' => now()->addDays(3)->toDateString(),
            'appointment_time' => '10:00:00',
            'appointment_type' => 'general_pms',
            'appointment_status' => 'scheduled',
            'status' => 'scheduled',
            'booking_source' => 'website',
            'service_request' => 'PMS',
            'notes' => 'PMS',
        ]);

        $token = BookingToken::generateForAppointment($appointment->id)->token;

        return [$customer, $vehicle, $appointment, $token];
    }

    public function test_guest_can_reschedule_via_raw_booking_token(): void
    {
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);

        [$customer, $vehicle, $appointment, $token] = $this->makeGuestAppointment();

        $newDate = now()->addDays(6)->toDateString();

        $response = $this->withHeaders(['X-Booking-Token' => $token])
            ->postJson("/api/booking/track/{$appointment->id}/reschedule", [
                'appointment_date' => $newDate,
                'appointment_time' => '15:30',
            ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $appointment->refresh();
        $this->assertSame($newDate, $appointment->appointment_date->toDateString());
        $this->assertSame('15:30:00', $appointment->appointment_time);
        $this->assertSame('rescheduled', $appointment->appointment_status);
        $this->assertSame('rescheduled', $appointment->status);
    }

    public function test_reschedule_requires_valid_date_and_time(): void
    {
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);

        [$customer, $vehicle, $appointment, $token] = $this->makeGuestAppointment();

        $response = $this->withHeaders(['X-Booking-Token' => $token])
            ->postJson("/api/booking/track/{$appointment->id}/reschedule", [
                'appointment_date' => '', // invalid
                'appointment_time' => '25:99', // invalid
            ]);

        $response->assertStatus(422);
    }

    public function test_non_owner_token_cannot_reschedule(): void
    {
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);

        [$customer, $vehicle, $appointment] = $this->makeGuestAppointment();

        $other = Customer::create([
            'first_name' => 'Other',
            'last_name' => 'Resched',
            'email' => 'other-resched@example.com',
            'phone' => '09170002222',
        ]);
        $otherAppt = Appointment::create([
            'appointment_number' => Appointment::generateAppointmentNumber(),
            'customer_id' => $other->id,
            'vehicle_id' => $vehicle->id,
            'appointment_date' => now()->addDays(4)->toDateString(),
            'appointment_time' => '08:00:00',
            'appointment_type' => 'diagnostics',
            'appointment_status' => 'scheduled',
            'status' => 'scheduled',
            'booking_source' => 'website',
        ]);
        $otherToken = BookingToken::generateForAppointment($otherAppt->id)->token;

        $response = $this->withHeaders(['X-Booking-Token' => $otherToken])
            ->postJson("/api/booking/track/{$appointment->id}/reschedule", [
                'appointment_date' => now()->addDays(6)->toDateString(),
                'appointment_time' => '16:00',
            ]);

        $this->assertTrue(in_array($response->status(), [401, 403, 404]),
            'Cross-customer reschedule must be rejected (got ' . $response->status() . ')');

        $appointment->refresh();
        $this->assertSame('scheduled', $appointment->appointment_status);
    }
}
