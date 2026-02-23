@extends('layouts.app')

@section('title', 'Create Vehicle Inspection - Fix-It Auto Services')

@section('content')
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-car me-2"></i>Create Vehicle Inspection
            </h1>
            <p class="text-muted mb-0">Create a new vehicle inspection record</p>
        </div>
        <div>
            <a href="{{ route('inspections.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Inspections
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('inspections.store') }}">
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
                                <label for="inspection_type" class="form-label">Inspection Type *</label>
                                <select class="form-select @error('inspection_type') is-invalid @enderror" 
                                        id="inspection_type" name="inspection_type" required>
                                    <option value="">Select Type</option>
                                    <option value="pre_purchase" {{ old('inspection_type') == 'pre_purchase' ? 'selected' : '' }}>Pre-Purchase Inspection</option>
                                    <option value="safety" {{ old('inspection_type') == 'safety' ? 'selected' : '' }}>Safety Inspection</option>
                                    <option value="emissions" {{ old('inspection_type') == 'emissions' ? 'selected' : '' }}>Emissions Inspection</option>
                                    <option value="routine" {{ old('inspection_type') == 'routine' ? 'selected' : '' }}>Routine Maintenance Check</option>
                                    <option value="diagnostic" {{ old('inspection_type') == 'diagnostic' ? 'selected' : '' }}>Diagnostic Inspection</option>
                                    <option value="post_repair" {{ old('inspection_type') == 'post_repair' ? 'selected' : '' }}>Post-Repair Verification</option>
                                </select>
                                @error('inspection_type')
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
                                    <option value="completed" {{ old('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                                    <option value="approved" {{ old('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                                    <option value="rejected" {{ old('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="form-group mb-3">
                                <label for="inspection_date" class="form-label">Inspection Date *</label>
                                <input type="date" class="form-control @error('inspection_date') is-invalid @enderror" 
                                       id="inspection_date" name="inspection_date" value="{{ old('inspection_date', date('Y-m-d')) }}" required>
                                @error('inspection_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="form-group mb-3">
                                <label for="inspector_id" class="form-label">Inspector *</label>
                                <select class="form-select @error('inspector_id') is-invalid @enderror" 
                                        id="inspector_id" name="inspector_id" required>
                                    <option value="">Select Inspector</option>
                                    @foreach($inspectors as $inspector)
                                        <option value="{{ $inspector->id }}" {{ old('inspector_id') == $inspector->id ? 'selected' : '' }}>
                                            {{ $inspector->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('inspector_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group mb-3">
                                <label for="notes" class="form-label">Initial Notes</label>
                                <textarea class="form-control @error('notes') is-invalid @enderror" 
                                          id="notes" name="notes" rows="3">{{ old('notes') }}</textarea>
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror>
                                <small class="form-text text-muted">Add any initial notes or observations</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="odometer_reading" class="form-label">Odometer Reading</label>
                                <input type="number" class="form-control @error('odometer_reading') is-invalid @enderror" 
                                       id="odometer_reading" name="odometer_reading" value="{{ old('odometer_reading') }}" min="0">
                                @error('odometer_reading')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror>
                                <small class="form-text text-muted">Current mileage</small>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="inspection_fee" class="form-label">Inspection Fee</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" step="0.01" class="form-control @error('inspection_fee') is-invalid @enderror" 
                                           id="inspection_fee" name="inspection_fee" value="{{ old('inspection_fee', 49.99) }}" min="0">
                                    @error('inspection_fee')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="next_inspection_date" class="form-label">Next Inspection Due</label>
                                <input type="date" class="form-control @error('next_inspection_date') is-invalid @enderror" 
                                       id="next_inspection_date" name="next_inspection_date" value="{{ old('next_inspection_date') }}">
                                @error('next_inspection_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="estimated_repair_cost" class="form-label">Estimated Repair Cost</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" step="0.01" class="form-control @error('estimated_repair_cost') is-invalid @enderror" 
                                           id="estimated_repair_cost" name="estimated_repair_cost" value="{{ old('estimated_repair_cost') }}" min="0">
                                    @error('estimated_repair_cost')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <!-- Inspection Categories -->
                    <div class="row">
                        <div class="col-12">
                            <h5 class="mb-3">Inspection Categories</h5>
                            <p class="text-muted mb-3">Select the categories to include in this inspection:</p>
                            
                            <div class="row">
                                @php
                                    $categories = [
                                        'engine' => 'Engine & Transmission',
                                        'brakes' => 'Brake System',
                                        'suspension' => 'Suspension & Steering',
                                        'electrical' => 'Electrical System',
                                        'tires' => 'Tires & Wheels',
                                        'exhaust' => 'Exhaust System',
                                        'interior' => 'Interior & Safety',
                                        'exterior' => 'Exterior & Body',
                                        'fluids' => 'Fluids & Filters',
                                        'ac' => 'A/C & Heating',
                                    ];
                                @endphp
                                
                                @foreach($categories as $key => $label)
                                    <div class="col-md-4 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" 
                                                   id="category_{{ $key }}" name="categories[]" 
                                                   value="{{ $key }}" checked>
                                            <label class="form-check-label" for="category_{{ $key }}">
                                                {{ $label }}
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('inspections.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times me-1"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Create Inspection
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
        $('#inspection_date').attr('min', today);
        $('#next_inspection_date').attr('min', today);
    });
</script>
@endsection