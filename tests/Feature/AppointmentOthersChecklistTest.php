<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Customer;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * TDD (red -> green) for "Fixit Adjustment - Create Appointment" card,
 * "Others" checklist (3 items):
 *
 * 1. Team Assignment section must be REMOVED from the admin create page.
 * 2. Additional Notes section must be MAINTAINED (kept as-is).
 * 3. Appointment cannot be created without required info, EXCEPT:
 *    plate number, contact number, service description, additional notes.
 *    (So required: customer/client name, car brand, car model, car year,
 *     appointment date, appointment time, service type.)
 */
class AppointmentOthersChecklistTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'super_admin']);
    }

    // ---------------------------------------------------------------
    // ITEM 1: Team Assignment section removed from create page
    // ---------------------------------------------------------------

    /** @test */
    public function create_page_has_no_team_assignment_section()
    {
        $this->actingAs($this->admin)
            ->get('/appointments/create')
            ->assertOk()
            ->assertDontSee('id="team"', false)
            ->assertDontSee('Team Assignment', false)
            ->assertDontSee('data-section="team"', false);
    }

    /** @test */
    public function create_page_has_no_team_nav_pill()
    {
        $this->actingAs($this->admin)
            ->get('/appointments/create')
            ->assertOk()
            ->assertDontSee('onclick="scrollToSection(\'team\')"', false);
    }

    /** @test */
    public function create_page_has_no_primary_technician_dropdown()
    {
        $this->actingAs($this->admin)
            ->get('/appointments/create')
            ->assertOk()
            ->assertDontSee('name="assigned_to"', false);
    }

    // ---------------------------------------------------------------
    // ITEM 2: Additional Notes section maintained
    // ---------------------------------------------------------------

    /** @test */
    public function create_page_keeps_additional_notes_section()
    {
        $this->actingAs($this->admin)
            ->get('/appointments/create')
            ->assertOk()
            ->assertSee('id="notes"', false)
            ->assertSee('Additional Notes', false)
            ->assertSee('name="notes"', false);
    }

    // ---------------------------------------------------------------
    // ITEM 3: Contact number is now optional (label + validation)
    // ---------------------------------------------------------------

    /** @test */
    public function create_page_contact_number_label_is_not_marked_required()
    {
        $this->actingAs($this->admin)
            ->get('/appointments/create')
            ->assertOk()
            ->assertDontSee('<label for="contact_no" class="form-label field-required">Contact No.</label>', false);
    }

    /** @test */
    public function manual_add_without_contact_number_succeeds()
    {
        $this->actingAs($this->admin)
            ->post('/appointments', [
                'client_name' => 'Juan Dela Cruz',
                'vehicle_brand' => 'Toyota',
                'vehicle_model' => 'Vios',
                'vehicle_year' => 2021,
                'appointment_date' => now()->addDays(3)->toDateString(),
                'appointment_time' => '14:00',
                'service_type' => ['preventive_maintenance'],
            ])
            ->assertRedirect(route('appointments.index'));

        $customer = Customer::where('first_name', 'Juan')->where('last_name', 'Dela Cruz')->first();
        $this->assertNotNull($customer, 'customer was not created from manual add without contact number');
        $this->assertNull($customer->phone);
        $this->assertNotNull($customer->email, 'guest customer must still get an email fallback');

        $appointment = Appointment::where('customer_id', $customer->id)->first();
        $this->assertNotNull($appointment, 'appointment was not created without contact number');
    }

    /** @test */
    public function manual_add_without_plate_number_description_and_notes_succeeds()
    {
        $this->actingAs($this->admin)
            ->post('/appointments', [
                'client_name' => 'Maria Santos',
                'contact_no' => '09185551234',
                'vehicle_brand' => 'Honda',
                'vehicle_model' => 'Civic',
                'vehicle_year' => 2019,
                'appointment_date' => now()->addDays(4)->toDateString(),
                'appointment_time' => '09:30',
                'service_type' => ['basic_tune_up'],
            ])
            ->assertRedirect(route('appointments.index'));

        $customer = Customer::where('phone', '09185551234')->first();
        $this->assertNotNull($customer);
        $appointment = Appointment::where('customer_id', $customer->id)->first();
        $this->assertNotNull($appointment);
        $this->assertNull($appointment->service_request, 'description should be optional');
        $this->assertNull($appointment->customer_notes, 'notes should be optional');
    }

    /** @test */
    public function manual_add_guest_email_fallback_is_valid_without_contact_number()
    {
        $this->actingAs($this->admin)
            ->post('/appointments', [
                'client_name' => 'Pedro Santos',
                'vehicle_brand' => 'Suzuki',
                'vehicle_model' => 'Swift',
                'vehicle_year' => 2020,
                'appointment_date' => now()->addDays(2)->toDateString(),
                'appointment_time' => '10:00',
                'service_type' => ['egr_service'],
            ])
            ->assertRedirect(route('appointments.index'));

        $customer = Customer::where('first_name', 'Pedro')->where('last_name', 'Santos')->first();
        $this->assertNotNull($customer);
        $this->assertStringEndsWith('@guest.local', $customer->email);
        $this->assertStringNotContainsString('guest_@guest.local', $customer->email, 'email fallback must not be broken when contact number is empty');
    }

    // ---------------------------------------------------------------
    // ITEM 3: Required fields still enforced
    // ---------------------------------------------------------------

    /** @test */
    public function manual_add_requires_client_name()
    {
        $this->actingAs($this->admin)
            ->post('/appointments', [
                'vehicle_brand' => 'Toyota',
                'vehicle_model' => 'Vios',
                'vehicle_year' => 2021,
                'appointment_date' => now()->addDays(3)->toDateString(),
                'appointment_time' => '14:00',
                'service_type' => ['preventive_maintenance'],
            ])
            ->assertSessionHasErrors('client_name');
    }

    /** @test */
    public function manual_add_requires_vehicle_brand_model_and_year()
    {
        $this->actingAs($this->admin)
            ->post('/appointments', [
                'client_name' => 'Juan Dela Cruz',
                'appointment_date' => now()->addDays(3)->toDateString(),
                'appointment_time' => '14:00',
                'service_type' => ['preventive_maintenance'],
            ])
            ->assertSessionHasErrors('vehicle_brand')
            ->assertSessionHasErrors('vehicle_model')
            ->assertSessionHasErrors('vehicle_year');
    }

    /** @test */
    public function manual_add_requires_date_time_and_service_type()
    {
        $this->actingAs($this->admin)
            ->post('/appointments', [
                'client_name' => 'Juan Dela Cruz',
                'vehicle_brand' => 'Toyota',
                'vehicle_model' => 'Vios',
                'vehicle_year' => 2021,
            ])
            ->assertSessionHasErrors('appointment_date')
            ->assertSessionHasErrors('appointment_time')
            ->assertSessionHasErrors('service_type');
    }

    /** @test */
    public function manual_add_requires_all_required_fields_together()
    {
        $this->actingAs($this->admin)
            ->post('/appointments', [])
            ->assertSessionHasErrors('client_name')
            ->assertSessionHasErrors('vehicle_brand')
            ->assertSessionHasErrors('vehicle_model')
            ->assertSessionHasErrors('vehicle_year')
            ->assertSessionHasErrors('appointment_date')
            ->assertSessionHasErrors('appointment_time')
            ->assertSessionHasErrors('service_type')
            ->assertSessionDoesntHaveErrors('contact_no')
            ->assertSessionDoesntHaveErrors('plate_number')
            ->assertSessionDoesntHaveErrors('description')
            ->assertSessionDoesntHaveErrors('notes');
    }
}
