@extends('layouts.app')

@section('title', 'Vehicle Inspections - Fix-It Auto Services')

@section('content')
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-car me-2"></i>Vehicle Inspections
            </h1>
            <p class="text-muted mb-0">Digital vehicle inspections with photo/video documentation</p>
        </div>
        <div>
            <a href="{{ route('inspections.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> New Inspection
            </a>
            <a href="{{ route('inspections.statistics') }}" class="btn btn-outline-primary ms-2">
                <i class="fas fa-chart-bar me-1"></i> Statistics
            </a>
        </div>
    </div>
</div>

<!-- Quick Stats -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body text-center">
                <h3 class="mb-0">{{ $stats['today'] ?? 0 }}</h3>
                <p class="mb-0">Today's Inspections</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body text-center">
                <h3 class="mb-0">{{ $stats['in_progress'] ?? 0 }}</h3>
                <p class="mb-0">In Progress</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body text-center">
                <h3 class="mb-0">{{ $stats['completed'] ?? 0 }}</h3>
                <p class="mb-0">Completed</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-danger text-white">
            <div class="card-body text-center">
                <h3 class="mb-0">{{ $stats['with_safety'] ?? 0 }}</h3>
                <p class="mb-0">Safety Concerns</p>
            </div>
        </div>
    </div>
</div>

<!-- Simple Table -->
<div class="card">
    <div class="card-body">
        @if($inspections->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Customer</th>
                            <th>Vehicle</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($inspections as $inspection)
                        <tr>
                            <td>INSP-{{ str_pad($inspection->id, 6, '0', STR_PAD_LEFT) }}</td>
                            <td>
                                @if($inspection->customer)
                                    {{ $inspection->customer->first_name }} {{ $inspection->customer->last_name }}
                                @else
                                    <span class="text-muted">No customer</span>
                                @endif
                            </td>
                            <td>
                                @if($inspection->vehicle)
                                    {{ $inspection->vehicle->year }} {{ $inspection->vehicle->make }} {{ $inspection->vehicle->model }}
                                @else
                                    <span class="text-muted">No vehicle</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $typeLabels = [
                                        'pre_service' => 'Pre-Service',
                                        'post_service' => 'Post-Service',
                                        'safety' => 'Safety',
                                        'comprehensive' => 'Comprehensive',
                                        'custom' => 'Custom',
                                    ];
                                @endphp
                                <span class="badge bg-primary">
                                    {{ $typeLabels[$inspection->inspection_type] ?? ucfirst($inspection->inspection_type) }}
                                </span>
                            </td>
                            <td>
                                @php
                                    $statusColors = [
                                        'draft' => 'secondary',
                                        'in_progress' => 'primary',
                                        'completed' => 'info',
                                        'approved' => 'success',
                                        'rejected' => 'warning',
                                        'cancelled' => 'danger',
                                    ];
                                @endphp
                                <span class="badge bg-{{ $statusColors[$inspection->inspection_status] ?? 'secondary' }}">
                                    {{ ucfirst(str_replace('_', ' ', $inspection->inspection_status)) }}
                                </span>
                            </td>
                            <td>{{ $inspection->created_at->format('M d, Y') }}</td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('inspections.show', $inspection) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                    @if($inspection->inspection_status == 'completed')
                                        <a href="{{ route('estimates.create', ['appointment_id' => $inspection->appointment_id ?? null, 'inspection_id' => $inspection->id]) }}" class="btn btn-sm btn-success">
                                            <i class="fas fa-file-invoice-dollar"></i> Create Estimate
                                        </a>
                                    @endif
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
                    Showing {{ $inspections->firstItem() }} to {{ $inspections->lastItem() }} of {{ $inspections->total() }} inspections
                </div>
                <div>
                    {{ $inspections->links() }}
                </div>
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-car fa-4x text-muted mb-3"></i>
                <h4 class="text-muted">No vehicle inspections found</h4>
                <p class="text-muted">Try adjusting your filters or create a new inspection</p>
                <a href="{{ route('inspections.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i> Create First Inspection
                </a>
            </div>
        @endif
    </div>
</div>
@endsection