{{-- 
==========================================================
 SIDEBAR — Premium Dark Design with Live Counters & Badges
==========================================================
--}}
@php
    // Efficient counters — all computed once
    // Counters match what's visible on each module's default index page
    $sidebarCounts = [
        'customers'          => \App\Models\Customer::count(),
        'vehicles'           => \App\Models\Vehicle::count(),
        // Appointments: scheduled + confirmed — matches default "Scheduled" tab listing
        'appointments_total' => \App\Models\Appointment::whereIn('appointment_status', ['scheduled', 'confirmed', 'customer_booked'])->count(),
        // New appointments: unviewed scheduled + confirmed + customer_booked ones
        'appointments_new'   => \App\Models\Appointment::whereIn('appointment_status', ['scheduled', 'confirmed', 'customer_booked'])->whereNull('viewed_at')->count(),
        'quotations_total'   => \App\Models\Quotation::count(),
        'quotations_new'     => \App\Models\Quotation::where('status', 'pending')->count(),
        // Inspections: excludes completed & those linked to work orders — matches default page filter
        'inspections_total'  => \App\Models\VehicleInspection::whereNotIn('inspection_status', ['completed'])
                                    ->whereNull('work_order_id')->count(),
        // New inspections: not yet viewed
        'inspections_new'    => \App\Models\VehicleInspection::whereNotIn('inspection_status', ['completed'])->whereNull('work_order_id')->whereNull('viewed_at')->count(),
        'estimates_total'    => \App\Models\Estimate::count(),
        'estimates_new'      => \App\Models\Estimate::whereNull('viewed_at')->count(),
        'work_orders_total'  => \App\Models\WorkOrder::count(),
        'work_orders_active' => \App\Models\WorkOrder::whereIn('work_order_status', ['pending', 'repairing', 'in_progress'])->whereNull('viewed_at')->count(),
        // Service Records: total of all source records (scheduled appts + repair orders + estimates + job orders)
        'service_records_total'   => (function() { $s = app(\App\Services\ServiceRecordService::class)->getSummaryCounts(); return $s['scheduledCount'] + $s['repairOrderCount'] + $s['estimateCount'] + $s['jobOrderCount']; })(),
        'service_records_recent'  => \App\Models\ServiceRecord::whereNull('viewed_at')->count(),
        'invoices_total'     => \App\Models\Invoice::count(),
        'invoices_new'       => \App\Models\Invoice::whereNull('viewed_at')->count(),
        'invoices_pending'   => \App\Models\Invoice::whereIn('status', ['draft', 'sent', 'partial', 'overdue'])->count(),
        'archives_total'     => \App\Models\Archive::count(),
    ];
@endphp

<nav class="sidebar-inner">
    {{-- Sidebar Section Label: Main Navigation --}}
    <div class="sidebar-section-label">MAIN MENU</div>

    <ul class="nav flex-column">
        {{-- Dashboard --}}
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}" data-tooltip="Dashboard">
                <i class="fas fa-tachometer-alt fa-fw"></i>
                <span>Dashboard</span>
            </a>
        </li>

        {{-- Quotations --}}
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('quotations.*') ? 'active' : '' }}" href="{{ route('quotations.index') }}" style="border-left-color:#f59e0b !important;" data-tooltip="Quotations">
                <i class="fas fa-file-invoice fa-fw"></i>
                <span>Quotations</span>
                <span class="sidebar-badge" id="s-quotations">{{ $sidebarCounts['quotations_total'] }}</span>
                @if($sidebarCounts['quotations_new'] > 0)
                    <span class="sidebar-badge-danger" id="s-quotations-new">{{ $sidebarCounts['quotations_new'] }}</span>
                @endif
            </a>
        </li>

        {{-- Customers --}}
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('customers.*') ? 'active' : '' }}" href="{{ route('customers.index') }}" data-tooltip="Customers">
                <i class="fas fa-users fa-fw"></i>
                <span>Customers</span>
                <span class="sidebar-badge" id="s-customers">{{ $sidebarCounts['customers'] }}</span>
            </a>
        </li>

        {{-- Vehicles --}}
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('vehicles.*') ? 'active' : '' }}" href="{{ route('vehicles.index') }}" data-tooltip="Vehicles">
                <i class="fas fa-car fa-fw"></i>
                <span>Vehicles</span>
                <span class="sidebar-badge" id="s-vehicles">{{ $sidebarCounts['vehicles'] }}</span>
            </a>
        </li>

        {{-- Expenses --}}
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('expenses.*') ? 'active' : '' }}" href="{{ route('expenses.index') }}" data-tooltip="Expenses">
                <i class="fas fa-money-bill-wave fa-fw"></i>
                <span>Expenses</span>
            </a>
        </li>
    </ul>

    {{-- Service Management (Collapsible) --}}
    <div class="sidebar-section-label">SERVICES</div>

    <ul class="nav flex-column">
        <li class="nav-item nav-section">
            @php
                $isServiceManagementRoute = request()->routeIs([
                    'appointments.*', 'quotations.*', 'estimates.*', 'work-orders.*',
                    'invoices.*', 'payments.*', 'inspections.*',
                    'service-records.*', 'archives.*',
                    'service-items.*', 'services.*'
                ]);
                $shouldExpand = $isServiceManagementRoute;
            @endphp

            <a class="nav-link section-toggle" href="#serviceManagementCollapse" role="button"
               aria-expanded="{{ $shouldExpand ? 'true' : 'false' }}"
               aria-controls="serviceManagementCollapse"
               id="serviceManagementToggle"
               data-tooltip="Service Management">
                <i class="fas fa-cogs fa-fw"></i>
                <span>Service Management</span>
                <i class="fas fa-chevron-{{ $shouldExpand ? 'up' : 'down' }} nav-chevron sidebar-chevron"></i>
            </a>

            <div class="collapse" id="serviceManagementCollapse">
                <ul class="nav flex-column nav-sub">
                    {{-- Appointments --}}
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('appointments.*') ? 'active' : '' }}" href="{{ route('appointments.index') }}" style="border-left-color:#3b82f6 !important;" data-tooltip="Appointments">
                            <i class="fas fa-calendar-alt fa-fw"></i>
                            <span>Appointments</span>
                            <span class="sidebar-badge" id="s-appointments">{{ $sidebarCounts['appointments_total'] }}</span>
                            @if($sidebarCounts['appointments_new'] > 0)
                                <span class="sidebar-badge-danger" id="s-appointments-new">{{ $sidebarCounts['appointments_new'] }}</span>
                            @endif
                        </a>
                    </li>

                    {{-- Inspections / Repair Orders --}}
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('inspections.*') ? 'active' : '' }}" href="{{ route('inspections.index') }}" style="border-left-color:#0ea5e9 !important;" data-tooltip="Repair Orders">
                            <i class="fas fa-tools fa-fw"></i>
                            <span>Repair Orders</span>
                            <span class="sidebar-badge" id="s-inspections">{{ $sidebarCounts['inspections_total'] }}</span>
                            @if($sidebarCounts['inspections_new'] > 0)
                                <span class="sidebar-badge-danger" id="s-inspections-new">{{ $sidebarCounts['inspections_new'] }}</span>
                            @endif
                        </a>
                    </li>

                    {{-- Estimates --}}
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('estimates.*') ? 'active' : '' }}" href="{{ route('estimates.index') }}" style="border-left-color:#8b5cf6 !important;" data-tooltip="Estimates">
                            <i class="fas fa-file-invoice-dollar fa-fw"></i>
                            <span>Estimates</span>
                            <span class="sidebar-badge" id="s-estimates">{{ $sidebarCounts['estimates_total'] }}</span>
                            @if($sidebarCounts['estimates_new'] > 0)
                                <span class="sidebar-badge-danger" id="s-estimates-new">{{ $sidebarCounts['estimates_new'] }}</span>
                            @endif
                        </a>
                    </li>

                    {{-- Job Orders / Work Orders --}}
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('work-orders.*') ? 'active' : '' }}" href="{{ route('work-orders.index') }}" style="border-left-color:#f97316 !important;" data-tooltip="Job Orders">
                            <i class="fas fa-clipboard-check fa-fw"></i>
                            <span>Job Orders</span>
                            <span class="sidebar-badge" id="s-work-orders">{{ $sidebarCounts['work_orders_total'] }}</span>
                            @if($sidebarCounts['work_orders_active'] > 0)
                                <span class="sidebar-badge-danger" id="s-work-orders-active">{{ $sidebarCounts['work_orders_active'] }}</span>
                            @endif
                        </a>
                    </li>

                    {{-- Service Records --}}
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('service-records.*') ? 'active' : '' }}" href="{{ route('service-records.index') }}" style="border-left-color:#6366f1 !important;" data-tooltip="Service Records">
                            <i class="fas fa-history fa-fw"></i>
                            <span>Service Records</span>
                            <span class="sidebar-badge" id="s-service-records">{{ $sidebarCounts['service_records_total'] }}</span>
                        </a>
                    </li>

                    {{-- Services --}}
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('service-items.*') || request()->routeIs('services.*') ? 'active' : '' }}" href="{{ route('service-items.index') }}" style="border-left-color:#8b5cf6 !important;" data-tooltip="Services">
                            <i class="fas fa-tools fa-fw"></i>
                            <span>Services</span>
                        </a>
                    </li>

                    {{-- Service Pricing --}}
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('service-pricings.*') ? 'active' : '' }}" href="{{ route('service-pricings.index') }}" style="border-left-color:#059669 !important;" data-tooltip="Service Pricing">
                            <i class="fas fa-tags fa-fw"></i>
                            <span>Service Pricing</span>
                        </a>
                    </li>

                    {{-- Archive --}}
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('archives.*') ? 'active' : '' }}" href="{{ route('archives.index') }}" style="border-left-color:#6b7280 !important;" data-tooltip="Archive">
                            <i class="fas fa-archive fa-fw"></i>
                            <span>Archive</span>
                            <span class="sidebar-badge" id="s-archives">{{ $sidebarCounts['archives_total'] }}</span>
                        </a>
                    </li>

                    {{-- Invoices & Payments --}}
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('invoices.*') || request()->routeIs('payments.*') ? 'active' : '' }}" href="{{ route('invoices.index') }}" style="border-left-color:#10b981 !important;" data-tooltip="Invoices & Payments">
                            <i class="fas fa-file-invoice-dollar fa-fw"></i>
                            <span>Invoices &amp; Payments</span>
                            <span class="sidebar-badge" id="s-invoices">{{ $sidebarCounts['invoices_total'] }}</span>
                            @if($sidebarCounts['invoices_new'] > 0)
                                <span class="sidebar-badge-danger" id="s-invoices-new">{{ $sidebarCounts['invoices_new'] }}</span>
                            @endif
                        </a>
                    </li>
                </ul>
            </div>
        </li>
    </ul>

    {{-- Other Modules --}}
    <div class="sidebar-section-label">MODULES</div>

    <ul class="nav flex-column">
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('inventory.*') ? 'active' : '' }}" href="{{ route('inventory.index') }}" data-tooltip="Inventory">
                <i class="fas fa-box fa-fw"></i>
                <span>Inventory</span>
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('personnel.*') ? 'active' : '' }}" href="{{ route('personnel.index') }}" data-tooltip="Personnel">
                <i class="fas fa-users fa-fw"></i>
                <span>Personnel</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}" href="{{ route('reports.dashboard') }}" data-tooltip="Reports">
                <i class="fas fa-chart-bar fa-fw"></i>
                <span>Reports</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('hr-payroll.*') ? 'active' : '' }}" href="{{ route('hr-payroll.dashboard') }}" data-tooltip="HR Payroll">
                <i class="fas fa-users-cog fa-fw"></i>
                <span>HR Payroll</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('settings.*') ? 'active' : '' }}" href="{{ route('settings.index') }}" data-tooltip="Settings" style="border-left-color:#6b7280 !important;">
                <i class="fas fa-cog fa-fw"></i>
                <span>Settings</span>
            </a>
        </li>
    </ul>

    {{-- Quick Stats --}}
    <div class="sidebar-footer mt-4 pb-3">
        <div class="quick-stats px-3">
            <small class="d-block" style="color:rgba(255,255,255,0.25);font-size:0.6rem;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;margin-bottom:8px;">QUICK STATS</small>
            <div class="quick-stat-item">
                <span class="quick-stat-label">Today's Appointments</span>
                <span class="quick-stat-value" id="qs-appointments-today">{{ \App\Models\Appointment::whereDate('appointment_date', \Carbon\Carbon::today())->count() }}</span>
            </div>
            <div class="quick-stat-item">
                <span class="quick-stat-label">Active Jobs</span>
                <span class="quick-stat-value" id="qs-active-jobs">{{ \App\Models\WorkOrder::whereIn('work_order_status', ['pending', 'repairing'])->count() }}</span>
            </div>
            <div class="quick-stat-item">
                <span class="quick-stat-label">Pending Invoices</span>
                <span class="quick-stat-value" id="qs-pending-invoices">{{ \App\Models\Invoice::whereIn('status', ['sent', 'partial'])->count() }}</span>
            </div>
        </div>
    </div>
