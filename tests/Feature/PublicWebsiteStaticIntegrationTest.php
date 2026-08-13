<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Contract tests for the PUBLIC WEBSITE integration items on the card
 * "Fixit Remake - P2: Public Booking (Guest Flow)" that sit purely on the
 * static site (/var/www/fixit-static -> https://fixitautoservices.com) rather
 * than the app API.
 *
 * These pin the static-site acceptance items:
 *   [ ] Booking form reachable from fixitautoservices.com (public website)
 *   [ ] Form fields match blueprint: name, email, phone, vehicle,
 *       service request, date/time
 *   [ ] On submit -> appointment recorded (status: scheduled)
 *   [ ] Book Now CTA on main site landing page linking to booking
 *
 * They assert against the LIVE deployed static site so that any future change
 * to the website that breaks the booking entry point is caught immediately.
 */
class PublicWebsiteStaticIntegrationTest extends TestCase
{
    protected string $base = 'https://fixitautoservices.com';

    /**
     * [ ] Booking form reachable from fixitautoservices.com (public website)
     */
    public function test_booking_form_is_reachable_from_public_website(): void
    {
        $res = Http::get("{$this->base}/booking/");
        $this->assertTrue($res->ok(), 'GET /booking/ should return 200 on the public website.');
        $this->assertStringContainsString('service_request', $res->body(), 'booking page should render the form (service_request field).');
    }

    /**
     * [ ] Form fields match blueprint: name, email, phone, vehicle,
     *     service request, date/time
     */
    public function test_booking_form_has_all_blueprint_fields(): void
    {
        $res = Http::get("{$this->base}/booking/");
        $this->assertTrue($res->ok());
        $body = $res->body();

        $requiredFields = [
            'name', 'email', 'phone',                       // guest contact
            'vehicle_make', 'vehicle_model', 'vehicle_year', 'vehicle_plate', // vehicle
            'service_type', 'service_request',              // service request
            'appointment_date', 'appointment_time',         // date/time
        ];

        foreach ($requiredFields as $field) {
            $this->assertStringContainsString(
                "name=\"{$field}\"",
                $body,
                "Booking form must contain a field named \"{$field}\"."
            );
        }
    }

    /**
     * [ ] Book Now CTA on main site landing page linking to booking
     */
    public function test_landing_page_has_booking_cta_linking_to_booking_route(): void
    {
        $res = Http::get($this->base . '/');
        $this->assertTrue($res->ok());
        $body = $res->body();

        // The landing page must contain at least one link pointing to /booking/.
        $this->assertStringContainsString('href="/booking/"', $body, 'Landing page must link to the booking page.');

        // A visible CTA label ("Book Now" / "Book Online") should accompany it.
        $this->assertMatchesRegularExpression(
            '/>Book (Now|Online)</',
            $body,
            'Landing page should have a visible Book Now / Book Online CTA.'
        );
    }

    /**
     * [ ] Mobile view: the show-navigation (hamburger) toggle must be able to
     *     open the mobile menu. The #mobile-menu element must NOT carry the
     *     Tailwind `hidden` class (display:none), which would always win over
     *     the CSS .show max-height toggle and make the menu unopenable.
     */
    public function test_landing_page_mobile_menu_is_toggleable(): void
    {
        $res = Http::get($this->base . '/');
        $this->assertTrue($res->ok());
        $body = $res->body();

        $this->assertStringContainsString('id="menu-toggle"', $body, 'Mobile hamburger button must exist.');
        $this->assertStringContainsString('id="mobile-menu"', $body, 'Mobile menu panel must exist.');
        $this->assertStringNotContainsString(
            'id="mobile-menu" class="md:hidden hidden',
            $body,
            'Mobile menu must not be permanently display:none via the `hidden` class.'
        );
    }

    /**
     * [ ] Mobile view: the booking page hamburger must have a menu to open.
     */
    public function test_booking_page_has_mobile_menu(): void
    {
        $res = Http::get($this->base . '/booking/');
        $this->assertTrue($res->ok());
        $body = $res->body();

        $this->assertStringContainsString('id="menu-toggle"', $body, 'Booking page must have a hamburger button.');
        $this->assertStringContainsString('id="mobile-menu"', $body, 'Booking page must have a mobile menu panel.');
    }

    /**
     * [ ] Booking must be as easy as Contact Us: a "Book an Appointment"
     *     CTA on the landing page, linking to /booking/.
     */
    public function test_landing_page_has_book_an_appointment_cta(): void
    {
        $res = Http::get($this->base . '/');
        $this->assertTrue($res->ok());
        $body = $res->body();

        $this->assertStringContainsString('Book an Appointment', $body, 'Landing page must show a "Book an Appointment" CTA.');
        $this->assertMatchesRegularExpression(
            '/href="\/booking\/"[^>]*>[^<]*Book an Appointment|Book an Appointment[^<]*<\/a>/',
            $body,
            'The Book an Appointment CTA must link to /booking/.'
        );
    }

    /**
     * [ ] Brand/Model autocomplete must be VISIBLE on the booking page:
     *     clickable suggestion dropdowns (not just invisible datalists).
     */
    public function test_booking_page_has_visible_brand_model_dropdowns(): void
    {
        $res = Http::get($this->base . '/booking/');
        $this->assertTrue($res->ok());
        $body = $res->body();

        $this->assertStringContainsString('id="make-dropdown"', $body, 'Make input must have a visible suggestions dropdown.');
        $this->assertStringContainsString('id="model-dropdown"', $body, 'Model input must have a visible suggestions dropdown.');
        $this->assertStringContainsString('vehicle-brands', $body, 'Dropdown data must be wired to the vehicle-brands API.');
    }

    /**
     * [ ] On submit -> appointment recorded in system (status: scheduled)
     *
     * Delegate to the existing API contract which proves a payload identical to
     * the form's POST creates an appointment with status "scheduled". This test
     * re-asserts the exact status transition the acceptance item asks for via
     * the real in-app submission path (protected against CSRF for the API).
     */
    public function test_submit_records_appointment_with_status_scheduled(): void
    {
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);

        $response = $this->postJson('/api/booking/customer-booking', [
            'name' => 'Guest Contracts',
            'email' => 'guest.contracts@example.com',
            'phone' => '09179876543',
            'vehicle_make' => 'Honda',
            'vehicle_model' => 'Civic',
            'vehicle_year' => 2020,
            'vehicle_plate' => 'XYZ-9876',
            'service_type' => 'general_checkup',
            'service_request' => 'Annual checkup',
            'appointment_date' => now()->addDays(4)->toDateString(),
            'appointment_time' => '10:00',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('appointments', [
            'booking_source' => 'website',
            'status' => 'scheduled',
            'appointment_date' => now()->addDays(4)->toDateString() . ' 00:00:00',
        ]);
    }
}
