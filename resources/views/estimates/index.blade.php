@extends('layouts.app')

@section('title', 'Estimates - Fix-It Auto Services')

@push('styles')
<style>
.module-estimates {
    --module-primary: #8b5cf6;
    --module-primary-dark: #7c3aed;
    --module-primary-light: #ede9fe;
    --module-primary-subtle: #f5f3ff;
    --module-active: #8b5cf6;
    --module-active-dark: #7c3aed;
    --module-active-light: #ede9fe;
}
</style>
@endpush

@section('content')
<div class="container-fluid module-estimates">
    <!-- Page Header -->
    <div class="page-module-header">
        <div class="d-flex flex-wrap justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-3">
                <span class="module-badge"><i class="fas fa-file-invoice-dollar"></i></span>
                <div>
                    <h1 class="module-title">Estimates</h1>
                    <p class="module-subtitle">Create and manage customer repair estimates</p>
                </div>
            </div>
            <div class="d-flex gap-2 mt-2 mt-sm-0">
                <a href="{{ route('estimates.create') }}" class="btn-create">
                    <i class="fas fa-plus"></i> New Estimate
                </a>
                <a href="{{ route('estimates.statistics') }}" class="btn-secondary-action">
                    <i class="fas fa-chart-bar"></i> Statistics
                </a>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-card-body">
                <div class="stat-card-icon"><i class="fas fa-file-invoice"></i></div>
                <div class="stat-card-info">
                    <h3>{{ $stats['total_count'] ?? $estimates->total() }}</h3>
                    <p>Total Estimates</p>
                </div>
            </div>
        </div>
        <div class="stat-card" style="border-left-color:#f59e0b;">
            <div class="stat-card-body">
                <div class="stat-card-icon" style="background:#fef3c7;color:#d97706;">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-card-info">
                    <h3>{{ $stats['pending_count'] ?? 0 }}</h3>
                    <p>Pending</p>
                </div>
            </div>
        </div>
        <div class="stat-card" style="border-left-color:#10b981;">
            <div class="stat-card-body">
                <div class="stat-card-icon" style="background:#d1fae5;color:#059669;">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-card-info">
                    <h3>{{ $stats['approved_count'] ?? 0 }}</h3>
                    <p>Approved</p>
                </div>
            </div>
        </div>
        <div class="stat-card" style="border-left-color:#ef4444;">
            <div class="stat-card-body">
                <div class="stat-card-icon" style="background:#fee2e2;color:#dc2626;">
                    <i class="fas fa-ban"></i>
                </div>
                <div class="stat-card-info">
                    <h3>{{ $stats['rejected_count'] ?? 0 }}</h3>
                    <p>Rejected</p>
                </div>
            </div>
        </div>
        <div class="stat-card" style="border-left-color:#f97316;">
            <div class="stat-card-body">
                <div class="stat-card-icon" style="background:#fff7ed;color:#ea580c;">
                    <i class="fas fa-tools"></i>
                </div>
                <div class="stat-card-info">
                    <h3>{{ $stats['converted_count'] ?? 0 }}</h3>
                    <p>Converted to Job</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="filter-bar">
        <form method="GET" action="{{ route('estimates.index') }}" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-control" placeholder="Search estimates..."
                       value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="sent" {{ request('status') == 'sent' ? 'selected' : '' }}>Sent</option>
                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                    <option value="converted" {{ request('status') == 'converted' ? 'selected' : '' }}>Converted</option>
                    <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Expired</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Date From</label>
                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">Date To</label>
                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
            </div>
            <div class="col-md-3 d-flex gap-2" style="padding-top:1.5rem;">
                <button type="submit" class="btn-filter-primary">
                    <i class="fas fa-filter"></i> Filter
                </button>
                <a href="{{ route('estimates.index') }}" class="btn-filter-outline">
                    <i class="fas fa-redo"></i> Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Estimates Table -->
    <div class="main-card">
        <div class="main-card-body">
            @if($estimates->count() > 0)
                <div class="table-responsive">
                    <table class="table-fixit">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Customer</th>
                                <th>Vehicle</th>
                                <th>Service Type</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($estimates as $estimate)
                            <tr>
                                <td><strong>{{ $estimate->estimate_number ?? $estimate->id }}</strong></td>
                                <td>
                                    @if($estimate->customer)
                                        <strong>{{ $estimate->customer->full_name }}</strong>
                                        <br>
                                        <small style="color:#94a3b8;font-size:0.775rem;">
                                            <i class="fas fa-phone me-1"></i>{{ $estimate->customer->phone }}
                                        </small>
                                    @else
                                        <span style="color:#94a3b8;">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    @if($estimate->vehicle)
                                        <span style="font-size:0.85rem;">
                                            {{ $estimate->vehicle->year }} {{ $estimate->vehicle->make }} {{ $estimate->vehicle->model }}
                                        </span>
                                        <br>
                                        <small style="color:#94a3b8;font-size:0.775rem;">{{ $estimate->vehicle->license_plate }}</small>
                                    @else
                                        <span style="color:#94a3b8;">No vehicle</span>
                                    @endif
                                </td>
                                <td>
                                    @if($estimate->service_type)
                                        @php
                                            $raw = $estimate->service_type;
                                            $decoded = is_array($raw) ? $raw : json_decode($raw, true);
                                            $serviceTypes = is_array($decoded) ? $decoded : (array)($decoded ?? $raw);
                                        @endphp
                                        @foreach($serviceTypes as $st)
                                            <span class="status-badge status-badge-info" style="margin-bottom:2px;display:inline-block;">
                                                {{ config('service-types.list.' . $st . '.name', ucfirst($st)) }}
                                            </span>
                                            @if(!$loop->last)<br>@endif
                                        @endforeach
                                    @else
                                        <span style="color:#94a3b8;">—</span>
                                    @endif
                                </td>
                                <td>
                                    <strong>{{ number_format($estimate->total_amount, 2) }}</strong>
                                </td>
                                <td>
                                    @php 
                                        $estStatusColors = [
                                            'draft' => 'secondary',
                                            'pending' => 'warning',
                                            'sent' => 'info',
                                            'approved' => 'success',
                                            'rejected' => 'danger',
                                            'converted' => 'info',
                                            'expired' => 'secondary',
                                        ];
                                        $estColor = $estStatusColors[$estimate->status] ?? 'secondary';
                                    @endphp
                                    <span class="status-badge status-badge-{{ $estColor }}">
                                        <i class="fas fa-circle" style="font-size:0.5rem;"></i>
                                        {{ ucfirst($estimate->status) }}
                                    </span>
                                </td>
                                <td>
                                    <span style="font-size:0.85rem;">{{ $estimate->created_at->format('M d, Y') }}</span>
                                </td>
                                <td>
                                    <div class="action-group">
                                        <a href="{{ route('estimates.show', $estimate) }}" class="btn-action" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if($estimate->status == 'draft')
                                        <a href="{{ route('estimates.edit', $estimate) }}" class="btn-action" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('estimates.send', $estimate) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn-action" style="color:#8b5cf6;border-color:#ddd6fe;" title="Send to Customer">
                                                <i class="fas fa-paper-plane"></i>
                                            </button>
                                        </form>
                                        @endif
                                        @if($estimate->status == 'approved')
                                        <form action="{{ route('work-orders.create') }}" method="GET" class="d-inline">
                                            <input type="hidden" name="estimate_id" value="{{ $estimate->id }}">
                                            <button type="submit" class="btn-action" style="color:#f97316;border-color:#fed7aa;" title="Convert to Job Order">
                                                <i class="fas fa-clipboard-check"></i>
                                            </button>
                                        </form>
                                        @endif
                                        <form action="{{ route('estimates.destroy', $estimate) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this estimate?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-action" style="border-color:#fde68a;color:#d97706;" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                        <a href="{{ route('estimates.print', $estimate) }}" class="btn-action" title="Print Estimate" style="color:#dc2626;border-color:#fecaca;" target="_blank">
                                            <i class="fas fa-print"></i>
                                        </a>
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
                        Showing {{ $estimates->firstItem() }} to {{ $estimates->lastItem() }} of {{ $estimates->total() }} estimates
                    </div>
                    <div>
                        {{ $estimates->links() }}
                    </div>
                </div>
            @else
                <div class="empty-state-module">
                    <div class="empty-state-icon">
                        <i class="fas fa-file-invoice"></i>
                    </div>
                    <h4>No estimates found</h4>
                    <p>Try adjusting your filters or create a new estimate to get started.</p>
                    <a href="{{ route('estimates.create') }}" class="btn-create">
                        <i class="fas fa-plus"></i> Create First Estimate
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
    // Any estimate-specific JS
});
</script>
@endpush