</nav>

{{-- Auto-refresh sidebar counters every 30 seconds --}}
@push('scripts')
<script>
(function() {
    'use strict';

    function refreshSidebarCounters() {
        fetch('/sidebar-counters')
            .then(function(r) { return r.json(); })
            .then(function(data) {
                // Total count badges
                var mapping = {
                    's-customers': 'customers',
                    's-vehicles': 'vehicles',
                    's-appointments': 'appointments_total',
                    's-quotations': 'quotations_total',
                    's-inspections': 'inspections_total',
                    's-estimates': 'estimates_total',
                    's-work-orders': 'work_orders_total',
                    's-service-records': 'service_records_total',
                    's-archives': 'archives_total',
                    's-invoices': 'invoices_total',
                };
                for (var id in mapping) {
                    var el = document.getElementById(id);
                    if (el) el.textContent = data[mapping[id]] || 0;
                }

                // Danger/notification badges
                var dangerMapping = {
                    's-appointments-new': { key: 'appointments_new', parent: 's-appointments' },
                    's-quotations-new': { key: 'quotations_new', parent: 's-quotations' },
                    's-inspections-new': { key: 'inspections_new', parent: 's-inspections' },
                    's-estimates-new': { key: 'estimates_new', parent: 's-estimates' },
                    's-work-orders-active': { key: 'work_orders_active', parent: 's-work-orders' },

                    's-invoices-new': { key: 'invoices_new', parent: 's-invoices' },
                };
                for (var did in dangerMapping) {
                    var cfg = dangerMapping[did];
                    var el = document.getElementById(did);
                    var val = data[cfg.key] || 0;
                    if (val > 0) {
                        if (el) {
                            el.textContent = val;
                        } else {
                            // Create badge if missing
                            var parent = document.getElementById(cfg.parent);
                            if (parent) {
                                var badge = document.createElement('span');
                                badge.className = 'sidebar-badge-danger';
                                badge.id = did;
                                badge.textContent = val;
                                parent.parentNode.insertBefore(badge, parent.nextSibling);
                            }
                        }
                    } else {
                        if (el) el.remove();
                    }
                }

                // Quick stats
                var qs1 = document.getElementById('qs-appointments-today');
                if (qs1) qs1.textContent = data.appointments_total || 0;
                var qs2 = document.getElementById('qs-active-jobs');
                if (qs2) qs2.textContent = data.work_orders_active || 0;
                var qs3 = document.getElementById('qs-pending-invoices');
                if (qs3) qs3.textContent = data.invoices_pending || 0;
            })
            .catch(function() { /* silent fail */ });
    }

    // First refresh after 5 seconds
    setTimeout(refreshSidebarCounters, 5000);
    // Then every 30 seconds
    setInterval(refreshSidebarCounters, 30000);
})();
</script>
@endpush
