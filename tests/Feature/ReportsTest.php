<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Customer;
use App\Models\Appointment;
use App\Models\WorkOrder;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Vehicle;
use App\Models\ServiceRecord;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ReportsTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a test user
        $this->user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);
        
        // Create test data
        $this->createTestData();
    }

    /**
     * Create test data for reports.
     */
    private function createTestData(): void
    {
        // Create customers
        $customer1 = Customer::create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'phone' => '123-456-7890',
            'created_at' => Carbon::today()->subDays(5),
        ]);

        $customer2 = Customer::create([
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'jane@example.com',
            'phone' => '987-654-3210',
            'created_at' => Carbon::today(),
        ]);

        // Create vehicles
        $vehicle1 = Vehicle::create([
            'customer_id' => $customer1->id,
            'make' => 'Toyota',
            'model' => 'Camry',
            'year' => 2020,
            'license_plate' => 'ABC123',
        ]);

        $vehicle2 = Vehicle::create([
            'customer_id' => $customer2->id,
            'make' => 'Honda',
            'model' => 'Civic',
            'year' => 2021,
            'license_plate' => 'XYZ789',
        ]);

        // Create appointments for today
        Appointment::create([
            'customer_id' => $customer1->id,
            'vehicle_id' => $vehicle1->id,
            'scheduled_date' => Carbon::today()->setTime(10, 0),
            'status' => 'scheduled',
        ]);

        Appointment::create([
            'customer_id' => $customer2->id,
            'vehicle_id' => $vehicle2->id,
            'scheduled_date' => Carbon::today()->setTime(14, 0),
            'status' => 'completed',
        ]);

        // Create work orders for today
        WorkOrder::create([
            'customer_id' => $customer1->id,
            'vehicle_id' => $vehicle1->id,
            'service_type' => 'oil_change',
            'status' => 'completed',
            'created_at' => Carbon::today(),
        ]);

        WorkOrder::create([
            'customer_id' => $customer2->id,
            'vehicle_id' => $vehicle2->id,
            'service_type' => 'brake_service',
            'status' => 'in_progress',
            'created_at' => Carbon::today(),
        ]);

        // Create invoices for today
        $invoice1 = Invoice::create([
            'customer_id' => $customer1->id,
            'invoice_number' => 'INV-001',
            'invoice_date' => Carbon::today(),
            'total_amount' => 150.00,
            'status' => 'paid',
        ]);

        $invoice2 = Invoice::create([
            'customer_id' => $customer2->id,
            'invoice_number' => 'INV-002',
            'invoice_date' => Carbon::today(),
            'total_amount' => 300.00,
            'status' => 'partial',
        ]);

        // Create payments for today
        Payment::create([
            'invoice_id' => $invoice1->id,
            'customer_id' => $customer1->id,
            'amount' => 150.00,
            'payment_date' => Carbon::today(),
            'payment_method' => 'credit_card',
        ]);

        Payment::create([
            'invoice_id' => $invoice2->id,
            'customer_id' => $customer2->id,
            'amount' => 100.00,
            'payment_date' => Carbon::today(),
            'payment_method' => 'cash',
        ]);

        // Create service records
        ServiceRecord::create([
            'vehicle_id' => $vehicle1->id,
            'service_date' => Carbon::today()->subMonth(),
            'service_type' => 'oil_change',
            'description' => 'Regular oil change',
            'cost' => 75.00,
            'status' => 'completed',
        ]);

        ServiceRecord::create([
            'vehicle_id' => $vehicle1->id,
            'service_date' => Carbon::today(),
            'service_type' => 'brake_service',
            'description' => 'Brake pad replacement',
            'cost' => 250.00,
            'status' => 'completed',
        ]);

        ServiceRecord::create([
            'vehicle_id' => $vehicle2->id,
            'service_date' => Carbon::today(),
            'service_type' => 'diagnostic',
            'description' => 'Engine diagnostic',
            'cost' => 120.00,
            'status' => 'in_progress',
        ]);
    }

    /**
     * Test reports dashboard access.
     */
    public function test_reports_dashboard_access(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('reports.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Reports Dashboard');
        $response->assertSee('Daily Activity Report');
        $response->assertSee('Monthly Performance');
        $response->assertSee('Customer History');
    }

    /**
     * Test daily activity report access.
     */
    public function test_daily_activity_report_access(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('reports.daily-activity', [
                'date' => Carbon::today()->format('Y-m-d')
            ]));

        $response->assertStatus(200);
        $response->assertSee('Daily Activity Report');
        $response->assertSee('Appointments');
        $response->assertSee('Work Orders');
        $response->assertSee('Revenue');
        $response->assertSee('New Customers');
    }

    /**
     * Test daily activity report with filters.
     */
    public function test_daily_activity_report_with_filters(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('reports.daily-activity', [
                'date' => Carbon::today()->format('Y-m-d'),
                'service_type' => 'oil_change'
            ]));

        $response->assertStatus(200);
        $response->assertSee('Daily Activity Report');
    }

    /**
     * Test monthly performance report access.
     */
    public function test_monthly_performance_report_access(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('reports.monthly-performance', [
                'date' => Carbon::today()->format('Y-m')
            ]));

        $response->assertStatus(200);
        $response->assertSee('Monthly Performance Report');
        $response->assertSee('Performance Summary');
        $response->assertSee('Revenue');
        $response->assertSee('Profit');
        $response->assertSee('Jobs Completed');
    }

    /**
     * Test customer history report access.
     */
    public function test_customer_history_report_access(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('reports.customer-history'));

        $response->assertStatus(200);
        $response->assertSee('Customer History Report');
        $response->assertSee('Total Customers');
        $response->assertSee('Total Services');
        $response->assertSee('Total Revenue');
    }

    /**
     * Test customer history report with specific customer.
     */
    public function test_customer_history_report_with_specific_customer(): void
    {
        $customer = Customer::first();
        
        $response = $this->actingAs($this->user)
            ->get(route('reports.customer-history', [
                'customer_id' => $customer->id
            ]));

        $response->assertStatus(200);
        $response->assertSee('Customer History Report');
        $response->assertSee($customer->first_name);
        $response->assertSee($customer->last_name);
    }

    /**
     * Test customer history report with date filters.
     */
    public function test_customer_history_report_with_date_filters(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('reports.customer-history', [
                'date_from' => Carbon::today()->subMonth()->format('Y-m-d'),
                'date_to' => Carbon::today()->format('Y-m-d')
            ]));

        $response->assertStatus(200);
        $response->assertSee('Customer History Report');
    }

    /**
     * Test report export endpoint.
     */
    public function test_report_export_endpoint(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('reports.export'), [
                'report_type' => 'daily_activity',
                'format' => 'pdf',
                'date' => Carbon::today()->format('Y-m-d'),
            ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'message',
            'export' => [
                'format',
                'generated_at',
                'data'
            ]
        ]);
    }

    /**
     * Test report types endpoint.
     */
    public function test_report_types_endpoint(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('reports.types'));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'report_types' => [
                '*' => ['id', 'name', 'description', 'icon']
            ]
        ]);
    }

    /**
     * Test report preview endpoint.
     */
    public function test_report_preview_endpoint(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('reports.preview'), [
                'report_type' => 'daily_activity',
                'date' => Carbon::today()->format('Y-m-d'),
            ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'preview',
            'report_type'
        ]);
    }

    /**
     * Test unauthorized access to reports.
     */
    public function test_unauthorized_access_to_reports(): void
    {
        // Create a non-admin user
        $user = User::factory()->create([
            'email' => 'user@example.com',
            'password' => bcrypt('password'),
            'role' => 'customer',
        ]);

        $response = $this->actingAs($user)
            ->get(route('reports.dashboard'));

        $response->assertStatus(403); // Should be forbidden for non-admin users
    }

    /**
     * Test report settings endpoint.
     */
    public function test_report_settings_endpoint(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('reports.settings.get', ['report_type' => 'daily_activity']));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'columns',
            'filters',
            'format'
        ]);
    }

    /**
     * Test navigation includes reports menu.
     */
    public function test_navigation_includes_reports_menu(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Reports'); // Should see Reports in navigation
    }

    /**
     * Test all report routes are accessible.
     */
    public function test_all_report_routes_are_accessible(): void
    {
        $routes = [
            route('reports.dashboard'),
            route('reports.daily-activity'),
            route('reports.monthly-performance'),
            route('reports.customer-history'),
        ];

        foreach ($routes as $route) {
            $response = $this->actingAs($this->user)->get($route);
            $response->assertStatus(200);
        }
    }

    /**
     * Test report data accuracy for daily activity.
     */
    public function test_daily_activity_report_data_accuracy(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('reports.daily-activity', [
                'date' => Carbon::today()->format('Y-m-d')
            ]));

        $response->assertStatus(200);
        
        // Check that we have the expected data
        $response->assertSee('2'); // 2 appointments
        $response->assertSee('2'); // 2 work orders
        $response->assertSee('450.00'); // $450 total revenue
        $response->assertSee('1'); // 1 new customer today
    }

    /**
     * Test monthly performance report data accuracy.
     */
    public function test_monthly_performance_report_data_accuracy(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('reports.monthly-performance', [
                'date' => Carbon::today()->format('Y-m')
            ]));

        $response->assertStatus(200);
        
        // Check that we have the expected data
        $response->assertSee('450.00'); // $450 revenue this month
        $response->assertSee('2'); // 2 customers
        $response->assertSee('3'); // 3 service records
    }
}