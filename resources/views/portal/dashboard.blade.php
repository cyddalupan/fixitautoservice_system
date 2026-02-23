@extends('layouts.app')

@section('title', 'Customer Portal Dashboard - Fix-It Auto Services')

@section('content')
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-tachometer-alt me-2"></i>Customer Portal Dashboard
            </h1>
            <p class="text-muted mb-0">Welcome back, {{ $customer->first_name }}!</p>
        </div>
        <div>
            <div class="btn-group">
                <a href="{{ route('portal.profile') }}" class="btn btn-outline-primary">
                    <i class="fas fa-user me-1"></i> My Profile
                </a>
                <a href="{{ route('portal.logout') }}" class="btn btn-outline-danger" 
                   onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    <i class="fas fa-sign-out-alt me-1"></i> Logout
                </a>
                <form id="logout-form" action="{{ route('portal.logout') }}" method="POST" class="d-none">
                    @csrf
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Quick Stats -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50 mb-0">Vehicles</h6>
                        <h3 class="mb-0">{{ $customer->vehicles->count() }}</h3>
                    </div>
                    <i class="fas fa-car fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50 mb-0">Upcoming Appointments</h6>
                        <h3 class="mb-0">{{ $customer->appointments()->where('status', 'scheduled')->count() }}</h3>
                    </div>
                    <i class="fas fa-calendar-check fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50 mb-0">Unread Messages</h6>
                        <h3 class="mb-0">{{ $unreadMessages }}</h3>
                    </div>
                    <i class="fas fa-envelope fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50 mb-0">Loyalty Points</h6>
                        <h3 class="mb-0">{{ auth()->guard('portal')->user()->loyalty_points_balance }}</h3>
                    </div>
                    <i class="fas fa-star fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Recent Appointments -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-calendar me-2"></i>Recent Appointments
                </h6>
                <a href="{{ route('portal.appointments') }}" class="btn btn-sm btn-outline-primary">
                    View All
                </a>
            </div>
            <div class="card-body">
                @if($recentAppointments->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($recentAppointments as $appointment)
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1">{{ $appointment->service_type }}</h6>
                                        <small class="text-muted">
                                            <i class="fas fa-car me-1"></i>
                                            {{ $appointment->vehicle->year }} {{ $appointment->vehicle->make }} {{ $appointment->vehicle->model }}
                                        </small>
                                        <br>
                                        <small class="text-muted">
                                            <i class="fas fa-clock me-1"></i>
                                            {{ $appointment->appointment_date->format('M j, Y') }} at {{ $appointment->appointment_time }}
                                        </small>
                                    </div>
                                    <span class="badge bg-{{ $appointment->status === 'scheduled' ? 'success' : 'secondary' }}">
                                        {{ ucfirst($appointment->status) }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-calendar fa-3x text-muted mb-3"></i>
                        <p class="text-muted mb-0">No appointments found</p>
                        <a href="{{ route('portal.appointments.create') }}" class="btn btn-primary btn-sm mt-2">
                            <i class="fas fa-plus me-1"></i> Schedule Appointment
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
    
    <!-- Recent Work Orders -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-clipboard-list me-2"></i>Recent Work Orders
                </h6>
                <a href="{{ route('portal.work-orders') }}" class="btn btn-sm btn-outline-primary">
                    View All
                </a>
            </div>
            <div class="card-body">
                @if($recentWorkOrders->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($recentWorkOrders as $workOrder)
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1">WO-{{ $workOrder->id }}</h6>
                                        <small class="text-muted">
                                            <i class="fas fa-car me-1"></i>
                                            {{ $workOrder->vehicle->year }} {{ $workOrder->vehicle->make }} {{ $workOrder->vehicle->model }}
                                        </small>
                                        <br>
                                        <small class="text-muted">
                                            <i class="fas fa-dollar-sign me-1"></i>
                                            ${{ number_format($workOrder->total_cost, 2) }}
                                        </small>
                                    </div>
                                    <span class="badge bg-{{ $workOrder->status === 'completed' ? 'success' : ($workOrder->status === 'in_progress' ? 'warning' : 'secondary') }}">
                                        {{ ucfirst(str_replace('_', ' ', $workOrder->status)) }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                        <p class="text-muted mb-0">No work orders found</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <!-- Quick Actions -->
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-bolt me-2"></i>Quick Actions
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <a href="{{ route('portal.appointments.create') }}" class="btn btn-outline-primary w-100 h-100 py-3">
                            <i class="fas fa-calendar-plus fa-2x mb-2"></i>
                            <br>
                            Schedule Appointment
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="{{ route('portal.service-requests.create') }}" class="btn btn-outline-success w-100 h-100 py-3">
                            <i class="fas fa-tools fa-2x mb-2"></i>
                            <br>
                            Request Service
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="{{ route('portal.messages') }}" class="btn btn-outline-info w-100 h-100 py-3">
                            <i class="fas fa-envelope fa-2x mb-2"></i>
                            <br>
                            View Messages
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="{{ route('portal.documents') }}" class="btn btn-outline-warning w-100 h-100 py-3">
                            <i class="fas fa-file-alt fa-2x mb-2"></i>
                            <br>
                            My Documents
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- My Vehicles -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-car me-2"></i>My Vehicles
                </h6>
                <a href="{{ route('portal.vehicles') }}" class="btn btn-sm btn-outline-primary">
                    View All
                </a>
            </div>
            <div class="card-body">
                @if($customer->vehicles->count() > 0)
                    <div class="row">
                        @foreach($customer->vehicles as $vehicle)
                            <div class="col-md-4 mb-3">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <h6 class="card-title">
                                            {{ $vehicle->year }} {{ $vehicle->make }} {{ $vehicle->model }}
                                        </h6>
                                        <p class="card-text text-muted small">
                                            <i class="fas fa-id-card me-1"></i> {{ $vehicle->license_plate ?? 'No Plate' }}<br>
                                            <i class="fas fa-gas-pump me-1"></i> {{ $vehicle->fuel_type ?? 'N/A' }}<br>
                                            <i class="fas fa-cog me-1"></i> {{ $vehicle->transmission ?? 'N/A' }}
                                        </p>
                                        <div class="d-flex justify-content-between">
                                            <a href="{{ route('portal.vehicles.show', $vehicle) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye me-1"></i> View
                                            </a>
                                            <a href="{{ route('portal.vehicles.service-history', $vehicle) }}" class="btn btn-sm btn-outline-secondary">
                                                <i class="fas fa-history me-1"></i> History
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-car fa-3x text-muted mb-3"></i>
                        <p class="text-muted mb-0">No vehicles registered</p>
                        <p class="text-muted small">Contact the shop to add your vehicles</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection