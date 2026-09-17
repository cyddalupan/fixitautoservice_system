<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\JobOrder;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

class JobOrderFactory extends Factory
{
    protected $model = JobOrder::class;

    public function definition(): array
    {
        $customer = Customer::factory()->create();
        $vehicle  = Vehicle::factory()->create(['customer_id' => $customer->id]);

        return [
            'customer_id'        => $customer->id,
            'vehicle_id'         => $vehicle->id,
            'service_advisor_id' => User::factory()->create()->id,
            'technician_id'      => null,
            'job_order_number'   => 'JO-' . now()->format('Y') . '-' . str_pad((string) $this->faker->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'job_order_date'     => now()->toDateString(),
            'job_order_status'   => 'draft',
            'priority'           => 'normal',
            'job_order_type'     => 'repair',
        ];
    }
}
