<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class VehicleFactory extends Factory
{
    protected $model = \App\Models\Vehicle::class;

    public function definition(): array
    {
        return [
            'customer_id' => \App\Models\Customer::factory(),
            'vin' => strtoupper(substr($this->faker->bothify('############'), 0, 17)),
            'license_plate' => strtoupper($this->faker->bothify('???:###')),
            'make' => $this->faker->randomElement(['Toyota', 'Honda', 'Mitsubishi', 'Nissan', 'Ford']),
            'model' => $this->faker->randomElement(['Vios', 'City', 'Mirage', 'Navara', 'Ranger']),
            'year' => $this->faker->numberBetween(2010, 2024),
            'color' => $this->faker->colorName(),
            'odometer' => $this->faker->numberBetween(10000, 150000),
            'is_active' => true,
        ];
    }
}
