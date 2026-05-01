@extends('layouts.app')

@section('title', $personnel->name . ' - Profile')

@push('styles')
<style>
:root {
    --prsnl-primary: #4361ee;
}
body.dark-mode { --prsnl-card-bg: #2a2d35; }
.prsnl-profile { background: #f4f6fa; min-height: 100vh; padding-top: .5rem; padding-bottom: 2rem; }
body.dark-mode .prsnl-profile { background: #1a1d23; }
.prsnl-card { border-radius: 12px; border: 0; box-shadow: 0 1px 3px rgba(0,0,0,.04); margin-bottom: 1rem; }
body.dark-mode .prsnl-card { background: #2a2d35; }

/* ── Profile header ── */
.prsnl-profile-header {
    position: relative;
    padding: 2rem 1.5rem 1.5rem;
    text-align: center;
    background: linear-gradient(135deg, #4361ee 0%, #3a0ca3 100%);
    border-radius: 12px 12px 0 0;
    color: #fff;
    overflow: hidden;
}
.prsnl-profile-header::before {
    content: '';
    position: absolute;
    inset: 0;
    background: repeating-linear-gradient(45deg, transparent, transparent 20px, rgba(255,255,255,.03) 20px, rgba(255,255,255,.03) 40px);
}
.prsnl-profile-avatar {
    width: 96px;
    height: 96px;
    border-radius: 50%;
    border: 3px solid rgba(255,255,255,.4);
    overflow: hidden;
    margin: 0 auto .75rem;
    position: relative;
    object-fit: cover;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    font-weight: 700;
    background: rgba(255,255,255,.2);
}
.prsnl-profile-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.prsnl-profile-name {
    font-size: 1.2rem;
    font-weight: 700;
    margin-bottom: .25rem;
    position: relative;
}
.prsnl-profile-role-badges {
    display: flex;
    flex-wrap: wrap;
    gap: .3rem;
    justify-content: center;
    margin-bottom: .75rem;
    position: relative;
}
.prsnl-profile-role-badges .badge {
    font-size: .68rem;
    font-weight: 500;
    padding: .15rem .55rem;
    background: rgba(255,255,255,.2);
    backdrop-filter: blur(4px);
}
.prsnl-profile-meta {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: .5rem 1.5rem;
    position: relative;
    font-size: .78rem;
    opacity: .9;
}
.prsnl-profile-meta i {
    margin-right: .3rem;
    width: 14px;
    text-align: center;
}

/* ── Section cards ── */
.prsnl-section-title {
    font-size: .82rem;
    font-weight: 600;
    color: #1a2332;
    padding: .75rem 1rem;
    border-bottom: 1px solid #eef0f3;
    display: flex;
    align-items: center;
    gap: .5rem;
}
body.dark-mode .prsnl-section-title { color: #e4e6eb; border-bottom-color: #3a3d45; }

/* ── Skills chips ── */
.skill-chip {
    display: inline-block;
    padding: .15rem .6rem;
    border-radius: 20px;
    font-size: .72rem;
    font-weight: 500;
    background: #eef1ff;
    color: #4361ee;
    margin: 2px;
}
body.dark-mode .skill-chip { background: #1a2a4a; color: #90aef9; }

/* ── Info rows ── */
.prsnl-info-row {
    display: flex;
    padding: .4rem 1rem;
    font-size: .8rem;
}
.prsnl-info-row:nth-child(even) { background: rgba(0,0,0,.02); }
body.dark-mode .prsnl-info-row:nth-child(even) { background: rgba(255,255,255,.03); }
.prsnl-info-label {
    width: 140px;
    flex-shrink: 0;
    color: #6c7a8d;
    font-weight: 500;
}
body.dark-mode .prsnl-info-label { color: #9ca3af; }
.prsnl-info-value { color: #1a2332; }
body.dark-mode .prsnl-info-value { color: #d1d5db; }

/* ── Empty state ── */
.prsnl-empty {
    padding: 2rem 1rem;
    text-align: center;
    color: #9ca3af;
    font-size: .82rem;
}

/* ── Timeline ── */
.prsnl-timeline { position: relative; padding: 1rem; }
.prsnl-timeline-item {
    position: relative;
    padding-left: 1.5rem;
    padding-bottom: 1rem;
}
.prsnl-timeline-item::before {
    content: '';
    position: absolute;
    left: 5px;
    top: 6px;
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #4361ee;
}
.prsnl-timeline-item:not(:last-child)::after {
    content: '';
    position: absolute;
    left: 8px;
    top: 18px;
    bottom: 0;
    width: 2px;
    background: #e5e7eb;
}
.prsnl-timeline-item small { color: #9ca3af; font-size: .68rem; }
</style>
@endpush

@section('content')
<div class="container-fluid px-3 px-md-4 prsnl-profile">
    <!-- ══ NAV ══ -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <a href="{{ route('personnel.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Personnel
            </a>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('personnel.edit', $personnel) }}" class="btn btn-sm btn-warning">
                <i class="fas fa-edit me-1"></i> Edit
            </a>
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDelete({{ $personnel->id }}, '{{ addslashes($personnel->name) }}')">
                <i class="fas fa-trash me-1"></i> Delete
            </button>
        </div>
    </div>

    <div class="row g-4">
        <!-- ══ LEFT COLUMN: Profile Header + Contact + Info ══ -->
        <div class="col-lg-4">
            <!-- Profile Header -->
            <div class="card prsnl-card">
                <div class="prsnl-profile-header">
                    <div class="prsnl-profile-avatar">
                        @if($personnel->profile_photo_path)
                            <img src="{{ Storage::url($personnel->profile_photo_path) }}" alt="{{ $personnel->name }}">
                        @else
                            {{ \App\Http\Controllers\PersonnelController::getInitials($personnel->name) }}
                        @endif
                    </div>
                    <div class="prsnl-profile-name">{{ $personnel->name }}</div>
                    <div class="prsnl-profile-role-badges">
                        @foreach($personnel->all_role_labels as $label)
                            <span class="badge">{{ $label }}</span>
                        @endforeach
                    </div>
                    <div class="prsnl-profile-meta">
                        <span>
                            <i class="fas fa-envelope"></i>{{ $personnel->email }}
                        </span>
                        @if($personnel->phone)
                            <span><i class="fas fa-phone"></i>{{ $personnel->phone }}</span>
                        @endif
                        <span>
                            <i class="fas fa-{{ $personnel->is_active ? 'check-circle text-success' : 'times-circle text-danger' }}"></i>
                            {{ $personnel->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Contact Details -->
            <div class="card prsnl-card">
                <div class="prsnl-section-title">
                    <i class="fas fa-address-card" style="color:#2ec4b6;"></i> Contact Details
                </div>
                <div>
                    <div class="prsnl-info-row">
                        <span class="prsnl-info-label">Email</span>
                        <span class="prsnl-info-value">{{ $personnel->email }}</span>
                    </div>
                    <div class="prsnl-info-row">
                        <span class="prsnl-info-label">Phone</span>
                        <span class="prsnl-info-value">{{ $personnel->phone ?? '—' }}</span>
                    </div>
                    <div class="prsnl-info-row">
                        <span class="prsnl-info-label">Address</span>
                        <span class="prsnl-info-value">{{ $personnel->address ?? '—' }}</span>
                    </div>
                </div>
            </div>

            <!-- Employment Info -->
            <div class="card prsnl-card">
                <div class="prsnl-section-title">
                    <i class="fas fa-briefcase" style="color:#f7a429;"></i> Employment
                </div>
                <div>
                    <div class="prsnl-info-row">
                        <span class="prsnl-info-label">Employee ID</span>
                        <span class="prsnl-info-value">{{ $personnel->employee_id ?? '—' }}</span>
                    </div>
                    <div class="prsnl-info-row">
                        <span class="prsnl-info-label">Hire Date</span>
                        <span class="prsnl-info-value">{{ $personnel->hire_date ? $personnel->hire_date->format('M d, Y') : '—' }}</span>
                    </div>
                    <div class="prsnl-info-row">
                        <span class="prsnl-info-label">Type</span>
                        <span class="prsnl-info-value">{{ $personnel->employment_type ? ucfirst(str_replace('_', ' ', $personnel->employment_type)) : '—' }}</span>
                    </div>
                    <div class="prsnl-info-row">
                        <span class="prsnl-info-label">Shift</span>
                        <span class="prsnl-info-value">{{ $personnel->shift_schedule ? ucfirst($personnel->shift_schedule) : '—' }}</span>
                    </div>
                    <div class="prsnl-info-row">
                        <span class="prsnl-info-label">Rate</span>
                        <span class="prsnl-info-value">{{ $personnel->hourly_rate ? '₱' . number_format($personnel->hourly_rate, 2) . '/hr' : '—' }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- ══ RIGHT COLUMN: Skills, Tasks, Timeline, Notes ══ -->
        <div class="col-lg-8">
            <!-- Skills -->
            <div class="card prsnl-card">
                <div class="prsnl-section-title">
                    <i class="fas fa-tools" style="color:#4361ee;"></i> Skills & Specializations
                </div>
                <div class="p-3">
                    @if($personnel->specialization)
                        <div class="mb-2">
                            <strong style="font-size:.78rem;">Position:</strong>
                            <span style="font-size:.8rem;">{{ $personnel->specialization }}</span>
                        </div>
                    @endif
                    @if($personnel->skills)
                        @php
                            $skillsList = is_array($personnel->skills) ? $personnel->skills : explode(',', $personnel->skills);
                        @endphp
                        <div style="display:flex;flex-wrap:wrap;gap:3px;">
                            @foreach($skillsList as $skill)
                                <span class="skill-chip">{{ trim($skill) }}</span>
                            @endforeach
                        </div>
                    @else
                        <div class="prsnl-empty">No skills listed</div>
                    @endif
                </div>
            </div>

            <!-- Notes -->
            <div class="card prsnl-card">
                <div class="prsnl-section-title">
                    <i class="fas fa-sticky-note" style="color:#20c997;"></i> Notes
                </div>
                <div class="p-3">
                    @if($personnel->notes)
                        <p style="font-size:.82rem;margin:0;white-space:pre-wrap;">{{ $personnel->notes }}</p>
                    @else
                        <div class="prsnl-empty">No notes</div>
                    @endif
                </div>
            </div>

            <!-- Recent Appointments -->
            <div class="card prsnl-card">
                <div class="prsnl-section-title">
                    <i class="fas fa-calendar-check" style="color:#2ec4b6;"></i> Recent Appointments
                </div>
                @if($appointments->count() > 0)
                    <div class="prsnl-timeline">
                        @foreach($appointments as $apt)
                            <div class="prsnl-timeline-item">
                                <strong style="font-size:.8rem;">{{ $apt->appointment_number ?? '#' . $apt->id }}</strong>
                                <br>
                                <small>{{ $apt->appointment_date ? \Carbon\Carbon::parse($apt->appointment_date)->format('M d, Y') : '' }} — {{ $apt->status ?? 'N/A' }}</small>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="prsnl-empty">
                        <i class="fas fa-calendar-times fa-2x mb-2"></i><br>
                        No recent appointments assigned
                    </div>
                @endif
            </div>

            <!-- Recent Work Orders -->
            <div class="card prsnl-card">
                <div class="prsnl-section-title">
                    <i class="fas fa-clipboard-list" style="color:#f7a429;"></i> Recent Work Orders
                </div>
                @if($workOrders->count() > 0)
                    <div class="prsnl-timeline">
                        @foreach($workOrders as $wo)
                            <div class="prsnl-timeline-item">
                                <strong style="font-size:.8rem;">{{ $wo->work_order_number ?? '#' . $wo->id }}</strong>
                                <br>
                                <small>{{ $wo->created_at->format('M d, Y') }} — {{ $wo->status ?? 'N/A' }}</small>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="prsnl-empty">
                        <i class="fas fa-clipboard fa-2x mb-2"></i><br>
                        No work orders assigned yet
                    </div>
                @endif
            </div>
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
                <p class="mb-1">Delete <strong id="deleteName"></strong>?</p>
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
