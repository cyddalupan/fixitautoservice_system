<?php

namespace Database\Seeders;

use App\Models\VehicleColor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class VehicleColorSeeder extends Seeder
{
    public function run(): void
    {
        $colors = [
            ['name' => 'White', 'hex_code' => '#FFFFFF'],
            ['name' => 'Black', 'hex_code' => '#000000'],
            ['name' => 'Silver', 'hex_code' => '#C0C0C0'],
            ['name' => 'Gray', 'hex_code' => '#808080'],
            ['name' => 'Red', 'hex_code' => '#FF0000'],
            ['name' => 'Blue', 'hex_code' => '#0000FF'],
            ['name' => 'Dark Blue', 'hex_code' => '#00008B'],
            ['name' => 'Light Blue', 'hex_code' => '#ADD8E6'],
            ['name' => 'Green', 'hex_code' => '#008000'],
            ['name' => 'Dark Green', 'hex_code' => '#006400'],
            ['name' => 'Yellow', 'hex_code' => '#FFFF00'],
            ['name' => 'Gold', 'hex_code' => '#FFD700'],
            ['name' => 'Orange', 'hex_code' => '#FFA500'],
            ['name' => 'Brown', 'hex_code' => '#A52A2A'],
            ['name' => 'Beige', 'hex_code' => '#F5F5DC'],
            ['name' => 'Maroon', 'hex_code' => '#800000'],
            ['name' => 'Purple', 'hex_code' => '#800080'],
            ['name' => 'Pink', 'hex_code' => '#FFC0CB'],
            ['name' => 'Burgundy', 'hex_code' => '#800020'],
            ['name' => 'Bronze', 'hex_code' => '#CD7F32'],
            ['name' => 'Champagne', 'hex_code' => '#F7E7CE'],
            ['name' => 'Charcoal', 'hex_code' => '#36454F'],
            ['name' => 'Navy', 'hex_code' => '#000080'],
            ['name' => 'Teal', 'hex_code' => '#008080'],
            ['name' => 'Lime Green', 'hex_code' => '#32CD32'],
            ['name' => 'Coral', 'hex_code' => '#FF7F50'],
            ['name' => 'Cyan', 'hex_code' => '#00FFFF'],
            ['name' => 'Magenta', 'hex_code' => '#FF00FF'],
            ['name' => 'Violet', 'hex_code' => '#EE82EE'],
            ['name' => 'Turquoise', 'hex_code' => '#40E0D0'],
            ['name' => 'Pearl White', 'hex_code' => '#FDF5E6'],
            ['name' => 'Midnight Blue', 'hex_code' => '#191970'],
            ['name' => 'Mint Green', 'hex_code' => '#98FB98'],
            ['name' => 'Rust', 'hex_code' => '#B7410E'],
            ['name' => 'Tan', 'hex_code' => '#D2B48C'],
            ['name' => 'Ivory', 'hex_code' => '#FFFFF0'],
            ['name' => 'Cream', 'hex_code' => '#FFFDD0'],
            ['name' => 'Wine Red', 'hex_code' => '#722F37'],
            ['name' => 'Sapphire Blue', 'hex_code' => '#0F52BA'],
            ['name' => 'Graphite', 'hex_code' => '#251607'],
        ];

        $popularity = count($colors);
        foreach ($colors as $color) {
            VehicleColor::create([
                'name' => $color['name'],
                'slug' => Str::slug($color['name']),
                'hex_code' => $color['hex_code'],
                'popularity_score' => $popularity--,
                'is_active' => true,
            ]);
        }

        $this->command->info('Seeded ' . count($colors) . ' vehicle colors.');
    }
}
