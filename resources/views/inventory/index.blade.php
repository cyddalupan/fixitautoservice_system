@extends('layouts.app')

@section('title', 'Inventory Management')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0">Inventory Management</h1>
                <div>
                    <a href="{{ route('inventory.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Item
                    </a>
                    <a href="{{ route('inventory.low-stock') }}" class="btn btn-warning">
                        <i class="fas fa-exclamation-triangle"></i> Low Stock
                    </a>
                    <a href="{{ route('inventory.statistics') }}" class="btn btn-info">
                        <i class="fas fa-chart-bar"></i> Statistics
                    </a>
                    <a href="{{ route('inventory.export') }}" class="btn btn-success">
                        <i class="fas fa-file-export"></i> Export CSV
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
                                Total Items</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['total_items']) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-boxes fa-2x text-gray-300"></i>
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
                                Total Value</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">${{ number_format($stats['total_value'], 2) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
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
                                Low Stock Items</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['low_stock']) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Out of Stock</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['out_of_stock']) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-times-circle fa-2x text-gray-300"></i>
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
            <form method="GET" action="{{ route('inventory.index') }}">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="search">Search</label>
                            <input type="text" class="form-control" id="search" name="search" 
                                   value="{{ request('search') }}" placeholder="Part #, Name, OEM, UPC...">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="category_id">Category</label>
                            <select class="form-control" id="category_id" name="category_id">
                                <option value="">All Categories</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="supplier_id">Supplier</label>
                            <select class="form-control" id="supplier_id" name="supplier_id">
                                <option value="">All Suppliers</option>
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                        {{ $supplier->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="stock_status">Stock Status</label>
                            <select class="form-control" id="stock_status" name="stock_status">
                                <option value="">All Status</option>
                                <option value="in_stock" {{ request('stock_status') == 'in_stock' ? 'selected' : '' }}>In Stock</option>
                                <option value="low_stock" {{ request('stock_status') == 'low_stock' ? 'selected' : '' }}>Low Stock</option>
                                <option value="out_of_stock" {{ request('stock_status') == 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
                            </select>
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
                    <div class="col-md-1">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-filter"></i> Filter
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Inventory Table -->
    <div class="card">
        <div class="card-header">
            <h6 class="m-0 font-weight-bold text-primary">Inventory Items</h6>
        </div>
        <div class="card-body">
            @if($inventory->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th>
                                    <a href="{{ route('inventory.index', array_merge(request()->all(), ['sort' => 'part_number', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc'])) }}">
                                        Part Number
                                        @if(request('sort') == 'part_number')
                                            <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }}"></i>
                                        @else
                                            <i class="fas fa-sort"></i>
                                        @endif
                                    </a>
                                </th>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Supplier</th>
                                <th>
                                    <a href="{{ route('inventory.index', array_merge(request()->all(), ['sort' => 'quantity', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc'])) }}">
                                        Quantity
                                        @if(request('sort') == 'quantity')
                                            <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }}"></i>
                                        @else
                                            <i class="fas fa-sort"></i>
                                        @endif
                                    </a>
                                </th>
                                <th>Cost</th>
                                <th>Retail</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($inventory as $item)
                                <tr>
                                    <td>
                                        <strong>{{ $item->part_number }}</strong>
                                        @if($item->oem_number)
                                            <br><small class="text-muted">OEM: {{ $item->oem_number }}</small>
                                        @endif
                                    </td>
                                    <td>{{ $item->name }}</td>
                                    <td>
                                        @if($item->category)
                                            <span class="badge" style="background-color: {{ $item->category->color ?? '#6c757d' }}; color: white;">
                                                {{ $item->category->name }}
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($item->supplier)
                                            {{ $item->supplier->name }}
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <span class="mr-2">{{ $item->quantity }}</span>
                                            @if($item->quantity <= 0)
                                                <span class="badge badge-danger">Out of Stock</span>
                                            @elseif($item->quantity <= $item->reorder_point)
                                                <span class="badge badge-warning">Low Stock</span>
                                            @else
                                                <span class="badge badge-success">In Stock</span>
                                            @endif
                                        </div>
                                        <small class="text-muted">Min: {{ $item->minimum_stock }}, Reorder: {{ $item->reorder_point }}</small>
                                    </td>
                                    <td>${{ number_format($item->cost_price, 2) }}</td>
                                    <td>${{ number_format($item->retail_price, 2) }}</td>
                                    <td>
                                        @php
                                            $statusColors = [
                                                'in_stock' => 'success',
                                                'low_stock' => 'warning',
                                                'out_of_stock' => 'danger',
                                                'discontinued' => 'secondary'
                                            ];
                                        @endphp
                                        <span class="badge badge-{{ $statusColors[$item->status] ?? 'secondary' }}">
                                            {{ ucfirst(str_replace('_', ' ', $item->status)) }}
                                        </span>
                                        @if(!$item->is_active)
                                            <span class="badge badge-secondary">Inactive</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('inventory.show', $item) }}" class="btn btn-sm btn-info" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('inventory.edit', $item) }}" class="btn btn-sm btn-primary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('inventory.destroy', $item) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this item?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" title="Delete">
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
                    {{ $inventory->appends(request()->query())->links() }}
                </div>
            @else
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> No inventory items found.
                    @if(request()->hasAny(['search', 'category_id', 'supplier_id', 'stock_status']))
                        <a href="{{ route('inventory.index') }}" class="alert-link">Clear filters</a> to see all items.
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
        $('#category_id, #supplier_id, #stock_status, #is_active').change(function() {
            $(this).closest('form').submit();
        });
    });
</script>
@endsection