<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;

/**
 * Bug #2: /job-orders/create page issues.
 *
 * (a) The "A/C Issue" quick-note tag is not clickable — its onclick attribute
 *     has double quotes nested inside a double-quoted HTML attribute.
 * (b) The Warranty & Insurance section appears empty — the warranty/insurance
 *     field blocks are hidden by default and the checkboxes live in a
 *     different section (Work Options) instead of inside the Warranty section.
 *
 * RED first: both defects are present in the view.
 */
class JobOrderCreateFormTest extends TestCase
{
    /** @test */
    public function create_page_renders_and_all_quick_note_tags_are_clickable()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get('/job-orders/create')
            ->assertOk();

        $html = $response->getContent();

        // Every quick-note tag must use valid single-quoted strings inside the
        // double-quoted onclick attribute — no broken quote nesting.
        $quickNotes = [
            "appendNote('customer_concerns', 'Check engine light on - needs diagnostic scan')",
            "appendNote('customer_concerns', 'Change oil, replace oil filter, top up fluids (PMS)')",
            "appendNote('customer_concerns', 'Vibrations when braking - inspect brake pads and rotors')",
            "appendNote('customer_concerns', 'Replace 4 tires, alignment, and balancing')",
            "appendNote('customer_concerns', 'A/C not blowing cold air - check refrigerant and compressor')",
        ];

        foreach ($quickNotes as $note) {
            $this->assertStringContainsString(
                $note,
                $html,
                'Quick-note tag must render with valid quoting: ' . $note
            );
        }
    }

    /** @test */
    public function warranty_and_insurance_section_contains_its_toggle_checkboxes()
    {
        $user = User::factory()->create();

        $html = $this->actingAs($user)
            ->get('/job-orders/create')
            ->assertOk()
            ->getContent();

        // The Warranty & Insurance section must contain the checkboxes that
        // reveal its fields — not just an empty hidden block.
        $warrantySection = $this->extractSection($html, 'warrantySection');

        $this->assertNotNull($warrantySection, 'warrantySection block must exist');
        $this->assertStringContainsString('name="is_warranty_work"', $warrantySection);
        $this->assertStringContainsString('name="is_insurance_work"', $warrantySection);
        $this->assertStringContainsString('name="warranty_type"', $warrantySection);
        $this->assertStringContainsString('name="insurance_company"', $warrantySection);
    }

    private function extractSection(string $html, string $id): ?string
    {
        // Capture from the section div up to the next section comment marker.
        $pattern = '/<div[^>]*id="' . preg_quote($id, '/') . '"[^>]*>(.*?)(?=<!-- ===== SECTION:|<!-- ========== SECTION:|$)/s';
        if (preg_match($pattern, $html, $m)) {
            return $m[1];
        }
        return null;
    }
}
