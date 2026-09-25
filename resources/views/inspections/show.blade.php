@extends('layouts.app')

@section('content')
@include('partials.customer-process-assets')
<div class="container-fluid px-4 py-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1" style="color: #1a237e;">
                <i class="fas fa-wrench me-2"></i>Repair Order {{ $inspection->appointment->appointment_number ?? '#'.$inspection->id }}
            </h4>
            <p class="text-muted mb-0">
                <i class="fas fa-calendar me-1"></i>
                {{ $inspection->created_at ? $inspection->created_at->format('F j, Y g:i A') : 'N/A' }}
                &middot; {{ is_string($inspection->inspection_type) ? ucfirst(str_replace('_', ' ', $inspection->inspection_type)) : 'Multi-Type' }}
                @if($inspection->is_from_quotation)
                    &middot; <span class="badge" style="background:#dcfce7;color:#166534;font-weight:600;" title="Galing sa Repair Quotation"><i class="fas fa-file-invoice-dollar me-1"></i>From Quotation</span>
                @elseif($inspection->is_walk_in)
                    &middot; <span class="badge" style="background:#fef3c7;color:#92400e;font-weight:600;" title="Walk-in (walang schedule)"><i class="fas fa-person-walking me-1"></i>Walk-in</span>
                @elseif($inspection->appointment_id || $inspection->job_order_id)
                    &middot; <span class="badge" style="background:#dbeafe;color:#1e40af;font-weight:600;" title="Dumaan sa schedule (appointment / job order)"><i class="fas fa-calendar-check me-1"></i>Scheduled</span>
                @endif
                @if($inspection->date_received)
                    &middot; <i class="fas fa-inbox me-1"></i>Received {{ $inspection->date_received->format('M d, Y') }}
                @endif
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('inspections.repair-order-slip', $inspection) }}" class="btn btn-outline-dark" target="_blank" title="Printable Repair Order slip">
                <i class="fas fa-file-invoice me-1"></i>Repair Order Slip
            </a>
            <a href="{{ route('inspections.quotation-slip', $inspection) }}" class="btn btn-outline-success" target="_blank" title="Printable Repair Quotation from this Repair Order's findings">
                <i class="fas fa-print me-1"></i>Print Repair Quotation
            </a>
            @if(isset($inspection->estimate) && $inspection->estimate)
                <a href="{{ route('estimates.edit', $inspection->estimate) }}" class="btn btn-outline-success" title="Open the linked Repair Quotation">
                    <i class="fas fa-file-invoice-dollar me-1"></i>View Repair Quotation
                </a>
                @php
                    // Findings discovered during the repair (added AFTER the quotation was
                    // locked). These are the only ones a fresh Repair Quotation should carry.
                    // The count is only meaningful once the original quotation is approved and
                    // its findings are frozen — before that, nothing is locked so every finding
                    // would count as "new" and the button would just duplicate the pending
                    // quotation. Hence the isFindingsLocked() gate.
                    $newFindingsCount = $inspection->isFindingsLocked()
                        ? $inspection->inspectionFindings
                            ->filter(fn ($f) => ! $f->is_declined && ! $inspection->findingIsLocked($f))
                            ->count()
                        : 0;
                @endphp
                @if($newFindingsCount > 0)
                <a href="{{ route('estimates.create', ['customer_id' => $inspection->customer_id, 'vehicle_id' => $inspection->vehicle_id, 'inspection_id' => $inspection->id, 'new_only' => 1]) }}" class="btn btn-success" title="Create a Repair Quotation for the findings discovered during the repair">
                    <i class="fas fa-plus me-1"></i>Create Repair Quotation ({{ $newFindingsCount }})
                </a>
                @endif
            @else
                @php
                    // Findings shown on the Findings tab (declined items excluded).
                    // A fresh Repair Quotation only makes sense once something was
                    // actually found, so the button stays disabled while it is empty.
                    $roFindingsCount = $inspection->inspectionFindings
                        ->where('is_declined', false)
                        ->count();
                @endphp
                @if($roFindingsCount > 0)
                    <a href="{{ route('estimates.create', ['customer_id' => $inspection->customer_id, 'vehicle_id' => $inspection->vehicle_id, 'inspection_id' => $inspection->id]) }}" class="btn btn-success" title="Create a Repair Quotation from this Repair Order's findings">
                        <i class="fas fa-file-invoice-dollar me-1"></i>Create Repair Quotation
                    </a>
                @else
                    <button type="button" class="btn btn-success" disabled
                            title="Wala pang findings. Magdagdag muna sa Findings tab bago gumawa ng Repair Quotation."
                            style="cursor:not-allowed;opacity:.55;">
                        <i class="fas fa-file-invoice-dollar me-1"></i>Create Repair Quotation
                    </button>
                @endif
            @endif
            <a href="{{ route('inspections.edit', $inspection) }}" class="btn btn-primary">
                <i class="fas fa-edit me-1"></i>Edit
            </a>
            <a href="{{ route('inspections.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>Back
            </a>
        </div>
    </div>

    @if(session('success'))
        <div id="fixit-flash-success" data-msg="{{ session('success') }}" style="display:none;"></div>
    @endif
    @if(session('error'))
        <div id="fixit-flash-error" data-msg="{{ session('error') }}" style="display:none;"></div>
    @endif

    <!-- Section Navigation Pills -->
    <ul class="nav nav-pills section-nav mb-4" id="detailTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="overview-tab" data-bs-toggle="pill" data-bs-target="#overview" type="button" role="tab">
                <i class="fas fa-info-circle me-1"></i>Overview
            </button>
        </li>
        @if($inspection->isFindingsLocked())
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="from-quotation-tab" data-bs-toggle="pill" data-bs-target="#from-quotation" type="button" role="tab">
                <i class="fas fa-file-invoice me-1"></i>From Quotation
            </button>
        </li>
        @endif
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="findings-tab" data-bs-toggle="pill" data-bs-target="#findings" type="button" role="tab">
                <i class="fas fa-clipboard-check me-1"></i>Findings
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
                @php
                    $ro = $inspection->appointment ?? null;
                    $svcList = config('service-types.list', []);
                    $roServices = [];
                    $rawSvc = $inspection->service_types ?: ($ro->service_types ?? null);
                    if ($rawSvc) {
                        if (is_string($rawSvc)) {
                            $dec = json_decode($rawSvc, true);
                            $roServices = is_array($dec) ? $dec : ($rawSvc !== '' ? [$rawSvc] : []);
                        } elseif (is_array($rawSvc)) {
                            $roServices = $rawSvc;
                        }
                    }
                    $roJob = $inspection->job_description_items ?: (($ro && is_array($ro->job_description_items)) ? $ro->job_description_items : []);
                    $roParts = $inspection->parts_items ?: (($ro && is_array($ro->parts_items)) ? $ro->parts_items : []);
                @endphp

                <!-- Left Column -->
                <div class="col-lg-8">

                    <!-- Customer Information -->
                    @if($inspection->customer)
                    <div class="form-section">
                        <div class="form-section-header no-collapse">
                            <h6><i class="fas fa-user"></i>Customer Information</h6>
                        </div>
                        <div class="form-section-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="text-muted small text-uppercase">Name</label>
                                    <p class="fw-semibold mb-0">{{ $inspection->customer->full_name ?? trim(($inspection->customer->first_name ?? '').' '.($inspection->customer->last_name ?? '')) }}</p>
                                </div>
                                <div class="col-md-6">
                                    <label class="text-muted small text-uppercase">Mobile No.</label>
                                    <p class="fw-semibold mb-0">{{ $inspection->customer->phone ?? 'N/A' }}</p>
                                </div>
                                <div class="col-md-6">
                                    <label class="text-muted small text-uppercase">Email</label>
                                    <p class="fw-semibold mb-0">{{ $inspection->customer->email ?? 'N/A' }}</p>
                                </div>
                                <div class="col-md-6">
                                    <label class="text-muted small text-uppercase">Address</label>
                                    <p class="fw-semibold mb-0">{{ ($inspection->customer->address ?? null) ?: 'N/A' }}{{ ($inspection->customer->city ?? null) ? ', '.$inspection->customer->city : '' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

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
                                        <label class="text-muted small text-uppercase">Engine No.</label>
                                        <p class="fw-semibold mb-0 text-monospace">{{ $inspection->vehicle->engine_no ?? 'N/A' }}</p>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="text-muted small text-uppercase">Transmission</label>
                                        <p class="fw-semibold mb-0">{{ $inspection->vehicle->transmission ?? 'N/A' }}</p>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="text-muted small text-uppercase">Fuel</label>
                                        <p class="fw-semibold mb-0">{{ $inspection->vehicle->fuel_type ?? 'N/A' }}</p>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="text-muted small text-uppercase">Odometer</label>
                                        @php
                                            // Show this Repair Order's *own* intake reading (vehicle_mileage)
                                            // when it has one; a fresh (quotation-promoted) RO is reset to 0,
                                            // so don't fall through to the vehicle master's older reading.
                                            $ovm = $inspection->vehicle_mileage;
                                            if ($ovm !== null) {
                                                $odoText = ((float) $ovm) > 0 ? number_format((float) $ovm) : 'N/A';
                                            } else {
                                                $odoText = optional($inspection->vehicle)->odometer ? number_format((float) $inspection->vehicle->odometer) : 'N/A';
                                            }
                                        @endphp
                                        <p class="fw-semibold mb-0">{{ $odoText }}</p>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="text-muted small text-uppercase">Color</label>
                                        <p class="fw-semibold mb-0">{{ $inspection->vehicle->color ?? 'N/A' }}</p>
                                    </div>
                                </div>
                            @else
                                <p class="text-muted mb-0"><i class="fas fa-exclamation-circle me-1"></i>No vehicle assigned</p>
                            @endif
                        </div>
                    </div>

                    <!-- Services & Job Description -->
                    <div class="form-section">
                        <div class="form-section-header no-collapse">
                            <h6><i class="fas fa-clipboard-list"></i>Services &amp; Job Description</h6>
                        </div>
                        <div class="form-section-body">
                            @if(!empty($roServices))
                                <div class="mb-3">
                                    @foreach($roServices as $svc)
                                        <span class="badge bg-soft-primary text-primary me-1 mb-1" style="font-weight:500;">{{ $svcList[$svc] ?? ucwords(str_replace('_', ' ', $svc)) }}</span>
                                    @endforeach
                                </div>
                            @endif
                            @if(count($roJob) > 0)
                                <div class="table-responsive">
                                    <table class="table table-sm align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width:42%">Job Description</th>
                                                <th style="width:12%">MH</th>
                                                <th style="width:18%">Unit Price</th>
                                                <th style="width:20%">Labor Cost</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($roJob as $row)
                                                <tr>
                                                    <td>{{ is_array($row) ? ($row['description'] ?? '') : $row }}</td>
                                                    <td>{{ is_array($row) ? ($row['mh'] ?? '') : '' }}</td>
                                                    <td>{{ is_array($row) ? ($row['unit_price'] ?? '') : '' }}</td>
                                                    <td>{{ is_array($row) ? ($row['labor_cost'] ?? '') : '' }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @elseif(empty($roServices))
                                <p class="text-muted mb-0 small">No job description recorded.</p>
                            @endif
                        </div>
                    </div>

                    <!-- Parts / Supplies -->
                    @if(count($roParts) > 0)
                    <div class="form-section">
                        <div class="form-section-header no-collapse">
                            <h6><i class="fas fa-boxes-stacked"></i>Parts / Supplies</h6>
                        </div>
                        <div class="form-section-body">
                            <div class="table-responsive">
                                <table class="table table-sm align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width:46%">Parts / Supplies Description</th>
                                            <th style="width:12%">Qty</th>
                                            <th style="width:18%">Unit Price</th>
                                            <th style="width:16%">Cost</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($roParts as $row)
                                            <tr>
                                                <td>{{ is_array($row) ? ($row['description'] ?? '') : $row }}</td>
                                                <td>{{ is_array($row) ? ($row['qty'] ?? '') : '' }}</td>
                                                <td>{{ is_array($row) ? ($row['unit_price'] ?? '') : '' }}</td>
                                                <td>{{ is_array($row) ? ($row['cost'] ?? '') : '' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Concern / Request -->
                    @if(($ro && $ro->service_request) || $inspection->customer_concerns)
                    <div class="form-section">
                        <div class="form-section-header no-collapse">
                            <h6><i class="fas fa-comment-dots"></i>Concern / Request</h6>
                        </div>
                        <div class="form-section-body">
                            <p class="mb-0" style="white-space: pre-wrap;">{{ ($ro && $ro->service_request) ? $ro->service_request : $inspection->customer_concerns }}</p>
                        </div>
                    </div>
                    @endif

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

                    <!-- Related Records -->
                    <div class="form-section">
                        <div class="form-section-header no-collapse">
                            <h6><i class="fas fa-link"></i>Related Records</h6>
                        </div>
                        <div class="form-section-body">
                            @php
                                $relations = [];
                                if($inspection->jobOrder) $relations[] = ['name' => 'Work Order', 'icon' => 'fa-clipboard-list', 'route' => route('job_orders.show', $inspection->jobOrder), 'color' => 'primary'];
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

                    {{-- Payment Verification (upload down/full payment + proof, verify) --}}
                    @include('inspections.partials.payment-verification', ['inspection' => $inspection])
                </div>
            </div>
        </div>

        @if($inspection->isFindingsLocked())
        <!-- === FROM QUOTATION TAB (read-only approved items) === -->
        <div class="tab-pane fade" id="from-quotation" role="tabpanel">
            <div class="alert d-flex flex-wrap align-items-center gap-2 mb-3" style="background:#fff7ed;border:1px solid #fdba74;color:#9a3412;">
                <i class="fas fa-lock"></i>
                <div class="flex-grow-1">
                    <strong>Approved na ito mula sa Repair Quotation.</strong>
                    Naka-lock ang parts at labor presyo. Kung may bagong makita habang ginagawa, ilagay sa <strong>Findings</strong> tab.
                </div>
                @if(in_array(auth()->user()->role ?? null, ['admin','super_admin','owner']))
                <form method="POST" action="{{ route('inspections.unlock-findings', $inspection) }}" onsubmit="return confirm('I-unlock ang findings? Mababago na ulit ang presyo ng lahat ng findings.');">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-unlock me-1"></i>Unlock findings</button>
                </form>
                @endif
            </div>
            @include('inspections.partials.findings-readonly')
        </div>
        @endif

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

            @include('inspections.partials.findings-board', ['findingsLocked' => $inspection->isFindingsLocked()])
        </div>

        @include('inspections.partials.findings-modal')


        <!-- === PHOTOS TAB === -->
        <div class="tab-pane fade" id="photos" role="tabpanel">
            @php
                $photos = is_array($inspection->photos) ? $inspection->photos : (json_decode($inspection->photos ?? '[]', true) ?? []);
                $photoUrl = function ($photo) {
                    if (is_array($photo)) {
                        if (!empty($photo['url'])) { return $photo['url']; }
                        if (!empty($photo['path_url'])) { return $photo['path_url']; }
                        if (!empty($photo['path'])) { return asset('storage/' . $photo['path']); }
                        return '';
                    }
                    if (is_string($photo)) {
                        return preg_match('#^https?://#i', $photo) ? $photo : asset('storage/' . $photo);
                    }
                    return '';
                };
                $photoCaption = function ($photo) {
                    return is_array($photo) ? ($photo['caption'] ?? $photo['description'] ?? '') : '';
                };
            @endphp

            {{-- Upload --}}
            <div class="form-section mb-3">
                <div class="form-section-header no-collapse">
                    <h6><i class="fas fa-cloud-upload-alt"></i>Upload Photos</h6>
                </div>
                <div class="form-section-body">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-9">
                            <label class="form-label small mb-1">Photos</label>
                            <input type="file" id="photoUploadInput" class="form-control form-control-sm" accept="image/*" multiple>
                        </div>
                        <div class="col-md-3 d-grid">
                            <button type="button" class="btn btn-primary btn-sm" id="photoUploadBtn" onclick="uploadInspectionPhotos()">
                                <i class="fas fa-upload me-1"></i>Upload
                            </button>
                        </div>
                    </div>
                    <div class="form-text">Pumili ng isa o maraming litrato (JPG / PNG / GIF, hanggang 5MB bawat isa).</div>
                </div>
            </div>

            {{-- Empty state --}}
            <div class="form-section" id="photosEmptyState" @if(count($photos) > 0) style="display:none;" @endif>
                <div class="form-section-header no-collapse">
                    <h6><i class="fas fa-camera"></i>Photos</h6>
                </div>
                <div class="form-section-body text-center py-4">
                    <i class="fas fa-image fa-3x text-muted mb-3"></i>
                    <p class="text-muted mb-0">No photos attached to this inspection.</p>
                </div>
            </div>

            {{-- Gallery --}}
            <div class="row g-3" id="photosGallery">
                @foreach($photos as $photo)
                    <div class="col-md-4 col-lg-3 photo-card" data-index="{{ $loop->index }}">
                        <div class="form-section position-relative">
                            <button type="button" class="btn btn-danger btn-sm position-absolute"
                                    style="top:.4rem;right:.4rem;z-index:2;" title="Delete photo"
                                    onclick="deleteInspectionPhoto({{ $loop->index }})">
                                <i class="fas fa-trash"></i>
                            </button>
                            <div class="form-section-body p-2">
                                <img src="{{ $photoUrl($photo) }}" alt="Inspection photo"
                                     class="img-fluid rounded" style="width:100%;height:180px;object-fit:cover;cursor:pointer;"
                                     onclick="window.open(this.src, '_blank')">
                                @if($photoCaption($photo))
                                    <p class="small text-muted mt-1 mb-0 px-1">{{ $photoCaption($photo) }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
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

    {{-- Repair Order History — what has happened on this Repair Order --}}
    @include('inspections.partials.repair-order-history', ['inspection' => $inspection])

</div>

@endsection

@push('styles')
    @include('inspections.partials.findings-styles')
@endpush

@push('scripts')
  @include('inspections.partials.inspection-findings-scripts')
@endpush

