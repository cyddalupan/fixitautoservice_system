@extends('layouts.app')

@section('title', 'Repair Orders - Fix-It Auto Services')

@push('styles')
<style>
.module-inspections {
    --module-primary: #0ea5e9;
    --module-primary-dark: #0284c7;
    --module-primary-light: #e0f2fe;
    --module-primary-subtle: #f0f9ff;
    --module-active: #0ea5e9;
    --module-active-dark: #0284c7;
    --module-active-light: #e0f2fe;
}
</style>
@endpush

@section('content')
<div class="container-fluid module-inspections">
    <!-- Page Header -->
    <div class="page-module-header">
        <div class="d-flex flex-wrap justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-3">
                <span class="module-badge"><i class="fas fa-tools"></i></span>
                <div>
                    <h1 class="module-title">Repair Orders</h1>
                    <p class="module-subtitle">Vehicle inspections and repair order management</p>
                </div>
            </div>
            <div class="d-flex gap-2 mt-2 mt-sm-0">
                <a href="{{ route('inspections.create') }}" class="btn-create">
                    <i class="fas fa-plus"></i> New Repair Order
                </a>
                <a href="{{ route('inspections.statistics') }}" class="btn-secondary-action">
                    <i class="fas fa-chart-bar"></i> Statistics
                </a>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-card-body">
                <div class="stat-card-icon"><i class="fas fa-clipboard-list"></i></div>
                <div class="stat-card-info">
                    <h3>{{ $stats['today'] ?? $inspections->count() }}</h3>
                    <p>Today's Inspections</p>
                </div>
            </div>
        </div>
        <div class="stat-card" style="border-left-color:#f59e0b;">
            <div class="stat-card-body">
                <div class="stat-card-icon" style="background:#fef3c7;color:#d97706;">
                    <i class="fas fa-hourglass-half"></i>
                </div>
                <div class="stat-card-info">
                    <h3>{{ $stats['pending'] ?? 0 ?? 0 }}</h3>
                    <p>Pending</p>
                </div>
            </div>
        </div>
        <div class="stat-card" style="border-left-color:#0ea5e9;">
            <div class="stat-card-body">
                <div class="stat-card-icon" style="background:#e0f2fe;color:#0284c7;">
                    <i class="fas fa-spinner"></i>
                </div>
                <div class="stat-card-info">
                    <h3>{{ $stats['in_progress'] ?? 0 }}</h3>
                    <p>In Progress</p>
                </div>
            </div>
        </div>
        <div class="stat-card" style="border-left-color:#10b981;">
            <div class="stat-card-body">
                <div class="stat-card-icon" style="background:#d1fae5;color:#059669;">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-card-info">
                    <h3>{{ $stats['completed'] ?? 0 }}</h3>
                    <p>Completed</p>
                </div>
            </div>
        </div>
        <div class="stat-card" style="border-left-color:#8b5cf6;">
            <div class="stat-card-body">
                <div class="stat-card-icon" style="background:#ede9fe;color:#7c3aed;">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <div class="stat-card-info">
                    <h3>{{ $stats['with_safety'] ?? 0 }}</h3>
                    <p>Safety Inspections</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="filter-bar">
        <form method="GET" action="{{ route('inspections.index') }}" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-control" placeholder="Search customers, vehicles..." 
                       value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Type</label>
                <select name="type" class="form-select">
                    <option value="">All Types</option>
                    <option value="safety" {{ request('type') == 'safety' ? 'selected' : '' }}>Safety</option>
                    <option value="diagnostic" {{ request('type') == 'diagnostic' ? 'selected' : '' }}>Diagnostic</option>
                    <option value="comprehensive" {{ request('type') == 'comprehensive' ? 'selected' : '' }}>Comprehensive</option>
                    <option value="emissions" {{ request('type') == 'emissions' ? 'selected' : '' }}>Emissions</option>
                    <option value="pre_purchase" {{ request('type') == 'pre_purchase' ? 'selected' : '' }}>Pre-Purchase</option>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2" style="padding-top:1.5rem;">
                <button type="submit" class="btn-filter-primary">
                    <i class="fas fa-filter"></i> Filter
                </button>
                <a href="{{ route('inspections.index') }}" class="btn-filter-outline">
                    <i class="fas fa-redo"></i> Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Inspections Table -->
    <div class="main-card">
        <div class="main-card-body">
            @if($inspections->count() > 0)
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <span class="text-muted">{{ $inspections->count() }} repair orders</span>
                    </div>
                    <div>
                        @if($inspections->whereNull('viewed_at')->count() > 0)
                            <button class="btn-mark-all-read" id="markAllReadBtn" onclick="markAllAsRead('inspections', this)">
                                <i class="fas fa-check-double"></i> Mark All as Read
                            </button>
                        @endif
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table-fixit" id="inspectionsTable">
                        <thead>
                            <tr>
                                <th style="width:30px;"></th>
                                <th>#</th>
                                <th>Customer</th>
                                <th>Vehicle</th>
                                <th>Inspection Type</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($inspections as $inspection)
                            <tr class="{{ $inspection->viewed_at === null ? 'tr-unread' : '' }}" data-id="{{ $inspection->id }}">
                                <td>
                                    @if($inspection->viewed_at === null)
                                        <span class="unread-dot" title="New"></span>
                                    @endif
                                </td>
                                <td>
                                    <span class="{{ $inspection->viewed_at === null ? 'unread-primary-text' : '' }}">
                                        <strong>{{ $inspection->id }}</strong>
                                        @if($inspection->viewed_at === null)
                                            <span class="badge-new-record">NEW</span>
                                        @endif
                                    </span>
                                </td>
                                <td>
                                    @if($inspection->customer)
                                        <strong>{{ $inspection->customer->full_name }}</strong>
                                        <br>
                                        <small style="color:#94a3b8;font-size:0.775rem;">
                                            <i class="fas fa-phone me-1"></i>{{ $inspection->customer->phone }}
                                        </small>
                                    @else
                                        <span style="color:#94a3b8;">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    @if($inspection->vehicle)
                                        <span style="font-size:0.85rem;">
                                            {{ $inspection->vehicle->year }} {{ $inspection->vehicle->make }} {{ $inspection->vehicle->model }}
                                        </span>
                                        <br>
                                        <small style="color:#94a3b8;font-size:0.775rem;">{{ $inspection->vehicle->license_plate }}</small>
                                    @else
                                        <span style="color:#94a3b8;">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    @if($inspection->service_type)
                                        @php
                                            $raw = $inspection->service_type;
                                            $decoded = is_array($raw) ? $raw : json_decode($raw, true);
                                            $serviceTypes = is_array($decoded) ? $decoded : (array)($decoded ?? $raw);
                                        @endphp
                                        @foreach($serviceTypes as $st)
                                            <span class="status-badge status-badge-info" style="margin-bottom:2px;display:inline-block;">
                                                {{ config('service-types.list.' . $st . '.name', ucfirst($st)) }}
                                            </span>
                                            @if(!$loop->last)<br>@endif
                                        @endforeach
                                    @elseif($inspection->inspectionType)
                                        <span class="status-badge status-badge-info">{{ $inspection->inspectionType }}</span>
                                    @else
                                        <span style="color:#94a3b8;">—</span>
                                    @endif
                                </td>
                                <td>
                                    @php 
                                        $statusColors = [
                                            'pending' => 'warning',
                                            'in_progress' => 'info',
                                            'completed' => 'success',
                                            'cancelled' => 'danger',
                                        ];
                                        $statusColor = $statusColors[$inspection->status] ?? 'secondary';
                                    @endphp
                                    <span class="status-badge status-badge-{{ $statusColor }}">
                                        <i class="fas fa-circle" style="font-size:0.5rem;"></i>
                                        {{ ucfirst(str_replace('_', ' ', $inspection->status)) }}
                                    </span>
                                </td>
                                <td>
                                    <span style="font-size:0.85rem;">{{ $inspection->created_at->format('M d, Y') }}</span>
                                    <br>
                                    <small style="color:#94a3b8;font-size:0.75rem;">{{ $inspection->created_at->format('h:i A') }}</small>
                                </td>
                                <td>
                                    <div class="action-group">
                                        <a href="{{ route('inspections.show', $inspection) }}" class="btn-action" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('inspections.edit', $inspection) }}" class="btn-action" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('inspections.destroy', $inspection) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this inspection?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-action" style="border-color:#fde68a;color:#d97706;" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <div class="d-flex justify-content-between align-items-center px-3 py-3 border-top">
                    <div class="pagination-info">
                        Showing {{ $inspections->firstItem() }} to {{ $inspections->lastItem() }} of {{ $inspections->total() }} inspections
                    </div>
                    <div>
                        {{ $inspections->links() }}
                    </div>
                </div>
            @else
                <div class="empty-state-module">
                    <div class="empty-state-icon">
                        <i class="fas fa-tools"></i>
                    </div>
                    <h4>No inspections found</h4>
                    <p>Try adjusting your filters or create a new inspection to get started.</p>
                    <a href="{{ route('inspections.create') }}" class="btn-create">
                        <i class="fas fa-plus"></i> New Repair Order
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const table = document.getElementById('inspectionsTable');
    if (table) {
        initUnreadSystem(table, 'inspections', { dataAttr: 'data-id', markAllBtnId: 'markAllReadBtn' });
    }
});
</script>
@endpush
