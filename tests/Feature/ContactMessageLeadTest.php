<?php

namespace Tests\Feature;

use App\Models\ContactMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * FixIt Contact Us card — "Contact Us form -> save to app database (LEAD)".
 *
 * Blueprint lead requirement (Cyd, 06 Aug 2026):
 * - Contact Us is a DISTINCT lead type from Appointments (separate table/model).
 * - Contact submissions are saved to the app DB (contact_messages / ContactMessage).
 * - Fields: name, email, phone, subject, message + timestamp.
 * - Server-side sanitization stays intact (validation + trimming).
 */
class ContactMessageLeadTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name'    => 'Cyd Dalupan',
            'email'   => 'cyd@example.com',
            'phone'   => '09175550001',
            'subject' => 'Quotation request',
            'message' => 'Please send me a quote for an aircon repair.',
        ], $overrides);
    }

    public function test_contact_messages_table_and_model_exist(): void
    {
        // Blueprint lead: separate contact_messages table + ContactMessage model.
        $migrationExists = collect(\Illuminate\Support\Facades\File::files(database_path('migrations')))
            ->filter(fn ($f) => str_contains($f->getFilename(), 'contact_messages'))
            ->isNotEmpty();

        $this->assertTrue($migrationExists, 'contact_messages migration must exist');
        $this->assertTrue(class_exists(\App\Models\ContactMessage::class), 'ContactMessage model must exist');
    }

    public function test_contact_post_persists_all_fields_to_db(): void
    {
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);

        $response = $this->postJson('/api/contact/save', $this->payload());
        $response->assertOk();
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('contact_messages', [
            'name'    => 'Cyd Dalupan',
            'email'   => 'cyd@example.com',
            'phone'   => '09175550001',
            'subject' => 'Quotation request',
            'message' => 'Please send me a quote for an aircon repair.',
        ]);

        $this->assertNotNull(ContactMessage::first()->created_at);
    }

    public function test_distinct_lead_type_from_appointments(): void
    {
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);

        $this->postJson('/api/contact/save', $this->payload())->assertOk();

        // Distinct table: exists and is separate from appointments table.
        $this->assertTrue(\Illuminate\Support\Facades\Schema::hasTable('contact_messages'));
        $this->assertFalse(\Schema::hasColumn('contact_messages', 'appointment_status'));
        $this->assertSame(0, \App\Models\Appointment::count());
        $this->assertSame(1, ContactMessage::count());
    }

    public function test_server_side_sanitization_trims_and_validates(): void
    {
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);

        // Missing required fields => 422 validation.
        $response = $this->postJson('/api/contact/save', [
            'name'    => '',
            'email'   => 'not-an-email',
            'message' => '',
        ]);
        $response->assertStatus(422);

        // Trimming: leading/trailing whitespace stripped server-side.
        $this->postJson('/api/contact/save', $this->payload(['name' => '  Cyd Dalupan  ']))->assertOk();
        $this->assertDatabaseHas('contact_messages', ['name' => 'Cyd Dalupan', 'subject' => 'Quotation request']);
    }
}
