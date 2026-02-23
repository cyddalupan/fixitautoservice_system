<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\VINDecoderCache;
use Illuminate\Foundation\Testing\RefreshDatabase;

class VINDecoderControllerTest extends TestCase
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
    public function it_can_decode_vin_via_api()
    {
        $vin = '1HGCM82633A123456';
        
        $response = $this->postJson(route('vin-decoder.decode'), [
            'vin' => $vin,
        ]);
        
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'cached',
            'data',
            'basic_info',
            'specifications',
            'features',
            'maintenance_schedule',
            'cache_info',
        ]);
        
        $response->assertJson(['success' => true]);
        
        // Check that cache entry was created
        $this->assertDatabaseHas('vin_decoder_cache', [
            'vin' => $vin,
        ]);
    }

    /** @test */
    public function it_returns_cached_data_when_available()
    {
        $vin = '1HGCM82633A123456';
        $cacheEntry = VINDecoderCache::factory()->create([
            'vin' => $vin,
            'expires_at' => now()->addDay(),
        ]);
        
        $response = $this->postJson(route('vin-decoder.decode'), [
            'vin' => $vin,
        ]);
        
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'cached' => true,
        ]);
        
        // Check that cache hit was incremented
        $this->assertEquals(1, $cacheEntry->fresh()->cache_hits);
    }

    /** @test */
    public function it_can_force_refresh_cached_data()
    {
        $vin = '1HGCM82633A123456';
        $cacheEntry = VINDecoderCache::factory()->create([
            'vin' => $vin,
            'expires_at' => now()->addDay(),
        ]);
        
        $response = $this->postJson(route('vin-decoder.decode'), [
            'vin' => $vin,
            'force_refresh' => true,
        ]);
        
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'cached' => false,
        ]);
    }

    /** @test */
    public function it_validates_vin_format_in_api()
    {
        $response = $this->postJson(route('vin-decoder.decode'), [
            'vin' => 'invalid',
        ]);
        
        $response->assertStatus(400);
        $response->assertJson([
            'success' => false,
            'message' => 'Unable to decode VIN. Please check the VIN and try again.',
        ]);
    }

    /** @test */
    public function it_can_batch_decode_vins_via_api()
    {
        $vins = ['1HGCM82633A123456', '2HGFA16566H123456', '3VWDP7AJ7DM123456'];
        
        $response = $this->postJson(route('vin-decoder.batch-decode'), [
            'vins' => $vins,
        ]);
        
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'summary' => ['total', 'successful', 'failed'],
            'results',
        ]);
        
        $response->assertJson([
            'success' => true,
            'summary' => [
                'total' => 3,
                'successful' => 3,
                'failed' => 0,
            ],
        ]);
        
        // Check that cache entries were created
        foreach ($vins as $vin) {
            $this->assertDatabaseHas('vin_decoder_cache', [
                'vin' => $vin,
            ]);
        }
    }

    /** @test */
    public function it_returns_cache_statistics()
    {
        VINDecoderCache::factory()->count(3)->create();
        
        $response = $this->getJson(route('vin-decoder.cache-stats'));
        
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'total_entries',
            'total_hits',
            'expired_entries',
            'entries_needing_refresh',
            'average_hits_per_entry',
            'most_popular',
            'recent_decodes',
        ]);
    }

    /** @test */
    public function it_can_clear_cache()
    {
        VINDecoderCache::factory()->count(3)->create();
        
        $response = $this->postJson(route('vin-decoder.clear-cache'), [
            'type' => 'all',
        ]);
        
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'deleted_count' => 3,
        ]);
        
        $this->assertDatabaseCount('vin_decoder_cache', 0);
    }

    /** @test */
    public function it_can_clear_only_expired_cache()
    {
        VINDecoderCache::factory()->create(['expires_at' => now()->subDay()]); // Expired
        VINDecoderCache::factory()->create(['expires_at' => now()->addDay()]); // Not expired
        
        $response = $this->postJson(route('vin-decoder.clear-cache'), [
            'type' => 'expired',
        ]);
        
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'deleted_count' => 1,
        ]);
        
        $this->assertDatabaseCount('vin_decoder_cache', 1);
    }

    /** @test */
    public function it_can_validate_vin_format()
    {
        $response = $this->postJson(route('vin-decoder.validate'), [
            'vin' => '1HGCM82633A123456',
        ]);
        
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'valid',
            'message',
            'vin',
            'check_digit_valid',
        ]);
        
        $response->assertJson(['valid' => true]);
    }

    /** @test */
    public function it_reports_invalid_vin_format()
    {
        $response = $this->postJson(route('vin-decoder.validate'), [
            'vin' => 'invalid-vin-here',
        ]);
        
        $response->assertStatus(200);
        $response->assertJson(['valid' => false]);
    }

    /** @test */
    public function it_can_get_decoding_history()
    {
        $vin = '1HGCM82633A123456';
        VINDecoderCache::factory()->count(3)->create(['vin' => $vin]);
        
        $response = $this->getJson(route('vin-decoder.history', ['vin' => $vin]));
        
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'vin',
            'vehicle',
            'history',
            'total_decodes',
            'total_hits',
        ]);
    }

    /** @test */
    public function it_can_export_data_as_json()
    {
        VINDecoderCache::factory()->count(2)->create();
        
        $response = $this->get(route('vin-decoder.export', ['format' => 'json']));
        
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertHeader('Content-Disposition', 'attachment; filename="vin-decoding-export-' . now()->format('Y-m-d') . '.json"');
    }

    /** @test */
    public function it_can_export_data_as_csv()
    {
        VINDecoderCache::factory()->count(2)->create();
        
        $response = $this->get(route('vin-decoder.export', ['format' => 'csv']));
        
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv');
        $response->assertHeader('Content-Disposition', 'attachment; filename="vin-decoding-export-' . now()->format('Y-m-d') . '.csv"');
    }

    /** @test */
    public function it_requires_authentication_for_api_routes()
    {
        auth()->logout();
        
        $response = $this->postJson(route('vin-decoder.decode'), [
            'vin' => '1HGCM82633A123456',
        ]);
        
        $response->assertStatus(401);
    }
}