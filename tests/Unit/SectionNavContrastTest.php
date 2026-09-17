<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * TDD (red -> green) for checklist item 2:
 * "Hindi makita yung Details, or category — gray font color, white background."
 *
 * The admin create page's section navigation pills ("Details / Service / Team /
 * Notes / History") must have sufficient contrast against the section-nav
 * background (WCAG AA >= 4.5:1 for normal text; we enforce AAA >= 7:1 so the
 * "gray on white" complaint cannot regress).
 */
class SectionNavContrastTest extends TestCase
{
    private function cssPath(): string
    {
        return dirname(__DIR__, 2) . '/resources/views/partials/customer-process-assets.blade.php';
    }

    /** @test */
    public function section_nav_pill_meets_wcag_aaa_contrast_against_background()
    {
        $css = file_get_contents($this->cssPath());
        $this->assertNotFalse($css, 'customer-process-assets partial not found');

        $pillColor = $this->extractProperty($css, '.section-nav .nav-pill', 'color');
        $bgColor = $this->extractProperty($css, '.section-nav', 'background');

        $this->assertNotNull($pillColor, '.section-nav .nav-pill color not found');
        $this->assertNotNull($bgColor, '.section-nav background not found');

        $ratio = $this->contrastRatio($this->hexToRgb($pillColor), $this->hexToRgb($bgColor));

        $this->assertGreaterThanOrEqual(
            7.0,
            $ratio,
            "Section nav pill color {$pillColor} on {$bgColor} has contrast {$ratio}:1 — needs >= 7:1 (gray-on-white complaint)"
        );
    }

    /** @test */
    public function section_nav_pill_color_is_not_the_old_light_gray()
    {
        $css = file_get_contents($this->cssPath());
        $pillColor = $this->extractProperty($css, '.section-nav .nav-pill', 'color');

        // Old value #666 on #f5f5f5 was the reported "hindi makita" issue.
        $this->assertNotEquals('#666', strtolower($pillColor));
        $this->assertNotEquals('#666666', strtolower($pillColor));
    }

    // ---------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------

    private function extractProperty(string $css, string $selector, string $property): ?string
    {
        // Match the selector block (up to closing brace), then find property: value;
        if (preg_match('/' . preg_quote($selector, '/') . '\s*\{([^}]*)\}/s', $css, $block)) {
            if (preg_match('/' . preg_quote($property, '/') . '\s*:\s*(#[0-9a-fA-F]{3,8}|rgba?\([^)]+\))\s*;/', $block[1], $m)) {
                $value = trim($m[1]);
                if (str_starts_with($value, '#')) {
                    return $value;
                }
                return null;
            }
        }
        return null;
    }

    private function hexToRgb(string $hex): array
    {
        $hex = ltrim(strtolower($hex), '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }
        return [
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2)),
        ];
    }

    private function relativeLuminance(array $rgb): float
    {
        $linear = array_map(function (int $c) {
            $s = $c / 255;
            return $s <= 0.03928 ? $s / 12.92 : (($s + 0.055) / 1.055) ** 2.4;
        }, $rgb);

        return 0.2126 * $linear[0] + 0.7152 * $linear[1] + 0.0722 * $linear[2];
    }

    private function contrastRatio(array $fg, array $bg): float
    {
        $l1 = $this->relativeLuminance($fg);
        $l2 = $this->relativeLuminance($bg);
        $lighter = max($l1, $l2);
        $darker = min($l1, $l2);

        return ($lighter + 0.05) / ($darker + 0.05);
    }
}
