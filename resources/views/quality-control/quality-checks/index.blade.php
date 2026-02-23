@extends('layouts.app')

@section('title', 'Quality Check Templates')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col">
                <h1 class="h3 mb-0">
                    <i class="fas fa-clipboard-check text-primary me-2"></i>
                    Quality Check Templates
                </h1>
                <p class="text-muted mb-0">Manage quality check templates and categories</p>
            </div>
            <div class="col-auto">
                <a href="{{ route('quality-checks.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i> New Template
                </a>
                <a href="{{ route('quality-checks.export') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-download me-1"></i> Export
                </a>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('quality-checks.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label for="category" class="form-label">Category</label>
                    <select name="category" id="category" class="form-select">
                        <option value="">All Categories</option>
                        @foreach(['safety' => 'Safety', 'workmanship' => 'Workmanship', 'cleanliness' => 'Cleanliness', 'documentation' => 'Documentation', 'parts' => 'Parts', 'customer_service' => 'Customer Service'] as $value => $label)
                            <option value="{{ $value }}" {{ request('category') == $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="status" class="form-label">Status</label>
                    <select name="status" id="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="search" class="form-label">Search</label>
                    <input type="text" name="search" id="search" class="form-control" 
                           placeholder="Search by name or description..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search me-1"></i> Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card stat-card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-subtitle mb-2">Total Templates</h6>
                            <h2 class="card-title mb-0">{{ $qualityChecks->total() }}</h2>
                        </div>
                        <div class="card-icon">
                            <i class="fas fa-clipboard-list"></i>
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
                            <h6 class="card-subtitle mb-2">Active Templates</h6>
                            <h2 class="card-title mb-0">{{ $qualityChecks->where('is_active', true)->count() }}</h2>
                        </div>
                        <div class="card-icon">
                            <i class="fas fa-check-circle"></i>
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
                            <h6 class="card-subtitle mb-2">Most Used</h6>
                            <h2 class="card-title mb-0">{{ $mostUsedTemplate->name ?? 'N/A' }}</h2>
                        </div>
                        <div class="card-icon">
                            <i class="fas fa-star"></i>
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
                            <h6 class="card-subtitle mb-2">Avg Completion</h6>
                            <h2 class="card-title mb-0">{{ number_format($averageCompletionRate, 1) }}%</h2>
                        </div>
                        <div class="card-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                    </div>
                </div>
            </div>
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
                            <th>Template Name</th>
                            <th>Category</th>
                            <th>Checklist Items</th>
                            <th>Status</th>
                            <th>Used</th>
                            <th>Completion Rate</th>
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
                                    <strong>{{ $check->name }}</strong>
                                    @if($check->description)
                                        <p class="text-muted mb-0 small">{{ Str::limit($check->description, 50) }}</p>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-secondary">{{ ucfirst($check->category) }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-info">{{ count($check->checklist_items ?? []) }} items</span>
                                </td>
                                <td>
                                    @if($check->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-secondary">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-primary">{{ $check->work_order_quality_checks_count ?? 0 }} times</span>
                                </td>
                                <td>
                                    @php
                                        $completionRate = $check->calculateCompletionRate();
                                    @endphp
                                    <div class="progress" style="height: 20px;">
                                        <div class="progress-bar {{ $completionRate >= 80 ? 'bg-success' : ($completionRate >= 60 ? 'bg-warning' : 'bg-danger') }}" 
                                             role="progressbar" style="width: {{ $completionRate }}%">
                                            {{ $completionRate }}%
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('quality-checks.show', $check->id) }}" 
                                           class="btn btn-sm btn-outline-primary" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('quality-checks.edit', $check->id) }}" 
                                           class="btn btn-sm btn-outline-secondary" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="{{ route('quality-checks.duplicate', $check->id) }}" 
                                           class="btn btn-sm btn-outline-info" title="Duplicate">
                                            <i class="fas fa-copy"></i>
                                        </a>
                                        <form action="{{ route('quality-checks.destroy', $check->id) }}" 
                                              method="POST" class="d-inline" 
                                              onsubmit="return confirm('Are you sure you want to delete this template?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="fas fa-clipboard-list fa-3x mb-3"></i>
                                        <h5>No quality check templates found</h5>
                                        <p>Create your first quality check template to get started.</p>
                                        <a href="{{ route('quality-checks.create') }}" class="btn btn-primary">
                                            <i class="fas fa-plus me-1"></i> Create Template
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
                                    <form action="{{ route('quality-checks.bulk-update') }}" method="POST" id="bulk-activate-form">
                                        @csrf
                                        <input type="hidden" name="check_ids" id="bulk-check-ids">
                                        <input type="hidden" name="action" value="activate">
                                        <button type="submit" class="dropdown-item" onclick="submitBulkForm('activate')">
                                            <i class="fas fa-check-circle text-success me-2"></i> Activate Selected
                                        </button>
                                    </form>
                                </li>
                                <li>
                                    <form action="{{ route('quality-checks.bulk-update') }}" method="POST" id="bulk-deactivate-form">
                                        @csrf
                                        <input type="hidden" name="check_ids" id="bulk-check-ids-deactivate">
                                        <input type="hidden" name="action" value="deactivate">
                                        <button type="submit" class="dropdown-item" onclick="submitBulkForm('deactivate')">
                                            <i class="fas fa-times-circle text-danger me-2"></i> Deactivate Selected
                                        </button>
                                    </form>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form action="{{ route('quality-checks.bulk-update') }}" method="POST" id="bulk-delete-form">
                                        @csrf
                                        <input type="hidden" name="check_ids" id="bulk-check-ids-delete">
                                        <input type="hidden" name="action" value="delete">
                                        <button type="submit" class="dropdown-item text-danger" 
                                                onclick="return confirm('Are you sure you want to delete selected templates?') && submitBulkForm('delete')">
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
            alert('Please select at least one template.');
            return false;
        }
        
        let formId;
        switch(action) {
            case 'activate':
                formId = 'bulk-activate-form';
                break;
            case 'deactivate':
                formId = 'bulk-deactivate-form';
                break;
            case 'delete':
                formId = 'bulk-delete-form';
                break;
        }
        
        document.getElementById('bulk-check-ids').value = JSON.stringify(checkIds);
        document.getElementById('bulk-check-ids-deactivate').value = JSON.stringify(checkIds);
        document.getElementById('bulk-check-ids-delete').value = JSON.stringify(checkIds);
        
        return true;
    }
</script>
@endpush
@endsection