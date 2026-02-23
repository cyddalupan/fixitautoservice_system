<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleRecall;
use App\Models\VINDecoderCache;
use Illuminate\Foundation\Testing\RefreshDatabase;

class VehicleToolsControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    /** @test */
    public function it_can_access_vehicle_tools_dashboard()
    {
        $response = $this->get(route('vehicle-tools.dashboard'));
        
        $response->assertStatus(200);
        $response->assertViewIs('vehicle-tools.dashboard');
        $response->assertViewHas(['totalVehicles', 'vehiclesWithVIN', 'vehiclesDecoded']);
    }

    /** @test */
    public function it_can_access_vin_decoder_page()
    {
        $response = $this->get(route('vehicle-tools.vin-decoder'));
        
        $response->assertStatus(200);
        $response->assertViewIs('vehicle-tools.vin-decoder');
        $response->assertViewHas('vehicles');
    }

    /** @test */
    public function it_can_decode_a_vin()
    {
        $vin = '1HGCM82633A123456';
        
        $response = $this->post(route('vehicle-tools.decode-vin'), [
            'vin' => $vin,
        ]);
        
        $response->assertRedirect(route('vehicle-tools.vin-results', ['vin' => $vin]));
        $response->assertSessionHas('success');
        
        // Check that cache entry was created
        $this->assertDatabaseHas('vin_decoder_cache', [
            'vin' => $vin,
        ]);
    }

    /** @test */
    public function it_validates_vin_format()
    {
        $response = $this->post(route('vehicle-tools.decode-vin'), [
            'vin' => 'invalid-vin',
        ]);
        
        $response->assertSessionHasErrors('vin');
    }

    /** @test */
    public function it_can_access_service_history_page()
    {
        $vehicle = Vehicle::factory()->create();
        
        $response = $this->get(route('vehicle-tools.service-history'));
        
        $response->assertStatus(200);
        $response->assertViewIs('vehicle-tools.service-history');
        $response->assertViewHas(['vehicle', 'serviceRecords', 'vehicles']);
    }

    /** @test */
    public function it_can_access_service_history_for_specific_vehicle()
    {
        $vehicle = Vehicle::factory()->create();
        
        $response = $this->get(route('vehicle-tools.service-history.vehicle', ['vehicleId' => $vehicle->id]));
        
        $response->assertStatus(200);
        $response->assertViewIs('vehicle-tools.service-history');
        $response->assertViewHas('vehicle', $vehicle);
    }

    /** @test */
    public function it_can_batch_decode_vins()
    {
        $vehicle1 = Vehicle::factory()->create(['vin' => '1HGCM82633A123456']);
        $vehicle2 = Vehicle::factory()->create(['vin' => '2HGFA16566H123456']);
        
        $response = $this->post(route('vehicle-tools.batch-decode-vin'), [
            'vehicle_ids' => [$vehicle1->id, $vehicle2->id],
        ]);
        
        $response->assertRedirect(route('vehicle-tools.dashboard'));
        $response->assertSessionHas('success');
        
        // Check that cache entries were created
        $this->assertDatabaseHas('vin_decoder_cache', [
            'vin' => $vehicle1->vin,
        ]);
        $this->assertDatabaseHas('vin_decoder_cache', [
            'vin' => $vehicle2->vin,
        ]);
    }

    /** @test */
    public function it_can_check_recalls_for_vehicle()
    {
        $vehicle = Vehicle::factory()->create(['vin' => '1HGCM82633A123456']);
        
        $response = $this->get(route('vehicle-tools.check-recalls', ['vehicleId' => $vehicle->id]));
        
        $response->assertRedirect(route('vehicle-tools.service-history', ['vehicleId' => $vehicle->id]));
        $response->assertSessionHas('success');
        
        // Check that last_recall_check was updated
        $this->assertNotNull($vehicle->fresh()->last_recall_check);
    }

    /** @test */
    public function it_can_batch_check_recalls()
    {
        $vehicle1 = Vehicle::factory()->create(['vin' => '1HGCM82633A123456']);
        $vehicle2 = Vehicle::factory()->create(['vin' => '2HGFA16566H123456']);
        
        $response = $this->post(route('vehicle-tools.batch-check-recalls'), [
            'vehicle_ids' => [$vehicle1->id, $vehicle2->id],
        ]);
        
        $response->assertRedirect(route('vehicle-tools.dashboard'));
        $response->assertSessionHas('success');
        
        // Check that last_recall_check was updated for both vehicles
        $this->assertNotNull($vehicle1->fresh()->last_recall_check);
        $this->assertNotNull($vehicle2->fresh()->last_recall_check);
    }

    /** @test */
    public function it_can_export_vehicle_data()
    {
        $vehicle = Vehicle::factory()->create();
        
        $response = $this->get(route('vehicle-tools.export-vehicle-data', ['vehicleId' => $vehicle->id]));
        
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertHeader('Content-Disposition', "attachment; filename=\"vehicle-{$vehicle->vin}-" . now()->format('Y-m-d') . '.json"');
    }

    /** @test */
    public function it_can_clear_expired_cache()
    {
        // Create an expired cache entry
        VINDecoderCache::factory()->create([
            'expires_at' => now()->subDay(),
        ]);
        
        $response = $this->post(route('vehicle-tools.clear-expired-cache'));
        
        $response->assertRedirect(route('vehicle-tools.dashboard'));
        $response->assertSessionHas('success');
        
        // Check that expired cache was cleared
        $this->assertDatabaseCount('vin_decoder_cache', 0);
    }

    /** @test */
    public function it_can_get_statistics()
    {
        Vehicle::factory()->count(3)->create();
        VehicleRecall::factory()->count(2)->create();
        VINDecoderCache::factory()->count(1)->create();
        
        $response = $this->get(route('vehicle-tools.statistics'));
        
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'total_vehicles',
            'vehicles_with_vin',
            'vehicles_decoded',
            'vehicles_with_recalls',
            'total_recalls',
            'open_recalls',
            'cache_entries',
            'cache_hits',
        ]);
    }

    /** @test */
    public function it_requires_authentication()
    {
        auth()->logout();
        
        $response = $this->get(route('vehicle-tools.dashboard'));
        
        $response->assertRedirect('/login');
    }
}