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
                            
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Service Type * (Select all that apply)</label>
                                @php $allServiceTypes = ['PREVENTIVE MAINTENANCE', 'AUTO-MECHANICAL', 'AUTO-ELECTRICAL', 'AUTO-ELECTRONICS', 'AUTO AIR-CONDITIONING', 'BODY REPAIR AND PAINTING', 'AUTO PARTS SALES', 'HOME SERVICE REQUEST']; @endphp
                                <div class="row g-3 mt-1">
                                    @foreach($allServiceTypes as $st)
                                        <div class="col-md-6">
                                            <label class="service-card">
                                                <input type="checkbox" name="service_type[]" value="{{ $st }}" class="service-card-input"
                                                    {{ is_array(old('service_type')) && in_array($st, old('service_type')) ? 'checked' : '' }}>
                                                <div class="service-card-body">
                                                    <i class="fas fa-tools service-card-icon"></i>
                                                    <span class="service-card-label">{{ $st }}</span>
                                                </div>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                                @error('service_type')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
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

@push('styles')
<style>
.service-card {
    display: block;
    cursor: pointer;
    width: 100%;
}
.service-card-input {
    display: none;
}
.service-card-body {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px 16px;
    border: 2px solid #dee2e6;
    border-radius: 10px;
    background: #fff;
    transition: all 0.25s ease;
    user-select: none;
    height: 100%;
}
.service-card-body:hover {
    border-color: #B50C09;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(181, 12, 9, 0.15);
}
.service-card-input:checked + .service-card-body {
    border-color: #B50C09;
    background: rgba(181, 12, 9, 0.08);
    box-shadow: 0 0 0 3px rgba(181, 12, 9, 0.15);
}
.service-card-icon {
    font-size: 22px;
    color: #B50C09;
    width: 32px;
    text-align: center;
    flex-shrink: 0;
}
.service-card-label {
    font-weight: 600;
    font-size: 13px;
    line-height: 1.3;
}
.service-card-input:checked + .service-card-body .service-card-label {
    color: #B50C09;
}
</style>
@endpush