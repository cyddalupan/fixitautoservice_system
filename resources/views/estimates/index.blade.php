@extends('layouts.app')

@section('title', 'Repair Quotations - Fix-It Auto Services')

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

    /* ===== Dark mode overrides ===== */
    [data-theme="dark"] .stat-card {
        background-color: var(--dark-card) !important;
        border-color: var(--dark-border) !important;
    }
    [data-theme="dark"] .stat-card-icon {
        background: var(--dark-hover) !important;
        color: var(--dark-text) !important;
    }
    [data-theme="dark"] .stat-card-info h3,
    [data-theme="dark"] .stat-card-info p {
        color: var(--dark-text) !important;
    }

    /* ===== Inline status dropdown (Repair Quotations list) ===== */
    .est-status-select {
        appearance: none;
        -webkit-appearance: none;
        border: 1px solid var(--esc, #64748b);
        background-color: color-mix(in srgb, var(--esc, #64748b) 14%, #fff);
        color: var(--esc, #64748b);
        font-weight: 600;
        font-size: 0.78rem;
        padding: 5px 26px 5px 10px;
        border-radius: 999px;
        cursor: pointer;
        line-height: 1.2;
        background-image: url("data:image/svg+xml;charset=UTF-8,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%2364748b'%3E%3Cpath d='M4.5 6.5 8 10l3.5-3.5z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 7px center;
        background-size: 12px;
        transition: box-shadow .15s ease, opacity .15s ease;
    }
    .est-status-select:focus {
        outline: none;
        box-shadow: 0 0 0 3px color-mix(in srgb, var(--esc, #64748b) 25%, transparent);
    }
    .est-status-select.is-saving { opacity: .55; pointer-events: none; }
    .est-status-select.is-error { border-color: #dc2626; color: #dc2626; background-color: #fef2f2; }
    .est-status-select option { color: #1a1a1a; background: #fff; }

    /* ===== Customer avatar (front of each Repair Quotation row — mirrors the RO list) ===== */
    .ro-avatar-wrap {
        position: relative;
        width: 46px;
        height: 46px;
        border-radius: 50%;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #f1f5f9, #e2e8f0);
        border: 2px solid #fff;
        box-shadow: 0 1px 5px rgba(15, 23, 42, .16);
        flex: 0 0 auto;
    }
    .ro-avatar-initials { font-weight: 700; color: #475569; font-size: .95rem; letter-spacing: .5px; }
    .ro-avatar-img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }
    [data-theme="dark"] .ro-avatar-wrap { border-color: var(--dark-elevated, #1e293b); }

    /* ===== Actions column: bigger, tap-friendly icon buttons ===== */
    .module-estimates .action-group { display: inline-flex; align-items: center; gap: .4rem; flex-wrap: wrap; }
    .module-estimates .action-group .btn-action {
        display: inline-flex; align-items: center; justify-content: center;
        width: 40px; height: 40px;
        padding: 0;
        border-radius: 10px;
        border: 1px solid #e2e8f0;
        background: #fff;
        color: #64748b;
        cursor: pointer;
        line-height: 1;
        transition: transform .12s ease, box-shadow .12s ease, background .12s ease;
    }
    .module-estimates .action-group .btn-action i { font-size: 1.15rem; pointer-events: none; }
    .module-estimates .action-group .btn-action:hover { background: #f8fafc; box-shadow: 0 2px 8px rgba(15,23,42,.12); transform: translateY(-1px); }
    [data-theme="dark"] .module-estimates .action-group .btn-action { background: #1f2937; border-color: #3a4550; color: #94a3b8; }
    [data-theme="dark"] .module-estimates .action-group .btn-action:hover { background: #273244; }
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
                    <h1 class="module-title">Repair Quotations</h1>
                    <p class="module-subtitle">Create and manage customer repair quotations</p>
                </div>
            </div>
            <div class="d-flex gap-2 mt-2 mt-sm-0">
                <a href="{{ route('estimates.create') }}" class="btn-create" aria-disabled="true" tabindex="-1" title="Temporarily disabled" style="opacity:.5;filter:grayscale(1);pointer-events:none;cursor:not-allowed;">
                    <i class="fas fa-plus"></i> New Repair Quotation
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
                    <p>Total Repair Quotations</p>
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
            <div class="col-12 col-md-6 col-xl-3">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-control" placeholder="Search estimates..."
                       value="{{ request('search') }}">
            </div>
            <div class="col-6 col-md-3 col-xl-2">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Supplier Quotation</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="sent" {{ request('status') == 'sent' ? 'selected' : '' }}>Sent</option>
                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                    <option value="converted" {{ request('status') == 'converted' ? 'selected' : '' }}>Converted</option>
                    <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Expired</option>
                </select>
            </div>
            <div class="col-6 col-md-3 col-xl-2">
                <label class="form-label">Date From</label>
                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
            </div>
            <div class="col-12 col-md-6 col-xl-2">
                <label class="form-label">Date To</label>
                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
            </div>
            <div class="col-12 col-md-6 col-xl-3 d-flex gap-2 align-items-end">
                <button type="submit" class="btn-filter-primary">
                    <i class="fas fa-filter"></i> Filter
                </button>
                <a href="{{ route('estimates.index') }}" class="btn-filter-outline">
                    <i class="fas fa-redo"></i> Reset
                </a>
            </div>
        </form>
    </div>

    @php
        // Worklists (view-only, inline) — follow-up aging & rejected
        $followUps = \App\Models\Estimate::with(['customer','vehicle'])
            ->whereNull('deleted_at')
            ->whereIn('status', ['sent','viewed','pending'])
            ->orderBy('sent_at')
            ->limit(15)->get();
        $rejected = \App\Models\Estimate::with(['customer','vehicle'])
            ->whereNull('deleted_at')
            ->where('status', 'rejected')
            ->orderByDesc('updated_at')
            ->limit(15)->get();
    @endphp
    @if($followUps->count() || $rejected->count())
    <div class="row g-3 mb-3">
        <div class="col-12 col-lg-6">
            <div class="main-card h-100">
                <div class="main-card-body">
                    <h6 style="font-weight:700;color:#d97706;margin-bottom:10px;"><i class="fas fa-bell"></i> Needs Follow-up (awaiting customer)</h6>
                    @forelse($followUps as $f)
                        @php $age = $f->sent_at ? (int) \Carbon\Carbon::parse($f->sent_at)->diffInDays(now()) : null; @endphp
                        <div class="d-flex justify-content-between align-items-center py-1" style="border-bottom:1px solid #f1f5f9;font-size:.82rem;">
                            <div>
                                <a href="{{ route('estimates.show', $f) }}" style="font-weight:600;">{{ $f->estimate_number ?? ('#'.$f->id) }}</a>
                                <span class="text-muted">· {{ $f->customer->full_name ?? 'N/A' }}</span>
                            </div>
                            <div>
                                @if($age !== null)
                                    <span class="badge" style="background:{{ $age >= 5 ? '#fee2e2' : '#fef3c7' }};color:{{ $age >= 5 ? '#991b1b' : '#92400e' }};">{{ $age }}d</span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="text-muted" style="font-size:.8rem;">Wala pang naka-pending na quotation. 🎉</div>
                    @endforelse
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-6">
            <div class="main-card h-100">
                <div class="main-card-body">
                    <h6 style="font-weight:700;color:#dc2626;margin-bottom:10px;"><i class="fas fa-ban"></i> Rejected (for reference)</h6>
                    @forelse($rejected as $r)
                        <div class="d-flex justify-content-between align-items-center py-1" style="border-bottom:1px solid #f1f5f9;font-size:.82rem;">
                            <div>
                                <a href="{{ route('estimates.show', $r) }}" style="font-weight:600;">{{ $r->estimate_number ?? ('#'.$r->id) }}</a>
                                <span class="text-muted">· {{ $r->customer->full_name ?? 'N/A' }}</span>
                            </div>
                            <span class="text-muted" style="font-size:.75rem;">{{ $r->updated_at ? $r->updated_at->format('M d') : '' }}</span>
                        </div>
                    @empty
                        <div class="text-muted" style="font-size:.8rem;">No rejected quotations.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Repair Quotations Table -->
    <div class="main-card">
        <div class="main-card-body">
            @if($estimates->count() > 0)
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <span class="text-muted">{{ $estimates->count() }} estimates</span>
                    </div>
                    <div>
                        @if($estimates->where('viewed_at', null)->count() > 0)
                            <button class="btn-mark-all-read" id="markAllReadBtn" onclick="markAllAsRead('estimates', this)">
                                <i class="fas fa-check-double"></i> Mark All as Read
                            </button>
                        @endif
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table-fixit" id="estimatesTable">
                        <thead>
                            <tr>
                                <th style="width:30px;"></th>
                                <th style="width:56px;"><span class="visually-hidden">Photo</span></th>
                                <th>#</th>
                                <th>Customer</th>
                                <th>Vehicle</th>
                                <th>Amount</th>
                                <th>Payment</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($estimates as $estimate)
                            <tr class="{{ $estimate->viewed_at === null ? 'tr-unread' : '' }}" data-id="{{ $estimate->id }}">
                                <td>
                                    @if($estimate->viewed_at === null)
                                        <span class="unread-dot" title="New"></span>
                                    @endif
                                </td>
                                <td>
                                    @if($estimate->customer)
                                        <div class="ro-avatar-wrap">
                                            <span class="ro-avatar-initials">{{ strtoupper(substr($estimate->customer->first_name ?? '', 0, 1)) }}{{ strtoupper(substr($estimate->customer->last_name ?? '', 0, 1)) }}</span>
                                            <img src="{{ $estimate->customer->avatar }}" alt="{{ $estimate->customer->full_name }}" class="ro-avatar-img" loading="lazy" onerror="this.style.display='none'">
                                        </div>
                                    @else
                                        <div class="ro-avatar-wrap">
                                            <span class="ro-avatar-initials"><i class="fas fa-user"></i></span>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <span class="{{ $estimate->viewed_at === null ? 'unread-primary-text' : '' }}">
                                        <strong>{{ $estimate->estimate_number ?? $estimate->id }}</strong>
                                        @if($estimate->viewed_at === null)
                                            <span class="badge-new-record">NEW</span>
                                        @endif
                                    </span>
                                </td>
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
                                    <strong>{{ number_format($estimate->quotation_total, 2) }}</strong>
                                    @php
                                        $estLabor = $estimate->quotation_labor_total;
                                        $estParts = $estimate->quotation_parts_total;
                                    @endphp
                                    @if($estLabor > 0 || $estParts > 0)
                                        <br>
                                        <small style="color:#64748b;font-size:0.72rem;line-height:1.35;display:inline-block;">
                                            <i class="fas fa-wrench me-1"></i>Labor: {{ number_format($estLabor, 2) }}
                                            <br>
                                            <i class="fas fa-cog me-1"></i>Parts: {{ number_format($estParts, 2) }}
                                        </small>
                                    @endif
                                </td>
                                <td>
                                    @include('inspections.partials.payment-status-badge', ['payments' => optional($estimate->inspection)->repairOrderPayments, 'total' => $estimate->quotation_total])
                                </td>
                                <td>
                                    @php
                                        $estStatusColors = [
                                            'draft' => '#64748b',
                                            'sent' => '#0ea5e9',
                                            'viewed' => '#6366f1',
                                            'waiting_approval' => '#f59e0b',
                                            'waiting_for_parts' => '#f59e0b',
                                            'approved' => '#16a34a',
                                            'rejected' => '#dc2626',
                                            'expired' => '#475569',
                                            'converted_to_job_order' => '#7c3aed',
                                            'converted_to_repair_order' => '#7c3aed',
                                        ];
                                        $estColor = $estStatusColors[$estimate->status] ?? '#64748b';
                                    @endphp
                                    <select class="est-status-select"
                                            data-url="{{ route('estimates.update-status', $estimate) }}"
                                            data-original="{{ $estimate->status }}"
                                            style="--esc:{{ $estColor }};"
                                            onchange="updateEstimateStatus(this)"
                                            title="Change status">
                                        @foreach(\App\Models\Estimate::STATUSES as $stKey => $stLabel)
                                            <option value="{{ $stKey }}" {{ $estimate->status === $stKey ? 'selected' : '' }}>{{ $stLabel }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <span style="font-size:0.85rem;">{{ $estimate->created_at->format('M d, Y') }}</span>
                                    <br>
                                    <small style="color:#94a3b8;font-size:0.75rem;">{{ $estimate->created_at->format('h:i A') }}</small>
                                    @php
                                        $estDays = (int) $estimate->created_at->diffInDays(now());
                                    @endphp
                                    <br>
                                    <span class="ws-days" title="Ilang araw nang nasa quotation slip">
                                        <i class="fas fa-hourglass-half"></i>
                                        {{ $estDays === 0 ? 'Today' : $estDays . ' day' . ($estDays > 1 ? 's' : '') }} on slip
                                    </span>
                                </td>
                                <td>
                                    <div class="action-group">
                                        {{-- View + Edit pointed to the same route (estimates.edit), so they're merged into one control. --}}
                                        <a href="{{ route('estimates.edit', $estimate) }}" class="btn-action" title="View / Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @if($estimate->status == 'approved')
                                        <form action="{{ route('job-orders.create') }}" method="GET" class="d-inline">
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
                                        <a href="{{ route('estimates.supplier-quotation', $estimate) }}" class="btn-action" title="Supplier Quotation" style="color:#2563eb;border-color:#bfdbfe;" target="_blank">
                                            <i class="fas fa-file-invoice"></i>
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
                    <h4>No repair quotations found</h4>
                    <p>Try adjusting your filters or create a new repair quotation to get started.</p>
                    <a href="{{ route('estimates.create') }}" class="btn-create" aria-disabled="true" tabindex="-1" title="Temporarily disabled" style="opacity:.5;filter:grayscale(1);pointer-events:none;cursor:not-allowed;">
                        <i class="fas fa-plus"></i> Create First Repair Quotation
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
    const table = document.getElementById('estimatesTable');
    if (table) {
        initUnreadSystem(table, 'estimates', { dataAttr: 'data-id', markAllBtnId: 'markAllReadBtn' });
    }
});

window.updateEstimateStatus = function(sel) {
    const url = sel.dataset.url;
    const prev = sel.dataset.original || sel.value;
    sel.classList.remove('is-error');
    sel.classList.add('is-saving');

    fetch(url, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ status: sel.value })
    })
    .then(function(res) { return res.json().then(function(data) { return { ok: res.ok, data: data }; }); })
    .then(function(result) {
        sel.classList.remove('is-saving');
        if (result.ok && result.data.success) {
            sel.dataset.original = sel.value;
            return;
        }
        // Revert on failure
        sel.value = prev;
        sel.classList.add('is-error');
        alert((result.data && result.data.message) || 'Hindi na-update ang status. Subukan ulit.');
    })
    .catch(function() {
        sel.classList.remove('is-saving');
        sel.value = prev;
        sel.classList.add('is-error');
        alert('Hindi na-update ang status. Subukan ulit.');
    });
};
</script>
@endpush
