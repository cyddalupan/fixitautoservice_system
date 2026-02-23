@extends('layouts.app')

@section('title', 'Core Returns - FixIt Auto Services')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0">
                    <i class="fas fa-recycle me-2"></i>Core Returns
                </h1>
                <div>
                    <a href="{{ route('parts-procurement.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back to Orders
                    </a>
                    <a href="{{ route('parts-procurement.core-returns.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus-circle me-1"></i> New Core Return
                    </a>
                </div>
            </div>
            <p class="text-muted mb-0">Manage core charge returns and refunds</p>
        </div>
    </div>

    <!-- Info Alert -->
    <div class="alert alert-info mb-4">
        <div class="d-flex align-items-center">
            <i class="fas fa-info-circle fa-2x me-3"></i>
            <div>
                <h5 class="alert-heading mb-1">What are Core Returns?</h5>
                <p class="mb-0">
                    Core returns are when you return old parts (cores) to vendors to get back core charges paid when purchasing new parts.
                    This helps recover costs and is common for parts like alternators, starters, and other rebuildable components.
                </p>
            </div>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('parts-procurement.core-returns.index') }}">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label fw-bold small mb-1">Status</label>
                        <select class="form-select" name="status">
                            <option value="">All Statuses</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="packaged" {{ request('status') == 'packaged' ? 'selected' : '' }}>Packaged</option>
                            <option value="shipped" {{ request('status') == 'shipped' ? 'selected' : '' }}>Shipped</option>
                            <option value="received" {{ request('status') == 'received' ? 'selected' : '' }}>Received by Vendor</option>
                            <option value="refunded" {{ request('status') == 'refunded' ? 'selected' : '' }}>Refunded</option>
                            <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold small mb-1">Vendor</label>
                        <select class="form-select" name="vendor_id">
                            <option value="">All Vendors</option>
                            @foreach($vendors as $vendor)
                                <option value="{{ $vendor->id }}" {{ request('vendor_id') == $vendor->id ? 'selected' : '' }}>
                                    {{ $vendor->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold small mb-1">Date From</label>
                        <input type="date" class="form-control" name="date_from" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold small mb-1">Date To</label>
                        <input type="date" class="form-control" name="date_to" value="{{ request('date_to') }}">
                    </div>
                    <div class="col-12">
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('parts-procurement.core-returns.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-redo me-1"></i> Reset
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-filter me-1"></i> Apply Filters
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Core Returns List Card -->
    <div class="card shadow-sm">
        <div class="card-header bg-white border-bottom">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-list me-2"></i>Core Returns List
                    <span class="badge bg-primary ms-2">{{ $coreReturns->total() }}</span>
                </h5>
                <div class="text-muted small">
                    Showing {{ $coreReturns->firstItem() ?? 0 }}-{{ $coreReturns->lastItem() ?? 0 }} of {{ $coreReturns->total() }}
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            @if($coreReturns->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Core Return #</th>
                                <th>Vendor</th>
                                <th>Parts Order</th>
                                <th class="text-center">Cores</th>
                                <th class="text-center">Core Value</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($coreReturns as $coreReturn)
                            @php
                                $statusColors = [
                                    'pending' => 'secondary',
                                    'packaged' => 'info',
                                    'shipped' => 'warning',
                                    'received' => 'primary',
                                    'refunded' => 'success',
                                    'rejected' => 'danger'
                                ];
                                
                                $statusIcons = [
                                    'pending' => 'fas fa-clock',
                                    'packaged' => 'fas fa-box',
                                    'shipped' => 'fas fa-shipping-fast',
                                    'received' => 'fas fa-check-circle',
                                    'refunded' => 'fas fa-dollar-sign',
                                    'rejected' => 'fas fa-times-circle'
                                ];
                            @endphp
                            <tr>
                                <td>
                                    <div class="fw-bold">#{{ $coreReturn->core_return_number }}</div>
                                    <div class="small text-muted">ID: {{ $coreReturn->id }}</div>
                                </td>
                                <td>
                                    @if($coreReturn->vendor)
                                        <strong>{{ $coreReturn->vendor->name }}</strong>
                                        @if($coreReturn->vendor->contact_phone)
                                            <div class="small text-muted">
                                                <i class="fas fa-phone me-1"></i> {{ $coreReturn->vendor->contact_phone }}
                                            </div>
                                        @endif
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    @if($coreReturn->partsOrder)
                                        <a href="{{ route('parts-procurement.show', $coreReturn->partsOrder) }}" class="text-decoration-none">
                                            #{{ $coreReturn->partsOrder->order_number }}
                                        </a>
                                        <div class="small text-muted">
                                            {{ $coreReturn->partsOrder->created_at->format('M d, Y') }}
                                        </div>
                                    @else
                                        <span class="text-muted">Not linked</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-secondary">{{ $coreReturn->items_count ?? 0 }}</span>
                                </td>
                                <td class="text-center">
                                    <div class="fw-bold">${{ number_format($coreReturn->total_core_value, 2) }}</div>
                                    @if($coreReturn->refund_amount)
                                        <div class="small text-success">
                                            Refund: ${{ number_format($coreReturn->refund_amount, 2) }}
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <i class="{{ $statusIcons[$coreReturn->status] ?? 'fas fa-info-circle' }} me-2 text-{{ $statusColors[$coreReturn->status] ?? 'secondary' }}"></i>
                                        <span class="badge bg-{{ $statusColors[$coreReturn->status] ?? 'secondary' }}">
                                            {{ ucfirst($coreReturn->status) }}
                                        </span>
                                    </div>
                                    @if($coreReturn->status == 'pending')
                                        <div class="small text-muted mt-1">
                                            <i class="fas fa-exclamation-circle me-1"></i> Needs packaging
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <div>{{ $coreReturn->created_at->format('M d, Y') }}</div>
                                    <div class="small text-muted">{{ $coreReturn->created_at->format('h:i A') }}</div>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="{{ route('parts-procurement.core-returns.show', $coreReturn) }}" class="btn btn-outline-info" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if(in_array($coreReturn->status, ['pending', 'packaged']))
                                            <a href="{{ route('parts-procurement.core-returns.edit', $coreReturn) }}" class="btn btn-outline-primary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endif
                                        @if(in_array($coreReturn->status, ['pending', 'packaged']))
                                            <form action="{{ route('parts-procurement.core-returns.destroy', $coreReturn) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger" 
                                                        onclick="return confirm('Delete this core return?')" title="Delete">
                                                    <i class="fas fa-trash"></i>
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
                @if($coreReturns->hasPages())
                <div class="card-footer bg-white border-top">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted small">
                            Page {{ $coreReturns->currentPage() }} of {{ $coreReturns->lastPage() }}
                        </div>
                        <div>
                            {{ $coreReturns->links() }}
                        </div>
                    </div>
                </div>
                @endif
            @else
                <div class="text-center py-5">
                    <div class="mb-4">
                        <i class="fas fa-recycle fa-4x text-muted"></i>
                    </div>
                    <h4 class="text-muted mb-3">No Core Returns Found</h4>
                    <p class="text-muted mb-4">
                        @if(request()->hasAny(['status', 'vendor_id', 'date_from', 'date_to']))
                            No core returns match your filter criteria.
                        @else
                            No core returns have been created yet. Core returns help recover core charges from vendors.
                        @endif
                    </p>
                    <a href="{{ route('parts-procurement.core-returns.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus-circle me-1"></i> Create First Core Return
                    </a>
                </div>
            @endif
        </div>
    </div>

    <!-- Stats Card -->
    <div class="row mt-4">
        <div class="col-md-3">
            <div class="card shadow-sm border-secondary">
                <div class="card-body text-center">
                    <div class="text-secondary mb-2">
                        <i class="fas fa-clock fa-2x"></i>
                    </div>
                    <h3 class="mb-1">{{ $stats['pending'] ?? 0 }}</h3>
                    <div class="text-muted small">Pending</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-info">
                <div class="card-body text-center">
                    <div class="text-info mb-2">
                        <i class="fas fa-box fa-2x"></i>
                    </div>
                    <h3 class="mb-1">{{ $stats['packaged'] ?? 0 }}</h3>
                    <div class="text-muted small">Packaged</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-warning">
                <div class="card-body text-center">
                    <div class="text-warning mb-2">
                        <i class="fas fa-shipping-fast fa-2x"></i>
                    </div>
                    <h3 class="mb-1">{{ $stats['shipped'] ?? 0 }}</h3>
                    <div class="text-muted small">Shipped</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-success">
                <div class="card-body text-center">
                    <div class="text-success mb-2">
                        <i class="fas fa-dollar-sign fa-2x"></i>
                    </div>
                    <h3 class="mb-1">${{ number_format($stats['total_refunded'] ?? 0, 2) }}</h3>
                    <div class="text-muted small">Total Refunded</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Core Return Process Info -->
    <div class="card shadow-sm mt-4">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0">
                <i class="fas fa-info-circle me-2"></i>Core Return Process
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <div class="text-center mb-3">
                        <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center mb-2" style="width: 60px; height: 60px;">
                            <i class="fas fa-box-open fa-2x"></i>
                        </div>
                        <h6>1. Collect Cores</h6>
                        <p class="small text-muted mb-0">Gather old parts with core charges</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center mb-3">
                        <div class="rounded-circle bg-info text-white d-inline-flex align-items-center justify-content-center mb-2" style="width: 60px; height: 60px;">
                            <i class="fas fa-edit fa-2x"></i>
                        </div>
                        <h6>2. Create Return</h6>
                        <p class="small text-muted mb-0">Document cores in system</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center mb-3">
                        <div class="rounded-circle bg-warning text-white d-inline-flex align-items-center justify-content-center mb-2" style="width: 60px; height: 60px;">
                            <i class="fas fa-shipping-fast fa-2x"></i>
                        </div>
                        <h6>3. Ship to Vendor</h6>
                        <p class="small text-muted mb-0">Package and ship cores</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center mb-3">
                        <div class="rounded-circle bg-success text-white d-inline-flex align-items-center justify-content-center mb-2" style="width: 60px; height: 60px;">
                            <i class="fas fa-check-circle fa-2x"></i>
                        </div>
                        <h6>4. Receive Refund</h6>
                        <p class="small text-muted mb-0">Get core charge refund</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection