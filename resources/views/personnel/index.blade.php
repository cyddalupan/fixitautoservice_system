@extends('layouts.app')

@section('title', 'Personnel Management')

@push('styles')
<style>
:root {
    --prsnl-primary: #4361ee;
    --prsnl-primary-soft: #eef1ff;
    --prsnl-success: #2ec4b6;
    --prsnl-success-soft: #e3f8f5;
    --prsnl-warning: #f7a429;
    --prsnl-danger: #e63946;
    --prsnl-card-radius: 12px;
    --prsnl-shadow: 0 1px 3px rgba(0,0,0,.04), 0 1px 2px rgba(0,0,0,.02);
}
body.dark-mode {
    --prsnl-shadow: 0 1px 3px rgba(0,0,0,.2);
}

/* ── Dashboard shell ── */
.prsnl-dash {
    background: #f4f6fa;
    min-height: 100vh;
    padding-top: .5rem;
    padding-bottom: 2rem;
}
body.dark-mode .prsnl-dash {
    background: #1a1d23;
}
.prsnl-dash .page-title {
    color: #1a2332;
    font-size: 1.15rem;
    font-weight: 700;
}
body.dark-mode .prsnl-dash .page-title {
    color: #e4e6eb;
}
.prsnl-dash .accent-icon { color: var(--prsnl-primary); }

/* ── Stat cards ── */
.prsnl-stat-card {
    border-radius: var(--prsnl-card-radius);
    border: 0;
    box-shadow: var(--prsnl-shadow);
    transition: box-shadow .2s, transform .15s;
}
.prsnl-stat-card:hover {
    box-shadow: 0 3px 12px rgba(0,0,0,.06);
    transform: translateY(-1px);
}
.prsnl-stat-icon {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.prsnl-stat-label {
    font-size: .68rem;
    color: #6c7a8d;
    text-transform: uppercase;
    letter-spacing: .4px;
    font-weight: 600;
}
.prsnl-stat-value {
    font-size: 1.2rem;
    font-weight: 700;
    color: #1a2332;
    line-height: 1.1;
}
body.dark-mode .prsnl-stat-value { color: #e4e6eb; }
body.dark-mode .prsnl-stat-label { color: #9ca3af; }

/* ── Card shell ── */
.prsnl-card {
    border-radius: var(--prsnl-card-radius);
    border: 0;
    box-shadow: var(--prsnl-shadow);
}
body.dark-mode .prsnl-card { background: #2a2d35; }
.prsnl-card-header {
    background: transparent;
    border-bottom: 1px solid #eef0f3;
    padding: .75rem 1rem;
    font-size: .82rem;
    font-weight: 600;
    color: #1a2332;
}
body.dark-mode .prsnl-card-header {
    border-bottom-color: #3a3d45;
    color: #e4e6eb;
}

/* ── Avatar ── */
.prsnl-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    flex-shrink: 0;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: .75rem;
    color: #fff;
    background: var(--prsnl-primary);
    object-fit: cover;
}
.prsnl-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.prsnl-avatar-sm {
    width: 28px;
    height: 28px;
    font-size: .65rem;
}

/* ── Role badges ── */
.prsnl-role-badge {
    font-size: .65rem;
    font-weight: 500;
    padding: .1rem .5rem;
    border-radius: 4px;
    white-space: nowrap;
    display: inline-block;
    margin: 1px 2px;
}
.prsnl-role-badge.technician { background: #e3f2fd; color: #1565c0; }
.prsnl-role-badge.admin { background: #fff3e0; color: #e65100; }
.prsnl-role-badge.office_staff { background: #e0f7fa; color: #00695c; }
.prsnl-role-badge.accounting { background: #f3e5f5; color: #7b1fa2; }
.prsnl-role-badge.manager { background: #e8eaf6; color: #283593; }
.prsnl-role-badge.super_admin { background: #fce4ec; color: #c62828; }
.prsnl-role-badge.executive { background: #fce4ec; color: #b71c1c; }
.prsnl-role-badge.service_advisor { background: #e0f2f1; color: #004d40; }
.prsnl-role-badge.default { background: #f5f5f5; color: #555; }
body.dark-mode .prsnl-role-badge.technician { background: #1a3a5c; color: #90caf9; }
body.dark-mode .prsnl-role-badge.admin { background: #3d2817; color: #ffcc80; }
body.dark-mode .prsnl-role-badge.office_staff { background: #133a3d; color: #80cbc4; }
body.dark-mode .prsnl-role-badge.accounting { background: #2a1a3a; color: #ce93d8; }
body.dark-mode .prsnl-role-badge.manager { background: #1a1a3a; color: #9fa8da; }
body.dark-mode .prsnl-role-badge.super_admin { background: #3a1a1a; color: #ef9a9a; }
body.dark-mode .prsnl-role-badge.executive { background: #3a1a1a; color: #ef9a9a; }
body.dark-mode .prsnl-role-badge.service_advisor { background: #1a302e; color: #80cbc4; }
body.dark-mode .prsnl-role-badge.default { background: #3a3a3a; color: #ccc; }

/* ── Table ── */
.prsnl-table th {
    font-size: .7rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .4px;
    color: #6c7a8d;
    border-bottom-width: 1px;
    padding: .65rem .75rem;
    white-space: nowrap;
}
body.dark-mode .prsnl-table th { color: #9ca3af; }
.prsnl-table td {
    padding: .55rem .75rem;
    vertical-align: middle;
    font-size: .8rem;
}
body.dark-mode .prsnl-table td { color: #d1d5db; }
body.dark-mode .prsnl-table tr:hover { background: rgba(67, 97, 238, .05); }

/* ── Sticky top ── */
.prsnl-sticky-header {
    position: sticky;
    top: 0;
    z-index: 10;
    background: #f4f6fa;
    padding-top: .5rem;
}
body.dark-mode .prsnl-sticky-header { background: #1a1d23; }
</style>
@endpush

@section('content')
<div class="container-fluid px-3 px-md-4 prsnl-dash">
    <!-- ══ HEADER ══ -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0 page-title">
                <i class="fas fa-users me-2 accent-icon"></i>Personnel
            </h4>
            <p class="mb-0 text-muted small" style="font-size:.75rem;">{{ $personnel->total() }} staff members</p>
        </div>
        <a href="{{ route('personnel.create') }}" class="btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-plus me-1"></i> Add Personnel
        </a>
    </div>

    <!-- ══ STAT CARDS ══ -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card prsnl-stat-card h-100">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start mb-1">
                        <span class="prsnl-stat-label">Total Personnel</span>
                        <div class="prsnl-stat-icon" style="background:{{ '#4361ee' }}12;">
                            <i class="fas fa-users" style="color:#4361ee;font-size:.8rem;"></i>
                        </div>
                    </div>
                    <div class="prsnl-stat-value">{{ $stats['total'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card prsnl-stat-card h-100">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start mb-1">
                        <span class="prsnl-stat-label">Technicians</span>
                        <div class="prsnl-stat-icon" style="background:#2ec4b612;">
                            <i class="fas fa-wrench" style="color:#2ec4b6;font-size:.8rem;"></i>
                        </div>
                    </div>
                    <div class="prsnl-stat-value">{{ $stats['technicians'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card prsnl-stat-card h-100">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start mb-1">
                        <span class="prsnl-stat-label">Office / Advisors</span>
                        <div class="prsnl-stat-icon" style="background:#f7a42912;">
                            <i class="fas fa-headset" style="color:#f7a429;font-size:.8rem;"></i>
                        </div>
                    </div>
                    <div class="prsnl-stat-value">{{ $stats['office_staff'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card prsnl-stat-card h-100">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start mb-1">
                        <span class="prsnl-stat-label">Management</span>
                        <div class="prsnl-stat-icon" style="background:#e6394612;">
                            <i class="fas fa-user-tie" style="color:#e63946;font-size:.8rem;"></i>
                        </div>
                    </div>
                    <div class="prsnl-stat-value">{{ $stats['executives'] }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- ══ FILTERS ══ -->
    <div class="card prsnl-card mb-3">
        <div class="prsnl-card-header">
            <i class="fas fa-filter me-1"></i> Search & Filter
        </div>
        <div class="card-body p-3 filter-body">
            <form method="GET" action="{{ route('personnel.index') }}" class="row g-2 g-md-3">
                <div class="col-md-4">
                    <input type="text" class="form-control form-control-sm" name="search"
                           value="{{ request('search') }}" placeholder="Search name, email, phone...">
                </div>
                <div class="col-md-3">
                    <select class="form-select form-select-sm" name="role_filter">
                        <option value="">All Roles</option>
                        @foreach($availableRoles as $key => $label)
                            <option value="{{ $key }}" {{ request('role_filter') == $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select form-select-sm" name="status">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <div class="d-flex gap-1">
                        <button type="submit" class="btn btn-sm btn-primary flex-fill">
                            <i class="fas fa-search me-1"></i> Filter
                        </button>
                        <a href="{{ route('personnel.index') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-times"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- ══ TABLE ══ -->
    <div class="card prsnl-card">
        <div class="prsnl-card-header d-flex justify-content-between align-items-center">
            <span><i class="fas fa-list me-1"></i> All Personnel</span>
            <small class="text-muted">{{ $personnel->firstItem() }}–{{ $personnel->lastItem() }} of {{ $personnel->total() }}</small>
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
                    <table class="table prsnl-table mb-0">
                        <thead>
                            <tr>
                                <th style="width:40px;"></th>
                                <th>Name</th>
                                <th>Roles</th>
                                <th>Contact</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($personnel as $person)
                                <tr>
                                    <!-- Profile image / avatar -->
                                    <td>
                                        <div class="prsnl-avatar prsnl-avatar-sm">
                                            @if($person->profile_photo_path)
                                                <img src="{{ Storage::url($person->profile_photo_path) }}" alt="{{ $person->name }}">
                                            @else
                                                {{ \App\Http\Controllers\PersonnelController::getInitials($person->name) }}
                                            @endif
                                        </div>
                                    </td>
                                    <!-- Name -->
                                    <td>
                                        <a href="{{ route('personnel.show', $person) }}" class="text-decoration-none fw-semibold" style="color:var(--prsnl-primary);font-size:.82rem;">
                                            {{ $person->name }}
                                        </a>
                                        <div class="text-muted" style="font-size:.65rem;">{{ $person->employee_id ?? '—' }}</div>
                                    </td>
                                    <!-- Roles (badges) -->
                                    <td>
                                        <div style="display:flex;flex-wrap:wrap;gap:2px;">
                                            @foreach($person->all_roles as $roleKey)
                                                <span class="prsnl-role-badge {{ $roleKey }}">
                                                    {{ \App\Models\User::roleLabels()[$roleKey] ?? ucfirst(str_replace('_', ' ', $roleKey)) }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </td>
                                    <!-- Contact -->
                                    <td>
                                        <div style="font-size:.78rem;">{{ $person->phone ?? '—' }}</div>
                                        <div class="text-muted" style="font-size:.65rem;">{{ $person->email }}</div>
                                    </td>
                                    <!-- Status -->
                                    <td>
                                        @if($person->is_active)
                                            <span class="badge bg-success" style="font-size:.65rem;font-weight:500;">Active</span>
                                        @else
                                            <span class="badge bg-secondary" style="font-size:.65rem;font-weight:500;">Inactive</span>
                                        @endif
                                    </td>
                                    <!-- Actions -->
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="{{ route('personnel.show', $person) }}" class="btn btn-outline-primary" title="View Profile" style="font-size:.7rem;padding:.2rem .5rem;">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('personnel.edit', $person) }}" class="btn btn-outline-warning" title="Edit" style="font-size:.7rem;padding:.2rem .5rem;">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-outline-danger" title="Delete" onclick="confirmDelete({{ $person->id }}, '{{ addslashes($person->name) }}')" style="font-size:.7rem;padding:.2rem .5rem;">
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
                <div class="d-flex justify-content-between align-items-center px-3 py-2 border-top">
                    <small class="text-muted">{{ $personnel->total() }} total</small>
                    <div>{{ $personnel->links() }}</div>
                </div>
                @endif
            @endif
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h6 class="modal-title fw-bold">Delete Personnel</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-1">Are you sure you want to delete <strong id="deleteName"></strong>?</p>
                <p class="text-danger small mb-0">This action cannot be undone.</p>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function confirmDelete(id, name) {
    document.getElementById('deleteForm').action = '/personnel/' + id;
    document.getElementById('deleteName').textContent = name;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>
@endpush
@endsection
