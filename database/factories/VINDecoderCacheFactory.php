<?php

namespace Database\Factories;

use App\Models\VINDecoderCache;
use Illuminate\Database\Eloquent\Factories\Factory;

class VINDecoderCacheFactory extends Factory
{
    protected $model = VINDecoderCache::class;

    public function definition(): array
    {
        $make = $this->faker->randomElement(['Toyota', 'Honda', 'Mitsubishi', 'Nissan', 'Ford']);
        $model = $this->faker->randomElement(['Vios', 'City', 'Mirage', 'Navara', 'Ranger']);
        $year = $this->faker->numberBetween(2010, 2024);

        return [
            'vin' => strtoupper(substr($this->faker->bothify('################'), 0, 17)),
            'decoded_data' => [
                'Make' => $make,
                'Model' => $model,
                'Year' => $year,
                'Trim' => $this->faker->word(),
                'Engine' => $this->faker->randomElement(['1.3L', '1.5L', '2.0L']),
                'Transmission' => $this->faker->randomElement(['Automatic', 'Manual']),
                'DriveType' => '2WD',
                'BodyStyle' => 'Sedan',
                'FuelType' => 'Gasoline',
                'Manufacturer' => $make,
                'PlantCode' => $this->faker->bothify('?#'),
            ],
            'make' => $make,
            'model' => $model,
            'year' => $year,
            'trim' => null,
            'engine' => null,
            'transmission' => null,
            'drive_type' => null,
            'body_style' => null,
            'fuel_type' => null,
            'manufacturer' => null,
            'plant_code' => null,
            'cache_hits' => 0,
            'last_accessed_at' => now(),
            'expires_at' => now()->addDays(30),
        ];
    }
}
