@extends('layouts.app')

@section('title', 'Repair Orders - Fix-It Auto Services')

@push('styles')
<style>
.module-inspections {
    --module-primary: #dc2626;
    --module-primary-dark: #b91c1c;
    --module-primary-light: #fee2e2;
    --module-primary-subtle: #fef2f2;
    --module-active: #dc2626;
    --module-active-dark: #b91c1c;
    --module-active-light: #fee2e2;
}

    /* ===== Repair status tag (monochrome + red) ===== */
    .rs-select {
        appearance: none;
        -webkit-appearance: none;
        border: 1px solid var(--rs-border, #e2e8f0);
        background-color: var(--rs-bg, #f1f5f9);
        color: var(--rs-text, #475569);
        font-weight: 600;
        font-size: .78rem;
        padding: 5px 26px 5px 12px;
        border-radius: 999px;
        cursor: pointer;
        background-image: url("data:image/svg+xml;charset=utf8,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%2394a3b8'%3E%3Cpath d='M3.5 6l4.5 4.5L12.5 6'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 8px center;
        background-size: 11px;
        transition: box-shadow .12s, opacity .12s;
        max-width: 175px;
    }
    .rs-select:focus { outline: none; box-shadow: 0 0 0 3px rgba(148, 163, 184, .35); }
    .rs-select.is-saving { opacity: .55; }
    .rs-select.is-error { box-shadow: 0 0 0 3px rgba(220, 38, 38, .30); }

    .ro-price {
        font-weight: 700;
        font-size: .9rem;
        color: #0f172a;
        white-space: nowrap;
    }
    [data-bs-theme="dark"] .ro-price, body.dark-mode .ro-price { color: #e2e8f0; }

    /* Days-in-workshop pill (Created column) */
    .ws-days {
        display: inline-block;
        margin-top: 4px;
        padding: 1px 8px;
        border-radius: 999px;
        background: #f1f5f9;
        border: 1px solid #e2e8f0;
        color: #475569;
        font-size: .7rem;
        font-weight: 600;
        white-space: nowrap;
    }
    .ws-days.is-released {
        background: #0f172a;
        border-color: #0f172a;
        color: #ffffff;
    }

    /* ===== Monochrome palette overrides (light gray / gray / black / white / red) ===== */
    .module-inspections .status-badge-warning,
    .module-inspections .status-badge-info,
    .module-inspections .status-badge-primary { background: #f1f5f9 !important; color: #475569 !important; }
    .module-inspections .status-badge-success { background: #e2e8f0 !important; color: #0f172a !important; }
    .module-inspections .status-badge-danger { background: #fee2e2 !important; color: #dc2626 !important; }
    .module-inspections .status-badge-secondary { background: #f1f5f9 !important; color: #64748b !important; }

    .module-inspections .table-fixit tbody tr.tr-unread { background-color: #f1f5f9 !important; }
    .module-inspections .table-fixit tbody tr.tr-unread:hover { background-color: #e9edf2 !important; }
    .module-inspections .tr-unread td:first-child { box-shadow: inset 3px 0 0 0 #dc2626 !important; }
    .module-inspections .unread-dot { background: #dc2626 !important; }
    .module-inspections .badge-new-record { background: #dc2626 !important; color: #ffffff !important; }
    .module-inspections .unread-primary-text { color: #0f172a !important; }
    .module-inspections .btn-mark-all-read { background: #f1f5f9; color: #475569; border-color: #e2e8f0; }
    .module-inspections .btn-mark-all-read:hover { background: #e2e8f0; color: #0f172a; filter: none; }

    /* ===== Row action buttons ===== */
    .module-inspections .action-group {
        display: inline-flex;
        align-items: center;
        gap: .5rem;
    }
    .module-inspections .btn-action {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 38px;
        height: 38px;
        padding: 0;
        border: 1px solid #d8dee6;
        border-radius: 10px;
        background: #fff;
        color: #475569;
        font-size: .95rem;
        line-height: 1;
        cursor: pointer;
        text-decoration: none;
        transition: background .15s ease, transform .15s ease, box-shadow .15s ease;
    }
    .module-inspections .btn-action:hover {
        background: #f1f5f9;
        transform: translateY(-1px);
        box-shadow: 0 2px 6px rgba(15,23,42,.08);
    }
    .module-inspections .btn-action i { pointer-events: none; }
    [data-theme="dark"] .module-inspections .btn-action {
        background: var(--dark-elevated, #1e293b);
        border-color: var(--dark-border, #334155);
        color: var(--dark-text-secondary, #cbd5e1);
    }
    [data-theme="dark"] .module-inspections .btn-action:hover {
        background: var(--dark-hover, #334155);
    }

    /* ===== Repair Orders table polish ===== */
    .module-inspections .main-card {
        border-radius: 14px;
        border: 1px solid #eef2f7;
        box-shadow: 0 6px 24px rgba(15, 23, 42, .06);
    }
    .module-inspections .table-responsive { border-radius: 12px; }
    .module-inspections .table-fixit {
        border-collapse: separate;
        border-spacing: 0;
        width: 100%;
    }
    .module-inspections .table-fixit thead th {
        background: #f8fafc;
        text-transform: uppercase;
        letter-spacing: .04em;
        font-size: .72rem;
        color: #64748b;
        border-bottom: 1px solid #e8edf3;
        padding-top: .85rem;
        padding-bottom: .85rem;
        white-space: nowrap;
    }
    .module-inspections .table-fixit tbody td {
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9;
        padding-top: .7rem;
        padding-bottom: .7rem;
    }
    .module-inspections .table-fixit tbody tr { transition: background .15s ease; }
    .module-inspections .table-fixit tbody tr:hover { background: #f8fafc; }
    .module-inspections .table-fixit tbody tr.tr-unread { background: #f1f5f9; }
    .module-inspections .table-fixit tbody tr.tr-unread:hover { background: #e9edf2; }

    /* Customer avatar (before the Customer column) */
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
    .ro-avatar-initials {
        font-weight: 700;
        color: #475569;
        font-size: .95rem;
        letter-spacing: .5px;
    }
    .ro-avatar-img {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    /* Clickable avatar → upload the customer photo in place */
    .ro-avatar-wrap.is-editable { cursor: pointer; }
    .ro-avatar-wrap.is-editable:hover { border-color: #dc2626; }
    .ro-avatar-overlay {
        position: absolute;
        inset: 0;
        z-index: 3;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(15, 23, 42, .45);
        color: #fff;
        font-size: .95rem;
        opacity: 0;
        transition: opacity .15s ease;
        pointer-events: none;
    }
    .ro-avatar-wrap.is-editable:hover .ro-avatar-overlay,
    .ro-avatar-wrap.is-uploading .ro-avatar-overlay { opacity: 1; }
    .ro-avatar-wrap.is-uploading .ro-avatar-overlay i { animation: roSpin .7s linear infinite; }
    @keyframes roSpin { to { transform: rotate(360deg); } }
    .ro-plate-chip {
        display: inline-block;
        margin-top: 4px;
        padding: 1px 8px;
        border-radius: 6px;
        background: #f1f5f9;
        border: 1px solid #e2e8f0;
        color: #475569;
        font-size: .72rem;
        font-weight: 600;
        letter-spacing: .03em;
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
    [data-theme="dark"] .module-inspections .table-fixit thead th {
        background: var(--dark-elevated, #1e293b);
        color: var(--dark-text-secondary, #cbd5e1);
        border-bottom-color: var(--dark-border, #334155);
    }
    [data-theme="dark"] .module-inspections .table-fixit tbody td {
        border-bottom-color: var(--dark-border, #334155);
    }
    [data-theme="dark"] .module-inspections .table-fixit tbody tr:hover,
    [data-theme="dark"] .module-inspections .table-fixit tbody tr.tr-unread {
        background: rgba(255, 255, 255, .04);
    }
    [data-theme="dark"] .ro-avatar-wrap { border-color: var(--dark-elevated, #1e293b); }
    [data-theme="dark"] .ro-plate-chip {
        background: var(--dark-hover, #334155);
        border-color: var(--dark-border, #334155);
        color: var(--dark-text-secondary, #cbd5e1);
    }
</style>
@endpush

@section('content')
<div class="container-fluid module-inspections">
    <!-- Page Header -->
    <div class="page-module-header">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div class="d-flex align-items-center gap-3">
                <span class="module-badge"><i class="fas fa-tools"></i></span>
                <div>
                    <h1 class="module-title">Repair Orders</h1>
                    <p class="module-subtitle">Vehicle repair orders and inspection management</p>
                </div>
            </div>
            <a href="{{ route('inspections.create') }}" class="btn btn-primary"
               style="border:0;border-radius:10px;font-weight:600;padding:10px 18px;background:linear-gradient(135deg,#dc2626,#b91c1c);box-shadow:0 4px 12px rgba(220,38,38,.30);">
                <i class="fas fa-plus me-1"></i> +Walk In Repair Order
            </a>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="stats-row">
        <div class="stat-card" style="border-left-color:#94a3b8;">
            <div class="stat-card-body">
                <div class="stat-card-icon" style="background:#f1f5f9;color:#475569;">
                    <i class="fas fa-hourglass-half"></i>
                </div>
                <div class="stat-card-info">
                    <h3>{{ $stats['pending'] ?? 0 }}</h3>
                    <p>Pending</p>
                </div>
            </div>
        </div>
        <div class="stat-card" style="border-left-color:#dc2626;">
            <div class="stat-card-body">
                <div class="stat-card-icon" style="background:#fee2e2;color:#dc2626;">
                    <i class="fas fa-spinner"></i>
                </div>
                <div class="stat-card-info">
                    <h3>{{ $stats['in_progress'] ?? 0 }}</h3>
                    <p>In Progress</p>
                </div>
            </div>
        </div>
        <div class="stat-card" style="border-left-color:#334155;">
            <div class="stat-card-body">
                <div class="stat-card-icon" style="background:#e2e8f0;color:#0f172a;">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-card-info">
                    <h3>{{ $stats['completed'] ?? 0 }}</h3>
                    <p>Completed</p>
                </div>
            </div>
        </div>
        <div class="stat-card" style="border-left-color:#dc2626;">
            <div class="stat-card-body">
                <div class="stat-card-icon" style="background:#fee2e2;color:#dc2626;">
                    <i class="fas fa-peso-sign"></i>
                </div>
                <div class="stat-card-info">
                    <h3 style="font-size:1.15rem;">&#8369;{{ number_format($stats['sales_amount'] ?? 0, 2) }}</h3>
                    <p>Sales Amount</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="filter-bar">
        <form method="GET" action="{{ route('inspections.index') }}" class="row g-2 align-items-end">
            <div class="col-12 col-md-6 col-xl-4">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-control" placeholder="Search customers, vehicles..." 
                       value="{{ request('search') }}">
            </div>
            <div class="col-6 col-md-3 col-xl-2">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>
            <div class="col-6 col-md-3 col-xl-2">
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
            <div class="col-12 col-md-6 col-xl-4 d-flex gap-2 align-items-end">
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
                                <th style="width:56px;"><span class="visually-hidden">Photo</span></th>
                                <th>Customer</th>
                                <th>Vehicle</th>
                                <th>Service Type</th>
                                <th>Repair Status</th>
                                <th>Created</th>
                                <th>Price</th>
                                <th>Payment</th>
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
                                        <div class="ro-avatar-wrap is-editable"
                                             data-upload-url="{{ route('customers.upload-profile-picture', $inspection->customer) }}"
                                             title="I-click para mag-upload ng picture">
                                            <span class="ro-avatar-initials">{{ strtoupper(substr($inspection->customer->first_name ?? '', 0, 1)) }}{{ strtoupper(substr($inspection->customer->last_name ?? '', 0, 1)) }}</span>
                                            <img src="{{ $inspection->customer->avatar }}" alt="{{ $inspection->customer->full_name }}" class="ro-avatar-img" loading="lazy" onerror="this.style.display='none'">
                                            <span class="ro-avatar-overlay"><i class="fas fa-camera"></i></span>
                                            <input type="file" class="ro-avatar-file" accept="image/*" hidden>
                                        </div>
                                    @else
                                        <div class="ro-avatar-wrap">
                                            <span class="ro-avatar-initials"><i class="fas fa-user"></i></span>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    @if($inspection->customer)
                                        <strong>{{ $inspection->customer->full_name }}</strong>
                                        @if($inspection->is_walk_in)
                                            <span class="badge" style="background:#fef3c7;color:#92400e;font-weight:600;font-size:.68rem;vertical-align:middle;margin-left:4px;" title="Walk-in (walang schedule)"><i class="fas fa-person-walking me-1"></i>Walk-in</span>
                                        @else
                                            <span class="badge" style="background:#dbeafe;color:#1e40af;font-weight:600;font-size:.68rem;vertical-align:middle;margin-left:4px;" title="Dumaan sa schedule (appointment / job order)"><i class="fas fa-calendar-check me-1"></i>Scheduled</span>
                                        @endif
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
                                        <span style="font-size:0.85rem;font-weight:500;">
                                            {{ $inspection->vehicle->year }} {{ $inspection->vehicle->make }} {{ $inspection->vehicle->model }}
                                        </span>
                                        <br>
                                        <span class="ro-plate-chip"><i class="fas fa-car me-1"></i>{{ $inspection->vehicle->license_plate }}</span>
                                    @else
                                        <span style="color:#94a3b8;">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        // Multi-select services live on the appointment (the Repair Order
                                        // Edit page's source of truth); fall back to the inspection's
                                        // own single service_type.
                                        $svcSource = optional($inspection->appointment)->service_types
                                            ?? $inspection->service_type;
                                        $svcDecoded = is_array($svcSource) ? $svcSource : json_decode((string) $svcSource, true);
                                        $serviceTypes = is_array($svcDecoded) ? $svcDecoded : ($svcSource ? (array) $svcSource : []);
                                        $serviceTypes = array_values(array_filter($serviceTypes, fn ($v) => $v !== null && $v !== ''));
                                    @endphp
                                    @forelse($serviceTypes as $st)
                                        <span class="status-badge status-badge-info" style="margin-bottom:2px;display:inline-block;">
                                            {{ \App\Models\ServiceType::name($st) }}
                                        </span>
                                        @if(!$loop->last)<br>@endif
                                    @empty
                                        <span style="color:#94a3b8;">—</span>
                                    @endforelse
                                </td>
                                <td>
                                    <select class="rs-select"
                                            data-url="{{ route('inspections.update-repair-status', $inspection) }}"
                                            data-prev="{{ $inspection->repair_status ?? 'received' }}"
                                            style="--rs-bg:{{ $inspection->repair_status_bg }};--rs-text:{{ $inspection->repair_status_text }};--rs-border:{{ $inspection->repair_status_border }};"
                                            onchange="updateRepairStatus(this)"
                                            title="Repair status">
                                        @foreach(\App\Models\VehicleInspection::REPAIR_STATUSES as $rsKey => $rsMeta)
                                            <option value="{{ $rsKey }}" {{ ($inspection->repair_status ?? 'received') === $rsKey ? 'selected' : '' }}>{{ $rsMeta['label'] }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <span style="font-size:0.85rem;">{{ optional($inspection->created_at)->format('M d, Y') ?? '—' }}</span>
                                    <br>
                                    <small style="color:#94a3b8;font-size:0.75rem;">{{ optional($inspection->created_at)->format('h:i A') }}</small>
                                    <br>
                                    <span class="ws-days {{ $inspection->is_released ? 'is-released' : '' }}"
                                          title="{{ $inspection->is_released ? 'Released' : 'Still in workshop' }}">
                                        <i class="fas fa-clock"></i> {{ $inspection->days_in_workshop_label }} in workshop
                                    </span>
                                </td>
                                <td>
                                    @php $labor = $inspection->repair_labor_total; $total = $inspection->repair_total; @endphp
                                    @if($total > 0 || $labor > 0)
                                        <span class="ro-price">{{ $inspection->repair_total_formatted }}</span>
                                        <br>
                                        <small style="color:#64748b;font-size:0.75rem;"><i class="fas fa-wrench me-1"></i>Labor: {{ $inspection->repair_labor_total_formatted }}</small>
                                    @else
                                        <span style="color:#94a3b8;">—</span>
                                    @endif
                                </td>
                                <td>
                                    @include('inspections.partials.payment-status-badge', ['payments' => $inspection->repairOrderPayments, 'total' => $inspection->repair_total])
                                </td>
                                <td>
                                    <div class="action-group">
                                        <a href="{{ route('inspections.show', $inspection) }}" class="btn-action" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('inspections.edit', $inspection) }}" class="btn-action" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('inspections.destroy', $inspection) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this repair order?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-action" style="border-color:#fecaca;color:#dc2626;" title="Delete">
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
                        Showing {{ $inspections->firstItem() }} to {{ $inspections->lastItem() }} of {{ $inspections->total() }} repair orders
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
                    <h4>No repair orders found</h4>
                    <p>Try adjusting your filters or create a new repair order to get started.</p>
                    <a href="{{ route('inspections.create') }}" class="btn-create">
                        <i class="fas fa-plus"></i> +Walk In Repair Order
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
window.updateRepairStatus = function(sel) {
    const url = sel.dataset.url;
    const prev = sel.dataset.prev || sel.value;
    sel.classList.remove('is-error');
    sel.classList.add('is-saving');
    fetch(url, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ repair_status: sel.value })
    })
    .then(r => r.json().then(d => ({ ok: r.ok, d })))
    .then(({ ok, d }) => {
        sel.classList.remove('is-saving');
        if (ok && d.success) {
            sel.style.setProperty('--rs-bg', d.bg);
            sel.style.setProperty('--rs-text', d.text);
            sel.style.setProperty('--rs-border', d.border);
            sel.dataset.prev = sel.value;
            const pill = sel.closest('tr')?.querySelector('.ws-days');
            if (pill && typeof d.days_label === 'string') {
                pill.innerHTML = '<i class="fas fa-clock"></i> ' + d.days_label + ' in workshop';
                pill.classList.toggle('is-released', !!d.is_released);
                pill.title = d.is_released ? 'Released' : 'Still in workshop';
            }
        } else {
            sel.value = prev;
            sel.classList.add('is-error');
        }
    })
    .catch(() => {
        sel.classList.remove('is-saving');
        sel.value = prev;
        sel.classList.add('is-error');
    });
};

document.addEventListener('DOMContentLoaded', function() {
    const table = document.getElementById('inspectionsTable');
    if (table) {
        initUnreadSystem(table, 'inspections', { dataAttr: 'data-id', markAllBtnId: 'markAllReadBtn' });
    }
});

// ===== Click the list avatar to upload the customer picture =====
(function () {
    function csrf() {
        const m = document.querySelector('meta[name="csrf-token"]');
        return m ? m.content : '';
    }

    document.addEventListener('click', function (e) {
        if (e.target.classList && e.target.classList.contains('ro-avatar-file')) { return; }
        const wrap = e.target.closest && e.target.closest('.ro-avatar-wrap.is-editable');
        if (!wrap) { return; }
        const input = wrap.querySelector('.ro-avatar-file');
        if (input) { input.click(); }
    });

    document.addEventListener('change', function (e) {
        const input = e.target.closest && e.target.closest('.ro-avatar-file');
        if (!input) { return; }
        const wrap = input.closest('.ro-avatar-wrap');
        const file = input.files && input.files[0];
        if (!file) { return; }

        const fd = new FormData();
        fd.append('profile_picture', file);
        wrap.classList.add('is-uploading');

        fetch(wrap.dataset.uploadUrl, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrf()
            },
            body: fd
        })
        .then(r => r.json().then(d => ({ ok: r.ok, d })))
        .then(({ ok, d }) => {
            wrap.classList.remove('is-uploading');
            if (ok && d.success) {
                const img = wrap.querySelector('.ro-avatar-img');
                if (img) { img.style.display = ''; img.src = d.profile_picture_url + '?t=' + Date.now(); }
                const ini = wrap.querySelector('.ro-avatar-initials');
                if (ini) { ini.style.display = 'none'; }
            } else {
                alert((d && d.message) || 'Upload failed');
            }
            input.value = '';
        })
        .catch(() => {
            wrap.classList.remove('is-uploading');
            input.value = '';
            alert('Upload failed');
        });
    });
})();
</script>
@endpush
