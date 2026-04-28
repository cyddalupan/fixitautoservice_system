@extends('layouts.app')

@section('title', 'Inventory Management')

@push('styles')
<style>
.fixit-inv-dash{ background:#f4f6fa; min-height:100vh; padding-top:0.5rem; padding-bottom:2rem; }
.fixit-inv-dash .page-title{ color:#1a2332; font-size:1.15rem; }
.fixit-inv-dash .inv-stat-card{ border-radius:10px; border:0; box-shadow:0 1px 2px rgba(0,0,0,.04); transition:box-shadow .2s,transform .15s; overflow:hidden; }
.fixit-inv-dash .inv-stat-card:hover{ box-shadow:0 3px 8px rgba(0,0,0,.06); transform:translateY(-1px); }
.fixit-inv-dash .inv-stat-icon{ width:32px;height:32px;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0; }
.fixit-inv-dash .inv-stat-label{ font-size:.68rem;color:#6c7a8d;text-transform:uppercase;letter-spacing:.4px;font-weight:600; }
.fixit-inv-dash .inv-stat-value{ font-size:1.2rem;font-weight:700;color:#1a2332;line-height:1.1; }
.fixit-inv-dash .inv-filter-card{ border-radius:10px; border:0; box-shadow:0 1px 2px rgba(0,0,0,.04); }
.fixit-inv-dash .inv-filter-header{ background:transparent; border-bottom:1px solid #f0f2f5; padding:.65rem 1rem; font-size:.82rem; font-weight:600; color:#1a2332; }
.fixit-inv-dash .inv-table-card{ border-radius:10px; border:0; box-shadow:0 1px 2px rgba(0,0,0,.04); }
.fixit-inv-dash .inv-table-header{ background:transparent; border-bottom:1px solid #f0f2f5; padding:.65rem 1rem; font-size:.82rem; font-weight:600; color:#1a2332; }
.fixit-inv-dash .border-left-primary{ border-left:4px solid #4361ee; }
.fixit-inv-dash .border-left-success{ border-left:4px solid #2ec4b6; }
.fixit-inv-dash .border-left-warning{ border-left:4px solid #f7a429; }
.fixit-inv-dash .border-left-danger{ border-left:4px solid #e63946; }
.fixit-inv-dash .btn-sm-inv{ font-size:.78rem;padding:.35rem .65rem;border-radius:7px; }
@media(max-width:767px){
    .fixit-inv-dash .inv-actions{ display:flex; flex-wrap:wrap; gap:4px; }
}
</style>
@endpush

@section('content')
<div class="container-fluid px-3 px-md-4 fixit-inv-dash">
    <!-- ── PAGE HEADER ── -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0 fw-bold page-title">
                <i class="fas fa-box me-2 accent-icon"></i>Inventory Management
            </h4>
            <p class="mb-0 text-muted small"><span class="status-dot"></span> Parts, supplies, and stock tracking</p>
        </div>
        <div class="mt-2 mt-md-0 d-flex gap-2 inv-actions">
            <a href="{{ route('inventory.create') }}" class="btn btn-sm btn-primary btn-sm-inv">
                <i class="fas fa-plus"></i> Add Item
            </a>
            <a href="{{ route('inventory.low-stock') }}" class="btn btn-sm btn-warning btn-sm-inv">
                <i class="fas fa-exclamation-triangle"></i> Low Stock
            </a>
            <a href="{{ route('inventory.statistics') }}" class="btn btn-sm btn-info btn-sm-inv">
                <i class="fas fa-chart-bar"></i> Stats
            </a>
            <a href="{{ route('inventory.export') }}" class="btn btn-sm btn-success btn-sm-inv">
                <i class="fas fa-file-export"></i> Export
            </a>
        </div>
    </div>

    <!-- ══ COMPACT STAT CARDS ══ -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card inv-stat-card border-left-primary h-100">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start mb-1">
                        <span class="inv-stat-label">Total Items</span>
                        <div class="inv-stat-icon" style="background:rgba(67,97,238,0.1);">
                            <i class="fas fa-boxes" style="color:#4361ee;font-size:.8rem;"></i>
                        </div>
                    </div>
                    <div class="inv-stat-value">{{ number_format($stats['total_items']) }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card inv-stat-card border-left-success h-100">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start mb-1">
                        <span class="inv-stat-label">Total Value</span>
                        <div class="inv-stat-icon" style="background:rgba(46,196,182,0.1);">
                            <i class="fas fa-dollar-sign" style="color:#2ec4b6;font-size:.8rem;"></i>
                        </div>
                    </div>
                    <div class="inv-stat-value">₱{{ number_format($stats['total_value'], 0) }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card inv-stat-card border-left-warning h-100">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start mb-1">
                        <span class="inv-stat-label">Low Stock</span>
                        <div class="inv-stat-icon" style="background:rgba(247,164,41,0.1);">
                            <i class="fas fa-exclamation-triangle" style="color:#f7a429;font-size:.8rem;"></i>
                        </div>
                    </div>
                    <div class="inv-stat-value">{{ number_format($stats['low_stock']) }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card inv-stat-card border-left-danger h-100">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start mb-1">
                        <span class="inv-stat-label">Out of Stock</span>
                        <div class="inv-stat-icon" style="background:rgba(230,57,70,0.1);">
                            <i class="fas fa-times-circle" style="color:#e63946;font-size:.8rem;"></i>
                        </div>
                    </div>
                    <div class="inv-stat-value">{{ number_format($stats['out_of_stock']) }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card inv-filter-card mb-4">
        <div class="inv-filter-header">
            <i class="fas fa-filter me-1"></i> Filters
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
                            <label for="category_id">Category *</label>
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
                            <label for="supplier_id">Supplier *</label>
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
            
            <!-- Quick Filter Links -->
            <div class="row mt-3">
                <div class="col-12">
                    <div class="d-flex flex-wrap gap-2">
                        <span class="font-weight-bold mr-2">Quick Filters:</span>
                        
                        <!-- View All Categories -->
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" 
                                    id="categoryQuickFilter" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-tags"></i> View All Category
                            </button>
                            <div class="dropdown-menu" aria-labelledby="categoryQuickFilter">
                                <a class="dropdown-item" href="{{ route('inventory.index') }}">All Categories</a>
                                <div class="dropdown-divider"></div>
                                @foreach($categories as $category)
                                    <a class="dropdown-item" href="{{ route('inventory.index', ['category_id' => $category->id]) }}">
                                        <span class="badge mr-2" style="background-color: {{ $category->color ?? '#6c757d' }}; color: white;">&nbsp;&nbsp;</span>
                                        {{ $category->name }}
                                    </a>
                                @endforeach
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="{{ route('inventory.categories.index') }}">
                                    <i class="fas fa-cog mr-2"></i> Manage Categories
                                </a>
                            </div>
                        </div>
                        
                        <!-- View All Suppliers -->
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" 
                                    id="supplierQuickFilter" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-truck"></i> View All Supplier
                            </button>
                            <div class="dropdown-menu" aria-labelledby="supplierQuickFilter">
                                <a class="dropdown-item" href="{{ route('inventory.index') }}">All Suppliers</a>
                                <div class="dropdown-divider"></div>
                                @foreach($suppliers as $supplier)
                                    <a class="dropdown-item" href="{{ route('inventory.index', ['supplier_id' => $supplier->id]) }}">
                                        <i class="fas fa-building mr-2"></i>
                                        {{ $supplier->name }}
                                    </a>
                                @endforeach
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="{{ route('inventory.suppliers.index') }}">
                                    <i class="fas fa-cog mr-2"></i> Manage Suppliers
                                </a>
                            </div>
                        </div>
                        
                        <!-- Direct Links to Management Pages -->
                        <a href="{{ route('inventory.categories.index') }}" class="btn btn-sm btn-info">
                            <i class="fas fa-tags"></i> Categories Page
                        </a>
                        <a href="{{ route('inventory.suppliers.index') }}" class="btn btn-sm btn-info">
                            <i class="fas fa-truck"></i> Suppliers Page
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Inventory Table -->
    <div class="card inv-table-card">
        <div class="inv-table-header">
            <i class="fas fa-list me-1"></i> Inventory Items
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
                                <th>
                                    <a href="{{ route('inventory.index', array_merge(request()->all(), ['sort' => 'category_id', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc'])) }}">
                                        Category *
                                        @if(request('sort') == 'category_id')
                                            <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }}"></i>
                                        @else
                                            <i class="fas fa-sort"></i>
                                        @endif
                                    </a>
                                </th>
                                <th>
                                    <a href="{{ route('inventory.index', array_merge(request()->all(), ['sort' => 'supplier_id', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc'])) }}">
                                        Supplier *
                                        @if(request('sort') == 'supplier_id')
                                            <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }}"></i>
                                        @else
                                            <i class="fas fa-sort"></i>
                                        @endif
                                    </a>
                                </th>
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
                                            <br><small class="text-gray-600">OEM: {{ $item->oem_number }}</small>
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
                                        <small class="text-gray-600">Min: {{ $item->minimum_stock }}, Reorder: {{ $item->reorder_point }}</small>
                                    </td>
                                    <td>₱{{ number_format($item->cost_price, 2) }}</td>
                                    <td>₱{{ number_format($item->retail_price, 2) }}</td>
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