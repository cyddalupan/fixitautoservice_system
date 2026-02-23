@extends('layouts.app')

@section('title', 'Parts Order Details - FixIt Auto Services')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-file-invoice me-2"></i>Parts Order #{{ $partsOrder->order_number }}
                    </h1>
                    <div class="text-muted">
                        Created: {{ $partsOrder->created_at->format('M d, Y h:i A') }}
                        @if($partsOrder->updated_at != $partsOrder->created_at)
                            • Updated: {{ $partsOrder->updated_at->format('M d, Y h:i A') }}
                        @endif
                    </div>
                </div>
                <div>
                    <a href="{{ route('parts-procurement.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back to Orders
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Alert -->
    <div class="row mb-4">
        <div class="col-12">
            @php
                $statusColors = [
                    'draft' => 'secondary',
                    'submitted' => 'info',
                    'approved' => 'primary',
                    'ordered' => 'warning',
                    'shipped' => 'info',
                    'delivered' => 'success',
                    'cancelled' => 'danger',
                    'returned' => 'dark'
                ];
                
                $statusIcons = [
                    'draft' => 'fas fa-edit',
                    'submitted' => 'fas fa-paper-plane',
                    'approved' => 'fas fa-check-circle',
                    'ordered' => 'fas fa-shopping-cart',
                    'shipped' => 'fas fa-shipping-fast',
                    'delivered' => 'fas fa-box-open',
                    'cancelled' => 'fas fa-times-circle',
                    'returned' => 'fas fa-undo'
                ];
            @endphp
            
            <div class="alert alert-{{ $statusColors[$partsOrder->status] ?? 'secondary' }} d-flex align-items-center">
                <i class="{{ $statusIcons[$partsOrder->status] ?? 'fas fa-info-circle' }} fa-2x me-3"></i>
                <div class="flex-grow-1">
                    <h5 class="alert-heading mb-1">Order Status: 
                        <span class="text-uppercase">{{ $partsOrder->status }}</span>
                    </h5>
                    <div class="mb-0">
                        @if($partsOrder->status == 'draft')
                            This order is in draft mode. Submit it for approval when ready.
                        @elseif($partsOrder->status == 'submitted')
                            Order submitted and waiting for approval.
                        @elseif($partsOrder->status == 'approved')
                            Order approved and ready to be placed with vendor.
                        @elseif($partsOrder->status == 'ordered')
                            Order has been placed with vendor.
                        @elseif($partsOrder->status == 'shipped')
                            Order has been shipped by vendor.
                            @if($partsOrder->estimated_delivery)
                                Estimated delivery: {{ $partsOrder->estimated_delivery->format('M d, Y') }}
                            @endif
                        @elseif($partsOrder->status == 'delivered')
                            Order has been delivered and received.
                        @elseif($partsOrder->status == 'cancelled')
                            Order has been cancelled.
                            @if($partsOrder->cancellation_reason)
                                Reason: {{ $partsOrder->cancellation_reason }}
                            @endif
                        @elseif($partsOrder->status == 'returned')
                            Order has been returned.
                        @endif
                    </div>
                </div>
                <div class="text-end">
                    <div class="fw-bold">Total: ${{ number_format($partsOrder->total_amount, 2) }}</div>
                    <div class="small text-muted">{{ count($partsOrder->items) }} item(s)</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Left Column: Order Details -->
        <div class="col-lg-8">
            <!-- Order Information Card -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>Order Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-sm">
                                <tr>
                                    <th width="140">Order Number:</th>
                                    <td>
                                        <code class="fs-5">{{ $partsOrder->order_number }}</code>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Vendor:</th>
                                    <td>
                                        @if($partsOrder->vendor)
                                            <strong>{{ $partsOrder->vendor->name }}</strong>
                                            @if($partsOrder->vendor->contact_phone)
                                                <div class="small text-muted">
                                                    <i class="fas fa-phone me-1"></i> {{ $partsOrder->vendor->contact_phone }}
                                                </div>
                                            @endif
                                        @else
                                            <span class="text-muted">Not specified</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Order Type:</th>
                                    <td>
                                        <span class="badge bg-{{ $partsOrder->order_type == 'emergency' ? 'danger' : 'info' }}">
                                            {{ ucfirst($partsOrder->order_type) }}
                                        </span>
                                        @if($partsOrder->order_type == 'emergency')
                                            <span class="badge bg-danger ms-1">
                                                <i class="fas fa-exclamation-triangle"></i> RUSH
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Priority:</th>
                                    <td>
                                        @php
                                            $priorityColors = [
                                                'low' => 'success',
                                                'normal' => 'info',
                                                'high' => 'warning',
                                                'urgent' => 'danger'
                                            ];
                                        @endphp
                                        <span class="badge bg-{{ $priorityColors[$partsOrder->priority] ?? 'secondary' }}">
                                            {{ ucfirst($partsOrder->priority) }}
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm">
                                <tr>
                                    <th width="140">Created By:</th>
                                    <td>
                                        @if($partsOrder->createdBy)
                                            {{ $partsOrder->createdBy->name }}
                                        @else
                                            <span class="text-muted">System</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Approved By:</th>
                                    <td>
                                        @if($partsOrder->approvedBy)
                                            {{ $partsOrder->approvedBy->name }}
                                            @if($partsOrder->approved_at)
                                                <div class="small text-muted">
                                                    {{ $partsOrder->approved_at->format('M d, Y h:i A') }}
                                                </div>
                                            @endif
                                        @else
                                            <span class="text-muted">Pending approval</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Work Order:</th>
                                    <td>
                                        @if($partsOrder->workOrder)
                                            <a href="{{ route('work-orders.show', $partsOrder->workOrder) }}" class="text-decoration-none">
                                                #{{ $partsOrder->workOrder->work_order_number }}
                                            </a>
                                            <div class="small text-muted">
                                                {{ $partsOrder->workOrder->customer->name ?? 'Unknown Customer' }}
                                            </div>
                                        @else
                                            <span class="text-muted">Not linked</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Vehicle:</th>
                                    <td>
                                        @if($partsOrder->vehicle)
                                            {{ $partsOrder->vehicle->year }} {{ $partsOrder->vehicle->make }} {{ $partsOrder->vehicle->model }}
                                            @if($partsOrder->vehicle->vin)
                                                <div class="small text-muted">
                                                    VIN: {{ $partsOrder->vehicle->vin }}
                                                </div>
                                            @endif
                                        @else
                                            <span class="text-muted">Not specified</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    @if($partsOrder->notes)
                    <div class="row mt-3">
                        <div class="col-12">
                            <h6><i class="fas fa-sticky-note me-2"></i>Notes</h6>
                            <div class="card card-body bg-light">
                                {{ $partsOrder->notes }}
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($partsOrder->special_instructions)
                    <div class="row mt-3">
                        <div class="col-12">
                            <h6><i class="fas fa-exclamation-circle me-2"></i>Special Instructions</h6>
                            <div class="card card-body bg-warning bg-opacity-10">
                                {{ $partsOrder->special_instructions }}
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Order Items Card -->
            <div class="card shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-list me-2"></i>Order Items
                            <span class="badge bg-primary ms-2">{{ count($partsOrder->items) }}</span>
                        </h5>
                        <div class="text-muted small">
                            Total: ${{ number_format($partsOrder->total_amount, 2) }}
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Part</th>
                                    <th class="text-center">Quantity</th>
                                    <th class="text-center">Unit Price</th>
                                    <th class="text-center">Total</th>
                                    <th class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($partsOrder->items as $item)
                                <tr>
                                    <td>
                                        <div class="fw-bold">{{ $item->part_name }}</div>
                                        <div class="small text-muted">
                                            <code>{{ $item->part_number }}</code>
                                            @if($item->manufacturer)
                                                • {{ $item->manufacturer }}
                                            @endif
                                        </div>
                                        @if($item->description)
                                        <div class="small text-muted mt-1">
                                            {{ Str::limit($item->description, 80) }}
                                        </div>
                                        @endif
                                    </td>
                                    <td class="text-center align-middle">
                                        <span class="badge bg-secondary fs-6">{{ $item->quantity }}</span>
                                    </td>
                                    <td class="text-center align-middle">
                                        ${{ number_format($item->unit_price, 2) }}
                                    </td>
                                    <td class="text-center align-middle fw-bold">
                                        ${{ number_format($item->quantity * $item->unit_price, 2) }}
                                    </td>
                                    <td class="text-center align-middle">
                                        @php
                                            $itemStatusColors = [
                                                'pending' => 'secondary',
                                                'ordered' => 'info',
                                                'shipped' => 'warning',
                                                'received' => 'success',
                                                'backordered' => 'danger',
                                                'cancelled' => 'dark'
                                            ];
                                        @endphp
                                        <span class="badge bg-{{ $itemStatusColors[$item->status] ?? 'secondary' }}">
                                            {{ ucfirst($item->status) }}
                                        </span>
                                        @if($item->received_quantity && $item->received_quantity != $item->quantity)
                                            <div class="small text-muted">
                                                Received: {{ $item->received_quantity }}/{{ $item->quantity }}
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="3" class="text-end">Subtotal:</th>
                                    <th class="text-center">${{ number_format($partsOrder->subtotal_amount, 2) }}</th>
                                    <th></th>
                                </tr>
                                @if($partsOrder->tax_amount > 0)
                                <tr>
                                    <th colspan="3" class="text-end">Tax:</th>
                                    <th class="text-center">${{ number_format($partsOrder->tax_amount, 2) }}</th>
                                    <th></th>
                                </tr>
                                @endif
                                @if($partsOrder->shipping_amount > 0)
                                <tr>
                                    <th colspan="3" class="text-end">Shipping:</th>
                                    <th class="text-center">${{ number_format($partsOrder->shipping_amount, 2) }}</th>
                                    <th></th>
                                </tr>
                                @endif
                                @if($partsOrder->discount_amount > 0)
                                <tr>
                                    <th colspan="3" class="text-end">Discount:</th>
                                    <th class="text-center">-${{ number_format($partsOrder->discount_amount, 2) }}</th>
                                    <th></th>
                                </tr>
                                @endif
                                <tr class="table-active">
                                    <th colspan="3" class="text-end fs-5">Total:</th>
                                    <th class="text-center fs-5">${{ number_format($partsOrder->total_amount, 2) }}</th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Actions & Timeline -->
        <div class="col-lg-4">
            <!-- Actions Card -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">
                        <i class="fas fa-cogs me-2"></i>Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @if($partsOrder->status == 'draft')
                            <a href="{{ route('parts-procurement.edit', $partsOrder) }}" class="btn btn-primary">
                                <i class="fas fa-edit me-1"></i> Edit Order
                            </a>
                            <form action="{{ route('parts-procurement.submit', $partsOrder) }}" method="POST" class="d-grid">
                                @csrf
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-paper-plane me-1"></i> Submit for Approval
                                </button>
                            </form>
                            <form action="{{ route('parts-procurement.destroy', $partsOrder) }}" method="POST" class="d-grid">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger" 
                                        onclick="return confirm('Are you sure you want to delete this order?')">
                                    <i class="fas fa-trash me-1"></i> Delete Order
                                </button>
                            </form>
                        @elseif($partsOrder->status == 'submitted')
                            @can('approve', $partsOrder)
                            <form action="{{ route('parts-procurement.approve', $partsOrder) }}" method="POST" class="d-grid">
                                @csrf
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-check-circle me-1"></i> Approve Order
                                </button>
                            </form>
                            @endcan
                            <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#cancelModal">
                                <i class="fas fa-times-circle me-1"></i> Cancel Order
                            </button>
                        @elseif($partsOrder->status == 'approved')
                            <form action="{{ route('parts-procurement.ship', $partsOrder) }}" method="POST" class="d-grid">
                                @csrf
                                <button type="submit" class="btn btn-warning">
                                    <i class="fas fa-shipping-fast me-1"></i> Mark as Ordered
                                </button>
                            </form>
                        @elseif($partsOrder->status == 'ordered')
                            <form action="{{ route('parts-procurement.deliver', $partsOrder) }}" method="POST" class="d-grid">
                                @csrf
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-box-open me-1"></i> Mark as Delivered
                                </button>
                            </form>
                        @endif

                        @if($partsOrder->status != 'cancelled' && $partsOrder->status != 'returned')
                            <a href="{{ route('parts-procurement.create-return', $partsOrder) }}" class="btn btn-outline-danger">
                                <i class="fas fa-undo me-1"></i> Create Return
                            </a>
                            <a href="{{ route('parts-procurement.create-core-return', $partsOrder) }}" class="btn btn-outline-info">
                                <i class="fas fa-recycle me-1"></i> Core Return
                            </a>
                        @endif

                        <a href="{{ route('parts-procurement.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-list me-1"></i> View All Orders
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Timeline Card -->
            <div class="card shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">
                        <i class="fas fa-history me-2"></i>Order Timeline
                    </h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        @php
                            $timelineEvents = [];
                            
                            // Created
                            $timelineEvents[] = [
                                'date' => $partsOrder->created_at,
                                'event' => 'Order Created',
                                'user' => $partsOrder->createdBy->name ?? 'System',
                                'icon' => 'fas fa-plus-circle',
                                'color' => 'primary'
                            ];
                            
                            // Submitted
                            if($partsOrder->submitted_at) {
                                $timelineEvents[] = [
                                    'date' => $partsOrder->submitted_at,
                                    'event' => 'Order Submitted',
                                    'user' => $partsOrder->createdBy->name ?? 'System',
                                    'icon' => 'fas fa-paper-plane',
                                    'color' => 'info'
                                ];
                            }
                            
                            // Approved
                            if($partsOrder->approved_at) {
                                $timelineEvents[] = [
                                    'date' => $partsOrder->approved_at,
                                    'event' => 'Order Approved',
                                    'user' => $partsOrder->approvedBy->name ?? 'Approver',
                                    'icon' => 'fas fa-check-circle',
                                    'color' => 'success'
                                ];
                            }
                            
                            // Ordered
                            if($partsOrder->ordered_at) {
                                $timelineEvents[] = [
                                    'date' => $partsOrder->ordered_at,
                                    'event' => 'Order Placed with Vendor',
                                    'user' => 'System',
                                    'icon' => 'fas fa-shopping-cart',
                                    'color' => 'warning'
                                ];
                            }
                            
                            // Shipped
                            if($partsOrder->shipped_at) {
                                $timelineEvents[] = [
                                    'date' => $partsOrder->shipped_at,
                                    'event' => 'Order Shipped',
                                    'user' => 'Vendor',
                                    'icon' => 'fas fa-shipping-fast',
                                    'color' => 'info'
                                ];
                            }
                            
                            // Delivered
                            if($partsOrder->delivered_at) {
                                $timelineEvents[] = [
                                    'date' => $partsOrder->delivered_at,
                                    'event' => 'Order Delivered',
                                    'user' => 'System',
                                    'icon' => 'fas fa-box-open',
                                    'color' => 'success'
                                ];
                            }
                            
                            // Cancelled
                            if($partsOrder->cancelled_at) {
                                $timelineEvents[] = [
                                    'date' => $partsOrder->cancelled_at,
                                    'event' => 'Order Cancelled',
                                    'user' => $partsOrder->cancelledBy->name ?? 'System',
                                    'icon' => 'fas fa-times-circle',
                                    'color' => 'danger'
                                ];
                            }
                            
                            // Sort by date
                            usort($timelineEvents, function($a, $b) {
                                return $a['date'] <=> $b['date'];
                            });
                        @endphp
                        
                        @foreach($timelineEvents as $event)
                        <div class="timeline-item mb-3">
                            <div class="d-flex">
                                <div class="timeline-icon me-3">
                                    <i class="{{ $event['icon'] }} fa-lg text-{{ $event['color'] }}"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="fw-bold">{{ $event['event'] }}</div>
                                    <div class="small text-muted">
                                        {{ $event['date']->format('M d, Y h:i A') }}
                                    </div>
                                    <div class="small">
                                        By: {{ $event['user'] }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                        
                        @if(count($timelineEvents) === 0)
                        <div class="text-center text-muted py-3">
                            <i class="fas fa-history fa-2x mb-2"></i>
                            <div>No timeline events yet</div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Shipping Information Card -->
            @if($partsOrder->tracking_number || $partsOrder->estimated_delivery)
            <div class="card shadow-sm mt-4">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">
                        <i class="fas fa-truck me-2"></i>Shipping Information
                    </h5>
                </div>
                <div class="card-body">
                    @if($partsOrder->tracking_number)
                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted mb-1">Tracking Number</label>
                        <div class="font-monospace">{{ $partsOrder->tracking_number }}</div>
                        <div class="small text-muted">
                            <a href="#" class="text-decoration-none">
                                <i class="fas fa-external-link-alt me-1"></i> Track Shipment
                            </a>
                        </div>
                    </div>
                    @endif
                    
                    @if($partsOrder->estimated_delivery)
                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted mb-1">Estimated Delivery</label>
                        <div>
                            {{ $partsOrder->estimated_delivery->format('M d, Y') }}
                            @if($partsOrder->estimated_delivery->isPast())
                                <span class="badge bg-danger ms-2">Overdue</span>
                            @elseif($partsOrder->estimated_delivery->isToday())
                                <span class="badge bg-success ms-2">Today</span>
                            @elseif($partsOrder->estimated_delivery->diffInDays(now()) <= 3)
                                <span class="badge bg-warning ms-2">Soon</span>
                            @endif
                        </div>
                    </div>
                    @endif
                    
                    @if($partsOrder->shipping_carrier)
                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted mb-1">Shipping Carrier</label>
                        <div>{{ $partsOrder->shipping_carrier }}</div>
                    </div>
                    @endif
                    
                    @if($partsOrder->shipping_method)
                    <div>
                        <label class="form-label fw-bold text-muted mb-1">Shipping Method</label>
                        <div>{{ $partsOrder->shipping_method }}</div>
                    </div>
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Cancel Order Modal -->
<div class="modal fade" id="cancelModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle me-2"></i>Cancel Order
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('parts-procurement.cancel', $partsOrder) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <strong>Warning:</strong> This action cannot be undone. The order will be marked as cancelled.
                    </div>
                    
                    <div class="mb-3">
                        <label for="cancellation_reason" class="form-label">
                            <i class="fas fa-comment me-1"></i>Reason for Cancellation
                            <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control" id="cancellation_reason" name="cancellation_reason" rows="3" required placeholder="Enter reason for cancellation..."></textarea>
                        <div class="form-text">This will be recorded in the order history.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-times-circle me-1"></i> Confirm Cancellation
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.timeline {
    position: relative;
    padding-left: 20px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 9px;
    top: 0;
    bottom: 0;
    width: 2px;
    background-color: #e9ecef;
}

.timeline-item {
    position: relative;
}

.timeline-item::before {
    content: '';
    position: absolute;
    left: -20px;
    top: 5px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background-color: #6c757d;
}

.timeline-item:first-child::before {
    background-color: #0d6efd;
}

.timeline-item:last-child::before {
    background-color: #198754;
}
</style>
@endsection