@extends('layouts.app')

@section('title', 'View Quotation')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-file-invoice-dollar text-primary"></i> Quotation #{{ str_pad($quotation->id, 5, '0', STR_PAD_LEFT) }}
                    </h5>
                    <div class="btn-group">
                        <a href="{{ route('quotations.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </a>
                        <a href="{{ route('quotations.edit', $quotation->id) }}" class="btn btn-warning">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="card mb-4">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="fas fa-user me-2"></i>Customer Information</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label text-muted small mb-1">Full Name</label>
                                            <div class="fw-semibold">{{ $quotation->name }}</div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label text-muted small mb-1">Email Address</label>
                                            <div class="fw-semibold">
                                                <a href="mailto:{{ $quotation->email }}" class="text-decoration-none">
                                                    {{ $quotation->email }}
                                                </a>
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label text-muted small mb-1">Phone Number</label>
                                            <div class="fw-semibold">
                                                <a href="tel:{{ $quotation->phone }}" class="text-decoration-none">
                                                    {{ $quotation->phone }}
                                                </a>
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label text-muted small mb-1">Submitted On</label>
                                            <div class="fw-semibold">
                                                {{ $quotation->created_at->format('F d, Y h:i A') }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="card mb-4">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="fas fa-car me-2"></i>Vehicle Information</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3 mb-3">
                                            <label class="form-label text-muted small mb-1">Make</label>
                                            <div class="fw-semibold">{{ $quotation->vehicle_make }}</div>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label class="form-label text-muted small mb-1">Model</label>
                                            <div class="fw-semibold">{{ $quotation->vehicle_model }}</div>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label class="form-label text-muted small mb-1">Year</label>
                                            <div class="fw-semibold">{{ $quotation->vehicle_year }}</div>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label class="form-label text-muted small mb-1">License Plate</label>
                                            <div class="fw-semibold">
                                                @if($quotation->license_plate)
                                                    <span class="badge bg-dark">{{ $quotation->license_plate }}</span>
                                                @else
                                                    <span class="text-muted">Not provided</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label text-muted small mb-1">VIN Number</label>
                                            <div class="fw-semibold">
                                                @if($quotation->vin_number)
                                                    <code>{{ $quotation->vin_number }}</code>
                                                @else
                                                    <span class="text-muted">Not provided</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label text-muted small mb-1">Preferred Appointment</label>
                                            <div class="fw-semibold">
                                                @if($quotation->preferred_date)
                                                    {{ \Carbon\Carbon::parse($quotation->preferred_date)->format('F d, Y') }}
                                                    @if($quotation->preferred_time)
                                                        <br><small class="text-muted">{{ $quotation->preferred_time }}</small>
                                                    @endif
                                                @else
                                                    <span class="text-muted">No preference</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="card mb-4">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="fas fa-tools me-2"></i>Service Details</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label text-muted small mb-1">Service Type</label>
                                        <div>
                                            <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-3 py-2">
                                                {{ $quotation->service_type }}
                                            </span>
                                        </div>
                                    </div>
                                    
                                    @if($quotation->service_checklist && is_array($quotation->service_checklist) && count($quotation->service_checklist) > 0)
                                        <div class="mb-3">
                                            <label class="form-label text-muted small mb-1">Service Checklist</label>
                                            <div class="border rounded p-3 bg-light">
                                                <ul class="mb-0">
                                                    @foreach($quotation->service_checklist as $service)
                                                        <li>{{ $service }}</li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        </div>
                                    @endif
                                    
                                    @if($quotation->parts_preference)
                                        <div class="mb-3">
                                            <label class="form-label text-muted small mb-1">Parts Preference</label>
                                            <div class="border rounded p-3 bg-light">
                                                {{ $quotation->parts_preference }}
                                            </div>
                                        </div>
                                    @endif
                                    
                                    @if($quotation->budget_min || $quotation->budget_max)
                                        <div class="mb-3">
                                            <label class="form-label text-muted small mb-1">Budget Range</label>
                                            <div class="border rounded p-3 bg-light">
                                                @if($quotation->budget_min && $quotation->budget_max)
                                                    ₱{{ number_format($quotation->budget_min, 2) }} - ₱{{ number_format($quotation->budget_max, 2) }}
                                                @elseif($quotation->budget_min)
                                                    Minimum: ₱{{ number_format($quotation->budget_min, 2) }}
                                                @elseif($quotation->budget_max)
                                                    Maximum: ₱{{ number_format($quotation->budget_max, 2) }}
                                                @endif
                                            </div>
                                        </div>
                                    @endif
                                    
                                    <div class="mb-3">
                                        <label class="form-label text-muted small mb-1">Service Description</label>
                                        <div class="border rounded p-3 bg-light">
                                            {!! nl2br(e($quotation->service_description)) !!}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            @if($quotation->photos && is_array($quotation->photos) && count($quotation->photos) > 0)
                                <div class="card mb-4">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0"><i class="fas fa-images me-2"></i>Uploaded Photos</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            @foreach($quotation->photos as $photo)
                                                <div class="col-md-4 mb-3">
                                                    <div class="border rounded p-2 text-center">
                                                        <i class="fas fa-image fa-3x text-muted mb-2"></i>
                                                        <div class="small text-truncate">{{ basename($photo) }}</div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endif
                            
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="fas fa-check-circle me-2"></i>Consent & Submission</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label text-muted small mb-1">Contact Consent</label>
                                        <div>
                                            @if($quotation->consent_contact)
                                                <span class="badge bg-success">Consent Given</span>
                                                <small class="text-muted d-block mt-1">Customer agreed to be contacted</small>
                                            @else
                                                <span class="badge bg-secondary">No Consent</span>
                                                <small class="text-muted d-block mt-1">Customer did not give consent</small>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label text-muted small mb-1">Submission Details</label>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <small class="text-muted">Submitted:</small>
                                                <div class="fw-semibold">{{ $quotation->created_at->format('F d, Y h:i A') }}</div>
                                            </div>
                                            <div class="col-md-6">
                                                <small class="text-muted">Last Updated:</small>
                                                <div class="fw-semibold">{{ $quotation->updated_at->format('F d, Y h:i A') }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="card mb-4">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="fas fa-cog me-2"></i>Status & Actions</h6>
                                </div>
                                <div class="card-body">
                                    @php
                                        $statusColors = [
                                            'pending' => 'warning',
                                            'reviewed' => 'info',
                                            'contacted' => 'primary',
                                            'converted' => 'success',
                                            'rejected' => 'danger'
                                        ];
                                        
                                        $statusLabels = [
                                            'pending' => 'Pending Review',
                                            'reviewed' => 'Reviewed',
                                            'contacted' => 'Customer Contacted',
                                            'converted' => 'Converted to Job',
                                            'rejected' => 'Rejected'
                                        ];
                                    @endphp
                                    
                                    <div class="mb-4">
                                        <label class="form-label text-muted small mb-1">Current Status</label>
                                        <div>
                                            <span class="badge bg-{{ $statusColors[$quotation->status] }} px-3 py-2 fs-6">
                                                {{ $statusLabels[$quotation->status] }}
                                            </span>
                                        </div>
                                    </div>
                                    <form action="{{ route("quotations.update-status", $quotation->id) }}" method="POST" class="mt-3">
                                        @csrf
                                        @method("PATCH")
                                        <label class="form-label text-muted small mb-1">Change Status</label>
                                        <div class="input-group">
                                            <select name="status" class="form-select" onchange="this.form.submit()">
                                                <option value="pending" {{ $quotation->status == "pending" ? "selected" : "" }}>Pending Review</option>
                                                <option value="reviewed" {{ $quotation->status == "reviewed" ? "selected" : "" }}>Reviewed</option>
                                                <option value="contacted" {{ $quotation->status == "contacted" ? "selected" : "" }}>Customer Contacted</option>
                                                <option value="converted" {{ $quotation->status == "converted" ? "selected" : "" }}>Converted to Job</option>
                                                <option value="rejected" {{ $quotation->status == "rejected" ? "selected" : "" }}>Rejected</option>
                                            </select>
                                            <button type="submit" class="btn btn-primary">Update</button>
                                        </div>
                                    </form>
                                    
                                    <div class="mb-4">
                                        <label class="form-label text-muted small mb-1">Quick Actions</label>
                                        <div class="d-grid gap-2">
                                            @if($quotation->status !== 'contacted')
                                                <a href="mailto:{{ $quotation->email }}?subject=Regarding%20Your%20Quote%20Request%20%23{{ str_pad($quotation->id, 5, '0', STR_PAD_LEFT) }}&body=Dear%20{{ urlencode($quotation->name) }}%2C%0A%0AThank%20you%20for%20your%20quote%20request%20for%20your%20{{ urlencode($quotation->vehicle_make . ' ' . $quotation->vehicle_model) }}.%20We%27re%20reviewing%20your%20request%20and%20will%20get%20back%20to%20you%20shortly.%0A%0ABest%20regards%2C%0AFixit%20Auto%20Services%20Team" 
                                                   class="btn btn-outline-primary" target="_blank">
                                                    <i class="fas fa-envelope me-2"></i> Send Email
                                                </a>
                                            @endif
                                            
                                            @if($quotation->status !== 'converted')
                                                <a href="{{ route('estimates.create') }}?quotation_id={{ $quotation->id }}" 
                                                   class="btn btn-outline-success">
                                                    <i class="fas fa-file-invoice-dollar me-2"></i> Create Estimate
                                                </a>
                                            @endif
                                            
                                            <a href="tel:{{ $quotation->phone }}" class="btn btn-outline-info">
                                                <i class="fas fa-phone me-2"></i> Call Customer
                                            </a>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label text-muted small mb-1">Last Updated</label>
                                        <div class="fw-semibold">
                                            {{ $quotation->updated_at->format('F d, Y h:i A') }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="fas fa-sticky-note me-2"></i>Admin Notes</h6>
                                </div>
                                <div class="card-body">
                                    @if($quotation->admin_notes)
                                        <div class="border rounded p-3 bg-light mb-3">
                                            {!! nl2br(e($quotation->admin_notes)) !!}
                                        </div>
                                    @else
                                        <div class="text-center text-muted py-3">
                                            <i class="fas fa-sticky-note fa-2x mb-2"></i>
                                            <p>No admin notes added yet.</p>
                                            <a href="{{ route('quotations.edit', $quotation->id) }}" class="btn btn-sm btn-outline-secondary">
                                                <i class="fas fa-edit me-1"></i> Add Notes
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection