@extends('layouts.app')

@section('title', 'Job Orders - Fix-It Auto Services')

@push('styles')
<style>
.module-work-orders { --module-primary: #f97316; --module-primary-dark: #ea580c; --module-primary-light: #fff7ed; --module-primary-subtle: #fffbeb; }

/* Progress bar styles for work orders */
.progress-fixit {
    height: 6px;
    border-radius: 4px;
    background: #f1f5f9;
}
.progress-fixit-bar {
    height: 100%;
    border-radius: 4px;
    background: var(--module-primary);
    transition: width 0.4s ease;
}

/* Bay number chip */
.bay-chip {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 2px 10px;
    border-radius: 50px;
    font-size: 0.7rem;
    font-weight: 600;
    background: #e0f2fe;
    color: #0369a1;
}

/* Technician avatar circle */
.tech-avatar {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: var(--module-primary-light);
    color: var(--module-primary-dark);
    font-size: 0.8rem;
    font-weight: 600;
    flex-shrink: 0;
}

/* Repair approval column */
.repair-approval-column {
    min-width: 180px;
}

.btn-repair-small {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 3px 10px;
    border-radius: 6px;
    font-size: 0.7rem;
    font-weight: 500;
    border: 1px solid #e2e8f0;
    background: #fff;
    color: #475569;
    cursor: pointer;
    transition: all 0.12s;
}
.btn-repair-small:hover {
    border-color: #cbd5e1;
    background: #f8fafc;
}

/* Today's active cards */
.today-card {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 14px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.04);
    transition: box-shadow 0.15s;
}
.today-card:hover {
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}
</style>
@endpush

