@extends('layouts.app')

@section('title', 'Work Order ' . $workOrder->work_order_number . ' - Fix-It Auto Services')

@section('content')
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-clipboard-list me-2"></i>Work Order: {{ $workOrder->work_order_number }}
            </h1>
            <p class="text-muted mb-0">
                {{ $workOrder->customer->first_name ?? 'Customer' }} {{ $workOrder->customer->last_name ?? '' }} - 
                {{ $workOrder->vehicle->make ?? '' }} {{ $workOrder->vehicle->model ?? '' }}
            </p>
        </div>
        <div>
            <a href="{{ route('work-orders.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to List
            </a>
            <a href="{{ route('work-orders.edit', $workOrder) }}" class="btn btn-primary ms-2">
                <i class="fas fa-edit me-1"></i> Edit
            </a>
        </div>
    </div>
</div>

<!-- Status Badge -->
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex align-items-center">
            <span class="badge bg-{{ $workOrder->work_order_status === 'completed' ? 'success' : ($workOrder->work_order_status === 'in_progress' ? 'warning' : ($workOrder->work_order_status === 'draft' ? 'secondary' : 'info')) }} me-2">
                {{ ucfirst(str_replace('_', ' ', $workOrder->work_order_status)) }}
            </span>
            <span class="badge bg-{{ $workOrder->priority === 'high' ? 'danger' : ($workOrder->priority === 'normal' ? 'info' : 'warning') }} me-2">
                {{ ucfirst($workOrder->priority) }} Priority
            </span>
            <span class="text-muted">
                Created: {{ $workOrder->created_at->format('M d, Y h:i A') }}
            </span>
        </div>
    </div>
</div>

