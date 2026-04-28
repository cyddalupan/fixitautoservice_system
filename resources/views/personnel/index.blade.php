@extends('layouts.app')

@section('title', 'Personnel Management')

@push('styles')
<style>
.fixit-prsnl-dash{ background:#f4f6fa; min-height:100vh; padding-top:0.5rem; padding-bottom:2rem; }
.fixit-prsnl-dash .page-title{ color:#1a2332; font-size:1.15rem; }
.fixit-prsnl-dash .accent-icon{ color:#4361ee; }
.fixit-prsnl-dash .status-dot{ display:inline-block; width:6px;height:6px;border-radius:50%;background:#4361ee;vertical-align:middle; }
/* Personnel Stat Cards */
.fixit-prsnl-dash .prsnl-stat-card{ border-radius:10px; border:0; box-shadow:0 1px 2px rgba(0,0,0,.04); transition:box-shadow .2s,transform .15s; overflow:hidden; }
.fixit-prsnl-dash .prsnl-stat-card:hover{ box-shadow:0 3px 8px rgba(0,0,0,.06); transform:translateY(-1px); }
.fixit-prsnl-dash .prsnl-stat-icon{ width:32px;height:32px;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0; }
.fixit-prsnl-dash .prsnl-stat-label{ font-size:.68rem;color:#6c7a8d;text-transform:uppercase;letter-spacing:.4px;font-weight:600; }
.fixit-prsnl-dash .prsnl-stat-value{ font-size:1.2rem;font-weight:700;color:#1a2332;line-height:1.1; }
.fixit-prsnl-dash .prsnl-card{ border-radius:10px; border:0; box-shadow:0 1px 2px rgba(0,0,0,.04); }
.fixit-prsnl-dash .prsnl-card-header{ background:transparent; border-bottom:1px solid #f0f2f5; padding:.65rem 1rem; font-size:.82rem; font-weight:600; color:#1a2332; }
.fixit-prsnl-dash .prsnl-avatar{ width:34px;height:34px;border-radius:50%;background:linear-gradient(135deg,#4361ee,#3a0ca3);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:600;font-size:.85rem;flex-shrink:0; }
</style>
@endpush

@section('content')
<div class="container-fluid px-3 px-md-4 fixit-prsnl-dash">
    <!-- ══ PAGE HEADER ══ -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0 fw-bold page-title">
                <i class="fas fa-users me-2 accent-icon"></i>Personnel Management
            </h4>
            <p class="mb-0 text-muted small"><span class="status-dot"></span> Manage all staff — technicians, office staff, executives</p>
        </div>
        <div>
            <a href="{{ route('personnel.create') }}" class="btn btn-sm btn-primary">
                <i class="fas fa-plus me-1"></i> Add Personnel
            </a>
        </div>
    </div>

    <!-- ══ STAT CARDS ══ -->
    <div class="row g-3 mb-4">
        @foreach([
            ['label'=>'Total Personnel','value'=>$stats['total'],'icon'=>'users','color'=>'#4361ee'],
            ['label'=>'Technicians','value'=>$stats['technicians'],'icon'=>'wrench','color'=>'#2ec4b6'],
            ['label'=>'Office Staff','value'=>$stats['office_staff'],'icon'=>'desktop','color'=>'#f7a429'],
            ['label'=>'Executives','value'=>$stats['executives'],'icon'=>'user-tie','color'=>'#e63946'],
        ] as $s)
        <div class="col-6 col-md-3">
            <div class="card prsnl-stat-card h-100">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start mb-1">
                        <span class="prsnl-stat-label">{{ $s['label'] }}</span>
                        <div class="prsnl-stat-icon" style="background:{{ $s['color'] }}12;">
                            <i class="fas fa-{{ $s['icon'] }}" style="color:{{ $s['color'] }};font-size:.8rem;"></i>
                        </div>
                    </div>
                    <div class="prsnl-stat-value">{{ $s['value'] }}</div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- ══ FILTERS ══ -->
    <div class="card prsnl-card mb-4">
        <div class="prsnl-card-header">
            <i class="fas fa-filter me-1"></i> Filter & Sort
        </div>
        <div class="card-body p-3">
            <form method="GET" action="{{ route('personnel.index') }}" class="row g-3">
                <div class="col-md-4">
                    <label for="search" class="form-label small">Search</label>
                    <input type="text" class="form-control form-control-sm" id="search" name="search" 
                           value="{{ request('search') }}" placeholder="Name, email, phone...">
                </div>
                <div class="col-md-3">
                    <label for="role" class="form-label small">Role</label>
                    <select class="form-select form-select-sm" id="role" name="role">
                        <option value="">All Roles</option>
                        @foreach($availableRoles as $key => $label)
                            <option value="{{ $key }}" {{ request('role') == $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="status" class="form-label small">Status</label>
                    <select class="form-select form-select-sm" id="status" name="status">
                        <option value="">All</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="sort_by" class="form-label small">Sort By</label>
                    <select class="form-select form-select-sm" id="sort_by" name="sort_by">
                        <option value="name" {{ request('sort_by', 'name') == 'name' ? 'selected' : '' }}>Name</option>
                        <option value="role" {{ request('sort_by') == 'role' ? 'selected' : '' }}>Role</option>
                        <option value="hire_date" {{ request('sort_by') == 'hire_date' ? 'selected' : '' }}>Hire Date</option>
                        <option value="years_experience" {{ request('sort_by') == 'years_experience' ? 'selected' : '' }}>Experience</option>
                        <option value="created_at" {{ request('sort_by') == 'created_at' ? 'selected' : '' }}>Date Added</option>
                    </select>
                </div>
                <div class="col-md-1">
                    <label for="sort_order" class="form-label small">Order</label>
                    <select class="form-select form-select-sm" id="sort_order" name="sort_order">
                        <option value="asc" {{ request('sort_order', 'asc') == 'asc' ? 'selected' : '' }}>Asc</option>
                        <option value="desc" {{ request('sort_order') == 'desc' ? 'selected' : '' }}>Desc</option>
                    </select>
                </div>
                <div class="col-12 mt-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <button type="submit" class="btn btn-sm btn-primary">
                                <i class="fas fa-filter me-1"></i> Apply Filters
                            </button>
                            <a href="{{ route('personnel.index') }}" class="btn btn-sm btn-outline-secondary ms-1">
                                <i class="fas fa-times me-1"></i> Clear
                            </a>
                        </div>
                        <small class="text-muted">{{ $personnel->firstItem() }}-{{ $personnel->lastItem() }} of {{ $personnel->total() }}</small>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- ══ TABLE ══ -->
    <div class="card prsnl-card">
        <div class="prsnl-card-header">
            <i class="fas fa-list me-1"></i> All Personnel
        </div>
        <div class="card-body p-0">
            @if($personnel->isEmpty())
                <div class="text-center py-5">
                    <i class="fas fa-users fa-3x text-muted mb-3"></i>
                    <h5>No personnel found</h5>
                    <p class="text-muted small">Add your first staff member to get started</p>
                    <a href="{{ route('personnel.create') }}" class="btn btn-sm btn-primary">
                        <i class="fas fa-plus me-1"></i> Add Personnel
                    </a>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover mb-0" style="font-size:.78rem;">
                        <thead class="table-light">
                            <tr>
                                <th>Name</th>
                                <th>Role</th>
                                <th>Department</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($personnel as $person)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="prsnl-avatar me-2" style="width:28px;height:28px;font-size:.7rem;">
                                                {{ substr($person->name, 0, 1) }}
                                            </div>
                                            <strong>{{ $person->name }}</strong>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $person->role_badge_color }}" style="font-size:.65rem;font-weight:500;">
                                            {{ ucfirst(str_replace('_', ' ', $person->role)) }}
                                        </span>
                                    </td>
                                    <td>{{ $person->department->name ?? '—' }}</td>
                                    <td>{{ $person->email }}</td>
                                    <td>{{ $person->phone ?? '—' }}</td>
                                    <td>
                                        @if($person->is_active)
                                            <span class="badge bg-success" style="font-size:.65rem;font-weight:500;">Active</span>
                                        @else
                                            <span class="badge bg-secondary" style="font-size:.65rem;font-weight:500;">Inactive</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="{{ route('personnel.show', $person) }}" class="btn btn-outline-primary" title="View" style="font-size:.7rem;padding:.2rem .5rem;">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('personnel.edit', $person) }}" class="btn btn-outline-warning" title="Edit" style="font-size:.7rem;padding:.2rem .5rem;">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-outline-danger" title="Delete" onclick="confirmDelete({{ $person->id }})" style="font-size:.7rem;padding:.2rem .5rem;">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($personnel->hasPages())
                <div class="d-flex justify-content-center py-3">
                    {{ $personnel->links() }}
                </div>
                @endif
            @endif
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