@section('content')
<div class="container-fluid module-work-orders">
    <!-- Page Header -->
    <div class="page-module-header">
        <div class="d-flex flex-wrap justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-3">
                <span class="module-badge"><i class="fas fa-clipboard-check"></i></span>
                <div>
                    <h1 class="module-title">Job Orders</h1>
                    <p class="module-subtitle">Manage job orders, estimates, and repair tracking</p>
                </div>
            </div>
            <div class="d-flex gap-2 mt-2 mt-sm-0">
                <a href="{{ route('work-orders.create') }}" class="btn-create">
                    <i class="fas fa-plus"></i> Create Job Order
                </a>
                <a href="{{ route('work-orders.statistics') }}" class="btn-secondary-action">
                    <i class="fas fa-chart-bar"></i> Statistics
                </a>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-card-body">
                <div class="stat-card-icon"><i class="fas fa-calendar-day"></i></div>
                <div class="stat-card-info">
                    <h3>{{ $stats['today'] }}</h3>
                    <p>Today's Orders</p>
                </div>
            </div>
        </div>
        <div class="stat-card" style="border-left-color:#f59e0b;">
            <div class="stat-card-body">
                <div class="stat-card-icon" style="background:#fef3c7;color:#d97706;">
                    <i class="fas fa-hourglass-start"></i>
                </div>
                <div class="stat-card-info">
                    <h3>{{ $stats['pending'] }}</h3>
                    <p>Pending</p>
                </div>
            </div>
        </div>
        <div class="stat-card" style="border-left-color:#0ea5e9;">
            <div class="stat-card-body">
                <div class="stat-card-icon" style="background:#e0f2fe;color:#0284c7;">
                    <i class="fas fa-wrench"></i>
                </div>
                <div class="stat-card-info">
                    <h3>{{ $stats['repairing'] }}</h3>
                    <p>Repairing</p>
                </div>
            </div>
        </div>
        <div class="stat-card" style="border-left-color:#8b5cf6;">
            <div class="stat-card-body">
                <div class="stat-card-icon" style="background:#ede9fe;color:#7c3aed;">
                    <i class="fas fa-box"></i>
                </div>
                <div class="stat-card-info">
                    <h3>{{ $stats['waiting_parts'] }}</h3>
                    <p>Waiting Parts</p>
                </div>
            </div>
        </div>
        <div class="stat-card" style="border-left-color:#10b981;">
            <div class="stat-card-body">
                <div class="stat-card-icon" style="background:#d1fae5;color:#059669;">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-card-info">
                    <h3>{{ $stats['completed'] }}</h3>
                    <p>Completed</p>
                </div>
            </div>
        </div>
        <div class="stat-card" style="border-left-color:#6366f1;">
            <div class="stat-card-body">
                <div class="stat-card-icon" style="background:#eef2ff;color:#4f46e5;">
                    <i class="fas fa-truck"></i>
                </div>
                <div class="stat-card-info">
                    <h3>{{ $stats['released'] }}</h3>
                    <p>Released</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="filter-bar">
        <form method="GET" action="{{ route('work-orders.index') }}" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-control" placeholder="Search work orders..." 
                       value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="repairing" {{ request('status') == 'repairing' ? 'selected' : '' }}>Repairing</option>
                    <option value="waiting_parts" {{ request('status') == 'waiting_parts' ? 'selected' : '' }}>Waiting Parts</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="released" {{ request('status') == 'released' ? 'selected' : '' }}>Released</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Priority</label>
                <select name="priority" class="form-select">
                    <option value="">All Priority</option>
                    <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>Low</option>
                    <option value="normal" {{ request('priority') == 'normal' ? 'selected' : '' }}>Normal</option>
                    <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>High</option>
                    <option value="emergency" {{ request('priority') == 'emergency' ? 'selected' : '' }}>Emergency</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Technician</label>
                <select name="technician_id" class="form-select">
                    <option value="">All Technicians</option>
                    @foreach($technicians as $technician)
                        <option value="{{ $technician->id }}" {{ request('technician_id') == $technician->id ? 'selected' : '' }}>
                            {{ $technician->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2" style="padding-top:1.5rem;">
                <button type="submit" class="btn-filter-primary">
                    <i class="fas fa-filter"></i> Filter
                </button>
                <a href="{{ route('work-orders.index') }}" class="btn-filter-outline">
                    <i class="fas fa-redo"></i> Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Work Orders Table -->
    <div class="main-card">
        <div class="main-card-body">
            @if($workOrders->count() > 0)
                <div class="table-responsive">
                    <table class="table-fixit">
                        <thead>
                            <tr>
                                <th>Work Order #</th>
                                <th>Customer & Vehicle</th>
                                <th>Date & Type</th>
                                <th>Priority & Status</th>
                                <th>Technician</th>
                                <th>Amount</th>
                                <th>Invoice Status</th>
                                <th>Repair Approval</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($workOrders as $workOrder)
                            <tr>
                                <td>
                                    <strong>{{ $workOrder->work_order_number }}</strong>
                                    @if($workOrder->is_warranty_work)
                                        <br><span class="status-badge status-badge-info" style="margin-top:4px;">Warranty</span>
                                    @endif
                                    @if($workOrder->is_insurance_work)
                                        <br><span class="status-badge status-badge-warning" style="margin-top:4px;">Insurance</span>
                                    @endif
                                </td>
                                <td>
                                    <div>
                                        <strong>{{ $workOrder->customer->full_name }}</strong>
                                        <br>
                                        <small style="color:#94a3b8;font-size:0.775rem;">
                                            <i class="fas fa-car me-1"></i>
                                            {{ $workOrder->vehicle->year }} {{ $workOrder->vehicle->make }} {{ $workOrder->vehicle->model }}
                                        </small>
                                        <br>
                                        <small style="color:#94a3b8;font-size:0.775rem;">{{ $workOrder->vehicle->license_plate }}</small>
                                    </div>
                                </td>
                                <td>
                                    <strong>{{ $workOrder->work_order_date->format('M d, Y') }}</strong>
                                    <br>
                                    <span style="color:#94a3b8;font-size:0.8rem;">{{ ucfirst($workOrder->work_order_type) }}</span>
                                    @if($workOrder->bay_number)
                                        <br>
                                        <span class="bay-chip"><i class="fas fa-car-side"></i> Bay #{{ $workOrder->bay_number }}</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="status-badge status-badge-{{ $workOrder->priority_color === 'warning' ? 'warning' : ($workOrder->priority_color === 'danger' ? 'danger' : 'secondary') }}" style="margin-bottom:4px;">
                                        {{ ucfirst($workOrder->priority) }}
                                    </span>
                                    <br>
                                    <span class="status-badge status-badge-{{ $workOrder->status_color === 'success' ? 'success' : ($workOrder->status_color === 'info' ? 'info' : ($workOrder->status_color === 'warning' ? 'warning' : ($workOrder->status_color === 'danger' ? 'danger' : 'secondary'))) }}">
                                        <i class="fas fa-circle" style="font-size:0.5rem;"></i>
                                        {{ ucfirst(str_replace('_', ' ', $workOrder->work_order_status)) }}
                                    </span>
                                    @if($workOrder->is_overdue)
                                        <br>
                                        <small style="color:#dc2626;font-size:0.75rem;">Overdue {{ $workOrder->days_overdue }} days</small>
                                    @endif
                                </td>
                                <td>
                                    @if($workOrder->technician)
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="tech-avatar">{{ substr($workOrder->technician->name, 0, 1) }}</div>
                                            <div>
                                                <strong style="font-size:0.85rem;">{{ $workOrder->technician->name }}</strong>
                                                <br>
                                                <small style="color:#94a3b8;font-size:0.7rem;">Technician</small>
                                            </div>
                                        </div>
                                    @else
                                        <span style="color:#94a3b8;">Not assigned</span>
                                    @endif
                                </td>
                                <td>
                                    <strong>{{ $workOrder->formatted_final_amount }}</strong>
                                    <br>
                                    <small style="color:{{ $workOrder->payment_status_color === 'success' ? '#059669' : ($workOrder->payment_status_color === 'warning' ? '#d97706' : '#dc2626') }};font-size:0.75rem;">
                                        {{ ucfirst($workOrder->payment_status) }}
                                    </small>
                                    @if($workOrder->balance_due > 0)
                                        <br>
                                        <small style="color:#dc2626;font-size:0.75rem;">Due: {{ $workOrder->formatted_balance_due }}</small>
                                    @endif
                                </td>
                                <td>
                                    @if($workOrder->invoice)
                                        <span class="status-badge status-badge-success" style="margin-bottom:4px;">
                                            <i class="fas fa-file-invoice-dollar"></i> Invoiced
                                        </span>
                                        <br>
                                        <small style="color:#94a3b8;font-size:0.75rem;">
                                            <a href="{{ route('invoices.show', $workOrder->invoice) }}" style="text-decoration:none;color:#6366f1;">
                                                {{ $workOrder->invoice->invoice_number }}
                                            </a>
                                        </small>
                                        <br>
                                        <small style="color:{{ $workOrder->invoice->status_color === 'success' ? '#059669' : '#475569' }};font-size:0.7rem;">
                                            {{ ucfirst($workOrder->invoice->status) }}
                                        </small>
                                    @else
                                        <span class="status-badge status-badge-secondary" style="margin-bottom:4px;">
                                            <i class="fas fa-clock"></i> No Invoice
                                        </span>
                                        <br>
                                        @if(in_array($workOrder->work_order_status, ['completed', 'released']))
                                            <a href="{{ route('invoices.create', ['work_order_id' => $workOrder->id]) }}" 
                                               class="btn-repair-small" style="color:#059669;border-color:#10b981;background:#d1fae5;">
                                                <i class="fas fa-plus"></i> Create Invoice
                                            </a>
                                        @else
                                            <small style="color:#94a3b8;font-size:0.7rem;">Complete work order first</small>
                                        @endif
                                    @endif
                                </td>
                                <td class="repair-approval-column" 
                                    style="background:{{ $workOrder->repair_approval_status == 'go' ? '#d1fae5' : ($workOrder->repair_approval_status == 'no_go' ? '#fee2e2' : '#f8fafc') }};">
                                    <div style="display:flex;flex-direction:column;align-items:center;gap:6px;min-height:80px;">
                                        @if($workOrder->repair_approval_status == 'pending')
                                            <span class="status-badge status-badge-secondary" style="margin-bottom:4px;">
                                                <i class="fas fa-clock"></i> PENDING REVIEW
                                            </span>
                                            <div class="dropdown">
                                                <button class="btn-repair-small dropdown-toggle" 
                                                        type="button" id="repairApprovalDropdown{{ $workOrder->id }}" 
                                                        data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="fas fa-cog"></i> Set Status
                                                </button>
                                                <ul class="dropdown-menu" aria-labelledby="repairApprovalDropdown{{ $workOrder->id }}">
                                                    <li>
                                                        <a class="dropdown-item text-success repair-approval-btn" 
                                                           href="#" data-id="{{ $workOrder->id }}" data-status="go">
                                                            <i class="fas fa-check-circle me-2"></i> <strong>Authorize</strong>
                                                            <small class="text-muted d-block mt-1">GO for Repair</small>
                                                        </a>
                                                    </li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <a class="dropdown-item text-danger repair-approval-btn" 
                                                           href="#" data-id="{{ $workOrder->id }}" data-status="no_go">
                                                            <i class="fas fa-times-circle me-2"></i> <strong>Not Cleared</strong>
                                                            <small class="text-muted d-block mt-1">NO GO for Repair</small>
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        @elseif($workOrder->repair_approval_status == 'go')
                                            <i class="fas fa-check-circle" style="color:#059669;font-size:1.4rem;"></i>
                                            <span class="status-badge status-badge-success" style="margin-bottom:2px;">
                                                <i class="fas fa-check"></i> AUTHORIZED
                                            </span>
                                            <small style="color:#64748b;font-size:0.7rem;">GO for Repair</small>
                                            <button type="button" class="btn-repair-small repair-reset-btn" 
                                                    data-id="{{ $workOrder->id }}">
                                                <i class="fas fa-redo"></i> Change
                                            </button>
                                        @elseif($workOrder->repair_approval_status == 'no_go')
                                            <i class="fas fa-times-circle" style="color:#dc2626;font-size:1.4rem;"></i>
                                            <span class="status-badge status-badge-danger" style="margin-bottom:2px;">
                                                <i class="fas fa-times"></i> NOT CLEARED
                                            </span>
                                            <small style="color:#64748b;font-size:0.7rem;">NO GO for Repair</small>
                                            <button type="button" class="btn-repair-small repair-reset-btn" 
                                                    data-id="{{ $workOrder->id }}">
                                                <i class="fas fa-redo"></i> Change
                                            </button>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="action-group">
                                        <a href="{{ route('work-orders.show', $workOrder) }}" class="btn-action" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('work-orders.edit', $workOrder) }}" class="btn-action" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        
                                        @if($workOrder->work_order_status === 'pending')
                                        <form action="{{ route('work-orders.start-work', $workOrder) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="button" class="btn-action btn-start-repair-list" 
                                                    data-workorder-id="{{ $workOrder->id }}" 
                                                    title="Start Repair"
                                                    style="color:#d97706;border-color:#fde68a;">
                                                <i class="fas fa-play"></i>
                                            </button>
                                        </form>
                                        @endif
                                        
                                        @if($workOrder->work_order_status === 'repairing')
                                        <form action="{{ route('work-orders.complete-work', $workOrder) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="button" class="btn-action btn-complete-repair-list" 
                                                    data-workorder-id="{{ $workOrder->id }}" 
                                                    title="Complete Repair"
                                                    style="color:#059669;border-color:#d1fae5;">
                                                <i class="fas fa-flag-checkered"></i>
                                            </button>
                                        </form>
                                        @endif
                                        
                                        @if($workOrder->work_order_status === 'completed')
                                        <form action="{{ route('work-orders.mark-released', $workOrder) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="button" class="btn-action btn-mark-released-list" 
                                                    data-workorder-id="{{ $workOrder->id }}" 
                                                    title="Mark as Released"
                                                    style="color:#0891b2;border-color:#cffafe;">
                                                <i class="fas fa-truck"></i>
                                            </button>
                                        </form>
                                        @endif
                                        @if(auth()->user() && in_array(auth()->user()->role, ['super_admin', 'admin', 'office_staff', 'technician']))
                                        <form action="{{ route('work-orders.destroy', $workOrder) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to move this work order to archive?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-action" style="border-color:#fde68a;color:#d97706;" title="Archive">
                                                <i class="fas fa-archive"></i>
                                            </button>
                                        </form>
                                        @endif
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
                        Showing {{ $workOrders->firstItem() }} to {{ $workOrders->lastItem() }} of {{ $workOrders->total() }} work orders
                    </div>
                    <div>
                        {{ $workOrders->links() }}
                    </div>
                </div>
            @else
                <div class="empty-state-module">
                    <div class="empty-state-icon">
                        <i class="fas fa-clipboard-list"></i>
                    </div>
                    <h4>No work orders found</h4>
                    <p>Try adjusting your filters or create a new work order to get started.</p>
                    <a href="{{ route('work-orders.create') }}" class="btn-create">
                        <i class="fas fa-plus"></i> Create First Work Order
                    </a>
                </div>
            @endif
        </div>
    </div>

    <!-- Today's Active Work Orders -->
    <div class="sub-card mt-4">
        <div class="sub-card-header">
            <h5><i class="fas fa-tools me-2" style="color:#f97316;"></i>Today's Active Work Orders</h5>
        </div>
        <div class="sub-card-body">
            @php
                $todayWorkOrders = \App\Models\WorkOrder::with(['customer', 'vehicle', 'technician'])
                    ->whereDate('work_order_date', \Carbon\Carbon::today())
                    ->whereIn('work_order_status', ['in_progress', 'approved'])
                    ->orderBy('priority', 'desc')
                    ->orderBy('created_at')
                    ->get();
            @endphp
            
            @if($todayWorkOrders->count() > 0)
                <div class="row">
                    @foreach($todayWorkOrders as $wo)
                    <div class="col-md-6 mb-3">
                        <div class="today-card">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <h6 style="font-size:0.9rem;font-weight:600;margin:0 0 4px 0;">{{ $wo->work_order_number }}</h6>
                                    <div>
                                        <strong style="font-size:0.85rem;">{{ $wo->customer->full_name }}</strong>
                                        <br>
                                        <small style="color:#94a3b8;font-size:0.75rem;">
                                            {{ $wo->vehicle->year }} {{ $wo->vehicle->make }} • {{ $wo->work_order_type }}
                                        </small>
                                    </div>
                                </div>
                                <div style="text-align:right;display:flex;flex-direction:column;gap:4px;">
                                    <span class="status-badge status-badge-{{ $wo->priority_color === 'warning' ? 'warning' : ($wo->priority_color === 'danger' ? 'danger' : 'secondary') }}">
                                        {{ ucfirst($wo->priority) }}
                                    </span>
                                    <span class="status-badge status-badge-{{ $wo->status_color === 'success' ? 'success' : ($wo->status_color === 'info' ? 'info' : ($wo->status_color === 'warning' ? 'warning' : 'secondary')) }}">
                                        {{ ucfirst(str_replace('_', ' ', $wo->work_order_status)) }}
                                    </span>
                                </div>
                            </div>
                            <div>
                                <div class="progress-fixit">
                                    <div class="progress-fixit-bar" style="width:{{ $wo->completion_percentage }}%"></div>
                                </div>
                                <small style="color:#94a3b8;font-size:0.7rem;">{{ $wo->completion_percentage }}% complete</small>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <div style="text-align:center;padding:30px 0;">
                    <i class="fas fa-check-circle" style="font-size:2.5rem;color:#d1d5db;margin-bottom:12px;"></i>
                    <p style="color:#94a3b8;margin:0;">No active work orders for today</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Start Repair Confirmation (List View)
    const startRepairListButtons = document.querySelectorAll('.btn-start-repair-list');
    startRepairListButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const workOrderId = this.getAttribute('data-workorder-id');
            
            Swal.fire({
                title: 'Start Repair?',
                text: 'Are you sure you want to start repair on this work order?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#ffc107',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, Start Repair',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Submit the form
                    this.closest('form').submit();
                }
            });
        });
    });
    
    // Complete Repair Confirmation (List View)
    const completeRepairListButtons = document.querySelectorAll('.btn-complete-repair-list');
    completeRepairListButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const workOrderId = this.getAttribute('data-workorder-id');
            
            Swal.fire({
                title: 'Complete Repair?',
                text: 'Are you sure you want to mark this repair as complete?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#198754',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, Complete Repair',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Submit the form
                    this.closest('form').submit();
                }
            });
        });
    });
    
    // Mark as Released Confirmation (List View)
    const markReleasedListButtons = document.querySelectorAll('.btn-mark-released-list');
    markReleasedListButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const workOrderId = this.getAttribute('data-workorder-id');
            
            Swal.fire({
                title: 'Mark as Released?',
                text: 'Are you sure you want to mark this work order as released to customer?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#0dcaf0',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, Mark as Released',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Submit the form
                    this.closest('form').submit();
                }
            });
        });
    });
    
    // Repair Approval Status Toggle
    const repairApprovalButtons = document.querySelectorAll('.repair-approval-btn');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    
    if (!csrfToken) {
        console.error('CSRF token not found!');
    }
    
    repairApprovalButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const workOrderId = this.getAttribute('data-id');
            const status = this.getAttribute('data-status');
            const actionText = status === 'go' ? 'Authorize' : 'Mark as Not Cleared';
            
            Swal.fire({
                title: `${actionText} this repair?`,
                text: `Are you sure you want to ${status === 'go' ? 'authorize' : 'mark as not cleared'} this repair? This can be changed later.`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: status === 'go' ? '#198754' : '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: `Yes, ${actionText}`,
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Updating...',
                        text: 'Please wait while we update the status.',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    
                    fetch(`/work-orders/${workOrderId}/update-repair-approval`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({
                            repair_approval_status: status
                        })
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        Swal.close();
                        if (data.success) {
                            Swal.fire({
                                title: 'Success!',
                                text: `Repair ${status === 'go' ? 'authorized' : 'marked as not cleared'} successfully`,
                                icon: 'success',
                                confirmButtonColor: '#198754'
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                title: 'Error!',
                                text: data.message || 'Failed to update status',
                                icon: 'error',
                                confirmButtonColor: '#dc3545'
                            });
                        }
                    })
                    .catch(error => {
                        Swal.close();
                        console.error('Error:', error);
                        Swal.fire({
                            title: 'Error!',
                            text: 'Network error occurred. Please check console for details.',
                            icon: 'error',
                            confirmButtonColor: '#dc3545'
                        });
                    });
                }
            });
        });
    });
    
    // Repair Approval Reset
    const repairResetButtons = document.querySelectorAll('.repair-reset-btn');
    
    repairResetButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const workOrderId = this.getAttribute('data-id');
            
            Swal.fire({
                title: 'Reset Approval Status?',
                text: 'Are you sure you want to reset the approval status to pending review?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#6c757d',
                cancelButtonColor: '#198754',
                confirmButtonText: 'Yes, Reset to Pending',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Resetting...',
                        text: 'Please wait while we reset the approval status.',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    
                    fetch(`/work-orders/${workOrderId}/update-repair-approval`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            repair_approval_status: 'pending'
                        })
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        Swal.close();
                        if (data.success) {
                            Swal.fire({
                                title: 'Success!',
                                text: 'Approval status reset to pending review',
                                icon: 'success',
                                confirmButtonColor: '#198754'
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                title: 'Error!',
                                text: data.message || 'Failed to reset status',
                                icon: 'error',
                                confirmButtonColor: '#dc3545'
                            });
                        }
                    })
                    .catch(error => {
                        Swal.close();
                        console.error('Error:', error);
                        Swal.fire({
                            title: 'Error!',
                            text: 'Network error occurred. Please check console for details.',
                            icon: 'error',
                            confirmButtonColor: '#dc3545'
                        });
                    });
                }
            });
        });
    });
});
</script>
@endpush
