@extends('layouts.app')

@section('title', 'Parts Returns - FixIt Auto Services')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0">
                    <i class="fas fa-undo me-2"></i>Parts Returns
                </h1>
                <div>
                    <a href="{{ route('parts-procurement.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back to Orders
                    </a>
                    <a href="{{ route('parts-procurement.returns.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus-circle me-1"></i> New Return
                    </a>
                </div>
            </div>
            <p class="text-muted mb-0">Manage parts returns and refunds</p>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('parts-procurement.returns.index') }}">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label fw-bold small mb-1">Status</label>
                        <select class="form-select" name="status">
                            <option value="">All Statuses</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="submitted" {{ request('status') == 'submitted' ? 'selected' : '' }}>Submitted</option>
                            <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="processed" {{ request('status') == 'processed' ? 'selected' : '' }}>Processed</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold small mb-1">Return Type</label>
                        <select class="form-select" name="return_type">
                            <option value="">All Types</option>
                            <option value="defective" {{ request('return_type') == 'defective' ? 'selected' : '' }}>Defective</option>
                            <option value="wrong_part" {{ request('return_type') == 'wrong_part' ? 'selected' : '' }}>Wrong Part</option>
                            <option value="excess" {{ request('return_type') == 'excess' ? 'selected' : '' }}>Excess Inventory</option>
                            <option value="warranty" {{ request('return_type') == 'warranty' ? 'selected' : '' }}>Warranty</option>
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
                            <a href="{{ route('parts-procurement.returns.index') }}" class="btn btn-outline-secondary">
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

    <!-- Returns List Card -->
    <div class="card shadow-sm">
        <div class="card-header bg-white border-bottom">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-list me-2"></i>Returns List
                    <span class="badge bg-primary ms-2">{{ $returns->total() }}</span>
                </h5>
                <div class="text-muted small">
                    Showing {{ $returns->firstItem() ?? 0 }}-{{ $returns->lastItem() ?? 0 }} of {{ $returns->total() }}
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            @if($returns->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Return #</th>
                                <th>Order #</th>
                                <th>Vendor</th>
                                <th>Type</th>
                                <th class="text-center">Items</th>
                                <th class="text-center">Amount</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($returns as $return)
                            @php
                                $statusColors = [
                                    'pending' => 'secondary',
                                    'submitted' => 'info',
                                    'approved' => 'primary',
                                    'processed' => 'warning',
                                    'completed' => 'success',
                                    'rejected' => 'danger'
                                ];
                                
                                $typeColors = [
                                    'defective' => 'danger',
                                    'wrong_part' => 'warning',
                                    'excess' => 'info',
                                    'warranty' => 'success'
                                ];
                            @endphp
                            <tr>
                                <td>
                                    <div class="fw-bold">#{{ $return->return_number }}</div>
                                    <div class="small text-muted">ID: {{ $return->id }}</div>
                                </td>
                                <td>
                                    @if($return->partsOrder)
                                        <a href="{{ route('parts-procurement.show', $return->partsOrder) }}" class="text-decoration-none">
                                            #{{ $return->partsOrder->order_number }}
                                        </a>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    @if($return->vendor)
                                        {{ $return->vendor->name }}
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-{{ $typeColors[$return->return_type] ?? 'secondary' }}">
                                        {{ ucfirst(str_replace('_', ' ', $return->return_type)) }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-secondary">{{ $return->items_count ?? 0 }}</span>
                                </td>
                                <td class="text-center">
                                    <div class="fw-bold">${{ number_format($return->total_amount, 2) }}</div>
                                    @if($return->refund_amount)
                                        <div class="small text-muted">
                                            Refund: ${{ number_format($return->refund_amount, 2) }}
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-{{ $statusColors[$return->status] ?? 'secondary' }}">
                                        {{ ucfirst($return->status) }}
                                    </span>
                                    @if($return->status == 'pending')
                                        <div class="small text-muted mt-1">
                                            <i class="fas fa-clock me-1"></i> Needs attention
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <div>{{ $return->created_at->format('M d, Y') }}</div>
                                    <div class="small text-muted">{{ $return->created_at->format('h:i A') }}</div>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="{{ route('parts-procurement.returns.show', $return) }}" class="btn btn-outline-info" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if($return->status == 'pending')
                                            <a href="{{ route('parts-procurement.returns.edit', $return) }}" class="btn btn-outline-primary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endif
                                        @if(in_array($return->status, ['pending', 'submitted']))
                                            <form action="{{ route('parts-procurement.returns.destroy', $return) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger" 
                                                        onclick="return confirm('Delete this return?')" title="Delete">
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
                @if($returns->hasPages())
                <div class="card-footer bg-white border-top">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted small">
                            Page {{ $returns->currentPage() }} of {{ $returns->lastPage() }}
                        </div>
                        <div>
                            {{ $returns->links() }}
                        </div>
                    </div>
                </div>
                @endif
            @else
                <div class="text-center py-5">
                    <div class="mb-4">
                        <i class="fas fa-undo fa-4x text-muted"></i>
                    </div>
                    <h4 class="text-muted mb-3">No Returns Found</h4>
                    <p class="text-muted mb-4">
                        @if(request()->hasAny(['status', 'return_type', 'date_from', 'date_to']))
                            No returns match your filter criteria.
                        @else
                            No parts returns have been created yet.
                        @endif
                    </p>
                    <a href="{{ route('parts-procurement.returns.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus-circle me-1"></i> Create First Return
                    </a>
                </div>
            @endif
        </div>
    </div>

    <!-- Stats Card -->
    <div class="row mt-4">
        <div class="col-md-3">
            <div class="card shadow-sm border-primary">
                <div class="card-body text-center">
                    <div class="text-primary mb-2">
                        <i class="fas fa-clock fa-2x"></i>
                    </div>
                    <h3 class="mb-1">{{ $stats['pending'] ?? 0 }}</h3>
                    <div class="text-muted small">Pending Returns</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-info">
                <div class="card-body text-center">
                    <div class="text-info mb-2">
                        <i class="fas fa-check-circle fa-2x"></i>
                    </div>
                    <h3 class="mb-1">{{ $stats['approved'] ?? 0 }}</h3>
                    <div class="text-muted small">Approved</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-success">
                <div class="card-body text-center">
                    <div class="text-success mb-2">
                        <i class="fas fa-check fa-2x"></i>
                    </div>
                    <h3 class="mb-1">{{ $stats['completed'] ?? 0 }}</h3>
                    <div class="text-muted small">Completed</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-warning">
                <div class="card-body text-center">
                    <div class="text-warning mb-2">
                        <i class="fas fa-dollar-sign fa-2x"></i>
                    </div>
                    <h3 class="mb-1">${{ number_format($stats['total_amount'] ?? 0, 2) }}</h3>
                    <div class="text-muted small">Total Refunds</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection