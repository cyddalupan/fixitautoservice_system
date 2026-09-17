<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ServicePricingFactory extends Factory
{
    protected $model = \App\Models\ServicePricing::class;

    public function definition(): array
    {
        return [
            'service_type_id' => \App\Models\ServiceType::factory(),
            'vehicle_type' => fake()->randomElement(['car', 'suv', 'truck', 'van', null]),
            'brand_name' => fake()->randomElement(['Toyota', 'Honda', 'Mitsubishi', 'Nissan']),
            'model_name' => fake()->randomElement(['Vios', 'Civic', 'Montero', 'Navara']),
            'price' => fake()->randomFloat(2, 100, 5000),
            'is_active' => true,
        ];
    }
}
