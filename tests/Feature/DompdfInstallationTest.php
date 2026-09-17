<?php

namespace Tests\Feature;

use Tests\TestCase;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\App;

class DompdfInstallationTest extends TestCase
{
    /**
     * The barryvdh/laravel-dompdf package must be installed and its
     * service provider registered so PDF generation works for J.O. printing.
     */
    public function test_dompdf_package_is_installed_and_registered(): void
    {
        $this->assertTrue(class_exists(\Barryvdh\DomPDF\ServiceProvider::class));
        $this->assertTrue(class_exists(Pdf::class));
    }

    public function test_pdf_facade_can_generate_a_pdf_from_a_view(): void
    {
        // Build a minimal PDF from an inline view via the DomPDF facade.
        $pdf = Pdf::loadView('pdfs.job-order', [
            'jobOrderNumber' => 'JO-0001',
            'notes'          => 'Test notes for TDD dompdf verification',
            'items'          => [
                ['service_name' => 'Oil Change', 'service_price' => 1200, 'quantity' => 1],
            ],
        ]);

        $this->assertInstanceOf(\Barryvdh\DomPDF\PDF::class, $pdf);

        $output = $pdf->output();

        // DomPDF outputs a binary PDF; it must be non-empty and start with %PDF.
        $this->assertNotEmpty($output);
        $this->assertStringStartsWith('%PDF', $output);
    }
}
