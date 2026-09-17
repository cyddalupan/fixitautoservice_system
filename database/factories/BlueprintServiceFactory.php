<?php

namespace Database\Factories;

use App\Models\BlueprintService;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BlueprintService>
 */
class BlueprintServiceFactory extends Factory
{
    protected $model = BlueprintService::class;

    public function definition(): array
    {
        return [
            'name' => ucwords(fake()->words(3, true)),
            'description' => fake()->sentence(),
            'default_price' => fake()->randomFloat(2, 500, 5000),
            'category' => fake()->randomElement(['Maintenance', 'Brakes', 'Engine', 'Electrical', 'Aircon', 'Detailing']),
            'is_active' => true,
        ];
    }
}
