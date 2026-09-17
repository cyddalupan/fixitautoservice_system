<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Appointment;
use App\Models\BookingSetting;

/**
 * Blueprint P2: "Cap bookings to avoid overbooking (booking limit /
 * capacity check)".
 *
 * A per-slot booking capacity (max_bookings_per_slot, default 1) must be
 * honored by GET api/booking/available-slots: a slot is only unavailable
 * once the number of active bookings in it reaches capacity.
 */
class BookingOverbookingCapacityTest extends TestCase
{
    /** @test */
    public function slot_is_unavailable_at_default_capacity_of_one()
    {
        // Default max_bookings_per_slot = 1. One existing booking fills the slot.
        $date = now()->addDays(1)->format('Y-m-d');
        Appointment::factory()->create([
            'appointment_date' => $date,
            'appointment_time' => '09:00',
            'appointment_status' => 'scheduled',
        ]);

        $response = $this->getJson('/api/booking/available-slots?date=' . $date)
            ->assertOk();

        $slot = collect($response->json('slots'))->firstWhere('time', '09:00');
        $this->assertNotNull($slot, 'slot 09:00 should exist');
        $this->assertFalse($slot['available'], 'slot should be unavailable at capacity 1');
    }

    /** @test */
    public function slot_stays_available_below_raised_capacity()
    {
        BookingSetting::setValue('max_bookings_per_slot', 2);

        $date = now()->addDays(1)->format('Y-m-d');
        Appointment::factory()->create([
            'appointment_date' => $date,
            'appointment_time' => '09:00',
            'appointment_status' => 'scheduled',
        ]);

        $response = $this->getJson('/api/booking/available-slots?date=' . $date)
            ->assertOk();

        $slot = collect($response->json('slots'))->firstWhere('time', '09:00');
        $this->assertNotNull($slot, 'slot 09:00 should exist');
        $this->assertTrue($slot['available'], 'slot should remain available below capacity of 2');
    }

    /** @test */
    public function slot_becomes_unavailable_when_reaching_raised_capacity()
    {
        BookingSetting::setValue('max_bookings_per_slot', 2);

        $date = now()->addDays(1)->format('Y-m-d');
        Appointment::factory()->create([
            'appointment_date' => $date,
            'appointment_time' => '09:00',
            'appointment_status' => 'scheduled',
        ]);
        Appointment::factory()->create([
            'appointment_date' => $date,
            'appointment_time' => '09:00',
            'appointment_status' => 'scheduled',
        ]);

        $response = $this->getJson('/api/booking/available-slots?date=' . $date)
            ->assertOk();

        $slot = collect($response->json('slots'))->firstWhere('time', '09:00');
        $this->assertNotNull($slot, 'slot 09:00 should exist');
        $this->assertFalse($slot['available'], 'slot should be unavailable once capacity of 2 is reached');
    }
}
