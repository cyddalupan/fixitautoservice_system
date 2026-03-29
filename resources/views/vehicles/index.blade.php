@extends('layouts.app')

@section('title', 'All Vehicles')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <h4 class="mb-0">All Vehicles</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Vehicles</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Vehicle Registry</h5>
                        <div class="d-flex gap-2">
                            <a href="{{ route('vehicles.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-1"></i> Add New Vehicle
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Search and Filter -->
                    <div class="row mb-4">
                        <div class="col-md-8">
                            <form method="GET" action="{{ route('vehicles.index') }}" class="d-flex">
                                <div class="input-group">
                                    <input type="text" class="form-control" name="search" 
                                           placeholder="Search by make, model, plate, VIN, engine no, or customer..." 
                                           value="{{ request('search') }}">
                                    <button class="btn btn-outline-secondary" type="submit">
                                        <i class="fas fa-search"></i>
                                    </button>
                                    @if(request('search') || request('status'))
                                        <a href="{{ route('vehicles.index') }}" class="btn btn-outline-danger">
                                            <i class="fas fa-times"></i> Clear
                                        </a>
                                    @endif
                                </div>
                            </form>
                        </div>
                        <div class="col-md-4">
                            <form method="GET" action="{{ route('vehicles.index') }}" class="d-flex">
                                <select class="form-select" name="status" onchange="this.form.submit()">
                                    <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>All Status</option>
                                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active Only</option>
                                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive Only</option>
                                </select>
                                <input type="hidden" name="search" value="{{ request('search') }}">
                            </form>
                        </div>
                    </div>

                    <!-- Stats Cards -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="card-title mb-1">Total Vehicles</h6>
                                            <h3 class="mb-0">{{ number_format($totalVehicles) }}</h3>
                                        </div>
                                        <i class="fas fa-car fa-2x opacity-50"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="card-title mb-1">Active Vehicles</h6>
                                            <h3 class="mb-0">{{ number_format($activeVehicles) }}</h3>
                                        </div>
                                        <i class="fas fa-check-circle fa-2x opacity-50"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="card-title mb-1">With VIN</h6>
                                            <h3 class="mb-0">{{ number_format($vehiclesWithVin) }}</h3>
                                        </div>
                                        <i class="fas fa-barcode fa-2x opacity-50"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-dark">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="card-title mb-1">With Engine No.</h6>
                                            <h3 class="mb-0">{{ number_format($vehiclesWithEngineNo) }}</h3>
                                        </div>
                                        <i class="fas fa-cog fa-2x opacity-50"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Vehicles Table -->
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Vehicle</th>
                                    <th>Customer</th>
                                    <th>Details</th>
                                    <th>Status</th>
                                    <th>Last Service</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($vehicles as $vehicle)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="flex-shrink-0">
                                                    <i class="fas fa-car fa-2x text-primary"></i>
                                                </div>
                                                <div class="flex-grow-1 ms-3">
                                                    <h6 class="mb-0">{{ $vehicle->make }} {{ $vehicle->model }}</h6>
                                                    <small class="text-muted">Year: {{ $vehicle->year }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            @if($vehicle->customer)
                                                <a href="{{ route('customers.show', $vehicle->customer) }}" class="text-decoration-none">
                                                    <strong>{{ $vehicle->customer->full_name }}</strong><br>
                                                    <small class="text-muted">{{ $vehicle->customer->phone }}</small>
                                                </a>
                                            @else
                                                <span class="text-muted">No Customer</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="small">
                                                @if($vehicle->license_plate)
                                                    <div><strong>Plate:</strong> {{ $vehicle->license_plate }}</div>
                                                @endif
                                                @if($vehicle->vin)
                                                    <div><strong>VIN:</strong> <code>{{ $vehicle->vin }}</code></div>
                                                @endif
                                                @if($vehicle->engine_no)
                                                    <div><strong>Engine No:</strong> {{ $vehicle->engine_no }}</div>
                                                @endif
                                                @if($vehicle->color)
                                                    <div><strong>Color:</strong> {{ $vehicle->color }}</div>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            @if($vehicle->is_active)
                                                <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-secondary">Inactive</span>
                                            @endif
                                        </td>
                                        <td>
                                            @php
                                                $lastService = $vehicle->serviceRecords()->latest()->first();
                                            @endphp
                                            @if($lastService)
                                                <div class="small">
                                                    {{ $lastService->service_date->format('M d, Y') }}<br>
                                                    <small class="text-muted">{{ $lastService->service_type }}</small>
                                                </div>
                                            @else
                                                <span class="text-muted">No service history</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('vehicles.show', $vehicle) }}" class="btn btn-outline-primary" title="View">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('vehicles.edit', $vehicle) }}" class="btn btn-outline-secondary" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                @if($vehicle->customer)
                                                    <a href="{{ route('customers.show', $vehicle->customer) }}" class="btn btn-outline-info" title="View Customer">
                                                        <i class="fas fa-user"></i>
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4">
                                            <i class="fas fa-car fa-3x text-muted mb-3"></i>
                                            <h5 class="text-muted">No vehicles found</h5>
                                            <p class="text-muted">
                                                @if(request('search') || request('status'))
                                                    Try adjusting your search or filter criteria
                                                @else
                                                    No vehicles have been added yet
                                                @endif
                                            </p>
                                            <a href="{{ route('vehicles.create') }}" class="btn btn-primary">
                                                <i class="fas fa-plus me-1"></i> Add First Vehicle
                                            </a>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($vehicles->hasPages())
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div class="text-muted">
                                Showing {{ $vehicles->firstItem() }} to {{ $vehicles->lastItem() }} of {{ $vehicles->total() }} vehicles
                            </div>
                            <div>
                                {{ $vehicles->links() }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Auto-submit search on enter
    document.querySelector('input[name="search"]').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            this.form.submit();
        }
    });
</script>
@endpush