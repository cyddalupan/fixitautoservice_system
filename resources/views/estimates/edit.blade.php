@extends('layouts.app')

@section('title', 'Repair Quotation - ' . $estimate->estimate_number)

@section('content')
@include('partials.customer-process-assets')
<div class="container-fluid px-4 py-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1" style="color: #1a237e;">
                <i class="fas fa-file-invoice-dollar me-2"></i>Repair Quotation {{ $estimate->estimate_number }}
            </h4>
            <p class="text-muted mb-0">
                <i class="fas fa-calendar me-1"></i>
                {{ $estimate->created_at ? $estimate->created_at->format('F j, Y g:i A') : 'N/A' }}
                @if($inspection)
                    &middot; <i class="fas fa-clipboard-list me-1"></i>Repair Order #{{ $inspection->id }}
                @endif
            </p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            @if($inspection)
                <a href="{{ route('inspections.show', $inspection) }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Back to Repair Order {{ $inspection->reference_label }}
                </a>
                <a href="{{ route('inspections.quotation-slip', $inspection) }}" class="btn btn-outline-success" target="_blank" title="Printable Repair Quotation slip (same as the Repair Order slip)">
                    <i class="fas fa-print me-1"></i>Repair Quotation Slip
                </a>
            @endif
            <a href="{{ route('estimates.show', $estimate->id) }}" class="btn btn-info">
                <i class="fas fa-eye me-1"></i>View
            </a>
            @if(!in_array($estimate->status, ['converted_to_repair_order', 'converted_to_job_order', 'converted', 'rejected'], true))
                @include('estimates.partials.proceed-to-repair-order', ['estimate' => $estimate, 'inspection' => $inspection])
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-3" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(!$inspection)
        <div class="alert alert-warning d-flex align-items-center" role="alert">
            <i class="fas fa-triangle-exclamation me-2"></i>
            <div>
                This quotation is not linked to a Repair Order, so there are no items to show.
                Use <a href="{{ route('estimates.show', $estimate->id) }}" class="alert-link">View</a> instead.
            </div>
        </div>
    @else
        <!-- Section Navigation Pills -->
        <ul class="nav nav-pills section-nav mb-4" id="quotationTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="overview-tab" data-bs-toggle="pill" data-bs-target="#overview" type="button" role="tab">
                    <i class="fas fa-info-circle me-1"></i>Overview
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="items-tab" data-bs-toggle="pill" data-bs-target="#items" type="button" role="tab">
                    <i class="fas fa-file-invoice-dollar me-1"></i>Quotation Items
                </button>
            </li>
        </ul>

        <div class="tab-content">

            <!-- === OVERVIEW TAB === -->
            <div class="tab-pane fade show active" id="overview" role="tabpanel">
                @include('estimates.partials.ro-overview', ['inspection' => $inspection])

                {{-- Payment Verification — same panel as the Repair Order, but priced off the
                     QUOTATION total (parts + labor from the linked RO's findings, excl. Not Pursued),
                     i.e. the same amount shown in the quotations list "Amount" column. --}}
                <div class="mt-3">
                    @include('inspections.partials.payment-verification', [
                        'inspection' => $inspection,
                        'payments' => $estimate->quotationPayments(),
                        'paymentTotal' => $estimate->quotation_total,
                        'paymentTotalFormatted' => $estimate->formatted_quotation_total,
                        'totalLabel' => 'Quotation total',
                    ])
                </div>
            </div>

            <!-- === QUOTATION ITEMS TAB === -->
            <div class="tab-pane fade" id="items" role="tabpanel">
                <div class="form-section">
                    <div class="form-section-header no-collapse">
                        <h6>
                            <i class="fas fa-file-invoice-dollar"></i>Quotation Items
                            <span class="badge bg-secondary ms-2">{{ $estimate->quotedFindings()->count() }} item(s)</span>
                        </h6>
                    </div>
                    <div class="form-section-body">
                        {{-- quotationMode=true: no "add" bar, no finding details edit, no delete. Only pricing + labor.
                             findingScope: show only the findings THIS quotation prices — a re-quote created after
                             the RO was locked (during repair) shows the new findings, not the original quotation's. --}}
                        @include('inspections.partials.findings-board', [
                            'quotationMode' => true,
                            'findingsLocked' => $inspection->isFindingsLocked(),
                            'findingScope' => $estimate->quotationFindingScope(),
                        ])
                    </div>
                </div>
            </div>

        </div>
    @endif
</div>

@if($inspection)
    @include('inspections.partials.findings-modal')
@endif
@endsection

@push('styles')
@include('inspections.partials.findings-styles')
@endpush

@if($inspection)
@push('scripts')
<script>window.FINDINGS_QUOTATION_MODE = true;</script>
@include('inspections.partials.inspection-findings-scripts')
@endpush
@endif
