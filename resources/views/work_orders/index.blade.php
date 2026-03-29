@extends('layouts.app')

@section('title', 'Job Orders - Fix-It Auto Services')

@section('content')
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-clipboard-check me-2"></i>Job Orders
            </h1>
            <p class="text-muted mb-0">Manage job orders, estimates, and repair tracking</p>
        </div>
        <div>
            <a href="{{ route('work-orders.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> Create Job Order
            </a>
            <a href="{{ route('work-orders.statistics') }}" class="btn btn-outline-primary ms-2">
                <i class="fas fa-chart-bar me-1"></i> Statistics
            </a>
        </div>
    </div>
</div>

<!-- Quick Stats -->
<div class="row mb-4">
    <div class="col-md-2">
        <div class="card bg-primary text-white">
            <div class="card-body text-center">
                <h3 class="mb-0">{{ $stats['today'] }}</h3>
                <p class="mb-0">Today's Orders</p>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card bg-warning text-white">
            <div class="card-body text-center">
                <h3 class="mb-0">{{ $stats['pending'] }}</h3>
                <p class="mb-0">Pending</p>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card bg-info text-white">
            <div class="card-body text-center">
                <h3 class="mb-0">{{ $stats['repairing'] }}</h3>
                <p class="mb-0">Repairing</p>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card bg-secondary text-white">
            <div class="card-body text-center">
                <h3 class="mb-0">{{ $stats['waiting_parts'] }}</h3>
                <p class="mb-0">Waiting Parts</p>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card bg-success text-white">
            <div class="card-body text-center">
                <h3 class="mb-0">{{ $stats['completed'] }}</h3>
                <p class="mb-0">Completed</p>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card bg-success text-white">
            <div class="card-body text-center">
                <h3 class="mb-0">{{ $stats['released'] }}</h3>
                <p class="mb-0">Released</p>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('work-orders.index') }}" class="row g-3">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control" placeholder="Search work orders..." 
                       value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
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
                <select name="priority" class="form-select">
                    <option value="">All Priority</option>
                    <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>Low</option>
                    <option value="normal" {{ request('priority') == 'normal' ? 'selected' : '' }}>Normal</option>
                    <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>High</option>
                    <option value="emergency" {{ request('priority') == 'emergency' ? 'selected' : '' }}>Emergency</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="technician_id" class="form-select">
                    <option value="">All Technicians</option>
                    @foreach($technicians as $technician)
                        <option value="{{ $technician->id }}" {{ request('technician_id') == $technician->id ? 'selected' : '' }}>
                            {{ $technician->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter me-1"></i> Filter
                    </button>
                    <a href="{{ route('work-orders.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-redo me-1"></i> Reset
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Work Orders Table -->
<div class="card">
    <div class="card-body">
        @if($workOrders->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
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
                                    <br>
                                    <span class="badge bg-info">Warranty</span>
                                @endif
                                @if($workOrder->is_insurance_work)
                                    <br>
                                    <span class="badge bg-warning">Insurance</span>
                                @endif
                            </td>
                            <td>
                                <div>
                                    <strong>{{ $workOrder->customer->full_name }}</strong>
                                    <br>
                                    <small class="text-muted">
                                        <i class="fas fa-car me-1"></i>
                                        {{ $workOrder->vehicle->year }} {{ $workOrder->vehicle->make }} {{ $workOrder->vehicle->model }}
                                    </small>
                                    <br>
                                    <small class="text-muted">{{ $workOrder->vehicle->license_plate }}</small>
                                </div>
                            </td>
                            <td>
                                <strong>{{ $workOrder->work_order_date->format('M d, Y') }}</strong>
                                <br>
                                <span class="text-muted">{{ ucfirst($workOrder->work_order_type) }}</span>
                                @if($workOrder->bay_number)
                                    <br>
                                    <small class="text-muted">Bay #{{ $workOrder->bay_number }}</small>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ $workOrder->priority_color }}">
                                    {{ ucfirst($workOrder->priority) }}
                                </span>
                                <br>
                                <span class="badge bg-{{ $workOrder->status_color }}">
                                    {{ ucfirst(str_replace('_', ' ', $workOrder->work_order_status)) }}
                                </span>
                                @if($workOrder->is_overdue)
                                    <br>
                                    <small class="text-danger">Overdue {{ $workOrder->days_overdue }} days</small>
                                @endif
                            </td><td>
                                @if($workOrder->technician)
                                    <div class="d-flex align-items-center">
                                        <div class="customer-avatar me-2">
                                            {{ substr($workOrder->technician->name, 0, 1) }}
                                        </div>
                                        <div>
                                            <strong>{{ $workOrder->technician->name }}</strong>
                                            <br>
                                            <small class="text-muted">Technician</small>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-muted">Not assigned</span>
                                @endif
                            </td>
                            <td>
                                <strong>{{ $workOrder->formatted_final_amount }}</strong>
                                <br>
                                <small class="text-{{ $workOrder->payment_status_color }}">
                                    {{ ucfirst($workOrder->payment_status) }}
                                </small>
                                @if($workOrder->balance_due > 0)
                                    <br>
                                    <small class="text-danger">Due: {{ $workOrder->formatted_balance_due }}</small>
                                @endif
                            </td>
                            <td>
                                @if($workOrder->invoice)
                                    <span class="badge bg-success">
                                        <i class="fas fa-file-invoice-dollar me-1"></i> Invoiced
                                    </span>
                                    <br>
                                    <small class="text-muted">
                                        <a href="{{ route('invoices.show', $workOrder->invoice) }}" class="text-decoration-none">
                                            {{ $workOrder->invoice->invoice_number }}
                                        </a>
                                    </small>
                                    <br>
                                    <small class="text-{{ $workOrder->invoice->status_color }}">
                                        {{ ucfirst($workOrder->invoice->status) }}
                                    </small>
                                @else
                                    <span class="badge bg-secondary">
                                        <i class="fas fa-clock me-1"></i> No Invoice
                                    </span>
                                    <br>
                                    @if(in_array($workOrder->work_order_status, ['completed', 'released']))
                                        <a href="{{ route('invoices.create', ['work_order_id' => $workOrder->id]) }}" 
                                           class="btn btn-sm btn-outline-primary mt-1">
                                            <i class="fas fa-plus me-1"></i> Create Invoice
                                        </a>
                                    @else
                                        <small class="text-muted">Complete work order first</small>
                                    @endif
                                @endif
                            </td>
                            <td class="text-center repair-approval-column 
                                @if($workOrder->repair_approval_status == 'go') bg-success-subtle 
                                @elseif($workOrder->repair_approval_status == 'no_go') bg-danger-subtle 
                                @else bg-light @endif">
                                <div class="d-flex flex-column align-items-center justify-content-center" style="min-height: 80px;">
                                    @if($workOrder->repair_approval_status == 'pending')
                                        <div class="mb-2">
                                            <span class="badge bg-secondary px-3 py-2">
                                                <i class="fas fa-clock me-1"></i> PENDING REVIEW
                                            </span>
                                        </div>
                                        <div class="dropdown w-100 px-2">
                                            <button class="btn btn-outline-secondary dropdown-toggle w-100 py-2" 
                                                    type="button" id="repairApprovalDropdown{{ $workOrder->id }}" 
                                                    data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="fas fa-cog me-2"></i> Set Approval Status
                                            </button>
                                            <ul class="dropdown-menu w-100" aria-labelledby="repairApprovalDropdown{{ $workOrder->id }}">
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
                                        <div class="mb-2">
                                            <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                                        </div>
                                        <span class="badge bg-success px-3 py-2 mb-2">
                                            <i class="fas fa-check me-1"></i> AUTHORIZED
                                        </span>
                                        <small class="text-muted d-block mb-2">GO for Repair</small>
                                        <button type="button" class="btn btn-sm btn-outline-secondary repair-reset-btn" 
                                                data-id="{{ $workOrder->id }}">
                                            <i class="fas fa-redo me-1"></i> Change Status
                                        </button>
                                    @elseif($workOrder->repair_approval_status == 'no_go')
                                        <div class="mb-2">
                                            <i class="fas fa-times-circle fa-2x text-danger mb-2"></i>
                                        </div>
                                        <span class="badge bg-danger px-3 py-2 mb-2">
                                            <i class="fas fa-times me-1"></i> NOT CLEARED
                                        </span>
                                        <small class="text-muted d-block mb-2">NO GO for Repair</small>
                                        <button type="button" class="btn btn-sm btn-outline-secondary repair-reset-btn" 
                                                data-id="{{ $workOrder->id }}">
                                            <i class="fas fa-redo me-1"></i> Change Status
                                        </button>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('work-orders.show', $workOrder) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('work-orders.edit', $workOrder) }}" class="btn btn-sm btn-outline-secondary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    
                                    @if($workOrder->work_order_status === 'pending')
                                    <form action="{{ route('work-orders.start-work', $workOrder) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="button" class="btn btn-sm btn-warning btn-start-repair-list" data-workorder-id="{{ $workOrder->id }}" title="Start Repair">
                                            <i class="fas fa-play"></i>
                                        </button>
                                    </form>
                                    @endif
                                    
                                    @if($workOrder->work_order_status === 'repairing')
                                    <form action="{{ route('work-orders.complete-work', $workOrder) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="button" class="btn btn-sm btn-success btn-complete-repair-list" data-workorder-id="{{ $workOrder->id }}" title="Complete Repair">
                                            <i class="fas fa-flag-checkered"></i>
                                        </button>
                                    </form>
                                    @endif
                                    
                                    @if($workOrder->work_order_status === 'completed')
                                    <form action="{{ route('work-orders.mark-released', $workOrder) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="button" class="btn btn-sm btn-info btn-mark-released-list" data-workorder-id="{{ $workOrder->id }}" title="Mark as Released">
                                            <i class="fas fa-truck"></i>
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
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div>
                    Showing {{ $workOrders->firstItem() }} to {{ $workOrders->lastItem() }} of {{ $workOrders->total() }} work orders
                </div>
                <div>
                    {{ $workOrders->links() }}
                </div>
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-clipboard-list fa-4x text-muted mb-3"></i>
                <h4 class="text-muted">No work orders found</h4>
                <p class="text-muted">Try adjusting your filters or create a new work order</p>
                <a href="{{ route('work-orders.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i> Create First Work Order
                </a>
            </div>
        @endif
    </div>
</div>

<!-- Today's Active Work Orders -->
<div class="card mt-4">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="fas fa-tools me-2"></i>Today's Active Work Orders
        </h5>
    </div>
    <div class="card-body">
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
                @foreach($todayWorkOrders as $workOrder)
                <div class="col-md-6 mb-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="card-title mb-1">{{ $workOrder->work_order_number }}</h6>
                                    <p class="card-text mb-1">
                                        <strong>{{ $workOrder->customer->full_name }}</strong>
                                        <br>
                                        <small class="text-muted">
                                            {{ $workOrder->vehicle->year }} {{ $workOrder->vehicle->make }} • 
                                            {{ $workOrder->work_order_type }}
                                        </small>
                                    </p>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-{{ $workOrder->priority_color }}">
                                        {{ ucfirst($workOrder->priority) }}
                                    </span>
                                    <br>
                                    <span class="badge bg-{{ $workOrder->status_color }}">
                                        {{ ucfirst(str_replace('_', ' ', $workOrder->work_order_status)) }}
                                    </span>
                                </div>
                            </div>
                            <div class="mt-2">
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-{{ $workOrder->status_color }}" 
                                         role="progressbar" 
                                         style="width: {{ $workOrder->completion_percentage }}%">
                                    </div>
                                </div>
                                <small class="text-muted">{{ $workOrder->completion_percentage }}% complete</small>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-4">
                <i class="fas fa-check-circle fa-3x text-muted mb-3"></i>
                <p class="text-muted">No active work orders for today</p>
            </div>
        @endif
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
            const statusText = status === 'go' ? 'Authorize (GO)' : 'Not Cleared (NO GO)';
            const actionText = status === 'go' ? 'Authorize' : 'Mark as Not Cleared';
            
            console.log('Button clicked:', { workOrderId, status, csrfToken: !!csrfToken });
            
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
                    // Show loading
                    Swal.fire({
                        title: 'Updating...',
                        text: 'Please wait while we update the status.',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    
                    // Send AJAX request
                    const url = `/work-orders/${workOrderId}/update-repair-approval`;
                    console.log('Making request to:', url);
                    
                    fetch(url, {
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
                    // Show loading
                    Swal.fire({
                        title: 'Resetting...',
                        text: 'Please wait while we reset the approval status.',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    
                    // Send AJAX request
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