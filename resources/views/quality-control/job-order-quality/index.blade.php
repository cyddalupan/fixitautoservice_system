@extends('layouts.app')

@section('title', 'Work Order Quality Checks')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col">
                <h1 class="h3 mb-0">
                    <i class="fas fa-clipboard-check text-primary me-2"></i>
                    Work Order Quality Checks
                </h1>
                <p class="text-muted mb-0">Monitor and approve quality checks for work orders</p>
            </div>
            <div class="col-auto">
                <a href="{{ route('work-order-quality.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i> New Quality Check
                </a>
                <a href="{{ route('work-order-quality.export') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-download me-1"></i> Export
                </a>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card stat-card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-subtitle mb-2">Pending Approval</h6>
                            <h2 class="card-title mb-0">{{ $pendingCount }}</h2>
                        </div>
                        <div class="card-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-subtitle mb-2">Approved</h6>
                            <h2 class="card-title mb-0">{{ $approvedCount }}</h2>
                        </div>
                        <div class="card-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-subtitle mb-2">Rejected</h6>
                            <h2 class="card-title mb-0">{{ $rejectedCount }}</h2>
                        </div>
                        <div class="card-icon">
                            <i class="fas fa-times-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-subtitle mb-2">Avg Score</h6>
                            <h2 class="card-title mb-0">{{ number_format($averageScore, 1) }}/100</h2>
                        </div>
                        <div class="card-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('work-order-quality.index') }}" class="row g-3">
                <div class="col-md-2">
                    <label for="status" class="form-label">Status</label>
                    <select name="status" id="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="technician_id" class="form-label">Technician</label>
                    <select name="technician_id" id="technician_id" class="form-select">
                        <option value="">All Technicians</option>
                        @foreach($technicians as $tech)
                            <option value="{{ $tech->id }}" {{ request('technician_id') == $tech->id ? 'selected' : '' }}>
                                {{ $tech->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="supervisor_id" class="form-label">Supervisor</label>
                    <select name="supervisor_id" id="supervisor_id" class="form-select">
                        <option value="">All Supervisors</option>
                        @foreach($supervisors as $sup)
                            <option value="{{ $sup->id }}" {{ request('supervisor_id') == $sup->id ? 'selected' : '' }}>
                                {{ $sup->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="start_date" class="form-label">Start Date</label>
                    <input type="date" name="start_date" id="start_date" class="form-control" 
                           value="{{ request('start_date') }}">
                </div>
                <div class="col-md-2">
                    <label for="end_date" class="form-label">End Date</label>
                    <input type="date" name="end_date" id="end_date" class="form-control" 
                           value="{{ request('end_date') }}">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search me-1"></i> Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Quality Checks Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th width="50">
                                <input type="checkbox" id="select-all">
                            </th>
                            <th>Work Order</th>
                            <th>Technician</th>
                            <th>Template</th>
                            <th>Score</th>
                            <th>Status</th>
                            <th>Photos</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($qualityChecks as $check)
                            <tr>
                                <td>
                                    <input type="checkbox" name="check_ids[]" value="{{ $check->id }}" class="check-select">
                                </td>
                                <td>
                                    <strong>WO-{{ str_pad($check->work_order_id, 6, '0', STR_PAD_LEFT) }}</strong>
                                    @if($check->workOrder)
                                        <p class="text-muted mb-0 small">
                                            {{ $check->workOrder->customer->name ?? 'Unknown Customer' }}
                                        </p>
                                    @endif
                                </td>
                                <td>
                                    {{ $check->technician->name ?? 'Unknown' }}
                                </td>
                                <td>
                                    <span class="badge bg-secondary">{{ $check->qualityCheck->name ?? 'N/A' }}</span>
                                </td>
                                <td>
                                    @php
                                        $score = $check->calculateScore();
                                        $scoreColor = $score >= 90 ? 'success' : ($score >= 80 ? 'warning' : 'danger');
                                    @endphp
                                    <div class="d-flex align-items-center">
                                        <div class="progress flex-grow-1 me-2" style="height: 20px;">
                                            <div class="progress-bar bg-{{ $scoreColor }}" 
                                                 role="progressbar" style="width: {{ $score }}%">
                                                {{ $score }}%
                                            </div>
                                        </div>
                                        @if($score >= 90)
                                            <i class="fas fa-star text-warning"></i>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    @if($check->status === 'approved')
                                        <span class="badge bg-success">Approved</span>
                                    @elseif($check->status === 'rejected')
                                        <span class="badge bg-danger">Rejected</span>
                                    @else
                                        <span class="badge bg-warning">Pending</span>
                                    @endif
                                </td>
                                <td>
                                    @if($check->photos && count($check->photos) > 0)
                                        <span class="badge bg-info">
                                            <i class="fas fa-camera me-1"></i>
                                            {{ count($check->photos) }}
                                        </span>
                                    @else
                                        <span class="badge bg-secondary">No photos</span>
                                    @endif
                                </td>
                                <td>
                                    {{ $check->created_at->format('M d, Y') }}
                                    <br>
                                    <small class="text-muted">{{ $check->created_at->format('h:i A') }}</small>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('work-order-quality.show', $check->id) }}" 
                                           class="btn btn-sm btn-outline-primary" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if($check->status === 'pending' && auth()->user()->can('approve', $check))
                                            <a href="{{ route('work-order-quality.approve', $check->id) }}" 
                                               class="btn btn-sm btn-outline-success" title="Approve"
                                               onclick="return confirm('Approve this quality check?')">
                                                <i class="fas fa-check"></i>
                                            </a>
                                            <a href="{{ route('work-order-quality.reject', $check->id) }}" 
                                               class="btn btn-sm btn-outline-danger" title="Reject"
                                               onclick="return confirm('Reject this quality check?')">
                                                <i class="fas fa-times"></i>
                                            </a>
                                        @endif
                                        @if($check->status === 'pending' && auth()->id() == $check->technician_id)
                                            <a href="{{ route('work-order-quality.edit', $check->id) }}" 
                                               class="btn btn-sm btn-outline-secondary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="fas fa-clipboard-check fa-3x mb-3"></i>
                                        <h5>No quality checks found</h5>
                                        <p>Create your first quality check for a work order.</p>
                                        <a href="{{ route('work-order-quality.create') }}" class="btn btn-primary">
                                            <i class="fas fa-plus me-1"></i> Create Quality Check
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Bulk Actions -->
            @if($qualityChecks->count() > 0)
                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="btn-group">
                            <button type="button" class="btn btn-outline-secondary dropdown-toggle" 
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-cog me-1"></i> Bulk Actions
                            </button>
                            <ul class="dropdown-menu">
                                <li>
                                    <form action="{{ route('work-order-quality.bulk-approve') }}" method="POST" id="bulk-approve-form">
                                        @csrf
                                        <input type="hidden" name="check_ids" id="bulk-check-ids-approve">
                                        <button type="submit" class="dropdown-item" onclick="submitBulkForm('approve')">
                                            <i class="fas fa-check-circle text-success me-2"></i> Approve Selected
                                        </button>
                                    </form>
                                </li>
                                <li>
                                    <form action="{{ route('work-order-quality.bulk-reject') }}" method="POST" id="bulk-reject-form">
                                        @csrf
                                        <input type="hidden" name="check_ids" id="bulk-check-ids-reject">
                                        <button type="submit" class="dropdown-item" onclick="submitBulkForm('reject')">
                                            <i class="fas fa-times-circle text-danger me-2"></i> Reject Selected
                                        </button>
                                    </form>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form action="{{ route('work-order-quality.bulk-delete') }}" method="POST" id="bulk-delete-form">
                                        @csrf
                                        <input type="hidden" name="check_ids" id="bulk-check-ids-delete">
                                        <button type="submit" class="dropdown-item text-danger" 
                                                onclick="return confirm('Are you sure you want to delete selected quality checks?') && submitBulkForm('delete')">
                                            <i class="fas fa-trash me-2"></i> Delete Selected
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <!-- Pagination -->
                        {{ $qualityChecks->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Select all checkboxes
    document.getElementById('select-all').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.check-select');
        checkboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
    });

    // Submit bulk form
    function submitBulkForm(action) {
        const checkboxes = document.querySelectorAll('.check-select:checked');
        const checkIds = Array.from(checkboxes).map(cb => cb.value);
        
        if (checkIds.length === 0) {
            alert('Please select at least one quality check.');
            return false;
        }
        
        let formId;
        switch(action) {
            case 'approve':
                formId = 'bulk-approve-form';
                break;
            case 'reject':
                formId = 'bulk-reject-form';
                break;
            case 'delete':
                formId = 'bulk-delete-form';
                break;
        }
        
        document.getElementById('bulk-check-ids-approve').value = JSON.stringify(checkIds);
        document.getElementById('bulk-check-ids-reject').value = JSON.stringify(checkIds);
        document.getElementById('bulk-check-ids-delete').value = JSON.stringify(checkIds);
        
        return true;
    }

    // Auto-submit date filters when dates are selected
    document.getElementById('start_date').addEventListener('change', function() {
        if (this.value && document.getElementById('end_date').value) {
            this.form.submit();
        }
    });

    document.getElementById('end_date').addEventListener('change', function() {
        if (this.value && document.getElementById('start_date').value) {
            this.form.submit();
        }
    });
</script>
@endpush
@endsection