@extends('layouts.app')

@section('title', 'Create Quotation')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-plus-circle text-primary"></i> Create New Quotation
                    </h5>
                    <a href="{{ route('quotations.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                </div>
                
                <div class="card-body">
                    <form action="{{ route('quotations.store') }}" method="POST">
                        @csrf
                        
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <h5 class="mb-3" style="color: #B50C09;"><i class="fas fa-user me-2"></i>Customer Information</h5>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Full Name *</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" value="{{ old('name') }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email Address *</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                       id="email" name="email" value="{{ old('email') }}" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Phone Number *</label>
                                <input type="tel" class="form-control @error('phone') is-invalid @enderror" 
                                       id="phone" name="phone" value="{{ old('phone') }}" required>
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
                                       id="vehicle_make" name="vehicle_make" value="{{ old('vehicle_make') }}" required>
                                @error('vehicle_make')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="vehicle_model" class="form-label">Vehicle Model *</label>
                                <input type="text" class="form-control @error('vehicle_model') is-invalid @enderror" 
                                       id="vehicle_model" name="vehicle_model" value="{{ old('vehicle_model') }}" required>
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
                                        <option value="{{ $year }}" {{ old('vehicle_year') == $year ? 'selected' : '' }}>
                                            {{ $year }}
                                        </option>
                                    @endfor
                                </select>
                                @error('vehicle_year')
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
                                    <option value="General Maintenance" {{ old('service_type') == 'General Maintenance' ? 'selected' : '' }}>General Maintenance</option>
                                    <option value="Brake Service" {{ old('service_type') == 'Brake Service' ? 'selected' : '' }}>Brake Service</option>
                                    <option value="Engine Repair" {{ old('service_type') == 'Engine Repair' ? 'selected' : '' }}>Engine Repair</option>
                                    <option value="Transmission" {{ old('service_type') == 'Transmission' ? 'selected' : '' }}>Transmission</option>
                                    <option value="Electrical" {{ old('service_type') == 'Electrical' ? 'selected' : '' }}>Electrical</option>
                                    <option value="AC Repair" {{ old('service_type') == 'AC Repair' ? 'selected' : '' }}>AC Repair</option>
                                    <option value="Tire Service" {{ old('service_type') == 'Tire Service' ? 'selected' : '' }}>Tire Service</option>
                                    <option value="Body Work" {{ old('service_type') == 'Body Work' ? 'selected' : '' }}>Body Work</option>
                                    <option value="Other" {{ old('service_type') == 'Other' ? 'selected' : '' }}>Other</option>
                                </select>
                                @error('service_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label">Status *</label>
                                <select class="form-select @error('status') is-invalid @enderror" 
                                        id="status" name="status" required>
                                    <option value="pending" {{ old('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="reviewed" {{ old('status') == 'reviewed' ? 'selected' : '' }}>Reviewed</option>
                                    <option value="contacted" {{ old('status') == 'contacted' ? 'selected' : '' }}>Contacted</option>
                                    <option value="converted" {{ old('status') == 'converted' ? 'selected' : '' }}>Converted</option>
                                    <option value="rejected" {{ old('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-12 mb-3">
                                <label for="service_description" class="form-label">Service Description *</label>
                                <textarea class="form-control @error('service_description') is-invalid @enderror" 
                                          id="service_description" name="service_description" rows="4" required>{{ old('service_description') }}</textarea>
                                @error('service_description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-12 mb-3">
                                <label for="admin_notes" class="form-label">Admin Notes</label>
                                <textarea class="form-control @error('admin_notes') is-invalid @enderror" 
                                          id="admin_notes" name="admin_notes" rows="3">{{ old('admin_notes') }}</textarea>
                                @error('admin_notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <button type="reset" class="btn btn-outline-secondary">
                                <i class="fas fa-redo"></i> Reset
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Create Quotation
                            </button>
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
</script>
@endsection