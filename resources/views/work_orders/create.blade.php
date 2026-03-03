@extends('layouts.app')

@section('title', 'Create Work Order - Fix-It Auto Services')

@section('content')
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-wrench me-2"></i>Create Work Order
            </h1>
            <p class="text-muted mb-0">Create a new service work order</p>
        </div>
        <div>
            <a href="{{ route('work-orders.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Work Orders
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('work-orders.store') }}">
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
                                <label for="vehicle_id" class="form-label">Vehicle *</label>
                                <select class="form-select @error('vehicle_id') is-invalid @enderror" 
                                        id="vehicle_id" name="vehicle_id" required>
                                    <option value="">Select Vehicle</option>
                                    <!-- Vehicles will be loaded via AJAX based on customer selection -->
                                </select>
                                @error('vehicle_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="form-group mb-3">
                                <label for="work_order_type" class="form-label">Work Order Type *</label>
                                <select class="form-select @error('work_order_type') is-invalid @enderror" 
                                        id="work_order_type" name="work_order_type" required>
                                    <option value="">Select Type</option>
                                    <option value="repair" {{ old('work_order_type') == 'repair' ? 'selected' : '' }}>Repair</option>
                                    <option value="maintenance" {{ old('work_order_type') == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                                    <option value="diagnostic" {{ old('work_order_type') == 'diagnostic' ? 'selected' : '' }}>Diagnostic</option>
                                    <option value="inspection" {{ old('work_order_type') == 'inspection' ? 'selected' : '' }}>Inspection</option>
                                    <option value="emergency" {{ old('work_order_type') == 'emergency' ? 'selected' : '' }}>Emergency</option>
                                </select>
                                @error('work_order_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="status" class="form-label">Status *</label>
                                <select class="form-select @error('status') is-invalid @enderror" 
                                        id="status" name="status" required>
                                    <option value="pending" {{ old('status', 'pending') == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="in_progress" {{ old('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                    <option value="waiting_parts" {{ old('status') == 'waiting_parts' ? 'selected' : '' }}>Waiting for Parts</option>
                                    <option value="completed" {{ old('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                                    <option value="cancelled" {{ old('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
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
                            
                            <div class="form-group mb-3">
                                <label for="estimated_completion_date" class="form-label">Estimated Completion</label>
                                <input type="date" class="form-control @error('estimated_completion_date') is-invalid @enderror" 
                                       id="estimated_completion_date" name="estimated_completion_date" value="{{ old('estimated_completion_date') }}">
                                @error('estimated_completion_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group mb-3">
                                <label for="description" class="form-label">Description *</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" 
                                          id="description" name="description" rows="3" required>{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror>
                                <small class="form-text text-muted">Describe the work to be performed</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="assigned_technician_id" class="form-label">Assigned Technician</label>
                                <select class="form-select @error('assigned_technician_id') is-invalid @enderror" 
                                        id="assigned_technician_id" name="assigned_technician_id">
                                    <option value="">Select Technician</option>
                                    @foreach($technicians as $tech)
                                        <option value="{{ $tech->id }}" {{ old('assigned_technician_id') == $tech->id ? 'selected' : '' }}>
                                            {{ $tech->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('assigned_technician_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="estimated_hours" class="form-label">Estimated Hours</label>
                                <input type="number" step="0.5" class="form-control @error('estimated_hours') is-invalid @enderror" 
                                       id="estimated_hours" name="estimated_hours" value="{{ old('estimated_hours') }}" min="0">
                                @error('estimated_hours')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="labor_rate" class="form-label">Labor Rate (₱/hour)</label>
                                <input type="number" step="0.01" class="form-control @error('labor_rate') is-invalid @enderror" 
                                       id="labor_rate" name="labor_rate" value="{{ old('labor_rate', 85.00) }}" min="0">
                                @error('labor_rate')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="estimated_total" class="form-label">Estimated Total</label>
                                <div class="input-group">
                                    <span class="input-group-text">₱</span>
                                    <input type="number" step="0.01" class="form-control @error('estimated_total') is-invalid @enderror" 
                                           id="estimated_total" name="estimated_total" value="{{ old('estimated_total') }}" min="0" readonly>
                                    @error('estimated_total')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror>
                                </div>
                                <small class="form-text text-muted">Calculated based on labor rate and estimated hours</small>
                            </div>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('work-orders.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times me-1"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Create Work Order
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
        
        // Calculate estimated total
        function calculateTotal() {
            var laborRate = parseFloat($('#labor_rate').val()) || 0;
            var estimatedHours = parseFloat($('#estimated_hours').val()) || 0;
            var estimatedTotal = laborRate * estimatedHours;
            $('#estimated_total').val(estimatedTotal.toFixed(2));
        }
        
        $('#labor_rate, #estimated_hours').on('input', calculateTotal);
        
        // Set minimum date to today
        var today = new Date().toISOString().split('T')[0];
        $('#estimated_completion_date').attr('min', today);
        
        // Initialize calculation
        calculateTotal();
    });
</script>
@endsection