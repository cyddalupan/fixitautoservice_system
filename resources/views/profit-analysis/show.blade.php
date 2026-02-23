@extends('layouts.app')

@section('title', 'Profit Analysis Details')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-bar"></i> Profit Analysis Details
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('profit-analysis.job-profitability') }}" class="btn btn-sm btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Reports
                        </a>
                        @if(!$analysis->is_finalized)
                        <form action="{{ route('profit-analysis.finalize', $analysis) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Finalize this analysis?')">
                                <i class="fas fa-check"></i> Finalize
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <!-- Analysis Header -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="callout callout-info">
                                <div class="row">
                                    <div class="col-md-4">
                                        <h5>Analysis ID: {{ $analysis->id }}</h5>
                                        <p class="mb-1">
                                            <strong>Date:</strong> {{ $analysis->analysis_date->format('F j, Y') }}
                                        </p>
                                        <p class="mb-1">
                                            <strong>Status:</strong>
                                            @if($analysis->is_finalized)
                                            <span class="badge bg-success">Finalized</span>
                                            @else
                                            <span class="badge bg-warning">Draft</span>
                                            @endif
                                        </p>
                                    </div>
                                    <div class="col-md-4">
                                        @if($analysis->workOrder)
                                        <h5>Work Order: {{ $analysis->workOrder->work_order_number }}</h5>
                                        <p class="mb-1">
                                            <strong>Customer:</strong> 
                                            {{ $analysis->workOrder->customer->name ?? 'N/A' }}
                                        </p>
                                        <p class="mb-1">
                                            <strong>Vehicle:</strong>
                                            @if($analysis->workOrder->vehicle)
                                            {{ $analysis->workOrder->vehicle->make }} {{ $analysis->workOrder->vehicle->model }}
                                            @else
                                            N/A
                                            @endif
                                        </p>
                                        @endif
                                    </div>
                                    <div class="col-md-4">
                                        @if($analysis->invoice)
                                        <h5>Invoice: {{ $analysis->invoice->invoice_number }}</h5>
                                        <p class="mb-1">
                                            <strong>Invoice Date:</strong>
                                            {{ $analysis->invoice->invoice_date->format('F j, Y') }}
                                        </p>
                                        <p class="mb-1">
                                            <strong>Invoice Status:</strong>
                                            <span class="badge bg-{{ $analysis->invoice->status == 'paid' ? 'success' : 'warning' }}">
                                                {{ ucfirst($analysis->invoice->status) }}
                                            </span>
                                        </p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Profit Summary Cards -->
                    <div class="row">
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-info">
                                <div class="inner">
                                    <h3>${{ number_format($analysis->total_revenue, 2) }}</h3>
                                    <p>Total Revenue</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-dollar-sign"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-danger">
                                <div class="inner">
                                    <h3>${{ number_format($analysis->total_cost, 2) }}</h3>
                                    <p>Total Cost</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-money-bill-wave"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-success">
                                <div class="inner">
                                    <h3>${{ number_format($analysis->gross_profit, 2) }}</h3>
                                    <p>Gross Profit</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-chart-line"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-warning">
                                <div class="inner">
                                    <h3>${{ number_format($analysis->net_profit, 2) }}</h3>
                                    <p>Net Profit</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-chart-pie"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Detailed Breakdown -->
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Revenue Breakdown</h3>
                                </div>
                                <div class="card-body">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Category</th>
                                                <th>Amount</th>
                                                <th>Percentage</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>Labor Revenue</td>
                                                <td>${{ number_format($analysis->labor_revenue, 2) }}</td>
                                                <td>
                                                    <div class="progress progress-xs">
                                                        <div class="progress-bar bg-info" style="width: {{ $analysis->total_revenue > 0 ? ($analysis->labor_revenue / $analysis->total_revenue * 100) : 0 }}%"></div>
                                                    </div>
                                                    <small>{{ number_format($analysis->total_revenue > 0 ? ($analysis->labor_revenue / $analysis->total_revenue * 100) : 0, 1) }}%</small>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Parts Revenue</td>
                                                <td>${{ number_format($analysis->parts_revenue, 2) }}</td>
                                                <td>
                                                    <div class="progress progress-xs">
                                                        <div class="progress-bar bg-success" style="width: {{ $analysis->total_revenue > 0 ? ($analysis->parts_revenue / $analysis->total_revenue * 100) : 0 }}%"></div>
                                                    </div>
                                                    <small>{{ number_format($analysis->total_revenue > 0 ? ($analysis->parts_revenue / $analysis->total_revenue * 100) : 0, 1) }}%</small>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Other Revenue</td>
                                                <td>${{ number_format($analysis->other_revenue, 2) }}</td>
                                                <td>
                                                    <div class="progress progress-xs">
                                                        <div class="progress-bar bg-warning" style="width: {{ $analysis->total_revenue > 0 ? ($analysis->other_revenue / $analysis->total_revenue * 100) : 0 }}%"></div>
                                                    </div>
                                                    <small>{{ number_format($analysis->total_revenue > 0 ? ($analysis->other_revenue / $analysis->total_revenue * 100) : 0, 1) }}%</small>
                                                </td>
                                            </tr>
                                            <tr class="table-active">
                                                <td><strong>Total Revenue</strong></td>
                                                <td><strong>${{ number_format($analysis->total_revenue, 2) }}</strong></td>
                                                <td><strong>100%</strong></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Cost Breakdown</h3>
                                </div>
                                <div class="card-body">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Category</th>
                                                <th>Amount</th>
                                                <th>Percentage</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>Labor Cost</td>
                                                <td>${{ number_format($analysis->labor_cost, 2) }}</td>
                                                <td>
                                                    <div class="progress progress-xs">
                                                        <div class="progress-bar bg-info" style="width: {{ $analysis->total_cost > 0 ? ($analysis->labor_cost / $analysis->total_cost * 100) : 0 }}%"></div>
                                                    </div>
                                                    <small>{{ number_format($analysis->total_cost > 0 ? ($analysis->labor_cost / $analysis->total_cost * 100) : 0, 1) }}%</small>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Parts Cost</td>
                                                <td>${{ number_format($analysis->parts_cost, 2) }}</td>
                                                <td>
                                                    <div class="progress progress-xs">
                                                        <div class="progress-bar bg-success" style="width: {{ $analysis->total_cost > 0 ? ($analysis->parts_cost / $analysis->total_cost * 100) : 0 }}%"></div>
                                                    </div>
                                                    <small>{{ number_format($analysis->total_cost > 0 ? ($analysis->parts_cost / $analysis->total_cost * 100) : 0, 1) }}%</small>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Other Costs</td>
                                                <td>${{ number_format($analysis->other_costs, 2) }}</td>
                                                <td>
                                                    <div class="progress progress-xs">
                                                        <div class="progress-bar bg-warning" style="width: {{ $analysis->total_cost > 0 ? ($analysis->other_costs / $analysis->total_cost * 100) : 0 }}%"></div>
                                                    </div>
                                                    <small>{{ number_format($analysis->total_cost > 0 ? ($analysis->other_costs / $analysis->total_cost * 100) : 0, 1) }}%</small>
                                                </td>
                                            </tr>
                                            <tr class="table-active">
                                                <td><strong>Total Cost</strong></td>
                                                <td><strong>${{ number_format($analysis->total_cost, 2) }}</strong></td>
                                                <td><strong>100%</strong></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Profit Margins -->
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Profit Margins</h3>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="info-box">
                                                <span class="info-box-icon bg-info"><i class="fas fa-percentage"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Gross Profit Margin</span>
                                                    <span class="info-box-number">{{ number_format($analysis->gross_profit_margin, 1) }}%</span>
                                                    <div class="progress">
                                                        <div class="progress-bar bg-info" style="width: {{ min($analysis->gross_profit_margin, 100) }}%"></div>
                                                    </div>
                                                    <small>
                                                        Gross Profit: ${{ number_format($analysis->gross_profit, 2) }}
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="info-box">
                                                <span class="info-box-icon bg-success"><i class="fas fa-percentage"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Net Profit Margin</span>
                                                    <span class="info-box-number">{{ number_format($analysis->net_profit_margin, 1) }}%</span>
                                                    <div class="progress">
                                                        <div class="progress-bar bg-success" style="width: {{ min($analysis->net_profit_margin, 100) }}%"></div>
                                                    </div>
                                                    <small>
                                                        Net Profit: ${{ number_format($analysis->net_profit, 2) }}
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="info-box">
                                                <span class="info-box-icon bg-warning"><i class="fas fa-balance-scale"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Profitability Ratio</span>
                                                    <span class="info-box-number">{{ number_format($analysis->profitability_ratio, 2) }}</span>
                                                    <div class="progress">
                                                        <div class="progress-bar bg-warning" style="width: {{ min($analysis->profitability_ratio * 10, 100) }}%"></div>
                                                    </div>
                                                    <small>
                                                        Revenue to Profit Ratio
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Additional Details -->
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Additional Details</h3>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h5>Service Details</h5>
                                            <table class="table table-sm">
                                                <tr>
                                                    <th width="40%">Service Type:</th>
                                                    <td>{{ $analysis->workOrder->service_type ?? 'N/A' }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Technician:</th>
                                                    <td>
                                                        @if($analysis->workOrder && $analysis->workOrder->technician)
                                                        {{ $analysis->workOrder->technician->name }}
                                                        @else
                                                        N/A
                                                        @endif
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th>Labor Hours:</th>
                                                    <td>{{ number_format($analysis->labor_hours, 1) }} hours</td>
                                                </tr>
                                                <tr>
                                                    <th>Effective Labor Rate:</th>
                                                    <td>${{ number_format($analysis->labor_hours > 0 ? ($analysis->labor_revenue / $analysis->labor_hours) : 0, 2) }}/hour</td>
                                                </tr>
                                            </table>
                                        </div>
                                        <div class="col-md-6">
                                            <h5>Analysis Metadata</h5>
                                            <table class="table table-sm">
                                                <tr>
                                                    <th width="40%">Created At:</th>
                                                    <td>{{ $analysis->created_at->format('Y-m-d H:i:s') }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Updated At:</th>
                                                    <td>{{ $analysis->updated_at->format('Y-m-d H:i:s') }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Finalized At:</th>
                                                    <td>
                                                        @if($analysis->finalized_at)
                                                        {{ $analysis->finalized_at->format('Y-m-d H:i:s') }}
                                                        @else
                                                        Not finalized
                                                        @endif
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th>Analysis Notes:</th>
                                                    <td>{{ $analysis->analysis_notes ?? 'No notes' }}</td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Actions</h3>
                                </div>
                                <div class="card-body">
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('profit-analysis.job-profitability') }}" class="btn btn-secondary">
                                            <i class="fas fa-arrow-left"></i> Back to Reports
                                        </a>
                                        @if($analysis->workOrder)
                                        <a href="{{ route('work-orders.show', $analysis->workOrder) }}" class="btn btn-info">
                                            <i class="fas fa-clipboard-list"></i> View Work Order
                                        </a>
                                        @endif
                                        @if($analysis->invoice)
                                        <a href="{{ route('invoices.show', $analysis->invoice) }}" class="btn btn-info">
                                            <i class="fas fa-file-invoice-dollar"></i> View Invoice
                                        </a>
                                        @endif
                                        @if(!$analysis->is_finalized)
                                        <form action="{{ route('profit-analysis.finalize', $analysis) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-success">
                                                <i class="fas fa-check"></i> Finalize Analysis
                                            </button>
                                        </form>
                                        @endif
                                        <form action="{{ route('profit-analysis.destroy', $analysis) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this analysis? This action cannot be undone.')">
                                                <i class="fas fa-trash"></i> Delete Analysis
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="row">
                        <div class="col-md-6">
                            <p class="text-muted">
                                <i class="fas fa-info-circle"></i> Analysis ID: {{ $analysis->id }}
                            </p>
                        </div>
                        <div class="col-md-6 text-right">
                            <small class="text-muted">
                                Last updated: {{ $analysis->updated_at->format('Y-m-d H:i:s') }}
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .progress-xs {
        height: 10px;
    }
    .callout {
        border-left: 5px solid #3498db;
        background-color: #f8f9fa;
        padding: 15px;
        border-radius: 3px;
    }
</style>
@endpush
@endsection