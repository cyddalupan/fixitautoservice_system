@extends('layouts.app')

@section('title', 'My Appointments - Customer Portal')

@section('content')
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-calendar me-2"></i>My Appointments
            </h1>
            <p class="text-muted mb-0">View and manage your service appointments</p>
        </div>
        <div>
            <a href="{{ route('portal.appointments.create') }}" class="btn btn-primary">
                <i class="fas fa-calendar-plus me-1"></i> Schedule New
            </a>
            <a href="{{ route('portal.dashboard') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
            </a>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<!-- Appointment Filters -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row">
            <div class="col-md-3 mb-3">
                <label class="form-label">Status</label>
                <select class="form-select" id="statusFilter">
                    <option value="">All Statuses</option>
                    <option value="scheduled">Scheduled</option>
                    <option value="confirmed">Confirmed</option>
                    <option value="checked_in">Checked In</option>
                    <option value="in_progress">In Progress</option>
                    <option value="completed">Completed</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label">Date Range</label>
                <select class="form-select" id="dateFilter">
                    <option value="">All Dates</option>
                    <option value="upcoming">Upcoming</option>
                    <option value="past">Past</option>
                    <option value="today">Today</option>
                    <option value="this_week">This Week</option>
                    <option value="this_month">This Month</option>
                </select>
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label">Vehicle</label>
                <select class="form-select" id="vehicleFilter">
                    <option value="">All Vehicles</option>
                    @foreach($customer->vehicles as $vehicle)
                        <option value="{{ $vehicle->id }}">
                            {{ $vehicle->year }} {{ $vehicle->make }} {{ $vehicle->model }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 mb-3 d-flex align-items-end">
                <button class="btn btn-outline-primary w-100" id="applyFilters">
                    <i class="fas fa-filter me-1"></i> Apply Filters
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Appointments List -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="fas fa-list me-2"></i>Appointments ({{ $appointments->total() }})
        </h6>
        <div class="btn-group">
            <button class="btn btn-sm btn-outline-secondary" id="listViewBtn">
                <i class="fas fa-list"></i>
            </button>
            <button class="btn btn-sm btn-outline-secondary active" id="calendarViewBtn">
                <i class="fas fa-calendar"></i>
            </button>
        </div>
    </div>
    
    <div class="card-body" id="listView">
        @if($appointments->count() > 0)
            <div class="list-group list-group-flush">
                @foreach($appointments as $appointment)
                    <div class="list-group-item appointment-item" 
                         data-status="{{ $appointment->status }}"
                         data-date="{{ $appointment->appointment_date->format('Y-m-d') }}"
                         data-vehicle="{{ $appointment->vehicle_id }}">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="mb-0">
                                        {{ $appointment->service_type }}
                                        @if($appointment->urgency === 'urgent')
                                            <span class="badge bg-danger ms-2">Urgent</span>
                                        @elseif($appointment->urgency === 'soon')
                                            <span class="badge bg-warning ms-2">Soon</span>
                                        @endif
                                    </h6>
                                    <span class="badge bg-{{ $appointment->status === 'scheduled' ? 'success' : 
                                                              ($appointment->status === 'confirmed' ? 'info' :
                                                              ($appointment->status === 'in_progress' ? 'warning' :
                                                              ($appointment->status === 'completed' ? 'secondary' : 'danger'))) }}">
                                        {{ ucfirst($appointment->status) }}
                                    </span>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-4">
                                        <p class="mb-1">
                                            <i class="fas fa-car me-2 text-muted"></i>
                                            <strong>Vehicle:</strong> 
                                            {{ $appointment->vehicle->year }} {{ $appointment->vehicle->make }} {{ $appointment->vehicle->model }}
                                        </p>
                                    </div>
                                    <div class="col-md-4">
                                        <p class="mb-1">
                                            <i class="fas fa-calendar me-2 text-muted"></i>
                                            <strong>Date:</strong> 
                                            {{ $appointment->appointment_date->format('M j, Y') }}
                                        </p>
                                    </div>
                                    <div class="col-md-4">
                                        <p class="mb-1">
                                            <i class="fas fa-clock me-2 text-muted"></i>
                                            <strong>Time:</strong> 
                                            {{ $appointment->appointment_time }}
                                        </p>
                                    </div>
                                </div>
                                
                                @if($appointment->description)
                                    <p class="mb-2 mt-2">
                                        <i class="fas fa-file-alt me-2 text-muted"></i>
                                        <strong>Description:</strong> 
                                        {{ Str::limit($appointment->description, 150) }}
                                    </p>
                                @endif
                                
                                @if($appointment->technician)
                                    <p class="mb-0">
                                        <i class="fas fa-user-cog me-2 text-muted"></i>
                                        <strong>Technician:</strong> 
                                        {{ $appointment->technician->name }}
                                    </p>
                                @endif
                            </div>
                            
                            <div class="ms-3">
                                <div class="btn-group-vertical">
                                    <a href="{{ route('portal.appointments.show', $appointment) }}" 
                                       class="btn btn-sm btn-outline-primary mb-1">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    
                                    @if(in_array($appointment->status, ['scheduled', 'confirmed']))
                                        <button type="button" class="btn btn-sm btn-outline-danger mb-1" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#cancelModal{{ $appointment->id }}">
                                            <i class="fas fa-times"></i>
                                        </button>
                                        
                                        <!-- Cancel Modal -->
                                        <div class="modal fade" id="cancelModal{{ $appointment->id }}" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Cancel Appointment</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p>Are you sure you want to cancel this appointment?</p>
                                                        <p><strong>{{ $appointment->service_type }}</strong></p>
                                                        <p>{{ $appointment->appointment_date->format('M j, Y') }} at {{ $appointment->appointment_time }}</p>
                                                        <form id="cancelForm{{ $appointment->id }}" 
                                                              action="{{ route('portal.appointments.cancel', $appointment) }}" 
                                                              method="POST">
                                                            @csrf
                                                            <div class="mb-3">
                                                                <label for="reason{{ $appointment->id }}" class="form-label">Reason for cancellation</label>
                                                                <textarea class="form-control" id="reason{{ $appointment->id }}" 
                                                                          name="reason" rows="3" 
                                                                          placeholder="Optional reason for cancellation"></textarea>
                                                            </div>
                                                        </form>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                        <button type="submit" form="cancelForm{{ $appointment->id }}" 
                                                                class="btn btn-danger">Cancel Appointment</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            
            <!-- Pagination -->
            @if($appointments->hasPages())
                <div class="mt-4">
                    {{ $appointments->links() }}
                </div>
            @endif
        @else
            <div class="text-center py-5">
                <i class="fas fa-calendar fa-4x text-muted mb-4"></i>
                <h4 class="text-muted mb-3">No Appointments Found</h4>
                <p class="text-muted mb-4">
                    You don't have any appointments scheduled yet.
                </p>
                <a href="{{ route('portal.appointments.create') }}" class="btn btn-primary">
                    <i class="fas fa-calendar-plus me-1"></i> Schedule Your First Appointment
                </a>
            </div>
        @endif
    </div>
    
    <!-- Calendar View (Hidden by default) -->
    <div class="card-body d-none" id="calendarView">
        <div class="text-center py-5">
            <i class="fas fa-calendar-alt fa-4x text-muted mb-4"></i>
            <h4 class="text-muted mb-3">Calendar View</h4>
            <p class="text-muted mb-4">
                Calendar view will be available in the next update.
            </p>
            <button class="btn btn-outline-primary" id="switchToListView">
                <i class="fas fa-list me-1"></i> Switch to List View
            </button>
        </div>
    </div>
