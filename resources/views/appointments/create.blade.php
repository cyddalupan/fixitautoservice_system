@extends('layouts.app')

@section('title', 'Schedule Appointment - Fix-It Auto Services')

@section('content')
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-calendar-plus me-2"></i>Schedule Appointment
            </h1>
            <p class="text-muted mb-0">Book a new service appointment</p>
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
                <form method="POST" action="{{ route('appointments.store') }}">
                    @csrf
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="customer_id" class="form-label">Customer *</label>
                                <select class="form-select @error('customer_id') is-invalid @enderror" 
                                        id="customer_id" name="customer_id" required>
                                    <option value="">Select Customer</option>
                                    @foreach($customers as $customer)
                                        <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                                            {{ $customer->first_name }} {{ $customer->last_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('customer_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="form-group mb-3">
                                <label for="vehicle_id" class="form-label">Vehicle</label>
                                <select class="form-select @error('vehicle_id') is-invalid @enderror" 
                                        id="vehicle_id" name="vehicle_id">
                                    <option value="">Select Vehicle</option>
                                    <!-- Vehicles will be loaded via AJAX based on customer selection -->
                                </select>
                                @error('vehicle_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="form-group mb-3">
                                <label for="service_type" class="form-label">Service Type *</label>
                                <select class="form-select @error('service_type') is-invalid @enderror" 
                                        id="service_type" name="service_type" required>
                                    <option value="">Select Service Type</option>
                                    <option value="oil_change" {{ old('service_type') == 'oil_change' ? 'selected' : '' }}>Oil Change</option>
                                    <option value="tire_rotation" {{ old('service_type') == 'tire_rotation' ? 'selected' : '' }}>Tire Rotation</option>
                                    <option value="brake_service" {{ old('service_type') == 'brake_service' ? 'selected' : '' }}>Brake Service</option>
                                    <option value="engine_diagnostic" {{ old('service_type') == 'engine_diagnostic' ? 'selected' : '' }}>Engine Diagnostic</option>
                                    <option value="transmission" {{ old('service_type') == 'transmission' ? 'selected' : '' }}>Transmission Service</option>
                                    <option value="ac_service" {{ old('service_type') == 'ac_service' ? 'selected' : '' }}>A/C Service</option>
                                    <option value="general_maintenance" {{ old('service_type') == 'general_maintenance' ? 'selected' : '' }}>General Maintenance</option>
                                    <option value="emergency" {{ old('service_type') == 'emergency' ? 'selected' : '' }}>Emergency Repair</option>
                                </select>
                                @error('service_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="appointment_date" class="form-label">Date *</label>
                                <input type="date" class="form-control @error('appointment_date') is-invalid @enderror" 
                                       id="appointment_date" name="appointment_date" value="{{ old('appointment_date', date('Y-m-d')) }}" required>
                                @error('appointment_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="form-group mb-3">
                                <label for="appointment_time" class="form-label">Time *</label>
                                <select class="form-select @error('appointment_time') is-invalid @enderror" 
                                        id="appointment_time" name="appointment_time" required>
                                    <option value="">Select Time</option>
                                    @php
                                        $times = [
                                            '08:00', '08:30', '09:00', '09:30', '10:00', '10:30',
                                            '11:00', '11:30', '12:00', '12:30', '13:00', '13:30',
                                            '14:00', '14:30', '15:00', '15:30', '16:00', '16:30'
                                        ];
                                    @endphp
                                    @foreach($times as $time)
                                        <option value="{{ $time }}" {{ old('appointment_time') == $time ? 'selected' : '' }}>
                                            {{ date('g:i A', strtotime($time)) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('appointment_time')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="form-group mb-3">
                                <label for="estimated_duration" class="form-label">Estimated Duration</label>
                                <select class="form-select @error('estimated_duration') is-invalid @enderror" 
                                        id="estimated_duration" name="estimated_duration">
                                    <option value="30" {{ old('estimated_duration') == '30' ? 'selected' : '' }}>30 minutes</option>
                                    <option value="60" {{ old('estimated_duration', '60') == '60' ? 'selected' : '' }}>1 hour</option>
                                    <option value="90" {{ old('estimated_duration') == '90' ? 'selected' : '' }}>1.5 hours</option>
                                    <option value="120" {{ old('estimated_duration') == '120' ? 'selected' : '' }}>2 hours</option>
                                    <option value="180" {{ old('estimated_duration') == '180' ? 'selected' : '' }}>3 hours</option>
                                    <option value="240" {{ old('estimated_duration') == '240' ? 'selected' : '' }}>4 hours</option>
                                    <option value="480" {{ old('estimated_duration') == '480' ? 'selected' : '' }}>Full day</option>
                                </select>
                                @error('estimated_duration')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group mb-3">
                                <label for="description" class="form-label">Description / Notes</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" 
                                          id="description" name="description" rows="3">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror>
                                <small class="form-text text-muted">Describe the issue or service needed</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="assigned_to" class="form-label">Assigned Technician</label>
                                <select class="form-select @error('assigned_to') is-invalid @enderror" 
                                        id="assigned_to" name="assigned_to">
                                    <option value="">Select Technician</option>
                                    @foreach($technicians as $tech)
                                        <option value="{{ $tech->id }}" {{ old('assigned_to') == $tech->id ? 'selected' : '' }}>
                                            {{ $tech->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('assigned_to')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="priority" class="form-label">Priority</label>
                                <select class="form-select @error('priority') is-invalid @enderror" 
                                        id="priority" name="priority">
                                    <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>Low</option>
                                    <option value="normal" {{ old('priority', 'normal') == 'normal' ? 'selected' : '' }}>Normal</option>
                                    <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>High</option>
                                    <option value="urgent" {{ old('priority') == 'urgent' ? 'selected' : '' }}>Urgent</option>
                                </select>
                                @error('priority')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('appointments.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times me-1"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-calendar-check me-1"></i> Schedule Appointment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Load vehicles when customer is selected
        $('#customer_id').on('change', function() {
            var customerId = $(this).val();
            if (customerId) {
                $.ajax({
                    url: '/api/customers/' + customerId + '/vehicles',
                    type: 'GET',
                    success: function(data) {
                        var vehicleSelect = $('#vehicle_id');
                        vehicleSelect.empty();
                        vehicleSelect.append('<option value="">Select Vehicle</option>');
                        
                        $.each(data, function(index, vehicle) {
                            var displayText = vehicle.year + ' ' + vehicle.make + ' ' + vehicle.model;
                            if (vehicle.trim) {
                                displayText += ' ' + vehicle.trim;
                            }
                            if (vehicle.license_plate) {
                                displayText += ' (' + vehicle.license_plate + ')';
                            }
                            
                            vehicleSelect.append('<option value="' + vehicle.id + '">' + displayText + '</option>');
                        });
                    },
                    error: function() {
                        console.log('Error loading vehicles');
                    }
                });
            } else {
                $('#vehicle_id').empty().append('<option value="">Select Vehicle</option>');
            }
        });
        
        // Set minimum date to today
        var today = new Date().toISOString().split('T')[0];
        $('#appointment_date').attr('min', today);
    });
</script>
@endsection