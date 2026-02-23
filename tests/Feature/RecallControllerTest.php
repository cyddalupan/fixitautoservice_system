<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleRecall;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RecallControllerTest extends TestCase
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
    public function it_can_access_recall_dashboard()
    {
        $response = $this->get(route('recalls.dashboard'));
        
        $response->assertStatus(200);
        $response->assertViewIs('vehicle-tools.recall-dashboard');
        $response->assertViewHas([
            'totalRecalls',
            'openRecalls',
            'inProgressRecalls',
            'completedRecalls',
            'closedRecalls',
            'needsNotification',
            'overdueRecalls',
            'urgentRecalls',
        ]);
    }

    /** @test */
    public function it_can_list_recalls()
    {
        VehicleRecall::factory()->count(3)->create();
        
        $response = $this->get(route('recalls.index'));
        
        $response->assertStatus(200);
        $response->assertViewIs('vehicle-tools.recalls-index');
        $response->assertViewHas('recalls');
    }

    /** @test */
    public function it_can_filter_recalls_by_status()
    {
        VehicleRecall::factory()->create(['status' => 'open']);
        VehicleRecall::factory()->create(['status' => 'completed']);
        
        $response = $this->get(route('recalls.index', ['status' => 'open']));
        
        $response->assertStatus(200);
        $response->assertViewHas('recalls', function ($recalls) {
            return $recalls->count() === 1 && $recalls->first()->status === 'open';
        });
    }

    /** @test */
    public function it_can_search_recalls()
    {
        $vehicle = Vehicle::factory()->create(['vin' => 'TESTVIN1234567890']);
        $recall = VehicleRecall::factory()->create([
            'vehicle_id' => $vehicle->id,
            'component' => 'Test Component',
        ]);
        
        $response = $this->get(route('recalls.index', ['search' => 'TESTVIN']));
        
        $response->assertStatus(200);
        $response->assertViewHas('recalls', function ($recalls) use ($recall) {
            return $recalls->contains($recall);
        });
    }

    /** @test */
    public function it_can_show_recall_details()
    {
        $recall = VehicleRecall::factory()->create();
        
        $response = $this->get(route('recalls.show', $recall->id));
        
        $response->assertStatus(200);
        $response->assertViewIs('vehicle-tools.recall-show');
        $response->assertViewHas('recall', $recall);
    }

    /** @test */
    public function it_can_create_recall()
    {
        $vehicle = Vehicle::factory()->create();
        
        $response = $this->post(route('recalls.store'), [
            'vehicle_id' => $vehicle->id,
            'campaign_number' => 'R2023001',
            'component' => 'Test Component',
            'summary' => 'Test summary',
            'consequence' => 'Test consequence',
            'remedy' => 'Test remedy',
            'recall_date' => now()->format('Y-m-d'),
            'status' => 'open',
            'severity' => 'medium',
        ]);
        
        $response->assertRedirect(route('recalls.show', 1));
        $response->assertSessionHas('success');
        
        $this->assertDatabaseHas('vehicle_recalls', [
            'campaign_number' => 'R2023001',
            'component' => 'Test Component',
            'status' => 'open',
        ]);
    }

    /** @test */
    public function it_validates_recall_creation()
    {
        $response = $this->post(route('recalls.store'), []);
        
        $response->assertSessionHasErrors([
            'vehicle_id',
            'campaign_number',
            'component',
            'summary',
            'consequence',
            'remedy',
            'recall_date',
            'status',
            'severity',
        ]);
    }

    /** @test */
    public function it_can_update_recall()
    {
        $recall = VehicleRecall::factory()->create(['status' => 'open']);
        
        $response = $this->put(route('recalls.update', $recall->id), [
            'campaign_number' => $recall->campaign_number,
            'component' => $recall->component,
            'summary' => 'Updated summary',
            'consequence' => $recall->consequence,
            'remedy' => $recall->remedy,
            'recall_date' => $recall->recall_date->format('Y-m-d'),
            'status' => 'in_progress',
            'severity' => $recall->severity,
        ]);
        
        $response->assertRedirect(route('recalls.show', $recall->id));
        $response->assertSessionHas('success');
        
        $this->assertDatabaseHas('vehicle_recalls', [
            'id' => $recall->id,
            'summary' => 'Updated summary',
            'status' => 'in_progress',
        ]);
    }

    /** @test */
    public function it_can_delete_recall()
    {
        $recall = VehicleRecall::factory()->create();
        
        $response = $this->delete(route('recalls.destroy', $recall->id));
        
        $response->assertRedirect(route('recalls.index'));
        $response->assertSessionHas('success');
        
        $this->assertDatabaseMissing('vehicle_recalls', ['id' => $recall->id]);
    }

    /** @test */
    public function it_can_check_recalls_for_vehicle()
    {
        $vehicle = Vehicle::factory()->create(['vin' => '1HGCM82633A123456']);
        
        $response = $this->get(route('recalls.check-vehicle', $vehicle->id));
        
        $response->assertRedirect(route('recalls.index'));
        $response->assertSessionHas('success');
        
        $this->assertNotNull($vehicle->fresh()->last_recall_check);
    }

    /** @test */
    public function it_can_batch_check_recalls()
    {
        $vehicle1 = Vehicle::factory()->create(['vin' => '1HGCM82633A123456']);
        $vehicle2 = Vehicle::factory()->create(['vin' => '2HGFA16566H123456']);
        
        $response = $this->post(route('recalls.batch-check'), [
            'vehicle_ids' => [$vehicle1->id, $vehicle2->id],
        ]);
        
        $response->assertRedirect(route('recalls.dashboard'));
        $response->assertSessionHas('success');
        
        $this->assertNotNull($vehicle1->fresh()->last_recall_check);
        $this->assertNotNull($vehicle2->fresh()->last_recall_check);
    }

    /** @test */
    public function it_can_send_recall_notification()
    {
        $recall = VehicleRecall::factory()->create(['customer_notified' => false]);
        
        $response = $this->post(route('recalls.send-notification', $recall->id));
        
        $response->assertRedirect(route('recalls.show', $recall->id));
        $response->assertSessionHas('success');
        
        $this->assertTrue($recall->fresh()->customer_notified);
    }

    /** @test */
    public function it_can_batch_send_notifications()
    {
        $recall1 = VehicleRecall::factory()->create(['customer_notified' => false]);
        $recall2 = VehicleRecall::factory()->create(['customer_notified' => false]);
        
        $response = $this->post(route('recalls.batch-send-notifications'), [
            'recall_ids' => [$recall1->id, $recall2->id],
        ]);
        
        $response->assertRedirect(route('recalls.dashboard'));
        $response->assertSessionHas('success');
        
        $this->assertTrue($recall1->fresh()->customer_notified);
        $this->assertTrue($recall2->fresh()->customer_notified);
    }

    /** @test */
    public function it_can_update_recall_status()
    {
        $recall = VehicleRecall::factory()->create(['status' => 'open']);
        
        $response = $this->post(route('recalls.update-status', $recall->id), [
            'status' => 'completed',
            'repair_date' => now()->format('Y-m-d'),
            'actual_cost' => 500.00,
        ]);
        
        $response->assertRedirect(route('recalls.show', $recall->id));
        $response->assertSessionHas('success');
        
        $this->assertDatabaseHas('vehicle_recalls', [
            'id' => $recall->id,
            'status' => 'completed',
            'actual_cost' => 500.00,
        ]);
    }

    /** @test */
    public function it_can_export_recalls_as_json()
    {
        VehicleRecall::factory()->count(2)->create();
        
        $response = $this->get(route('recalls.export', ['format' => 'json']));
        
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertHeader('Content-Disposition', 'attachment; filename="recalls-export-' . now()->format('Y-m-d') . '.json"');
    }

    /** @test */
    public function it_can_export_recalls_as_csv()
    {
        VehicleRecall::factory()->count(2)->create();
        
        $response = $this->get(route('recalls.export', ['format' => 'csv']));
        
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv');
        $response->assertHeader('Content-Disposition', 'attachment; filename="recalls-export-' . now()->format('Y-m-d') . '.csv"');
    }

    /** @test */
    public function it_can_get_recall_statistics()
    {
        VehicleRecall::factory()->count(3)->create();
        
        $response = $this->getJson(route('recalls.statistics'));
        
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'total_recalls',
            'open_recalls',
            'in_progress_recalls',
            'completed_recalls',
            'closed_recalls',
            'needs_notification',
            'overdue_recalls',
            'urgent_recalls',
            'total_estimated_cost',
            'total_actual_cost',
            'cost_savings',
            'recall_trends',
            'top_components',
            'top_makes',
        ]);
    }

    /** @test */
    public function it_can_access_needs_notification_page()
    {
        VehicleRecall::factory()->create(['customer_notified' => false, 'status' => 'open']);
        
        $response = $this->get(route('recalls.needs-notification'));
        
        $response->assertStatus(200);
        $response->assertViewIs('vehicle-tools.recalls-needs-notification');
        $response->assertViewHas('recalls');
    }

    /** @test */
    public function it_can_access_overdue_recalls_page()
    {
        VehicleRecall::factory()->create([
            'status' => 'open',
            'recall_date' => now()->subDays(31),
        ]);
        
        $response = $this->get(route('recalls.overdue'));
        
        $response->assertStatus(200);
        $response->assertViewIs('vehicle-tools.recalls-overdue');
        $response->assertViewHas('recalls');
    }

    /** @test */
    public function it_can_access_urgent_recalls_page()
    {
        VehicleRecall::factory()->create([
            'status' => 'open',
            'severity' => 'high',
        ]);
        
        $response = $this->get(route('recalls.urgent'));
        
        $response->assertStatus(200);
        $response->assertViewIs('vehicle-tools.recalls-urgent');
        $response->assertViewHas('recalls');
    }

    /** @test */
    public function it_can_search_recalls_via_search_page()
    {
        $recall = VehicleRecall::factory()->create(['component' => 'Test Search Component']);
        
        $response = $this->get(route('recalls.search', ['q' => 'Search']));
        
        $response->assertStatus(200);
        $response->assertViewIs('vehicle-tools.recalls-search');
        $response->assertViewHas('recalls', function ($recalls) use ($recall) {
            return $recalls->contains($recall);
        });
    }

    /** @test */
    public function it_can_access_recalls_via_api()
    {
        VehicleRecall::factory()->count(3)->create();
        
        $response = $this->getJson(route('recalls.api.index'));
        
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data',
            'pagination' => ['total', 'limit', 'offset', 'has_more'],
        ]);
    }

    /** @test */
    public function it_can_show_single_recall_via_api()
    {
        $recall = VehicleRecall::factory()->create();
        
        $response = $this->getJson(route('recalls.api.show', $recall->id));
        
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'id',
                'campaign_number',
                'component',
                'summary',
                'consequence',
                'remedy',
                'recall_date',
                'status',
                'severity',
                'estimated_cost',
                'actual_cost',
                'vehicle',
                'customer',
            ],
        ]);
    }

    /** @test */
    public function it_returns_404_for_nonexistent_recall_via_api()
    {
        $response = $this->getJson(route('recalls.api.show', 999));
        
        $response->assertStatus(404);
        $response->assertJson([
            'success' => false,
            'message' => 'Recall not found',
        ]);
    }

    /** @test */
    public function it_requires_authentication()
    {
        auth()->logout();
        
        $response = $this->get(route('recalls.index'));
        
        $response->assertRedirect('/login');
    }
}