<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ServiceTypeFactory extends Factory
{
    protected $model = \App\Models\ServiceType::class;

    public function definition(): array
    {
        return [
            'key' => fake()->unique()->slug(2),
            'name' => fake()->randomElement(['Preventive Maintenance', 'Corrective Maintenance', 'Diagnostic', 'Body Repair', 'Electrical']),
        ];
    }
}
