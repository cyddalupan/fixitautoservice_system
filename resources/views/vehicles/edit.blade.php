@extends('layouts.app')

@section('title', 'Edit Vehicle - Fix-It Auto Services')
@section('body-class', 'page-vehicles')

@push('styles')
<style>
:root {
    --vhcl-primary: #dc2626;
    --vhcl-primary-dark: #991b1b;
}

.vhcl-form-wrap {
    background: #f4f6fa;
    min-height: 100vh;
    padding-top: .5rem;
    padding-bottom: 100px;
}
body.dark-mode .vhcl-form-wrap {
    background: #1a1d23;
}

/* ── Sticky Header ── */
.vhcl-sticky-header {
    position: sticky;
    top: 0;
    z-index: 1020;
    background: #fff;
    border-bottom: 1px solid #e5e7eb;
    padding: .6rem 1.5rem;
    margin: 0 -1.5rem .5rem;
    display: none;
    align-items: center;
    justify-content: space-between;
    box-shadow: 0 2px 8px rgba(0,0,0,.05);
}
body.dark-mode .vhcl-sticky-header {
    background: #2a2d35;
    border-bottom-color: #3a3d45;
}
.vhcl-sticky-header.show { display: flex; }
.vhcl-sticky-header .vhcl-sh-name {
    font-weight: 600;
    font-size: .9rem;
    color: #0f172a;
}
body.dark-mode .vhcl-sticky-header .vhcl-sh-name { color: #e4e6eb; }
.vhcl-sticky-header .vhcl-sh-plate {
    font-size: .78rem;
    color: #64748b;
}
.vhcl-sticky-header .vhcl-sh-status {
    font-size: .75rem;
    padding: .25rem .75rem;
    border-radius: 20px;
    background: #e8f5e9;
    color: #16a34a;
    font-weight: 500;
}
body.dark-mode .vhcl-sticky-header .vhcl-sh-status {
    background: #1a3a1a;
    color: #4ade80;
}

/* ── Section Cards ── */
.vhcl-section {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,.04);
    margin-bottom: 1.25rem;
    overflow: hidden;
    border: 1px solid #eef0f3;
}
body.dark-mode .vhcl-section {
    background: #2a2d35;
    border-color: #3a3d45;
}
.vhcl-section-header {
    padding: .85rem 1.25rem;
    font-size: .85rem;
    font-weight: 600;
    color: #1a2332;
    border-bottom: 1px solid #eef0f3;
    display: flex;
    align-items: center;
    gap: .55rem;
}
body.dark-mode .vhcl-section-header {
    color: #e4e6eb;
    border-bottom-color: #3a3d45;
}
.vhcl-section-header i {
    width: 18px;
    text-align: center;
}
.vhcl-section-body {
    padding: 1.25rem;
}

