<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Customer;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * TDD (red -> green) for the three reported regressions on the
 * appointment EDIT page (appointments/{id}/edit):
 *
 * 1. Vehicle dropdown must auto-select the appointment's vehicle.
 * 2. The primary Technician dropdown must print technician names
 *    (User model has a single `name` column, not first_name/last_name).
 * 3. Admin booking email must go to BOTH admin recipients
 *    (cydmdalupan@gmail.com + andrewacecontreras@gmail.com), like contact form.
 */
class AppointmentEditPageFixesTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'super_admin']);
    }

    private function makeAppointment(array $overrides = []): Appointment
    {
        $customer = Customer::factory()->create();
        $vehicle = Vehicle::factory()->create(['customer_id' => $customer->id]);

        return Appointment::factory()->create(array_merge([
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
        ], $overrides));
    }

    /** @test */
    public function edit_page_auto_selects_the_appointments_vehicle()
    {
        $appointment = $this->makeAppointment();

        $this->actingAs($this->admin)
            ->get("/appointments/{$appointment->id}/edit")
            ->assertOk()
            ->assertSee('<option value="' . $appointment->vehicle_id . '" selected', false);
    }

    /** @test */
    public function edit_page_prints_technician_name_in_primary_technician_dropdown()
    {
        $technician = User::factory()->create([
            'role' => 'technician',
            'name' => 'Juan Dela Cruz',
            'is_active' => true,
        ]);
        $appointment = $this->makeAppointment();

        $html = $this->actingAs($this->admin)
            ->get("/appointments/{$appointment->id}/edit")
            ->assertOk()
            ->getContent();

        preg_match('/<select[^>]*id="assigned_technician_id".*?<\/select>/s', $html, $m);
        $this->assertNotEmpty($m, 'primary technician select not found');
        $this->assertStringContainsString('Juan Dela Cruz', $m[0]);
    }

    /** @test */
    public function edit_page_prints_advisor_name_in_service_advisor_dropdown()
    {
        $advisor = User::factory()->create([
            'role' => 'service_advisor',
            'name' => 'Maria Santos',
            'is_active' => true,
        ]);
        $appointment = $this->makeAppointment();

        $html = $this->actingAs($this->admin)
            ->get("/appointments/{$appointment->id}/edit")
            ->assertOk()
            ->getContent();

        preg_match('/<select[^>]*id="service_advisor_id".*?<\/select>/s', $html, $m);
        $this->assertNotEmpty($m, 'service advisor select not found');
        $this->assertStringContainsString('Maria Santos', $m[0]);
    }

    /** @test */
    public function admin_recipients_config_includes_both_cyd_and_andrew()
    {
        $recipients = config('mail.admin_recipients', []);

        $this->assertContains('cydmdalupan@gmail.com', $recipients);
        $this->assertContains('andrewacecontreras@gmail.com', $recipients);
    }

    /** @test */
    public function guest_booking_emails_both_admin_recipients()
    {
        \Illuminate\Support\Facades\Mail::fake();
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);

        $this->postJson('/api/booking/customer-booking', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'phone' => '09181234567',
            'vehicle_make' => 'Honda',
            'vehicle_model' => 'Civic',
            'vehicle_year' => 2019,
            'vehicle_plate' => 'XYZ-999',
            'service_type' => 'aircon_service',
            'appointment_date' => now()->addDays(3)->toDateString(),
            'appointment_time' => '11:00',
            'notes' => 'AC not cooling',
        ])->assertOk();

        \Illuminate\Support\Facades\Mail::assertSent(
            \App\Mail\NewBookingAdminNotification::class,
            fn ($mail) => $mail->hasTo('cydmdalupan@gmail.com')
        );
        \Illuminate\Support\Facades\Mail::assertSent(
            \App\Mail\NewBookingAdminNotification::class,
            fn ($mail) => $mail->hasTo('andrewacecontreras@gmail.com')
        );
    }
}
