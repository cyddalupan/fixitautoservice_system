@extends('layouts.app')

@section('title', 'Service Request Details')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Service Request #{{ $serviceRequest->id }}</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('portal.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('portal.service-requests.index') }}">Service Requests</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Request #{{ $serviceRequest->id }}</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('portal.service-requests.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Requests
            </a>
            @if($serviceRequest->status === 'pending' || $serviceRequest->status === 'confirmed')
            <a href="{{ route('portal.service-requests.edit', $serviceRequest->id) }}" class="btn btn-outline-primary">
                <i class="bi bi-pencil me-1"></i> Edit Request
            </a>
            @endif
            @if($serviceRequest->status === 'pending')
            <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#cancelRequestModal">
                <i class="bi bi-x-circle me-1"></i> Cancel Request
            </button>
            @endif
        </div>
    </div>

    <!-- Status Alert -->
    <div class="alert alert-{{ $serviceRequest->status === 'completed' ? 'success' : ($serviceRequest->status === 'in_progress' ? 'info' : ($serviceRequest->status === 'confirmed' ? 'primary' : ($serviceRequest->status === 'pending' ? 'warning' : 'danger'))) }} mb-4">
        <div class="d-flex align-items-center">
            <div class="flex-shrink-0">
                <i class="bi bi-{{ $serviceRequest->status === 'completed' ? 'check-circle' : ($serviceRequest->status === 'in_progress' ? 'gear' : ($serviceRequest->status === 'confirmed' ? 'calendar-check' : ($serviceRequest->status === 'pending' ? 'clock' : 'x-circle'))) }} fs-4"></i>
            </div>
            <div class="flex-grow-1 ms-3">
                <h5 class="alert-heading mb-1">
                    @if($serviceRequest->status === 'completed')
                    Service Completed
                    @elseif($serviceRequest->status === 'in_progress')
                    Service In Progress
                    @elseif($serviceRequest->status === 'confirmed')
                    Service Confirmed
                    @elseif($serviceRequest->status === 'pending')
                    Awaiting Confirmation
                    @else
                    Service Cancelled
                    @endif
                </h5>
                <p class="mb-0">
                    @if($serviceRequest->status === 'completed')
                    Your service was completed on {{ $serviceRequest->completed_at->format('M j, Y') }}. Thank you for choosing us!
                    @elseif($serviceRequest->status === 'in_progress')
                    Your vehicle is currently being serviced. Estimated completion: {{ $serviceRequest->estimated_completion ? $serviceRequest->estimated_completion->format('M j, Y g:i A') : 'To be determined' }}
                    @elseif($serviceRequest->status === 'confirmed')
                    Your service is scheduled for {{ $serviceRequest->scheduled_date->format('M j, Y') }} at {{ $serviceRequest->scheduled_time }}.
                    @elseif($serviceRequest->status === 'pending')
                    We've received your request and will contact you shortly to confirm details.
                    @else
                    This service request was cancelled on {{ $serviceRequest->cancelled_at->format('M j, Y') }}.
                    @endif
                </p>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Service Details Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Service Details</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">Service Type</label>
                            <div class="fw-medium">{{ $serviceRequest->service_type }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">Priority</label>
                            <div>
                                <span class="badge bg-{{ $serviceRequest->priority === 'high' ? 'danger' : ($serviceRequest->priority === 'medium' ? 'warning text-dark' : 'secondary') }}">
                                    {{ ucfirst($serviceRequest->priority) }} Priority
                                </span>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">Requested Date</label>
                            <div class="fw-medium">{{ $serviceRequest->created_at->format('M j, Y g:i A') }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">Scheduled Date</label>
                            <div class="fw-medium">
                                @if($serviceRequest->scheduled_date)
                                {{ $serviceRequest->scheduled_date->format('M j, Y') }} at {{ $serviceRequest->scheduled_time }}
                                @else
                                <span class="text-muted">Not scheduled yet</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label text-muted">Service Description</label>
                            <div class="fw-medium">{{ $serviceRequest->description }}</div>
                        </div>
                        @if($serviceRequest->additional_notes)
                        <div class="col-12 mb-3">
                            <label class="form-label text-muted">Additional Notes</label>
                            <div class="fw-medium">{{ $serviceRequest->additional_notes }}</div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Vehicle Information Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Vehicle Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">Vehicle</label>
                            <div class="fw-medium">
                                {{ $serviceRequest->vehicle->year }} {{ $serviceRequest->vehicle->make }} {{ $serviceRequest->vehicle->model }}
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">VIN</label>
                            <div class="fw-medium">{{ $serviceRequest->vehicle->vin }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">License Plate</label>
                            <div class="fw-medium">{{ $serviceRequest->vehicle->license_plate }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">Mileage</label>
                            <div class="fw-medium">
                                @if($serviceRequest->current_mileage)
                                {{ number_format($serviceRequest->current_mileage) }} miles
                                @else
                                <span class="text-muted">Not provided</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-12">
                            <a href="{{ route('portal.vehicles.show', $serviceRequest->vehicle->id) }}" class="btn btn-outline-primary">
                                <i class="bi bi-car-front me-1"></i> View Vehicle Details
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Updates & Communication Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Updates & Communication</h5>
                        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addUpdateModal">
                            <i class="bi bi-plus-circle me-1"></i> Add Update
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    @if($serviceRequest->updates && count($serviceRequest->updates) > 0)
                    <div class="timeline">
                        @foreach($serviceRequest->updates->sortByDesc('created_at') as $update)
                        <div class="timeline-item {{ $update->type === 'technician' ? 'timeline-item-technician' : ($update->type === 'system' ? 'timeline-item-system' : 'timeline-item-customer') }}">
                            <div class="timeline-marker">
                                <i class="bi bi-{{ $update->type === 'technician' ? 'person-gear' : ($update->type === 'system' ? 'gear' : 'person') }}"></i>
                            </div>
                            <div class="timeline-content">
                                <div class="timeline-header">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0">
                                            @if($update->type === 'technician')
                                            Technician Update
                                            @elseif($update->type === 'system')
                                            System Update
                                            @else
                                            Your Update
                                            @endif
                                        </h6>
                                        <small class="text-muted">{{ $update->created_at->format('M j, Y g:i A') }}</small>
                                    </div>
                                </div>
                                <div class="timeline-body">
                                    <p class="mb-2">{{ $update->message }}</p>
                                    @if($update->attachments && count($update->attachments) > 0)
                                    <div class="mt-2">
                                        <small class="text-muted d-block mb-1">Attachments:</small>
                                        <div class="d-flex flex-wrap gap-2">
                                            @foreach($update->attachments as $attachment)
                                            <a href="{{ $attachment['url'] }}" class="btn btn-sm btn-outline-secondary" target="_blank">
                                                <i class="bi bi-paperclip me-1"></i> {{ $attachment['name'] }}
                                            </a>
                                            @endforeach
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="text-center py-4">
                        <i class="bi bi-chat-text display-1 text-muted mb-3"></i>
                        <h5 class="text-muted mb-2">No updates yet</h5>
                        <p class="text-muted mb-0">Updates will appear here once service progresses</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Photos Card -->
            @if($serviceRequest->photos && count($serviceRequest->photos) > 0)
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Photos ({{ count($serviceRequest->photos) }})</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @foreach($serviceRequest->photos as $photo)
                        <div class="col-md-4 col-6">
                            <a href="{{ $photo['url'] }}" data-fancybox="service-photos" data-caption="{{ $photo['caption'] ?? 'Service Photo' }}">
                                <img src="{{ $photo['thumbnail_url'] ?? $photo['url'] }}" 
                                     alt="{{ $photo['caption'] ?? 'Service Photo' }}" 
                                     class="img-fluid rounded border" style="height: 150px; object-fit: cover;">
                            </a>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>

        <div class="col-lg-4">
            <!-- Quick Actions Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @if($serviceRequest->status === 'completed')
                        <a href="{{ route('portal.reviews.create', ['service_request_id' => $serviceRequest->id]) }}" class="btn btn-success">
                            <i class="bi bi-star me-1"></i> Leave a Review
                        </a>
                        @endif
                        
                        <a href="{{ route('portal.service-requests.print', $serviceRequest->id) }}" class="btn btn-outline-primary" target="_blank">
                            <i class="bi bi-printer me-1"></i> Print Request
                        </a>
                        
                        <a href="{{ route('portal.service-requests.download', $serviceRequest->id) }}" class="btn btn-outline-secondary">
                            <i class="bi bi-download me-1"></i> Download PDF
                        </a>
                        
                        @if($serviceRequest->status === 'pending' || $serviceRequest->status === 'confirmed')
                        <a href="{{ route('portal.service-requests.edit', $serviceRequest->id) }}" class="btn btn-outline-warning">
                            <i class="bi bi-pencil me-1"></i> Edit Request
                        </a>
                        @endif
                        
                        @if($serviceRequest->status === 'pending')
                        <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#cancelRequestModal">
                            <i class="bi bi-x-circle me-1"></i> Cancel Request
                        </button>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Technician Card -->
            @if($serviceRequest->technician)
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Assigned Technician</h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <div class="avatar avatar-xl mb-3">
                            <img src="{{ $serviceRequest->technician->avatar_url ?? asset('images/default-avatar.png') }}" 
                                 alt="{{ $serviceRequest->technician->name }}" class="rounded-circle">
                        </div>
                        <h5 class="mb-1">{{ $serviceRequest->technician->name }}</h5>
                        <p class="text-muted small mb-2">{{ $serviceRequest->technician->title ?? 'Technician' }}</p>
                        <div class="d-flex justify-content-center gap-2 mb-3">
                            @if($serviceRequest->technician->rating)
                            <div class="text-warning">
                                <i class="bi bi-star-fill"></i>
                                <span class="fw-medium">{{ number_format($serviceRequest->technician->rating, 1) }}</span>
                            </div>
                            @endif
                            @if($serviceRequest->technician->experience_years)
                            <div class="text-muted">
                                <i class="bi bi-award"></i>
                                <span>{{ $serviceRequest->technician->experience_years }} years</span>
                            </div>
                            @endif
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label text-muted">Specialties</label>
                        <div class="d-flex flex-wrap gap-1">
                            @foreach($serviceRequest->technician->specialties as $specialty)
                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25">
                                {{ $specialty }}
                            </span>
                            @endforeach
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label text-muted">Contact</label>
                        <div class="small">
                            <div class="d-flex align-items-center mb-1">
                                <i class="bi bi-telephone me-2 text-muted"></i>
                                <span>{{ $serviceRequest->technician->phone ?? 'N/A' }}</span>
                            </div>
                            <div class="d-flex align-items-center">
                                <i class="bi bi-envelope me-2 text-muted"></i>
                                <span>{{ $serviceRequest->technician->email ?? 'N/A' }}</span>
                            </div>
                        </div>
                    </div>
                    
                    <button type="button" class="btn btn-outline-primary w-100" data-bs-toggle="modal" data-bs-target="#contactTechnicianModal">
                        <i class="bi bi-chat-text me-1"></i> Send Message
                    </button>
                </div>
            </div>
            @endif

            <!-- Estimated Cost Card -->
            @if($serviceRequest->estimated_cost || $serviceRequest->actual_cost)
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Cost Information</h5>
                </div>
                <div class="card-body">
                    @if($serviceRequest->actual_cost)
                    <div class="mb-3">
                        <label class="form-label text-muted">Actual Cost</label>
                        <div class="display-6 fw-bold text-success">${{ number_format($serviceRequest->actual_cost, 2) }}</div>
                    </div>
                    @endif
                    
                    @if($serviceRequest->estimated_cost)
                    <div class="mb-3">
                        <label class="form-label text-muted">Estimated Cost</label>
                        <div class="h4 fw-bold">${{ number_format($serviceRequest->estimated_cost, 2) }}</div>
                        @if($serviceRequest->actual_cost)
                        <div class="small text-muted">
                            @if($serviceRequest->actual_cost > $serviceRequest->estimated_cost)
                            <span class="text-danger">+${{ number_format($serviceRequest->actual_cost - $serviceRequest->estimated_cost, 2) }} over estimate</span>
                            @elseif($serviceRequest->actual_cost < $serviceRequest->estimated_cost)
                            <span class="text-success">-${{ number_format($serviceRequest->estimated_cost - $serviceRequest->actual_cost, 2) }} under estimate</span>
                            @else
                            <span class="text-success">Exactly as estimated</span>
                            @endif
                        </div>
                        @endif
                    </div>
                    @endif
                    
                    @if($serviceRequest->payment_status)
                    <div class="mb-3">
                        <label class="form-label text-muted">Payment Status</label>
                        <div>
                            <span class="badge bg-{{ $serviceRequest->payment_status === 'paid' ? 'success' : ($serviceRequest->payment_status === 'partial' ? 'warning text-dark' : 'danger') }}">
                                {{ ucfirst($serviceRequest->payment_status) }}
                            </span>
                        </div>
                    </div>
                    @endif
                    
                    @if($serviceRequest->actual_cost && $serviceRequest->payment_status !== 'paid')
                    <a href="{{ route('portal.billing.show', $serviceRequest->invoice_id ?? '') }}" class="btn btn-primary w-100">
                        <i class="bi bi-credit-card me-1"></i> Make Payment
                    </a>
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Cancel Request Modal -->
<div class="modal fade" id="cancelRequestModal" tabindex="-1" aria-labelledby="cancelRequestModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cancelRequestModalLabel">Cancel Service Request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to cancel this service request?</p>
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>Note:</strong> Cancellation may be subject to fees depending on how close to the scheduled date.
                </div>
                <form id="cancelRequestForm" action="{{ route('portal.service-requests.cancel', $serviceRequest->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label for="cancellation_reason" class="form-label">Reason for Cancellation</label>
                        <textarea class="form-control" id="cancellation_reason" name="cancellation_reason" rows="3" required></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Keep Request</button>
                <button type="submit" form="cancelRequestForm" class="btn btn-danger">Cancel Request</button>
            </div>
        </div>
    </div>
</div>

<!-- Add Update Modal -->
<div class="modal fade" id="addUpdateModal" tabindex="-1" aria-labelledby="addUpdateModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addUpdateModalLabel">Add Update</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addUpdateForm" action="{{ route('portal.service-requests.add-update', $serviceRequest->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label for="update_message" class="form-label">Message</label>
                        <textarea class="form-control" id="update_message" name="message" rows="4" required placeholder="Add your update or question here..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="update_attachments" class="form-label">Attachments (Optional)</label>
                        <input type="file" class="form-control" id="update_attachments" name="attachments[]" multiple>
                        <div class="form-text">You can upload photos or documents related to your update.</div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="addUpdateForm" class="btn btn-primary">Send Update</button>
            </div>
        </div>
    </div>
</div>

<!-- Contact Technician Modal -->
@if($serviceRequest->technician)
<div class="modal fade" id="contactTechnicianModal" tabindex="-1" aria-labelledby="contactTechnicianModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="contactTechnicianModalLabel">Message Technician</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="contactTechnicianForm" action="{{ route('portal.service-requests.contact-technician', $serviceRequest->id) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="technician_message" class="form-label">Message to {{ $serviceRequest->technician->name }}</label>
                        <textarea class="form-control" id="technician_message" name="message" rows="4" required placeholder="Type your message to the technician..."></textarea>
                    </div>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        Your message will be sent to the technician and appear in the updates timeline.
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="contactTechnicianForm" class="btn btn-primary">Send Message</button>
            </div>
        </div>
    </div>
</div>
@endif

@section('styles')
<style>
    .timeline {
        position: relative;
        padding-left: 30px;
    }
    
    .timeline::before {
        content: '';
        position: absolute;
        left: 15px;
        top: 0;
        bottom: 0;
        width: 2px;
        background-color: #e9ecef;
    }
    
    .timeline-item {
        position: relative;
        margin-bottom: 20px;
    }
    
    .timeline-marker {
        position: absolute;
        left: -30px;
        top: 0;
        width: 30px;
        height: 30px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 14px;
    }
    
    .timeline-item-technician .timeline-marker {
        background-color: #0d6efd;
    }
    
    .timeline-item-system .timeline-marker {
        background-color: #6c757d;
    }
    
    .timeline-item-customer .timeline-marker {
        background-color: #198754;
    }
    
    .timeline-content {
        background: white;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        padding: 15px;
    }
    
    .timeline-header {
        border-bottom: 1px solid #e9ecef;
        padding-bottom: 10px;
        margin-bottom: 10px;
    }
    
    .avatar {
        width: 80px;
        height: 80px;
        overflow: hidden;
    }
    
    .avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
</style>
@endsection

@section('scripts')
<script>
    // Initialize fancybox for photo gallery
    if (typeof Fancybox !== 'undefined') {
        Fancybox.bind("[data-fancybox]", {
            // Options
        });
    }
    
    // Form validation
    document.addEventListener('DOMContentLoaded', function() {
        const cancelForm = document.getElementById('cancelRequestForm');
        if (cancelForm) {
            cancelForm.addEventListener('submit', function(e) {
                const reason = document.getElementById('cancellation_reason').value.trim();
                if (!reason) {
                    e.preventDefault();
                    alert('Please provide a reason for cancellation.');
                }
            });
        }
        
        const updateForm = document.getElementById('addUpdateForm');
        if (updateForm) {
            updateForm.addEventListener('submit', function(e) {
                const message = document.getElementById('update_message').value.trim();
                if (!message) {
                    e.preventDefault();
                    alert('Please enter a message for your update.');
                }
            });
        }
        
        const contactForm = document.getElementById('contactTechnicianForm');
        if (contactForm) {
            contactForm.addEventListener('submit', function(e) {
                const message = document.getElementById('technician_message').value.trim();
                if (!message) {
                    e.preventDefault();
                    alert('Please enter a message for the technician.');
                }
            });
        }
    });
</script>
@endsection