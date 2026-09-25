@extends('layouts.app')

@section('content')
@include('partials.customer-process-assets')
<style>
/* Align the Repair Order edit sections with the Update Information card look */
#mainForm .form-section {
    background: #fff;
    border: 0;
    border-radius: .5rem;
    box-shadow: 0 1px .25rem rgba(0, 0, 0, .075);
    margin-bottom: 1rem;
}
#mainForm .form-section-header {
    background: #fff;
    border-bottom: 1px solid rgba(0, 0, 0, .125);
    padding: 1rem 1.25rem;
    border-radius: .5rem .5rem 0 0;
}
#mainForm .form-section-header:hover { background: #fff; }
#mainForm .form-section-header h6 {
    font-size: 1.25rem;
    font-weight: 500;
    color: inherit;
    line-height: 1.2;
}
#mainForm .form-section-header h6 i { color: inherit; width: auto; }
#mainForm .form-section-body { padding: 1.25rem; }
@media (max-width: 576px) {
    #mainForm .form-section-header { padding: .85rem 1rem; }
    #mainForm .form-section-body { padding: 1rem; }
}
[data-theme="dark"] #mainForm .form-section { background: var(--dark-card); border-color: var(--dark-border); }
[data-theme="dark"] #mainForm .form-section-header { background: var(--dark-surface); border-bottom-color: var(--dark-border); }
[data-theme="dark"] #mainForm .form-section-header:hover { background: var(--dark-hover); }
</style>
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1" style="color: #1a237e;">
                <i class="fas fa-file-invoice me-2"></i>Edit Repair Order #{{ $inspection->id }}
            </h4>
            <p class="text-muted mb-0">Update repair order details, line items, and findings</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('inspections.repair-order-slip', $inspection) }}" target="_blank" class="btn btn-outline-dark">
                <i class="fas fa-file-invoice me-1"></i>Review Repair Order Slip
            </a>
            <a href="{{ route('inspections.quotation-slip', $inspection) }}" target="_blank" class="btn btn-outline-success">
                <i class="fas fa-print me-1"></i>Repair Quotation
            </a>
            <a href="{{ route('inspections.show', $inspection) }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>Back to Repair Order
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-3" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>Please correct the errors below.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form action="{{ route('inspections.update', $inspection) }}" method="POST" id="mainForm">
        @csrf
        @method('PUT')

        <!-- Customer Summary -->
        @if($inspection->customer)
            @include('partials.customer-summary-card', ['customer' => $inspection->customer])
        @endif

        <!-- Section Navigation Pills -->
        <ul class="nav nav-pills section-nav mb-4" id="editTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="basic-tab" data-bs-toggle="pill" data-bs-target="#basic" type="button" role="tab">
                    <i class="fas fa-info-circle me-1"></i>Basic Info
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="findings-tab" data-bs-toggle="pill" data-bs-target="#findings" type="button" role="tab">
                    <i class="fas fa-clipboard-check me-1"></i>Findings
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="team-tab" data-bs-toggle="pill" data-bs-target="#team" type="button" role="tab">
                    <i class="fas fa-users me-1"></i>Team &amp; Status
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="relations-tab" data-bs-toggle="pill" data-bs-target="#relations" type="button" role="tab">
                    <i class="fas fa-link me-1"></i>Related Records
                </button>
            </li>
        </ul>

        <div class="tab-content">

            <!-- === BASIC INFO === -->
            <div class="tab-pane fade show active" id="basic" role="tabpanel">
                {{-- Inspection Details panel removed: a Repair Order is not an inspection.
                     inspection_name / inspection_type / inspection_status are kept as hidden
                     fields so the existing form submit still carries them through unchanged. --}}
                <input type="hidden" name="inspection_name" value="Repair Order #{{ $inspection->id }}">
                <input type="hidden" name="inspection_type" value="{{ is_array($inspection->inspection_type) ? '' : $inspection->inspection_type }}">
                <input type="hidden" name="inspection_status" value="{{ $inspection->inspection_status }}">

                <!-- Receipt -->
                <div class="form-section">
                    <div class="form-section-header" onclick="toggleSection(this)">
                        <h6><i class="fas fa-calendar-check"></i>Receipt</h6>
                        <i class="fas fa-chevron-down text-muted"></i>
                    </div>
                    <div class="form-section-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="date_received" class="form-label fw-medium">
                                    <i class="fas fa-calendar-day me-1 text-primary"></i>Date Received
                                </label>
                                <input type="date" class="form-control" id="date_received" name="date_received"
                                       value="{{ old('date_received', $inspection->appointment?->date_received?->format('Y-m-d')) }}">
                                <div class="form-text">Petsa na natanggap ang sasakyan.</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Customer -->
                <div class="form-section">
                    <div class="form-section-header" onclick="toggleSection(this)">
                        <h6><i class="fas fa-user"></i>Customer</h6>
                        <i class="fas fa-chevron-down text-muted"></i>
                    </div>
                    <div class="form-section-body">
                        <div class="row g-3 mb-1">
                            <div class="col-md-6">
                                <label for="customer_id" class="form-label fw-medium">
                                    <i class="fas fa-exchange-alt me-1 text-primary"></i>Linked Customer
                                </label>
                                <select class="form-select @error('customer_id') is-invalid @enderror" id="customer_id" name="customer_id">
                                    <option value="">Select customer...</option>
                                    @foreach($customers as $customer)
                                        <option value="{{ $customer->id }}" {{ old('customer_id', $inspection->customer_id) == $customer->id ? 'selected' : '' }}>
                                            {{ $customer->full_name ?? $customer->first_name }} {{ $customer->last_name ?? '' }}
                                            @if($customer->phone) - {{ $customer->phone }} @endif
                                        </option>
                                    @endforeach
                                </select>
                                @error('customer_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <p class="text-muted small">Punan ang mga kulang o maling detalye. Ang iiwan na blangko ay hindi mababago.</p>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">First Name</label>
                                <input type="text" class="form-control" name="first_name" value="{{ old('first_name', $inspection->customer->first_name ?? '') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Last Name</label>
                                <input type="text" class="form-control" name="last_name" value="{{ old('last_name', $inspection->customer->last_name ?? '') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Mobile No.</label>
                                <input type="text" class="form-control" name="phone" value="{{ old('phone', $inspection->customer->phone ?? '') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" name="email" value="{{ old('email', $inspection->customer->email ?? '') }}">
                            </div>
                            <div class="col-md-8">
                                <label class="form-label">Address</label>
                                <input type="text" class="form-control" name="address" value="{{ old('address', $inspection->customer->address ?? '') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">City</label>
                                <input type="text" class="form-control" name="city" value="{{ old('city', $inspection->customer->city ?? '') }}">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Vehicle -->
                <div class="form-section">
                    <div class="form-section-header" onclick="toggleSection(this)">
                        <h6><i class="fas fa-car"></i>Vehicle</h6>
                        <i class="fas fa-chevron-down text-muted"></i>
                    </div>
                    <div class="form-section-body">
                        <div class="row g-3 mb-1">
                            <div class="col-md-6">
                                <label for="vehicle_id" class="form-label fw-medium">
                                    <i class="fas fa-exchange-alt me-1 text-primary"></i>Linked Vehicle
                                </label>
                                <select class="form-select @error('vehicle_id') is-invalid @enderror" id="vehicle_id" name="vehicle_id" data-initial="{{ old('vehicle_id', $inspection->vehicle_id) }}">
                                    <option value="">Select vehicle...</option>
                                    @foreach($vehicles as $vehicle)
                                        <option value="{{ $vehicle->id }}" {{ old('vehicle_id', $inspection->vehicle_id) == $vehicle->id ? 'selected' : '' }}>
                                            {{ $vehicle->year }} {{ $vehicle->make }} {{ $vehicle->model }}
                                            @if($vehicle->license_plate) ({{ $vehicle->license_plate }}) @endif
                                            @if($vehicle->customer) - {{ $vehicle->customer->full_name ?? $vehicle->customer->first_name }} @endif
                                        </option>
                                    @endforeach
                                </select>
                                @error('vehicle_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Brand / Make</label>
                                <input type="text" class="form-control" name="make" value="{{ old('make', $inspection->vehicle->make ?? '') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Model</label>
                                <input type="text" class="form-control" name="model" value="{{ old('model', $inspection->vehicle->model ?? '') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Year Model</label>
                                <input type="text" class="form-control" name="year" value="{{ old('year', $inspection->vehicle->year ?? '') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Plate No.</label>
                                <input type="text" class="form-control" name="license_plate" value="{{ old('license_plate', $inspection->vehicle->license_plate ?? '') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">VIN No.</label>
                                <input type="text" class="form-control" name="vin" value="{{ old('vin', $inspection->vehicle->vin ?? '') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Engine No.</label>
                                <input type="text" class="form-control" name="engine_no" value="{{ old('engine_no', $inspection->vehicle->engine_no ?? '') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Transmission</label>
                                @php $trans = old('transmission', $inspection->vehicle->transmission ?? ''); @endphp
                                <select class="form-select" name="transmission">
                                    <option value="">-- piliin --</option>
                                    <option value="AT" {{ $trans === 'AT' ? 'selected' : '' }}>AT (Automatic)</option>
                                    <option value="MT" {{ $trans === 'MT' ? 'selected' : '' }}>MT (Manual)</option>
                                    <option value="CVT" {{ $trans === 'CVT' ? 'selected' : '' }}>CVT</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Fuel</label>
                                @php $fuel = old('fuel_type', $inspection->vehicle->fuel_type ?? ''); @endphp
                                <select class="form-select" name="fuel_type">
                                    <option value="">-- piliin --</option>
                                    <option value="Gas" {{ $fuel === 'Gas' ? 'selected' : '' }}>Gas</option>
                                    <option value="Diesel" {{ $fuel === 'Diesel' ? 'selected' : '' }}>Diesel</option>
                                    <option value="Hybrid" {{ $fuel === 'Hybrid' ? 'selected' : '' }}>Hybrid</option>
                                    <option value="Electric" {{ $fuel === 'Electric' ? 'selected' : '' }}>Electric</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Odometer</label>
                                {{-- ROs promoted from a quotation are a fresh intake: the client is
                                     coming back with a higher reading, so start the odometer at zero
                                     (blank) and let the user enter the current reading. --}}
                                @if($inspection->is_from_quotation)
                                    <input type="text" class="form-control" name="odometer"
                                           value="{{ old('odometer', '') }}" placeholder="0" inputmode="numeric">
                                    <small style="color:#dc2626;font-size:.72rem;">
                                        <i class="fas fa-rotate-left me-1"></i>Na-reset sa zero — i-type ang bagong odometer reading.
                                    </small>
                                @else
                                    <input type="text" class="form-control" name="odometer" value="{{ old('odometer', $inspection->vehicle->odometer ?? '') }}">
                                @endif
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Color</label>
                                <input type="text" class="form-control" name="color" value="{{ old('color', $inspection->vehicle->color ?? '') }}">
                            </div>
                        </div>
                    </div>
                </div>

                @php
                    $rawTypes = old('service_types', $inspection->service_types ?: ($inspection->appointment->service_types ?? null));
                    if (is_string($rawTypes)) {
                        $decoded = json_decode($rawTypes, true);
                        $selectedTypes = is_array($decoded) ? $decoded : ($rawTypes !== '' ? [$rawTypes] : []);
                    } elseif (is_array($rawTypes)) {
                        $selectedTypes = $rawTypes;
                    } else {
                        $selectedTypes = [];
                    }
                    if (empty($selectedTypes) && $inspection->service_type) { $selectedTypes = [$inspection->service_type]; }
                @endphp
                <div class="form-section">
                    <div class="form-section-header" onclick="toggleSection(this)">
                        <h6><i class="fas fa-tools"></i>Services</h6>
                        <i class="fas fa-chevron-down text-muted"></i>
                    </div>
                    <div class="form-section-body">
                        <p class="text-muted small mb-3">Pwedeng higit sa isa (hal. PMS + Aircon + Basic Tune Up).</p>
                        <div class="row g-2">
                            @foreach(config('service-types.list', []) as $svcKey => $svcLabel)
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="service_types[]"
                                               value="{{ $svcKey }}" id="svc_{{ $svcKey }}"
                                               {{ in_array($svcKey, $selectedTypes, true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="svc_{{ $svcKey }}">{{ $svcLabel }}</label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                @php
                    $jdItems = old('job_description_items', $inspection->job_description_items ?: ($inspection->appointment->job_description_items ?? []));
                    if (!is_array($jdItems)) { $jdItems = []; }
                    $partsItems = old('parts_items', $inspection->parts_items ?: ($inspection->appointment->parts_items ?? []));
                    if (!is_array($partsItems)) { $partsItems = []; }
                @endphp
                <div class="form-section">
                    <div class="form-section-header" onclick="toggleSection(this)">
                        <h6><i class="fas fa-clipboard-list"></i>Job Description</h6>
                        <i class="fas fa-chevron-down text-muted"></i>
                    </div>
                    <div class="form-section-body">
                        <div class="d-flex justify-content-end mb-2">
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="rosAddJobRow()"><i class="fas fa-plus me-1"></i>Add row</button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th style="width:42%">Job Description</th>
                                        <th style="width:12%">MH</th>
                                        <th style="width:18%">Unit Price</th>
                                        <th style="width:20%">Labor Cost</th>
                                        <th style="width:8%"></th>
                                    </tr>
                                </thead>
                                <tbody id="rosJobBody">
                                    @foreach($jdItems as $i => $row)
                                        <tr>
                                            <td><input type="text" class="form-control form-control-sm" name="job_description_items[{{ $i }}][description]" value="{{ is_array($row) ? ($row['description'] ?? '') : ($row ?? '') }}"></td>
                                            <td><input type="number" step="0.01" min="0" class="form-control form-control-sm ros-jd-mh" name="job_description_items[{{ $i }}][mh]" value="{{ is_array($row) ? ($row['mh'] ?? '') : '' }}"></td>
                                            <td><input type="number" step="0.01" min="0" class="form-control form-control-sm ros-jd-unit" name="job_description_items[{{ $i }}][unit_price]" value="{{ is_array($row) ? ($row['unit_price'] ?? '') : '' }}"></td>
                                            <td><input type="number" step="0.01" min="0" class="form-control form-control-sm ros-jd-cost" name="job_description_items[{{ $i }}][labor_cost]" value="{{ is_array($row) ? ($row['labor_cost'] ?? '') : '' }}"></td>
                                            <td class="text-end"><button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('tr').remove()">&times;</button></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <div class="form-section-header" onclick="toggleSection(this)">
                        <h6><i class="fas fa-cog"></i>Parts &amp; Supplies</h6>
                        <i class="fas fa-chevron-down text-muted"></i>
                    </div>
                    <div class="form-section-body">
                        <div class="d-flex justify-content-end mb-2">
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="rosAddPartRow()"><i class="fas fa-plus me-1"></i>Add row</button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th style="width:42%">Parts Description</th>
                                        <th style="width:12%">Qty</th>
                                        <th style="width:18%">Unit Price</th>
                                        <th style="width:20%">Cost</th>
                                        <th style="width:8%"></th>
                                    </tr>
                                </thead>
                                <tbody id="rosPartBody">
                                    @foreach($partsItems as $i => $row)
                                        <tr>
                                            <td><input type="text" class="form-control form-control-sm" name="parts_items[{{ $i }}][description]" value="{{ is_array($row) ? ($row['description'] ?? '') : ($row ?? '') }}"></td>
                                            <td><input type="number" step="0.01" min="0" class="form-control form-control-sm ros-pt-qty" name="parts_items[{{ $i }}][qty]" value="{{ is_array($row) ? ($row['qty'] ?? '') : '' }}"></td>
                                            <td><input type="number" step="0.01" min="0" class="form-control form-control-sm ros-pt-unit" name="parts_items[{{ $i }}][unit_price]" value="{{ is_array($row) ? ($row['unit_price'] ?? '') : '' }}"></td>
                                            <td><input type="number" step="0.01" min="0" class="form-control form-control-sm ros-pt-cost" name="parts_items[{{ $i }}][cost]" value="{{ is_array($row) ? ($row['cost'] ?? '') : '' }}"></td>
                                            <td class="text-end"><button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('tr').remove()">&times;</button></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="row g-3 mt-1">
                            <div class="col-md-4">
                                <label class="form-label">Discount (&#8369;)</label>
                                <input type="number" step="0.01" min="0" class="form-control" name="discount" value="{{ old('discount', $inspection->discount ?: ($inspection->appointment->discount ?? '')) }}">
                                <div class="form-text">Lalabas ito sa printed Repair Order slip.</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <div class="form-section-header" onclick="toggleSection(this)">
                        <h6><i class="fas fa-comment-dots"></i>Concern / Request</h6>
                        <i class="fas fa-chevron-down text-muted"></i>
                    </div>
                    <div class="form-section-body">
                        <textarea class="form-control" name="service_request" rows="4">{{ old('service_request', $inspection->appointment->service_request ?? $inspection->customer_concerns ?? '') }}</textarea>                    </div>
                </div>
            </div>

            <!-- === FINDINGS === -->
            <div class="tab-pane fade" id="findings" role="tabpanel">
                <div class="form-section">
                    <div class="form-section-header" onclick="toggleSection(this)">
                        <h6><i class="fas fa-stethoscope"></i>Technician Findings</h6>
                        <i class="fas fa-chevron-down text-muted"></i>
                    </div>
                    <div class="form-section-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="technician_notes" class="form-label fw-medium">
                                    <i class="fas fa-notes-medical me-1 text-primary"></i>Technician Notes &amp; Findings
                                </label>
                                <textarea class="form-control @error('technician_notes') is-invalid @enderror"
                                          id="technician_notes" name="technician_notes" rows="5"
                                          placeholder="Detailed findings from the inspection...">{{ old('technician_notes', $inspection->technician_notes) }}</textarea>
                                @error('technician_notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- === TEAM & STATUS === -->
            <div class="tab-pane fade" id="team" role="tabpanel">
                <div class="form-section">
                    <div class="form-section-header" onclick="toggleSection(this)">
                        <h6><i class="fas fa-user-cog"></i>Assigned Team</h6>
                        <i class="fas fa-chevron-down text-muted"></i>
                    </div>
                    <div class="form-section-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="technician_id" class="form-label fw-medium">
                                    <i class="fas fa-user-cog me-1 text-primary"></i>Lead Technician
                                </label>
                                <select class="form-select @error('technician_id') is-invalid @enderror" id="technician_id" name="technician_id">
                                    <option value="">Select lead technician...</option>
                                    @foreach($technicians as $tech)
                                        <option value="{{ $tech->id }}" {{ old('technician_id', $inspection->technician_id) == $tech->id ? 'selected' : '' }}>
                                            {{ $tech->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('technician_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label for="service_advisor_id" class="form-label fw-medium">
                                    <i class="fas fa-user-tie me-1 text-primary"></i>Service Advisor
                                </label>
                                <select class="form-select @error('service_advisor_id') is-invalid @enderror" id="service_advisor_id" name="service_advisor_id">
                                    <option value="">Select advisor...</option>
                                    @foreach($advisors as $advisor)
                                        <option value="{{ $advisor->id }}" {{ old('service_advisor_id', $inspection->service_advisor_id) == $advisor->id ? 'selected' : '' }}>
                                            {{ $advisor->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('service_advisor_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        
                        <!-- Multi-Technician -->
                        <div class="row g-3 mt-2">
                            <div class="col-12">
                                @php $existingTechIds = $inspection->technicians->pluck('id')->toArray(); @endphp
                                @include('partials.technician-selector', [
                                    'technicians' => $allTechnicians,
                                    'selectedIds' => old('technicians', $existingTechIds),
                                    'label' => 'Additional Technicians',
                                    'helpText' => 'Assign additional technicians to perform this inspection',
                                ])
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- === RELATED RECORDS === -->
            <div class="tab-pane fade" id="relations" role="tabpanel">
                <div class="form-section">
                    <div class="form-section-header" onclick="toggleSection(this)">
                        <h6><i class="fas fa-link"></i>Link Related Records</h6>
                        <i class="fas fa-chevron-down text-muted"></i>
                    </div>
                    <div class="form-section-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="job_order_id" class="form-label fw-medium">
                                    <i class="fas fa-clipboard-list me-1 text-primary"></i>Work Order
                                </label>
                                <select class="form-select @error('job_order_id') is-invalid @enderror" id="job_order_id" name="job_order_id">
                                    <option value="">None</option>
                                    @foreach($jobOrders as $wo)
                                        <option value="{{ $wo->id }}" {{ old('job_order_id', $inspection->job_order_id) == $wo->id ? 'selected' : '' }}>
                                            #{{ $wo->id }} - {{ $wo->customer->full_name ?? $wo->customer->first_name ?? 'N/A' }} ({{ $wo->vehicle->make ?? '' }} {{ $wo->vehicle->model ?? '' }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('job_order_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label for="appointment_id" class="form-label fw-medium">
                                    <i class="fas fa-calendar-check me-1 text-primary"></i>Appointment
                                </label>
                                <select class="form-select @error('appointment_id') is-invalid @enderror" id="appointment_id" name="appointment_id">
                                    <option value="">None</option>
                                    @foreach($appointments as $apt)
                                        <option value="{{ $apt->id }}" {{ old('appointment_id', $inspection->appointment_id) == $apt->id ? 'selected' : '' }}>
                                            #{{ $apt->id }} - {{ $apt->customer->full_name ?? $apt->customer->first_name ?? 'N/A' }} ({{ $apt->appointment_date ? $apt->appointment_date->format('M j') : '' }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('appointment_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sticky Save Bar -->
        <div style="height: 70px;"></div>
        <div class="sticky-save-bar visible">
            <div class="save-info">
                <i class="fas fa-save text-primary"></i>
                <span>Repair Order #{{ $inspection->id }} changes</span>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('inspections.show', $inspection) }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-times me-1"></i> Cancel
                </a>
                <button type="submit" class="btn btn-primary btn-sm px-4">
                    <i class="fas fa-check me-1"></i> Save Changes
                </button>
            </div>
        </div>
    </form>

</div>
@endsection

@push('scripts')
<script>
// Toggle collapsible sections
function toggleSection(header) {
    var content = header.nextElementSibling;
    if (content) {
        content.style.display = content.style.display === 'none' ? 'block' : 'none';
        header.classList.toggle('collapsed');
    }
}

$(document).ready(function() {
    initTechnicianMultiSelect(".technician-select-wrapper:not([data-tech-init])");
});

// ===== Repair Order line items (Job Description / Parts) =====
(function () {
    var jobBody = document.getElementById('rosJobBody');
    var partBody = document.getElementById('rosPartBody');
    if (!jobBody || !partBody) { return; }
    var seq = 100000;

    function num(v) { var n = parseFloat(v); return isNaN(n) ? 0 : n; }
    function money(n) { return (Math.round(n * 100) / 100).toFixed(2); }

    window.rosAddJobRow = function (description, mh, unit, cost) {
        var i = seq++;
        var tr = document.createElement('tr');
        tr.innerHTML =
            '<td><input type="text" class="form-control form-control-sm" name="job_description_items[' + i + '][description]"></td>' +
            '<td><input type="number" step="0.01" min="0" class="form-control form-control-sm ros-jd-mh" name="job_description_items[' + i + '][mh]"></td>' +
            '<td><input type="number" step="0.01" min="0" class="form-control form-control-sm ros-jd-unit" name="job_description_items[' + i + '][unit_price]"></td>' +
            '<td><input type="number" step="0.01" min="0" class="form-control form-control-sm ros-jd-cost" name="job_description_items[' + i + '][labor_cost]"></td>' +
            '<td class="text-end"><button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest(\'tr\').remove()">&times;</button></td>';
        jobBody.appendChild(tr);
        if (description) { tr.querySelector('input[type=text]').value = description; }
        if (mh) { tr.querySelector('.ros-jd-mh').value = mh; }
        if (unit) { tr.querySelector('.ros-jd-unit').value = unit; }
        if (cost) { tr.querySelector('.ros-jd-cost').value = cost; }
        return tr;
    };

    window.rosAddPartRow = function (description, qty, unit, cost) {
        var i = seq++;
        var tr = document.createElement('tr');
        tr.innerHTML =
            '<td><input type="text" class="form-control form-control-sm" name="parts_items[' + i + '][description]"></td>' +
            '<td><input type="number" step="0.01" min="0" class="form-control form-control-sm ros-pt-qty" name="parts_items[' + i + '][qty]"></td>' +
            '<td><input type="number" step="0.01" min="0" class="form-control form-control-sm ros-pt-unit" name="parts_items[' + i + '][unit_price]"></td>' +
            '<td><input type="number" step="0.01" min="0" class="form-control form-control-sm ros-pt-cost" name="parts_items[' + i + '][cost]"></td>' +
            '<td class="text-end"><button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest(\'tr\').remove()">&times;</button></td>';
        partBody.appendChild(tr);
        if (description) { tr.querySelector('input[type=text]').value = description; }
        if (qty) { tr.querySelector('.ros-pt-qty').value = qty; }
        if (unit) { tr.querySelector('.ros-pt-unit').value = unit; }
        if (cost) { tr.querySelector('.ros-pt-cost').value = cost; }
        return tr;
    };

    document.addEventListener('input', function (e) {
        if (e.target.classList.contains('ros-jd-mh') || e.target.classList.contains('ros-jd-unit')) {
            var tr = e.target.closest('tr');
            var mh = num(tr.querySelector('.ros-jd-mh').value);
            var un = num(tr.querySelector('.ros-jd-unit').value);
            if (mh || un) { tr.querySelector('.ros-jd-cost').value = money(mh * un); }
        }
        if (e.target.classList.contains('ros-pt-qty') || e.target.classList.contains('ros-pt-unit')) {
            var ptr = e.target.closest('tr');
            var q = num(ptr.querySelector('.ros-pt-qty').value);
            var pu = num(ptr.querySelector('.ros-pt-unit').value);
            if (q || pu) { ptr.querySelector('.ros-pt-cost').value = money(q * pu); }
        }
    });

    // Seed Job Description rows from checked Services (only if not already present).
    function syncServiceRows() {
        var existing = {};
        Array.prototype.forEach.call(jobBody.querySelectorAll('input[type=text]'), function (inp) {
            var v = (inp.value || '').trim().toUpperCase();
            if (v) { existing[v] = true; }
        });
        Array.prototype.forEach.call(document.querySelectorAll('input[name="service_types[]"]:checked'), function (cb) {
            var lbl = cb.closest('.form-check') ? cb.closest('.form-check').querySelector('label') : null;
            var label = (lbl ? lbl.textContent : '').trim();
            if (label && !existing[label.toUpperCase()]) {
                window.rosAddJobRow(label);
                existing[label.toUpperCase()] = true;
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        syncServiceRows();
        Array.prototype.forEach.call(document.querySelectorAll('input[name="service_types[]"]'), function (cb) {
            cb.addEventListener('change', syncServiceRows);
        });
    });
})();
</script>
@endpush
