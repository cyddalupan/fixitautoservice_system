@extends('layouts.app')

@section('content')
@include('partials.customer-process-assets')
<div class="container-fluid px-4 py-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1" style="color: #1a237e;">
                <i class="fas fa-search me-2"></i>Inspection #{{ $inspection->id }}
            </h4>
            <p class="text-muted mb-0">
                <i class="fas fa-calendar me-1"></i>
                {{ $inspection->created_at ? $inspection->created_at->format('F j, Y g:i A') : 'N/A' }}
                &middot; {{ is_string($inspection->inspection_type) ? ucfirst(str_replace('_', ' ', $inspection->inspection_type)) : 'Multi-Type' }}
            </p>
        </div>
        <div class="d-flex gap-2">
            <div class="dropdown">
                <button class="btn btn-success dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="fas fa-forward me-1"></i>Proceed To
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <a class="dropdown-item" href="{{ route('estimates.create', ['customer_id' => $inspection->customer_id, 'vehicle_id' => $inspection->vehicle_id]) }}">
                            <i class="fas fa-file-invoice me-2 text-primary"></i> Create Estimate
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="{{ route('work-orders.create', ['customer_id' => $inspection->customer_id, 'vehicle_id' => $inspection->vehicle_id, 'inspection_id' => $inspection->id]) }}">
                            <i class="fas fa-wrench me-2 text-warning"></i> Create Job Order
                        </a>
                    </li>
                </ul>
            </div>
            <a href="{{ route('inspections.edit', $inspection) }}" class="btn btn-primary">
                <i class="fas fa-edit me-1"></i>Edit
            </a>
            <a href="{{ route('inspections.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>Back
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Section Navigation Pills -->
    <ul class="nav nav-pills section-nav mb-4" id="detailTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="overview-tab" data-bs-toggle="pill" data-bs-target="#overview" type="button" role="tab">
                <i class="fas fa-info-circle me-1"></i>Overview
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="findings-tab" data-bs-toggle="pill" data-bs-target="#findings" type="button" role="tab">
                <i class="fas fa-clipboard-check me-1"></i>Findings
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="items-tab" data-bs-toggle="pill" data-bs-target="#items" type="button" role="tab">
                <i class="fas fa-list me-1"></i>Inspection Items
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="photos-tab" data-bs-toggle="pill" data-bs-target="#photos" type="button" role="tab">
                <i class="fas fa-camera me-1"></i>Photos
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="timeline-tab" data-bs-toggle="pill" data-bs-target="#timeline" type="button" role="tab">
                <i class="fas fa-history me-1"></i>Timeline
            </button>
        </li>
    </ul>

    <div class="tab-content">

        <!-- === OVERVIEW TAB === -->
        <div class="tab-pane fade show active" id="overview" role="tabpanel">
            <div class="row g-3">
                <!-- Customer Summary -->
                @if($inspection->customer)
                    @include('partials.customer-summary-card', ['customer' => $inspection->customer])
                @endif

                <!-- Left Column -->
                <div class="col-lg-8">
                    <!-- Basic Info -->
                    <div class="form-section">
                        <div class="form-section-header no-collapse">
                            <h6><i class="fas fa-info-circle"></i>Inspection Details</h6>
                            <span class="badge bg-{{ $inspection->status_badge ?? 'secondary' }}">
                                {{ ucfirst($inspection->inspection_status ?? 'unknown') }}
                            </span>
                        </div>
                        <div class="form-section-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="text-muted small text-uppercase">Type</label>
                                    <p class="fw-semibold mb-0">{{ is_string($inspection->inspection_type) ? ucfirst(str_replace('_', ' ', $inspection->inspection_type)) : 'Multi-Type' }}</p>
                                </div>
                                <div class="col-md-4">
                                    <label class="text-muted small text-uppercase">Status</label>
                                    <p class="fw-semibold mb-0">
                                        <span class="badge bg-{{ $inspection->status_badge ?? 'secondary' }}">
                                            {{ ucfirst($inspection->inspection_status ?? 'unknown') }}
                                        </span>
                                    </p>
                                </div>
                                <div class="col-md-4">
                                    <label class="text-muted small text-uppercase">Mileage</label>
                                    <p class="fw-semibold mb-0">
                                        <i class="fas fa-tachometer-alt me-1 text-primary"></i>
                                        {{ $inspection->vehicle_mileage ? number_format($inspection->vehicle_mileage) . ' mi' : 'N/A' }}
                                    </p>
                                </div>
                                <div class="col-md-4">
                                    <label class="text-muted small text-uppercase">Service Type</label>
                                    <p class="fw-semibold mb-0">
                                        @if($inspection->service_type)
                                            <span class="badge bg-soft-primary text-primary">
                                                @php
                                                    $st = $inspection->service_type;
                                                    $stArr = is_string($st) && str_starts_with($st, '[') ? json_decode($st, true) : (is_array($st) ? $st : [$st]);
                                                    $stArr = array_filter((array)$stArr);
                                                @endphp
                                                @if(!empty($stArr))
                                                    @foreach($stArr as $stItem)
                                                        <span class="badge bg-soft-primary text-primary me-1" style="font-weight:500;font-size:.75rem">
                                                            {{ config('service-types.list.' . $stItem . '.name', ucfirst(str_replace('_', ' ', $stItem))) }}
                                                        </span>
                                                    @endforeach
                                                @else
                                                    <span class="text-muted">N/A</span>
                                                @endif
                                            </span>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Vehicle Info -->
                    <div class="form-section">
                        <div class="form-section-header no-collapse">
                            <h6><i class="fas fa-car"></i>Vehicle Information</h6>
                        </div>
                        <div class="form-section-body">
                            @if($inspection->vehicle)
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="text-muted small text-uppercase">Vehicle</label>
                                        <p class="fw-semibold mb-0">{{ $inspection->vehicle->year }} {{ $inspection->vehicle->make }} {{ $inspection->vehicle->model }}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="text-muted small text-uppercase">License Plate</label>
                                        <p class="fw-semibold mb-0">{{ $inspection->vehicle->license_plate ?? 'N/A' }}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="text-muted small text-uppercase">VIN</label>
                                        <p class="fw-semibold mb-0 text-monospace">{{ $inspection->vehicle->vin ?? 'N/A' }}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="text-muted small text-uppercase">Color</label>
                                        <p class="fw-semibold mb-0">{{ $inspection->vehicle->color ?? 'N/A' }}</p>
                                    </div>
                                </div>
                            @else
                                <p class="text-muted mb-0"><i class="fas fa-exclamation-circle me-1"></i>No vehicle assigned</p>
                            @endif
                        </div>
                    </div>

                    <!-- Assigned Team -->
                    <div class="form-section">
                        <div class="form-section-header no-collapse">
                            <h6><i class="fas fa-users"></i>Assigned Team</h6>
                        </div>
                        <div class="form-section-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="text-muted small text-uppercase">Technician</label>
                                    <p class="fw-semibold mb-0">
                                        <i class="fas fa-user-cog me-1 text-primary"></i>
                                        {{ $inspection->technician->name ?? 'Unassigned' }}
                                    </p>
                                    @if($inspection->technicians->isNotEmpty())
                                        <div class="mt-1">
                                            @foreach($inspection->technicians as $tech)
                                                <span class="badge bg-light text-dark me-1" style="font-weight:500;">
                                                    <i class="fas fa-user me-1"></i>{{ $tech->name }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                                <div class="col-md-6">
                                    <label class="text-muted small text-uppercase">Service Advisor</label>
                                    <p class="fw-semibold mb-0">
                                        <i class="fas fa-user-tie me-1 text-primary"></i>
                                        {{ $inspection->advisor->name ?? 'Unassigned' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Customer Concerns -->
                    @if($inspection->customer_concerns)
                    <div class="form-section">
                        <div class="form-section-header no-collapse">
                            <h6><i class="fas fa-question-circle"></i>Customer Concerns</h6>
                        </div>
                        <div class="form-section-body">
                            <p class="mb-0">{{ $inspection->customer_concerns }}</p>
                        </div>
                    </div>
                    @endif

                    <!-- Technician Findings -->
                    @if($inspection->technician_notes)
                    <div class="form-section">
                        <div class="form-section-header no-collapse">
                            <h6><i class="fas fa-stethoscope"></i>Technician Findings</h6>
                        </div>
                        <div class="form-section-body">
                            <p class="mb-0" style="white-space: pre-wrap;">{{ $inspection->technician_notes }}</p>
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Right Column -->
                <div class="col-lg-4">
                    <!-- Status Card -->
                    <div class="form-section">
                        <div class="form-section-header no-collapse">
                            <h6><i class="fas fa-flag"></i>Status Timeline</h6>
                        </div>
                        <div class="form-section-body">
                            <div class="mb-3">
                                <span class="badge bg-{{ $inspection->status_badge ?? 'secondary' }} fs-6 px-3 py-2">
                                    {{ ucfirst($inspection->inspection_status ?? 'unknown') }}
                                </span>
                            </div>
                            @php
                                $statusLog = [];
                                if($inspection->inspection_started_at) $statusLog[] = ['label' => 'Started', 'time' => $inspection->inspection_started_at, 'icon' => 'fa-play', 'color' => 'info'];
                                if($inspection->inspection_completed_at) $statusLog[] = ['label' => 'Completed', 'time' => $inspection->inspection_completed_at, 'icon' => 'fa-check', 'color' => 'success'];
                                if($inspection->report_generated_at) $statusLog[] = ['label' => 'Report Generated', 'time' => $inspection->report_generated_at, 'icon' => 'fa-file-alt', 'color' => 'primary'];
                            @endphp
                            @if(count($statusLog) > 0)
                                @foreach($statusLog as $entry)
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="text-{{ $entry['color'] }} me-2"><i class="fas {{ $entry['icon'] }}"></i></div>
                                        <div>
                                            <small class="text-muted d-block">{{ $entry['label'] }}</small>
                                            <small>{{ $entry['time']->format('M j, Y g:i A') }}</small>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>

                    <!-- Inspection Notes -->
                    @if($inspection->inspection_notes)
                    <div class="form-section">
                        <div class="form-section-header no-collapse">
                            <h6><i class="fas fa-sticky-note"></i>Inspection Notes</h6>
                        </div>
                        <div class="form-section-body">
                            <p class="mb-0 small" style="white-space: pre-wrap;">{{ $inspection->inspection_notes }}</p>
                        </div>
                    </div>
                    @endif

                    <!-- Quick Stats -->
                    <div class="form-section">
                        <div class="form-section-header no-collapse">
                            <h6><i class="fas fa-chart-bar"></i>Summary</h6>
                        </div>
                        <div class="form-section-body">
                            <div class="d-flex justify-content-around text-center">
                                <div>
                                    <div class="fw-bold fs-5 text-primary">{{ $inspection->items->count() }}</div>
                                    <small class="text-muted">Items</small>
                                </div>
                                <div>
                                    <div class="fw-bold fs-5 text-success">
                                        {{ $inspection->items->where('status', 'passed')->count() }}
                                    </div>
                                    <small class="text-muted">Passed</small>
                                </div>
                                <div>
                                    <div class="fw-bold fs-5 text-danger">
                                        {{ $inspection->items->where('status', 'failed')->count() }}
                                    </div>
                                    <small class="text-muted">Failed</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Related Records -->
                    <div class="form-section">
                        <div class="form-section-header no-collapse">
                            <h6><i class="fas fa-link"></i>Related Records</h6>
                        </div>
                        <div class="form-section-body">
                            @php
                                $relations = [];
                                if($inspection->workOrder) $relations[] = ['name' => 'Work Order', 'icon' => 'fa-clipboard-list', 'route' => route('work_orders.show', $inspection->workOrder), 'color' => 'primary'];
                                if($inspection->appointment) $relations[] = ['name' => 'Appointment', 'icon' => 'fa-calendar-check', 'route' => route('appointments.show', $inspection->appointment), 'color' => 'info'];
                                if(isset($inspection->estimate) && $inspection->estimate) $relations[] = ['name' => 'Estimate', 'icon' => 'fa-file-invoice-dollar', 'route' => route('estimates.show', $inspection->estimate), 'color' => 'success'];
                            @endphp
                            @if(count($relations) > 0)
                                @foreach($relations as $rel)
                                    <a href="{{ $rel['route'] }}" class="btn btn-outline-{{ $rel['color'] }} btn-sm w-100 mb-2 text-start">
                                        <i class="fas {{ $rel['icon'] }} me-1"></i>{{ $rel['name'] }}
                                    </a>
                                @endforeach
                            @else
                                <p class="text-muted mb-0 small">No related records.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- === FINDINGS TAB === -->
        <div class="tab-pane fade" id="findings" role="tabpanel">
            <div class="row g-3">
                <div class="col-12">
                    @if($inspection->customer_concerns)
                    <div class="form-section mb-3">
                        <div class="form-section-header no-collapse">
                            <h6><i class="fas fa-question-circle"></i>Customer Concerns</h6>
                        </div>
                        <div class="form-section-body">
                            <p class="mb-0" style="white-space: pre-wrap;">{{ $inspection->customer_concerns }}</p>
                        </div>
                    </div>
                    @endif
                    @if($inspection->inspection_notes)
                    <div class="form-section mb-3">
                        <div class="form-section-header no-collapse">
                            <h6><i class="fas fa-sticky-note"></i>Additional Notes</h6>
                        </div>
                        <div class="form-section-body">
                            <p class="mb-0" style="white-space: pre-wrap;">{{ $inspection->inspection_notes }}</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Quick Add Bar -->
            <div class="findings-quick-add py-3 px-3 mb-3 rounded-3" style="background: linear-gradient(135deg, rgba(26,35,126,0.05) 0%, rgba(13,71,161,0.1) 100%); border: 1px solid rgba(26,35,126,0.15); position: sticky; top: 0; z-index: 10;">
                <div class="row g-2 align-items-end">
                    <div class="col-12 col-md-3">
                        <label class="small fw-semibold text-muted mb-1"><i class="fas fa-tag me-1"></i>Category</label>
                        <select id="quick-category" class="form-select form-select-sm">
                            <option value="Engine">Engine</option>
                            <option value="Brakes">Brakes</option>
                            <option value="Suspension">Suspension</option>
                            <option value="Electrical">Electrical</option>
                            <option value="Cooling">Cooling</option>
                            <option value="Transmission">Transmission</option>
                            <option value="Tires">Tires</option>
                            <option value="Aircon">Aircon</option>
                            <option value="Steering">Steering</option>
                            <option value="Body / Exterior">Body / Exterior</option>
                            <option value="Safety">Safety</option>
                            <option value="Maintenance">Maintenance</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="small fw-semibold text-muted mb-1"><i class="fas fa-search me-1"></i>Issue Title</label>
                        <div class="position-relative">
                            <input type="text" id="quick-issue-title" class="form-control form-control-sm" placeholder="Type to search issue library..." autocomplete="off">
                            <div id="autosuggest-results" class="list-group position-absolute w-100 shadow-sm" style="z-index: 1000; display: none; max-height: 250px; overflow-y: auto;"></div>
                        </div>
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="small fw-semibold text-muted mb-1"><i class="fas fa-exclamation-triangle me-1"></i>Severity</label>
                        <div class="d-flex gap-1">
                            <button type="button" class="btn btn-sm btn-outline-info severity-btn" data-value="low">Low</button>
                            <button type="button" class="btn btn-sm btn-outline-warning severity-btn active" data-value="medium">Med</button>
                            <button type="button" class="btn btn-sm btn-outline-danger severity-btn" data-value="high">High</button>
                            <button type="button" class="btn btn-sm btn-outline-dark severity-btn" data-value="critical">Crit</button>
                        </div>
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="small fw-semibold text-muted mb-1"><i class="fas fa-clock me-1"></i>Urgency</label>
                        <div class="d-flex gap-1">
                            <button type="button" class="btn btn-sm btn-outline-secondary urgency-btn" data-value="routine">Rtn</button>
                            <button type="button" class="btn btn-sm btn-outline-info urgency-btn active" data-value="soon">Soon</button>
                            <button type="button" class="btn btn-sm btn-outline-warning urgency-btn" data-value="urgent">Urg</button>
                            <button type="button" class="btn btn-sm btn-outline-danger urgency-btn" data-value="immediate">Imm</button>
                        </div>
                    </div>
                    <div class="col-12 col-md-1">
                        <button id="btn-quick-add" class="btn btn-primary btn-sm w-100" style="background: linear-gradient(135deg, #1a237e, #283593);" disabled>
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>
                <div class="mt-2 text-end">
                    <small class="text-muted">
                        <a href="#" class="text-decoration-none" data-bs-toggle="collapse" data-bs-target="#bulkQuickAddPanel">
                            <i class="fas fa-bolt me-1"></i>Bulk Quick Add
                        </a>
                    </small>
                </div>
            </div>

            <!-- Bulk Quick Add Panel -->
            <div class="collapse mb-3" id="bulkQuickAddPanel">
                <div class="card card-body p-3" style="background: linear-gradient(135deg, #f8f9fa, #fff); border: 1px solid #e2e8f0;">
                    <div class="row g-3">
                        <div class="col-12">
                            <div class="d-flex flex-wrap gap-2 mb-3">
                                <span class="badge bg-primary px-3 py-2" onclick="bulkQuickAddByCategory('Engine')" style="cursor: pointer;">Engine</span>
                                <span class="badge bg-danger px-3 py-2" onclick="bulkQuickAddByCategory('Brakes')" style="cursor: pointer;">Brakes</span>
                                <span class="badge bg-warning text-dark px-3 py-2" onclick="bulkQuickAddByCategory('Suspension')" style="cursor: pointer;">Suspension</span>
                                <span class="badge bg-info px-3 py-2" onclick="bulkQuickAddByCategory('Electrical')" style="cursor: pointer;">Electrical</span>
                                <span class="badge bg-secondary px-3 py-2" onclick="bulkQuickAddByCategory('Cooling')" style="cursor: pointer;">Cooling</span>
                                <span class="badge bg-dark px-3 py-2" onclick="bulkQuickAddByCategory('Transmission')" style="cursor: pointer;">Transmission</span>
                                <span class="badge bg-success px-3 py-2" onclick="bulkQuickAddByCategory('Tires')" style="cursor: pointer;">Tires</span>
                                <span class="badge bg-primary px-3 py-2" onclick="bulkQuickAddByCategory('Aircon')" style="cursor: pointer;">Aircon</span>
                                <span class="badge bg-info px-3 py-2" onclick="bulkQuickAddByCategory('Steering')" style="cursor: pointer;">Steering</span>
                                <span class="badge bg-warning text-dark px-3 py-2" onclick="bulkQuickAddByCategory('Body / Exterior')" style="cursor: pointer;">Body</span>
                            </div>
                        </div>
                        <div class="col-12">
                            <div id="bulk-issues-container" class="d-flex flex-wrap gap-2">
                                <span class="small text-muted">Click a category badge above to show common issues.</span>
                            </div>
                        </div>
                        <div class="col-12 text-end" id="bulk-add-spinner" style="display: none;">
                            <span class="spinner-border spinner-border-sm me-2" role="status"></span>Adding findings...
                        </div>
                    </div>
                </div>
            </div>

            <!-- Findings List -->
            <div id="findings-list-container">
                @forelse($inspection->inspectionFindings as $finding)
                <div class="finding-card mb-2" data-id="{{ $finding->id }}">
                    <div class="card border-0 shadow-sm" style="border-radius: 10px; background: #fff;">
                        <div class="card-body p-3">
                            <div class="d-flex flex-wrap align-items-start gap-2">
                                <div class="flex-grow-1">
                                    <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                                        <span class="badge category-badge-{{ Str::slug($finding->category) }}" style="
                                            @switch($finding->category)
                                                @case('Engine') background: linear-gradient(135deg, #1a237e, #283593); @break
                                                @case('Brakes') background: linear-gradient(135deg, #c62828, #d32f2f); @break
                                                @case('Suspension') background: linear-gradient(135deg, #e65100, #ef6c00); @break
                                                @case('Electrical') background: linear-gradient(135deg, #00695c, #00897b); @break
                                                @case('Cooling') background: linear-gradient(135deg, #4a148c, #6a1b9a); @break
                                                @case('Transmission') background: linear-gradient(135deg, #37474f, #455a64); @break
                                                @case('Tires') background: linear-gradient(135deg, #1b5e20, #2e7d32); @break
                                                @case('Aircon') background: linear-gradient(135deg, #01579b, #0277bd); @break
                                                @case('Steering') background: linear-gradient(135deg, #3e2723, #4e342e); @break
                                                @case('Body / Exterior') background: linear-gradient(135deg, #827717, #9e9d24); @break
                                                @default background: linear-gradient(135deg, #546e7a, #607d8b); @endswitch
                                            color: #fff; padding: 3px 10px; border-radius: 12px; font-size: 11px;">
                                            <i class="fas fa-tag me-1"></i>{{ $finding->category }}
                                        </span>
                                        <strong class="finding-title">{{ $finding->issue_title }}</strong>
                                    </div>
                                    @if($finding->detailed_notes)
                                    <p class="text-muted small mb-2 finding-notes">{{ $finding->detailed_notes }}</p>
                                    @endif
                                    @if($finding->recommended_action)
                                    <div class="mb-2">
                                        <small class="text-muted"><i class="fas fa-wrench me-1"></i>Recommended: </small>
                                        <span class="small finding-action">{{ $finding->recommended_action }}</span>
                                    </div>
                                    @endif
                                </div>
                                <div class="text-end" style="min-width: 120px;">
                                    <div class="d-flex flex-wrap gap-1 justify-content-end mb-2">
                                        <span class="badge severity-badge-{{ $finding->severity }} px-2 py-1" style="font-size: 10px;">
                                            @switch($finding->severity)
                                                @case('low') <i class="fas fa-chevron-down me-1"></i>Low @break
                                                @case('medium') <i class="fas fa-minus me-1"></i>Medium @break
                                                @case('high') <i class="fas fa-chevron-up me-1"></i>High @break
                                                @case('critical') <i class="fas fa-exclamation me-1"></i>Critical @break
                                            @endswitch
                                        </span>
                                        <span class="badge urgency-badge-{{ $finding->estimated_urgency }} px-2 py-1" style="font-size: 10px;">
                                            @switch($finding->estimated_urgency)
                                                @case('routine') <i class="fas fa-calendar me-1"></i>Routine @break
                                                @case('soon') <i class="fas fa-clock me-1"></i>Soon @break
                                                @case('urgent') <i class="fas fa-exclamation-circle me-1"></i>Urgent @break
                                                @case('immediate') <i class="fas fa-bolt me-1"></i>Immediate @break
                                            @endswitch
                                        </span>
                                    </div>
                                    @if($finding->estimated_cost !== null)
                                    <div class="fw-bold finding-cost" style="color: #1a237e;">
                                        ₱{{ number_format($finding->estimated_cost, 2) }}
                                    </div>
                                    @endif
                                    <div class="small text-muted mt-1">
                                        @if($finding->technician)
                                        <i class="fas fa-user me-1"></i>{{ $finding->technician->name }}<br>
                                        @endif
                                        <i class="fas fa-clock me-1"></i>{{ $finding->created_at ? $finding->created_at->diffForHumans() : '' }}
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex gap-2 mt-2 pt-2 border-top">
                                <button class="btn btn-sm btn-outline-primary" onclick="editFinding({{ $finding->id }})" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger" onclick="deleteFinding({{ $finding->id }})" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                                <span class="badge ms-auto align-self-center finding-linked-badge" style="@if($finding->is_linked_to_estimate) background: #059669; color: #fff; @else display: none; @endif font-size: 10px;">
                                    <i class="fas fa-link me-1"></i>In Estimate
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center py-5">
                    <div class="mb-3">
                        <i class="fas fa-clipboard-list" style="font-size: 48px; color: #cfd8dc;"></i>
                    </div>
                    <h6 class="text-muted mb-2">No Findings Recorded Yet</h6>
                    <p class="text-muted small mb-3">Use the quick-add bar above or the Bulk Quick Add panel to start logging findings.</p>
                    <div class="d-flex flex-wrap justify-content-center gap-2">
                        <span class="badge bg-primary px-3 py-2" onclick="document.getElementById('quick-category').value='Engine'; document.getElementById('quick-issue-title').focus();" style="cursor: pointer;">
                            <i class="fas fa-bolt me-1"></i>Engine Issue
                        </span>
                        <span class="badge bg-danger px-3 py-2" onclick="document.getElementById('quick-category').value='Brakes'; document.getElementById('quick-issue-title').focus();" style="cursor: pointer;">
                            <i class="fas fa-bolt me-1"></i>Brake Issue
                        </span>
                        <span class="badge bg-warning text-dark px-3 py-2" onclick="document.getElementById('quick-category').value='Suspension'; document.getElementById('quick-issue-title').focus();" style="cursor: pointer;">
                            <i class="fas fa-bolt me-1"></i>Suspension Issue
                        </span>
                    </div>
                </div>
                @endforelse
            </div>
        </div>

        <!-- Findings Add/Edit Modal -->
        <div class="modal fade" id="findingEditModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content" style="border-radius:12px;border:none;box-shadow:0 10px 40px rgba(0,0,0,.15);">
                    <div class="modal-header" style="background:linear-gradient(135deg,#1a237e,#283593);color:#fff;border-radius:12px 12px 0 0;">
                        <h5 class="modal-title"><i class="fas fa-clipboard-list me-2"></i><span id="findingModalTitle">Add Finding</span></h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <form id="findingForm">
                            <input type="hidden" id="editFindingId" value="">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Category</label>
                                    <select id="editCategory" class="form-select form-select-sm">
                                        <option value="Engine">Engine</option>
                                        <option value="Brakes">Brakes</option>
                                        <option value="Suspension">Suspension</option>
                                        <option value="Electrical">Electrical</option>
                                        <option value="Cooling">Cooling</option>
                                        <option value="Transmission">Transmission</option>
                                        <option value="Tires">Tires</option>
                                        <option value="Aircon">Aircon</option>
                                        <option value="Steering">Steering</option>
                                        <option value="Body / Exterior">Body / Exterior</option>
                                        <option value="Safety">Safety</option>
                                        <option value="Maintenance">Maintenance</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Issue Title</label>
                                    <div class="position-relative">
                                        <input type="text" id="editIssueTitle" class="form-control form-control-sm" placeholder="e.g. Oil Leak" autocomplete="off">
                                        <div id="modal-autosuggest-results" class="list-group position-absolute w-100 shadow-sm" style="z-index: 1050; display: none; max-height: 250px; overflow-y: auto;"></div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label small fw-semibold">Detailed Notes</label>
                                    <textarea id="editDetailedNotes" class="form-control form-control-sm" rows="2" placeholder="Additional observations..."></textarea>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-semibold">Severity</label>
                                    <div class="d-flex gap-2 flex-wrap">
                                        <button type="button" class="btn btn-outline-info btn-sm severity-btn px-3" data-value="low">Low</button>
                                        <button type="button" class="btn btn-outline-warning btn-sm severity-btn px-3 active" data-value="medium">Medium</button>
                                        <button type="button" class="btn btn-outline-danger btn-sm severity-btn px-3" data-value="high">High</button>
                                        <button type="button" class="btn btn-outline-dark btn-sm severity-btn px-3" data-value="critical">Critical</button>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-semibold">Urgency</label>
                                    <div class="d-flex gap-2 flex-wrap">
                                        <button type="button" class="btn btn-outline-secondary btn-sm urgency-btn px-3" data-value="routine">Routine</button>
                                        <button type="button" class="btn btn-outline-info btn-sm urgency-btn px-3 active" data-value="soon">Soon</button>
                                        <button type="button" class="btn btn-outline-warning btn-sm urgency-btn px-3" data-value="urgent">Urgent</button>
                                        <button type="button" class="btn btn-outline-danger btn-sm urgency-btn px-3" data-value="immediate">Immediate</button>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-semibold">Est. Cost (₱)</label>
                                    <input type="number" id="editEstimatedCost" class="form-control form-control-sm" min="0" step="0.01" placeholder="0.00">
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label small fw-semibold">Recommended Action</label>
                                    <textarea id="editRecommendedAction" class="form-control form-control-sm" rows="2" placeholder="Recommended repair action..."></textarea>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer border-0 pt-0 px-4 pb-4">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" onclick="saveFindingFromModal()">
                            <i class="fas fa-save me-1"></i>Save Finding
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- === INSPECTION ITEMS TAB === -->
        <div class="tab-pane fade" id="items" role="tabpanel">
            @if($inspection->items && $inspection->items->count() > 0)
                <div class="form-section">
                    <div class="form-section-header no-collapse">
                        <h6><i class="fas fa-list"></i>Inspection Items ({{ $inspection->items->count() }})</h6>
                    </div>
                    <div class="form-section-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 40px;">#</th>
                                        <th>Category</th>
                                        <th>Item</th>
                                        <th>Status</th>
                                        <th>Notes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($inspection->items as $index => $item)
                                        <tr>
                                            <td class="text-muted">{{ $index + 1 }}</td>
                                            <td><span class="badge bg-secondary-subtle text-secondary">{{ $item->category ?? 'General' }}</span></td>
                                            <td class="fw-medium">{{ $item->item_name ?? $item->name ?? 'Item' }}</td>
                                            <td>
                                                @if($item->status == 'passed')
                                                    <span class="badge bg-success"><i class="fas fa-check me-1"></i>Passed</span>
                                                @elseif($item->status == 'failed')
                                                    <span class="badge bg-danger"><i class="fas fa-times me-1"></i>Failed</span>
                                                @elseif($item->status == 'warning')
                                                    <span class="badge bg-warning text-dark"><i class="fas fa-exclamation me-1"></i>Warning</span>
                                                @else
                                                    <span class="badge bg-secondary"><i class="fas fa-clock me-1"></i>Pending</span>
                                                @endif
                                            </td>
                                            <td class="small text-muted">{{ $item->notes ?? '' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @else
                <div class="form-section">
                    <div class="form-section-header no-collapse">
                        <h6><i class="fas fa-list"></i>Inspection Items</h6>
                    </div>
                    <div class="form-section-body text-center py-4">
                        <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                        <p class="text-muted mb-0">No inspection items recorded yet.</p>
                    </div>
                </div>
            @endif
        </div>

        <!-- === PHOTOS TAB === -->
        <div class="tab-pane fade" id="photos" role="tabpanel">
            @php
                $photos = is_array($inspection->photos) ? $inspection->photos : (json_decode($inspection->photos ?? '[]', true) ?? []);
            @endphp
            @if(count($photos) > 0)
                <div class="row g-3">
                    @foreach($photos as $photo)
                        <div class="col-md-4 col-lg-3">
                            <div class="form-section">
                                <div class="form-section-body p-2">
                                    <img src="{{ $photo['url'] ?? $photo }}" alt="Inspection photo"
                                         class="img-fluid rounded" style="width:100%;height:180px;object-fit:cover;cursor:pointer;"
                                         onclick="window.open(this.src, '_blank')">
                                    @if(isset($photo['caption']))
                                        <p class="small text-muted mt-1 mb-0 px-1">{{ $photo['caption'] }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="form-section">
                    <div class="form-section-header no-collapse">
                        <h6><i class="fas fa-camera"></i>Photos</h6>
                    </div>
                    <div class="form-section-body text-center py-4">
                        <i class="fas fa-image fa-3x text-muted mb-3"></i>
                        <p class="text-muted mb-0">No photos attached to this inspection.</p>
                    </div>
                </div>
            @endif
        </div>

        <!-- === TIMELINE TAB === -->
        <div class="tab-pane fade" id="timeline" role="tabpanel">
            <div class="form-section">
                <div class="form-section-header no-collapse">
                    <h6><i class="fas fa-history"></i>Inspection Timeline</h6>
                </div>
                <div class="form-section-body">
                    @php
                        $timelineEntries = [];
                        if($inspection->created_at) $timelineEntries[] = ['event' => 'Inspection Created', 'time' => $inspection->created_at, 'icon' => 'fa-plus-circle', 'color' => 'primary'];
                        if($inspection->inspection_started_at) $timelineEntries[] = ['event' => 'Inspection Started', 'time' => $inspection->inspection_started_at, 'icon' => 'fa-play', 'color' => 'info'];
                        if($inspection->inspection_completed_at) $timelineEntries[] = ['event' => 'Inspection Completed', 'time' => $inspection->inspection_completed_at, 'icon' => 'fa-check-circle', 'color' => 'success'];
                        if($inspection->report_generated_at) $timelineEntries[] = ['event' => 'Report Generated', 'time' => $inspection->report_generated_at, 'icon' => 'fa-file-alt', 'color' => 'primary'];
                    @endphp
                    @if(count($timelineEntries) > 0)
                        <div class="timeline-vertical">
                            @foreach($timelineEntries as $entry)
                                <div class="d-flex mb-3">
                                    <div class="me-3 text-{{ $entry['color'] }}" style="font-size: 1.2rem;">
                                        <i class="fas {{ $entry['icon'] }}"></i>
                                    </div>
                                    <div>
                                        <strong>{{ $entry['event'] }}</strong>
                                        <br><small class="text-muted">{{ $entry['time']->format('M j, Y g:i A') }}</small>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted mb-0">No timeline events available.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
/* Findings System Styles */
.findings-quick-add{border:1px solid #d0d9f0!important;position:relative;z-index:10}
#quick-issue-title{font-size:.875rem}
#autosuggest-results .list-group-item,
#modal-autosuggest-results .list-group-item{padding:.35rem .75rem;cursor:pointer;border-left:3px solid transparent;font-size:.8rem}
#autosuggest-results .list-group-item:hover,#autosuggest-results .list-group-item.active,
#modal-autosuggest-results .list-group-item:hover,#modal-autosuggest-results .list-group-item.active{border-left-color:#1a237e;background:#f0f4ff}
#autosuggest-results .list-group-item .category-hint,
#modal-autosuggest-results .list-group-item .category-hint{font-size:.65rem;color:#94a3b8}
.severity-btn.active,.urgency-btn.active{box-shadow:0 0 0 2px rgba(26,35,126,.25)}
.btn-outline-info.active{background:#0dcaf0!important;color:#fff!important;border-color:#0dcaf0!important}
.btn-outline-warning.active{background:#ffc107!important;color:#000!important;border-color:#ffc107!important}
.btn-outline-danger.active{background:#dc3545!important;color:#fff!important;border-color:#dc3545!important}
.btn-outline-dark.active{background:#212529!important;color:#fff!important;border-color:#212529!important}
.btn-outline-secondary.active{background:#6c757d!important;color:#fff!important;border-color:#6c757d!important}
.btn-outline-info.active{background:#0dcaf0!important;color:#fff!important;border-color:#0dcaf0!important}
.finding-card{transition:all .15s ease}
.finding-card .card:hover{border-color:#c8d6e5!important;box-shadow:0 2px 8px rgba(0,0,0,.06)!important}
.finding-title{color:#1e293b;font-size:.9rem}
.finding-notes{font-size:.82rem;color:#64748b}
.finding-action{font-size:.82rem;color:#475569}
.finding-cost{font-size:.95rem}
/* Animate new findings */
@keyframes fadeSlideDown{from{opacity:0;transform:translateY(-10px)}to{opacity:1;transform:translateY(0)}}
.finding-new{animation:fadeSlideDown .3s ease}
/* Animate removal */
@keyframes fadeScaleOut{from{opacity:1;transform:scale(1)}to{opacity:0;transform:scale(.95)}}
.finding-removing{animation:fadeScaleOut .2s ease}
/* Modify modal styling for premium look */
#findingEditModal .modal-header{background:linear-gradient(135deg,#1a237e,#283593);color:#fff;border-radius:.375rem .375rem 0 0}
#findingEditModal .modal-header .btn-close{filter:brightness(0) invert(1)}
.bulk-add-btn{transition:all .1s ease;cursor:pointer}
.bulk-add-btn:hover{transform:translateY(-1px);box-shadow:0 2px 4px rgba(0,0,0,.1)}
</style>
@endpush

@push('scripts')
<script>
// ====================== ISSUE LIBRARY ======================
const issueLibrary = {
    'Engine':[
        {title:'Engine Oil Leak',action:'Inspect and repair oil leak source',avgCost:150,severity:'medium',urgency:'urgent'},
        {title:'Engine Misfire',action:'Diagnose and repair misfire',avgCost:200,severity:'high',urgency:'urgent'},
        {title:'Engine Overheating',action:'Check cooling system, thermostat, water pump',avgCost:300,severity:'critical',urgency:'immediate'},
        {title:'Timing Belt/Chain Noise',action:'Inspect and replace timing belt/chain',avgCost:500,severity:'medium',urgency:'soon'},
        {title:'Check Engine Light On',action:'Run diagnostic scan',avgCost:85,severity:'medium',urgency:'urgent'},
        {title:'Rough Idle',action:'Clean throttle body, check spark plugs',avgCost:120,severity:'low',urgency:'soon'},
        {title:'Knocking Noise',action:'Inspect internal engine components',avgCost:250,severity:'high',urgency:'urgent'},
        {title:'Low Compression',action:'Perform compression test',avgCost:150,severity:'high',urgency:'soon'},
        {title:'Valve Cover Gasket Leak',action:'Replace valve cover gasket',avgCost:180,severity:'medium',urgency:'routine'},
        {title:'Oil Sludge Build-up',action:'Perform engine flush',avgCost:100,severity:'low',urgency:'routine'}
    ],
    'Brakes':[
        {title:'Brake Pads Worn',action:'Replace brake pads',avgCost:180,severity:'high',urgency:'urgent'},
        {title:'Brake Rotors Warped',action:'Resurface or replace rotors',avgCost:300,severity:'high',urgency:'urgent'},
        {title:'Brake Fluid Low',action:'Check for leaks, top up fluid',avgCost:50,severity:'medium',urgency:'soon'},
        {title:'Brake Noise (Squealing)',action:'Inspect pads, shims, lubricate',avgCost:100,severity:'low',urgency:'routine'},
        {title:'Brake Pedal Soft/Spongy',action:'Bleed brake system',avgCost:80,severity:'critical',urgency:'immediate'},
        {title:'Parking Brake Issues',action:'Adjust parking brake cable',avgCost:90,severity:'low',urgency:'routine'},
        {title:'ABS Light On',action:'Diagnose ABS system',avgCost:120,severity:'medium',urgency:'soon'},
        {title:'Brake Line Leak',action:'Replace brake line',avgCost:250,severity:'critical',urgency:'immediate'},
        {title:'Master Cylinder Failure',action:'Replace master cylinder',avgCost:350,severity:'critical',urgency:'immediate'},
        {title:'Calipers Sticking',action:'Rebuild or replace caliper',avgCost:280,severity:'high',urgency:'urgent'}
    ],
    'Suspension':[
        {title:'Suspension Noise (Clunking)',action:'Inspect bushings, struts, links',avgCost:150,severity:'medium',urgency:'soon'},
        {title:'Shock Absorber Leaking',action:'Replace shock absorber',avgCost:350,severity:'medium',urgency:'soon'},
        {title:'Tie Rod Loose',action:'Replace tie rod end',avgCost:180,severity:'high',urgency:'urgent'},
        {title:'Ball Joint Worn',action:'Replace ball joint',avgCost:250,severity:'high',urgency:'urgent'},
        {title:'Control Arm Bushing Worn',action:'Replace control arm bushing',avgCost:200,severity:'medium',urgency:'soon'},
        {title:'Sway Bar Link Broken',action:'Replace sway bar link',avgCost:120,severity:'medium',urgency:'soon'},
        {title:'Vehicle Pulling to One Side',action:'Check alignment, suspension components',avgCost:100,severity:'medium',urgency:'soon'},
        {title:'Uneven Tire Wear',action:'Check alignment, rotate tires',avgCost:80,severity:'low',urgency:'routine'},
        {title:'Strut Mount Noise',action:'Replace strut mount',avgCost:200,severity:'medium',urgency:'routine'},
        {title:'Lowering Springs Sagged',action:'Replace springs',avgCost:400,severity:'medium',urgency:'soon'}
    ],
    'Electrical':[
        {title:'Battery Weak/Dead',action:'Test battery, replace if needed',avgCost:150,severity:'medium',urgency:'urgent'},
        {title:'Alternator Not Charging',action:'Test and replace alternator',avgCost:400,severity:'high',urgency:'urgent'},
        {title:'Starter Not Engaging',action:'Test and replace starter',avgCost:350,severity:'high',urgency:'urgent'},
        {title:'Headlight Not Working',action:'Replace bulb/assembly',avgCost:80,severity:'low',urgency:'routine'},
        {title:'Turn Signal Malfunction',action:'Diagnose and repair signal circuit',avgCost:100,severity:'low',urgency:'routine'},
        {title:'Power Window Not Working',action:'Check switch, motor, regulator',avgCost:200,severity:'low',urgency:'routine'},
        {title:'Wiring Harness Damage',action:'Repair wiring harness',avgCost:250,severity:'medium',urgency:'soon'},
        {title:'Fuse Blown Repeatedly',action:'Trace short circuit',avgCost:120,severity:'medium',urgency:'soon'},
        {title:'Central Locking Not Working',action:'Diagnose door lock system',avgCost:150,severity:'low',urgency:'routine'},
        {title:'Battery Corrosion',action:'Clean terminals, replace clamps',avgCost:40,severity:'low',urgency:'routine'}
    ],
    'Cooling':[
        {title:'Coolant Leak',action:'Pressure test, repair leak source',avgCost:200,severity:'high',urgency:'urgent'},
        {title:'Radiator Clogged/Damaged',action:'Flush or replace radiator',avgCost:350,severity:'high',urgency:'urgent'},
        {title:'Thermostat Stuck',action:'Replace thermostat',avgCost:150,severity:'medium',urgency:'soon'},
        {title:'Coolant Fan Not Working',action:'Replace fan motor/module',avgCost:250,severity:'high',urgency:'urgent'},
        {title:'Water Pump Leaking',action:'Replace water pump',avgCost:400,severity:'high',urgency:'urgent'},
        {title:'Heater Not Working',action:'Check heater core, coolant level',avgCost:180,severity:'low',urgency:'routine'},
        {title:'Coolant Contaminated',action:'Flush cooling system',avgCost:100,severity:'medium',urgency:'routine'},
        {title:'Hose Cracked/Bulging',action:'Replace radiator hose',avgCost:80,severity:'medium',urgency:'soon'},
        {title:'Reservoir Tank Leaking',action:'Replace coolant reservoir',avgCost:60,severity:'low',urgency:'routine'},
        {title:'Thermal Switch Malfunction',action:'Replace thermal switch',avgCost:120,severity:'medium',urgency:'soon'}
    ],
    'Transmission':[
        {title:'Transmission Fluid Leak',action:'Inspect and repair leak',avgCost:250,severity:'medium',urgency:'soon'},
        {title:'Transmission Slipping',action:'Diagnose transmission',avgCost:300,severity:'high',urgency:'urgent'},
        {title:'Hard Shifting',action:'Check fluid, adjust linkage',avgCost:150,severity:'medium',urgency:'soon'},
        {title:'Transmission Noises',action:'Inspect transmission internals',avgCost:200,severity:'medium',urgency:'soon'},
        {title:'Clutch Slipping',action:'Replace clutch kit',avgCost:600,severity:'high',urgency:'urgent'},
        {title:'Clutch Pedal Hard',action:'Inspect clutch cable/hydraulics',avgCost:150,severity:'medium',urgency:'routine'},
        {title:'Transmission Mount Worn',action:'Replace transmission mount',avgCost:180,severity:'low',urgency:'routine'},
        {title:'CV Axle Boot Torn',action:'Replace CV axle/boot',avgCost:250,severity:'medium',urgency:'soon'},
        {title:'Differential Noise',action:'Check differential fluid',avgCost:100,severity:'low',urgency:'routine'},
        {title:'Gear Grinding',action:'Check synchros, fluid',avgCost:200,severity:'high',urgency:'urgent'}
    ],
    'Tires':[
        {title:'Tire Pressure Low',action:'Inflate to proper pressure',avgCost:0,severity:'low',urgency:'routine'},
        {title:'Tread Depth Below Safe',action:'Replace tires',avgCost:400,severity:'high',urgency:'urgent'},
        {title:'Tire Sidewall Damage',action:'Replace damaged tire',avgCost:150,severity:'high',urgency:'urgent'},
        {title:'Tire Puncture/Cut',action:'Repair puncture',avgCost:30,severity:'medium',urgency:'routine'},
        {title:'Tire Cupping Wear',action:'Alignment check, replace tire',avgCost:150,severity:'medium',urgency:'soon'},
        {title:'Valve Stem Leaking',action:'Replace valve stem',avgCost:15,severity:'low',urgency:'routine'},
        {title:'TPMS Light On',action:'Diagnose TPMS system',avgCost:60,severity:'low',urgency:'routine'},
        {title:'Wheel Bearing Noise',action:'Replace wheel bearing',avgCost:300,severity:'medium',urgency:'soon'},
        {title:'Wheel Balance Off',action:'Balance wheels',avgCost:60,severity:'low',urgency:'routine'},
        {title:'Spare Tire Missing',action:'Replace spare tire',avgCost:100,severity:'low',urgency:'routine'}
    ],
    'Aircon':[
        {title:'A/C Not Cooling',action:'Check refrigerant level, recharge',avgCost:150,severity:'medium',urgency:'urgent'},
        {title:'A/C Weak Airflow',action:'Replace cabin filter, check blower',avgCost:80,severity:'low',urgency:'routine'},
        {title:'Strange Odors from A/C',action:'Clean evaporator, replace filter',avgCost:100,severity:'low',urgency:'routine'},
        {title:'A/C Compressor Noisy',action:'Replace compressor',avgCost:600,severity:'medium',urgency:'soon'},
        {title:'Refrigerant Leak',action:'UV dye test, repair leak',avgCost:250,severity:'medium',urgency:'soon'},
        {title:'Condenser Damaged',action:'Replace condenser',avgCost:400,severity:'medium',urgency:'soon'},
        {title:'Blower Motor Not Working',action:'Replace blower motor',avgCost:300,severity:'low',urgency:'routine'},
        {title:'Blend Door Issue',action:'Repair blend door actuator',avgCost:200,severity:'low',urgency:'routine'},
        {title:'Expansion Valve Stuck',action:'Replace expansion valve',avgCost:250,severity:'medium',urgency:'soon'},
        {title:'A/C Clutch Not Engaging',action:'Check relay, clutch coil',avgCost:150,severity:'medium',urgency:'soon'}
    ],
    'Steering':[
        {title:'Steering Wheel Vibration',action:'Check balance, suspension',avgCost:100,severity:'medium',urgency:'routine'},
        {title:'Power Steering Fluid Leak',action:'Repair leak, replace hose/pump',avgCost:300,severity:'medium',urgency:'soon'},
        {title:'Steering Rack Worn',action:'Replace steering rack',avgCost:500,severity:'high',urgency:'urgent'},
        {title:'Steering Wheel Off-Center',action:'Align steering wheel',avgCost:80,severity:'low',urgency:'routine'},
        {title:'Power Steering Pump Noisy',action:'Replace pump, flush fluid',avgCost:350,severity:'medium',urgency:'soon'},
        {title:'Steering Column Play',action:'Inspect steering column',avgCost:200,severity:'high',urgency:'urgent'},
        {title:'Steering Fluid Dark/Contaminated',action:'Flush power steering system',avgCost:100,severity:'low',urgency:'routine'},
        {title:'Steering Wheel Hard to Turn',action:'Check power steering, belt, fluid',avgCost:120,severity:'high',urgency:'urgent'},
        {title:'Knocking when Turning',action:'Check CV joints, tie rods',avgCost:150,severity:'medium',urgency:'soon'},
        {title:'Poor Return-to-Center',action:'Check alignment, steering gear',avgCost:100,severity:'low',urgency:'routine'}
    ],
    'Body / Exterior':[
        {title:'Door Hinge Squeaking',action:'Lubricate hinges',avgCost:30,severity:'low',urgency:'routine'},
        {title:'Trunk/Hood Latch Issue',action:'Adjust or replace latch',avgCost:80,severity:'low',urgency:'routine'},
        {title:'Window Seal Deteriorated',action:'Replace weatherstrip',avgCost:100,severity:'low',urgency:'routine'},
        {title:'Body Scratch / Dent',action:'Touch up paint / dent repair',avgCost:150,severity:'low',urgency:'routine'},
        {title:'Rust / Corrosion',action:'Rust treatment and panel repair',avgCost:300,severity:'medium',urgency:'soon'},
        {title:'Wiper Blades Worn',action:'Replace wiper blades',avgCost:30,severity:'low',urgency:'routine'},
        {title:'Side Mirror Damaged',action:'Replace side mirror',avgCost:150,severity:'low',urgency:'routine'},
        {title:'Tail Light Cracked',action:'Replace tail light assembly',avgCost:120,severity:'low',urgency:'routine'},
        {title:'Exhaust Leak',action:'Repair exhaust system',avgCost:200,severity:'medium',urgency:'soon'},
        {title:'Catalytic Converter Issue',action:'Diagnose and replace cat',avgCost:500,severity:'medium',urgency:'soon'}
    ]
};

// ====================== CONFIG ======================
const severityMap = {low:'info',medium:'warning',high:'danger',critical:'dark'};
const urgencyMap = {routine:'secondary',soon:'info',urgent:'warning',immediate:'danger'};
const inspectionId = {{ $inspection->id }};
const csrfToken = '{{ csrf_token() }}';

// ====================== INIT ======================
document.addEventListener('DOMContentLoaded', function() {
    // Severity button toggles
    document.querySelectorAll('.severity-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var parent = this.closest('.d-flex');
            parent.querySelectorAll('.severity-btn').forEach(function(b) { b.classList.remove('active'); });
            this.classList.add('active');
        });
    });

    // Urgency button toggles
    document.querySelectorAll('.urgency-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var parent = this.closest('.d-flex');
            parent.querySelectorAll('.urgency-btn').forEach(function(b) { b.classList.remove('active'); });
            this.classList.add('active');
        });
    });

    // Issue title input with autosuggest
    var issueInput = document.getElementById('quick-issue-title');
    if (issueInput) {
        issueInput.addEventListener('input', function() { showAutosuggest(this); });
        issueInput.addEventListener('keydown', function(e) { handleAutosuggestKey(e, this); });
        issueInput.addEventListener('blur', function() {
            setTimeout(function() {
                document.getElementById('autosuggest-results').style.display = 'none';
            }, 150);
        });
        issueInput.addEventListener('focus', function() {
            var cat = document.getElementById('quick-category').value;
            console.log('FOCUS - cat:', cat, 'val:', this.value, 'lib:', issueLibrary[cat] ? 'YES' : 'NO');
            if (cat && cat !== '' && issueLibrary[cat]) {
                showAutosuggestForCategory(this, cat);
            } else if (this.value.length >= 1) {
                showAutosuggest(this);
            }
        });
    }

    // Enable add button when title has text
    issueInput && issueInput.addEventListener('input', function() {
        document.getElementById('btn-quick-add').disabled = this.value.trim().length === 0;
    });

    // Quick add button
    document.getElementById('btn-quick-add').addEventListener('click', function() {
        var title = document.getElementById('quick-issue-title').value.trim();
        if (!title) return;
        addFinding(
            document.getElementById('quick-category').value,
            title,
            getSelectedSeverity(),
            getSelectedUrgency()
        );
    });
});

function getSelectedSeverity() {
    var active = document.querySelector('.severity-btn.active');
    return active ? active.dataset.value : 'medium';
}

function getSelectedUrgency() {
    var active = document.querySelector('.urgency-btn.active');
    return active ? active.dataset.value : 'soon';
}

// ====================== AUTO-SUGGEST ======================
function showAutosuggest(input) {
    var dd = document.getElementById('autosuggest-results');
    var val = input.value.toLowerCase().trim();
    if (val.length < 1) { dd.style.display = 'none'; return; }

    dd.innerHTML = '';
    var results = [];

    for (var cat in issueLibrary) {
        issueLibrary[cat].forEach(function(issue) {
            if (issue.title.toLowerCase().includes(val) || (issue.action && issue.action.toLowerCase().includes(val))) {
                results.push({category: cat, issue: issue});
            }
        });
    }

    // Also search category names
    if (results.length < 3) {
        for (var catName in issueLibrary) {
            if (catName.toLowerCase().includes(val)) {
                issueLibrary[catName].forEach(function(iss) {
                    var found = results.some(function(r) { return r.issue.title === iss.title; });
                    if (!found) results.push({category: catName, issue: iss});
                });
            }
        }
    }

    if (results.length === 0) {
        dd.style.display = 'none';
        return;
    }

    // Show top 8 results
    results.slice(0, 8).forEach(function(r) {
        var item = document.createElement('button');
        item.type = 'button';
        item.className = 'list-group-item list-group-item-action';
        item.innerHTML = '<strong>' + r.issue.title + '</strong> <span class="category-hint">[' + r.category + ']</span>'
            + '<br><small class="text-muted">' + r.issue.action + ' — ₱' + r.issue.avgCost + '</small>';
        item.addEventListener('click', function() {
            selectIssue(r, input);
        });
        dd.appendChild(item);
    });

    dd.style.display = 'block';
}

function handleAutosuggestKey(e, input) {
    var dd = document.getElementById('autosuggest-results');
    if (!dd || dd.style.display === 'none') return;
    var items = dd.querySelectorAll('.list-group-item');
    var active = dd.querySelector('.active');
    var idx = Array.from(items).indexOf(active);

    if (e.key === 'ArrowDown') {
        e.preventDefault();
        var next = (idx + 1) % items.length;
        if (active) active.classList.remove('active');
        items[next].classList.add('active');
    } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        var prev = (idx - 1 + items.length) % items.length;
        if (active) active.classList.remove('active');
        items[prev].classList.add('active');
    } else if (e.key === 'Enter') {
        e.preventDefault();
        if (active) active.click();
    } else if (e.key === 'Escape') {
        dd.style.display = 'none';
    }
}

function selectIssue(result, input) {
    input.value = result.issue.title;
    document.getElementById('quick-category').value = result.category;
    // Set severity
    document.querySelectorAll('.severity-btn').forEach(function(b) {
        b.classList.toggle('active', b.dataset.value === (result.issue.severity || 'medium'));
    });
    // Set urgency
    document.querySelectorAll('.urgency-btn').forEach(function(b) {
        b.classList.toggle('active', b.dataset.value === (result.issue.urgency || 'soon'));
    });
    document.getElementById('autosuggest-results').style.display = 'none';
    document.getElementById('btn-quick-add').disabled = false;
    // Trigger add
    addFinding(result.category, result.issue.title, result.issue.severity || 'medium', result.issue.urgency || 'soon');
}

// ====================== ADD FINDING (AJAX) ======================
function addFinding(category, title, severity, urgency, skipFocus) {
    var btn = document.getElementById('btn-quick-add');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

    fetch('/inspections/' + inspectionId + '/findings', {
        method: 'POST',
        headers: {'Content-Type':'application/json','X-CSRF-TOKEN':csrfToken,'Accept':'application/json'},
        body: JSON.stringify({
            category: category,
            issue_title: title,
            severity: severity || 'medium',
            estimated_urgency: urgency || 'soon',
            detailed_notes: '',
            recommended_action: '',
            estimated_cost: null
        })
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            appendFindingCard(data.finding);
            updateStats();
            document.getElementById('quick-issue-title').value = '';
            if (!skipFocus) {
                document.getElementById('quick-issue-title').focus();
            }
        }
    })
    .catch(function(err) { console.error('Add finding error:', err); })
    .finally(function() {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-plus"></i>';
    });
}

function appendFindingCard(finding) {
    // Remove empty state
    var emptyEl = document.querySelector('#findings-list-container .text-center.py-5');
    if (emptyEl) emptyEl.remove();

    var costStr = finding.estimated_cost !== null && finding.estimated_cost !== undefined
        ? '₱' + parseFloat(finding.estimated_cost).toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2})
        : '';

    var cardHtml = '<div class="finding-card mb-2 finding-new" data-id="' + finding.id + '">'
        + '<div class="card border-0 shadow-sm" style="border-radius:10px;background:#fff;">'
        + '<div class="card-body p-3">'
        + '<div class="d-flex flex-wrap align-items-start gap-2">'
        + '<div class="flex-grow-1">'
        + '<div class="d-flex flex-wrap align-items-center gap-2 mb-2">'
        + '<span class="badge" style="background:linear-gradient(135deg,#1a237e,#283593);color:#fff;padding:3px 10px;border-radius:12px;font-size:11px;"><i class="fas fa-tag me-1"></i>' + finding.category + '</span>'
        + '<strong class="finding-title">' + escapeHtml(finding.issue_title) + '</strong>'
        + '</div>'
        + '</div>'
        + '<div class="text-end" style="min-width:120px;">'
        + '<div class="d-flex flex-wrap gap-1 justify-content-end mb-2">'
        + '<span class="badge px-2 py-1" style="font-size:10px;background:' + (finding.severity === 'low' ? '#0dcaf0' : finding.severity === 'medium' ? '#ffc107' : finding.severity === 'high' ? '#dc3545' : '#212529') + ';color:' + (finding.severity === 'medium' ? '#000' : '#fff') + ';"><i class="fas ' + (finding.severity === 'low' ? 'fa-chevron-down' : finding.severity === 'medium' ? 'fa-minus' : finding.severity === 'high' ? 'fa-chevron-up' : 'fa-exclamation') + ' me-1"></i>' + ucfirst(finding.severity) + '</span>'
        + '<span class="badge px-2 py-1" style="font-size:10px;background:' + (finding.estimated_urgency === 'routine' ? '#6c757d' : finding.estimated_urgency === 'soon' ? '#0dcaf0' : finding.estimated_urgency === 'urgent' ? '#ffc107' : '#dc3545') + ';color:' + (finding.estimated_urgency === 'routine' || finding.estimated_urgency === 'soon' ? '#fff' : '#000') + ';"><i class="fas ' + (finding.estimated_urgency === 'routine' ? 'fa-calendar' : finding.estimated_urgency === 'soon' ? 'fa-clock' : finding.estimated_urgency === 'urgent' ? 'fa-exclamation-circle' : 'fa-bolt') + ' me-1"></i>' + ucfirst(finding.estimated_urgency) + '</span>'
        + (costStr ? '<span class="fw-bold finding-cost" style="color:#1a237e;">' + costStr + '</span>' : '')
        + '</div>'
        + '</div>'
        + '</div>'
        + '<div class="d-flex gap-2 mt-2 pt-2 border-top">'
        + '<button class="btn btn-sm btn-outline-primary" onclick="editFinding(' + finding.id + ')"><i class="fas fa-edit"></i></button>'
        + '<button class="btn btn-sm btn-outline-danger" onclick="deleteFinding(' + finding.id + ')"><i class="fas fa-trash"></i></button>'
        + '</div>'
        + '</div>'
        + '</div>'
        + '</div>';

    var container = document.getElementById('findings-list-container');
    container.insertAdjacentHTML('beforeend', cardHtml);
}

// ====================== DELETE FINDING (AJAX) ======================
function deleteFinding(id) {
    if (!confirm('Remove this finding?')) return;
    var el = document.querySelector('.finding-card[data-id="' + id + '"]');
    if (el) el.classList.add('finding-removing');

    fetch('/inspections/findings/' + id, {
        method: 'DELETE',
        headers: {'X-CSRF-TOKEN':csrfToken,'Accept':'application/json'}
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            if (el) setTimeout(function() { el.remove(); }, 200);
            updateStats();
            // Show empty state if no more
            if (document.querySelectorAll('.finding-card').length === 0) {
                var container = document.getElementById('findings-list-container');
                container.innerHTML = '<div class="text-center py-5">'
                    + '<div class="mb-3"><i class="fas fa-clipboard-list" style="font-size:48px;color:#cfd8dc;"></i></div>'
                    + '<h6 class="text-muted mb-2">No Findings Recorded Yet</h6>'
                    + '<p class="text-muted small mb-0">Use the quick-add bar above to start logging findings.</p>'
                    + '</div>';
            }
        }
    })
    .catch(function(err) {
        console.error('Delete error:', err);
        if (el) el.classList.remove('finding-removing');
    });
}

// ====================== BULK QUICK ADD ======================
function bulkQuickAddByCategory(category) {
    var issues = issueLibrary[category];
    if (!issues) return;

    var container = document.getElementById('bulk-issues-container');
    container.innerHTML = '';

    issues.forEach(function(issue) {
        var badge = document.createElement('span');
        badge.className = 'badge bulk-add-btn px-3 py-2 me-1 mb-1';
        badge.style.cssText = 'background:#f0f4ff;color:#1a237e;border:1px solid #d0d9f0;font-size:11px;cursor:pointer;';
        badge.textContent = issue.title.length > 25 ? issue.title.substring(0, 22) + '...' : issue.title;
        badge.title = issue.title + ' — ' + issue.action + ' (₱' + issue.avgCost + ')';
        badge.addEventListener('click', function() {
            addFinding(category, issue.title, issue.severity || 'medium', issue.urgency || 'soon', true);
            this.style.background = '#059669';
            this.style.color = '#fff';
            this.style.borderColor = '#059669';
            setTimeout(function() {
                if (badge.parentNode) badge.remove();
            }, 1000);
        });
        container.appendChild(badge);
    });
}

// ====================== UTILITIES ======================
function escapeHtml(text) {
    var div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function ucfirst(str) {
    return str.charAt(0).toUpperCase() + str.slice(1);
}

function updateStats() {
    // Update findings count in tab badge if present
    var count = document.querySelectorAll('.finding-card').length;
    var badge = document.querySelector('[href="#findings"] .badge');
    if (badge) badge.textContent = count;
}

// ====================== EDIT FINDING (Modal) ======================
function editFinding(id) {
    var card = document.querySelector('.finding-card[data-id="' + id + '"]');
    if (!card) return;

    var title = card.querySelector('.finding-title');
    var category = card.querySelector('.badge[style*="background"]');
    var severityBadge = card.querySelector('.badge[style*="background"] + .d-flex .badge');
    var cost = card.querySelector('.finding-cost');

    // For a proper edit, we'll redirect to a simpler modal-based approach
    // Since we don't have a modal yet, open the page with the finding highlighted
    var categoryText = category ? category.textContent.replace('tag', '').trim() : '';
    var titleText = title ? title.textContent.trim() : '';

    openEditModal(id, categoryText, titleText);
}

function openEditModal(id, category, title) {
    document.getElementById('findingModalTitle').textContent = 'Edit Finding';
    document.getElementById('editFindingId').value = id;
    document.getElementById('editCategory').value = category || 'Engine';
    document.getElementById('editIssueTitle').value = title || '';
    document.getElementById('editDetailedNotes').value = '';
    document.getElementById('editEstimatedCost').value = '';
    document.getElementById('editRecommendedAction').value = '';
    document.querySelectorAll('.severity-btn').forEach(function(b) { b.classList.remove('active'); });
    document.querySelector('.severity-btn[data-value="medium"]').classList.add('active');
    document.querySelectorAll('.urgency-btn').forEach(function(b) { b.classList.remove('active'); });
    document.querySelector('.urgency-btn[data-value="soon"]').classList.add('active');

    try {
        var data = window._findingsData && window._findingsData[id];
        if (data) {
            document.getElementById('editDetailedNotes').value = data.detailed_notes || '';
            document.getElementById('editEstimatedCost').value = data.estimated_cost || '';
            document.getElementById('editRecommendedAction').value = data.recommended_action || '';
            if (data.severity) {
                document.querySelectorAll('.severity-btn').forEach(function(b) { b.classList.toggle('active', b.dataset.value === data.severity); });
            }
            if (data.estimated_urgency) {
                document.querySelectorAll('.urgency-btn').forEach(function(b) { b.classList.toggle('active', b.dataset.value === data.estimated_urgency); });
            }
        }
    } catch(e) {}

    var modal = new bootstrap.Modal(document.getElementById('findingEditModal'));
    modal.show();
}

function saveFindingFromModal() {
    var id = document.getElementById('editFindingId').value;
    var isNew = !id || id === '';

    var data = {
        category: document.getElementById('editCategory').value,
        issue_title: document.getElementById('editIssueTitle').value,
        detailed_notes: document.getElementById('editDetailedNotes').value,
        severity: getSelectedSeverity(),
        recommended_action: document.getElementById('editRecommendedAction').value,
        estimated_urgency: getSelectedUrgency(),
        estimated_cost: document.getElementById('editEstimatedCost').value || null
    };

    if (!data.issue_title.trim()) {
        alert('Please enter an issue title.');
        return;
    }

    if (isNew) {
        // Add new finding
        fetch('/inspections/' + inspectionId + '/findings', {
            method: 'POST',
            headers: {'Content-Type':'application/json','X-CSRF-TOKEN':csrfToken,'Accept':'application/json'},
            body: JSON.stringify(data)
        })
        .then(function(r) { return r.json(); })
        .then(function(resp) {
            if (resp.success) {
                appendFindingCard(resp.finding);
                updateStats();
                var modal = bootstrap.Modal.getInstance(document.getElementById('findingEditModal'));
                if (modal) modal.hide();
            }
        })
        .catch(function(err) { console.error('Save error:', err); });
    } else {
        // Update existing finding
        fetch('/inspections/findings/' + id, {
            method: 'PUT',
            headers: {'Content-Type':'application/json','X-CSRF-TOKEN':csrfToken,'Accept':'application/json'},
            body: JSON.stringify(data)
        })
        .then(function(r) { return r.json(); })
        .then(function(resp) {
            if (resp.success) {
                // Refresh the card by removing and re-adding
                var oldCard = document.querySelector('.finding-card[data-id="' + id + '"]');
                if (oldCard) oldCard.remove();
                appendFindingCard(resp.finding);
                updateStats();
                var modal = bootstrap.Modal.getInstance(document.getElementById('findingEditModal'));
                if (modal) modal.hide();
            }
        })
        .catch(function(err) { console.error('Update error:', err); });
    }
}

// ====================== INITIALIZE BULK CATEGORY BADGES ======================
// Already initialized in DOMContentLoaded, additional UI handlers:
document.addEventListener('DOMContentLoaded', function() {
    // Keyboard shortcut: Ctrl+Enter to quick add
    document.getElementById('quick-issue-title').addEventListener('keydown', function(e) {
        if (e.ctrlKey && e.key === 'Enter') {
            e.preventDefault();
            document.getElementById('btn-quick-add').click();
        }
    });

    // Category change clears input and hides suggestions
    document.getElementById('quick-category').addEventListener('change', function() {
        var input = document.getElementById('quick-issue-title');
        input.placeholder = 'Type to search ' + this.value + ' issues...';
        input.value = '';
        document.getElementById('btn-quick-add').disabled = true;
        document.getElementById('autosuggest-results').style.display = 'none';
    });

    // Trigger initial placeholder
    document.getElementById('quick-category').dispatchEvent(new Event('change'));

    // Trigger initial placeholder (moved before global function def)
    document.getElementById('quick-category').dispatchEvent(new Event('change'));
});

// ===== GLOBAL: CATEGORY-FILTERED SUGGESTIONS ON FOCUS (must be global — called from other DOMContentLoaded blocks & inline) =====
function showAutosuggestForCategory(input, category) {
    console.log('showAutosuggestForCategory called with:', category);
    var dd = document.getElementById('autosuggest-results');
    if (!dd) { console.log('ERROR: autosuggest-results not found'); return; }
    var issues = issueLibrary[category];
    console.log('issues found:', issues ? issues.length : 0);
    if (!issues || issues.length === 0) { dd.style.display = 'none'; return; }

    dd.innerHTML = '';
    issues.slice(0, 8).forEach(function(issue) {
        var item = document.createElement('button');
        item.type = 'button';
        item.className = 'list-group-item list-group-item-action';
        item.innerHTML = '<strong>' + issue.title + '</strong> <span class="category-hint">[' + category + ']</span>'
            + '<br><small class="text-muted">' + issue.action + ' — ₱' + issue.avgCost + '</small>';
        item.addEventListener('click', function() {
            selectIssue({category: category, issue: issue}, input);
        });
        dd.appendChild(item);
    });
    dd.style.display = 'block';
}

// ===== MODAL AUTO-SUGGEST (runs on DOMContentLoaded) =====
document.addEventListener('DOMContentLoaded', function() {
    var modalTitle = document.getElementById('editIssueTitle');
    var modalCat = document.getElementById('editCategory');
    if (modalTitle && modalCat) {
        modalTitle.addEventListener('focus', function() {
            var cat = modalCat.value;
            if (this.value === '' && cat && issueLibrary[cat]) {
                var dd = document.getElementById('modal-autosuggest-results');
                if (!dd) return;
                var issues = issueLibrary[cat];
                dd.innerHTML = '';
                issues.slice(0, 8).forEach(function(issue) {
                    var item = document.createElement('button');
                    item.type = 'button';
                    item.className = 'list-group-item list-group-item-action';
                    item.innerHTML = '<strong>' + issue.title + '</strong> <span class="category-hint">[' + cat + ']</span>'
                        + '<br><small class="text-muted">' + issue.action + ' — ₱' + issue.avgCost + '</small>';
                    item.addEventListener('click', function() {
                        modalTitle.value = issue.title;
                        dd.style.display = 'none';
                    });
                    dd.appendChild(item);
                });
                dd.style.display = 'block';
            }
        });
        // Hide on blur
        modalTitle.addEventListener('blur', function() {
            setTimeout(function() {
                var dd = document.getElementById('modal-autosuggest-results');
                if (dd) dd.style.display = 'none';
            }, 200);
        });
    }
});
</script>
@endpush
