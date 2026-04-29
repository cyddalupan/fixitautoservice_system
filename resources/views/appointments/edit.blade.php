@extends('layouts.app')

@section('title', 'Reschedule Appointment - Fix-It Auto Services')

@section('content')
@include('partials.customer-process-assets')

<div class="container-fluid py-3">
    <!-- Page Header -->
    <div class="status-header-bar d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center gap-3">
            <h4 class="mb-0 fw-bold">
                <i class="fas fa-calendar-edit me-2" style="color: var(--primary-color);"></i>Reschedule Appointment
            </h4>
            <span class="badge bg-info">{{ $appointment->appointment_number }}</span>
        </div>
        <div>
            <a href="{{ route('appointments.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Back to Appointments
            </a>
        </div>
    </div>

    <form method="POST" action="{{ route('appointments.update', $appointment) }}" id="appointmentForm">
        @csrf
        @method('PUT')

        <div class="row g-4">
            <!-- Left Column -->
            <div class="col-lg-8">
                <!-- Section: Schedule -->
                <div class="card modern-card mb-4">
                    <div class="card-header modern-card-header">
                        <h5 class="mb-0"><i class="fas fa-clock me-2"></i>Schedule</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="appointment_date" class="form-label">Appointment Date <span class="text-danger">*</span></label>
                                    <input type="date" 
                                           class="form-control @error('appointment_date') is-invalid @enderror" 
                                           id="appointment_date" 
                                           name="appointment_date" 
                                           value="{{ old('appointment_date', $appointment->appointment_date->format('Y-m-d')) }}" 
                                           required
                                           min="{{ date('Y-m-d') }}">
                                    @error('appointment_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="appointment_time" class="form-label">Appointment Time <span class="text-danger">*</span></label>
                                    <input type="time" 
                                           class="form-control @error('appointment_time') is-invalid @enderror" 
                                           id="appointment_time" 
                                           name="appointment_time" 
                                           value="{{ old('appointment_time', date('H:i', strtotime($appointment->appointment_time))) }}" 
                                           required>
                                    @error('appointment_time')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="estimated_duration" class="form-label">Estimated Duration (hours)</label>
                                    <input type="number" step="0.5" min="0.5" max="24"
                                           class="form-control @error('estimated_duration') is-invalid @enderror" 
                                           id="estimated_duration" 
                                           name="estimated_duration" 
                                           value="{{ old('estimated_duration', $appointment->estimated_duration) }}"
                                           placeholder="e.g. 1.5">
                                    @error('estimated_duration')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                @include('partials.service-type-selector', [
                                    'selected' => old('appointment_type', $appointment->appointment_type ?? ''),
                                    'name' => 'appointment_type',
                                    'label' => 'SERVICE TYPE',
                                    'required' => true,
                                    'showIcons' => true,
                                    'multiple' => true,
                                    'placeholder' => 'Select Service Type',
                                    'module' => 'appointments',
                                ])
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section: Customer & Vehicle -->
                <div class="card modern-card mb-4">
                    <div class="card-header modern-card-header">
                        <h5 class="mb-0"><i class="fas fa-user me-2"></i>Customer & Vehicle</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="customer_id" class="form-label">Customer <span class="text-danger">*</span></label>
                                    <select class="form-select @error('customer_id') is-invalid @enderror" 
                                            id="customer_id" name="customer_id" required disabled>
                                        <option value="">Select Customer</option>
                                        @foreach($customers as $customer)
                                            <option value="{{ $customer->id }}" {{ $appointment->customer_id == $customer->id ? 'selected' : '' }}>
                                                {{ $customer->first_name }} {{ $customer->last_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <input type="hidden" name="customer_id" value="{{ $appointment->customer_id }}">
                                    <small class="form-text text-muted">
                                        <i class="fas fa-lock me-1"></i> Customer cannot be changed when rescheduling
                                    </small>
                                    @error('customer_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="vehicle_id" class="form-label">Vehicle <span class="text-danger">*</span></label>
                                    <select class="form-select @error('vehicle_id') is-invalid @enderror" 
                                            id="vehicle_id" name="vehicle_id" required>
                                        <option value="">Select Vehicle</option>
                                        @foreach($vehicles->where('customer_id', $appointment->customer_id) as $vehicle)
                                            <option value="{{ $vehicle->id }}" {{ $appointment->vehicle_id == $vehicle->id ? 'selected' : '' }}>
                                                {{ $vehicle->year }} {{ $vehicle->make }} {{ $vehicle->model }}
                                                @if($vehicle->license_plate)
                                                    ({{ $vehicle->license_plate }})
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('vehicle_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section: Staff Assignment -->
                <div class="card modern-card mb-4">
                    <div class="card-header modern-card-header">
                        <h5 class="mb-0"><i class="fas fa-users me-2"></i>Staff Assignment</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="service_advisor_id" class="form-label">Service Advisor</label>
                                    <select class="form-select @error('service_advisor_id') is-invalid @enderror" 
                                            id="service_advisor_id" name="service_advisor_id">
                                        <option value="">Select Service Advisor</option>
                                        @foreach($advisors as $advisor)
                                            <option value="{{ $advisor->id }}" {{ old('service_advisor_id', $appointment->service_advisor_id) == $advisor->id ? 'selected' : '' }}>
                                                {{ $advisor->first_name }} {{ $advisor->last_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('service_advisor_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="technician_id" class="form-label">Technician</label>
                                    <select class="form-select @error('technician_id') is-invalid @enderror" 
                                            id="assigned_technician_id" name="assigned_technician_id">
                                        <option value="">Select Technician</option>
                                        @foreach($technicians as $technician)
                                            <option value="{{ $technician->id }}" {{ old('assigned_technician_id', $appointment->assigned_technician_id) == $technician->id ? 'selected' : '' }}>
                                                {{ $technician->first_name }} {{ $technician->last_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('assigned_technician_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <!-- Multi-Technician Assignment -->
                            <div class="col-md-6">
                                @php $existingTechIds = $appointment->technicians->pluck('id')->toArray(); @endphp
                                @include('partials.technician-selector', [
                                    'technicians' => $allTechnicians,
                                    'selectedIds' => old('technicians', $existingTechIds),
                                    'label' => 'Additional Technicians',
                                    'helpText' => 'Assign additional technicians to this appointment',
                                ])
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section: Service Details -->
                <div class="card modern-card mb-4">
                    <div class="card-header modern-card-header">
                        <h5 class="mb-0"><i class="fas fa-tools me-2"></i>Service Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <div class="form-group mb-3">
                                    <label for="service_description" class="form-label">Service Description</label>
                                    <textarea class="form-control @error('service_description') is-invalid @enderror" 
                                              id="service_description" 
                                              name="service_description" 
                                              rows="3"
                                              placeholder="Describe the service needed...">{{ old('service_description', $appointment->service_description) }}</textarea>
                                    @error('service_description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section: Notes -->
                <div class="card modern-card mb-4">
                    <div class="card-header modern-card-header">
                        <h5 class="mb-0"><i class="fas fa-sticky-note me-2"></i>Notes</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="customer_notes" class="form-label">
                                        <i class="fas fa-user me-1"></i> Customer Notes
                                    </label>
                                    <textarea class="form-control @error('customer_notes') is-invalid @enderror" 
                                              id="customer_notes" 
                                              name="customer_notes" 
                                              rows="2"
                                              placeholder="Notes shared by customer...">{{ old('customer_notes', $appointment->customer_notes) }}</textarea>
                                    @error('customer_notes')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="internal_notes" class="form-label">
                                        <i class="fas fa-lock me-1"></i> Internal Notes
                                    </label>
                                    <textarea class="form-control @error('internal_notes') is-invalid @enderror" 
                                              id="internal_notes" 
                                              name="internal_notes" 
                                              rows="2"
                                              placeholder="Internal team notes...">{{ old('internal_notes', $appointment->internal_notes) }}</textarea>
                                    @error('internal_notes')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column -->
            <div class="col-lg-4">
                <!-- Appointment Info -->
                <div class="card modern-card mb-4">
                    <div class="card-header modern-card-header">
                        <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Appointment Info</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="info-label">Appointment #</div>
                            <div class="info-value fw-bold">{{ $appointment->appointment_number }}</div>
                        </div>
                        <div class="mb-3">
                            <div class="info-label">Current Status</div>
                            <div class="info-value">
                                <span class="badge badge-status-{{ $appointment->appointment_status }} fs-6">
                                    {{ ucfirst(str_replace('_', ' ', $appointment->appointment_status)) }}
                                </span>
                            </div>
                        </div>
                        @if($appointment->customer)
                        <div class="mb-3">
                            <div class="info-label">Customer Since</div>
                            <div class="info-value">
                                {{ $appointment->customer->created_at ? $appointment->customer->created_at->format('M d, Y') : 'N/A' }}
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="card modern-card mb-4">
                    <div class="card-header modern-card-header">
                        <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-save me-2"></i> Update Appointment
                            </button>
                            <a href="{{ route('appointments.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i> Cancel
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    var today = new Date().toISOString().split('T')[0];
    $('#appointment_date').attr('min', today);
    
    var customerId = {{ $appointment->customer_id }};
    filterVehiclesByCustomer(customerId);
    
    function filterVehiclesByCustomer(customerId) {
        var $vehicleSelect = $('#vehicle_id');
        $vehicleSelect.empty();
        $vehicleSelect.append('<option value="">Select Vehicle</option>');
        var allVehicles = {!! json_encode($vehicles) !!};
        var customerVehicles = allVehicles.filter(function(vehicle) {
            return vehicle.customer_id == customerId;
        });
        if (customerVehicles.length > 0) {
            customerVehicles.forEach(function(vehicle) {
                var displayText = vehicle.year + ' ' + vehicle.make + ' ' + vehicle.model;
                if (vehicle.license_plate) {
                    displayText += ' (' + vehicle.license_plate + ')';
                }
                var selected = vehicle.id == {{ $appointment->vehicle_id ?? 'null' }} ? 'selected' : '';
                $vehicleSelect.append('<option value="' + vehicle.id + '" ' + selected + '>' + displayText + '</option>');
            });
        } else {
            $vehicleSelect.append('<option value="">No vehicles registered for this customer</option>');
        }
    }
    
    // ===== VEHICLE TRANSACTION CHECK =====
    function checkVehicleTransactions(vehicleId) {
        if (!vehicleId) return;
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
                $('#vehicle_id').closest('.form-group').after(errMsg);
            }
        });
    }
    
    $('#vehicle_id').on('change', function() {
        checkVehicleTransactions($(this).val());
    });
    
    // Check on page load too
    checkVehicleTransactions($('#vehicle_id').val());
    
    initTechnicianMultiSelect(".technician-select-wrapper:not([data-tech-init])");
    
    // ===== PREVENT DUPLICATE TECHNICIAN =====
    var $techWrapper = $('.technician-select-wrapper');
    var $techOptions = $techWrapper.find('.tech-option');
    
    function syncPrimaryTechExclusion() {
        var primaryId = String($('#assigned_technician_id').val() || '');
        
        $techWrapper.find('.technician-tag[data-id="' + primaryId + '"] .remove-tech-btn').each(function() {
            $(this).trigger('click');
        });
        
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
    
    $techWrapper.off('input', '.search-input')
        .on('input.search-tech', '.search-input', applyExclusions);
    
    $techWrapper.find('.tech-add-btn').on('click', function() {
        setTimeout(applyExclusions, 50);
    });
    
    $('#assigned_technician_id').on('change', syncPrimaryTechExclusion);
    syncPrimaryTechExclusion();
});
</script>
@endpush

@endsection
