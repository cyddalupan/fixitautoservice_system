@extends('layouts.app')

@section('title', 'Create Appointment - Fix-It Auto Services')

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
        <button class="nav-pill" data-section="service" onclick="scrollToSection('service')">
            <i class="fas fa-wrench"></i> Service
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
    
    <form action="{{ route('appointments.store') }}" method="POST" autocomplete="off">
        @csrf
        <input type="hidden" name="customer_selection_mode" value="{{ $selectedCustomer ? 'from_url' : 'manual' }}">
        
        <!-- ===== SECTION: Details ===== -->
        <div class="form-section" id="details">
            <div class="form-section-header">
                <h6><i class="fas fa-info-circle"></i> Appointment Details</h6>
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
                    
                    <!-- Vehicle Description -->
                    <div class="col-md-6">
                        <div class="form-group" style="position:relative;">
                            <label for="vehicle_description" class="form-label field-required">Vehicle Description</label>
                            <input type="text" class="form-control @error('vehicle_description') is-invalid @enderror"
                                   id="vehicle_description" name="vehicle_description" autocomplete="off"
                                   value="{{ old('vehicle_description', $selectedVehicle ? $selectedVehicle->year.' '.$selectedVehicle->make.' '.$selectedVehicle->model.($selectedVehicle->license_plate ? ' - '.$selectedVehicle->license_plate : '') : '') }}"
                                   required placeholder="e.g. 2023 Toyota Vios">
                            <input type="hidden" name="vehicle_id" id="vehicle_id" value="{{ $selectedVehicle->id ?? '' }}">
                            <div id="vehicle-suggestions" class="vehicle-suggestions-dropdown" style="display:none;"></div>
                            @error('vehicle_description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    
                    <!-- Appointment Date -->
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="appointment_date" class="form-label field-required">Appointment Date</label>
                            <input type="date" class="form-control @error('appointment_date') is-invalid @enderror"
                                   id="appointment_date" name="appointment_date"
                                   value="{{ old('appointment_date', date('Y-m-d')) }}" required>
                            @error('appointment_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    
                    <!-- Appointment Time -->
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="appointment_time" class="form-label field-required">Appointment Time</label>
                            <input type="time" class="form-control @error('appointment_time') is-invalid @enderror"
                                   id="appointment_time" name="appointment_time"
                                   value="{{ old('appointment_time', '09:00') }}" required>
                            @error('appointment_time')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    
                    <!-- Priority -->
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="priority" class="form-label field-required">Priority</label>
                            <select class="form-select @error('priority') is-invalid @enderror"
                                    id="priority" name="priority" required>
                                <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>🔵 Low</option>
                                <option value="normal" {{ old('priority', 'normal') == 'normal' ? 'selected' : '' }}>🟢 Normal</option>
                                <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>🟠 High</option>
                                <option value="urgent" {{ old('priority') == 'urgent' ? 'selected' : '' }}>🔴 Urgent</option>
                            </select>
                            @error('priority')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- ===== SECTION: Service ===== -->
        <div class="form-section" id="service">
            <div class="form-section-header">
                <h6><i class="fas fa-wrench"></i> Service Information</h6>
                <div class="collapse-icon"><i class="fas fa-chevron-down"></i></div>
            </div>
            <div class="form-section-body">
                <div class="row g-3">
                    <!-- Service Type -->
                    <div class="col-md-6">
                        @include('partials.service-type-selector', [
                            'selected' => old('service_type', ''),
                            'name' => 'service_type',
                            'label' => 'SERVICE TYPE',
                            'required' => true,
                            'showIcons' => true,
                            'multiple' => true,
                            'placeholder' => 'Select Service Type',
                            'module' => 'appointments',
                        ])
                    </div>
                    
                    <!-- Estimated Cost -->
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="estimated_cost" class="form-label">Estimated Cost (₱)</label>
                            <div class="input-group">
                                <span class="input-group-text">₱</span>
                                <input type="number" step="0.01" min="0"
                                       class="form-control @error('estimated_cost') is-invalid @enderror"
                                       id="estimated_cost" name="estimated_cost"
                                       value="{{ old('estimated_cost') }}"
                                       placeholder="0.00">
                            </div>
                            @error('estimated_cost')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    
                    <!-- Quick Cost Templates -->
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="form-label">Quick Estimate</label>
                            <div class="d-flex flex-wrap gap-1 pt-1">
                                <span class="quick-note-btn" onclick="document.getElementById('estimated_cost').value='500'">₱500</span>
                                <span class="quick-note-btn" onclick="document.getElementById('estimated_cost').value='1500'">₱1,500</span>
                                <span class="quick-note-btn" onclick="document.getElementById('estimated_cost').value='3000'">₱3,000</span>
                                <span class="quick-note-btn" onclick="document.getElementById('estimated_cost').value='5000'">₱5,000</span>
                                <span class="quick-note-btn" onclick="document.getElementById('estimated_cost').value='10000'">₱10,000</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Description -->
                    <div class="col-12">
                        <div class="form-group">
                            <label for="description" class="form-label">Service Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror"
                                      id="description" name="description" rows="3"
                                      placeholder="Describe the service needed...">{{ old('description') }}</textarea>
                            
                            <!-- Quick note templates -->
                            <div class="d-flex flex-wrap gap-1 mt-2">
                                <span class="quick-note-btn" onclick="appendNote('description', 'Check engine light is on')"><i class="fas fa-lightbulb"></i> Check Engine</span>
                                <span class="quick-note-btn" onclick="appendNote('description', 'Unusual noise when braking')"><i class="fas fa-volume-up"></i> Brake Noise</span>
                                <span class="quick-note-btn" onclick="appendNote('description', 'Car pulls to the left')"><i class="fas fa-arrows-alt-h"></i> Alignment</span>
                                <span class="quick-note-btn" onclick="appendNote('description', 'Needs PMS - change oil and filters')"><i class="fas fa-oil-can"></i> PMS</span>
                                <span class="quick-note-btn" onclick="appendNote('description', 'Aircon not cooling properly')"><i class="fas fa-snowflake"></i> A/C Issue</span>
                            </div>
                            
                            @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
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
                    <!-- Single Technician (existing field) -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="assigned_to" class="form-label">Primary Technician</label>
                            <select class="form-select @error('assigned_to') is-invalid @enderror"
                                    id="assigned_to" name="assigned_to">
                                <option value="">-- Not Assigned --</option>
                                @foreach($technicians as $tech)
                                    <option value="{{ $tech->id }}" {{ old('assigned_to', $selectedCustomer->technician_id ?? '') == $tech->id ? 'selected' : '' }}>
                                        {{ $tech->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('assigned_to')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    
                    <!-- Multi-Technician Assignment -->
                    <div class="col-md-6">
                        @include('partials.technician-selector', [
                            'technicians' => $allTechnicians,
                            'selectedIds' => old('technicians', []),
                            'label' => 'Additional Technicians',
                            'helpText' => 'Assign additional technicians to this appointment',
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
                                      placeholder="Internal notes (not visible to customer)...">{{ old('notes') }}</textarea>
                            @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- ===== SECTION: History ===== -->
        <div id="historySection">
            @php
                $historyRecords = $customerHistory ?? collect([]);
                $processedHistory = $historyRecords->map(function($record) {
                    $record->historyTitle = $record->appointment_number ?? 'Appointment #'.$record->id;
                    $record->iconClass = match($record->appointment_status ?? '') {
                        'completed', 'checked_out' => 'fas fa-check-circle',
                        'cancelled', 'no_show' => 'fas fa-times-circle',
                        'scheduled', 'confirmed' => 'fas fa-calendar-check',
                        default => 'fas fa-calendar-alt',
                    };
                    $record->statusClass = match($record->appointment_status ?? '') {
                        'completed', 'checked_out' => 'completed',
                        'cancelled', 'no_show' => 'cancelled',
                        'scheduled', 'confirmed' => 'pending',
                        default => 'info',
                    };
                    $record->statusLabel = $record->appointment_status ? ucfirst(str_replace('_', ' ', $record->appointment_status)) : '';
                    return $record;
                });
            @endphp
            @include('partials.customer-history', [
                'customerHistory' => $processedHistory,
                'historyTitle' => 'Appointment History',
                'historyType' => 'appointment',
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
                <a href="{{ route('appointments.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-times me-1"></i> Cancel
                </a>
                <button type="submit" class="btn btn-primary btn-sm px-4">
                    <i class="fas fa-check me-1"></i> Create Appointment
                </button>
            </div>
        </div>
    </form>
</div>

<script>
// Vehicle autocomplete data - all customer vehicles
var customerVehiclesData = {!! json_encode($customerVehicles->map(function($v) {
    return [
        'id' => $v->id,
        'label' => $v->year.' '.$v->make.' '.$v->model.($v->license_plate ? ' ['.$v->license_plate.']' : ''),
        'year' => $v->year,
        'make' => $v->make,
        'model' => $v->model,
        'license_plate' => $v->license_plate,
    ];
})) !!};

function appendNote(fieldId, text) {
    var $field = $('#' + fieldId);
    var current = $field.val() || '';
    $field.val(current + (current ? '\n' : '') + text);
    $field.focus();
    $field.trigger('input');
}

function fillVehicleDescription(vehicle) {
    var desc = vehicle.year + ' ' + vehicle.make + ' ' + vehicle.model;
    if (vehicle.license_plate) {
        desc += ' - ' + vehicle.license_plate;
    }
    $('#vehicle_description').val(desc);
    $('#vehicle_id').val(vehicle.id);
    $('#vehicle-suggestions').hide();
    checkVehicleTransactions(vehicle.id);
}

function checkVehicleTransactions(vehicleId) {
    if (!vehicleId) return;
    // Remove any existing error first
    $('.vehicle-transaction-error').remove();
    
    $.get('/api/vehicle-transactions/' + vehicleId, function(data) {
        if (data.has_active_transaction) {
            var tx = data.transactions[0];
            var errMsg = '<div class="vehicle-transaction-error alert alert-danger alert-dismissible fade show mt-2">' +
                '<i class="fas fa-exclamation-triangle"></i> ' +
                'This vehicle already has an active ' + tx.type + ' (' + tx.number + '). ' +
                '<a href="' + tx.url + '" class="alert-link" target="_blank">View ' + tx.type + '</a>' +
                '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
                '</div>';
            $('#vehicle_description').closest('.form-group').after(errMsg);
        }
    });
}

$(document).ready(function() {
    initTechnicianMultiSelect(".technician-select-wrapper:not([data-tech-init])");
    
    // ===== PREVENT DUPLICATE TECHNICIAN =====
    // When primary technician is selected, hide them from the
    // "Additional Technicians" multi-select dropdown
    var $techWrapper = $('.technician-select-wrapper');
    var $techOptions = $techWrapper.find('.tech-option');
    
    function syncPrimaryTechExclusion() {
        var primaryId = String($('#assigned_to').val() || '');
        
        // Remove primary tech from additional list if present
        $techWrapper.find('.technician-tag[data-id="' + primaryId + '"] .remove-tech-btn').each(function() {
            $(this).trigger('click');
        });
        
        // Store & immediately apply exclusion
        $techWrapper.data('exclude-primary', primaryId);
        applyExclusions();
    }
    
    function applyExclusions() {
        var excludePrimary = $techWrapper.data('exclude-primary') || '';
        var q = $techWrapper.find('.search-input').val().toLowerCase().trim();
        
        $techOptions.each(function() {
            var techId = String($(this).data('id'));
            var isSelected = $(this).hasClass('selected');
            
            if (isSelected) { $(this).hide(); return; }
            if (techId === excludePrimary) { $(this).hide(); return; }
            
            var name = $(this).data('name').toLowerCase();
            $(this).toggle(!q || name.indexOf(q) !== -1);
        });
    }
    
    // Replace the search handler with our extended version
    $techWrapper.off('input', '.search-input')
        .on('input.search-tech', '.search-input', applyExclusions);
    
    // Re-apply exclusions whenever the dropdown opens (rebuild may have reset visibility)
    $techWrapper.find('.tech-add-btn').on('click', function() {
        setTimeout(applyExclusions, 50);
    });
    
    // Run on load and when primary tech changes
    $('#assigned_to').on('change', syncPrimaryTechExclusion);
    syncPrimaryTechExclusion();
    
    // ===== VEHICLE AUTOSUGGEST =====
    var $vehInput = $('#vehicle_description');
    var $suggestions = $('#vehicle-suggestions');
    
    if (customerVehiclesData.length > 0) {
        // Show suggestions as user types
        $vehInput.on('input focus', function() {
            var val = $(this).val().toLowerCase().trim();
            
            if (!val) {
                // Show all vehicles if input is empty (focus only)
                if (document.activeElement === this && customerVehiclesData.length <= 10) {
                    renderSuggestions(customerVehiclesData);
                } else {
                    $suggestions.hide();
                }
                return;
            }
            
            var matches = customerVehiclesData.filter(function(v) {
                return v.label.toLowerCase().indexOf(val) > -1
                    || v.make.toLowerCase().indexOf(val) > -1
                    || v.model.toLowerCase().indexOf(val) > -1
                    || (v.license_plate && v.license_plate.toLowerCase().indexOf(val) > -1);
            });
            
            if (matches.length > 0) {
                renderSuggestions(matches);
            } else {
                // Clear vehicle_id if typed text doesn't match any vehicle
                if ($('#vehicle_id').val()) {
                    $('#vehicle_id').val('');
                }
                $suggestions.hide();
            }
        });
        
        function renderSuggestions(vehicles) {
            $suggestions.empty();
            vehicles.forEach(function(v) {
                var $item = $('<div class="vehicle-suggestion-item"></div>');
                $item.html(
                    '<div class="suggestion-main">' + v.year + ' ' + v.make + ' ' + v.model + '</div>'
                    + (v.license_plate ? '<div class="suggestion-sub">' + v.license_plate + '</div>' : '')
                );
                $item.on('click', function() {
                    fillVehicleDescription(v);
                });
                $suggestions.append($item);
            });
            $suggestions.show();
        }
        
        // Hide on blur (with delay for click to register)
        $vehInput.on('blur', function() {
            setTimeout(function() { $suggestions.hide(); }, 200);
        });
        
        // Auto-fill if a vehicle_id is already set (from URL param)
        var initialVehicleId = parseInt($('#vehicle_id').val());
        if (initialVehicleId) {
            var matched = customerVehiclesData.find(function(v) { return v.id === initialVehicleId; });
            if (matched) {
                fillVehicleDescription(matched);
            }
        }
        
        // ===== QUOTATION AUTO-FILL =====
        @if($quotationData && $quotationData->service_description)
        // Auto-fill service description from latest quotation
        var qDesc = $('#description').val();
        if (!qDesc || qDesc.trim() === '') {
            $('#description').val('{{ addslashes($quotationData->service_description) }}');
        }
        @endif
    }
    
    // ===== VEHICLE PILL HANDLER =====
    // The customer-summary-card onclick tries $('#vehicle_id').trigger('change')
    // Intercept the change event to fill description
    $(document).on('change', '#vehicle_id', function() {
        var vehId = parseInt($(this).val());
        if (!vehId) return;
        var matched = customerVehiclesData.find(function(v) { return v.id === vehId; });
        if (matched) {
            var desc = matched.year + ' ' + matched.make + ' ' + matched.model;
            if (matched.license_plate) {
                desc += ' - ' + matched.license_plate;
            }
            $('#vehicle_description').val(desc);
        }
        checkVehicleTransactions(vehId);
    });
    
    // Also handle manual typing clearing the vehicle_id link
    $vehInput.on('change', function() {
        // If user typed something completely different, clear hidden vehicle_id
        var val = $(this).val().toLowerCase().trim();
        var currentVehId = parseInt($('#vehicle_id').val());
        if (currentVehId && customerVehiclesData.length > 0) {
            var matched = customerVehiclesData.find(function(v) { return v.id === currentVehId; });
            if (matched && val !== matched.label.toLowerCase().trim()) {
                // Don't clear immediately - let them choose from suggestions
            }
        }
    });

    // ===== QUOTATION AUTO-FILL ON CUSTOMER CHANGE =====
    $(document).on('change', '#customer_id', function() {
        var customerId = parseInt($(this).val());
        if (!customerId) return;
        
        $.get('{{ route("api.customer-latest-quotation", ["customer" => "__CUSTOMER_ID__"]) }}'.replace('__CUSTOMER_ID__', customerId), function(data) {
            if (data && data.service_description) {
                var descField = $('#description');
                if (!descField.val() || descField.val().trim() === '') {
                    descField.val(data.service_description);
                }
            }
        });
    });
});
</script>

<style>
.vehicle-suggestions-dropdown {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    z-index: 999;
    background: #fff;
    border: 1px solid #dee2e6;
    border-radius: 0 0 6px 6px;
    max-height: 200px;
    overflow-y: auto;
    box-shadow: 0 4px 12px rgba(0,0,0,0.12);
}
.vehicle-suggestion-item {
    padding: 8px 12px;
    cursor: pointer;
    border-bottom: 1px solid #f0f0f0;
    transition: background 0.15s;
}
.vehicle-suggestion-item:last-child {
    border-bottom: none;
}
.vehicle-suggestion-item:hover {
    background: #e8f4fd;
}
.suggestion-main {
    font-size: 0.85rem;
    font-weight: 500;
    color: #1a1a2e;
}
.suggestion-sub {
    font-size: 0.75rem;
    color: #6c757d;
    margin-top: 1px;
}
</style>
@endsection
