@extends('layouts.app')

@section('title', 'Inventory Item: ' . $inventory->name)

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0">
                    <i class="fas fa-box text-primary"></i> {{ $inventory->name }}
                    <small class="text-muted">{{ $inventory->part_number }}</small>
                </h1>
                <div>
                    <a href="{{ route('inventory.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Inventory
                    </a>
                    <a href="{{ route('inventory.edit', $inventory) }}" class="btn btn-warning">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                </div>
            </div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('inventory.index') }}">Inventory</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $inventory->name }}</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Basic Information -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Item Details</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="40%">Part Number:</th>
                                    <td><strong>{{ $inventory->part_number }}</strong></td>
                                </tr>
                                <tr>
                                    <th>Name:</th>
                                    <td>{{ $inventory->name }}</td>
                                </tr>
                                <tr>
                                    <th>Description:</th>
                                    <td>{{ $inventory->description ?? 'No description' }}</td>
                                </tr>
                                <tr>
                                    <th>Manufacturer:</th>
                                    <td>{{ $inventory->manufacturer ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Category:</th>
                                    <td>
                                        @if($inventory->category)
                                            <span class="badge bg-info">{{ $inventory->category->name }}</span>
                                        @else
                                            <span class="text-muted">Uncategorized</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Supplier:</th>
                                    <td>
                                        @if($inventory->supplier)
                                            {{ $inventory->supplier->name }}
                                        @else
                                            <span class="text-muted">No supplier</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="40%">Location:</th>
                                    <td>{{ $inventory->location ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Bin:</th>
                                    <td>{{ $inventory->bin ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Quantity:</th>
                                    <td>
                                        <span class="badge {{ $inventory->quantity <= $inventory->reorder_point ? 'bg-danger' : ($inventory->quantity <= $inventory->minimum_stock ? 'bg-warning' : 'bg-success') }}">
                                            {{ number_format($inventory->quantity) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Minimum Stock:</th>
                                    <td>{{ number_format($inventory->minimum_stock) }}</td>
                                </tr>
                                <tr>
                                    <th>Reorder Point:</th>
                                    <td>{{ number_format($inventory->reorder_point) }}</td>
                                </tr>
                                <tr>
                                    <th>Status:</th>
                                    <td>
                                        @php
                                            $statusColors = [
                                                'in_stock' => 'success',
                                                'low_stock' => 'warning',
                                                'out_of_stock' => 'danger',
                                                'discontinued' => 'secondary',
                                                'on_order' => 'info'
                                            ];
                                            $color = $statusColors[$inventory->status] ?? 'secondary';
                                        @endphp
                                        <span class="badge bg-{{ $color }}">{{ ucfirst(str_replace('_', ' ', $inventory->status)) }}</span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pricing Information -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Pricing Information</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="50%">Cost Price:</th>
                                    <td class="text-end">₱{{ number_format($inventory->cost_price, 2) }}</td>
                                </tr>
                                <tr>
                                    <th>Retail Price:</th>
                                    <td class="text-end"><strong>₱{{ number_format($inventory->retail_price, 2) }}</strong></td>
                                </tr>
                                <tr>
                                    <th>Wholesale Price:</th>
                                    <td class="text-end">₱{{ number_format($inventory->wholesale_price ?? 0, 2) }}</td>
                                </tr>
                                <tr>
                                    <th>Core Price:</th>
                                    <td class="text-end">₱{{ number_format($inventory->core_price ?? 0, 2) }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="50%">Markup:</th>
                                    <td class="text-end">
                                        @php
                                            $markup = $inventory->cost_price > 0 ? (($inventory->retail_price - $inventory->cost_price) / $inventory->cost_price * 100) : 0;
                                        @endphp
                                        <span class="{{ $markup >= 50 ? 'text-success' : 'text-warning' }}">
                                            {{ number_format($markup, 1) }}%
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Tax Rate:</th>
                                    <td class="text-end">{{ number_format($inventory->tax_rate, 2) }}%</td>
                                </tr>
                                <tr>
                                    <th>Taxable:</th>
                                    <td class="text-end">
                                        @if($inventory->is_taxable)
                                            <span class="badge bg-success">Yes</span>
                                        @else
                                            <span class="badge bg-secondary">No</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Total Value:</th>
                                    <td class="text-end"><strong>₱{{ number_format($inventory->quantity * $inventory->cost_price, 2) }}</strong></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Sidebar -->
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Statistics</h6>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th>Total Sales:</th>
                            <td class="text-end">₱{{ number_format($stats['total_sales'] ?? 0, 2) }}</td>
                        </tr>
                        <tr>
                            <th>Total Cost:</th>
                            <td class="text-end">₱{{ number_format($stats['total_cost'] ?? 0, 2) }}</td>
                        </tr>
                        <tr>
                            <th>Profit:</th>
                            <td class="text-end">
                                <span class="{{ ($stats['profit'] ?? 0) >= 0 ? 'text-success' : 'text-danger' }}">
                                    ₱{{ number_format($stats['profit'] ?? 0, 2) }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Profit Margin:</th>
                            <td class="text-end">
                                <span class="{{ ($stats['profit_margin'] ?? 0) >= 20 ? 'text-success' : 'text-warning' }}">
                                    {{ number_format($stats['profit_margin'] ?? 0, 1) }}%
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Turnover Rate:</th>
                            <td class="text-end">{{ number_format($stats['turnover_rate'] ?? 0) }}x</td>
                        </tr>
                        <tr>
                            <th>Days of Supply:</th>
                            <td class="text-end">{{ number_format($stats['days_of_supply'] ?? 0) }} days</td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Transaction History -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Transactions</h6>
                </div>
                <div class="card-body">
                    @if($transactions && $transactions->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Type</th>
                                        <th>Qty</th>
                                        <th>Reference</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($transactions as $transaction)
                                        <tr>
                                            <td>{{ $transaction['date']->format('M d') }}</td>
                                            <td>
                                                <span class="badge {{ $transaction['type'] == 'purchase' ? 'bg-info' : 'bg-success' }}">
                                                    {{ ucfirst($transaction['type']) }}
                                                </span>
                                            </td>
                                            <td>{{ number_format($transaction['quantity']) }}</td>
                                            <td>
                                                <small>{{ $transaction['reference'] ?? 'N/A' }}</small>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted text-center mb-0">No transactions found</p>
                    @endif
                </div>
            </div>

            <!-- Dates -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Dates</h6>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th>Created:</th>
                            <td class="text-end">{{ $inventory->created_at->format('M d, Y') }}</td>
                        </tr>
                        <tr>
                            <th>Updated:</th>
                            <td class="text-end">{{ $inventory->updated_at->format('M d, Y') }}</td>
                        </tr>
                        <tr>
                            <th>Last Purchased:</th>
                            <td class="text-end">{{ $inventory->last_purchased ? $inventory->last_purchased->format('M d, Y') : 'Never' }}</td>
                        </tr>
                        <tr>
                            <th>Last Sold:</th>
                            <td class="text-end">{{ $inventory->last_sold ? $inventory->last_sold->format('M d, Y') : 'Never' }}</td>
                        </tr>
                        <tr>
                            <th>Total Sold:</th>
                            <td class="text-end">{{ number_format($inventory->total_sold) }}</td>
                        </tr>
                    </table>
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
                <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete <strong>{{ $inventory->name }}</strong>?</p>
                <p class="text-danger"><strong>Warning:</strong> This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="{{ route('inventory.destroy', $inventory) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection