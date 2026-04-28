@extends('layouts.app')

@section('title', 'Appointment Details')

@section('content')
<div class="container-fluid">
    <!-- Progress Bar at the Top -->
    @php
        $progress = \App\Models\ServiceProgress::where('appointment_id', $appointment->id)->first();
    @endphp
    
    @if($progress)
        @include('components.service-progress-bar', [
    'progress' => $progress,
    'currentStage' => 'appointment'
])
    @endif
    
    <!-- Appointment Details Card -->
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">
                <i class="fas fa-calendar-alt me-2"></i>Appointment Details
                <span class="badge bg-{{ $appointment->status_badge }} ms-2">{{ ucfirst($appointment->appointment_status) }}</span>
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                <!-- Left Column: Appointment Info -->
                <div class="col-md-6">
                    <div class="mb-4">
                        <h6 class="text-muted mb-3">Appointment Information</h6>
                        <div class="row">
                            <div class="col-6">
                                <small class="text-muted">Appointment Number</small>
                                <div class="fw-bold">{{ $appointment->appointment_number }}</div>
                            </div>
                            <div class="col-6">
                                <small class="text-muted">Date & Time</small>
                                <div class="fw-bold">
                                    {{ $appointment->appointment_date->format('M d, Y') }} 
                                    at {{ $appointment->appointment_time }}
                                </div>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-6">
                                <small class="text-muted">Type</small>
                                <div class="fw-bold">{{ ucfirst(str_replace('_', ' ', $appointment->appointment_type)) }}</div>
                            </div>
                            <div class="col-6">
                                <small class="text-muted">Priority</small>
                                <div class="fw-bold">
                                    <span class="badge bg-{{ $appointment->priority === 'emergency' ? 'danger' : ($appointment->priority === 'high' ? 'warning' : 'info') }}">
                                        {{ ucfirst($appointment->priority) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-12">
                                <small class="text-muted">Service Request</small>
                                <div class="fw-bold">{{ $appointment->service_request ?? 'No specific request' }}</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Customer Information -->
                    <div class="mb-4">
                        <h6 class="text-muted mb-3">Customer Information</h6>
                        <div class="row">
                            <div class="col-6">
                                <small class="text-muted">Customer</small>
                                <div class="fw-bold">
                                    {{ $appointment->customer->full_name }}
                                </div>
                            </div>
                            <div class="col-6">
                                <small class="text-muted">Phone</small>
                                <div class="fw-bold">{{ $appointment->customer->phone_number }}</div>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-12">
                                <small class="text-muted">Email</small>
                                <div class="fw-bold">{{ $appointment->customer->email }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Right Column: Vehicle & Staff -->
                <div class="col-md-6">
                    <!-- Vehicle Information -->
                    <div class="mb-4">
                        <h6 class="text-muted mb-3">Vehicle Information</h6>
                        <div class="row">
                            <div class="col-12">
                                <small class="text-muted">Vehicle Description</small>
                                <div class="fw-bold">
                                    {{ $appointment->vehicle_description }}
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Staff Assignment -->
                    <div class="mb-4">
                        <h6 class="text-muted mb-3">Staff Assignment</h6>
                        <div class="row">
                            <div class="col-6">
                                <small class="text-muted">Service Advisor</small>
                                <div class="fw-bold">
                                    @if($appointment->advisor)
                                        {{ $appointment->advisor->name }}
                                    @else
                                        <span class="text-muted">Not assigned</span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-6">
                                <small class="text-muted">Technician</small>
                                <div class="fw-bold">
                                    @if($appointment->technician)
                                        {{ $appointment->technician->name }}
                                    @else
                                        <span class="text-muted">Not assigned</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-6">
                                <small class="text-muted">Bay Number</small>
                                <div class="fw-bold">{{ $appointment->bay_number ?? 'Not assigned' }}</div>
                            </div>
                            <div class="col-6">
                                <small class="text-muted">Estimated Duration</small>
                                <div class="fw-bold">{{ $appointment->estimated_duration ?? 'N/A' }} hours</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Additional Information -->
                    <div class="mb-4">
                        <h6 class="text-muted mb-3">Additional Information</h6>
                        <div class="row">
                            <div class="col-6">
                                <small class="text-muted">Requires Deposit</small>
                                <div class="fw-bold">
                                    @if($appointment->requires_deposit)
                                        <span class="badge bg-success">Yes - ₱{{ number_format($appointment->deposit_amount, 2) }}</span>
                                    @else
                                        <span class="badge bg-secondary">No</span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-6">
                                <small class="text-muted">Waitlist</small>
                                <div class="fw-bold">
                                    @if($appointment->is_waitlist)
                                        <span class="badge bg-warning">Yes</span>
                                    @else
                                        <span class="badge bg-success">No</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-12">
                                <small class="text-muted">Customer Notes</small>
                                <div class="fw-bold">{{ $appointment->customer_notes ?? 'No notes' }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="d-flex justify-content-between">
                        <div>
                            <a href="{{ route('appointments.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Back to Appointments
                            </a>
                            
                            <!-- Create Estimate Button (if no estimate exists) -->
                            @if(!$appointment->estimate)
                                <a href="{{ route('estimates.create', ['appointment_id' => $appointment->id]) }}" class="btn btn-success ms-2">
                                    <i class="fas fa-file-invoice-dollar me-1"></i> Create Estimate
                                </a>
                            @else
                                <a href="{{ route('estimates.show', $appointment->estimate) }}" class="btn btn-outline-success ms-2">
                                    <i class="fas fa-eye me-1"></i> View Estimate
                                </a>
                            @endif
                            
                            <!-- Create Inspection Button -->
                            <a href="{{ route('inspections.create', ['appointment_id' => $appointment->id]) }}" class="btn btn-primary ms-2">
                                <i class="fas fa-car me-1"></i> Create Inspection
                            </a>
                        </div>
                        
                        <div>
                            <!-- Edit Button -->
                            <a href="{{ route('appointments.edit', $appointment) }}" class="btn btn-warning">
                                <i class="fas fa-edit me-1"></i> Edit
                            </a>
                            
                            <!-- Status Change Buttons -->
                            @if($appointment->appointment_status === 'scheduled')
                                <form action="{{ route('appointments.check-in', $appointment) }}" method="POST" class="d-inline ms-2">
                                    @csrf
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-check-circle me-1"></i> Check In
                                    </button>
                                </form>
                            @endif
                            
                            @if($appointment->appointment_status === 'checked_in')
                                <form action="{{ route('appointments.complete', $appointment) }}" method="POST" class="d-inline ms-2">
                                    @csrf
                                    <button type="submit" class="btn btn-info">
                                        <i class="fas fa-flag-checkered me-1"></i> Complete
                                    </button>
                                </form>
                            @endif
                            
                            <!-- Cancel Button -->
                            @if(!in_array($appointment->appointment_status, ['cancelled', 'completed']))
                                <button type="button" class="btn btn-danger ms-2" data-bs-toggle="modal" data-bs-target="#cancelModal">
                                    <i class="fas fa-times me-1"></i> Cancel
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Related Information Cards -->
    <div class="row mt-4">
        <!-- Related Estimates -->
        @if($appointment->estimate)
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h6 class="mb-0">
                            <i class="fas fa-file-invoice-dollar me-2"></i>Related Estimate
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">Estimate #{{ $appointment->estimate->estimate_number }}</h6>
                                <p class="mb-1 text-muted">Total: ₱{{ number_format($appointment->estimate->total_amount, 2) }}</p>
                                <p class="mb-0 text-muted">Status: 
                                    <span class="badge bg-{{ $appointment->estimate->status_badge }}">
                                        {{ ucfirst($appointment->estimate->estimate_status) }}
                                    </span>
                                </p>
                            </div>
                            <a href="{{ route('estimates.show', $appointment->estimate) }}" class="btn btn-outline-success btn-sm">
                                View Details
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endif
        
        <!-- Related Work Orders -->
        @if($appointment->workOrder)
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0">
                            <i class="fas fa-wrench me-2"></i>Related Work Order
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">Work Order #{{ $appointment->workOrder->work_order_number }}</h6>
                                <p class="mb-1 text-muted">Technician: {{ $appointment->workOrder->technician->name ?? 'Not assigned' }}</p>
                                <p class="mb-0 text-muted">Status: 
                                    <span class="badge bg-{{ $appointment->workOrder->status_badge }}">
                                        {{ ucfirst($appointment->workOrder->work_order_status) }}
                                    </span>
                                </p>
                            </div>
                            <a href="{{ route('work-orders.show', $appointment->workOrder) }}" class="btn btn-outline-primary btn-sm">
                                View Details
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
    
    <!-- Customer Appointment History -->
    @if($customerAppointments->count() > 0)
        <div class="card mt-4">
            <div class="card-header bg-info text-white">
                <h6 class="mb-0">
                    <i class="fas fa-history me-2"></i>Customer's Recent Appointments
                </h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Type</th>
                                <th>Vehicle</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($customerAppointments as $pastAppointment)
                                <tr>
                                    <td>{{ $pastAppointment->appointment_date->format('M d, Y') }}</td>
                                    <td>{{ $pastAppointment->appointment_time }}</td>
                                    <td>{{ ucfirst(str_replace('_', ' ', $pastAppointment->appointment_type)) }}</td>
                                    <td>{{ $pastAppointment->vehicle_description ?? 'No vehicle specified' }}</td>
                                    <td>
                                        <span class="badge bg-{{ $pastAppointment->status_badge }}">
                                            {{ ucfirst($pastAppointment->appointment_status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('appointments.show', $pastAppointment) }}" class="btn btn-sm btn-outline-primary">
                                            View
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>

<!-- Cancel Appointment Modal -->
@if(!in_array($appointment->appointment_status, ['cancelled', 'completed']))
    <div class="modal fade" id="cancelModal" tabindex="-1" aria-labelledby="cancelModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="cancelModalLabel">Cancel Appointment</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('appointments.cancel', $appointment) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <div class="modal-body">
                        <p>Are you sure you want to cancel this appointment?</p>
                        <div class="mb-3">
                            <label for="cancellation_reason" class="form-label">Cancellation Reason</label>
                            <textarea class="form-control" id="cancellation_reason" name="cancellation_reason" rows="3" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-danger">Cancel Appointment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif

@endsection

@section('scripts')
<script>
    // Add any JavaScript needed for the appointment show page
    document.addEventListener('DOMContentLoaded', function() {
        // Example: Auto-refresh page every 30 seconds if appointment is in progress
        @if(in_array($appointment->appointment_status, ['checked_in', 'in_progress']))
            setTimeout(function() {
                location.reload();
            }, 30000);
        @endif
    });
</script>
@endsection