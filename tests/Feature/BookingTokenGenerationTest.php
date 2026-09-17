<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\BookingToken;
use App\Models\Customer;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * P2 Booking API: "Generates BookingToken on booking creation".
 *
 * A booking (appointment) must receive a BookingToken so the guest/client can
 * manage it later via /booking/{token} (proceed/reschedule/cancel) — blueprint
 * guest flow (email + phone, no account). The token must be linked to the
 * appointment (not only to a customer), and the booking API response must
 * expose the token + a track URL.
 */
class BookingTokenGenerationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Ensure the booking_tokens table supports appointment linkage in-memory.
        $this->artisan('migrate');
    }

    private function makeCustomer(): Customer
    {
        return Customer::create([
            'first_name' => 'Test',
            'last_name' => 'Customer',
            'email' => 'guest@example.com',
            'phone' => '09171234567',
        ]);
    }

    private function makeVehicle(Customer $customer): Vehicle
    {
        return Vehicle::create([
            'customer_id' => $customer->id,
            'make' => 'Toyota',
            'model' => 'Vios',
            'year' => 2020,
            'license_plate' => 'ABC-123',
        ]);
    }

    public function test_booking_tokens_table_has_appointment_id_column(): void
    {
        $this->assertTrue(
            \Illuminate\Support\Facades\Schema::hasColumn('booking_tokens', 'appointment_id'),
            'booking_tokens must have an appointment_id column for guest booking tokens.'
        );
        // customer_id remains (nullable now) for backwards compatibility.
        $this->assertTrue(\Illuminate\Support\Facades\Schema::hasColumn('booking_tokens', 'customer_id'));
    }

    public function test_booking_token_can_be_generated_for_appointment(): void
    {
        $customer = $this->makeCustomer();
        $vehicle = $this->makeVehicle($customer);

        $appointment = Appointment::create([
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'appointment_number' => 'APT-TEST-0001',
            'appointment_date' => now()->addDays(1)->toDateString(),
            'appointment_time' => '10:00:00',
            'appointment_type' => 'engine_service',
            'appointment_status' => 'customer_booked',
            'status' => 'scheduled',
            'booking_source' => 'website',
        ]);

        $token = BookingToken::generateForAppointment($appointment->id);

        $this->assertNotNull($token);
        $this->assertSame($appointment->id, $token->appointment_id);
        $this->assertNotNull($token->token);
        $this->assertTrue($token->expires_at->isFuture());

        // It must be findable by token and resolve to the appointment.
        $found = BookingToken::findValid($token->token);
        $this->assertNotNull($found);
        $this->assertSame($appointment->id, $found->appointment_id);
    }

    public function test_api_create_booking_returns_and_stores_booking_token(): void
    {
        $customer = $this->makeCustomer();
        $vehicle = $this->makeVehicle($customer);

        // Authenticate the booking API via the encrypted X-Booking-Token header
        // (same format the magic-link/login flow produces: encrypt(json_encode(...))).
        $enc = encrypt(json_encode([
            'customer_id' => $customer->id,
            'expires_at' => now()->addHours(1)->timestamp,
        ]));

        $response = $this->withHeader('X-Booking-Token', $enc)
            ->postJson('/api/booking/customer-booking', [
                'vehicle_id' => $vehicle->id,
                'service_type' => 'engine_service',
                'appointment_date' => now()->addDays(2)->toDateString(),
                'appointment_time' => '14:00',
                'notes' => 'Test booking',
            ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $tokenStr = $response->json('appointment.token');
        $this->assertNotNull($tokenStr);
        $this->assertNotEmpty($tokenStr);

        // The token must be persisted and linked to the created appointment.
        $created = \App\Models\BookingToken::where('token', $tokenStr)->first();
        $this->assertNotNull($created);
        $this->assertNotNull($created->appointment_id);
        $this->assertSame(
            $response->json('appointment.id'),
            $created->appointment_id
        );
    }
}
