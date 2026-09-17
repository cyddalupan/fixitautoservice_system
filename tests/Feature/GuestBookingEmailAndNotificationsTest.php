<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\BookingToken;
use App\Models\Customer;
use App\Models\User;
use App\Models\Vehicle;
use App\Notifications\NewBookingNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * TDD: website/guest booking must (1) email the CUSTOMER a confirmation
 * (not just the admin), and (2) create an in-app staff notification so the
 * bell shows real data. Both were missing on the guest path.
 */
class GuestBookingEmailAndNotificationsTest extends TestCase
{
    use RefreshDatabase;

    private function guestBookingPayload(): array
    {
        return [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'phone' => '09181234567',
            'vehicle_make' => 'Honda',
            'vehicle_model' => 'Civic',
            'vehicle_year' => 2019,
            'vehicle_plate' => 'XYZ-999',
            'service_type' => 'aircon_repair',
            'appointment_date' => now()->addDays(3)->toDateString(),
            'appointment_time' => '11:00',
            'notes' => 'AC not cooling',
        ];
    }

    public function test_guest_booking_sends_confirmation_email_to_customer(): void
    {
        Mail::fake();
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);

        $this->postJson('/api/booking/customer-booking', $this->guestBookingPayload())->assertOk();

        Mail::assertSent(\App\Mail\BookingConfirmation::class, function ($mail) {
            return $mail->hasTo('jane@example.com');
        });
    }

    public function test_guest_booking_creates_staff_notification(): void
    {
        Notification::fake();
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);

        $admin = User::factory()->create(['role' => 'super_admin']);

        $this->postJson('/api/booking/customer-booking', $this->guestBookingPayload())->assertOk();

        Notification::assertSentTo($admin, NewBookingNotification::class);
    }

    public function test_notification_index_returns_unread_count_and_items(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin']);

        $this->actingAs($admin)->getJson('/api/notifications')->assertOk()
            ->assertJsonStructure(['unread_count', 'items']);

        $admin->notify(new NewBookingNotification(
            new Appointment(['appointment_number' => 'APT-TEST-1', 'appointment_type' => 'oil_change']),
            new Customer(['first_name' => 'Jane', 'last_name' => 'Doe'])
        ));

        $this->actingAs($admin)->getJson('/api/notifications')->assertOk()
            ->assertJsonPath('unread_count', 1)
            ->assertJsonCount(1, 'items');
    }

    public function test_mark_all_read_clears_unread_count(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin']);
        $admin->notify(new NewBookingNotification(
            new Appointment(['appointment_number' => 'APT-TEST-2', 'appointment_type' => 'oil_change']),
            new Customer(['first_name' => 'Jane', 'last_name' => 'Doe'])
        ));

        $this->actingAs($admin)->postJson('/api/notifications/read-all')->assertOk();

        $this->assertSame(0, $admin->unreadNotifications()->count());
    }
}
