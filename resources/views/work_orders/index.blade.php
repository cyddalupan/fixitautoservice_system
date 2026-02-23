@extends('layouts.app')

@section('title', 'Work Orders - Fix-It Auto Services')

@section('content')
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-clipboard-list me-2"></i>Work Orders
            </h1>
            <p class="text-muted mb-0">Manage repair orders, estimates, and job tracking</p>
        </div>
        <div>
            <a href="{{ route('work-orders.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> Create Work Order
            </a>
            <a href="{{ route('work-orders.statistics') }}" class="btn btn-outline-primary ms-2">
                <i class="fas fa-chart-bar me-1"></i> Statistics
            </a>
        </div>
    </div>
</div>

<!-- Quick Stats -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body text-center">
                <h3 class="mb-0">{{ $stats['today'] }}</h3>
                <p class="mb-0">Today's Work Orders</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body text-center">
                <h3 class="mb-0">{{ $stats['in_progress'] }}</h3>
                <p class="mb-0">In Progress</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body text-center">
                <h3 class="mb-0">{{ $stats['completed'] }}</h3>
                <p class="mb-0">Completed</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-danger text-white">
            <div class="card-body text-center">
                <h3 class="mb-0">{{ $stats['overdue'] }}</h3>
                <p class="mb-0">Overdue</p>
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
                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="pending_approval" {{ request('status') == 'pending_approval' ? 'selected' : '' }}>Pending Approval</option>
                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    <option value="invoiced" {{ request('status') == 'invoiced' ? 'selected' : '' }}>Invoiced</option>
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
                            </td>
                            <td>
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
                                <div class="btn-group" role="group">
                                    <a href="{{ route('work-orders.show', $workOrder) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('work-orders.edit', $workOrder) }}" class="btn btn-sm btn-outline-secondary">
                                        <i class="fas fa-edit"></i>
                                    </a>
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