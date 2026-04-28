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
                        <small class="text-muted ms-2">Lead</small>
                    </h5>
                    <div class="btn-group">
                        <a href="{{ route('quotations.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                        <a href="{{ route('quotations.edit', $quotation->id) }}" class="btn btn-warning">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    <div class="row">
                        <!-- Main Content -->
                        <div class="col-md-8">
                            <!-- Customer Information -->
                            <div class="card mb-4">
                                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0"><i class="fas fa-user me-2"></i>Customer Information</h6>
                                    @if($quotation->customer)
                                        <a href="{{ route('customers.show', $quotation->customer_id) }}" class="btn btn-sm btn-outline-success">
                                            <i class="fas fa-external-link-alt"></i> View Customer Profile
                                        </a>
                                    @endif
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
                                                <a href="mailto:{{ $quotation->email }}" class="text-decoration-none">{{ $quotation->email }}</a>
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label text-muted small mb-1">Phone Number</label>
                                            <div class="fw-semibold">
                                                <a href="tel:{{ $quotation->phone }}" class="text-decoration-none">{{ $quotation->phone }}</a>
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label text-muted small mb-1">Submitted On</label>
                                            <div class="fw-semibold">{{ $quotation->created_at->format('F d, Y h:i A') }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Vehicle Information -->
                            <div class="card mb-4">
                                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0"><i class="fas fa-car me-2"></i>Vehicle Information</h6>
                                    @if($quotation->vehicle)
                                        <span class="badge bg-info">Vehicle #{{ $quotation->vehicle->id }}</span>
                                    @endif
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
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label text-muted small mb-1">VIN / Chassis</label>
                                            <div class="fw-semibold">
                                                @if($quotation->vin_number)
                                                    <code>{{ $quotation->vin_number }}</code>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label text-muted small mb-1">Color</label>
                                            <div class="fw-semibold">{{ $quotation->color ?? '—' }}</div>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label text-muted small mb-1">Transmission</label>
                                            <div class="fw-semibold">{{ $quotation->transmission ?? '—' }}</div>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label text-muted small mb-1">Engine Type</label>
                                            <div class="fw-semibold">{{ $quotation->engine_type ?? '—' }}</div>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label text-muted small mb-1">Mileage</label>
                                            <div class="fw-semibold">{{ $quotation->mileage ? number_format($quotation->mileage) . ' km' : '—' }}</div>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label text-muted small mb-1">Preferred Appointment</label>
                                            <div class="fw-semibold">
                                                @if($quotation->preferred_date)
                                                    {{ \Carbon\Carbon::parse($quotation->preferred_date)->format('M d, Y') }}
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
                            
                            <!-- Service Details -->
                            <div class="card mb-4">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="fas fa-tools me-2"></i>Service Details — Customer Concern</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label text-muted small mb-1">Service Type</label>
                                        <div>
                                            @if($quotation->service_type && count($quotation->service_type) > 0)
                                                <div class="d-flex flex-wrap gap-1">
                                                    @foreach($quotation->service_type as $service)
                                                        <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-3 py-2">{{ $service }}</span>
                                                    @endforeach
                                                </div>
                                            @else
                                                <span class="text-muted">Not specified</span>
                                            @endif
                                        </div>
                                    </div>

                                    @if($quotation->parts_preference)
                                        <div class="mb-3">
                                            <label class="form-label text-muted small mb-1">Parts Preference</label>
                                            <div class="border rounded p-3 bg-light">{{ $quotation->parts_preference }}</div>
                                        </div>
                                    @endif

                                    @if($quotation->budget_min || $quotation->budget_max)
                                        <div class="mb-3">
                                            <label class="form-label text-muted small mb-1">Budget Range</label>
                                            <div class="border rounded p-3 bg-light">
                                                @if($quotation->budget_min && $quotation->budget_max)
                                                    ₱{{ number_format($quotation->budget_min, 2) }} – ₱{{ number_format($quotation->budget_max, 2) }}
                                                @elseif($quotation->budget_min)
                                                    Min: ₱{{ number_format($quotation->budget_min, 2) }}
                                                @elseif($quotation->budget_max)
                                                    Max: ₱{{ number_format($quotation->budget_max, 2) }}
                                                @endif
                                            </div>
                                        </div>
                                    @endif

                                    <div class="mb-3">
                                        <label class="form-label text-muted small mb-1">Customer Concern / Service Description</label>
                                        <div class="alert alert-warning border-start border-warning border-4 mb-0">
                                            <strong>Initial Complaint:</strong><br>
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

                            <!-- Consent Footer -->
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
                                            @endif
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label text-muted small mb-1">Timeline</label>
                                        <small class="text-muted d-block">Submitted: {{ $quotation->created_at->format('M d, Y h:i A') }}</small>
                                        <small class="text-muted d-block">Updated: {{ $quotation->updated_at->format('M d, Y h:i A') }}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Right Sidebar -->
                        <div class="col-md-4">
                            <!-- Status Card -->
                            <div class="card mb-4">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="fas fa-sync-alt me-2"></i>Lead Pipeline</h6>
                                </div>
                                <div class="card-body">
                                    <!-- Status Timeline -->
                                    @php
                                        $pipelineSteps = [
                                            'new_lead'              => ['label' => 'New Lead',              'icon' => 'fa-star',                   'color' => 'warning'],
                                            'contacted'             => ['label' => 'Contacted',             'icon' => 'fa-phone',                  'color' => 'info'],
                                            'converted_to_customer' => ['label' => 'Converted to Customer', 'icon' => 'fa-user-check',            'color' => 'success'],
                                            'appointment_booked'    => ['label' => 'Appointment Booked',    'icon' => 'fa-calendar-check',        'color' => 'primary'],
                                            'won'                   => ['label' => 'Won',                   'icon' => 'fa-trophy',                'color' => 'success'],
                                            'lost'                  => ['label' => 'Lost',                  'icon' => 'fa-times-circle',          'color' => 'danger'],
                                            'archived'              => ['label' => 'Archived',              'icon' => 'fa-archive',               'color' => 'secondary'],
                                        ];
                                        $currentStatus = $quotation->status;
                                        $statusSequence = ['new_lead', 'contacted', 'converted_to_customer', 'appointment_booked', 'won', 'lost', 'archived'];
                                        $currentIdx = array_search($currentStatus, $statusSequence);
                                    @endphp
                                    
                                    <div class="mb-3">
                                        @foreach($pipelineSteps as $key => $step)
                                            @php
                                                $stepIdx = array_search($key, $statusSequence);
                                                $isDone = $stepIdx <= $currentIdx && !in_array($currentStatus, ['lost', 'archived']);
                                                $isCurrent = $key === $currentStatus;
                                            @endphp
                                            <div class="d-flex align-items-center mb-2 {{ $stepIdx > $currentIdx ? 'opacity-25' : '' }}">
                                                <div style="width: 24px; text-align: center;">
                                                    <i class="fas {{ $step['icon'] }} text-{{ $step['color'] }}"></i>
                                                </div>
                                                <div class="ms-2" style="flex:1;">
                                                    <div class="d-flex align-items-center">
                                                        <small class="{{ $isCurrent ? 'fw-bold' : '' }}">{{ $step['label'] }}</small>
                                                        @if($isCurrent)
                                                            <span class="ms-2 badge bg-{{ $step['color'] }}">Current</span>
                                                        @endif
                                                    </div>
                                                    @if(!$loop->last)
                                                        <div style="width: 2px; height: 10px; background: #ccc; margin: 2px 0 2px 11px;"></div>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>

                                    <form action="{{ route('quotations.update-status', $quotation->id) }}" method="POST" class="mt-3">
                                        @csrf @method('PATCH')
                                        <label class="form-label text-muted small mb-1">Change Status</label>
                                        <div class="input-group input-group-sm">
                                            <select name="status" class="form-select">
                                                @foreach(['new_lead', 'contacted', 'converted_to_customer', 'appointment_booked', 'won', 'lost', 'archived'] as $s)
                                                    <option value="{{ $s }}" {{ $quotation->status === $s ? 'selected' : '' }}>
                                                        {{ $pipelineSteps[$s]['label'] }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <button type="submit" class="btn btn-primary">Update</button>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <!-- CRM Quick Actions -->
                            <div class="card mb-4">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="fas fa-bolt me-2"></i>CRM Actions</h6>
                                </div>
                                <div class="card-body">
                                    <div class="d-grid gap-2">
                                        @if(!$quotation->customer)
                                            <form action="{{ route('quotations.convert-to-customer', $quotation->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="btn btn-success w-100">
                                                    <i class="fas fa-user-plus me-2"></i> Convert to Customer
                                                </button>
                                            </form>
                                        @else
                                            <a href="{{ route('customers.show', $quotation->customer_id) }}" class="btn btn-outline-success w-100">
                                                <i class="fas fa-user me-2"></i> View Customer Profile
                                            </a>
                                            <a href="{{ route('appointments.create', ['customer_id' => $quotation->customer_id, 'quotation_id' => $quotation->id]) }}" class="btn btn-outline-primary w-100">
                                                <i class="fas fa-calendar-plus me-2"></i> Book Appointment
                                            </a>
                                            <a href="{{ route('estimates.create', ['customer_id' => $quotation->customer_id, 'quotation_id' => $quotation->id]) }}" class="btn btn-outline-info w-100">
                                                <i class="fas fa-file-invoice-dollar me-2"></i> Create Estimate
                                            </a>
                                        @endif
                                    </div>
                                    <hr>
                                    <div class="d-grid gap-2">
                                        @if($quotation->status === 'new_lead')
                                            <form action="{{ route('quotations.update-status', $quotation->id) }}" method="POST">
                                                @csrf @method('PATCH')
                                                <input type="hidden" name="status" value="contacted">
                                                <button type="submit" class="btn btn-outline-info w-100">
                                                    <i class="fas fa-phone me-2"></i> Mark Contacted
                                                </button>
                                            </form>
                                        @endif
                                        @if(!in_array($quotation->status, ['archived', 'lost']))
                                            <form action="{{ route('quotations.update-status', $quotation->id) }}" method="POST">
                                                @csrf @method('PATCH')
                                                <input type="hidden" name="status" value="archived">
                                                <button type="submit" class="btn btn-outline-secondary w-100" onclick="return confirm('Archive this lead?')">
                                                    <i class="fas fa-archive me-2"></i> Archive Lead
                                                </button>
                                            </form>
                                        @endif
                                        <a href="mailto:{{ $quotation->email }}?subject=Regarding%20Your%20Quote%20Request%20%23{{ str_pad($quotation->id, 5, '0', STR_PAD_LEFT) }}&body=Dear%20{{ urlencode($quotation->name) }}%2C%0A%0AThank%20you%20for%20your%20quote%20request.%0A%0ABest%20regards%2C%0AFixit%20Auto%20Services%20Team"
                                           class="btn btn-outline-primary w-100" target="_blank">
                                            <i class="fas fa-envelope me-2"></i> Send Email
                                        </a>
                                        <a href="tel:{{ $quotation->phone }}" class="btn btn-outline-info w-100">
                                            <i class="fas fa-phone me-2"></i> Call Customer
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Admin Notes -->
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="fas fa-sticky-note me-2"></i>Admin Notes</h6>
                                </div>
                                <div class="card-body">
                                    @if($quotation->admin_notes)
                                        <div class="border rounded p-3 bg-light mb-3">{!! nl2br(e($quotation->admin_notes)) !!}</div>
                                    @else
                                        <div class="text-center text-muted py-3">
                                            <i class="fas fa-sticky-note fa-2x mb-2"></i>
                                            <p>No admin notes yet.</p>
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
