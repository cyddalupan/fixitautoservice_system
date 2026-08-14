<?php

namespace Tests\Feature;

use App\Models\ContactMessage;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * FixIt Contact Us card — "Admin notified on new contact submission (email)"
 * + "Brevo email + admin notification works for Contact Us leads"
 * + "reCAPTCHA v3 + honeypot spam protection preserved".
 *
 * Blueprint lead requirement (Cyd, 06 Aug 2026):
 * - Contact submissions are saved to the app DB.
 * - Admin is notified (email) on each new contact submission (same Brevo flow
 *   as the appointment new-booking notification).
 * - Server-side spam protection (honeypot) stays intact.
 */
class ContactAdminNotificationTest extends TestCase
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

    /**
     * Admin is notified (email) when a new contact submission arrives.
     * Uses Mail::fake() (array transport) so no SMTP server is needed —
     * verifies sendability of the notification, mirroring the appointment
     * new-booking admin notification test.
     */
    public function test_contact_submission_sends_admin_notification_email(): void
    {
        Config::set('mail.admin_recipients', ['admin@fixitautoservices.com']);
        Mail::fake();

        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);

        $this->postJson('/api/contact/save', $this->payload())->assertOk();

        // An admin-notification mailable was sent to the admin recipient(s).
        Mail::assertSent(\App\Mail\NewContactAdminNotification::class, function ($mail) {
            return $mail->hasTo('admin@fixitautoservices.com');
        });
    }

    /**
     * The new-contact admin notification mailable renders without error and
     * carries the contact details so the admin can follow up.
     */
    public function test_contact_admin_notification_mailable_renders_with_contact_details(): void
    {
        $message = new ContactMessage([
            'name'    => 'Cyd Dalupan',
            'email'   => 'cyd@example.com',
            'phone'   => '09175550001',
            'subject' => 'Quotation request',
            'message' => 'Please send me a quote.',
        ]);

        $html = (new \App\Mail\NewContactAdminNotification($message))->render();

        $this->assertStringContainsString('Contact', $html);
        $this->assertStringContainsString('Cyd', $html);
        $this->assertStringContainsString('cyd@example.com', $html);
    }

    /**
     * Honeypot spam protection: when the hidden honeypot field is filled in
     * (a bot), the submission is silently rejected and NOT saved, and no
     * admin notification is sent.
     */
    public function test_contact_submission_with_honeypot_filled_is_rejected_as_spam(): void
    {
        Config::set('mail.admin_recipients', ['admin@fixitautoservices.com']);
        Mail::fake();

        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);

        $response = $this->postJson('/api/contact/save', $this->payload([
            'website' => 'http://spam-bot.example.com', // honeypot field filled by a bot
        ]));

        // Silently accept (don't leak it's a bot) but do NOT persist or email.
        $response->assertOk();
        $this->assertSame(0, ContactMessage::count());
        Mail::assertNothingSent();
    }
}
