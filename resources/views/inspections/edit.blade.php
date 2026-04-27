@extends('layouts.app')

@section('content')
@include('partials.customer-process-assets')
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1" style="color: #1a237e;">
                <i class="fas fa-search me-2"></i>Edit Inspection #{{ $inspection->id }}
            </h4>
            <p class="text-muted mb-0">Update vehicle inspection details and findings</p>
        </div>
        <div>
            <a href="{{ route('inspections.show', $inspection) }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>Back to Inspection
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
                    <i class="fas fa-clipboard-check me-1"></i>Findings &amp; Notes
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
                <div class="form-section">
                    <div class="form-section-header" onclick="toggleSection(this)">
                        <h6><i class="fas fa-info-circle"></i>Inspection Details</h6>
                        <i class="fas fa-chevron-down text-muted"></i>
                    </div>
                    <div class="form-section-body">
                        <div class="row g-3">
                            <input type="hidden" name="inspection_name" value="Inspection #{{ $inspection->id }}">
                            <div class="col-md-4">
                                <label for="inspection_type" class="form-label fw-medium">
                                    <i class="fas fa-tag me-1 text-primary"></i>Inspection Type
                                </label>
                                <select class="form-select @error('inspection_type') is-invalid @enderror" id="inspection_type" name="inspection_type">
                                    <option value="">Select type...</option>
                                    <option value="pre_purchase" {{ old('inspection_type', $inspection->inspection_type) == 'pre_purchase' ? 'selected' : '' }}>Pre-Purchase</option>
                                    <option value="routine_maintenance" {{ old('inspection_type', $inspection->inspection_type) == 'routine_maintenance' ? 'selected' : '' }}>Routine Maintenance</option>
                                    <option value="safety" {{ old('inspection_type', $inspection->inspection_type) == 'safety' ? 'selected' : '' }}>Safety</option>
                                    <option value="comprehensive" {{ old('inspection_type', $inspection->inspection_type) == 'comprehensive' ? 'selected' : '' }}>Comprehensive</option>
                                    <option value="diagnostic" {{ old('inspection_type', $inspection->inspection_type) == 'diagnostic' ? 'selected' : '' }}>Diagnostic</option>
                                    <option value="emissions" {{ old('inspection_type', $inspection->inspection_type) == 'emissions' ? 'selected' : '' }}>Emissions</option>
                                    <option value="custom" {{ old('inspection_type', $inspection->inspection_type) == 'custom' ? 'selected' : '' }}>Custom</option>
                                </select>
                                @error('inspection_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label for="inspection_status" class="form-label fw-medium">
                                    <i class="fas fa-flag me-1 text-primary"></i>Status
                                </label>
                                <select class="form-select @error('inspection_status') is-invalid @enderror" id="inspection_status" name="inspection_status">
                                    <option value="draft" {{ old('inspection_status', $inspection->inspection_status) == 'draft' ? 'selected' : '' }}>Draft</option>
                                    <option value="in_progress" {{ old('inspection_status', $inspection->inspection_status) == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                    <option value="completed" {{ old('inspection_status', $inspection->inspection_status) == 'completed' ? 'selected' : '' }}>Completed</option>
                                    <option value="approved" {{ old('inspection_status', $inspection->inspection_status) == 'approved' ? 'selected' : '' }}>Approved</option>
                                    <option value="rejected" {{ old('inspection_status', $inspection->inspection_status) == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                    <option value="cancelled" {{ old('inspection_status', $inspection->inspection_status) == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                </select>
                                @error('inspection_status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label for="vehicle_mileage" class="form-label fw-medium">
                                    <i class="fas fa-tachometer-alt me-1 text-primary"></i>Mileage
                                </label>
                                <input type="number" class="form-control @error('vehicle_mileage') is-invalid @enderror"
                                       id="vehicle_mileage" name="vehicle_mileage" min="0"
                                       value="{{ old('vehicle_mileage', $inspection->vehicle_mileage) }}" placeholder="Current mileage">
                                @error('vehicle_mileage')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Vehicle Info -->
                <div class="form-section">
                    <div class="form-section-header" onclick="toggleSection(this)">
                        <h6><i class="fas fa-car"></i>Vehicle &amp; Customer</h6>
                        <i class="fas fa-chevron-down text-muted"></i>
                    </div>
                    <div class="form-section-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="customer_id" class="form-label fw-medium">
                                    <i class="fas fa-user me-1 text-primary"></i>Customer
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
                            <div class="col-md-6">
                                <label for="vehicle_id" class="form-label fw-medium">
                                    <i class="fas fa-car me-1 text-primary"></i>Vehicle
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
                    </div>
                </div>
            </div>

            <!-- === FINDINGS & NOTES === -->
            <div class="tab-pane fade" id="findings" role="tabpanel">
                <div class="form-section">
                    <div class="form-section-header" onclick="toggleSection(this)">
                        <h6><i class="fas fa-clipboard-check"></i>Customer Concerns</h6>
                        <i class="fas fa-chevron-down text-muted"></i>
                    </div>
                    <div class="form-section-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="customer_concerns" class="form-label fw-medium">
                                    <i class="fas fa-question-circle me-1 text-primary"></i>Customer Reported Concerns
                                </label>
                                <textarea class="form-control @error('customer_concerns') is-invalid @enderror"
                                          id="customer_concerns" name="customer_concerns" rows="3"
                                          placeholder="What issues did the customer report?">{{ old('customer_concerns', $inspection->customer_concerns) }}</textarea>
                                @error('customer_concerns')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </div>

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

                <div class="form-section">
                    <div class="form-section-header" onclick="toggleSection(this)">
                        <h6><i class="fas fa-sticky-note"></i>Inspection Notes</h6>
                        <i class="fas fa-chevron-down text-muted"></i>
                    </div>
                    <div class="form-section-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="inspection_notes" class="form-label fw-medium">
                                    <i class="fas fa-pen me-1 text-primary"></i>Additional Notes
                                </label>
                                <textarea class="form-control @error('inspection_notes') is-invalid @enderror"
                                          id="inspection_notes" name="inspection_notes" rows="4"
                                          placeholder="Any additional notes or observations...">{{ old('inspection_notes', $inspection->inspection_notes) }}</textarea>
                                @error('inspection_notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
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
                                <div class="form-group">
                                    <label class="form-label fw-medium">
                                        <i class="fas fa-users me-1 text-primary"></i>Additional Technicians
                                    </label>
                                    <div class="technician-select-wrapper">
                                        <div class="technician-tags"></div>
                                        <button type="button" class="btn btn-outline-primary btn-sm tech-select-trigger" style="font-size: 0.82rem;">
                                            <i class="fas fa-plus me-1"></i> Add Technician
                                        </button>
                                        
                                        <div class="technician-dropdown">
                                            <input type="text" class="search-input" placeholder="Search technicians...">
                                            @foreach($allTechnicians as $tech)
                                            @php
                                                $isSelected = $inspection->technicians->contains($tech->id);
                                            @endphp
                                            <div class="tech-option {{ $isSelected ? 'selected' : '' }}" data-id="{{ $tech->id }}" data-name="{{ $tech->name }}" data-role="Technician">
                                                <span class="tech-check {{ $isSelected ? 'checked' : '' }}"></span>
                                                <span>{{ $tech->name }}</span>
                                            </div>
                                            @endforeach
                                        </div>
                                        @php
                                            $existingTechIds = $inspection->technicians->pluck('id')->toArray();
                                        @endphp
                                        @forelse($existingTechIds as $techId)
                                        <input type="hidden" name="technicians[]" value="{{ $techId }}">
                                        @empty
                                        <input type="hidden" name="technicians[]" value="">
                                        @endforelse
                                    </div>
                                    <small class="text-muted">Assign additional technicians to perform this inspection</small>
                                </div>
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
                                <label for="work_order_id" class="form-label fw-medium">
                                    <i class="fas fa-clipboard-list me-1 text-primary"></i>Work Order
                                </label>
                                <select class="form-select @error('work_order_id') is-invalid @enderror" id="work_order_id" name="work_order_id">
                                    <option value="">None</option>
                                    @foreach($workOrders as $wo)
                                        <option value="{{ $wo->id }}" {{ old('work_order_id', $inspection->work_order_id) == $wo->id ? 'selected' : '' }}>
                                            #{{ $wo->id }} - {{ $wo->customer->full_name ?? $wo->customer->first_name ?? 'N/A' }} ({{ $wo->vehicle->make ?? '' }} {{ $wo->vehicle->model ?? '' }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('work_order_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
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
        <div class="sticky-save">
            <div class="container-fluid px-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <i class="fas fa-save me-2 text-white opacity-75"></i>
                        <span class="text-white opacity-75">Inspection #{{ $inspection->id }} changes</span>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('inspections.show', $inspection) }}" class="btn btn-outline-light btn-sm">Cancel</a>
                        <button type="submit" class="btn btn-light text-primary fw-semibold btn-sm">
                            <i class="fas fa-check me-1"></i>Save Changes
                        </button>
                    </div>
                </div>
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
    initTechnicianMultiSelect();
});
</script>
@endpush
