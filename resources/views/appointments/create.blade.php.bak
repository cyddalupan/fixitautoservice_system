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
                                        id="customer_id" name="customer_id" required
                                        {{ isset($selectedCustomer) && $selectedCustomer ? 'disabled' : '' }}>
                                    <option value="">Select Customer</option>
                                    @foreach($customers as $customer)
                                        <option value="{{ $customer->id }}" {{ (old('customer_id') == $customer->id || (isset($selectedCustomer) && $selectedCustomer && $selectedCustomer->id == $customer->id)) ? 'selected' : '' }}>
                                            {{ $customer->first_name }} {{ $customer->last_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @if(isset($selectedCustomer) && $selectedCustomer)
                                <input type="hidden" name="customer_id" value="{{ $selectedCustomer->id }}">
                                <small class="form-text text-muted">
                                    <i class="fas fa-lock me-1"></i> Customer locked: Scheduling appointment exclusively for {{ $selectedCustomer->first_name }} {{ $selectedCustomer->last_name }}
                                </small>
                                @endif
                                @error('customer_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="form-group mb-3">
                                <label for="vehicle_id" class="form-label">Vehicle *</label>
                                <select class="form-select @error('vehicle_id') is-invalid @enderror" 
                                        id="vehicle_id" name="vehicle_id" required>
                                    <option value="">Select Customer First</option>
                                    <!-- Vehicles will be populated dynamically based on customer selection -->
                                </select>
                                @error('vehicle_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">Select customer first to see their registered vehicles</small>
                                
                                <!-- Hidden field to store vehicle description for backward compatibility -->
                                <input type="hidden" id="vehicle_description" name="vehicle_description" value="{{ old('vehicle_description') }}">
                            </div>
                            
                            <!-- Add New Vehicle Button (Optional) -->
                            <div class="form-group mb-3">
                                <button type="button" class="btn btn-outline-primary btn-sm" id="addNewVehicleBtn" style="display: none;">
                                    <i class="fas fa-plus me-1"></i> Add New Vehicle for This Customer
                                </button>
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
    
    <!-- JavaScript for dynamic vehicle dropdown -->
    
    <!-- JavaScript for dynamic vehicle dropdown -->
    <script>
    // Store all vehicles data from server
    var allVehicles = {!! json_encode($vehicles) !!};
    
    // Store selected vehicle ID from server (if any)
    var selectedVehicleId = {{ $selectedVehicle ? $selectedVehicle->id : 'null' }};
    
    // Group vehicles by customer_id for quick lookup
    var vehiclesByCustomer = {};
    allVehicles.forEach(function(vehicle) {
        if (!vehiclesByCustomer[vehicle.customer_id]) {
            vehiclesByCustomer[vehicle.customer_id] = [];
        }
        vehiclesByCustomer[vehicle.customer_id].push(vehicle);
    });
    
    // Wait for jQuery to be available
    function waitForJQuery(callback) {
        if (window.jQuery) {
            callback();
        } else {
            setTimeout(function() { waitForJQuery(callback); }, 100);
        }
    }
    
    waitForJQuery(function() {
        $(document).ready(function() {
            console.log('=== DEBUG: APPOINTMENTS CREATE PAGE LOADED ===');
            console.log('DEBUG: selectedVehicleId =', selectedVehicleId);
            
            // Get elements
            var $customer = $('#customer_id');
            var $vehicle = $('#vehicle_id');
            var $vehicleDescription = $('#vehicle_description');
            
            console.log('DEBUG: Customer dropdown disabled?', $customer.prop('disabled'));
            console.log('DEBUG: Customer dropdown value:', $customer.val());
            console.log('DEBUG: Vehicle dropdown exists?', $vehicle.length > 0);
            
            // Customer change handler (only if not disabled)
            if (!$customer.prop('disabled')) {
                $customer.on('change', function() {
                    var customerId = $(this).val();
                    
                    // Clear vehicle dropdown
                    $vehicle.empty();
                    
                    if (!customerId) {
                        // No customer selected
                        $vehicle.append('<option value="">Select Customer First</option>');
                        $vehicle.prop('disabled', true);
                    } else {
                        // Customer selected
                        $vehicle.append('<option value="">Select Vehicle</option>');
                        $vehicle.prop('disabled', false);
                        
                        // Get vehicles for this customer
                        var customerVehicles = vehiclesByCustomer[customerId] || [];
                        
                        if (customerVehicles.length > 0) {
                            // Add customer's actual vehicles
                            customerVehicles.forEach(function(vehicle) {
                                var optionText = vehicle.year + ' ' + vehicle.make + ' ' + vehicle.model;
                                if (vehicle.plate_number) {
                                    optionText += ' (' + vehicle.plate_number + ')';
                                }
                                
                                $vehicle.append('<option value="' + vehicle.id + '" data-description="' + optionText + '">' + optionText + '</option>');
                            });
                        } else {
                            // Customer has no vehicles
                            $vehicle.append('<option value="">No vehicles registered for this customer</option>');
                        }
                        
                        // Select the pre-selected vehicle if any (and if it belongs to this customer)
                        if (selectedVehicleId && selectedVehicleId !== null) {
                            setTimeout(function() {
                                // Check if this vehicle belongs to the selected customer
                                var vehicleBelongsToCustomer = false;
                                if (customerVehicles.length > 0) {
                                    customerVehicles.forEach(function(vehicle) {
                                        if (vehicle.id == selectedVehicleId) {
                                            vehicleBelongsToCustomer = true;
                                        }
                                    });
                                }
                                
                                if (vehicleBelongsToCustomer) {
                                    $vehicle.val(selectedVehicleId).trigger('change');
                                }
                            }, 50);
                        }
                    }
                    
                    // Clear vehicle description
                    $vehicleDescription.val('');
                });
            }
            
            // If customer is pre-selected and locked, trigger vehicle load immediately
            if ($customer.prop('disabled') && $customer.val()) {
                var customerId = $customer.val();
                $vehicle.empty();
                $vehicle.append('<option value="">Select Vehicle</option>');
                $vehicle.prop('disabled', false);
                
                // Get vehicles for this customer
                var customerVehicles = vehiclesByCustomer[customerId] || [];
                
                if (customerVehicles.length > 0) {
                    // Add customer's actual vehicles
                    customerVehicles.forEach(function(vehicle) {
                        var optionText = vehicle.year + ' ' + vehicle.make + ' ' + vehicle.model;
                        if (vehicle.plate_number) {
                            optionText += ' (' + vehicle.plate_number + ')';
                        }
                        
                        $vehicle.append('<option value="' + vehicle.id + '" data-description="' + optionText + '">' + optionText + '</option>');
                    });
                } else {
                    // Customer has no vehicles
                    $vehicle.append('<option value="">No vehicles registered for this customer</option>');
                }
                
                // Select the pre-selected vehicle if any
                if (selectedVehicleId && selectedVehicleId !== null) {
                    console.log('DEBUG: Customer disabled block - Trying to select vehicle ID:', selectedVehicleId);
                    console.log('DEBUG: Vehicle options available:', $vehicle.find('option').length);
                    
                    setTimeout(function() {
                        console.log('DEBUG: 50ms timeout - Selecting vehicle ID:', selectedVehicleId);
                        console.log('DEBUG: Vehicle value before:', $vehicle.val());
                        $vehicle.val(selectedVehicleId).trigger('change');
                        console.log('DEBUG: Vehicle value after:', $vehicle.val());
                        
                        // Double-check after a bit more time
                        setTimeout(function() {
                            console.log('DEBUG: 150ms check - Vehicle value:', $vehicle.val());
                            console.log('DEBUG: Vehicle selected text:', $vehicle.find('option:selected').text());
                        }, 100);
                    }, 50);
                }
            }
            
            // Update vehicle description when vehicle is selected
            $vehicle.on('change', function() {
                var selectedOption = $(this).find('option:selected');
                var description = selectedOption.data('description') || selectedOption.text();
                $vehicleDescription.val(description);
            });
            
            // Trigger on page load if customer pre-selected
            if ($customer.val()) {
                $customer.trigger('change');
                
                // After vehicle dropdown is populated, select the pre-selected vehicle if any
                setTimeout(function() {
                    if (selectedVehicleId && selectedVehicleId !== null) {
                        console.log('DEBUG: Customer trigger change block - Selecting vehicle ID:', selectedVehicleId);
                        console.log('DEBUG: Vehicle value before (100ms):', $vehicle.val());
                        $vehicle.val(selectedVehicleId).trigger('change');
                        console.log('DEBUG: Vehicle value after (100ms):', $vehicle.val());
                    }
                }, 100);
            }
            
            // Set minimum date to today
            var today = new Date().toISOString().split('T')[0];
            $('#appointment_date').attr('min', today);
        });
    });
    </script>
</div>
@endsection
