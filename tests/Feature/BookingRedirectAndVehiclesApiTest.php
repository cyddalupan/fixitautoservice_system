<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\BookingToken;
use App\Models\Customer;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * P2 — remaining Booking API items:
 *  1. GET /booking on app redirects to the public website (app is NOT the UI).
 *  2. GET api/booking/vehicles serves the guest's own vehicles via booking token.
 */
class BookingRedirectAndVehiclesApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_booking_redirects_to_public_website(): void
    {
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);

        $response = $this->get('/booking');

        $response->assertRedirect();
        $this->assertStringContainsString(
            'fixitautoservices.com',
            $response->headers->get('Location'),
            'App must defer to the public website as the booking UI.'
        );
    }

    public function test_guest_can_list_own_vehicles_via_raw_booking_token(): void
    {
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);

        $customer = Customer::create([
            'first_name' => 'Vehicle',
            'last_name' => 'Guest',
            'email' => 'veh-guest@example.com',
            'phone' => '09183334455',
        ]);
        $vehicle = Vehicle::create([
            'customer_id' => $customer->id,
            'vin' => 'GUEST-' . strtoupper(\Illuminate\Support\Str::random(12)),
            'make' => 'Toyota',
            'model' => 'Vios',
            'year' => 2014,
            'license_plate' => 'VEH-555',
        ]);
        $appointment = Appointment::create([
            'appointment_number' => Appointment::generateAppointmentNumber(),
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'appointment_date' => now()->addDays(2)->toDateString(),
            'appointment_time' => '11:00:00',
            'appointment_type' => 'aircon_repair',
            'appointment_status' => 'scheduled',
            'status' => 'scheduled',
            'booking_source' => 'website',
        ]);
        $token = BookingToken::generateForAppointment($appointment->id)->token;

        $response = $this->withHeaders(['X-Booking-Token' => $token])
            ->getJson('/api/booking/vehicles');

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $vehicles = collect($response->json('vehicles'));
        $this->assertTrue(
            $vehicles->contains(fn ($v) => ($v['license_plate'] ?? null) === 'VEH-555'),
            'Guest vehicle should be listed for its owner.'
        );
    }

    public function test_vehicles_endpoint_requires_auth(): void
    {
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);

        $response = $this->getJson('/api/booking/vehicles');

        $response->assertStatus(401);
    }
}
