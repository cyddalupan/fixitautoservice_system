@extends('layouts.app')

@section('title', 'Appointment Details - Fix-It Auto Services')

@section('content')
@include('partials.customer-process-assets')

<div class="container-fluid py-3">
    <!-- Status Header Bar -->
    <div class="status-header-bar d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center gap-3">
            <h4 class="mb-0 fw-bold">
                <i class="fas fa-calendar-alt me-2" style="color: var(--primary-color);"></i>Appointment Details
            </h4>
            <span class="badge badge-status-{{ $appointment->appointment_status }}">
                {{ ucfirst(str_replace('_', ' ', $appointment->appointment_status)) }}
            </span>
            <span class="badge bg-secondary">{{ $appointment->appointment_number }}</span>
        </div>
        <div class="d-flex gap-2 mt-2 mt-md-0">
            <a href="{{ route('appointments.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>
            <a href="{{ route('appointments.edit', $appointment) }}" class="btn btn-warning btn-sm">
                <i class="fas fa-edit me-1"></i> Edit
            </a>
            <button class="btn btn-outline-secondary btn-sm" onclick="window.print()">
                <i class="fas fa-print me-1"></i> Print
            </button>
        </div>
    </div>

    <div class="row g-4">
        <!-- Left Column -->
        <div class="col-lg-8">
            <!-- Customer Summary Card -->
            @if($appointment->customer)
            <div class="customer-summary-card">
                <div class="card-body">
                    <div class="d-flex align-items-start gap-3 flex-wrap">
                        <div class="customer-avatar">
                            {{ strtoupper(substr($appointment->customer->first_name, 0, 1)) }}{{ strtoupper(substr($appointment->customer->last_name, 0, 1)) }}
                        </div>
                        <div class="flex-grow-1" style="min-width: 200px;">
                            <div class="customer-name">{{ $appointment->customer->first_name }} {{ $appointment->customer->last_name }}</div>
                            <div class="customer-subtitle">
                                <i class="fas fa-phone-alt me-1"></i> {{ $appointment->customer->phone ?? 'No phone' }}
                                @if($appointment->customer->email)
                                    &nbsp;·&nbsp; <i class="fas fa-envelope me-1"></i>{{ $appointment->customer->email }}
                                @endif
                            </div>
                            @if($appointment->customer->address)
                                <div class="customer-subtitle mt-1">
                                    <i class="fas fa-map-marker-alt me-1"></i>{{ $appointment->customer->address }}
                                    @if($appointment->customer->city), {{ $appointment->customer->city }}@endif
                                </div>
                            @endif
                        </div>
                        <div class="d-flex">
                            <div class="stat-item">
                                <div class="stat-value">{{ $customerVehicles->count() }}</div>
                                <div class="stat-label">Vehicles</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-value">{{ $customerHistory }}</div>
                                <div class="stat-label">Visits</div>
                            </div>
                        </div>
                    </div>
                    @if($customerVehicles->isNotEmpty())
                    <div class="mt-3">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <i class="fas fa-car" style="font-size: 0.75rem; opacity: 0.7;"></i>
                            <span style="font-size: 0.75rem; opacity: 0.7; text-transform: uppercase; letter-spacing: 0.5px;">Vehicles</span>
                        </div>
                        <div class="vehicle-pills">
                            @foreach($customerVehicles as $v)
                            <span class="vehicle-pill {{ ($appointment->vehicle && $appointment->vehicle->id == $v->id) ? 'active' : '' }}">
                                {{ $v->year }} {{ $v->make }} {{ $v->model }}
                                @if($v->license_plate) <strong>[{{ $v->license_plate }}]</strong> @endif
                            </span>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Appointment Info Card -->
            <div class="card modern-card mt-4 mb-4">
                <div class="card-header modern-card-header">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Appointment Information</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="info-label">Date & Time</div>
                            <div class="info-value">
                                <i class="far fa-calendar me-1"></i>
                                {{ $appointment->appointment_date ? $appointment->appointment_date->format('M d, Y') : 'N/A' }}
                                @if($appointment->appointment_time)
                                    <span class="ms-2 badge bg-soft-info text-dark">
                                        <i class="far fa-clock me-1"></i>{{ $appointment->appointment_time }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-label">Type</div>
                            <div class="info-value">
                                <span class="badge bg-soft-primary text-primary">
                                    {{ config('service-types.list.' . $appointment->appointment_type . '.name', ucfirst(str_replace('_', ' ', $appointment->appointment_type))) }}
                                </span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-label">Priority</div>
                            <div class="info-value">
                                <span class="badge bg-{{ $appointment->priority === 'emergency' ? 'danger' : ($appointment->priority === 'high' ? 'warning' : 'info') }}">
                                    {{ ucfirst($appointment->priority) }}
                                </span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-label">Vehicle</div>
                            <div class="info-value">
                                @if($appointment->vehicle)
                                    <i class="fas fa-car me-1"></i>
                                    {{ $appointment->vehicle->year }} {{ $appointment->vehicle->make }} {{ $appointment->vehicle->model }}
                                    @if($appointment->vehicle->license_plate)
                                        <span class="badge bg-light text-dark ms-1">{{ $appointment->vehicle->license_plate }}</span>
                                    @endif
                                @elseif($appointment->vehicle_description)
                                    {{ $appointment->vehicle_description }}
                                @else
                                    <span class="text-muted">No vehicle specified</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-label">Bay / Duration</div>
                            <div class="info-value">
                                @if($appointment->bay_number)
                                    <span class="badge bg-soft-secondary">Bay #{{ $appointment->bay_number }}</span>
                                @endif
                                @if($appointment->estimated_duration)
                                    <span class="ms-1">{{ $appointment->estimated_duration }} hrs</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-label">Estimated Cost</div>
                            <div class="info-value">
                                @if($appointment->estimated_cost)
                                    ₱{{ number_format($appointment->estimated_cost, 2) }}
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </div>
                        </div>
                        @if($appointment->service_request)
                        <div class="col-12">
                            <div class="info-label">Service Request</div>
                            <div class="info-value bg-light p-2 rounded">{{ $appointment->service_request }}</div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Staff Assignment Card -->
            <div class="card modern-card mb-4">
                <div class="card-header modern-card-header">
                    <h5 class="mb-0"><i class="fas fa-users me-2"></i>Staff Assignment</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="info-label">Service Advisor</div>
                            <div class="info-value">
                                @if($appointment->advisor ?? $appointment->serviceAdvisor)
                                    <span class="tech-chip">
                                        <img src="https://ui-avatars.com/api/?name={{ urlencode(($appointment->advisor ?? $appointment->serviceAdvisor)->name) }}&size=28&background=random" 
                                             class="rounded-circle me-2" width="28" height="28">
                                        {{ ($appointment->advisor ?? $appointment->serviceAdvisor)->name }}
                                    </span>
                                @else
                                    <span class="text-muted fst-italic">Not assigned</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-label">Technician</div>
                            <div class="info-value">
                                @if($appointment->technicians && $appointment->technicians->count() > 0)
                                    @foreach($appointment->technicians as $tech)
                                        <span class="tech-chip">
                                            <img src="https://ui-avatars.com/api/?name={{ urlencode($tech->name) }}&size=20&background=random" 
                                                 class="rounded-circle me-1" width="20" height="20">
                                            {{ $tech->name }}
                                            @if($tech->pivot->role)
                                                <span class="text-muted small">({{ $tech->pivot->role }})</span>
                                            @endif
                                        </span>
                                    @endforeach
                                @elseif($appointment->technician)
                                    <span class="tech-chip">
                                        <img src="https://ui-avatars.com/api/?name={{ urlencode($appointment->technician->name) }}&size=20&background=random" 
                                             class="rounded-circle me-1" width="20" height="20">
                                        {{ $appointment->technician->name }}
                                    </span>
                                @else
                                    <span class="text-muted fst-italic">Not assigned</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Customer Notes -->
            @if($appointment->customer_notes)
            <div class="card modern-card mb-4">
                <div class="card-header modern-card-header">
                    <h5 class="mb-0"><i class="fas fa-sticky-note me-2"></i>Customer Notes</h5>
                </div>
                <div class="card-body">
                    <p class="mb-0">{{ $appointment->customer_notes }}</p>
                </div>
            </div>
            @endif

            <!-- Status Timeline -->
            <div class="card modern-card mb-4">
                <div class="card-header modern-card-header">
                    <h5 class="mb-0"><i class="fas fa-clock me-2"></i>Status Timeline</h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        @if($appointment->scheduled_at)
                        <div class="timeline-item">
                            <div class="timeline-marker bg-info"></div>
                            <div class="timeline-content">
                                <strong>Scheduled</strong>
                                <small class="text-muted d-block">{{ $appointment->scheduled_at->format('M d, Y h:i A') }}</small>
                            </div>
                        </div>
                        @endif
                        @if($appointment->confirmed_at)
                        <div class="timeline-item">
                            <div class="timeline-marker bg-primary"></div>
                            <div class="timeline-content">
                                <strong>Confirmed</strong>
                                <small class="text-muted d-block">{{ $appointment->confirmed_at->format('M d, Y h:i A') }}</small>
                            </div>
                        </div>
                        @endif
                        @if($appointment->checked_in_at)
                        <div class="timeline-item">
                            <div class="timeline-marker bg-success"></div>
                            <div class="timeline-content">
                                <strong>Checked In</strong>
                                <small class="text-muted d-block">{{ $appointment->checked_in_at->format('M d, Y h:i A') }}</small>
                            </div>
                        </div>
                        @endif
                        @if($appointment->started_at)
                        <div class="timeline-item">
                            <div class="timeline-marker bg-warning"></div>
                            <div class="timeline-content">
                                <strong>In Progress</strong>
                                <small class="text-muted d-block">{{ $appointment->started_at->format('M d, Y h:i A') }}</small>
                            </div>
                        </div>
                        @endif
                        @if($appointment->completed_at)
                        <div class="timeline-item">
                            <div class="timeline-marker bg-success"></div>
                            <div class="timeline-content">
                                <strong>Completed</strong>
                                <small class="text-muted d-block">{{ $appointment->completed_at->format('M d, Y h:i A') }}</small>
                            </div>
                        </div>
                        @endif
                        @if($appointment->cancelled_at)
                        <div class="timeline-item">
                            <div class="timeline-marker bg-danger"></div>
                            <div class="timeline-content">
                                <strong>Cancelled</strong>
                                <small class="text-muted d-block">{{ $appointment->cancelled_at->format('M d, Y h:i A') }}</small>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column -->
        <div class="col-lg-4">
            <!-- Quick Actions -->
            <div class="card modern-card mb-4">
                <div class="card-header modern-card-header">
                    <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @if(!$appointment->estimate)
                            <a href="{{ route('estimates.create', ['appointment_id' => $appointment->id]) }}" class="btn btn-success">
                                <i class="fas fa-file-invoice-dollar me-1"></i> Create Estimate
                            </a>
                        @else
                            <a href="{{ route('estimates.show', $appointment->estimate) }}" class="btn btn-outline-success">
                                <i class="fas fa-eye me-1"></i> View Estimate
                            </a>
                        @endif
                        
                        @if(!$appointment->vehicleInspection)
                            <a href="{{ route('inspections.create', ['appointment_id' => $appointment->id]) }}" class="btn btn-primary">
                                <i class="fas fa-search me-1"></i> Create Inspection
                            </a>
                        @else
                            <a href="{{ route('inspections.show', $appointment->vehicleInspection) }}" class="btn btn-outline-primary">
                                <i class="fas fa-eye me-1"></i> View Inspection
                            </a>
                        @endif

                        @if($appointment->appointment_status === 'scheduled')
                            <form action="{{ route('appointments.check-in', $appointment) }}" method="POST" class="d-grid">
                                @csrf
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-check-circle me-1"></i> Check In
                                </button>
                            </form>
                        @endif

                        @if($appointment->appointment_status === 'checked_in')
                            <form action="{{ route('appointments.complete', $appointment) }}" method="POST" class="d-grid">
                                @csrf
                                <button type="submit" class="btn btn-info">
                                    <i class="fas fa-flag-checkered me-1"></i> Complete Appointment
                                </button>
                            </form>
                        @endif

                        @if(!in_array($appointment->appointment_status, ['cancelled', 'completed']))
                            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#cancelModal">
                                <i class="fas fa-times me-1"></i> Cancel Appointment
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Related Records -->
            @if($appointment->estimate || $appointment->workOrder || $appointment->vehicleInspection)
            <div class="card modern-card mb-4">
                <div class="card-header modern-card-header">
                    <h5 class="mb-0"><i class="fas fa-link me-2"></i>Related Records</h5>
                </div>
                <div class="card-body">
                    @if($appointment->estimate)
                    <div class="related-item d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <div class="fw-bold small">Estimate</div>
                            <div class="text-muted small">#{{ $appointment->estimate->estimate_number ?? 'E-'.$appointment->estimate->id }}</div>
                            <span class="badge bg-{{ $appointment->estimate->status_badge ?? 'secondary' }} status-badge-sm">
                                {{ ucfirst($appointment->estimate->status ?? 'draft') }}
                            </span>
                        </div>
                        <a href="{{ route('estimates.show', $appointment->estimate) }}" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-eye"></i>
                        </a>
                    </div>
                    @endif
                    @if($appointment->workOrder)
                    <div class="related-item d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <div class="fw-bold small">Work Order</div>
                            <div class="text-muted small">#{{ $appointment->workOrder->work_order_number }}</div>
                            <span class="badge badge-status-{{ $appointment->workOrder->work_order_status ?? 'pending' }} status-badge-sm">
                                {{ ucfirst(str_replace('_', ' ', $appointment->workOrder->work_order_status)) }}
                            </span>
                        </div>
                        <a href="{{ route('work-orders.show', $appointment->workOrder) }}" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-eye"></i>
                        </a>
                    </div>
                    @endif
                    @if($appointment->vehicleInspection)
                    <div class="related-item d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fw-bold small">Inspection</div>
                            <div class="text-muted small">{{ $appointment->vehicleInspection->inspection_name ?? 'Inspection #'.$appointment->vehicleInspection->id }}</div>
                            <span class="badge badge-status-{{ $appointment->vehicleInspection->inspection_status ?? 'pending' }} status-badge-sm">
                                {{ ucfirst(str_replace('_', ' ', $appointment->vehicleInspection->inspection_status)) }}
                            </span>
                        </div>
                        <a href="{{ route('inspections.show', $appointment->vehicleInspection) }}" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-eye"></i>
                        </a>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Deposit Info if applicable -->
            @if($appointment->requires_deposit || $appointment->deposit_amount)
            <div class="card modern-card mb-4">
                <div class="card-header modern-card-header">
                    <h5 class="mb-0"><i class="fas fa-money-bill me-2"></i>Deposit</h5>
                </div>
                <div class="card-body text-center py-4">
                    <h3 class="text-primary fw-bold mb-2">₱{{ number_format($appointment->deposit_amount ?? 0, 2) }}</h3>
                    <span class="badge bg-{{ $appointment->deposit_status === 'paid' ? 'success' : ($appointment->deposit_status === 'pending' ? 'warning' : 'secondary') }} px-3 py-2">
                        {{ ucfirst($appointment->deposit_status ?? 'not_required') }}
                    </span>
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Customer's Appointment History -->
    @if($customerAppointments->count() > 0)
    <div class="card modern-card mt-4">
        <div class="card-header modern-card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-history me-2"></i>Customer's Appointment History</h5>
            <span class="badge bg-secondary">{{ $customerAppointments->count() }} records</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Date</th>
                            <th>Time</th>
                            <th>Type</th>
                            <th>Vehicle</th>
                            <th>Status</th>
                            <th class="text-end pe-3">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($customerAppointments as $pastAppointment)
                            <tr>
                                <td class="ps-3">{{ $pastAppointment->appointment_date ? $pastAppointment->appointment_date->format('M d, Y') : 'N/A' }}</td>
                                <td>{{ $pastAppointment->appointment_time ?? '—' }}</td>
                                <td>{{ config('service-types.list.' . $pastAppointment->appointment_type . '.name', ucfirst(str_replace('_', ' ', $pastAppointment->appointment_type))) }}</td>
                                <td>
                                    @if($pastAppointment->vehicle)
                                        {{ $pastAppointment->vehicle->make }} {{ $pastAppointment->vehicle->model }}
                                    @else
                                        {{ $pastAppointment->vehicle_description ?? '—' }}
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-status-{{ $pastAppointment->appointment_status }}">
                                        {{ ucfirst(str_replace('_', ' ', $pastAppointment->appointment_status)) }}
                                    </span>
                                </td>
                                <td class="text-end pe-3">
                                    <a href="{{ route('appointments.show', $pastAppointment) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i>
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
<div class="modal fade" id="cancelModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0">
            <div class="modal-header bg-danger text-white border-0">
                <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i>Cancel Appointment</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('appointments.cancel', $appointment) }}" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <div class="alert alert-warning d-flex align-items-center mb-3">
                        <i class="fas fa-info-circle me-2 fs-5"></i>
                        <span>This action cannot be undone. Please provide a reason for cancellation.</span>
                    </div>
                    <div class="mb-3">
                        <label for="cancellation_reason" class="form-label fw-semibold">Cancellation Reason <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="cancellation_reason" name="cancellation_reason" rows="3" required placeholder="Provide reason for cancellation..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-danger px-4">
                        <i class="fas fa-times me-1"></i> Cancel Appointment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@push('styles')
<style>
.timeline {
    position: relative;
    padding-left: 30px;
}
.timeline::before {
    content: '';
    position: absolute;
    left: 10px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e0e0e0;
}
.timeline-item {
    position: relative;
    padding-bottom: 20px;
}
.timeline-marker {
    position: absolute;
    left: -24px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 2px solid #fff;
    box-shadow: 0 0 0 2px #e0e0e0;
    top: 4px;
}
.timeline-content {
    padding-left: 5px;
}
.related-item {
    border-bottom: 1px solid #f0f0f0;
    padding-bottom: 10px;
}
.related-item:last-child {
    border-bottom: none;
    padding-bottom: 0;
}
.status-badge-sm {
    font-size: 0.65rem;
    padding: 0.2em 0.5em;
}
@media print {
    .btn, .modal { display: none !important; }
    .card { box-shadow: none !important; border: 1px solid #dee2e6 !important; }
    .container-fluid { padding: 0 !important; }
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    @if(in_array($appointment->appointment_status, ['checked_in', 'in_progress']))
        setTimeout(function() { location.reload(); }, 30000);
    @endif
});
</script>
@endpush

@endsection
