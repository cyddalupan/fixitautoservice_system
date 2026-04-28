<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_types', function (Blueprint $table) {
            $table->id();
            $table->string('key', 50)->unique();              // e.g., 'preventive_maintenance'
            $table->string('name', 100);                       // e.g., 'PREVENTIVE MAINTENANCE'
            $table->string('icon', 10)->nullable();            // optional emoji icon
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Seed the master service types
        $types = [
            ['key' => 'preventive_maintenance',   'name' => 'PREVENTIVE MAINTENANCE',       'icon' => '🔧', 'sort_order' => 1],
            ['key' => 'auto_mechanical',          'name' => 'AUTO-MECHANICAL',              'icon' => '⚙️', 'sort_order' => 2],
            ['key' => 'auto_electrical',           'name' => 'AUTO-ELECTRICAL',             'icon' => '⚡', 'sort_order' => 3],
            ['key' => 'auto_electronics',          'name' => 'AUTO-ELECTRONICS',            'icon' => '🔌', 'sort_order' => 4],
            ['key' => 'auto_air_conditioning',    'name' => 'AUTO AIR-CONDITIONING',        'icon' => '❄️', 'sort_order' => 5],
            ['key' => 'body_repair_painting',      'name' => 'BODY REPAIR AND PAINTING',    'icon' => '🎨', 'sort_order' => 6],
            ['key' => 'auto_parts_sales',          'name' => 'AUTO PARTS SALES',            'icon' => '🔩', 'sort_order' => 7],
            ['key' => 'home_service_request',      'name' => 'HOME SERVICE REQUEST',        'icon' => '🏠', 'sort_order' => 8],
        ];

        foreach ($types as $type) {
            DB::table('service_types')->insert($type);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('service_types');
    }
};