</div>

<style>
    .appointment-item {
        border-left: 4px solid #dee2e6;
        transition: all 0.2s;
    }
    
    .appointment-item:hover {
        background-color: #f8f9fa;
        border-left-color: #667eea;
    }
    
    .appointment-item[data-status="scheduled"] {
        border-left-color: #28a745;
    }
    
    .appointment-item[data-status="confirmed"] {
        border-left-color: #17a2b8;
    }
    
    .appointment-item[data-status="in_progress"] {
        border-left-color: #ffc107;
    }
    
    .appointment-item[data-status="completed"] {
        border-left-color: #6c757d;
    }
    
    .appointment-item[data-status="cancelled"] {
        border-left-color: #dc3545;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // View toggle
        const listViewBtn = document.getElementById('listViewBtn');
        const calendarViewBtn = document.getElementById('calendarViewBtn');
        const listView = document.getElementById('listView');
        const calendarView = document.getElementById('calendarView');
        const switchToListView = document.getElementById('switchToListView');
        
        listViewBtn.addEventListener('click', function() {
            listView.classList.remove('d-none');
            calendarView.classList.add('d-none');
            listViewBtn.classList.add('active');
            calendarViewBtn.classList.remove('active');
        });
        
        calendarViewBtn.addEventListener('click', function() {
            listView.classList.add('d-none');
            calendarView.classList.remove('d-none');
            listViewBtn.classList.remove('active');
            calendarViewBtn.classList.add('active');
        });
        
        switchToListView?.addEventListener('click', function() {
            listViewBtn.click();
        });
        
        // Filter functionality
        const applyFiltersBtn = document.getElementById('applyFilters');
        const statusFilter = document.getElementById('statusFilter');
        const dateFilter = document.getElementById('dateFilter');
        const vehicleFilter = document.getElementById('vehicleFilter');
        const appointmentItems = document.querySelectorAll('.appointment-item');
        
        applyFiltersBtn.addEventListener('click', function() {
            const status = statusFilter.value;
            const date = dateFilter.value;
            const vehicle = vehicleFilter.value;
            const today = new Date().toISOString().split('T')[0];
            
            appointmentItems.forEach(item => {
                let show = true;
                const itemStatus = item.dataset.status;
                const itemDate = item.dataset.date;
                const itemVehicle = item.dataset.vehicle;
                
                // Status filter
                if (status && itemStatus !== status) {
                    show = false;
                }
                
                // Date filter
                if (date) {
                    const appointmentDate = new Date(itemDate);
                    const now = new Date();
                    
                    switch(date) {
                        case 'upcoming':
                            if (appointmentDate < now) show = false;
                            break;
                        case 'past':
                            if (appointmentDate >= now) show = false;
                            break;
                        case 'today':
                            if (itemDate !== today) show = false;
                            break;
                        case 'this_week':
                            const startOfWeek = new Date(now);
                            startOfWeek.setDate(now.getDate() - now.getDay());
                            const endOfWeek = new Date(startOfWeek);
                            endOfWeek.setDate(startOfWeek.getDate() + 6);
                            if (appointmentDate < startOfWeek || appointmentDate > endOfWeek) show = false;
                            break;
                        case 'this_month':
                            if (appointmentDate.getMonth() !== now.getMonth() || 
                                appointmentDate.getFullYear() !== now.getFullYear()) show = false;
                            break;
                    }
                }
                
                // Vehicle filter
                if (vehicle && itemVehicle !== vehicle) {
                    show = false;
                }
                
                item.style.display = show ? '' : 'none';
            });
        });
    });
</script>
@endsection