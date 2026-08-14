<?php

namespace Tests\Feature;

use App\Models\ContactMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Contact Us card — "Viewable in Admin UI (lead list/filter) on app.fixitautoservices.com"
 * + "NEW admin sidebar: two top-level tabs - Inbox + Appointments"
 * + "Contact Us messages appear under Inbox tab (not Appointments)".
 */
class ContactInboxAdminTest extends TestCase
{
    use RefreshDatabase;

    private function adminUser(): User
    {
        return User::create([
            'name'     => 'Admin',
            'email'    => 'admin@fixitautoservices.com',
            'password' => bcrypt('password'),
        ]);
    }

    public function test_admin_sidebar_has_inbox_and_appointments_tabs(): void
    {
        $sidebar = \Illuminate\Support\Facades\File::get(resource_path('views/partials/sidebar.blade.php'));

        // Two top-level tabs: Inbox + Appointments.
        $this->assertStringContainsString('Inbox', $sidebar);
        $this->assertStringContainsString('>Appointments<', $sidebar);
        // Contact messages route referenced in the sidebar.
        $this->assertStringContainsString('inbox', strtolower($sidebar));
    }

    public function test_admin_can_view_contact_messages_on_inbox_page(): void
    {
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);
        $user = $this->adminUser();

        ContactMessage::create([
            'name'    => 'Cyd Dalupan',
            'email'   => 'cyd@example.com',
            'phone'   => '09175550001',
            'subject' => 'Quotation request',
            'message' => 'Please send me a quote.',
        ]);

        $response = $this->actingAs($user)->get('/inbox');
        $response->assertStatus(200);
        $response->assertSee('Cyd Dalupan');
        $response->assertSee('Quotation request');
        $response->assertSee('Please send me a quote.');
    }
}
