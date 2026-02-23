@extends('layouts.app')

@section('title', 'Parts Procurement - Fix-It Auto Services')

@section('content')
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-shopping-cart me-2"></i>Parts Procurement
            </h1>
            <p class="text-muted mb-0">Manage parts orders, returns, and core returns</p>
        </div>
        <div>
            <div class="btn-group">
                <a href="{{ route('parts-procurement.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i> New Order
                </a>
                <button type="button" class="btn btn-primary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false">
                    <span class="visually-hidden">Toggle Dropdown</span>
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="{{ route('parts-procurement.lookup') }}">
                        <i class="fas fa-search me-2"></i> Parts Lookup
                    </a></li>
                    <li><a class="dropdown-item" href="{{ route('parts-procurement.returns.index') }}">
                        <i class="fas fa-undo me-2"></i> View Returns
                    </a></li>
                    <li><a class="dropdown-item" href="{{ route('parts-procurement.core-returns.index') }}">
                        <i class="fas fa-recycle me-2"></i> View Core Returns
                    </a></li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-primary">Parts Orders</h6>
            </div>
            <div class="card-body">
                @if($partsOrders->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Order #</th>
                                    <th>Vendor</th>
                                    <th>Customer</th>
                                    <th>Vehicle</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Order Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($partsOrders as $order)
                                    <tr>
                                        <td>
                                            <strong>{{ $order->order_number }}</strong>
                                            @if($order->workOrder)
                                                <div class="text-muted small">WO: {{ $order->workOrder->work_order_number }}</div>
                                            @endif
                                        </td>
                                        <td>{{ $order->vendor->name ?? 'N/A' }}</td>
                                        <td>
                                            @if($order->customer)
                                                {{ $order->customer->first_name }} {{ $order->customer->last_name }}
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($order->vehicle)
                                                <small>{{ $order->vehicle->year }} {{ $order->vehicle->make }} {{ $order->vehicle->model }}</small>
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td>${{ number_format($order->total, 2) }}</td>
                                        <td>
                                            <span class="badge bg-{{ $order->status_color }}">
                                                {{ ucfirst($order->status) }}
                                            </span>
                                        </td>
                                        <td>{{ $order->order_date ? $order->order_date->format('M j, Y') : 'N/A' }}</td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('parts-procurement.show', $order) }}" class="btn btn-outline-primary">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @if($order->is_editable)
                                                    <a href="{{ route('parts-procurement.edit', $order) }}" class="btn btn-outline-secondary">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="d-flex justify-content-center">
                        {{ $partsOrders->links() }}
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No parts orders found</h5>
                        <p class="text-muted mb-4">Get started by creating your first parts order</p>
                        <a href="{{ route('parts-procurement.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i> Create New Order
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Quick Stats -->
<div class="row mt-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50 mb-0">Draft Orders</h6>
                        <h3 class="mb-0">{{ \App\Models\PartsOrder::where('status', 'draft')->count() }}</h3>
                    </div>
                    <i class="fas fa-file fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50 mb-0">Pending Approval</h6>
                        <h3 class="mb-0">{{ \App\Models\PartsOrder::where('status', 'pending')->count() }}</h3>
                    </div>
                    <i class="fas fa-clock fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50 mb-0">In Transit</h6>
                        <h3 class="mb-0">{{ \App\Models\PartsOrder::where('status', 'shipped')->count() }}</h3>
                    </div>
                    <i class="fas fa-shipping-fast fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50 mb-0">Delivered</h6>
                        <h3 class="mb-0">{{ \App\Models\PartsOrder::where('status', 'delivered')->count() }}</h3>
                    </div>
                    <i class="fas fa-check-circle fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection