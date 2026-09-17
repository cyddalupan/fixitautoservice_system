<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class AppointmentFactory extends Factory
{
    protected $model = \App\Models\Appointment::class;

    public function definition(): array
    {
        return [
            'customer_id' => \App\Models\Customer::factory(),
            'vehicle_id' => \App\Models\Vehicle::factory(),
            'appointment_number' => \App\Models\Appointment::generateAppointmentNumber(),
            'appointment_date' => now()->addDays(1)->format('Y-m-d'),
            'appointment_time' => '09:00',
            'appointment_type' => 'regular_service',
            'appointment_status' => 'scheduled',
            'priority' => 'normal',
            'booking_source' => 'admin',
        ];
    }
}
