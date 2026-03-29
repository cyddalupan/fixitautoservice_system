@extends('layouts.app')

@section('title', 'Vehicle Details')

@section('content')
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-car me-2"></i>Vehicle Details
            </h1>
            <p class="text-muted mb-0">{{ $vehicle->make }} {{ $vehicle->model }} {{ $vehicle->year }}</p>
        </div>
        <div>
            <a href="{{ route('vehicles.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> Add Another Vehicle
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

<div class="row">
    <!-- Vehicle Information -->
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-info-circle me-2"></i>Vehicle Information
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-sm">
                            <tr>
                                <th width="40%">Brand:</th>
                                <td>{{ $vehicle->make }}</td>
                            </tr>
                            <tr>
                                <th>Model:</th>
                                <td>{{ $vehicle->model }}</td>
                            </tr>
                            <tr>
                                <th>Year:</th>
                                <td>{{ $vehicle->year }}</td>
                            </tr>
                            <tr>
                                <th>Color:</th>
                                <td>{{ $vehicle->color ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Vehicle Type:</th>
                                <td>{{ $vehicle->vehicle_type ?? 'N/A' }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-sm">
                            <tr>
                                <th width="40%">License Plate:</th>
                                <td>
                                    @if($vehicle->license_plate)
                                        <span class="badge bg-light text-dark">{{ $vehicle->license_plate }}</span>
                                    @else
                                        N/A
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>VIN:</th>
                                <td>{{ $vehicle->vin ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Customer:</th>
                                <td>
                                    @if($vehicle->customer)
                                        <a href="{{ route('customers.show', $vehicle->customer) }}">
                                            {{ $vehicle->customer->first_name }} {{ $vehicle->customer->last_name }}
                                        </a>
                                    @else
                                        N/A
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Registered:</th>
                                <td>{{ $vehicle->created_at->format('M d, Y') }}</td>
                            </tr>
                            <tr>
                                <th>Last Updated:</th>
                                <td>{{ $vehicle->updated_at->format('M d, Y') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Service History -->
        <div class="card">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-history me-2"></i>Service History
                </h6>
            </div>
            <div class="card-body">
                @if($vehicle->serviceRecords && $vehicle->serviceRecords->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Service Type</th>
                                    <th>Description</th>
                                    <th>Cost</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($vehicle->serviceRecords->sortByDesc('service_date') as $record)
                                    <tr>
                                        <td>{{ $record->service_date->format('M d, Y') }}</td>
                                        <td>{{ $record->service_type }}</td>
                                        <td>{{ Str::limit($record->description, 50) }}</td>
                                        <td>${{ number_format($record->cost, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-info-circle fa-2x text-muted mb-3"></i>
                        <p class="text-muted">No service history recorded for this vehicle.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Quick Actions & Stats -->
    <div class="col-md-4">
        <!-- Vehicle Photo -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-camera me-2"></i>Vehicle Photo
                </h6>
            </div>
            <div class="card-body text-center">
                @if($vehicle->photo && $vehicle->photo_path)
                    <div class="mb-3">
                        <img src="{{ Storage::url($vehicle->photo_path) }}" 
                             alt="{{ $vehicle->make }} {{ $vehicle->model }}"
                             class="img-fluid rounded" 
                             style="max-height: 300px;">
                    </div>
                    <div class="text-muted small mb-3">
                        <p class="mb-1"><i class="fas fa-file-image me-1"></i> {{ $vehicle->photo }}</p>
                        <p class="mb-0">
                            <a href="{{ Storage::url($vehicle->photo_path) }}" 
                               target="_blank" 
                               class="text-decoration-none">
                                <i class="fas fa-external-link-alt me-1"></i> View Full Size
                            </a>
                        </p>
                    </div>
                    <div class="d-grid">
                        <a href="{{ route('vehicles.edit', $vehicle) }}" class="btn btn-outline-primary">
                            <i class="fas fa-edit me-2"></i> Edit Photo & Details
                        </a>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-car fa-3x text-muted mb-3"></i>
                        <p class="text-muted mb-3">No photo uploaded for this vehicle.</p>
                        <div class="d-grid">
                            <a href="{{ route('vehicles.edit', $vehicle) }}" class="btn btn-primary">
                                <i class="fas fa-camera me-2"></i> Add Vehicle Photo
                            </a>
                        </div>
                        <small class="text-muted mt-2 d-block">
                            <a href="{{ route('vehicles.edit', $vehicle) }}" class="text-decoration-none">
                                <i class="fas fa-edit me-1"></i> Edit vehicle details
                            </a>
                        </small>
                    </div>
                @endif
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-bolt me-2"></i>Quick Actions
                </h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route("appointments.create") }}?vehicle_id={{ $vehicle->id }}" 
                       class="btn btn-outline-primary">
                        <i class="fas fa-calendar-plus me-2"></i> Schedule Appointment
                    </a>
                    <a href="{{ route("work-orders.create") }}?vehicle_id={{ $vehicle->id }}" 
                       class="btn btn-outline-primary">
                        <i class="fas fa-wrench me-2"></i> Create Work Order
                    </a>
                    <a href="{{ route("inspections.create") }}?vehicle_id={{ $vehicle->id }}" 
                       class="btn btn-outline-primary">
                        <i class="fas fa-clipboard-check me-2"></i> Create Repair Order
                    </a>
                    <a href="{{ route("estimates.create") }}?vehicle_id={{ $vehicle->id }}" 
                       class="btn btn-outline-primary">
                        <i class="fas fa-file-invoice me-2"></i> Create Estimate
                    </a>
                </div>
            </div>
        </div>
        <!-- Vehicle Stats -->
        <div class="card">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-chart-bar me-2"></i>Vehicle Stats
                </h6>
            </div>
            <div class="card-body">
                <div class="text-center">
                    <div class="mb-3">
                        <div class="h4 mb-0">{{ $vehicle->serviceRecords->count() }}</div>
                        <div class="text-muted small">Total Services</div>
                    </div>
                    <div class="mb-3">
                        <div class="h4 mb-0">
                            ${{ number_format($vehicle->serviceRecords->sum('cost'), 2) }}
                        </div>
                        <div class="text-muted small">Total Service Cost</div>
                    </div>
                    <div class="mb-3">
                        <div class="h4 mb-0">{{ $vehicle->appointments->count() }}</div>
                        <div class="text-muted small">Total Appointments</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection