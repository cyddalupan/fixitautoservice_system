@extends('layouts.app')

@section('title', 'Reschedule Appointment - Fix-It Auto Services')

@section('content')
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-calendar-edit me-2"></i>Reschedule Appointment
            </h1>
            <p class="text-muted mb-0">Update appointment details</p>
        </div>
        <div>
            <a href="{{ route('appointments.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Appointments
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('appointments.update', $appointment) }}">
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="customer_id" class="form-label">Customer *</label>
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
                                <label for="vehicle_id" class="form-label">Vehicle *</label>
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
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="appointment_date" class="form-label">Appointment Date *</label>
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
                                <label for="appointment_time" class="form-label">Appointment Time *</label>
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
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="appointment_type" class="form-label">Service Type *</label>
                                <select class="form-select @error('appointment_type') is-invalid @enderror" 
                                        id="appointment_type" name="appointment_type" required>
                                    <option value="">Select Service Type</option>
                                    <option value="regular_service" {{ old('appointment_type', $appointment->appointment_type) == 'regular_service' ? 'selected' : '' }}>Regular Service</option>
                                    <option value="emergency" {{ old('appointment_type', $appointment->appointment_type) == 'emergency' ? 'selected' : '' }}>Emergency</option>
                                    <option value="inspection" {{ old('appointment_type', $appointment->appointment_type) == 'inspection' ? 'selected' : '' }}>Inspection</option>
                                    <option value="diagnostic" {{ old('appointment_type', $appointment->appointment_type) == 'diagnostic' ? 'selected' : '' }}>Diagnostic</option>
                                    <option value="repair" {{ old('appointment_type', $appointment->appointment_type) == 'repair' ? 'selected' : '' }}>Repair</option>
                                    <option value="maintenance" {{ old('appointment_type', $appointment->appointment_type) == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                                    <option value="tire_service" {{ old('appointment_type', $appointment->appointment_type) == 'tire_service' ? 'selected' : '' }}>Tire Service</option>
                                    <option value="oil_change" {{ old('appointment_type', $appointment->appointment_type) == 'oil_change' ? 'selected' : '' }}>Oil Change</option>
                                    <option value="brake_service" {{ old('appointment_type', $appointment->appointment_type) == 'brake_service' ? 'selected' : '' }}>Brake Service</option>
                                    <option value="other" {{ old('appointment_type', $appointment->appointment_type) == 'other' ? 'selected' : '' }}>Other</option>
                                </select>
                                @error('appointment_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="estimated_duration" class="form-label">Estimated Duration (hours)</label>
                                <input type="number" 
                                       class="form-control @error('estimated_duration') is-invalid @enderror" 
                                       id="estimated_duration" 
                                       name="estimated_duration" 
                                       value="{{ old('estimated_duration', $appointment->estimated_duration) }}">
                                @error('estimated_duration')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
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
                                        id="technician_id" name="technician_id">
                                    <option value="">Select Technician</option>
                                    @foreach($technicians as $technician)
                                        <option value="{{ $technician->id }}" {{ old('technician_id', $appointment->technician_id) == $technician->id ? 'selected' : '' }}>
                                            {{ $technician->first_name }} {{ $technician->last_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('technician_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="service_description" class="form-label">Service Description</label>
                        <textarea class="form-control @error('service_description') is-invalid @enderror" 
                                  id="service_description" 
                                  name="service_description" 
                                  rows="3">{{ old('service_description', $appointment->service_description) }}</textarea>
                        @error('service_description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="customer_notes" class="form-label">Customer Notes</label>
                        <textarea class="form-control @error('customer_notes') is-invalid @enderror" 
                                  id="customer_notes" 
                                  name="customer_notes" 
                                  rows="2">{{ old('customer_notes', $appointment->customer_notes) }}</textarea>
                        @error('customer_notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="internal_notes" class="form-label">Internal Notes</label>
                        <textarea class="form-control @error('internal_notes') is-invalid @enderror" 
                                  id="internal_notes" 
                                  name="internal_notes" 
                                  rows="2">{{ old('internal_notes', $appointment->internal_notes) }}</textarea>
                        @error('internal_notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('appointments.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times me-1"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Update Appointment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Set minimum date to today
    var today = new Date().toISOString().split('T')[0];
    $('#appointment_date').attr('min', today);
    
    // Filter vehicles by selected customer (customer is locked in edit mode)
    var customerId = {{ $appointment->customer_id }};
    var $vehicleSelect = $('#vehicle_id');
    
    // Initially filter vehicles for this customer
    filterVehiclesByCustomer(customerId);
    
    function filterVehiclesByCustomer(customerId) {
        $vehicleSelect.empty();
        $vehicleSelect.append('<option value="">Select Vehicle</option>');
        
        // Get all vehicles from PHP (passed as $vehicles)
        var allVehicles = {!! json_encode($vehicles) !!};
        
        // Filter vehicles for this customer
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
});
</script>
@endpush
@endsection