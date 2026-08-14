<?php

namespace Tests\Feature;

use App\Models\ContactMessage;
use App\Models\Customer;
use App\Models\JobOrder;
use App\Models\JobOrderItem;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Hydrogen (#hydrogen) — Cyd requests:
 *  1. Print button on /job-orders list + single request view (technician prints work order).
 *  2. /inbox lists ALL Contact Us submissions from fixitautoservices.com/contact.html —
 *     data saved in app DB (email still sent), proper notification in admin navbar,
 *     see all unread contact messages.
 *
 * RED -> GREEN (red first: print view missing / static form not persisted / no navbar notification).
 */
class JobOrderPrintAndContactInboxTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['email' => 'admin@fixit.test', 'role' => 'super_admin']);
    }

    // ============================================================
    // FEATURE 1: JOB ORDER PRINT (technician prints work order)
    // ============================================================

    private function makeJobOrder(): JobOrder
    {
        $customer = Customer::factory()->create([
            'first_name' => 'Dante',
            'last_name'  => 'Reyes',
            'email'      => 'dante@example.com',
            'phone'      => '09171234567',
        ]);
        $vehicle = Vehicle::factory()->create([
            'customer_id'   => $customer->id,
            'license_plate' => 'XYZ-1234',
        ]);

        $jobOrder = JobOrder::factory()->create([
            'customer_id'      => $customer->id,
            'vehicle_id'       => $vehicle->id,
            'job_order_status' => 'pending',
        ]);

        JobOrderItem::create([
            'job_order_id' => $jobOrder->id,
            'item_type'    => 'service',
            'description'  => 'Aircon recharge',
            'quantity'     => 1,
            'unit_cost'    => 1500,
            'total_cost'   => 1500,
            'final_amount' => 1500,
        ]);

        return $jobOrder->load('customer', 'vehicle', 'technician', 'items');
    }

    public function test_job_order_print_route_renders_printable_view(): void
    {
        $jobOrder = $this->makeJobOrder();

        // RED: view('job_orders.print') does not exist -> 500. GREEN: renders 200.
        $response = $this->actingAs($this->admin)
            ->get(route('job-orders.print', $jobOrder));

        $response->assertOk();
        $response->assertSee($jobOrder->job_order_number, false);
        $response->assertSee('Dante Reyes');
        $response->assertSee('XYZ-1234');
        $response->assertSee('Aircon recharge');
    }

    public function test_job_orders_index_has_print_button(): void
    {
        $view = \Illuminate\Support\Facades\File::get(resource_path('views/job_orders/index.blade.php'));

        $this->assertStringContainsString("route('job-orders.print', \$jobOrder)", $view);
    }

    public function test_job_orders_show_has_print_button(): void
    {
        $view = \Illuminate\Support\Facades\File::get(resource_path('views/job_orders/show.blade.php'));

        $this->assertStringContainsString("route('job-orders.print', \$jobOrder)", $view);
    }

    // ============================================================
    // FEATURE 2: INBOX — CONTACT US SUBMISSIONS PERSISTED + NOTIFIED
    // ============================================================

    public function test_static_contact_script_forwards_to_app_db(): void
    {
        // RED: /var/www/fixit-static/contact-send.php only emails (Brevo), never saves.
        // GREEN: it must also POST the submission to the app API so it lands in /inbox.
        $script = \Illuminate\Support\Facades\File::get('/var/www/fixit-static/contact-send.php');

        $this->assertStringContainsString('app.fixitautoservices.com/api/contact/save', $script);
    }

    public function test_contact_submission_creates_staff_navbar_notification(): void
    {
        Notification::fake();

        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);

        $this->postJson('/api/contact/save', [
            'name'    => 'Cyd Dalupan',
            'email'   => 'cyd@example.com',
            'phone'   => '09175550001',
            'subject' => 'Quotation request',
            'message' => 'Please send me a quote.',
        ])->assertOk();

        // RED: no NewContactMessageNotification exists -> nothing sent.
        Notification::assertSentTo($this->admin, \App\Notifications\NewContactMessageNotification::class);
    }

    public function test_notifications_api_returns_contact_message_with_inbox_link(): void
    {
        $message = ContactMessage::create([
            'name'    => 'Jane Doe',
            'email'   => 'jane@example.com',
            'subject' => 'Inquiry',
            'message' => 'Hello',
        ]);

        $this->admin->notify(new \App\Notifications\NewContactMessageNotification($message));

        $this->actingAs($this->admin)->getJson('/api/notifications')->assertOk()
            ->assertJsonPath('unread_count', 1)
            ->assertJsonPath('items.0.link', '/inbox');
    }

    public function test_contact_save_with_skip_email_persists_without_duplicate_email(): void
    {
        // The static script already emails via Brevo; when it forwards to the app
        // API it must NOT trigger a second admin email, but must still save + notify.
        Mail::fake();

        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);

        $this->postJson('/api/contact/save', [
            'name'       => 'Cyd Dalupan',
            'email'      => 'cyd@example.com',
            'subject'    => 'Quotation request',
            'message'    => 'Please send me a quote.',
            'skip_email' => true,
        ])->assertOk();

        $this->assertDatabaseHas('contact_messages', ['name' => 'Cyd Dalupan']);
        Mail::assertNothingSent();
        $this->assertSame(1, $this->admin->notifications()->count());
    }
}
