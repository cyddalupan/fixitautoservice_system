@extends('layouts.app')

@section('title', 'Appointments - Fix-It Auto Services')

@section('content')
<script>
// Simple check-in function that will definitely work
function checkInCustomer(appointmentId, appointmentNumber, customerName) {
    console.log('Check-in button clicked!', appointmentId, appointmentNumber, customerName);
    
    // Check if SweetAlert2 is loaded
    if (typeof Swal === 'undefined') {
        alert('Error: SweetAlert2 not loaded. Please refresh page.');
        return;
    }
    
    // Check if jQuery is loaded
    if (typeof jQuery === 'undefined') {
        alert('Error: jQuery not loaded. Please refresh page.');
        return;
    }
    
    Swal.fire({
        title: 'Check In Customer',
        html: 'Are you sure you want to check in <strong>' + customerName + '</strong> for appointment <strong>' + appointmentNumber + '</strong>?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, Check In',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#198754',
    }).then((result) => {
        if (result.isConfirmed) {
            console.log('Sending AJAX request for appointment:', appointmentId);
            
            $.ajax({
                url: '/appointments/' + appointmentId + '/ajax-check-in',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    console.log('AJAX success:', response);
                    if (response.success) {
                        Swal.fire({
                            title: 'Success!',
                            html: response.message + '<br><br>' + (response.redirect_message || ''),
                            icon: 'success',
                            showCancelButton: true,
                            confirmButtonText: 'Go to Inspection',
                            cancelButtonText: 'Stay Here',
                            confirmButtonColor: '#198754',
                        }).then((result) => {
                            if (result.isConfirmed && response.inspection_url) {
                                // Redirect to the vehicle inspection
                                window.location.href = response.inspection_url;
                            } else {
                                // Reload the page to show updated status
                                location.reload();
                            }
                        });
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function(xhr) {
                    console.error('AJAX error:', xhr);
                    Swal.fire('Error', 'Failed to check in appointment. Please try again.', 'error');
                }
            });
        }
    });
}
</script>

<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-calendar-alt me-2"></i>Appointments
            </h1>
            <p class="text-muted mb-0">Scheduled bookings | Status: Scheduled | Arrived | Cancelled | Converted</p>
        </div>
        <div>
            <a href="{{ route('appointments.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> Schedule Appointment
            </a>
        </div>
    </div>
</div>

<!-- Status Tabs -->
<div class="card mb-4">
    <div class="card-body p-0">
        <ul class="nav nav-tabs nav-tabs-custom" id="appointmentTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="scheduled-tab" data-bs-toggle="tab" data-bs-target="#scheduled" type="button" role="tab">
                    <i class="fas fa-clock me-1"></i> Scheduled ({{ $stats['scheduled'] }})
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="arrived-tab" data-bs-toggle="tab" data-bs-target="#arrived" type="button" role="tab">
                    <i class="fas fa-check-circle me-1"></i> Arrived ({{ $stats['arrived'] }})
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="cancelled-tab" data-bs-toggle="tab" data-bs-target="#cancelled" type="button" role="tab">
                    <i class="fas fa-times-circle me-1"></i> Cancelled ({{ $stats['cancelled'] }})
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="converted-tab" data-bs-toggle="tab" data-bs-target="#converted" type="button" role="tab">
                    <i class="fas fa-exchange-alt me-1"></i> Converted ({{ $stats['converted'] }})
                </button>
            </li>
        </ul>
        
        <div class="tab-content p-3" id="appointmentTabsContent">
            <!-- Scheduled Tab -->
            <div class="tab-pane fade show active" id="scheduled" role="tabpanel">
                @if($scheduledAppointments->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Appointment #</th>
                                    <th>Customer & Vehicle</th>
                                    <th>Date & Time</th>
                                    <th>Service Type</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($scheduledAppointments as $appointment)
                                <tr>
                                    <td>
                                        <strong>{{ $appointment->appointment_number }}</strong>
                                        @if($appointment->is_waitlist)
                                            <br>
                                            <span class="badge bg-warning">Waitlist</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div>
                                            <strong>{{ $appointment->customer->full_name }}</strong>
                                            <br>
                                            <small class="text-muted">
                                                <i class="fas fa-car me-1"></i>
                                                {{ $appointment->vehicle->year }} {{ $appointment->vehicle->make }} {{ $appointment->vehicle->model }}
                                            </small>
                                            <br>
                                            <small class="text-muted">{{ $appointment->vehicle->license_plate }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <strong>{{ $appointment->appointment_date->format('M d, Y') }}</strong>
                                        <br>
                                        <span class="text-muted">{{ date('g:i A', strtotime($appointment->appointment_time)) }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ ucfirst(str_replace('_', ' ', $appointment->appointment_type)) }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $appointment->status_color }}">
                                            {{ ucfirst(str_replace('_', ' ', $appointment->appointment_status)) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <!-- PRIMARY ACTION: Check In Customer -->
                                            <button type="button" class="btn btn-success btn-sm check-in-btn" 
                                                    onclick="checkInCustomer({{ $appointment->id }}, '{{ $appointment->appointment_number }}', '{{ addslashes($appointment->customer->full_name) }}')"
                                                    data-appointment-id="{{ $appointment->id }}"
                                                    data-appointment-number="{{ $appointment->appointment_number }}"
                                                    data-customer-name="{{ $appointment->customer->full_name }}">
                                                <i class="fas fa-check-circle me-1"></i> Check In
                                            </button>
                                            
                                            <!-- Convert to Estimate -->
                                            @php
                                                $inspection = $appointment->vehicleInspection;
                                                $estimateParams = $inspection 
                                                    ? ['inspection_id' => $inspection->id] 
                                                    : ['appointment_id' => $appointment->id];
                                            @endphp
                                            <a href="{{ route('estimates.create', $estimateParams) }}" 
                                               class="btn btn-primary btn-sm">
                                                <i class="fas fa-file-invoice-dollar me-1"></i> Create Estimate
                                            </a>
                                            
                                            <!-- View Details -->
                                            <a href="{{ route('appointments.show', $appointment) }}" class="btn btn-outline-primary btn-sm">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            
                                            <!-- Cancel Appointment -->
                                            <button type="button" class="btn btn-outline-danger btn-sm cancel-btn"
                                                    data-appointment-id="{{ $appointment->id }}"
                                                    data-appointment-number="{{ $appointment->appointment_number }}">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-calendar-times fa-4x text-muted mb-3"></i>
                        <h4 class="text-muted">No scheduled appointments</h4>
                        <p class="text-muted">All appointments have been processed or cancelled</p>
                    </div>
                @endif
            </div>
            
            <!-- Arrived Tab -->
            <div class="tab-pane fade" id="arrived" role="tabpanel">
                @if($arrivedAppointments->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Appointment #</th>
                                    <th>Customer & Vehicle</th>
                                    <th>Arrival Time</th>
                                    <th>Service Type</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($arrivedAppointments as $appointment)
                                <tr>
                                    <td>
                                        <strong>{{ $appointment->appointment_number }}</strong>
                                    </td>
                                    <td>
                                        <div>
                                            <strong>{{ $appointment->customer->full_name }}</strong>
                                            <br>
                                            <small class="text-muted">
                                                <i class="fas fa-car me-1"></i>
                                                {{ $appointment->vehicle->year }} {{ $appointment->vehicle->make }} {{ $appointment->vehicle->model }}
                                            </small>
                                            <br>
                                            <small class="text-muted">{{ $appointment->vehicle->license_plate }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        @if($appointment->checked_in_at)
                                            <strong>{{ $appointment->checked_in_at->format('M d, Y') }}</strong>
                                            <br>
                                            <span class="text-muted">{{ $appointment->checked_in_at->format('g:i A') }}</span>
                                        @else
                                            <span class="text-muted">Not recorded</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ ucfirst(str_replace('_', ' ', $appointment->appointment_type)) }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-warning">
                                            <i class="fas fa-check-circle me-1"></i> Arrived
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <!-- PRIMARY ACTION: Create Estimate -->
                                            <a href="{{ route('estimates.create', ['appointment_id' => $appointment->id]) }}" 
                                               class="btn btn-primary btn-sm">
                                                <i class="fas fa-file-invoice-dollar me-1"></i> Create Estimate
                                            </a>
                                            
                                            <!-- View Details -->
                                            <a href="{{ route('appointments.show', $appointment) }}" class="btn btn-outline-primary btn-sm">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            
                                            <!-- Mark as No Show -->
                                            <button type="button" class="btn btn-outline-danger btn-sm mark-no-show-btn"
                                                    data-appointment-id="{{ $appointment->id }}"
                                                    data-appointment-number="{{ $appointment->appointment_number }}">
                                                <i class="fas fa-user-times me-1"></i> No Show
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-user-check fa-4x text-muted mb-3"></i>
                        <h4 class="text-muted">No arrived appointments</h4>
                        <p class="text-muted">No customers have checked in yet</p>
                    </div>
                @endif
            </div>
            
            <!-- Cancelled Tab -->
            <div class="tab-pane fade" id="cancelled" role="tabpanel">
                @if($cancelledAppointments->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Appointment #</th>
                                    <th>Customer & Vehicle</th>
                                    <th>Original Date</th>
                                    <th>Cancelled On</th>
                                    <th>Reason</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($cancelledAppointments as $appointment)
                                <tr>
                                    <td>
                                        <strong>{{ $appointment->appointment_number }}</strong>
                                    </td>
                                    <td>
                                        <div>
                                            <strong>{{ $appointment->customer->full_name }}</strong>
                                            <br>
                                            <small class="text-muted">
                                                <i class="fas fa-car me-1"></i>
                                                {{ $appointment->vehicle->year }} {{ $appointment->vehicle->make }} {{ $appointment->vehicle->model }}
                                            </small>
                                        </div>
                                    </td>
                                    <td>
                                        <strong>{{ $appointment->appointment_date->format('M d, Y') }}</strong>
                                        <br>
                                        <span class="text-muted">{{ date('g:i A', strtotime($appointment->appointment_time)) }}</span>
                                    </td>
                                    <td>
                                        @if($appointment->cancelled_at)
                                            <strong>{{ $appointment->cancelled_at->format('M d, Y') }}</strong>
                                            <br>
                                            <span class="text-muted">{{ $appointment->cancelled_at->format('g:i A') }}</span>
                                        @else
                                            <span class="text-muted">Not recorded</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-danger">{{ $appointment->cancellation_reason ?? 'No reason given' }}</span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <!-- Reschedule -->
                                            <a href="{{ route('appointments.edit', $appointment) }}" class="btn btn-outline-primary btn-sm">
                                                <i class="fas fa-calendar-plus me-1"></i> Reschedule
                                            </a>
                                            
                                            <!-- View Details -->
                                            <a href="{{ route('appointments.show', $appointment) }}" class="btn btn-outline-secondary btn-sm">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-ban fa-4x text-muted mb-3"></i>
                        <h4 class="text-muted">No cancelled appointments</h4>
                        <p class="text-muted">Great! No appointments have been cancelled</p>
                    </div>
                @endif
            </div>
            
            <!-- Converted Tab -->
            <div class="tab-pane fade" id="converted" role="tabpanel">
                @if($convertedAppointments->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Appointment #</th>
                                    <th>Customer & Vehicle</th>
                                    <th>Appointment Date</th>
                                    <th>Converted To</th>
                                    <th>Converted On</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($convertedAppointments as $appointment)
                                <tr>
                                    <td>
                                        <strong>{{ $appointment->appointment_number }}</strong>
                                    </td>
                                    <td>
                                        <div>
                                            <strong>{{ $appointment->customer->full_name }}</strong>
                                            <br>
                                            <small class="text-muted">
                                                <i class="fas fa-car me-1"></i>
                                                {{ $appointment->vehicle->year }} {{ $appointment->vehicle->make }} {{ $appointment->vehicle->model }}
                                            </small>
                                        </div>
                                    </td>
                                    <td>
                                        <strong>{{ $appointment->appointment_date->format('M d, Y') }}</strong>
                                        <br>
                                        <span class="text-muted">{{ date('g:i A', strtotime($appointment->appointment_time)) }}</span>
                                    </td>
                                    <td>
                                        @if($appointment->estimate)
                                            <span class="badge bg-success">
                                                <i class="fas fa-file-invoice-dollar me-1"></i> Estimate #{{ $appointment->estimate->estimate_number }}
                                            </span>
                                        @elseif($appointment->workOrder)
                                            <span class="badge bg-primary">
                                                <i class="fas fa-wrench me-1"></i> Work Order #{{ $appointment->workOrder->work_order_number }}
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">Unknown</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($appointment->estimate)
                                            <strong>{{ $appointment->estimate->created_at->format('M d, Y') }}</strong>
                                            <br>
                                            <span class="text-muted">{{ $appointment->estimate->created_at->format('g:i A') }}</span>
                                        @elseif($appointment->workOrder)
                                            <strong>{{ $appointment->workOrder->created_at->format('M d, Y') }}</strong>
                                            <br>
                                            <span class="text-muted">{{ $appointment->workOrder->created_at->format('g:i A') }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <!-- View Estimate -->
                                            @if($appointment->estimate)
                                                <a href="{{ route('estimates.show', $appointment->estimate) }}" class="btn btn-outline-success btn-sm">
                                                    <i class="fas fa-file-invoice-dollar me-1"></i> View Estimate
                                                </a>
                                            @endif
                                            
                                            <!-- View Work Order -->
                                            @if($appointment->workOrder)
                                                <a href="{{ route('work-orders.show', $appointment->workOrder) }}" class="btn btn-outline-primary btn-sm">
                                                    <i class="fas fa-wrench me-1"></i> View Work Order
                                                </a>
                                            @endif
                                            
                                            <!-- View Appointment -->
                                            <a href="{{ route('appointments.show', $appointment) }}" class="btn btn-outline-secondary btn-sm">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-exchange-alt fa-4x text-muted mb-3"></i>
                        <h4 class="text-muted">No converted appointments</h4>
                        <p class="text-muted">No appointments have been converted to estimates or work orders yet</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Today's Arrivals -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="fas fa-users me-2"></i>Today's Arrivals
        </h5>
    </div>
    <div class="card-body">
        @php
            $todayArrivals = $arrivedAppointments->filter(function($appointment) {
                return $appointment->appointment_date->isToday();
            });
        @endphp
        
        @if($todayArrivals->count() > 0)
            <div class="row">
                @foreach($todayArrivals as $appointment)
                <div class="col-md-4 mb-3">
                    <div class="card border-start border-warning border-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="card-title mb-1">{{ $appointment->customer->full_name }}</h6>
                                    <p class="text-muted mb-1">
                                        <i class="fas fa-car me-1"></i>
                                        {{ $appointment->vehicle->make }} {{ $appointment->vehicle->model }}
                                    </p>
                                    <p class="text-muted mb-0">
                                        <i class="fas fa-clock me-1"></i>
                                        Arrived at {{ $appointment->checked_in_at->format('g:i A') }}
                                    </p>
                                </div>
                                <span class="badge bg-warning">Arrived</span>
                            </div>
                            <div class="mt-3">
                                <a href="{{ route('estimates.create', ['appointment_id' => $appointment->id]) }}" 
                                   class="btn btn-primary btn-sm w-100">
                                    <i class="fas fa-file-invoice-dollar me-1"></i> Create Estimate
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-4">
                <i class="fas fa-user-clock fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No arrivals today</h5>
                <p class="text-muted">No customers have checked in yet today</p>
            </div>
        @endif
    </div>
</div>

<!-- Quick Stats -->
<div class="row mt-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-0">Scheduled</h6>
                        <h2 class="mb-0">{{ $stats['scheduled'] }}</h2>
                    </div>
                    <i class="fas fa-calendar fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-0">Arrived</h6>
                        <h2 class="mb-0">{{ $stats['arrived'] }}</h2>
                    </div>
                    <i class="fas fa-user-check fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-danger text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-0">Cancelled</h6>
                        <h2 class="mb-0">{{ $stats['cancelled'] }}</h2>
                    </div>
                    <i class="fas fa-times-circle fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-0">Converted</h6>
                        <h2 class="mb-0">{{ $stats['converted'] }}</h2>
                    </div>
                    <i class="fas fa-exchange-alt fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Check In Button Handler - Using inline onclick function checkInCustomer() instead
    
    // Cancel Button Handler
    $('.cancel-btn').click(function() {
        const appointmentId = $(this).data('appointment-id');
        const appointmentNumber = $(this).data('appointment-number');
        
        Swal.fire({
            title: 'Cancel Appointment',
            html: `Cancel appointment <strong>${appointmentNumber}</strong>?`,
            input: 'textarea',
            inputLabel: 'Cancellation Reason',
            inputPlaceholder: 'Enter reason for cancellation...',
            inputAttributes: {
                maxlength: 500
            },
            showCancelButton: true,
            confirmButtonText: 'Yes, Cancel',
            cancelButtonText: 'Keep Appointment',
            confirmButtonColor: '#dc3545',
            inputValidator: (value) => {
                if (!value) {
                    return 'Please provide a cancellation reason';
                }
            }
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/appointments/${appointmentId}/ajax-cancel`,
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        cancellation_reason: result.value
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                title: 'Cancelled!',
                                text: response.message,
                                icon: 'success',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    },
                    error: function(xhr) {
                        Swal.fire('Error', 'Failed to cancel appointment', 'error');
                    }
                });
            }
        });
    });
    
    // Mark as No Show Button Handler
    $('.mark-no-show-btn').click(function() {
        const appointmentId = $(this).data('appointment-id');
        const appointmentNumber = $(this).data('appointment-number');
        
        Swal.fire({
            title: 'Mark as No Show',
            html: `Mark appointment <strong>${appointmentNumber}</strong> as no-show?`,
            text: 'This will update the customer\'s no-show count.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, Mark as No Show',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#6c757d',
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/appointments/${appointmentId}/ajax-mark-no-show`,
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                title: 'Marked as No Show!',
                                text: response.message,
                                icon: 'success',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    },
                    error: function(xhr) {
                        Swal.fire('Error', 'Failed to mark as no-show', 'error');
                    }
                });
            }
        });
    });
    
    // Tab Persistence
    const activeTab = localStorage.getItem('appointmentsActiveTab');
    if (activeTab) {
        $(`#${activeTab}-tab`).tab('show');
    }
    
    $('#appointmentTabs button').on('shown.bs.tab', function (e) {
        const tabId = $(e.target).attr('id').replace('-tab', '');
        localStorage.setItem('appointmentsActiveTab', tabId);
    });
});
</script>
@endsection