@extends('layouts.app')

@section('title', $vehicle->full_description . ' - Vehicle Details')
@section('body-class', 'page-vehicles')

@push('styles')
<style>
:root {
    --vhcl-primary: #dc2626;
}
body.dark-mode { --vhcl-card-bg: #2a2d35; }

/* ── Profile header ── */
.vhcl-card { border-radius: 12px; border: 0; box-shadow: 0 1px 3px rgba(0,0,0,.04); margin-bottom: 1rem; }
body.dark-mode .vhcl-card { background: #2a2d35; }

.vhcl-profile-header {
    position: relative;
    padding: 2rem 1.5rem 1.5rem;
    text-align: center;
    background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%);
    border-radius: 12px 12px 0 0;
    color: #fff;
    overflow: hidden;
}
.vhcl-profile-header::before {
    content: '';
    position: absolute;
    inset: 0;
    background: repeating-linear-gradient(45deg, transparent, transparent 20px, rgba(255,255,255,.04) 20px, rgba(255,255,255,.04) 40px);
}
.vhcl-profile-avatar {
    width: 96px;
    height: 96px;
    border-radius: 12px;
    overflow: hidden;
    margin: 0 auto .75rem;
    position: relative;
    object-fit: cover;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2.5rem;
    background: rgba(255,255,255,.15);
    border: 3px solid rgba(255,255,255,.3);
}
.vhcl-profile-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.vhcl-profile-name {
    font-size: 1.25rem;
    font-weight: 700;
    margin-bottom: .15rem;
    position: relative;
}
.vhcl-profile-plate {
    font-size: .82rem;
    opacity: .9;
    margin-bottom: .5rem;
    position: relative;
}
.vhcl-profile-plate code {
    background: rgba(255,255,255,.15);
    padding: .15rem .55rem;
    border-radius: 4px;
    font-size: .82rem;
    color: #fff;
}
.vhcl-profile-meta {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: .5rem 1.5rem;
    position: relative;
    font-size: .78rem;
    opacity: .9;
}
.vhcl-profile-meta i {
    margin-right: .3rem;
    width: 14px;
    text-align: center;
}

/* ── Detail rows ── */
.vhcl-section-title {
    font-size: .82rem;
    font-weight: 600;
    color: #1a2332;
    padding: .75rem 1rem;
    border-bottom: 1px solid #eef0f3;
    display: flex;
    align-items: center;
    gap: .5rem;
}
body.dark-mode .vhcl-section-title { color: #e4e6eb; border-bottom-color: #3a3d45; }

.vhcl-info-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0;
}
.vhcl-info-item {
    padding: .5rem .85rem;
    font-size: .8rem;
    border-bottom: 1px solid #f1f5f9;
}
.vhcl-info-item:nth-child(even) { border-left: 1px solid #f1f5f9; }
body.dark-mode .vhcl-info-item { border-bottom-color: #3a3d45; }
body.dark-mode .vhcl-info-item:nth-child(even) { border-left-color: #3a3d45; }
.vhcl-info-label {
    font-size: .65rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .5px;
    color: #94a3b8;
    margin-bottom: .15rem;
}
.vhcl-info-value {
    color: #0f172a;
    font-weight: 500;
}
body.dark-mode .vhcl-info-value { color: #d1d5db; }

/* Active job card */
.vhcl-active-job {
    border: 1px solid #ffedd5;
    border-radius: 10px;
    overflow: hidden;
    background: #fff7ed;
}
body.dark-mode .vhcl-active-job { border-color: #5a3a0a; background: #2a1a0a; }
.vhcl-active-job-header {
    display: flex;
    align-items: center;
    gap: .5rem;
    padding: .65rem .85rem;
    background: #ffedd5;
    font-size: .78rem;
    font-weight: 600;
    color: #9a3412;
}
body.dark-mode .vhcl-active-job-header { background: #3a2a0a; color: #fbbf24; }
.vhcl-active-job-body { padding: .65rem .85rem; font-size: .8rem; }

/* ── Empty state ── */
.vhcl-empty {
    padding: 2rem 1rem;
    text-align: center;
    color: #9ca3af;
    font-size: .82rem;
}

/* ── Stats row ── */
.vhcl-stats-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 0;
    border-bottom: 1px solid #e2e8f0;
}
.vhcl-stat-item {
    text-align: center;
    padding: .85rem .5rem;
}
.vhcl-stat-item + .vhcl-stat-item { border-left: 1px solid #e2e8f0; }
body.dark-mode .vhcl-stat-item + .vhcl-stat-item { border-left-color: #374151; }
.vhcl-stat-item .num {
    font-size: 1.2rem;
    font-weight: 700;
    color: #0f172a;
    line-height: 1.2;
}
body.dark-mode .vhcl-stat-item .num { color: #e4e6eb; }
.vhcl-stat-item .lbl {
    font-size: .68rem;
    font-weight: 500;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: .3px;
}

@media (max-width: 576px) {
    .vhcl-info-grid { grid-template-columns: 1fr; }
    .vhcl-info-item:nth-child(even) { border-left: none; }
}

/* ── Dark Mode Extras (text visibility, sidebar, tabs) ── */
body.dark-mode .vhcl-info-label {
    color: #9ca3af;
}
body.dark-mode .vhcl-info-value {
    color: #e4e6eb;
}
body.dark-mode .vhcl-info-value small {
    color: #6b7280;
}
body.dark-mode .vhcl-section-title {
    color: #e4e6eb !important;
    border-bottom-color: #3a3d45 !important;
}
body.dark-mode .vhcl-card .main-card-header {
    color: #e4e6eb;
    border-bottom-color: #3a3d45;
}
body.dark-mode .vhcl-card .main-card-body {
    color: #d1d5db;
}
body.dark-mode .main-card {
    background: #2a2d35;
    border-color: #3a3d45;
}
body.dark-mode .main-card-header {
    color: #e4e6eb;
    border-bottom-color: #3a3d45;
}
body.dark-mode .main-card-body {
    color: #d1d5db;
}
body.dark-mode .text-muted {
    color: #9ca3af !important;
}
body.dark-mode .card,
body.dark-mode .card-body {
    background: #2a2d35;
    color: #d1d5db;
}
body.dark-mode .tabs-nav {
    border-bottom-color: #3a3d45;
}
body.dark-mode .tabs-nav .tab-item {
    color: #9ca3af;
}
body.dark-mode .tabs-nav .tab-item.active,
body.dark-mode .tabs-nav .tab-item:hover {
    color: #dc2626;
}
body.dark-mode .vhcl-stat-item .lbl {
    color: #6b7280;
}
body.dark-mode .breadcrumb-item.active {
    color: #d1d5db !important;
}
body.dark-mode .page-module-header h1 {
    color: #e4e6eb;
}
body.dark-mode .vhcl-active-job .btn-outline-warning {
    color: #fbbf24;
    border-color: #fbbf24;
}
body.dark-mode .quick-action-btn {
    background: #1e2028;
    border-color: #3a3d45;
    color: #d1d5db;
}
body.dark-mode .quick-action-btn:hover {
    background: #374151;
}
body.dark-mode .quick-action-btn i {
    color: #dc2626;
}
body.dark-mode .quick-action-btn .qab-label {
    color: #9ca3af;
}
body.dark-mode .badge-custom {
    color: #d1d5db !important;
}
body.dark-mode .detail-row {
    color: #d1d5db;
}
body.dark-mode .detail-label {
    color: #9ca3af;
}
body.dark-mode .customer-details-section {
    border-bottom-color: #3a3d45;
}
body.dark-mode .btn-filter-outline {
    color: #d1d5db;
    border-color: #4b5563;
}
body.dark-mode .btn-filter-outline:hover {
    background: #374151;
    color: #fff;
}
body.dark-mode .table-fixit td,
body.dark-mode .table-fixit th {
    color: #d1d5db;
    border-bottom-color: #3a3d45;
}
body.dark-mode .table-fixit tbody tr:hover {
    background: #2a2a2a;
}
body.dark-mode .vhcl-card .btn-outline-primary {
    color: #60a5fa;
    border-color: #60a5fa;
}
body.dark-mode .vhcl-card .btn-outline-secondary {
    color: #9ca3af;
    border-color: #4b5563;
}
body.dark-mode .vhcl-quick-actions {
    border-top-color: #3a3d45;
}
body.dark-mode .vhcl-info-grid {
    border-top-color: #3a3d45;
}
body.dark-mode .vhcl-stats-grid {
    border-bottom-color: #3a3d45;
}
body.dark-mode .service-type-badge {
    color: #d1d5db;
}
body.dark-mode .service-status-badge {
    color: #d1d5db;
}
</style>
@endpush

@section('content')
<div class="container-fluid px-3 px-md-4">
    <!-- ══ PAGE MODULE HEADER ══ -->
    <div class="page-module-header">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="module-icon">
                    <i class="fas fa-car"></i>
                </div>
                <div>
                    <h1 class="h4 mb-1" style="font-weight: 700;">{{ $vehicle->full_description }}</h1>
                    <ol class="breadcrumb m-0 p-0" style="background: none; font-size: 0.8rem;">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('vehicles.index') }}">Vehicles</a></li>
                        <li class="breadcrumb-item active">{{ $vehicle->full_description }}</li>
                    </ol>
                </div>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('vehicles.edit', $vehicle) }}" class="btn-filter-primary">
                    <i class="fas fa-edit me-1"></i> Edit Vehicle
                </a>
                <a href="{{ route('vehicles.index') }}" class="btn-filter-outline">
                    <i class="fas fa-arrow-left me-1"></i> Back to List
                </a>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-4">
        <!-- ══ LEFT COLUMN: Profile + Quick Actions ══ -->
        <div class="col-lg-4">
            <!-- Profile Card -->
            <div class="main-card">
                <div class="vhcl-profile-header">
                    <div class="vhcl-profile-avatar">
                        @if($vehicle->photo && $vehicle->photo_path)
                            <img src="{{ Storage::url($vehicle->photo_path) }}" alt="{{ $vehicle->full_description }}">
                        @else
                            <i class="fas fa-car"></i>
                        @endif
                    </div>
                    <div class="vhcl-profile-name">{{ $vehicle->full_description }}</div>
                    <div class="vhcl-profile-plate">
                        @if($vehicle->license_plate)
                            <code><i class="fas fa-tag me-1"></i>{{ $vehicle->license_plate }}</code>
                        @else
                            <span style="opacity:.6">No plate registered</span>
                        @endif
                    </div>
                    <div class="vhcl-profile-meta">
                        @if($vehicle->customer)
                            <span>
                                <i class="fas fa-user"></i>
                                <a href="{{ route('customers.show', $vehicle->customer) }}" style="color: #fff; text-decoration: underline; text-underline-offset: 2px;">
                                    {{ $vehicle->customer->first_name }} {{ $vehicle->customer->last_name }}
                                </a>
                            </span>
                        @endif
                        <span>
                            <i class="fas fa-{{ $vehicle->is_active ? 'check-circle' : 'times-circle' }}"></i>
                            {{ $vehicle->is_active ? 'Active' : 'Inactive' }}
                        </span>
                        @if($vehicle->color)
                            <span><i class="fas fa-palette"></i>{{ $vehicle->color }}</span>
                        @endif
                    </div>
                </div>
                <div class="main-card-body px-3 pb-3">
                    <!-- Key Info -->
                    <div class="customer-details-section" style="margin-top: .75rem;">
                        <div class="detail-label">Vehicle Information</div>
                        <div class="detail-row">
                            <i class="fas fa-barcode detail-icon"></i>
                            <span>VIN: <strong>{{ $vehicle->vin ?? 'N/A' }}</strong></span>
                        </div>
                        <div class="detail-row">
                            <i class="fas fa-palette detail-icon"></i>
                            <span>Color: <strong>{{ $vehicle->color ?? 'N/A' }}</strong></span>
                        </div>
                        <div class="detail-row">
                            <i class="fas fa-cog detail-icon"></i>
                            <span>Engine: <strong>{{ $vehicle->engine_type ?? 'N/A' }} {{ $vehicle->engine_no ? '('.$vehicle->engine_no.')' : '' }}</strong></span>
                        </div>
                        <div class="detail-row">
                            <i class="fas fa-tachometer-alt detail-icon"></i>
                            <span>Transmission: <strong>{{ $vehicle->transmission ?? 'N/A' }}</strong></span>
                        </div>
                        <div class="detail-row">
                            <i class="fas fa-gas-pump detail-icon"></i>
                            <span>Fuel: <strong>{{ $vehicle->fuel_type ?? 'N/A' }}</strong></span>
                        </div>
                        <div class="detail-row">
                            <i class="fas fa-tachometer detail-icon"></i>
                            <span>Odometer: <strong>{{ $vehicle->odometer ? number_format($vehicle->odometer).' mi' : 'N/A' }}</strong></span>
                        </div>
                    </div>

                    <!-- Warranty -->
                    <div class="customer-details-section">
                        <div class="detail-label">Warranty & Recalls</div>
                        <div class="detail-row">
                            <i class="fas fa-shield-alt detail-icon" style="color: {{ $vehicle->has_warranty ? '#16a34a' : '#94a3b8' }};"></i>
                            <span>
                                @if($vehicle->has_warranty)
                                    Warranty active
                                    @if($vehicle->warranty_expiry)
                                        <span class="text-muted">— expires {{ \Carbon\Carbon::parse($vehicle->warranty_expiry)->format('M j, Y') }}</span>
                                    @endif
                                @else
                                    No warranty
                                @endif
                            </span>
                        </div>
                        <div class="detail-row">
                            <i class="fas fa-exclamation-triangle detail-icon" style="color: {{ $vehicle->has_recall ? '#dc2626' : '#94a3b8' }};"></i>
                            <span>
                                @if($vehicle->has_recall)
                                    Has active recall
                                    @if($vehicle->recall_details)
                                        <span class="text-muted">— {{ Str::limit($vehicle->recall_details, 40) }}</span>
                                    @endif
                                @else
                                    No recalls
                                @endif
                            </span>
                        </div>
                    </div>

                    <!-- Service Status -->
                    <div class="customer-details-section">
                        <div class="detail-label">Service Schedule</div>
                        <div class="detail-row">
                            <i class="fas fa-calendar-check detail-icon" style="color: var(--module-active);"></i>
                            <span>Last service: 
                                @if($vehicle->last_service_date)
                                    <strong>{{ \Carbon\Carbon::parse($vehicle->last_service_date)->format('M j, Y') }}</strong>
                                @else
                                    <span class="text-muted">Never</span>
                                @endif
                            </span>
                        </div>
                        <div class="detail-row">
                            <i class="fas fa-clock detail-icon" style="color: var(--module-active);"></i>
                            <span>
                                Next service due:
                                @php
                                    $dueLabel = $vehicle->next_service_due;
                                    $dueColor = $vehicle->service_status_color;
                                @endphp
                                <span class="badge-custom badge-{{ $dueColor }}">
                                    {{ $dueLabel ?? 'Not set' }}
                                </span>
                            </span>
                        </div>
                        @if($vehicle->service_interval_miles || $vehicle->service_interval_months)
                            <div class="detail-row">
                                <i class="fas fa-redo-alt detail-icon"></i>
                                <span class="text-muted small">
                                    Every {{ $vehicle->service_interval_miles ? number_format($vehicle->service_interval_miles).' mi' : '' }}
                                    {{ ($vehicle->service_interval_miles && $vehicle->service_interval_months) ? '/ ' : '' }}
                                    {{ $vehicle->service_interval_months ? $vehicle->service_interval_months.' month'.($vehicle->service_interval_months > 1 ? 's' : '') : '' }}
                                </span>
                            </div>
                        @endif
                    </div>

                    <!-- Registration dates -->
                    <div class="customer-details-section" style="border-bottom: none;">
                        <div class="detail-label">Registration</div>
                        <div class="detail-row">
                            <i class="fas fa-calendar-plus detail-icon"></i>
                            <span>Registered {{ $vehicle->created_at->format('M j, Y') }}</span>
                        </div>
                        <div class="detail-row">
                            <i class="fas fa-calendar-alt detail-icon"></i>
                            <span>Updated {{ $vehicle->updated_at->format('M j, Y') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Active Job Card -->
            @php
                $activeAppts = $vehicle->appointments ? $vehicle->appointments->filter(function($a) {
                    return in_array($a->status, ['pending', 'confirmed', 'checked_in', 'in_progress', 'repairing']);
                })->values() : collect();
                $activeWO = $vehicle->serviceRecords ? $vehicle->serviceRecords->filter(function($sr) {
                    return in_array($sr->status ?? '', ['pending', 'in_progress', 'checked_in', 'repairing']);
                })->values() : collect();
            @endphp
            @if($activeAppts->count() > 0 || $activeWO->count() > 0)
                <div class="vhcl-active-job mt-4">
                    <div class="vhcl-active-job-header">
                        <i class="fas fa-wrench"></i>
                        <span>Active Job</span>
                        @if($activeAppts->count() > 0)
                            <span class="badge-custom badge-warning ms-auto">{{ $activeAppts->count() }} active</span>
                        @endif
                    </div>
                    <div class="vhcl-active-job-body">
                        @foreach($activeAppts as $apt)
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <div>
                                    <strong style="font-size:.8rem;">{{ $apt->appointment_number ?? 'Appointment #'.$apt->id }}</strong>
                                    <div class="text-muted" style="font-size:.72rem;">
                                        {{ $apt->service_type ?? 'General' }}
                                    </div>
                                </div>
                                <span class="status-badge bg-warning text-dark" style="font-size:.68rem;">{{ ucfirst($apt->status) }}</span>
                            </div>
                        @endforeach
                        @foreach($activeWO as $wo)
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <div>
                                    <strong style="font-size:.8rem;">{{ $wo->work_order_number ?? 'Work Order #'.$wo->id }}</strong>
                                    <div class="text-muted" style="font-size:.72rem;">
                                        {{ $wo->description ?? 'General' }}
                                    </div>
                                </div>
                                <span class="status-badge bg-warning text-dark" style="font-size:.68rem;">{{ ucfirst($wo->status ?? 'pending') }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Quick Actions -->
            <div class="main-card mt-4">
                <div class="main-card-header">
                    <i class="fas fa-bolt me-2" style="color: var(--module-active);"></i> Quick Actions
                </div>
                <div class="main-card-body px-3 pb-3">
                    <div class="quick-actions-grid">
                        <a href="{{ route('vehicles.edit', $vehicle) }}" class="quick-action-btn" style="border-left-color: #f59e0b;">
                            <i class="fas fa-edit" style="color: #f59e0b;"></i>
                            <span>Edit</span>
                        </a>
                        <a href="{{ route('appointments.create') }}?vehicle_id={{ $vehicle->id }}" class="quick-action-btn" style="border-left-color: var(--module-active);">
                            <i class="fas fa-calendar-plus" style="color: var(--module-active);"></i>
                            <span>Appointment</span>
                        </a>
                        <a href="{{ route('estimates.create') }}?vehicle_id={{ $vehicle->id }}" class="quick-action-btn" style="border-left-color: #0ea5e9;">
                            <i class="fas fa-file-invoice-dollar" style="color: #0ea5e9;"></i>
                            <span>Estimate</span>
                        </a>
                        <a href="{{ route('work-orders.create') }}?vehicle_id={{ $vehicle->id }}" class="quick-action-btn" style="border-left-color: #ef4444;">
                            <i class="fas fa-wrench" style="color: #ef4444;"></i>
                            <span>Work Order</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- ══ RIGHT COLUMN: Tabbed Content ══ -->
        <div class="col-lg-8">
            <div class="main-card mb-4">
                <!-- Tab Navigation -->
                <div class="tabs-nav-wrapper">
                    <ul class="tabs-nav" id="vehicleTabs" role="tablist">
                        <li class="tab-item active" role="presentation">
                            <button class="tab-link" id="info-tab" data-bs-toggle="tab" data-bs-target="#info" type="button" role="tab" aria-selected="true">
                                <i class="fas fa-info-circle me-1"></i> Info
                            </button>
                        </li>
                        <li class="tab-item" role="presentation">
                            <button class="tab-link" id="history-tab" data-bs-toggle="tab" data-bs-target="#history" type="button" role="tab" aria-selected="false">
                                <i class="fas fa-history me-1"></i> Service History
                                @if(isset($unifiedHistory) && count($unifiedHistory) > 0)
                                    <span class="badge-tab">{{ count($unifiedHistory) }}</span>
                                @endif
                            </button>
                        </li>
                        <li class="tab-item" role="presentation">
                            <button class="tab-link" id="notes-tab" data-bs-toggle="tab" data-bs-target="#notes" type="button" role="tab" aria-selected="false">
                                <i class="fas fa-sticky-note me-1"></i> Notes
                            </button>
                        </li>
                    </ul>
                    <div class="tabs-nav-extra">
                        @if($vehicle->notes)
                            <button type="button" class="btn-filter-outline btn-sm" data-bs-toggle="modal" data-bs-target="#addNoteModal">
                                <i class="fas fa-plus me-1"></i> Add Note
                            </button>
                        @endif
                    </div>
                </div>

                <!-- Tab Content -->
                <div class="tab-content p-0">
                    <!-- ══ INFO TAB ══ -->
                    <div class="tab-pane fade show active" id="info" role="tabpanel">
                        <!-- Stats row -->
                        <div class="vhcl-stats-grid">
                            <div class="vhcl-stat-item">
                                <div class="num">{{ isset($unifiedHistory) ? count($unifiedHistory) : $vehicle->serviceRecords->count() }}</div>
                                <div class="lbl">Transactions</div>
                            </div>
                            <div class="vhcl-stat-item">
                                <div class="num">
                                    ${{ number_format(collect($unifiedHistory)->sum('total_amount'), 2) }}
                                </div>
                                <div class="lbl">Service Cost</div>
                            </div>
                            <div class="vhcl-stat-item">
                                <div class="num">{{ $vehicle->appointments->count() }}</div>
                                <div class="lbl">Appointments</div>
                            </div>
                        </div>

                        <div class="vhcl-section-title">
                            <i class="fas fa-car" style="color: var(--module-active);"></i> Vehicle Details
                        </div>
                        <div class="vhcl-info-grid">
                            <div class="vhcl-info-item">
                                <div class="vhcl-info-label">Make</div>
                                <div class="vhcl-info-value">{{ $vehicle->make }}</div>
                            </div>
                            <div class="vhcl-info-item">
                                <div class="vhcl-info-label">Model</div>
                                <div class="vhcl-info-value">{{ $vehicle->model }}</div>
                            </div>
                            <div class="vhcl-info-item">
                                <div class="vhcl-info-label">Year</div>
                                <div class="vhcl-info-value">{{ $vehicle->year }}</div>
                            </div>
                            <div class="vhcl-info-item">
                                <div class="vhcl-info-label">Color</div>
                                <div class="vhcl-info-value">
                                    @if($vehicle->color)
                                        <span style="display:inline-block;width:12px;height:12px;border-radius:3px;background:{{ $vehicle->color }};border:1px solid #e2e8f0;vertical-align:middle;margin-right:4px;"></span>
                                        {{ $vehicle->color }}
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </div>
                            </div>
                            <div class="vhcl-info-item">
                                <div class="vhcl-info-label">Engine Type</div>
                                <div class="vhcl-info-value">{{ $vehicle->engine_type ?? 'N/A' }}</div>
                            </div>
                            <div class="vhcl-info-item">
                                <div class="vhcl-info-label">Engine No.</div>
                                <div class="vhcl-info-value">{{ $vehicle->engine_no ?? 'N/A' }}</div>
                            </div>
                            <div class="vhcl-info-item">
                                <div class="vhcl-info-label">Transmission</div>
                                <div class="vhcl-info-value">{{ $vehicle->transmission ?? 'N/A' }}</div>
                            </div>
                            <div class="vhcl-info-item">
                                <div class="vhcl-info-label">Fuel Type</div>
                                <div class="vhcl-info-value">{{ $vehicle->fuel_type ?? 'N/A' }}</div>
                            </div>
                            <div class="vhcl-info-item">
                                <div class="vhcl-info-label">Vehicle Type</div>
                                <div class="vhcl-info-value">{{ $vehicle->vehicle_type ?? 'N/A' }}</div>
                            </div>
                            <div class="vhcl-info-item">
                                <div class="vhcl-info-label">VIN</div>
                                <div class="vhcl-info-value">
                                    <code style="font-size:.75rem;background:#f1f5f9;padding:1px 6px;border-radius:3px;">{{ $vehicle->vin ?? 'N/A' }}</code>
                                </div>
                            </div>
                            <div class="vhcl-info-item">
                                <div class="vhcl-info-label">License Plate</div>
                                <div class="vhcl-info-value">
                                    @if($vehicle->license_plate)
                                        <code style="font-size:.75rem;background:#f1f5f9;padding:1px 6px;border-radius:3px;">{{ $vehicle->license_plate }}</code>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </div>
                            </div>
                            <div class="vhcl-info-item">
                                <div class="vhcl-info-label">Odometer</div>
                                <div class="vhcl-info-value">{{ $vehicle->odometer ? number_format($vehicle->odometer).' mi' : 'N/A' }}</div>
                            </div>
                            <div class="vhcl-info-item">
                                <div class="vhcl-info-label">Warranty</div>
                                <div class="vhcl-info-value">
                                    @if($vehicle->has_warranty)
                                        <span class="badge-custom badge-success">Active</span>
                                        @if($vehicle->warranty_expiry)
                                            <span class="text-muted small">until {{ \Carbon\Carbon::parse($vehicle->warranty_expiry)->format('M j, Y') }}</span>
                                        @endif
                                    @else
                                        <span class="text-muted">None</span>
                                    @endif
                                </div>
                            </div>
                            <div class="vhcl-info-item">
                                <div class="vhcl-info-label">Next Service Due</div>
                                <div class="vhcl-info-value">
                                    @php
                                        $dueLabel = $vehicle->next_service_due;
                                        $dueColor = $vehicle->service_status_color;
                                    @endphp
                                    <span class="badge-custom badge-{{ $dueColor }}">{{ $dueLabel ?? 'Not set' }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Notes section within Info tab -->
                        @if($vehicle->notes)
                            <div class="vhcl-section-title" style="border-top: 1px solid #e2e8f0;">
                                <i class="fas fa-sticky-note" style="color: #20c997;"></i> Notes
                            </div>
                            <div class="px-3 py-3">
                                <div style="white-space: pre-wrap; font-size: .82rem; color: #334155; line-height: 1.6;">
                                    {{ $vehicle->notes }}
                                </div>
                                <button type="button" class="btn-filter-outline btn-sm mt-2" data-bs-toggle="modal" data-bs-target="#addNoteModal">
                                    <i class="fas fa-pen me-1"></i> Edit Note
                                </button>
                            </div>
                        @endif
                    </div>

                    <!-- ══ SERVICE HISTORY TAB ══ -->
                    <div class="tab-pane fade" id="history" role="tabpanel">
                        @if(isset($unifiedHistory) && count($unifiedHistory) > 0)
                            <div class="table-responsive">
                                <table class="table-fixit">
                                    <thead>
                                        <tr>
                                            <th>Transaction ID</th>
                                            <th>Date</th>
                                            <th>Type</th>
                                            <th>Service</th>
                                            <th>Description</th>
                                            <th class="text-end">Amount</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($unifiedHistory as $tx)
                                            <tr>
                                                <td><span class="badge" style="font-size:.7rem; background: var(--module-active); color: #fff;">{{ $tx->ref_number }}</span></td>
                                                <td><span style="font-size: 0.85rem;">@if($tx->created_at ?? $tx->archived_at){{ \Carbon\Carbon::parse($tx->created_at ?? $tx->archived_at)->format('M j, Y h:i A') }}@else—@endif</span></td>
                                                <td>
                                                    <span class="badge-custom badge-secondary">
                                                        @switch($tx->type)
                                                            @case('appointment') <i class="fas fa-calendar"></i> Appt @break
                                                            @case('inspection') <i class="fas fa-clipboard-check"></i> Inspection @break
                                                            @case('work_order') <i class="fas fa-wrench"></i> Work Order @break
                                                            @case('estimate') <i class="fas fa-file-invoice-dollar"></i> Estimate @break
                                                            @case('invoice') <i class="fas fa-receipt"></i> Invoice @break
                                                            @case('archived_inspection') <i class="fas fa-archive"></i> Archived @break
                                                            @default {{ ucfirst(str_replace('_', ' ', $tx->type)) }}
                                                        @endswitch
                                                    </span>
                                                </td>
                                                <td><span style="font-size: 0.85rem; font-weight: 500;">{{ $tx->service_type ?: 'General' }}</span></td>
                                                <td>
                                                    <span style="font-size: 0.82rem; color: #64748b;">{{ Str::limit($tx->description ?? '—', 50) }}</span>
                                                </td>
                                                <td class="text-end">
                                                    @if($tx->total_amount)
                                                        <span style="font-weight: 600;">${{ number_format($tx->total_amount, 2) }}</span>
                                                    @else
                                                        <span class="text-muted">—</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @php
                                                        $color = \App\Services\TransactionHistoryService::statusColor($tx->type, $tx->status ?? 'pending');
                                                    @endphp
                                                    <span class="status-badge {{ $color }}">{{ ucfirst($tx->status ?? 'pending') }}</span>
                                                    @if($tx->type === 'archived_inspection')
                                                        <span class="status-badge bg-secondary">Archived</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="empty-state-module py-4">
                                <div class="empty-state-icon">
                                    <i class="fas fa-history"></i>
                                </div>
                                <h5>No Service History</h5>
                                <p>No service history recorded for this vehicle.</p>
                            </div>
                        @endif
                    </div>

                    <!-- ══ NOTES TAB ══ -->
                    <div class="tab-pane fade" id="notes" role="tabpanel">
                        @if($vehicle->notes)
                            <div class="px-3 py-3">
                                <div class="note-card">
                                    <div class="note-card-header">
                                        <span class="badge-custom badge-note-type">
                                            <i class="fas fa-sticky-note me-1"></i> Vehicle Note
                                        </span>
                                        <small class="text-muted ms-2">Last updated {{ $vehicle->updated_at->format('M j, Y') }}</small>
                                    </div>
                                    <div class="note-card-body">
                                        {{ $vehicle->notes }}
                                    </div>
                                </div>
                                <button type="button" class="btn-filter-outline btn-sm" data-bs-toggle="modal" data-bs-target="#addNoteModal">
                                    <i class="fas fa-pen me-1"></i> Edit Note
                                </button>
                            </div>
                        @else
                            <div class="empty-state-module py-4">
                                <div class="empty-state-icon">
                                    <i class="fas fa-sticky-note"></i>
                                </div>
                                <h5>No Notes</h5>
                                <p>No notes recorded for this vehicle.</p>
                                <button type="button" class="btn-filter-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addNoteModal">
                                    <i class="fas fa-plus me-1"></i> Add Note
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Note Modal -->
<div class="modal fade" id="addNoteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('vehicles.update', $vehicle) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h6 class="modal-title fw-bold">
                        <i class="fas fa-sticky-note me-1" style="color: var(--module-active);"></i>
                        {{ $vehicle->notes ? 'Edit' : 'Add' }} Vehicle Note
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-0">
                        <label class="form-label small fw-semibold text-muted">Notes</label>
                        <textarea name="notes" rows="5" class="form-control" placeholder="Enter notes about this vehicle...">{{ $vehicle->notes }}</textarea>
                    </div>
                    <input type="hidden" name="redirect_to" value="show">
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-filter-primary btn-sm">
                        <i class="fas fa-save me-1"></i> Save Note
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop