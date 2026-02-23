<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BusinessIntelligenceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a test user
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    /** @test */
    public function business_intelligence_dashboard_is_accessible()
    {
        $response = $this->get('/business-intelligence');
        
        $response->assertStatus(200);
        $response->assertViewIs('business-intelligence.dashboard');
        $response->assertSee('Business Intelligence Dashboard');
    }

    /** @test */
    public function metrics_page_is_accessible()
    {
        $response = $this->get('/business-intelligence/metrics');
        
        $response->assertStatus(200);
        $response->assertViewIs('business-intelligence.metrics');
        $response->assertSee('Business Intelligence Metrics');
    }

    /** @test */
    public function technician_performance_page_is_accessible()
    {
        $response = $this->get('/business-intelligence/technician-performance');
        
        $response->assertStatus(200);
        $response->assertViewIs('business-intelligence.technician-performance');
        $response->assertSee('Technician Performance Analytics');
    }

    /** @test */
    public function customer_retention_page_is_accessible()
    {
        $response = $this->get('/business-intelligence/customer-retention');
        
        $response->assertStatus(200);
        $response->assertViewIs('business-intelligence.customer-retention');
        $response->assertSee('Customer Retention Analytics');
    }

    /** @test */
    public function widget_management_page_is_accessible()
    {
        $response = $this->get('/business-intelligence/widget-management');
        
        $response->assertStatus(200);
        $response->assertViewIs('business-intelligence.widget-management');
        $response->assertSee('Dashboard Widget Management');
    }

    /** @test */
    public function analytics_dashboard_is_accessible()
    {
        $response = $this->get('/analytics/dashboard');
        
        $response->assertStatus(200);
        $response->assertViewIs('analytics.dashboard');
    }

    /** @test */
    public function analytics_metrics_page_is_accessible()
    {
        $response = $this->get('/analytics/metrics');
        
        $response->assertStatus(200);
        $response->assertViewIs('analytics.metrics');
    }

    /** @test */
    public function dashboard_widgets_api_returns_json()
    {
        $response = $this->get('/dashboard/widgets');
        
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'widgets' => [
                '*' => [
                    'id',
                    'widget_type',
                    'widget_title',
                    'column_position',
                    'row_position',
                    'width',
                    'height'
                ]
            ]
        ]);
    }

    /** @test */
    public function generate_daily_metrics_endpoint_works()
    {
        $response = $this->post('/analytics/generate-daily');
        
        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Daily metrics generation started'
        ]);
    }

    /** @test */
    public function export_analytics_endpoint_works()
    {
        $response = $this->get('/analytics/export?format=csv&type=metrics');
        
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    /** @test */
    public function unauthorized_users_cannot_access_business_intelligence_pages()
    {
        // Log out the user
        auth()->logout();
        
        $response = $this->get('/business-intelligence');
        
        $response->assertRedirect('/login');
    }

    /** @test */
    public function widget_creation_endpoint_works()
    {
        $widgetData = [
            'widget_type' => 'metric',
            'widget_title' => 'Test Widget',
            'width' => 1,
            'height' => 1,
            'column_position' => 0,
            'row_position' => 0,
            'color_theme' => 'primary'
        ];
        
        $response = $this->post('/dashboard/widgets', $widgetData);
        
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true
        ]);
    }

    /** @test */
    public function widget_deletion_endpoint_works()
    {
        // First create a widget
        $widgetData = [
            'widget_type' => 'metric',
            'widget_title' => 'Test Widget to Delete',
            'width' => 1,
            'height' => 1,
            'column_position' => 0,
            'row_position' => 0
        ];
        
        $createResponse = $this->post('/dashboard/widgets', $widgetData);
        $widgetId = $createResponse->json()['widget']['id'];
        
        // Then delete it
        $deleteResponse = $this->delete("/dashboard/widgets/{$widgetId}");
        
        $deleteResponse->assertStatus(200);
        $deleteResponse->assertJson([
            'success' => true
        ]);
    }

    /** @test */
    public function dashboard_layout_save_endpoint_works()
    {
        $layoutData = [
            'widgets' => [
                [
                    'id' => 1,
                    'column_position' => 0,
                    'row_position' => 0,
                    'width' => 1,
                    'height' => 1
                ]
            ]
        ];
        
        $response = $this->post('/dashboard/layout/save', $layoutData);
        
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true
        ]);
    }
}