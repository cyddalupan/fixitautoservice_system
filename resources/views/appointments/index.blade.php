@extends('layouts.app')

@section('title', 'Appointments - Fix-It Auto Services')

@section('content')
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-calendar-alt me-2"></i>Appointments
            </h1>
            <p class="text-muted mb-0">Manage appointments, scheduling, and technician assignments</p>
        </div>
        <div>
            <a href="{{ route('appointments.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> Schedule Appointment
            </a>
            <a href="{{ route('appointments.calendar') }}" class="btn btn-outline-primary ms-2">
                <i class="fas fa-calendar me-1"></i> Calendar View
            </a>
        </div>
    </div>
</div>

<!-- Quick Stats -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body text-center">
                <h3 class="mb-0">{{ $stats['today'] }}</h3>
                <p class="mb-0">Today's Appointments</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body text-center">
                <h3 class="mb-0">{{ $stats['upcoming'] }}</h3>
                <p class="mb-0">Upcoming (7 days)</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body text-center">
                <h3 class="mb-0">{{ $stats['waitlist'] }}</h3>
                <p class="mb-0">Waitlist</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-danger text-white">
            <div class="card-body text-center">
                <h3 class="mb-0">{{ $stats['no_show'] }}</h3>
                <p class="mb-0">No Shows</p>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('appointments.index') }}" class="row g-3">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control" placeholder="Search appointments..." 
                       value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="scheduled" {{ request('status') == 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                    <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                    <option value="checked_in" {{ request('status') == 'checked_in' ? 'selected' : '' }}>Checked In</option>
                    <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    <option value="no_show" {{ request('status') == 'no_show' ? 'selected' : '' }}>No Show</option>
                </select>
            </div>
            <div class="col-md-2">
                <input type="date" name="date" class="form-control" value="{{ request('date') }}">
            </div>
            <div class="col-md-2">
                <select name="technician_id" class="form-select">
                    <option value="">All Technicians</option>
                    @foreach($technicians as $technician)
                        <option value="{{ $technician->id }}" {{ request('technician_id') == $technician->id ? 'selected' : '' }}>
                            {{ $technician->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter me-1"></i> Filter
                    </button>
                    <a href="{{ route('appointments.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-redo me-1"></i> Reset
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Appointments Table -->
<div class="card">
    <div class="card-body">
        @if($appointments->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Appointment #</th>
                            <th>Customer & Vehicle</th>
                            <th>Date & Time</th>
                            <th>Type & Priority</th>
                            <th>Technician</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($appointments as $appointment)
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
                                <br>
                                <small class="text-muted">
                                    @if($appointment->estimated_duration)
                                        {{ $appointment->estimated_duration }}h
                                    @endif
                                </small>
                            </td>
                            <td>
                                <span class="badge bg-info">{{ ucfirst(str_replace('_', ' ', $appointment->appointment_type)) }}</span>
                                <br>
                                <span class="badge bg-{{ $appointment->priority_color }}">
                                    {{ ucfirst($appointment->priority) }}
                                </span>
                                @if($appointment->bay_number)
                                    <br>
                                    <small class="text-muted">Bay #{{ $appointment->bay_number }}</small>
                                @endif
                            </td>
                            <td>
                                @if($appointment->technician)
                                    <div class="d-flex align-items-center">
                                        <div class="customer-avatar me-2">
                                            {{ substr($appointment->technician->name, 0, 1) }}
                                        </div>
                                        <div>
                                            <strong>{{ $appointment->technician->name }}</strong>
                                            <br>
                                            <small class="text-muted">Technician</small>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-muted">Not assigned</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ $appointment->status_color }}">
                                    {{ ucfirst(str_replace('_', ' ', $appointment->appointment_status)) }}
                                </span>
                                @if($appointment->isOverdue())
                                    <br>
                                    <small class="text-danger">Overdue</small>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('appointments.show', $appointment) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('appointments.edit', $appointment) }}" class="btn btn-sm btn-outline-secondary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div>
                    Showing {{ $appointments->firstItem() }} to {{ $appointments->lastItem() }} of {{ $appointments->total() }} appointments
                </div>
                <div>
                    {{ $appointments->links() }}
                </div>
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-calendar-times fa-4x text-muted mb-3"></i>
                <h4 class="text-muted">No appointments found</h4>
                <p class="text-muted">Try adjusting your filters or schedule a new appointment</p>
                <a href="{{ route('appointments.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i> Schedule First Appointment
                </a>
            </div>
        @endif
    </div>
</div>

<!-- Today's Schedule -->
<div class="card mt-4">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="fas fa-clock me-2"></i>Today's Schedule
        </h5>
    </div>
    <div class="card-body">
        @php
            $todayAppointments = \App\Models\Appointment::with(['customer', 'vehicle', 'technician'])
                ->whereDate('appointment_date', \Carbon\Carbon::today())
                ->whereNotIn('appointment_status', ['cancelled', 'no_show', 'completed'])
                ->orderBy('appointment_time')
                ->get();
        @endphp
        
        @if($todayAppointments->count() > 0)
            <div class="timeline">
                @foreach($todayAppointments as $appointment)
                <div class="timeline-item mb-3">
                    <div class="d-flex">
                        <div class="timeline-time me-3">
                            <strong>{{ date('g:i A', strtotime($appointment->appointment_time)) }}</strong>
                        </div>
                        <div class="timeline-content flex-grow-1">
                            <div class="card">
                                <div class="card-body py-2">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong>{{ $appointment->customer->full_name }}</strong>
                                            <br>
                                            <small class="text-muted">
                                                {{ $appointment->vehicle->year }} {{ $appointment->vehicle->make }} • 
                                                {{ $appointment->appointment_type }}
                                            </small>
                                        </div>
                                        <div class="text-end">
                                            <span class="badge bg-{{ $appointment->status_color }}">
                                                {{ ucfirst(str_replace('_', ' ', $appointment->appointment_status)) }}
                                            </span>
                                            <br>
                                            @if($appointment->technician)
                                                <small class="text-muted">{{ $appointment->technician->name }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-4">
                <i class="fas fa-calendar-check fa-3x text-muted mb-3"></i>
                <p class="text-muted">No appointments scheduled for today</p>
            </div>
        @endif
    </div>
</div>
@endsection