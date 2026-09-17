<?php

namespace Database\Factories;

use App\Models\Vehicle;
use App\Models\VehicleRecall;
use Illuminate\Database\Eloquent\Factories\Factory;

class VehicleRecallFactory extends Factory
{
    protected $model = VehicleRecall::class;

    public function definition(): array
    {
        return [
            'vehicle_id' => Vehicle::factory(),
            'recall_id' => 'RCL-' . $this->faker->unique()->numerify('#######'),
            'campaign_number' => $this->faker->bothify('??##-####'),
            'component' => $this->faker->randomElement(['Brake System', 'Airbag', 'Fuel Pump', 'Engine', 'Steering', 'Seatbelt']),
            'summary' => $this->faker->sentence(),
            'consequence' => $this->faker->sentence(),
            'remedy' => $this->faker->sentence(),
            'recall_date' => $this->faker->date(),
            'status' => $this->faker->randomElement(['open', 'in_progress', 'completed', 'closed']),
            'notification_date' => null,
            'repair_date' => null,
            'repair_notes' => null,
            'estimated_cost' => $this->faker->randomFloat(2, 1000, 50000),
            'actual_cost' => null,
            'customer_notified' => false,
            'customer_notification_date' => null,
            'customer_responded' => false,
            'customer_response_date' => null,
            'customer_response_notes' => null,
            'is_active' => true,
            'notes' => null,
        ];
    }
}
