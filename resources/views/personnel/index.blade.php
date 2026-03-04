@extends('layouts.app')

@section('title', 'Personnel Management')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="page-header">
                <h1 class="mb-0">
                    <i class="fas fa-users me-2"></i>Personnel Management
                </h1>
                <p class="text-muted">Manage all staff members including technicians, office staff, and executives</p>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="stat-card bg-primary text-white p-3 rounded">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0">Total Personnel</h6>
                        <h2 class="mb-0">{{ $stats['total'] }}</h2>
                    </div>
                    <i class="fas fa-users fa-2x"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="stat-card bg-success text-white p-3 rounded">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0">Technicians</h6>
                        <h2 class="mb-0">{{ $stats['technicians'] }}</h2>
                    </div>
                    <i class="fas fa-wrench fa-2x"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="stat-card bg-warning text-white p-3 rounded">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0">Office Staff</h6>
                        <h2 class="mb-0">{{ $stats['office_staff'] }}</h2>
                    </div>
                    <i class="fas fa-desktop fa-2x"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="stat-card bg-info text-white p-3 rounded">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0">Executives</h6>
                        <h2 class="mb-0">{{ $stats['executives'] }}</h2>
                    </div>
                    <i class="fas fa-user-tie fa-2x"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtering and Sorting Controls -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Filter & Sort Personnel</h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('personnel.index') }}" class="row g-3">
                        <!-- Search -->
                        <div class="col-md-4">
                            <label for="search" class="form-label">Search</label>
                            <input type="text" class="form-control" id="search" name="search" 
                                   value="{{ request('search') }}" placeholder="Search by name, email, phone...">
                        </div>

                        <!-- Role Filter -->
                        <div class="col-md-3">
                            <label for="role" class="form-label">Role</label>
                            <select class="form-select" id="role" name="role">
                                <option value="">All Roles</option>
                                @foreach($availableRoles as $key => $label)
                                    <option value="{{ $key }}" {{ request('role') == $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Status Filter -->
                        <div class="col-md-2">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="">All Status</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>

                        <!-- Sort By -->
                        <div class="col-md-2">
                            <label for="sort_by" class="form-label">Sort By</label>
                            <select class="form-select" id="sort_by" name="sort_by">
                                <option value="name" {{ request('sort_by', 'name') == 'name' ? 'selected' : '' }}>Name</option>
                                <option value="role" {{ request('sort_by') == 'role' ? 'selected' : '' }}>Role</option>
                                <option value="hire_date" {{ request('sort_by') == 'hire_date' ? 'selected' : '' }}>Hire Date</option>
                                <option value="years_experience" {{ request('sort_by') == 'years_experience' ? 'selected' : '' }}>Experience</option>
                                <option value="created_at" {{ request('sort_by') == 'created_at' ? 'selected' : '' }}>Date Added</option>
                            </select>
                        </div>

                        <!-- Sort Order -->
                        <div class="col-md-1">
                            <label for="sort_order" class="form-label">Order</label>
                            <select class="form-select" id="sort_order" name="sort_order">
                                <option value="asc" {{ request('sort_order', 'asc') == 'asc' ? 'selected' : '' }}>Asc</option>
                                <option value="desc" {{ request('sort_order') == 'desc' ? 'selected' : '' }}>Desc</option>
                            </select>
                        </div>

                        <!-- Form Actions -->
                        <div class="col-md-12 mt-3">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-filter me-1"></i> Apply Filters
                                    </button>
                                    <a href="{{ route('personnel.index') }}" class="btn btn-outline-secondary ms-2">
                                        <i class="fas fa-times me-1"></i> Clear Filters
                                    </a>
                                </div>
                                <div>
                                    <span class="text-muted">
                                        Showing {{ $personnel->firstItem() }}-{{ $personnel->lastItem() }} of {{ $personnel->total() }} personnel
                                    </span>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-end">
                <a href="{{ route('personnel.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i> Add New Personnel
                </a>
            </div>
        </div>
    </div>

    <!-- Personnel Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">All Personnel</h5>
                </div>
                <div class="card-body">
                    @if($personnel->isEmpty())
                        <div class="text-center py-5">
                            <i class="fas fa-users fa-3x text-muted mb-3"></i>
                            <h5>No personnel found</h5>
                            <p class="text-muted">Add your first staff member to get started</p>
                            <a href="{{ route('personnel.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-1"></i> Add Personnel
                            </a>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Role</th>
                                        <th>Department</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($personnel as $person)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="customer-avatar me-3">
                                                        {{ substr($person->name, 0, 1) }}
                                                    </div>
                                                    <div>
                                                        <strong>{{ $person->name }}</strong>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $person->role_badge_color }}">
                                                    {{ ucfirst(str_replace('_', ' ', $person->role)) }}
                                                </span>
                                            </td>
                                            <td>
                                                {{ $person->department->name ?? 'N/A' }}
                                            </td>
                                            <td>{{ $person->email }}</td>
                                            <td>{{ $person->phone ?? 'N/A' }}</td>
                                            <td>
                                                @if($person->is_active)
                                                    <span class="badge bg-success">Active</span>
                                                @else
                                                    <span class="badge bg-secondary">Inactive</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <a href="{{ route('personnel.show', $person) }}" class="btn btn-outline-primary" title="View">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('personnel.edit', $person) }}" class="btn btn-outline-warning" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-outline-danger" title="Delete" onclick="confirmDelete({{ $person->id }})">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <div class="d-flex justify-content-center mt-3">
                            {{ $personnel->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this personnel? This action cannot be undone.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function confirmDelete(personnelId) {
    const form = document.getElementById('deleteForm');
    form.action = `/personnel/${personnelId}`;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>
@endpush
@endsection