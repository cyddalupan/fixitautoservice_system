@extends('layouts.app')

@section('title', 'Edit Quotation')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-edit text-warning"></i> Edit Quotation #{{ str_pad($quotation->id, 5, '0', STR_PAD_LEFT) }}
                    </h5>
                    <div class="btn-group">
                        <a href="{{ route('quotations.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </a>
                        <a href="{{ route('quotations.show', $quotation->id) }}" class="btn btn-outline-primary">
                            <i class="fas fa-eye"></i> View
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    <form action="{{ route('quotations.update', $quotation->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <h5 class="mb-3" style="color: #B50C09;"><i class="fas fa-user me-2"></i>Customer Information</h5>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Full Name *</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" value="{{ old('name', $quotation->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email Address *</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                       id="email" name="email" value="{{ old('email', $quotation->email) }}" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Phone Number *</label>
                                <input type="tel" class="form-control @error('phone') is-invalid @enderror" 
                                       id="phone" name="phone" value="{{ old('phone', $quotation->phone) }}" required>
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <h5 class="mb-3" style="color: #B50C09;"><i class="fas fa-car me-2"></i>Vehicle Information</h5>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="vehicle_make" class="form-label">Vehicle Make *</label>
                                <input type="text" class="form-control @error('vehicle_make') is-invalid @enderror" 
                                       id="vehicle_make" name="vehicle_make" value="{{ old('vehicle_make', $quotation->vehicle_make) }}" required>
                                @error('vehicle_make')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="vehicle_model" class="form-label">Vehicle Model *</label>
                                <input type="text" class="form-control @error('vehicle_model') is-invalid @enderror" 
                                       id="vehicle_model" name="vehicle_model" value="{{ old('vehicle_model', $quotation->vehicle_model) }}" required>
                                @error('vehicle_model')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="vehicle_year" class="form-label">Year *</label>
                                <select class="form-select @error('vehicle_year') is-invalid @enderror" 
                                        id="vehicle_year" name="vehicle_year" required>
                                    <option value="">Select Year</option>
                                    @for($year = date('Y') + 1; $year >= 1990; $year--)
                                        <option value="{{ $year }}" {{ old('vehicle_year', $quotation->vehicle_year) == $year ? 'selected' : '' }}>
                                            {{ $year }}
                                        </option>
                                    @endfor
                                </select>
                                @error('vehicle_year')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="license_plate" class="form-label">License Plate</label>
                                <input type="text" class="form-control @error('license_plate') is-invalid @enderror" 
                                       id="license_plate" name="license_plate" value="{{ old('license_plate', $quotation->license_plate) }}">
                                @error('license_plate')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="vin_number" class="form-label">VIN Number</label>
                                <input type="text" class="form-control @error('vin_number') is-invalid @enderror" 
                                       id="vin_number" name="vin_number" value="{{ old('vin_number', $quotation->vin_number) }}">
                                @error('vin_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="preferred_date" class="form-label">Preferred Date</label>
                                <input type="date" class="form-control @error('preferred_date') is-invalid @enderror" 
                                       id="preferred_date" name="preferred_date" value="{{ old('preferred_date', $quotation->preferred_date) }}">
                                @error('preferred_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="preferred_time" class="form-label">Preferred Time</label>
                                <select class="form-select @error('preferred_time') is-invalid @enderror" 
                                        id="preferred_time" name="preferred_time">
                                    <option value="">Select Time</option>
                                    <option value="Morning (8AM-12PM)" {{ old('preferred_time', $quotation->preferred_time) == 'Morning (8AM-12PM)' ? 'selected' : '' }}>Morning (8AM-12PM)</option>
                                    <option value="Afternoon (1PM-5PM)" {{ old('preferred_time', $quotation->preferred_time) == 'Afternoon (1PM-5PM)' ? 'selected' : '' }}>Afternoon (1PM-5PM)</option>
                                    <option value="Evening (6PM-9PM)" {{ old('preferred_time', $quotation->preferred_time) == 'Evening (6PM-9PM)' ? 'selected' : '' }}>Evening (6PM-9PM)</option>
                                    <option value="Anytime" {{ old('preferred_time', $quotation->preferred_time) == 'Anytime' ? 'selected' : '' }}>Anytime</option>
                                </select>
                                @error('preferred_time')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <h5 class="mb-3" style="color: #B50C09;"><i class="fas fa-tools me-2"></i>Service Information</h5>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="service_type" class="form-label">Service Type *</label>
                                <select class="form-select @error('service_type') is-invalid @enderror" 
                                        id="service_type" name="service_type" required>
                                    <option value="">Select Service Type</option>
                                    <option value="General Maintenance" {{ old('service_type', $quotation->service_type) == 'General Maintenance' ? 'selected' : '' }}>General Maintenance</option>
                                    <option value="Brake Service" {{ old('service_type', $quotation->service_type) == 'Brake Service' ? 'selected' : '' }}>Brake Service</option>
                                    <option value="Engine Repair" {{ old('service_type', $quotation->service_type) == 'Engine Repair' ? 'selected' : '' }}>Engine Repair</option>
                                    <option value="Transmission" {{ old('service_type', $quotation->service_type) == 'Transmission' ? 'selected' : '' }}>Transmission</option>
                                    <option value="Electrical" {{ old('service_type', $quotation->service_type) == 'Electrical' ? 'selected' : '' }}>Electrical</option>
                                    <option value="AC Repair" {{ old('service_type', $quotation->service_type) == 'AC Repair' ? 'selected' : '' }}>AC Repair</option>
                                    <option value="Tire Service" {{ old('service_type', $quotation->service_type) == 'Tire Service' ? 'selected' : '' }}>Tire Service</option>
                                    <option value="Body Work" {{ old('service_type', $quotation->service_type) == 'Body Work' ? 'selected' : '' }}>Body Work</option>
                                    <option value="Other" {{ old('service_type', $quotation->service_type) == 'Other' ? 'selected' : '' }}>Other</option>
                                </select>
                                @error('service_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label">Status *</label>
                                <select class="form-select @error('status') is-invalid @enderror" 
                                        id="status" name="status" required>
                                    <option value="pending" {{ old('status', $quotation->status) == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="reviewed" {{ old('status', $quotation->status) == 'reviewed' ? 'selected' : '' }}>Reviewed</option>
                                    <option value="contacted" {{ old('status', $quotation->status) == 'contacted' ? 'selected' : '' }}>Contacted</option>
                                    <option value="converted" {{ old('status', $quotation->status) == 'converted' ? 'selected' : '' }}>Converted</option>
                                    <option value="rejected" {{ old('status', $quotation->status) == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="parts_preference" class="form-label">Parts Preference</label>
                                <select class="form-select @error('parts_preference') is-invalid @enderror" 
                                        id="parts_preference" name="parts_preference">
                                    <option value="">Select Preference</option>
                                    <option value="OEM Parts Only" {{ old('parts_preference', $quotation->parts_preference) == 'OEM Parts Only' ? 'selected' : '' }}>OEM Parts Only</option>
                                    <option value="Aftermarket Parts OK" {{ old('parts_preference', $quotation->parts_preference) == 'Aftermarket Parts OK' ? 'selected' : '' }}>Aftermarket Parts OK</option>
                                    <option value="Used/Refurbished OK" {{ old('parts_preference', $quotation->parts_preference) == 'Used/Refurbished OK' ? 'selected' : '' }}>Used/Refurbished OK</option>
                                    <option value="No Preference" {{ old('parts_preference', $quotation->parts_preference) == 'No Preference' ? 'selected' : '' }}>No Preference</option>
                                </select>
                                @error('parts_preference')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <label for="budget_min" class="form-label">Min Budget (₱)</label>
                                <input type="number" class="form-control @error('budget_min') is-invalid @enderror" 
                                       id="budget_min" name="budget_min" value="{{ old('budget_min', $quotation->budget_min) }}" min="0" step="0.01">
                                @error('budget_min')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <label for="budget_max" class="form-label">Max Budget (₱)</label>
                                <input type="number" class="form-control @error('budget_max') is-invalid @enderror" 
                                       id="budget_max" name="budget_max" value="{{ old('budget_max', $quotation->budget_max) }}" min="0" step="0.01">
                                @error('budget_max')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-12 mb-3">
                                <label for="service_description" class="form-label">Service Description *</label>
                                <textarea class="form-control @error('service_description') is-invalid @enderror" 
                                          id="service_description" name="service_description" rows="4" required>{{ old('service_description', $quotation->service_description) }}</textarea>
                                @error('service_description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-12 mb-3">
                                <div class="form-check">
                                    <input class="form-check-input @error('consent_contact') is-invalid @enderror" 
                                           type="checkbox" id="consent_contact" name="consent_contact" value="1" 
                                           {{ old('consent_contact', $quotation->consent_contact) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="consent_contact">
                                        Customer consented to be contacted
                                    </label>
                                    @error('consent_contact')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-12 mb-3">
                                <label for="admin_notes" class="form-label">Admin Notes</label>
                                <textarea class="form-control @error('admin_notes') is-invalid @enderror" 
                                          id="admin_notes" name="admin_notes" rows="3">{{ old('admin_notes', $quotation->admin_notes) }}</textarea>
                                @error('admin_notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <button type="reset" class="btn btn-outline-secondary">
                                <i class="fas fa-redo"></i> Reset to Original
                            </button>
                            <div class="btn-group">
                                <a href="{{ route('quotations.show', $quotation->id) }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times"></i> Cancel
                                </a>
                                <button type="submit" class="btn btn-warning">
                                    <i class="fas fa-save"></i> Update Quotation
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Auto-format phone number
    document.getElementById('phone').addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length > 0) {
            if (value.length <= 3) {
                value = value;
            } else if (value.length <= 6) {
                value = value.slice(0, 3) + ' ' + value.slice(3);
            } else if (value.length <= 10) {
                value = value.slice(0, 3) + ' ' + value.slice(3, 6) + ' ' + value.slice(6);
            } else {
                value = value.slice(0, 3) + ' ' + value.slice(3, 6) + ' ' + value.slice(6, 10) + ' ' + value.slice(10);
            }
        }
        e.target.value = value;
    });
    
    // Reset form to original values
    document.querySelector('button[type="reset"]').addEventListener('click', function(e) {
        e.preventDefault();
        if (confirm('Are you sure you want to reset all changes? This will restore the original values.')) {
            // Reload the page to get original values
            window.location.reload();
        }
    });
</script>
@endsection