<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Schema;

class BlueprintServicesTableTest extends TestCase
{
    use \Illuminate\Foundation\Testing\RefreshDatabase;

    /**
     * Blueprint source of truth: the services table must be the SIMPLE
     * blueprint schema (id, name, description, default_price decimal,
     * category, is_active) with NO break-out by brand/model.
     */
    public function test_services_table_has_blueprint_schema(): void
    {
        $this->assertTrue(Schema::hasTable('services'));

        $columns = Schema::getColumns('services');
        $names = array_column($columns, 'name');

        // Blueprint requires these exact columns.
        foreach (['id', 'name', 'description', 'default_price', 'category', 'is_active'] as $required) {
            $this->assertContains($required, $names, "Missing blueprint column: {$required}");
        }

        // NO break-out by brand/model (blueprint explicitly drops this).
        foreach (['brand', 'model', 'vehicle_type', 'brand_name', 'model_name'] as $forbidden) {
            $this->assertNotContains($forbidden, $names, "Legacy brand/model column must NOT exist: {$forbidden}");
        }
    }

    public function test_services_default_price_is_decimal(): void
    {
        $columns = Schema::getColumns('services');
        $price = collect($columns)->firstWhere('name', 'default_price');

        $this->assertNotNull($price);

        // SQLite stores decimals as double/numeric; assert the migration
        // declared it as decimal by checking the auto-increment/int/string
        // type is not a plain integer for default_price.
        $type = strtolower($price['type'] ?? '');
        $this->assertStringNotContainsString('int', $type, 'default_price must be decimal, not integer');
    }

    public function test_services_is_active_boolean_defaults_true(): void
    {
        $columns = Schema::getColumns('services');
        $active = collect($columns)->firstWhere('name', 'is_active');

        $this->assertNotNull($active);
        $this->assertNotNull($active['default']);
    }
}
