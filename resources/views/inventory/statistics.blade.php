@extends('layouts.app')

@section('title', 'Inventory Statistics')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-chart-bar text-primary"></i> Inventory Statistics
        </h1>
        <div class="btn-group">
            <a href="{{ route('inventory.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Inventory
            </a>
            <button onclick="window.location.reload()" class="btn btn-primary">
                <i class="fas fa-sync-alt"></i> Refresh
            </button>
        </div>
    </div>

    <!-- Overall Statistics Cards -->
    <div class="row">
        <!-- Total Items Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Items
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($stats['total_items']) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-boxes fa-2x text-gray-500"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Value Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Total Inventory Value
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                ₱{{ number_format($stats['total_value'], 2) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-money-bill-wave fa-2x text-gray-500"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Active Items Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Active Items
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($stats['active_items']) }}
                            </div>
                            <div class="mt-2 mb-0 text-gray-600 text-xs">
                                <span>{{ number_format($stats['active_items'] / max($stats['total_items'], 1) * 100, 1) }}% of total</span>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-500"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Low Stock Items Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Low Stock Items
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($stats['low_stock_count']) }}
                            </div>
                            <div class="mt-2 mb-0 text-gray-600 text-xs">
                                <span>{{ number_format($stats['low_stock_count'] / max($stats['total_items'], 1) * 100, 1) }}% of total</span>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-500"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Financial Metrics -->
    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-line"></i> Financial Metrics
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 text-center">
                            <div class="text-gray-600">Total Sales Value</div>
                            <div class="h4 text-success">₱{{ number_format($stats['total_sales_value'], 2) }}</div>
                        </div>
                        <div class="col-md-3 text-center">
                            <div class="text-gray-600">Total Cost Value</div>
                            <div class="h4 text-danger">₱{{ number_format($stats['total_cost_value'], 2) }}</div>
                        </div>
                        <div class="col-md-3 text-center">
                            <div class="text-gray-600">Total Profit</div>
                            <div class="h4 {{ $stats['total_profit'] >= 0 ? 'text-success' : 'text-danger' }}">
                                ₱{{ number_format($stats['total_profit'], 2) }}
                            </div>
                        </div>
                        <div class="col-md-3 text-center">
                            <div class="text-gray-600">Avg Profit Margin</div>
                            <div class="h4 {{ $stats['average_profit_margin'] >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ number_format($stats['average_profit_margin'], 2) }}%
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Category Statistics -->
    <div class="row">
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-tags"></i> Category Statistics
                    </h6>
                </div>
                <div class="card-body">
                    @if($categoryStats->isNotEmpty())
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Category</th>
                                        <th>Item Count</th>
                                        <th>Total Value</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($categoryStats as $category)
                                    <tr>
                                        <td>{{ $category->name }}</td>
                                        <td class="text-center">{{ number_format($category->item_count) }}</td>
                                        <td class="text-right">₱{{ number_format($category->total_value, 2) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center text-gray-600 py-4">
                            <i class="fas fa-info-circle fa-2x mb-3"></i>
                            <p>No category data available</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Supplier Statistics -->
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-truck"></i> Supplier Statistics
                    </h6>
                </div>
                <div class="card-body">
                    @if($supplierStats->isNotEmpty())
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Supplier</th>
                                        <th>Item Count</th>
                                        <th>Total Value</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($supplierStats as $supplier)
                                    <tr>
                                        <td>{{ $supplier->company_name }}</td>
                                        <td class="text-center">{{ number_format($supplier->item_count) }}</td>
                                        <td class="text-right">₱{{ number_format($supplier->total_value, 2) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center text-gray-600 py-4">
                            <i class="fas fa-info-circle fa-2x mb-3"></i>
                            <p>No supplier data available</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Top Selling Items -->
    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-fire"></i> Top Selling Items
                    </h6>
                </div>
                <div class="card-body">
                    @if($topSelling->isNotEmpty())
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>Item Name</th>
                                        <th>Part #</th>
                                        <th>Category</th>
                                        <th>Total Sold</th>
                                        <th>Revenue</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($topSelling as $item)
                                    <tr>
                                        <td>{{ $item->item_name }}</td>
                                        <td>{{ $item->part_number }}</td>
                                        <td>{{ $item->category->name ?? 'N/A' }}</td>
                                        <td class="text-center">{{ number_format($item->total_sold) }}</td>
                                        <td class="text-right">₱{{ number_format($item->total_sales, 2) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center text-gray-600 py-4">
                            <i class="fas fa-info-circle fa-2x mb-3"></i>
                            <p>No sales data available</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Items Needing Reorder -->
    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-warning">
                        <i class="fas fa-shopping-cart"></i> Items Needing Reorder
                    </h6>
                    <a href="{{ route('inventory.low-stock') }}" class="btn btn-sm btn-warning">
                        <i class="fas fa-list"></i> View All Low Stock
                    </a>
                </div>
                <div class="card-body">
                    @if($needReorder->isNotEmpty())
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>Item Name</th>
                                        <th>Part #</th>
                                        <th>Current Qty</th>
                                        <th>Reorder Point</th>
                                        <th>Deficit</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($needReorder as $item)
                                    <tr>
                                        <td>{{ $item->item_name }}</td>
                                        <td>{{ $item->part_number }}</td>
                                        <td class="text-center">{{ number_format($item->quantity) }}</td>
                                        <td class="text-center">{{ number_format($item->reorder_point) }}</td>
                                        <td class="text-center">
                                            @php
                                                $deficit = max(0, $item->reorder_point - $item->quantity);
                                            @endphp
                                            <span class="badge badge-danger">{{ number_format($deficit) }}</span>
                                        </td>
                                        <td class="text-center">
                                            @if($item->quantity == 0)
                                                <span class="badge badge-danger">Out of Stock</span>
                                            @elseif($item->quantity <= $item->reorder_point)
                                                <span class="badge badge-warning">Low Stock</span>
                                            @else
                                                <span class="badge badge-success">Adequate</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center text-success py-4">
                            <i class="fas fa-check-circle fa-2x mb-3"></i>
                            <p>All inventory items are adequately stocked!</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Inventory Summary -->
    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-clipboard-check"></i> Inventory Summary
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="card border-left-success shadow h-100 py-2 mb-4">
                                <div class="card-body">
                                    <div class="text-center">
                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                            Inventory Health
                                        </div>
                                        @php
                                            $healthScore = 100;
                                            if ($stats['total_items'] > 0) {
                                                $lowStockPercent = ($stats['low_stock_count'] / $stats['total_items']) * 100;
                                                $outOfStockPercent = ($stats['out_of_stock_count'] / $stats['total_items']) * 100;
                                                $healthScore = 100 - ($lowStockPercent * 0.5) - ($outOfStockPercent * 1);
                                                $healthScore = max(0, min(100, $healthScore));
                                            }
                                        @endphp
                                        <div class="h2 mb-0 font-weight-bold {{ $healthScore >= 80 ? 'text-success' : ($healthScore >= 60 ? 'text-warning' : 'text-danger') }}">
                                            {{ number_format($healthScore, 1) }}%
                                        </div>
                                        <div class="mt-2">
                                            @if($healthScore >= 80)
                                                <span class="badge badge-success">Excellent</span>
                                            @elseif($healthScore >= 60)
                                                <span class="badge badge-warning">Good</span>
                                            @else
                                                <span class="badge badge-danger">Needs Attention</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="alert alert-info">
                                <h6 class="alert-heading"><i class="fas fa-lightbulb"></i> Inventory Insights</h6>
                                <ul class="mb-0">
                                    <li>Total inventory value: <strong>₱{{ number_format($stats['total_value'], 2) }}</strong></li>
                                    <li>{{ $stats['low_stock_count'] }} items need reordering</li>
                                    <li>{{ $stats['out_of_stock_count'] }} items are out of stock</li>
                                    <li>Average profit margin: <strong>{{ number_format($stats['average_profit_margin'], 2) }}%</strong></li>
                                    @if($stats['total_profit'] > 0)
                                        <li>Total profit from inventory: <strong class="text-success">₱{{ number_format($stats['total_profit'], 2) }}</strong></li>
                                    @endif
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Auto-refresh statistics every 5 minutes
        setInterval(function() {
            window.location.reload();
        }, 300000); // 5 minutes

        // Initialize DataTables if needed
        if ($.fn.DataTable) {
            $('table').DataTable({
                pageLength: 10,
                responsive: true
            });
        }
    });
</script>
@endsection