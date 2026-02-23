@extends('layouts.app')

@section('title', 'Profit Analysis Dashboard')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-line"></i> Profit Analysis Dashboard
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('profit-analysis.job-profitability') }}" class="btn btn-sm btn-primary">
                            <i class="fas fa-file-alt"></i> Job Profitability Reports
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Summary Cards -->
                    <div class="row">
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-info">
                                <div class="inner">
                                    <h3>${{ number_format($todayTotals['total_revenue'], 2) }}</h3>
                                    <p>Today's Revenue</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-dollar-sign"></i>
                                </div>
                                <a href="{{ route('profit-analysis.job-profitability', ['start_date' => now()->format('Y-m-d'), 'end_date' => now()->format('Y-m-d')]) }}" class="small-box-footer">
                                    View Details <i class="fas fa-arrow-circle-right"></i>
                                </a>
                            </div>
                        </div>
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-success">
                                <div class="inner">
                                    <h3>${{ number_format($todayTotals['net_profit'], 2) }}</h3>
                                    <p>Today's Net Profit</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-chart-pie"></i>
                                </div>
                                <a href="{{ route('profit-analysis.job-profitability', ['start_date' => now()->format('Y-m-d'), 'end_date' => now()->format('Y-m-d')]) }}" class="small-box-footer">
                                    View Details <i class="fas fa-arrow-circle-right"></i>
                                </a>
                            </div>
                        </div>
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-warning">
                                <div class="inner">
                                    <h3>${{ number_format($monthTotals['total_revenue'], 2) }}</h3>
                                    <p>Monthly Revenue</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-calendar-alt"></i>
                                </div>
                                <a href="{{ route('profit-analysis.job-profitability', ['start_date' => now()->startOfMonth()->format('Y-m-d'), 'end_date' => now()->format('Y-m-d')]) }}" class="small-box-footer">
                                    View Details <i class="fas fa-arrow-circle-right"></i>
                                </a>
                            </div>
                        </div>
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-danger">
                                <div class="inner">
                                    <h3>${{ number_format($monthTotals['net_profit'], 2) }}</h3>
                                    <p>Monthly Net Profit</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-money-bill-wave"></i>
                                </div>
                                <a href="{{ route('profit-analysis.job-profitability', ['start_date' => now()->startOfMonth()->format('Y-m-d'), 'end_date' => now()->format('Y-m-d')]) }}" class="small-box-footer">
                                    View Details <i class="fas fa-arrow-circle-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Profit Breakdown -->
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Today's Profit Breakdown</h3>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p class="text-muted">Labor Profit</p>
                                            <h3 class="text-info">${{ number_format($todayTotals['labor_profit'], 2) }}</h3>
                                            <div class="progress progress-sm">
                                                <div class="progress-bar bg-info" style="width: {{ $todayTotals['total_revenue'] > 0 ? ($todayTotals['labor_revenue'] / $todayTotals['total_revenue'] * 100) : 0 }}%"></div>
                                            </div>
                                            <small class="text-muted">
                                                Revenue: ${{ number_format($todayTotals['labor_revenue'], 2) }} | 
                                                Cost: ${{ number_format($todayTotals['labor_cost'], 2) }}
                                            </small>
                                        </div>
                                        <div class="col-md-6">
                                            <p class="text-muted">Parts Profit</p>
                                            <h3 class="text-success">${{ number_format($todayTotals['parts_profit'], 2) }}</h3>
                                            <div class="progress progress-sm">
                                                <div class="progress-bar bg-success" style="width: {{ $todayTotals['total_revenue'] > 0 ? ($todayTotals['parts_revenue'] / $todayTotals['total_revenue'] * 100) : 0 }}%"></div>
                                            </div>
                                            <small class="text-muted">
                                                Revenue: ${{ number_format($todayTotals['parts_revenue'], 2) }} | 
                                                Cost: ${{ number_format($todayTotals['parts_cost'], 2) }}
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Monthly Profit Breakdown</h3>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p class="text-muted">Labor Profit</p>
                                            <h3 class="text-info">${{ number_format($monthTotals['labor_profit'], 2) }}</h3>
                                            <div class="progress progress-sm">
                                                <div class="progress-bar bg-info" style="width: {{ $monthTotals['total_revenue'] > 0 ? ($monthTotals['labor_revenue'] / $monthTotals['total_revenue'] * 100) : 0 }}%"></div>
                                            </div>
                                            <small class="text-muted">
                                                Revenue: ${{ number_format($monthTotals['labor_revenue'], 2) }} | 
                                                Cost: ${{ number_format($monthTotals['labor_cost'], 2) }}
                                            </small>
                                        </div>
                                        <div class="col-md-6">
                                            <p class="text-muted">Parts Profit</p>
                                            <h3 class="text-success">${{ number_format($monthTotals['parts_profit'], 2) }}</h3>
                                            <div class="progress progress-sm">
                                                <div class="progress-bar bg-success" style="width: {{ $monthTotals['total_revenue'] > 0 ? ($monthTotals['parts_revenue'] / $monthTotals['total_revenue'] * 100) : 0 }}%"></div>
                                            </div>
                                            <small class="text-muted">
                                                Revenue: ${{ number_format($monthTotals['parts_revenue'], 2) }} | 
                                                Cost: ${{ number_format($monthTotals['parts_cost'], 2) }}
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Profit Analyses -->
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Recent Profit Analyses</h3>
                                    <div class="card-tools">
                                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                            <i class="fas fa-minus"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body table-responsive p-0">
                                    <table class="table table-hover text-nowrap">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Work Order</th>
                                                <th>Invoice</th>
                                                <th>Total Revenue</th>
                                                <th>Total Cost</th>
                                                <th>Gross Profit</th>
                                                <th>Net Profit</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($recentAnalyses as $analysis)
                                            <tr>
                                                <td>{{ $analysis->analysis_date->format('Y-m-d') }}</td>
                                                <td>
                                                    @if($analysis->workOrder)
                                                    <a href="{{ route('work-orders.show', $analysis->workOrder) }}">
                                                        {{ $analysis->workOrder->work_order_number }}
                                                    </a>
                                                    @else
                                                    <span class="text-muted">N/A</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($analysis->invoice)
                                                    <a href="{{ route('invoices.show', $analysis->invoice) }}">
                                                        {{ $analysis->invoice->invoice_number }}
                                                    </a>
                                                    @else
                                                    <span class="text-muted">N/A</span>
                                                    @endif
                                                </td>
                                                <td>${{ number_format($analysis->total_revenue, 2) }}</td>
                                                <td>${{ number_format($analysis->total_cost, 2) }}</td>
                                                <td>
                                                    <span class="badge bg-{{ $analysis->gross_profit >= 0 ? 'success' : 'danger' }}">
                                                        ${{ number_format($analysis->gross_profit, 2) }}
                                                    </span>
                                                    <br>
                                                    <small>{{ number_format($analysis->gross_profit_margin, 1) }}%</small>
                                                </td>
                                                <td>
                                                    <span class="badge bg-{{ $analysis->net_profit >= 0 ? 'success' : 'danger' }}">
                                                        ${{ number_format($analysis->net_profit, 2) }}
                                                    </span>
                                                    <br>
                                                    <small>{{ number_format($analysis->net_profit_margin, 1) }}%</small>
                                                </td>
                                                <td>
                                                    @if($analysis->is_finalized)
                                                    <span class="badge bg-success">Finalized</span>
                                                    @else
                                                    <span class="badge bg-warning">Draft</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <a href="{{ route('profit-analysis.show', $analysis) }}" class="btn btn-sm btn-info">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    @if(!$analysis->is_finalized)
                                                    <form action="{{ route('profit-analysis.finalize', $analysis) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Finalize this analysis?')">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                    </form>
                                                    @endif
                                                    <form action="{{ route('profit-analysis.destroy', $analysis) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this analysis?')">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="9" class="text-center">No profit analyses found.</td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Stats -->
                    <div class="row mt-4">
                        <div class="col-md-4">
                            <div class="info-box">
                                <span class="info-box-icon bg-info"><i class="fas fa-clipboard-list"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Analyses</span>
                                    <span class="info-box-number">{{ $todayTotals['count'] + $monthTotals['count'] }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-box">
                                <span class="info-box-icon bg-success"><i class="fas fa-percentage"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Avg. Gross Margin</span>
                                    <span class="info-box-number">
                                        @php
                                            $totalRevenue = $monthTotals['total_revenue'];
                                            $totalCost = $monthTotals['total_cost'];
                                            $avgMargin = $totalRevenue > 0 ? (($totalRevenue - $totalCost) / $totalRevenue * 100) : 0;
                                        @endphp
                                        {{ number_format($avgMargin, 1) }}%
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-box">
                                <span class="info-box-icon bg-warning"><i class="fas fa-chart-bar"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Avg. Net Margin</span>
                                    <span class="info-box-number">
                                        @php
                                            $totalNetProfit = $monthTotals['net_profit'];
                                            $avgNetMargin = $totalRevenue > 0 ? ($totalNetProfit / $totalRevenue * 100) : 0;
                                        @endphp
                                        {{ number_format($avgNetMargin, 1) }}%
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="row">
                        <div class="col-md-6">
                            <p class="text-muted">
                                <i class="fas fa-info-circle"></i> Data includes finalized analyses only
                            </p>
                        </div>
                        <div class="col-md-6 text-right">
                            <a href="{{ route('profit-analysis.export') }}" class="btn btn-sm btn-secondary">
                                <i class="fas fa-download"></i> Export Data
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection