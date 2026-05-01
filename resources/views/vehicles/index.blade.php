@extends('layouts.app')

@section('title', 'All Vehicles')

@section('body-class', 'page-vehicles')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h4 class="mb-1" style="font-weight: 700; color: #0f172a;">Vehicles Overview</h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0" style="background: none; padding: 0;">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" style="color: #64748b; text-decoration: none;">Dashboard</a></li>
                            <li class="breadcrumb-item active" aria-current="page" style="color: #0f172a;">Vehicles</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('vehicle-brands.index') }}" class="btn btn-outline-secondary btn-sm" title="Vehicle Brands">
                        <i class="fas fa-tag"></i> Brands
                    </a>
                    <a href="{{ route('vehicle-models.index') }}" class="btn btn-outline-secondary btn-sm" title="Vehicle Models">
                        <i class="fas fa-car-side"></i> Models
                    </a>
                    <a href="{{ route('vehicle-colors.index') }}" class="btn btn-outline-secondary btn-sm" title="Vehicle Colors">
                        <i class="fas fa-palette"></i> Colors
                    </a>
                    <a href="{{ route('vehicles.create') }}" class="btn" style="background: #dc2626; color: #fff; border: none;">
                        <i class="fas fa-plus me-1"></i> Add Vehicle
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="stat-card-gradient stat-gradient-blue p-3" style="border-radius: 12px; height: 100%;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="mb-1" style="font-size: 0.8rem; opacity: 0.85; font-weight: 500;">Total Vehicles</p>
                        <h3 class="mb-0" style="font-weight: 700;">{{ number_format($totalVehicles) }}</h3>
                    </div>
                    <div class="stat-icon" style="opacity: 0.3;">
                        <i class="fas fa-car" style="font-size: 2.2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card-gradient stat-gradient-green p-3" style="border-radius: 12px; height: 100%;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="mb-1" style="font-size: 0.8rem; opacity: 0.85; font-weight: 500;">Active Vehicles</p>
                        <h3 class="mb-0" style="font-weight: 700;">{{ number_format($activeVehicles) }}</h3>
                    </div>
                    <div class="stat-icon" style="opacity: 0.3;">
                        <i class="fas fa-check-circle" style="font-size: 2.2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card-gradient stat-gradient-orange p-3" style="border-radius: 12px; height: 100%;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="mb-1" style="font-size: 0.8rem; opacity: 0.85; font-weight: 500;">With VIN</p>
                        <h3 class="mb-0" style="font-weight: 700;">{{ number_format($vehiclesWithVin) }}</h3>
                    </div>
                    <div class="stat-icon" style="opacity: 0.3;">
                        <i class="fas fa-barcode" style="font-size: 2.2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card-gradient stat-gradient-purple p-3" style="border-radius: 12px; height: 100%;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="mb-1" style="font-size: 0.8rem; opacity: 0.85; font-weight: 500;">With Engine No.</p>
                        <h3 class="mb-0" style="font-weight: 700;">{{ number_format($vehiclesWithEngineNo) }}</h3>
                    </div>
                    <div class="stat-icon" style="opacity: 0.3;">
                        <i class="fas fa-cog" style="font-size: 2.2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Card -->
    <div class="card" style="border: none; border-radius: 10px; box-shadow: 0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.1);">
        <div class="card-body p-4">
            <!-- Filter Bar -->
            <div class="d-flex flex-wrap align-items-center gap-2 mb-4">
                <form method="GET" action="{{ route('vehicles.index') }}" class="d-flex flex-wrap align-items-center gap-2" style="flex: 1;">
                    <div style="position: relative; flex: 1; min-width: 200px;">
                        <i class="fas fa-search" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 0.85rem;"></i>
                        <input type="text" class="form-control" name="search" 
                               placeholder="Search by make, model, plate, VIN..." 
                               value="{{ request('search') }}"
                               style="padding-left: 36px; border-radius: 8px; border: 1px solid #e2e8f0; font-size: 0.85rem;">
                    </div>
                    <select class="form-select" name="status" onchange="this.form.submit()" style="border-radius: 8px; border: 1px solid #e2e8f0; font-size: 0.85rem; width: auto; min-width: 140px;">
                        <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>All Status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active Only</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive Only</option>
                    </select>
                    <input type="hidden" name="search" value="{{ request('search') }}">
                    <button type="submit" class="btn" style="background: #dc2626; color: #fff; border: none; border-radius: 8px; font-size: 0.85rem;">
                        <i class="fas fa-filter me-1"></i> Filter
                    </button>
                    @if(request('search') || request('status') && request('status') != 'all')
                        <a href="{{ route('vehicles.index') }}" class="btn btn-outline-secondary" style="border-radius: 8px; font-size: 0.85rem;">
                            <i class="fas fa-times me-1"></i> Clear
                        </a>
                    @endif
                </form>
            </div>

            <!-- Vehicles Table -->
            <div class="table-responsive">
                <table class="table" style="margin-bottom: 0;">
                    <thead>
                        <tr style="border-bottom: 2px solid #f1f5f9;">
                            <th style="padding: 0.75rem; font-size: 0.75rem; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Plate Number</th>
                            <th style="padding: 0.75rem; font-size: 0.75rem; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Owner</th>
                            <th style="padding: 0.75rem; font-size: 0.75rem; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Make / Model</th>
                            <th style="padding: 0.75rem; font-size: 0.75rem; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Year</th>
                            <th style="padding: 0.75rem; font-size: 0.75rem; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Last Service</th>
                            <th style="padding: 0.75rem; font-size: 0.75rem; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Status</th>
                            <th style="padding: 0.75rem; font-size: 0.75rem; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; width: 80px; text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($vehicles as $vehicle)
                            <tr onclick="window.location.href='{{ route('vehicles.show', $vehicle) }}'"
                                style="border-bottom: 1px solid #f1f5f9; transition: background 0.15s; cursor: pointer;"
                                onmouseover="this.style.background='#f8fafc'"
                                onmouseout="this.style.background='transparent'">
                                <td style="padding: 0.75rem; vertical-align: middle;">
                                    <span style="font-weight: 600; font-size: 0.85rem; color: #0f172a;">{{ $vehicle->license_plate ?? '—' }}</span>
                                </td>
                                <td style="padding: 0.75rem; vertical-align: middle;">
                                    @if($vehicle->customer)
                                        <a href="{{ route('customers.show', $vehicle->customer) }}" style="text-decoration: none; color: #2563eb; font-weight: 500; font-size: 0.85rem;">
                                            {{ $vehicle->customer->full_name }}
                                        </a>
                                        <div style="font-size: 0.75rem; color: #94a3b8;">{{ $vehicle->customer->phone }}</div>
                                    @else
                                        <span style="color: #94a3b8; font-size: 0.85rem;">No Customer</span>
                                    @endif
                                </td>
                                <td style="padding: 0.75rem; vertical-align: middle;">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-car" style="color: #dc2626; margin-right: 8px; font-size: 0.9rem;"></i>
                                        <div>
                                            <div style="font-weight: 600; font-size: 0.85rem; color: #0f172a;">{{ $vehicle->make }} {{ $vehicle->model }}</div>
                                            <div style="font-size: 0.75rem; color: #94a3b8;">
                                                @if($vehicle->vin)
                                                    <span title="{{ $vehicle->vin }}">VIN: {{ substr($vehicle->vin, 0, 8) }}...</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td style="padding: 0.75rem; vertical-align: middle; font-size: 0.85rem; color: #334155;">{{ $vehicle->year }}</td>
                                <td style="padding: 0.75rem; vertical-align: middle;">
                                    @php
                                        $lastService = $vehicle->serviceRecords()->latest()->first();
                                    @endphp
                                    @if($lastService)
                                        <div style="font-size: 0.8rem; color: #334155;">{{ $lastService->service_date->format('M d, Y') }}</div>
                                        <div style="font-size: 0.7rem; color: #94a3b8;">{{ $lastService->service_type }}</div>
                                    @else
                                        <span style="font-size: 0.8rem; color: #94a3b8;">No service history</span>
                                    @endif
                                </td>
                                <td style="padding: 0.75rem; vertical-align: middle;">
                                    @if($vehicle->is_active)
                                        <span style="display: inline-block; padding: 0.25rem 0.6rem; border-radius: 50px; font-size: 0.7rem; font-weight: 500; background: #dcfce7; color: #166534;">Active</span>
                                    @else
                                        <span style="display: inline-block; padding: 0.25rem 0.6rem; border-radius: 50px; font-size: 0.7rem; font-weight: 500; background: #f1f5f9; color: #475569;">Inactive</span>
                                    @endif
                                </td>
                                <td style="padding: 0.75rem; vertical-align: middle; text-align: right;">
                                    <div class="dropdown">
                                        <button class="btn btn-sm" type="button" data-bs-toggle="dropdown" 
                                                style="border: none; border-radius: 8px; background: transparent; padding: 0.4rem 0.5rem; transition: all 0.15s;"
                                                aria-expanded="false"
                                                onclick="event.stopPropagation();"
                                                onmouseover="this.style.background='#f1f5f9'"
                                                onmouseout="this.style.background='transparent'">
                                            <i class="fas fa-ellipsis-v" style="color: #94a3b8; font-size: 1rem;"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end" style="border: none; border-radius: 10px; box-shadow: 0 4px 20px rgba(0,0,0,0.12); padding: 0.4rem; min-width: 180px;">
                                            <li>
                                                <a href="{{ route('vehicles.show', $vehicle) }}" class="dropdown-item" style="font-size: 0.85rem; padding: 0.5rem 0.75rem; border-radius: 6px; display: flex; align-items: center; gap: 8px;">
                                                    <i class="fas fa-eye" style="color: #0f172a; width: 16px; text-align: center;"></i>
                                                    <span>View Details</span>
                                                </a>
                                            </li>
                                            <li>
                                                <a href="{{ route('vehicles.edit', $vehicle) }}" class="dropdown-item" style="font-size: 0.85rem; padding: 0.5rem 0.75rem; border-radius: 6px; display: flex; align-items: center; gap: 8px;">
                                                    <i class="fas fa-pen-to-square" style="color: #0f172a; width: 16px; text-align: center;"></i>
                                                    <span>Edit Vehicle</span>
                                                </a>
                                            </li>
                                            @if($vehicle->customer)
                                                <li><hr class="dropdown-divider" style="margin: 0.25rem 0;"></li>
                                                <li>
                                                    <a href="{{ route('customers.show', $vehicle->customer) }}" class="dropdown-item" style="font-size: 0.85rem; padding: 0.5rem 0.75rem; border-radius: 6px; display: flex; align-items: center; gap: 8px;">
                                                        <i class="fas fa-user" style="color: #2563eb; width: 16px; text-align: center;"></i>
                                                        <span>View Customer</span>
                                                    </a>
                                                </li>
                                            @endif
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" style="padding: 3rem 2rem; text-align: center;">
                                    <i class="fas fa-car" style="font-size: 3rem; color: #cbd5e1; margin-bottom: 1rem; display: block;"></i>
                                    <h5 style="font-weight: 600; color: #0f172a; margin-bottom: 0.5rem;">No vehicles found</h5>
                                    <p style="color: #94a3b8; font-size: 0.85rem; margin-bottom: 1.25rem;">
                                        @if(request('search') || request('status'))
                                            Try adjusting your search or filter criteria
                                        @else
                                            No vehicles have been added yet
                                        @endif
                                    </p>
                                    <a href="{{ route('vehicles.create') }}" class="btn" style="background: #dc2626; color: #fff; border: none; border-radius: 8px;">
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
                <div class="d-flex justify-content-between align-items-center mt-3 pt-3" style="border-top: 1px solid #f1f5f9;">
                    <div style="font-size: 0.8rem; color: #64748b;">
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
@endsection

@push('scripts')
<script>
    // Auto-submit search on enter
    document.querySelector('input[name="search"]')?.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            this.form.submit();
        }
    });
</script>
@endpush
