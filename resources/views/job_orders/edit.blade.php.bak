@extends('layouts.app')

@section('title', 'Edit Work Order ' . $workOrder->work_order_number . ' - Fix-It Auto Services')

@section('content')
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-edit me-2"></i>Edit Work Order: {{ $workOrder->work_order_number }}
            </h1>
            <p class="text-muted mb-0">
                {{ $workOrder->customer->first_name ?? 'Customer' }} {{ $workOrder->customer->last_name ?? '' }} - 
                {{ $workOrder->vehicle->make ?? '' }} {{ $workOrder->vehicle->model ?? '' }}
            </p>
        </div>
        <div>
            <a href="{{ route('work-orders.show', $workOrder) }}" class="btn btn-outline-secondary">
                <i class="fas fa-times me-1"></i> Cancel
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Work Order Details</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('work-orders.update', $workOrder) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <!-- Customer Information -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="customer_id" class="form-label">Customer *</label>
                                <select name="customer_id" id="customer_id" class="form-select" required>
                                    <option value="">Select Customer</option>
                                    @foreach($customers as $customer)
                                        <option value="{{ $customer->id }}" {{ $workOrder->customer_id == $customer->id ? 'selected' : '' }}>
                                            {{ $customer->first_name }} {{ $customer->last_name }} - {{ $customer->phone }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        
                        <!-- Vehicle Information -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="vehicle_id" class="form-label">Vehicle *</label>
                                <select name="vehicle_id" id="vehicle_id" class="form-select" required>
                                    <option value="">Select Vehicle</option>
                                    @foreach($vehicles as $vehicle)
                                        <option value="{{ $vehicle->id }}" {{ $workOrder->vehicle_id == $vehicle->id ? 'selected' : '' }}>
                                            {{ $vehicle->make }} {{ $vehicle->model }} - {{ $vehicle->license_plate }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <!-- Work Order Date -->
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="work_order_date" class="form-label">Work Order Date *</label>
                                <input type="date" name="work_order_date" id="work_order_date" 
                                       class="form-control" value="{{ old('work_order_date', $workOrder->work_order_date ? $workOrder->work_order_date->format('Y-m-d') : '') }}" required>
                            </div>
                        </div>
                        
                        <!-- Status -->
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="work_order_status" class="form-label">Status *</label>
                                <select name="work_order_status" id="work_order_status" class="form-select" required>
                                    <option value="pending" {{ $workOrder->work_order_status == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="repairing" {{ $workOrder->work_order_status == 'repairing' ? 'selected' : '' }}>Repairing</option>
                                    <option value="waiting_parts" {{ $workOrder->work_order_status == 'waiting_parts' ? 'selected' : '' }}>Waiting Parts</option>
                                    <option value="completed" {{ $workOrder->work_order_status == 'completed' ? 'selected' : '' }}>Completed</option>
                                    <option value="released" {{ $workOrder->work_order_status == 'released' ? 'selected' : '' }}>Released</option>
                                    <option value="cancelled" {{ $workOrder->work_order_status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                </select>
                            </div>
                        </div>
                        
                        <!-- Priority -->
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="priority" class="form-label">Priority *</label>
                                <select name="priority" id="priority" class="form-select" required>
                                    <option value="low" {{ $workOrder->priority == 'low' ? 'selected' : '' }}>Low</option>
                                    <option value="normal" {{ $workOrder->priority == 'normal' ? 'selected' : '' }}>Normal</option>
                                    <option value="high" {{ $workOrder->priority == 'high' ? 'selected' : '' }}>High</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <!-- Assigned Technician -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="technician_id" class="form-label">Assigned Technician</label>
                                <select name="technician_id" id="technician_id" class="form-select">
                                    <option value="">Select Technician</option>
                                    @foreach($technicians as $technician)
                                        <option value="{{ $technician->id }}" {{ $workOrder->technician_id == $technician->id ? 'selected' : '' }}>
                                            {{ $technician->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        
                        <!-- Service Advisor -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="service_advisor_id" class="form-label">Service Advisor</label>
                                <select name="service_advisor_id" id="service_advisor_id" class="form-select">
                                    <option value="">Select Service Advisor</option>
                                    @foreach($advisors as $advisor)
                                        <option value="{{ $advisor->id }}" {{ $workOrder->service_advisor_id == $advisor->id ? 'selected' : '' }}>
                                            {{ $advisor->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Description -->
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea name="description" id="description" class="form-control" rows="3">{{ old('description', $workOrder->description) }}</textarea>
                    </div>
                    
                    <!-- Notes -->
                    <div class="mb-3">
                        <label for="notes" class="form-label">Internal Notes</label>
                        <textarea name="notes" id="notes" class="form-control" rows="3">{{ old('notes', $workOrder->notes) }}</textarea>
                    </div>
                    
                    <!-- Submit Buttons -->
                    <div class="d-flex justify-content-end">
                        <a href="{{ route('work-orders.show', $workOrder) }}" class="btn btn-outline-secondary me-2">
                            <i class="fas fa-times me-1"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Update Work Order
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Auto-update vehicle options based on selected customer
    document.getElementById('customer_id').addEventListener('change', function() {
        const customerId = this.value;
        const vehicleSelect = document.getElementById('vehicle_id');
        
        if (!customerId) {
            // Reset to all vehicles
            vehicleSelect.innerHTML = '<option value="">Select Vehicle</option>' +
                @foreach($vehicles as $vehicle)
                    '<option value="{{ $vehicle->id }}" {{ $workOrder->vehicle_id == $vehicle->id ? 'selected' : '' }}>' +
                    '{{ $vehicle->make }} {{ $vehicle->model }} - {{ $vehicle->license_plate }}' +
                    '</option>' +
                @endforeach
            '';
            return;
        }
        
        // Filter vehicles by customer
        const allVehicles = @json($vehicles);
        vehicleSelect.innerHTML = '<option value="">Select Vehicle</option>';
        
        allVehicles.forEach(vehicle => {
            if (vehicle.customer_id == customerId) {
                const option = document.createElement('option');
                option.value = vehicle.id;
                option.textContent = vehicle.make + ' ' + vehicle.model + ' - ' + vehicle.license_plate;
                if (vehicle.id == {{ $workOrder->vehicle_id }}) {
                    option.selected = true;
                }
                vehicleSelect.appendChild(option);
            }
        });
    });
</script>
@endsection