<div class="row">
    <!-- Left Column: Work Order Details -->
    <div class="col-md-8">
        <!-- Customer & Vehicle Information -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-user me-2"></i>Customer & Vehicle Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Customer Details</h6>
                        <p class="mb-1">
                            <strong>Name:</strong> 
                            {{ $workOrder->customer->first_name ?? 'N/A' }} {{ $workOrder->customer->last_name ?? '' }}
                        </p>
                        <p class="mb-1">
                            <strong>Phone:</strong> 
                            {{ $workOrder->customer->phone ?? 'N/A' }}
                        </p>
                        <p class="mb-1">
                            <strong>Email:</strong> 
                            {{ $workOrder->customer->email ?? 'N/A' }}
                        </p>
                    </div>
                    <div class="col-md-6">
                        <h6>Vehicle Details</h6>
                        <p class="mb-1">
                            <strong>Vehicle:</strong> 
                            {{ $workOrder->vehicle->make ?? 'N/A' }} {{ $workOrder->vehicle->model ?? '' }}
                        </p>
                        <p class="mb-1">
                            <strong>Year:</strong> 
                            {{ $workOrder->vehicle->year ?? 'N/A' }}
                        </p>
                        <p class="mb-1">
                            <strong>License Plate:</strong> 
                            {{ $workOrder->vehicle->license_plate ?? 'N/A' }}
                        </p>
                        <p class="mb-1">
                            <strong>VIN:</strong> 
                            {{ $workOrder->vehicle->vin ?? 'N/A' }}
                        </p>
                        <p class="mb-1">
                            <strong>Odometer In:</strong> 
                            {{ $workOrder->odometer_in ? number_format($workOrder->odometer_in) . ' km' : 'N/A' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Customer Concerns & Diagnosis -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-stethoscope me-2"></i>Customer Concerns & Diagnosis</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Customer Concerns</h6>
                        <p>{{ $workOrder->customer_concerns ?? 'No concerns reported.' }}</p>
                    </div>
                    <div class="col-md-6">
                        <h6>Technician Diagnosis</h6>
                        <p>{{ $workOrder->technician_diagnosis ?? 'Diagnosis pending.' }}</p>
                    </div>
                </div>
                @if($workOrder->recommended_services)
                <div class="row mt-3">
                    <div class="col-12">
                        <h6>Recommended Services</h6>
                        <p>{{ $workOrder->recommended_services }}</p>
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Items & Tasks -->
        <div class="row">
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-boxes me-2"></i>Items ({{ $workOrder->items->count() }})</h5>
                    </div>
                    <div class="card-body">
                        @if($workOrder->items->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Description</th>
                                            <th>Qty</th>
                                            <th>Cost</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($workOrder->items as $item)
                                        <tr>
                                            <td>{{ $item->description }}</td>
                                            <td>{{ $item->quantity }} {{ $item->unit }}</td>
                                            <td>₱{{ number_format($item->final_amount, 2) }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-muted mb-0">No items added yet.</p>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-tasks me-2"></i>Tasks ({{ $workOrder->tasks->count() }})</h5>
                    </div>
                    <div class="card-body">
                        @if($workOrder->tasks->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Task</th>
                                            <th>Status</th>
                                            <th>Hours</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($workOrder->tasks as $task)
                                        <tr>
                                            <td>{{ $task->task_name }}</td>
                                            <td>
                                                <span class="badge bg-{{ $task->task_status === 'completed' ? 'success' : ($task->task_status === 'in_progress' ? 'warning' : 'secondary') }}">
                                                    {{ ucfirst($task->task_status) }}
                                                </span>
                                            </td>
                                            <td>{{ $task->actual_hours > 0 ? $task->actual_hours : $task->estimated_hours }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-muted mb-0">No tasks assigned yet.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column: Summary & Actions -->
    <div class="col-md-4">
        <!-- Summary Card -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-file-invoice-dollar me-2"></i>Financial Summary</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <h6>Estimated Costs</h6>
                    <p class="mb-1 d-flex justify-content-between">
                        <span>Labor:</span>
                        <span>₱{{ number_format($workOrder->estimated_labor_cost, 2) }}</span>
                    </p>
                    <p class="mb-1 d-flex justify-content-between">
                        <span>Parts:</span>
                        <span>₱{{ number_format($workOrder->estimated_parts_cost, 2) }}</span>
                    </p>
                    <p class="mb-1 d-flex justify-content-between">
                        <span>Tax:</span>
                        <span>₱{{ number_format($workOrder->estimated_tax, 2) }}</span>
                    </p>
                    <hr>
                    <p class="mb-0 d-flex justify-content-between fw-bold">
                        <span>Estimated Total:</span>
                        <span>₱{{ number_format($workOrder->estimated_total, 2) }}</span>
                    </p>
                </div>

                @if($workOrder->work_order_status === 'completed' || $workOrder->actual_total > 0)
                <div class="mt-3">
                    <h6>Actual Costs</h6>
                    <p class="mb-1 d-flex justify-content-between">
                        <span>Labor:</span>
                        <span>₱{{ number_format($workOrder->actual_labor_cost, 2) }}</span>
                    </p>
                    <p class="mb-1 d-flex justify-content-between">
                        <span>Parts:</span>
                        <span>₱{{ number_format($workOrder->actual_parts_cost, 2) }}</span>
                    </p>
                    <p class="mb-1 d-flex justify-content-between">
                        <span>Tax:</span>
                        <span>₱{{ number_format($workOrder->actual_tax, 2) }}</span>
                    </p>
                    <hr>
                    <p class="mb-0 d-flex justify-content-between fw-bold">
                        <span>Actual Total:</span>
                        <span>₱{{ number_format($workOrder->actual_total, 2) }}</span>
                    </p>
                </div>
                @endif

                <div class="mt-3">
                    <h6>Payment Status</h6>
                    <span class="badge bg-{{ $workOrder->payment_status === 'paid' ? 'success' : ($workOrder->payment_status === 'partial' ? 'warning' : 'secondary') }}">
                        {{ ucfirst($workOrder->payment_status) }}
                    </span>
                    @if($workOrder->payment_status !== 'paid')
                        <p class="mt-2 mb-0">
                            <small>Balance Due: ₱{{ number_format($workOrder->balance_due, 2) }}</small>
                        </p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Team Assignment -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-users me-2"></i>Team Assignment</h5>
            </div>
            <div class="card-body">
                <p class="mb-2">
                    <strong>Service Advisor:</strong><br>
                    {{ $workOrder->serviceAdvisor->name ?? 'Not assigned' }}
                </p>
                <p class="mb-2">
                    <strong>Technician:</strong><br>
                    {{ $workOrder->technician->name ?? 'Not assigned' }}
                </p>
                @if($workOrder->qualityChecker)
                <p class="mb-0">
                    <strong>Quality Checker:</strong><br>
                    {{ $workOrder->qualityChecker->name }}
                </p>
                @endif
            </div>
        </div>

        <!-- Timeline -->
        @if(isset($timeline) && count($timeline) > 0)
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-history me-2"></i>Timeline</h5>
            </div>
            <div class="card-body">
                <div class="timeline">
                    @foreach($timeline as $event)
                    <div class="timeline-item mb-3">
                        <div class="timeline-marker"></div>
                        <div class="timeline-content">
                            <h6 class="mb-0">{{ $event['event'] ?? 'Event' }}</h6>
                            <small class="text-muted">{{ $event['time'] ?? '' }}</small>
                            @if(isset($event['description']) && $event['description'])
                            <p class="mb-0 mt-1 small">{{ $event['description'] }}</p>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        <!-- Actions -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-cogs me-2"></i>Actions</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    @if($workOrder->work_order_status === 'draft')
                    <a href="#" class="btn btn-success">
                        <i class="fas fa-check me-1"></i> Submit for Approval
                    </a>
                    @endif
                    
                    @if($workOrder->work_order_status === 'approved')
                    <a href="#" class="btn btn-warning">
                        <i class="fas fa-play me-1"></i> Start Work
                    </a>
                    @endif
                    
                    @if($workOrder->work_order_status === 'in_progress')
                    <a href="#" class="btn btn-success">
                        <i class="fas fa-flag-checkered me-1"></i> Complete Work
                    </a>
                    @endif
                    
                    <a href="{{ route('work-orders.edit', $workOrder) }}" class="btn btn-outline-primary">
                        <i class="fas fa-edit me-1"></i> Edit Work Order
                    </a>
                    
                    <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                        <i class="fas fa-trash me-1"></i> Delete
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Delete Work Order</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete work order <strong>{{ $workOrder->work_order_number }}</strong>?
                This action cannot be undone.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="{{ route('work-orders.destroy', $workOrder) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete Work Order</button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.timeline {
    position: relative;
    padding-left: 20px;
}
.timeline-item {
    position: relative;
}
.timeline-marker {
    position: absolute;
    left: -20px;
    top: 5px;
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background-color: #6c757d;
}
.timeline-content {
    padding-bottom: 10px;
}
</style>
@endsection