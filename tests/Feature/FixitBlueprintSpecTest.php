<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Customer;
use App\Models\Vehicle;
use App\Models\Appointment;
use App\Models\ServicePricing;
use App\Models\JobOrder;
use App\Models\JobOrderItem;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;

/**
 * Fixit Blueprint Spec Tests (TDD)
 *
 * These tests define the acceptance criteria for the Fixit Remake.
 * Write them FIRST, then make each test pass one by one.
 *
 * Each test method maps to a Trello checklist item on the Hydrogen board.
 * Cards: P1-P7 in the "Andrew" list.
 *
 * Prerequisites for running:
 * - App\Models\Customer must use HasFactory (add trait + factory)
 * - App\Models\ServicePricing must use HasFactory (add trait + factory)
 * - Routes for /admin/job-orders/* must exist (adapt JobOrder routes)
 * - Routes for /admin/appointments/cancelled must exist
 * - Routes for /booking/* (public) must exist
 * - Routes for /admin/job-orders/{id}/print and /print/tech must exist
 * - barryvdh/laravel-dompdf must be installed
 * - MAIL_MAILER must NOT be 'log' in .env
 * - QUEUE_CONNECTION must NOT be 'sync'
 * - Crontab must have php artisan schedule:run
 */
class FixitBlueprintSpecTest extends TestCase
{

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create([
            'email' => 'admin@fixit.test',
            'password' => bcrypt('password'),
        ]);
        $this->actingAs($this->admin);
    }

    // ═══════════════════════════════════════════
    // P1: Foundation & Auth (6 tests)
    // ═══════════════════════════════════════════

    /** @test */
    public function admin_can_login_with_valid_credentials()
    {
        $this->post('/logout');
        $this->post('/login', [
            'email' => 'admin@fixit.test',
            'password' => 'password',
        ])->assertSessionHasNoErrors();
        $this->assertAuthenticated();
    }

    /** @test */
    public function guest_is_redirected_to_login_page()
    {
        $this->post('/logout');
        $this->get('/admin/dashboard')->assertRedirect('/login');
    }

    /** @test */
    public function admin_can_logout()
    {
        $this->post('/logout');
        $this->assertGuest();
    }

    /** @test */
    public function env_has_proper_mail_config()
    {
        $this->assertNotEquals('log', config('mail.default'),
            'MAIL_MAILER must not be "log" — emails need to actually send');
    }

    /** @test */
    public function queue_is_configured_properly()
    {
        $this->assertNotEquals('sync', config('queue.default'),
            'QUEUE_CONNECTION must not be "sync" — background jobs needed');
    }

    /** @test */
    public function dompdf_is_installed()
    {
        $this->assertTrue(class_exists(\Barryvdh\DomPDF\Facade\Pdf::class),
            'barryvdh/laravel-dompdf must be installed for JO PDF printing');
    }

    // ═══════════════════════════════════════════
    // P2: Public Booking - Guest Flow (7 tests)
    // ═══════════════════════════════════════════

    /** @test */
    public function public_booking_page_is_accessible()
    {
        $this->post('/logout');
        $this->get('/booking')->assertStatus(200);
    }

    /** @test */
    public function guest_can_submit_booking()
    {
        $this->post('/logout');
        $this->post('/booking', [
            'name' => 'Juan Dela Cruz',
            'email' => 'juan@example.com',
            'phone' => '09171234567',
            'make' => 'Toyota',
            'model' => 'Vios',
            'year' => 2020,
            'license_plate' => 'ABC 1234',
            'service_request' => 'Oil change',
            'appointment_date' => now()->addDays(1)->format('Y-m-d'),
            'appointment_time' => '10:00',
        ])->assertStatus(302);

        $this->assertDatabaseHas('appointments', [
            'email' => 'juan@example.com',
            'status' => 'scheduled',
        ]);
    }

    /** @test */
    public function guest_gets_unique_token_url_after_booking()
    {
        $this->markTestIncomplete('Needs booking token URL display on success page');
    }

    /** @test */
    public function guest_can_view_appointment_via_token()
    {
        $this->markTestIncomplete('Needs GET /booking/{token} route');
    }

    /** @test */
    public function guest_can_proceed_appointment_via_token()
    {
        $this->markTestIncomplete('Needs POST /booking/{token}/proceed route');
    }

    /** @test */
    public function guest_can_cancel_appointment_via_token()
    {
        $this->markTestIncomplete('Needs POST /booking/{token}/cancel route');
    }

    /** @test */
    public function guest_can_reschedule_appointment_via_token()
    {
        $this->markTestIncomplete('Needs POST /booking/{token}/reschedule route');
    }

    // ═══════════════════════════════════════════
    // P3: Admin Appointments (5 tests)
    // ═══════════════════════════════════════════

    /** @test */
    public function admin_can_view_appointments_list()
    {
        $this->get('/admin/appointments')->assertStatus(200);
    }

    /** @test */
    public function admin_can_filter_appointments_by_status()
    {
        $this->get('/admin/appointments?status=scheduled')->assertStatus(200);
    }

    /** @test */
    public function admin_can_create_appointment()
    {
        $customer = Customer::factory()->create();
        $vehicle = Vehicle::factory()->create(['customer_id' => $customer->id]);

        $this->post('/admin/appointments', [
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'appointment_date' => now()->addDays(1)->format('Y-m-d'),
            'appointment_time' => '09:00',
        ])->assertStatus(302);

        $this->assertDatabaseHas('appointments', [
            'customer_id' => $customer->id,
            'booking_source' => 'admin',
        ]);
    }

    /** @test */
    public function admin_can_view_cancelled_appointments()
    {
        $this->get('/admin/appointments/cancelled')->assertStatus(200);
    }

    /** @test */
    public function admin_can_delete_appointment()
    {
        $appointment = Appointment::factory()->create();
        $this->delete('/admin/appointments/' . $appointment->id)->assertStatus(302);
    }

    // ═══════════════════════════════════════════
    // P4: Customer & Vehicle (4 tests)
    // ═══════════════════════════════════════════

    /** @test */
    public function admin_can_create_customer()
    {
        $this->post('/admin/customers', [
            'first_name' => 'Juan',
            'last_name' => 'Dela Cruz',
            'email' => 'juan.dc@example.com',
            'phone' => '09170000001',
        ])->assertStatus(302);

        $this->assertDatabaseHas('customers', ['email' => 'juan.dc@example.com']);
    }

    /** @test */
    public function admin_can_edit_customer()
    {
        $customer = Customer::factory()->create();
        $this->put('/admin/customers/' . $customer->id, [
            'first_name' => 'Updated',
            'last_name' => 'Name',
            'email' => $customer->email,
            'phone' => $customer->phone,
        ])->assertStatus(302);

        $this->assertDatabaseHas('customers', ['first_name' => 'Updated']);
    }

    /** @test */
    public function admin_can_create_vehicle_for_customer()
    {
        $customer = Customer::factory()->create();
        $this->post('/admin/vehicles', [
            'customer_id' => $customer->id,
            'make' => 'Toyota',
            'model' => 'Vios',
            'year' => 2020,
            'license_plate' => 'ABC 1234',
        ])->assertStatus(302);

        $this->assertDatabaseHas('vehicles', ['license_plate' => 'ABC 1234']);
    }

    /** @test */
    public function proceeded_appointment_auto_creates_customer_record()
    {
        $this->markTestIncomplete('Needs auto-populate logic from appointment -> customer');
    }

    // ═══════════════════════════════════════════
    // P5: Service Catalog (2 tests)
    // ═══════════════════════════════════════════

    /** @test */
    public function admin_can_create_service_with_pricing()
    {
        $this->post('/admin/service-pricings', [
            'service_name' => 'Oil Change',
            'price' => 500.00,
            'is_active' => true,
        ])->assertStatus(302);
    }

    /** @test */
    public function admin_can_toggle_service_active_status()
    {
        $this->markTestIncomplete('Needs is_active toggle on service edit form');
    }

    // ═══════════════════════════════════════════
    // P6: Job Orders (4 tests)
    // ═══════════════════════════════════════════

    /** @test */
    public function admin_can_create_job_order()
    {
        $customer = Customer::factory()->create();
        $vehicle = Vehicle::factory()->create(['customer_id' => $customer->id]);

        $this->post('/admin/job-orders', [
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'assigned_technician' => 'Juan Tech',
            'services' => [['name' => 'Oil Change', 'price' => 500, 'quantity' => 1]],
        ])->assertStatus(302);
    }

    /** @test */
    public function job_order_has_unique_number()
    {
        $this->markTestIncomplete('Needs auto-generated JO number (e.g. JO-2026-0001)');
    }

    /** @test */
    public function admin_can_update_job_order_status()
    {
        $this->markTestIncomplete('Needs status transitions: pending -> in_progress -> completed');
    }

    /** @test */
    public function guest_cannot_access_job_orders()
    {
        $this->post('/logout');
        $this->get('/admin/job-orders')->assertRedirect('/login');
    }

    // ═══════════════════════════════════════════
    // P7: JO Printing (3 tests)
    // ═══════════════════════════════════════════

    /** @test */
    public function job_order_print_with_pricing_shows_prices()
    {
        $this->markTestIncomplete('Needs GET /admin/job-orders/{id}/print with pricing');
    }

    /** @test */
    public function job_order_print_tech_version_hides_pricing()
    {
        $this->markTestIncomplete('Needs GET /admin/job-orders/{id}/print/tech without prices');
    }

    /** @test */
    public function tech_version_hides_customer_personal_info()
    {
        $this->markTestIncomplete('Tech version must hide name, address, phone, email');
    }
}
