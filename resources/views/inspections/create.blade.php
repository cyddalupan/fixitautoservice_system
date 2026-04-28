@extends('layouts.app')

@section('title', 'Create Vehicle Inspection - Fix-It Auto Services')

@section('content')
<div class="container-fluid py-3">
    @include('partials.customer-process-assets')
    @include('partials.customer-summary-card')
    
    <div class="auto-save-toast" style="display:none;"><i class="fas fa-check-circle"></i> <span></span></div>
    
    <!-- Section Navigation -->
    <div class="section-nav" id="sectionNav">
        <button class="nav-pill active" data-section="details" onclick="scrollToSection('details')">
            <i class="fas fa-info-circle"></i> Details
        </button>
        <button class="nav-pill" data-section="findings" onclick="scrollToSection('findings')">
            <i class="fas fa-search"></i> Findings
        </button>
        <button class="nav-pill" data-section="team" onclick="scrollToSection('team')">
            <i class="fas fa-users"></i> Team
        </button>
        <button class="nav-pill" data-section="notes" onclick="scrollToSection('notes')">
            <i class="fas fa-sticky-note"></i> Notes
        </button>
        <button class="nav-pill" data-section="history" onclick="scrollToSection('historySection')">
            <i class="fas fa-history"></i> History
        </button>
    </div>
    
    <form action="{{ route('inspections.store') }}" method="POST" autocomplete="off">
        @csrf
        <input type="hidden" name="customer_selection_mode" value="{{ $selectedCustomer ? 'from_url' : 'manual' }}">
        
        <!-- ===== SECTION: Details ===== -->
        <div class="form-section" id="details">
            <div class="form-section-header">
                <h6><i class="fas fa-info-circle"></i> Inspection Details</h6>
                <div class="collapse-icon"><i class="fas fa-chevron-down"></i></div>
            </div>
            <div class="form-section-body">
                <div class="row g-3">
                    <!-- Customer -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="customer_id" class="form-label field-required">Customer</label>
                            @if($selectedCustomer)
                                <input type="hidden" name="customer_id" value="{{ $selectedCustomer->id }}">
                                <div class="form-control-plaintext fw-bold" style="padding: 6px 0;">
                                    <i class="fas fa-user text-primary me-1"></i>
                                    {{ $selectedCustomer->name }}
                                    <small class="text-muted ms-2">({{ $selectedCustomer->phone ?? '' }})</small>
                                </div>
                            @else
                                <select class="form-select @error('customer_id') is-invalid @enderror"
                                        id="customer_id" name="customer_id" required>
                                    <option value="">Select Customer</option>
                                    @foreach($customers as $c)
                                        <option value="{{ $c->id }}" 
                                            {{ old('customer_id', $selectedCustomer->id ?? '') == $c->id ? 'selected' : '' }}>
                                            {{ $c->name }} {{ $c->phone ? '- '.$c->phone : '' }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('customer_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            @endif
                        </div>
                    </div>
                    
                    <!-- Vehicle -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="vehicle_id" class="form-label field-required">Vehicle</label>
                            <select class="form-select @error('vehicle_id') is-invalid @enderror"
                                    id="vehicle_id" name="vehicle_id" required>
                                <option value="">Select Vehicle</option>
                                @foreach($vehicles as $v)
                                    <option value="{{ $v->id }}" 
                                        data-plate="{{ $v->license_plate ?? '' }}"
                                        data-customer="{{ $v->customer_id }}"
                                        {{ old('vehicle_id', $selectedVehicle->id ?? '') == $v->id ? 'selected' : '' }}>
                                        {{ $v->year }} {{ $v->make }} {{ $v->model }}
                                        @if($v->license_plate) [{{ $v->license_plate }}] @endif
                                        @if($v->customer) - {{ $v->customer->name }} @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('vehicle_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <small class="text-muted">
                            <i class="fas fa-lightbulb"></i> Tip: Select customer first to filter available vehicles
                        </small>
                    </div>
                    
                    <!-- Inspection Type (Multi-select) -->
                    <div class="col-12">
                        <div class="form-group">
                            <label class="form-label field-required">Inspection Type</label>
                            <div class="inspection-type-grid">
                                <label class="type-card {{ collect(old('inspection_type', []))->contains('pre_purchase') ? 'selected' : '' }}">
                                    <input type="checkbox" name="inspection_type[]" value="pre_purchase"
                                        {{ collect(old('inspection_type', []))->contains('pre_purchase') ? 'checked' : '' }}>
                                    <i class="fas fa-search-dollar"></i>
                                    <span>Pre-Purchase</span>
                                </label>
                                <label class="type-card {{ collect(old('inspection_type', []))->contains('safety') ? 'selected' : '' }}">
                                    <input type="checkbox" name="inspection_type[]" value="safety"
                                        {{ collect(old('inspection_type', []))->contains('safety') ? 'checked' : '' }}>
                                    <i class="fas fa-shield-alt"></i>
                                    <span>Safety</span>
                                </label>
                                <label class="type-card {{ collect(old('inspection_type', []))->contains('emissions') ? 'selected' : '' }}">
                                    <input type="checkbox" name="inspection_type[]" value="emissions"
                                        {{ collect(old('inspection_type', []))->contains('emissions') ? 'checked' : '' }}>
                                    <i class="fas fa-smog"></i>
                                    <span>Emissions</span>
                                </label>
                                <label class="type-card {{ collect(old('inspection_type', []))->contains('routine') ? 'selected' : '' }}">
                                    <input type="checkbox" name="inspection_type[]" value="routine"
                                        {{ collect(old('inspection_type', []))->contains('routine') ? 'checked' : '' }}>
                                    <i class="fas fa-clipboard-check"></i>
                                    <span>Routine</span>
                                </label>
                                <label class="type-card {{ collect(old('inspection_type', []))->contains('diagnostic') ? 'selected' : '' }}">
                                    <input type="checkbox" name="inspection_type[]" value="diagnostic"
                                        {{ collect(old('inspection_type', []))->contains('diagnostic') ? 'checked' : '' }}>
                                    <i class="fas fa-stethoscope"></i>
                                    <span>Diagnostic</span>
                                </label>
                                <label class="type-card {{ collect(old('inspection_type', []))->contains('post_repair') ? 'selected' : '' }}">
                                    <input type="checkbox" name="inspection_type[]" value="post_repair"
                                        {{ collect(old('inspection_type', []))->contains('post_repair') ? 'checked' : '' }}>
                                    <i class="fas fa-tools"></i>
                                    <span>Post-Repair</span>
                                </label>
                                <label class="type-card {{ collect(old('inspection_type', []))->contains('comprehensive') ? 'selected' : '' }}">
                                    <input type="checkbox" name="inspection_type[]" value="comprehensive"
                                        {{ collect(old('inspection_type', []))->contains('comprehensive') ? 'checked' : '' }}>
                                    <i class="fas fa-list-alt"></i>
                                    <span>Comprehensive</span>
                                </label>
                                <label class="type-card {{ collect(old('inspection_type', []))->contains('custom') ? 'selected' : '' }}">
                                    <input type="checkbox" name="inspection_type[]" value="custom"
                                        {{ collect(old('inspection_type', []))->contains('custom') ? 'checked' : '' }}>
                                    <i class="fas fa-star"></i>
                                    <span>Custom</span>
                                </label>
                            </div>
                            @error('inspection_type')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    
                    <!-- Inspection Name -->
                    <div class="col-md-8">
                        <div class="form-group">
                            <label for="inspection_name" class="form-label">Inspection Name</label>
                            <input type="text" class="form-control @error('inspection_name') is-invalid @enderror"
                                   id="inspection_name" name="inspection_name"
                                   value="{{ old('inspection_name') }}"
                                   placeholder="Auto-generated if empty">
                            @error('inspection_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    
                    <!-- Template Select -->
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="template_id" class="form-label">Inspection Template</label>
                            <select class="form-select @error('template_id') is-invalid @enderror"
                                    id="template_id" name="template_id">
                                <option value="">Standard Checklist</option>
                                @foreach($templates ?? [] as $tpl)
                                    <option value="{{ $tpl->id }}" {{ old('template_id') == $tpl->id ? 'selected' : '' }}>
                                        {{ $tpl->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('template_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    
                    <!-- Vehicle Mileage -->
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="vehicle_mileage" class="form-label">Vehicle Mileage (km)</label>
                            <input type="number" min="0" step="1"
                                   class="form-control @error('vehicle_mileage') is-invalid @enderror"
                                   id="vehicle_mileage" name="vehicle_mileage"
                                   value="{{ old('vehicle_mileage', $selectedVehicle->odometer ?? '') }}"
                                   placeholder="Current odometer reading">
                            @error('vehicle_mileage')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    
                    <!-- Service Type -->
                    <div class="col-12">
                        @include('partials.service-type-selector', [
                            'selected' => old('service_type', $inspection->service_type ?? ''),
                            'name' => 'service_type',
                            'label' => 'SERVICE TYPE',
                            'required' => true,
                            'showIcons' => true,
                            'multiple' => true,
                            'placeholder' => 'Select Service Type',
                            'module' => 'inspections',
                        ])
                    </div>
                </div>
            </div>
        </div>
        
        <!-- ===== SECTION: Findings ===== -->
        <div class="form-section" id="findings">
            <div class="form-section-header">
                <h6><i class="fas fa-search"></i> Inspection Findings</h6>
                <div class="collapse-icon"><i class="fas fa-chevron-down"></i></div>
            </div>
            <div class="form-section-body">
                <div class="row g-3">
                    <!-- Customer Concerns -->
                    <div class="col-12">
                        <div class="form-group">
                            <label for="customer_concerns" class="form-label">Customer Concerns</label>
                            <textarea class="form-control @error('customer_concerns') is-invalid @enderror"
                                      id="customer_concerns" name="customer_concerns" rows="3"
                                      placeholder="What did the customer report? ...">{{ old('customer_concerns', $selectedAppointment->service_request ?? $selectedWorkOrder->customer_concerns ?? ($quotationData->service_description ?? '')) }}</textarea>
                            @error('customer_concerns')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    
                    <!-- Inspection Notes -->
                    <div class="col-12">
                        <div class="form-group">
                            <label for="inspection_notes" class="form-label">Inspection Notes</label>
                            <textarea class="form-control @error('inspection_notes') is-invalid @enderror"
                                      id="inspection_notes" name="inspection_notes" rows="5"
                                      placeholder="Enter inspection findings, observations, and recommendations...">{{ old('inspection_notes') }}</textarea>
                            
                            <div class="d-flex flex-wrap gap-1 mt-2">
                                <span class="quick-note-btn" onclick="appendNote('inspection_notes', '⚠️ SAFETY: Brake pads below minimum thickness - recommend immediate replacement')"><i class="fas fa-exclamation-triangle"></i> Safety Issue</span>
                                <span class="quick-note-btn" onclick="appendNote('inspection_notes', '🟡 RECOMMEND: Tires worn unevenly - alignment check recommended')"><i class="fas fa-info-circle"></i> Recommendation</span>
                                <span class="quick-note-btn" onclick="appendNote('inspection_notes', '✅ PASS: All lights, signals, and wipers functioning properly')"><i class="fas fa-check"></i> Pass</span>
                                <span class="quick-note-btn" onclick="appendNote('inspection_notes', '🔴 CRITICAL: Transmission fluid leaking from pan gasket')"><i class="fas fa-times-circle"></i> Critical</span>
                                <span class="quick-note-btn" onclick="appendNote('inspection_notes', '📝 NOTE: Customer declined additional diagnostic at this time')"><i class="fas fa-pen"></i> Note</span>
                            </div>
                            
                            @error('inspection_notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    
                    <!-- Requires Customer Approval -->
                    <div class="col-12">
                        <div class="form-group">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input"
                                       id="requires_customer_approval" name="requires_customer_approval" value="1"
                                       {{ old('requires_customer_approval') ? 'checked' : '' }}>
                                <label class="form-check-label" for="requires_customer_approval">
                                    <i class="fas fa-file-signature text-primary me-1"></i>
                                    Requires Customer Approval
                                </label>
                                <small class="d-block text-muted" style="margin-left: 1.5rem;">
                                    Check this if the findings require customer authorization before proceeding
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- ===== SECTION: Team ===== -->
        <div class="form-section" id="team">
            <div class="form-section-header">
                <h6><i class="fas fa-users"></i> Team Assignment</h6>
                <div class="collapse-icon"><i class="fas fa-chevron-down"></i></div>
            </div>
            <div class="form-section-body">
                <div class="row g-3">
                    <!-- Primary Technician (existing) -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="technician_id" class="form-label field-required">Lead Technician</label>
                            <select class="form-select @error('technician_id') is-invalid @enderror"
                                    id="technician_id" name="technician_id" required>
                                <option value="">Select Technician</option>
                                @foreach($inspectors as $inspector)
                                    <option value="{{ $inspector->id }}" {{ old('technician_id', $selectedAppointment->assigned_technician_id ?? '') == $inspector->id ? 'selected' : '' }}>
                                        {{ $inspector->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('technician_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    
                    <!-- Service Advisor -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="service_advisor_id" class="form-label">Service Advisor</label>
                            <select class="form-select @error('service_advisor_id') is-invalid @enderror"
                                    id="service_advisor_id" name="service_advisor_id">
                                <option value="">-- Not Assigned --</option>
                                @foreach($advisors as $adv)
                                    <option value="{{ $adv->id }}" {{ old('service_advisor_id', $selectedAppointment->service_advisor_id ?? '') == $adv->id ? 'selected' : '' }}>
                                        {{ $adv->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('service_advisor_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    
                    <!-- Multi-Technician -->
                    <div class="col-12">
                        @include('partials.technician-selector', [
                            'technicians' => $allTechnicians,
                            'selectedIds' => old('technicians', []),
                            'label' => 'Additional Technicians',
                            'helpText' => 'Assign additional technicians to perform this inspection',
                        ])
                    </div>
                </div>
            </div>
        </div>
        
        <!-- ===== SECTION: Notes ===== -->
        <div class="form-section" id="notes">
            <div class="form-section-header">
                <h6><i class="fas fa-sticky-note"></i> Additional Notes</h6>
                <div class="collapse-icon"><i class="fas fa-chevron-down"></i></div>
            </div>
            <div class="form-section-body">
                <div class="row g-3">
                    <div class="col-12">
                        <div class="form-group">
                            <label for="notes" class="form-label">Admin Notes</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror"
                                      id="notes" name="notes" rows="3"
                                      placeholder="Internal notes...">{{ old('notes') }}</textarea>
                            @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- ===== SECTION: History ===== -->
        <div id="historySection">
            @php
                $inspectionHistory = $customerHistory ?? collect([]);
                $processedInspections = $inspectionHistory->map(function($record) {
                    $record->historyTitle = $record->inspection_name ?? 'Inspection #'.$record->id;
                    $record->iconClass = match($record->inspection_status ?? '') {
                        'completed' => 'fas fa-check-circle',
                        'in_progress' => 'fas fa-spinner',
                        'approved' => 'fas fa-thumbs-up',
                        'cancelled' => 'fas fa-times-circle',
                        'draft' => 'fas fa-pen',
                        default => 'fas fa-clipboard-check',
                    };
                    $record->statusClass = match($record->inspection_status ?? '') {
                        'completed', 'approved' => 'completed',
                        'cancelled' => 'cancelled',
                        'draft', 'in_progress' => 'pending',
                        default => 'info',
                    };
                    $record->statusLabel = $record->inspection_status ? ucfirst(str_replace('_', ' ', $record->inspection_status)) : '';
                    return $record;
                });
            @endphp
            @include('partials.customer-history', [
                'customerHistory' => $processedInspections,
                'historyTitle' => 'Inspection History',
                'historyType' => 'inspection',
                'historyRoute' => '#',
            ])
        </div>
        
        <!-- Sticky Save Bar -->
        <div style="height: 70px;"></div>
        <div class="sticky-save-bar visible">
            <div class="save-info">
                <i class="fas fa-save text-primary"></i>
                <span>All changes are saved as draft automatically</span>
                <span class="auto-save-indicator">• Auto-save active</span>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('inspections.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-times me-1"></i> Cancel
                </a>
                <button type="submit" class="btn btn-primary btn-sm px-4">
                    <i class="fas fa-check me-1"></i> Create Inspection
                </button>
            </div>
        </div>
    </form>
</div>

<script>
function appendNote(fieldId, text) {
    var $field = $('#' + fieldId);
    var current = $field.val() || '';
    $field.val(current + (current ? '\n' : '') + text);
    $field.focus();
    $field.trigger('input');
}

$(document).ready(function() {
    initTechnicianMultiSelect(".technician-select-wrapper:not([data-tech-init])");

    // ===== QUOTATION AUTO-FILL ON CUSTOMER CHANGE =====
    $(document).on('change', '#customer_id', function() {
        var customerId = parseInt($(this).val());
        if (!customerId) return;
        
        $.get('{{ route("api.customer-latest-quotation", "") }}/' + customerId, function(data) {
            if (data && data.service_description) {
                var concernField = $('#customer_concerns');
                if (!concernField.val() || concernField.val().trim() === '') {
                    concernField.val(data.service_description);
                }
            }
        });
    });
});
</script>
@endsection