/* ── Labels & Inputs ── */
.vhcl-label {
    display: block;
    font-size: .78rem;
    font-weight: 600;
    color: #374151;
    margin-bottom: .35rem;
}
body.dark-mode .vhcl-label { color: #d1d5db; }

.vhcl-input {
    border-radius: 8px;
    border: 1px solid #d1d5db;
    font-size: .85rem;
    padding: .5rem .75rem;
    transition: border-color .15s ease, box-shadow .15s ease;
}
.vhcl-input:focus {
    border-color: var(--vhcl-primary);
    box-shadow: 0 0 0 3px rgba(220,38,38,.12);
    outline: none;
}
.input-group-fixit .vhcl-input {
    border-top-left-radius: 0;
    border-bottom-left-radius: 0;
}

/* ── Profile Photo (Sidebar) ── */
.vhcl-profile-photo {
    width: 160px;
    height: 160px;
    border-radius: 12px;
    overflow: hidden;
    margin: 0 auto .75rem;
    border: 3px solid #eef0f3;
    background: #f4f6fa;
    display: flex;
    align-items: center;
    justify-content: center;
}
body.dark-mode .vhcl-profile-photo {
    border-color: #3a3d45;
    background: #22252b;
}

/* ── Customer Info Banner ── */
.vhcl-owner-banner {
    background: linear-gradient(135deg, #fef2f2 0%, #fff5f5 100%);
    border: 1px solid #fecaca;
    border-radius: 10px;
    padding: .85rem 1rem;
    display: flex;
    align-items: center;
    gap: .75rem;
    margin-bottom: 1rem;
}
body.dark-mode .vhcl-owner-banner {
    background: #2a1a1a;
    border-color: #5a2a2a;
}
.vhcl-owner-banner i {
    font-size: 1.2rem;
    color: #dc2626;
}
.vhcl-owner-banner .vhcl-owner-info {
    font-size: .82rem;
    color: #991b1b;
    flex: 1;
}
body.dark-mode .vhcl-owner-banner .vhcl-owner-info { color: #fca5a5; }
.vhcl-owner-banner .vhcl-owner-info strong { font-weight: 600; }
.vhcl-owner-banner .vhcl-owner-info a {
    color: #dc2626;
    text-decoration: underline;
    text-underline-offset: 2px;
}
.vhcl-owner-banner .vhcl-owner-info a:hover { color: #991b1b; }

/* ── Customer Search Results ── */
.customer-search-result {
    padding: .65rem .85rem;
    border-bottom: 1px solid #f1f5f9;
    cursor: pointer;
    transition: background .1s;
    display: flex;
    align-items: center;
    gap: .75rem;
}
.customer-search-result:last-child { border-bottom: none; }
.customer-search-result:hover { background: #fff7ed; }
.customer-search-result .csr-name {
    font-weight: 600;
    font-size: .82rem;
    color: #0f172a;
}
.customer-search-result .csr-meta {
    font-size: .72rem;
    color: #94a3b8;
}
.customer-search-result .csr-phone {
    font-size: .72rem;
    color: #64748b;
}

/* ── Selected Customer Card ── */
.vhcl-selected-customer {
    display: flex;
    align-items: center;
    gap: .85rem;
    padding: .75rem 1rem;
    background: #f0fdf4;
    border: 1px solid #bbf7d0;
    border-radius: 10px;
    margin-top: .65rem;
}
body.dark-mode .vhcl-selected-customer {
    background: #0a2a0a;
    border-color: #166534;
}
.vhcl-selected-customer .vsc-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #16a34a;
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: .9rem;
    flex-shrink: 0;
}
.vhcl-selected-customer .vsc-info { flex: 1; }
.vhcl-selected-customer .vsc-name {
    font-weight: 600;
    font-size: .82rem;
    color: #0f172a;
}
body.dark-mode .vhcl-selected-customer .vsc-name { color: #e4e6eb; }
.vhcl-selected-customer .vsc-detail {
    font-size: .72rem;
    color: #64748b;
}

/* ── VIN Tooltip ── */
.vin-tooltip {
    display: inline-block;
    margin-left: .35rem;
    color: #94a3b8;
    cursor: help;
    font-size: .75rem;
}
.vin-tooltip:hover { color: #64748b; }

/* ── Dark mode overrides for autocomplete ── */
body.dark-mode .ui-autocomplete {
    background: #2a2d35;
    border-color: #3a3d45;
    color: #e4e6eb;
}
body.dark-mode .ui-menu-item {
    border-bottom-color: #3a3d45;
}
body.dark-mode .ui-menu-item:hover,
body.dark-mode .ui-state-focus {
    background: #374151;
    color: #e4e6eb;
}

/* ── Additional Dark Mode Overrides ── */
body.dark-mode .vhcl-input {
    background: #22252b;
    border-color: #3a3d45;
    color: #e4e6eb;
}
body.dark-mode .vhcl-input::placeholder {
    color: #6b7280;
}
body.dark-mode .vhcl-input:focus {
    background: #22252b;
    border-color: var(--vhcl-primary);
}
body.dark-mode .input-group-fixit .input-group-text {
    background: #2a2d35;
    border-color: #3a3d45;
    color: #9ca3af;
}
body.dark-mode .form-text {
    color: #6b7280 !important;
}
body.dark-mode .invalid-feedback {
    color: #f87171;
}
body.dark-mode .customer-search-result {
    border-bottom-color: #3a3d45;
}
body.dark-mode .customer-search-result:hover {
    background: #2a2a2a;
}
body.dark-mode .customer-search-result .csr-name {
    color: #e4e6eb;
}
body.dark-mode .customer-search-result .csr-meta {
    color: #6b7280;
}
body.dark-mode .customer-search-result .csr-phone {
    color: #6b7280;
}
body.dark-mode #customerSearchResults {
    background: #2a2d35 !important;
    border-color: #3a3d45 !important;
}
body.dark-mode #selectedCustomerInfo .vsc-detail {
    color: #9ca3af;
}
body.dark-mode .btn-filter-outline {
    color: #d1d5db;
    border-color: #4b5563;
}
body.dark-mode .btn-filter-outline:hover {
    background: #374151;
    color: #fff;
}
body.dark-mode .vin-tooltip {
    color: #6b7280;
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
body.dark-mode .main-card-body .btn-outline-primary {
    color: #60a5fa;
    border-color: #60a5fa;
}
body.dark-mode .main-card-body .btn-outline-secondary {
    color: #9ca3af;
    border-color: #4b5563;
}
body.dark-mode .page-module-header h1 {
    color: #e4e6eb;
}
body.dark-mode .page-module-header .breadcrumb {
    color: #9ca3af;
}
body.dark-mode .page-module-header .breadcrumb .breadcrumb-item.active {
    color: #d1d5db;
}
body.dark-mode .page-module-header .breadcrumb .breadcrumb-item + .breadcrumb-item::before {
    color: #6b7280;
}
body.dark-mode select.vhcl-input option {
    background: #2a2d35;
    color: #e4e6eb;
}

/* ── Responsive ── */
@media (max-width: 767.98px) {
    .vhcl-form-wrap { padding-top: 0; }
    .vhcl-profile-photo { width: 130px; height: 130px; }
}
</style>
@endpush

@section('content')
<div class="vhcl-form-wrap">
<div class="container-fluid">

    <!-- ══ PAGE MODULE HEADER ══ -->
    <div class="page-module-header">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="module-icon" style="background: linear-gradient(135deg, #dc2626, #991b1b);">
                    <i class="fas fa-car"></i>
                </div>
                <div>
                    <h1 class="h4 mb-1" style="font-weight: 700;">Edit Vehicle</h1>
                    <ol class="breadcrumb m-0 p-0" style="background: none; font-size: 0.8rem;">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('vehicles.index') }}">Vehicles</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('vehicles.show', $vehicle) }}">{{ $vehicle->full_description ?? $vehicle->make.' '.$vehicle->model }}</a></li>
                        <li class="breadcrumb-item active">Edit</li>
                    </ol>
                </div>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('vehicles.show', $vehicle) }}" class="btn-filter-outline">
                    <i class="fas fa-times me-1"></i> Cancel
                </a>
                <a href="{{ route('vehicles.show', $vehicle) }}" class="btn-filter-outline">
                    <i class="fas fa-eye me-1"></i> View Vehicle
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

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong><i class="fas fa-exclamation-triangle me-1"></i> There were errors:</strong>
            <ul class="mb-0 mt-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <form method="POST" action="{{ route('vehicles.update', $vehicle) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="row g-4">

            <!-- ══ LEFT COLUMN: Main Form ══ -->
            <div class="col-lg-8">

                {{-- ───── Section 1: Vehicle Information ───── --}}
                <div class="vhcl-section">
                    <div class="vhcl-section-header">
                        <i class="fas fa-car" style="color: var(--vhcl-primary);"></i>
                        Vehicle Information
                    </div>
                    <div class="vhcl-section-body">
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="license_plate" class="vhcl-label">License Plate</label>
                                <div class="input-group-fixit">
                                    <span class="input-group-text"><i class="fas fa-tag"></i></span>
                                    <input type="text" class="form-control vhcl-input @error('license_plate') is-invalid @enderror"
                                           id="license_plate" name="license_plate"
                                           value="{{ old('license_plate', $vehicle->license_plate) }}"
                                           placeholder="e.g., ABC 123" style="text-transform: uppercase;">
                                </div>
                                <div class="form-text" style="font-size: .7rem;">License plates are auto-formatted to uppercase</div>
                                @error('license_plate')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="vehicle_type" class="vhcl-label">Vehicle Type <span class="text-danger">*</span></label>
                                <div class="input-group-fixit">
                                    <span class="input-group-text"><i class="fas fa-truck"></i></span>
                                    <select class="form-select vhcl-input @error('vehicle_type') is-invalid @enderror"
                                            id="vehicle_type" name="vehicle_type">
                                        <option value="">Select Type (Default: Car)</option>
                                        <option value="car" {{ old('vehicle_type', $vehicle->vehicle_type) == 'car' ? 'selected' : '' }}>Car</option>
                                        <option value="truck" {{ old('vehicle_type', $vehicle->vehicle_type) == 'truck' ? 'selected' : '' }}>Truck</option>
                                        <option value="suv" {{ old('vehicle_type', $vehicle->vehicle_type) == 'suv' ? 'selected' : '' }}>SUV</option>
                                        <option value="van" {{ old('vehicle_type', $vehicle->vehicle_type) == 'van' ? 'selected' : '' }}>Van</option>
                                        <option value="motorcycle" {{ old('vehicle_type', $vehicle->vehicle_type) == 'motorcycle' ? 'selected' : '' }}>Motorcycle</option>
                                        <option value="commercial" {{ old('vehicle_type', $vehicle->vehicle_type) == 'commercial' ? 'selected' : '' }}>Commercial</option>
                                    </select>
                                </div>
                                @error('vehicle_type')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label for="make" class="vhcl-label">Make / Brand <span class="text-danger">*</span></label>
                                <div class="input-group-fixit">
                                    <span class="input-group-text"><i class="fas fa-industry"></i></span>
                                    <input type="text" class="form-control vhcl-input @error('make') is-invalid @enderror"
                                           id="make" name="make" value="{{ old('make', $vehicle->make) }}"
                                           placeholder="e.g., Toyota" autocomplete="off" required>
                                </div>
                                @error('make')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="model" class="vhcl-label">Model <span class="text-danger">*</span></label>
                                <div class="input-group-fixit">
                                    <span class="input-group-text"><i class="fas fa-car-side"></i></span>
                                    <input type="text" class="form-control vhcl-input @error('model') is-invalid @enderror"
                                           id="model" name="model" value="{{ old('model', $vehicle->model) }}"
                                           placeholder="e.g., Camry" autocomplete="off" required>
                                </div>
                                @error('model')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="year" class="vhcl-label">Year <span class="text-danger">*</span></label>
                                <div class="input-group-fixit">
                                    <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                    <input type="number" class="form-control vhcl-input @error('year') is-invalid @enderror"
                                           id="year" name="year" value="{{ old('year', $vehicle->year) }}"
                                           min="1900" max="{{ date('Y') + 1 }}"
                                           placeholder="e.g., 2023" required>
                                </div>
                                @error('year')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="color" class="vhcl-label">Color</label>
                                <div class="input-group-fixit">
                                    <span class="input-group-text"><i class="fas fa-palette"></i></span>
                                    <input type="text" class="form-control vhcl-input @error('color') is-invalid @enderror"
                                           id="color" name="color" value="{{ old('color', $vehicle->color) }}"
                                           placeholder="e.g., Red" autocomplete="off">
                                </div>
                                @error('color')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="vin" class="vhcl-label">
                                    VIN / Chassis Number
                                    <span class="vin-tooltip" title="Vehicle Identification Number (VIN) is a unique 17-character code that identifies your vehicle.">
                                        <i class="fas fa-question-circle"></i>
                                    </span>
                                </label>
                                <div class="input-group-fixit">
                                    <span class="input-group-text"><i class="fas fa-barcode"></i></span>
                                    <input type="text" class="form-control vhcl-input @error('vin') is-invalid @enderror"
                                           id="vin" name="vin" value="{{ old('vin', $vehicle->vin) }}"
                                           placeholder="17-character VIN" maxlength="17" style="text-transform: uppercase;">
                                </div>
                                <div class="form-text" style="font-size: .7rem;">VIN is unique — duplicate entries will be rejected</div>
                                @error('vin')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ───── Section 2: Technical Details ───── --}}
                <div class="vhcl-section">
                    <div class="vhcl-section-header">
                        <i class="fas fa-cogs" style="color: var(--vhcl-primary);"></i>
                        Technical Details
                    </div>
                    <div class="vhcl-section-body">
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="engine_type" class="vhcl-label">Engine Type</label>
                                <div class="input-group-fixit">
                                    <span class="input-group-text"><i class="fas fa-engine"></i></span>
                                    <input type="text" class="form-control vhcl-input @error('engine_type') is-invalid @enderror"
                                           id="engine_type" name="engine_type"
                                           value="{{ old('engine_type', $vehicle->engine_type) }}"
                                           placeholder="e.g., 2.0L Turbo, V6">
                                </div>
                                @error('engine_type')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="engine_no" class="vhcl-label">Engine No.</label>
                                <div class="input-group-fixit">
                                    <span class="input-group-text"><i class="fas fa-hashtag"></i></span>
                                    <input type="text" class="form-control vhcl-input @error('engine_no') is-invalid @enderror"
                                           id="engine_no" name="engine_no"
                                           value="{{ old('engine_no', $vehicle->engine_no) }}"
                                           placeholder="Engine serial number">
                                </div>
                                @error('engine_no')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="transmission" class="vhcl-label">Transmission</label>
                                <div class="input-group-fixit">
                                    <span class="input-group-text"><i class="fas fa-cog"></i></span>
                                    <select class="form-select vhcl-input @error('transmission') is-invalid @enderror"
                                            id="transmission" name="transmission">
                                        <option value="">Select Transmission</option>
                                        <option value="Automatic" {{ old('transmission', $vehicle->transmission) == 'Automatic' ? 'selected' : '' }}>Automatic</option>
                                        <option value="Manual" {{ old('transmission', $vehicle->transmission) == 'Manual' ? 'selected' : '' }}>Manual</option>
                                        <option value="CVT" {{ old('transmission', $vehicle->transmission) == 'CVT' ? 'selected' : '' }}>CVT</option>
                                    </select>
                                </div>
                                @error('transmission')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="fuel_type" class="vhcl-label">Fuel Type</label>
                                <div class="input-group-fixit">
                                    <span class="input-group-text"><i class="fas fa-gas-pump"></i></span>
                                    <select class="form-select vhcl-input @error('fuel_type') is-invalid @enderror"
                                            id="fuel_type" name="fuel_type">
                                        <option value="">Select Fuel Type</option>
                                        <option value="Gasoline" {{ old('fuel_type', $vehicle->fuel_type) == 'Gasoline' ? 'selected' : '' }}>Gasoline</option>
                                        <option value="Diesel" {{ old('fuel_type', $vehicle->fuel_type) == 'Diesel' ? 'selected' : '' }}>Diesel</option>
                                        <option value="Hybrid" {{ old('fuel_type', $vehicle->fuel_type) == 'Hybrid' ? 'selected' : '' }}>Hybrid</option>
                                        <option value="Electric" {{ old('fuel_type', $vehicle->fuel_type) == 'Electric' ? 'selected' : '' }}>Electric</option>
                                        <option value="LPG" {{ old('fuel_type', $vehicle->fuel_type) == 'LPG' ? 'selected' : '' }}>LPG</option>
                                    </select>
                                </div>
                                @error('fuel_type')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="odometer" class="vhcl-label">Odometer / Mileage</label>
                                <div class="input-group-fixit">
                                    <span class="input-group-text"><i class="fas fa-tachometer-alt"></i></span>
                                    <input type="number" class="form-control vhcl-input @error('odometer') is-invalid @enderror"
                                           id="odometer" name="odometer"
                                           value="{{ old('odometer', $vehicle->odometer) }}"
                                           placeholder="e.g., 45000" min="0">
                                </div>
                                <div class="form-text" style="font-size: .7rem;">Current mileage in miles</div>
                                @error('odometer')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ───── Section 3: Ownership ───── --}}
                <div class="vhcl-section">
                    <div class="vhcl-section-header">
                        <i class="fas fa-user-tie" style="color: var(--vhcl-primary);"></i>
                        Ownership
                    </div>
                    <div class="vhcl-section-body">
                        @if($vehicle->customer)
                            {{-- Current owner banner --}}
                            <div class="vhcl-owner-banner">
                                <i class="fas fa-user-check"></i>
                                <div class="vhcl-owner-info">
                                    This vehicle belongs to
                                    <a href="{{ route('customers.show', $vehicle->customer) }}">
                                        <strong>{{ $vehicle->customer->first_name }} {{ $vehicle->customer->last_name }}</strong>
                                    </a>
                                    @if($vehicle->customer->phone)
                                        ({{ $vehicle->customer->phone }})
                                    @endif
                                    <div style="font-size: .72rem; margin-top: 2px; opacity: .85;">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Customer linked to this vehicle. Customer cannot be changed.
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" name="customer_id" value="{{ $vehicle->customer_id }}">
                        @else
                            {{-- Searchable customer selector --}}
                            <label class="vhcl-label">Link Customer <span class="text-danger">*</span></label>
                            <div class="input-group-fixit">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                                <input type="text" class="form-control vhcl-input"
                                       id="customerSearch" placeholder="Search customers by name, phone, or email..."
                                       autocomplete="off">
                            </div>
                            <div id="customerSearchResults" class="d-none" style="border:1px solid #e2e8f0;border-radius:8px;margin-top:.35rem;max-height:220px;overflow-y:auto;background:#fff;">
                            </div>
                            <input type="hidden" name="customer_id" id="customer_id" value="{{ old('customer_id', $vehicle->customer_id) }}">
                            <div id="selectedCustomerInfo" class="d-none">
                                <div class="vhcl-selected-customer">
                                    <div class="vsc-avatar" id="vscInitials">?</div>
                                    <div class="vsc-info">
                                        <div class="vsc-name" id="vscName">Customer Name</div>
                                        <div class="vsc-detail" id="vscPhone">Phone</div>
                                        <div class="vsc-detail" id="vscEmail">Email</div>
                                        <div class="vsc-detail" id="vscVehicles">0 vehicles</div>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-danger" id="clearCustomerBtn" title="Remove customer">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="form-text" style="font-size: .7rem;">Search and select a customer to link to this vehicle</div>
                            @error('customer_id')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        @endif
                    </div>
                </div>

                {{-- ───── Section 4: Additional Details ───── --}}
                <div class="vhcl-section">
                    <div class="vhcl-section-header">
                        <i class="fas fa-ellipsis-h" style="color: var(--vhcl-primary);"></i>
                        Additional Details
                    </div>
                    <div class="vhcl-section-body">
                        <div class="mb-4">
                            <label for="notes" class="vhcl-label">Notes</label>
                            <textarea class="form-control vhcl-input @error('notes') is-invalid @enderror"
                                      id="notes" name="notes" rows="4"
                                      placeholder="Any additional notes about this vehicle (service preferences, special parts, warnings, etc.)">{{ old('notes', $vehicle->notes) }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="is_active" class="vhcl-label">Is Active</label>
                                <div class="input-group-fixit">
                                    <span class="input-group-text"><i class="fas fa-circle"></i></span>
                                    <select class="form-select vhcl-input @error('is_active') is-invalid @enderror"
                                            id="is_active" name="is_active">
                                        <option value="1" {{ old('is_active', $vehicle->is_active ?? true) ? 'selected' : '' }}>Yes</option>
                                        <option value="0" {{ !(old('is_active', $vehicle->is_active ?? true)) ? 'selected' : '' }}>No</option>
                                    </select>
                                </div>
                                <div class="form-text" style="font-size: .7rem;">Inactive vehicles are hidden from most lists</div>
                                @error('is_active')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Submit --}}
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <small class="text-muted"><span class="text-danger">*</span> Required fields</small>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('vehicles.show', $vehicle) }}" class="btn-filter-outline">
                            <i class="fas fa-times me-1"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary px-4" style="background: linear-gradient(135deg, #dc2626, #991b1b); border: none;">
                            <i class="fas fa-save me-1"></i> Update Vehicle
                        </button>
                    </div>
                </div>
            </div>

            <!-- ══ RIGHT COLUMN: Sidebar ══ -->
            <div class="col-lg-4">

                {{-- Photo Card --}}
                <div class="main-card mb-4">
                    <div class="main-card-header">
                        <i class="fas fa-camera me-2" style="color: var(--module-active);"></i> Vehicle Photo
                    </div>
                    <div class="main-card-body px-3 py-3 text-center">
                        <div class="vhcl-profile-photo" id="vehicleEditPhotoPreview">
                            @if($vehicle->photo && $vehicle->photo_path)
                                <img src="{{ Storage::url($vehicle->photo_path) }}"
                                     alt="{{ $vehicle->full_description ?? $vehicle->make.' '.$vehicle->model }}"
                                     style="width:100%;height:100%;object-fit:cover;">
                            @else
                                <i class="fas fa-car" style="font-size: 3rem; color: #9ca3af;"></i>
                            @endif
                        </div>
                        <input type="file" class="d-none" id="vehiclePhotoInput" name="photo" accept="image/*">
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="document.getElementById('vehiclePhotoInput').click();">
                            <i class="fas fa-upload me-1"></i>
                            @if($vehicle->photo && $vehicle->photo_path)
                                Replace Photo
                            @else
                                Upload Photo
                            @endif
                        </button>
                        @if($vehicle->photo && $vehicle->photo_path)
                            <div class="mt-2">
                                <label class="btn btn-outline-danger btn-sm" id="vehicleRemovePhotoBtn" style="cursor:pointer;font-size:.72rem;">
                                    <i class="fas fa-trash me-1"></i> Remove
                                </label>
                            </div>
                        @endif
                        <div id="vehiclePhotoPreviewContainer" class="d-none mt-3">
                            <div class="card">
                                <div class="card-body p-2 text-start">
                                    <p class="mb-1 small fw-bold">New Photo Preview:</p>
                                    <img id="vehiclePhotoPreviewImg" src="#" alt="Preview"
                                         class="img-thumbnail" style="max-width: 100%; max-height: 140px; object-fit: contain;">
                                    <p class="mb-0 text-muted small mt-1" id="vehiclePhotoFileName"></p>
                                    <p class="mb-0 text-muted small" id="vehiclePhotoFileSize"></p>
                                </div>
                            </div>
                        </div>
                        <div class="form-text mt-2" style="font-size: .68rem;">
                            <i class="fas fa-info-circle me-1"></i>
                            JPG, PNG, GIF, WebP. Max 5MB.
                        </div>
                    </div>
                </div>

                {{-- Vehicle Info Card --}}
                <div class="main-card mb-4">
                    <div class="main-card-header">
                        <i class="fas fa-info-circle me-2" style="color: var(--module-active);"></i> Vehicle Info
                    </div>
                    <div class="main-card-body px-3 py-3">
                        <div class="customer-details-section">
                            <div class="detail-label">Vehicle ID</div>
                            <div class="detail-row">
                                <i class="fas fa-hashtag detail-icon"></i>
                                <code>#{{ $vehicle->id }}</code>
                            </div>
                        </div>
                        <div class="customer-details-section">
                            <div class="detail-label">Created</div>
                            <div class="detail-row">
                                <i class="fas fa-calendar-plus detail-icon"></i>
                                <span>{{ $vehicle->created_at->format('F j, Y') }}</span>
                            </div>
                        </div>
                        <div class="customer-details-section">
                            <div class="detail-label">Last Updated</div>
                            <div class="detail-row">
                                <i class="fas fa-clock detail-icon"></i>
                                <span>{{ $vehicle->updated_at->diffForHumans() }}</span>
                            </div>
                        </div>
                        <div class="customer-details-section" style="border-bottom: none;">
                            <div class="detail-label">Status</div>
                            <div class="detail-row">
                                <i class="fas fa-{{ $vehicle->is_active ? 'check-circle' : 'times-circle' }} detail-icon"></i>
                                <span class="badge bg-{{ $vehicle->is_active ? 'success' : 'secondary' }}">
                                    {{ $vehicle->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Quick Links --}}
                <div class="main-card">
                    <div class="main-card-header">
                        <i class="fas fa-link me-2" style="color: var(--module-active);"></i> Quick Links
                    </div>
                    <div class="main-card-body px-3 py-3">
                        <div class="d-grid gap-2">
                            <a href="{{ route('vehicles.show', $vehicle) }}" class="btn btn-outline-primary btn-sm text-start">
                                <i class="fas fa-eye me-2"></i> View Vehicle Profile
                            </a>
                            <a href="{{ route('vehicles.index') }}" class="btn btn-outline-secondary btn-sm text-start">
                                <i class="fas fa-arrow-left me-2"></i> Back to Vehicles List
                            </a>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </form>
</div>
</div>

{{-- ══ STICKY HEADER ══ --}}
<div class="vhcl-sticky-header" id="vhclStickyHeader">
    <div>
        <div class="vhcl-sh-name">{{ $vehicle->full_description ?? $vehicle->make.' '.$vehicle->model }}</div>
        <div class="vhcl-sh-plate">{{ $vehicle->license_plate ? $vehicle->license_plate : 'No plate' }}</div>
    </div>
    <div class="vhcl-sh-status">
        <i class="fas fa-pen me-1"></i> Editing
    </div>
</div>

@endsection

@push('scripts')
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
<link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<script>
$(document).ready(function() {

    /* ── Sticky Header ── */
    const vhclHeader = $('#vhclStickyHeader');
    const vhclFormWrap = $('.vhcl-form-wrap');
    let vhclHeaderVisible = false;
    const vhclCheckHeader = function() {
        const scrollTop = $(window).scrollTop();
        if (scrollTop > 250 && !vhclHeaderVisible) {
            vhclHeader.addClass('show');
            vhclHeaderVisible = true;
            vhclFormWrap.css('margin-top', vhclHeader.outerHeight() + 'px');
        } else if (scrollTop <= 250 && vhclHeaderVisible) {
            vhclHeader.removeClass('show');
            vhclHeaderVisible = false;
            vhclFormWrap.css('margin-top', '');
        }
    };
    $(window).on('scroll', vhclCheckHeader);

    /* ── Make Autocomplete ── */
    $('#make').autocomplete({
        source: function(request, response) {
            $.ajax({
                url: '{{ route("api.vehicle-brands") }}',
                data: { q: request.term },
                success: function(data) {
                    response($.map(data, function(item) {
                        return {
                            label: item.name,
                            value: item.name
                        };
                    }));
                }
            });
        },
        minLength: 1,
        select: function(event, ui) {
            $('#make').val(ui.item.value);
            $('#model').autocomplete('search', '');
            return false;
        }
    });

    /* ── Model Autocomplete ── */
    $('#model').autocomplete({
        source: function(request, response) {
            const make = $('#make').val();
            $.ajax({
                url: '{{ route("api.vehicle-models") }}',
                data: {
                    q: request.term,
                    brand: make
                },
                success: function(data) {
                    response($.map(data, function(item) {
                        return {
                            label: item.name,
                            value: item.name
                        };
                    }));
                }
            });
        },
        minLength: 1
    });

    /* ── Color Autocomplete ── */
    const commonColors = [
        'Black', 'White', 'Silver', 'Gray', 'Red', 'Blue', 'Green', 'Yellow',
        'Orange', 'Brown', 'Beige', 'Gold', 'Maroon', 'Navy', 'Teal', 'Purple',
        'Pink', 'Bronze', 'Champagne', 'Charcoal', 'Crimson', 'Cyan', 'Indigo',
        'Ivory', 'Khaki', 'Lavender', 'Lime', 'Magenta', 'Mint', 'Olive',
        'Peach', 'Plum', 'Rose', 'Ruby', 'Rust', 'Sapphire', 'Tan', 'Turquoise',
        'Violet', 'Wine'
    ];
    $('#color').autocomplete({
        source: commonColors,
        minLength: 0
    }).focus(function() {
        $(this).autocomplete('search', '');
    });

    /* ── Photo Upload & Preview ── */
    $('#vehiclePhotoInput').on('change', function() {
        const file = this.files[0];
        if (!file) return;

        // Validate file size (5MB = 5 * 1024 * 1024)
        const maxSize = 5 * 1024 * 1024;
        if (file.size > maxSize) {
            alert('File size exceeds 5MB limit. Please select a smaller file.');
            $(this).val('');
            return;
        }

        // Validate file type
        const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp'];
        if (!allowedTypes.includes(file.type)) {
            alert('Invalid file type. Please upload a JPG, PNG, GIF, or WebP image.');
            $(this).val('');
            return;
        }

        // Show preview
        const reader = new FileReader();
        reader.onload = function(e) {
            $('#vehiclePhotoPreviewImg').attr('src', e.target.result);
            $('#vehiclePhotoFileName').text('File: ' + file.name);
            const fileSize = (file.size / 1024).toFixed(1) + ' KB';
            $('#vehiclePhotoFileSize').text('Size: ' + fileSize);
            $('#vehiclePhotoPreviewContainer').removeClass('d-none');
        };
        reader.readAsDataURL(file);
    });

    /* ── Customer Search (when no existing customer) ── */
    let customerSearchTimeout;
    $('#customerSearch').on('input', function() {
        clearTimeout(customerSearchTimeout);
        const q = $(this).val().trim();
        if (q.length < 2) {
            $('#customerSearchResults').addClass('d-none').empty();
            return;
        }
        customerSearchTimeout = setTimeout(function() {
            $.ajax({
                url: '{{ route("api.customers.search") }}',
                data: { q: q },
                success: function(data) {
                    const results = $('#customerSearchResults');
                    results.empty().removeClass('d-none');
                    if (data.length === 0) {
                        results.append('<div class="customer-search-result" style="cursor:default;color:#94a3b8;">No customers found</div>');
                        return;
                    }
                    $.each(data, function(i, customer) {
                        const name = (customer.first_name || '') + ' ' + (customer.last_name || '');
                        const phone = customer.phone || '';
                        const email = customer.email || '';
                        const vehicles = customer.vehicles_count !== undefined ? customer.vehicles_count : (customer.vehicles ? customer.vehicles.length : 0);
                        const initials = (customer.first_name ? customer.first_name.charAt(0).toUpperCase() : '') + (customer.last_name ? customer.last_name.charAt(0).toUpperCase() : '');
                        results.append(
                            '<div class="customer-search-result" data-id="' + customer.id + '" data-name="' + $('<span>').text(name).html() + '" data-phone="' + $('<span>').text(phone).html() + '" data-email="' + $('<span>').text(email).html() + '" data-vehicles="' + vehicles + '" data-initials="' + initials + '">' +
                                '<div style="width:36px;height:36px;border-radius:50%;background:#dc2626;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.78rem;flex-shrink:0;">' + initials + '</div>' +
                                '<div style="flex:1;">' +
                                    '<div class="csr-name">' + $('<span>').text(name).html() + '</div>' +
                                    '<div class="csr-meta">' + (phone ? $('<span>').text(phone).html() + ' · ' : '') + vehicles + ' vehicle' + (vehicles !== 1 ? 's' : '') + '</div>' +
                                '</div>' +
                            '</div>'
                        );
                    });
                }
            });
        }, 300);
    });

    /* ── Select Customer ── */
    $(document).on('click', '.customer-search-result', function() {
        const id = $(this).data('id');
        const name = $(this).data('name');
        const phone = $(this).data('phone');
        const email = $(this).data('email');
        const vehicles = $(this).data('vehicles');
        const initials = $(this).data('initials');

        $('#customer_id').val(id);
        $('#vscInitials').text(initials);
        $('#vscName').text(name);
        $('#vscPhone').text(phone ? '📞 ' + phone : '');
        $('#vscEmail').text(email ? '✉️ ' + email : '');
        $('#vscVehicles').text(vehicles + ' vehicle' + (vehicles !== 1 ? 's' : ''));
        $('#selectedCustomerInfo').removeClass('d-none');
        $('#customerSearchResults').addClass('d-none').empty();
        $('#customerSearch').val(name).prop('disabled', true);
    });

    /* ── Clear Selected Customer ── */
    $('#clearCustomerBtn').on('click', function() {
        $('#customer_id').val('');
        $('#selectedCustomerInfo').addClass('d-none');
        $('#customerSearch').val('').prop('disabled', false).focus();
    });

});
</script>
@endpush