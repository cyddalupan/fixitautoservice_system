@extends('layouts.app')

@section('title', 'Inventory Suppliers')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0">Inventory Suppliers</h1>
                <div>
                    <a href="{{ route('inventory.suppliers.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Supplier
                    </a>
                    <a href="{{ route('inventory.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Inventory
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Suppliers</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['total_suppliers']) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-truck fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Active Suppliers</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['active_suppliers']) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Preferred Suppliers</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['preferred_suppliers']) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-star fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Total Credit Limit</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">₱{{ number_format($stats['total_credit_limit'], 2) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-credit-card fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-header">
            <h6 class="m-0 font-weight-bold text-primary">Filters</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('inventory.suppliers.index') }}">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="search">Search</label>
                            <input type="text" class="form-control" id="search" name="search" 
                                   value="{{ request('search') }}" placeholder="Name, code, contact...">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="is_active">Active Status</label>
                            <select class="form-control" id="is_active" name="is_active">
                                <option value="">All</option>
                                <option value="true" {{ request('is_active') === 'true' ? 'selected' : '' }}>Active</option>
                                <option value="false" {{ request('is_active') === 'false' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="is_preferred">Preferred</label>
                            <select class="form-control" id="is_preferred" name="is_preferred">
                                <option value="">All</option>
                                <option value="true" {{ request('is_preferred') === 'true' ? 'selected' : '' }}>Preferred</option>
                                <option value="false" {{ request('is_preferred') === 'false' ? 'selected' : '' }}>Not Preferred</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="balance">Balance</label>
                            <select class="form-control" id="balance" name="balance">
                                <option value="">All</option>
                                <option value="with_balance" {{ request('balance') == 'with_balance' ? 'selected' : '' }}>With Balance</option>
                                <option value="no_balance" {{ request('balance') == 'no_balance' ? 'selected' : '' }}>No Balance</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-filter"></i> Filter
                            </button>
                        </div>
                    </div>
                    <div class="col-md-1">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <a href="{{ route('inventory.suppliers.index') }}" class="btn btn-secondary btn-block">
                                <i class="fas fa-times"></i> Clear
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Suppliers Table -->
    <div class="card">
        <div class="card-header">
            <h6 class="m-0 font-weight-bold text-primary">Suppliers List</h6>
        </div>
        <div class="card-body">
            @if($suppliers->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th>
                                    <a href="{{ route('inventory.suppliers.index', array_merge(request()->all(), ['sort' => 'name', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc'])) }}">
                                        Name
                                        @if(request('sort') == 'name')
                                            <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }}"></i>
                                        @else
                                            <i class="fas fa-sort"></i>
                                        @endif
                                    </a>
                                </th>
                                <th>Code</th>
                                <th>Contact</th>
                                <th>Credit Limit</th>
                                <th>Balance</th>
                                <th>Items</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($suppliers as $supplier)
                                <tr>
                                    <td>
                                        <strong>{{ $supplier->name }}</strong>
                                        @if($supplier->is_preferred)
                                            <span class="badge badge-warning ml-1"><i class="fas fa-star"></i> Preferred</span>
                                        @endif
                                    </td>
                                    <td>{{ $supplier->code ?? 'N/A' }}</td>
                                    <td>
                                        @if($supplier->contact_name)
                                            <div>{{ $supplier->contact_name }}</div>
                                        @endif
                                        @if($supplier->contact_phone)
                                            <small class="text-muted">{{ $supplier->contact_phone }}</small>
                                        @endif
                                    </td>
                                    <td>₱{{ number_format($supplier->credit_limit, 2) }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <span class="mr-2">₱{{ number_format($supplier->current_balance, 2) }}</span>
                                            @if($supplier->credit_limit > 0)
                                                @php
                                                    $utilization = $supplier->credit_utilization;
                                                    $badgeClass = $utilization >= 90 ? 'badge-danger' : ($utilization >= 70 ? 'badge-warning' : 'badge-success');
                                                @endphp
                                                <span class="badge {{ $badgeClass }}">{{ $utilization }}%</span>
                                            @endif
                                        </div>
                                        @if($supplier->credit_limit > 0)
                                            <small class="text-muted">Available: ₱{{ number_format($supplier->credit_available, 2) }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge badge-info">{{ $supplier->inventoryItems->count() }}</span>
                                        @if($supplier->inventoryItems->count() > 0)
                                            <br><small class="text-muted">Value: ₱{{ number_format($supplier->total_inventory_value, 2) }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        @if($supplier->is_active)
                                            <span class="badge badge-success">Active</span>
                                        @else
                                            <span class="badge badge-danger">Inactive</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('inventory.suppliers.show', $supplier) }}" class="btn btn-sm btn-info" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('inventory.suppliers.edit', $supplier) }}" class="btn btn-sm btn-primary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('inventory.suppliers.destroy', $supplier) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this supplier?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" title="Delete" {{ $supplier->inventoryItems->count() > 0 || $supplier->purchaseOrders->count() > 0 ? 'disabled' : '' }}>
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
                <div class="d-flex justify-content-center">
                    {{ $suppliers->withQueryString()->links() }}
                </div>
            @else
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> No suppliers found.
                    @if(request()->hasAny(['search', 'is_active', 'is_preferred', 'balance']))
                        <a href="{{ route('inventory.suppliers.index') }}" class="alert-link">Clear filters</a> to see all suppliers.
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Auto-submit form on filter change
        $('#is_active, #is_preferred, #balance').change(function() {
            $(this).closest('form').submit();
        });
    });
</script>
@endsection