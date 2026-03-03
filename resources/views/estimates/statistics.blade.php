@extends('layouts.app')

@section('title', 'Estimate Statistics')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-chart-bar text-primary"></i> Estimate Statistics
        </h1>
        <div class="btn-group">
            <a href="{{ route('estimates.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Estimates
            </a>
            <button onclick="window.location.reload()" class="btn btn-primary">
                <i class="fas fa-sync-alt"></i> Refresh
            </button>
        </div>
    </div>

    <!-- Overall Statistics Cards -->
    <div class="row">
        <!-- Total Estimates Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Estimates
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($totalEstimates) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-file-invoice-dollar fa-2x text-gray-500"></i>
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
                                Total Value
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                ₱{{ number_format($totalValue, 2) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-money-bill-wave fa-2x text-gray-500"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Average Value Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Average Value
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                ₱{{ number_format($avgValue, 2) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calculator fa-2x text-gray-500"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Status Breakdown Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Status Breakdown
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ count($statusBreakdown) }} Statuses
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-pie fa-2x text-gray-500"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row">
        <!-- Status Breakdown Chart -->
        <div class="col-xl-6 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Estimate Status Breakdown</h6>
                </div>
                <div class="card-body">
                    <div class="chart-pie pt-4 pb-2">
                        <canvas id="statusPieChart"></canvas>
                    </div>
                    <div class="mt-4 text-center small">
                        @foreach($statusBreakdown as $status => $count)
                            <span class="mr-2">
                                <i class="fas fa-circle" style="color: {{ getStatusColor($status) }}"></i> {{ ucfirst($status) }} ({{ $count }})
                            </span>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Monthly Trends Chart -->
        <div class="col-xl-6 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Monthly Estimate Trends (Last 6 Months)</h6>
                </div>
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="monthlyTrendChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Tables Row -->
    <div class="row">
        <!-- Top Customers -->
        <div class="col-xl-6 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Top Customers by Estimate Count</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Customer</th>
                                    <th>Estimate Count</th>
                                    <th>Total Value</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($topCustomers as $customer)
                                    <tr>
                                        <td>
                                            @if($customer->customer)
                                                {{ $customer->customer->first_name }} {{ $customer->customer->last_name }}
                                            @else
                                                Unknown Customer
                                            @endif
                                        </td>
                                        <td>{{ $customer->estimate_count }}</td>
                                        <td>₱{{ number_format($customer->total_value, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Technician Performance -->
        <div class="col-xl-6 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Technician Performance</h6>
                </div>
                <div class="card-body">
                    @if($technicianStats->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>Technician</th>
                                        <th>Estimate Count</th>
                                        <th>Total Value</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($technicianStats as $tech)
                                        <tr>
                                            <td>
                                                @if($tech->technician)
                                                    {{ $tech->technician->first_name }} {{ $tech->technician->last_name }}
                                                @else
                                                    Unassigned
                                                @endif
                                            </td>
                                            <td>{{ $tech->estimate_count }}</td>
                                            <td>₱{{ number_format($tech->total_value, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-info-circle fa-2x text-gray-300 mb-3"></i>
                            <p class="text-muted">Technician performance data is not available.</p>
                            <p class="small text-muted">Estimates are not currently assigned to technicians.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Status Breakdown Pie Chart
    const statusPieChart = document.getElementById('statusPieChart');
    if (statusPieChart) {
        new Chart(statusPieChart, {
            type: 'pie',
            data: {
                labels: {!! json_encode(array_keys($statusBreakdown)) !!},
                datasets: [{
                    data: {!! json_encode(array_values($statusBreakdown)) !!},
                    backgroundColor: {!! json_encode(array_map(function($status) {
                        return getStatusColor($status);
                    }, array_keys($statusBreakdown))) !!},
                    hoverBackgroundColor: {!! json_encode(array_map(function($status) {
                        return getStatusColor($status, true);
                    }, array_keys($statusBreakdown))) !!},
                    hoverBorderColor: "rgba(234, 236, 244, 1)",
                }],
            },
            options: {
                maintainAspectRatio: false,
                tooltips: {
                    backgroundColor: "rgb(255,255,255)",
                    bodyFontColor: "#858796",
                    borderColor: '#dddfeb',
                    borderWidth: 1,
                    xPadding: 15,
                    yPadding: 15,
                    displayColors: false,
                    caretPadding: 10,
                },
                legend: {
                    display: false
                },
                cutoutPercentage: 0,
            },
        });
    }

    // Monthly Trends Chart
    const monthlyTrendChart = document.getElementById('monthlyTrendChart');
    if (monthlyTrendChart) {
        const months = {!! json_encode($monthlyStats->pluck('month')) !!};
        const counts = {!! json_encode($monthlyStats->pluck('count')) !!};
        const totals = {!! json_encode($monthlyStats->pluck('total')) !!};
        
        new Chart(monthlyTrendChart, {
            type: 'line',
            data: {
                labels: months,
                datasets: [{
                    label: "Estimate Count",
                    lineTension: 0.3,
                    backgroundColor: "rgba(78, 115, 223, 0.05)",
                    borderColor: "rgba(78, 115, 223, 1)",
                    pointRadius: 3,
                    pointBackgroundColor: "rgba(78, 115, 223, 1)",
                    pointBorderColor: "rgba(78, 115, 223, 1)",
                    pointHoverRadius: 3,
                    pointHoverBackgroundColor: "rgba(78, 115, 223, 1)",
                    pointHoverBorderColor: "rgba(78, 115, 223, 1)",
                    pointHitRadius: 10,
                    pointBorderWidth: 2,
                    data: counts,
                }],
            },
            options: {
                maintainAspectRatio: false,
                layout: {
                    padding: {
                        left: 10,
                        right: 25,
                        top: 25,
                        bottom: 0
                    }
                },
                scales: {
                    xAxes: [{
                        gridLines: {
                            display: false,
                            drawBorder: false
                        },
                        ticks: {
                            maxTicksLimit: 7
                        }
                    }],
                    yAxes: [{
                        ticks: {
                            maxTicksLimit: 5,
                            padding: 10,
                            beginAtZero: true
                        },
                        gridLines: {
                            color: "rgb(234, 236, 244)",
                            zeroLineColor: "rgb(234, 236, 244)",
                            drawBorder: false,
                            borderDash: [2],
                            zeroLineBorderDash: [2]
                        }
                    }],
                },
                legend: {
                    display: false
                },
                tooltips: {
                    backgroundColor: "rgb(255,255,255)",
                    bodyFontColor: "#858796",
                    titleMarginBottom: 10,
                    titleFontColor: '#6e707e',
                    titleFontSize: 14,
                    borderColor: '#dddfeb',
                    borderWidth: 1,
                    xPadding: 15,
                    yPadding: 15,
                    displayColors: false,
                    intersect: false,
                    mode: 'index',
                    caretPadding: 10,
                }
            }
        });
    }

    // Helper function to get status colors
    function getStatusColor(status, hover = false) {
        const colors = {
            'draft': hover ? '#e74a3b' : '#f6c23e',
            'pending': hover ? '#36b9cc' : '#1cc88a',
            'sent': hover ? '#4e73df' : '#858796',
            'viewed': hover ? '#6610f2' : '#e74a3b',
            'accepted': hover ? '#1cc88a' : '#36b9cc',
            'rejected': hover ? '#e74a3b' : '#f6c23e',
            'expired': hover ? '#858796' : '#4e73df',
            'approved': hover ? '#1cc88a' : '#36b9cc',
        };
        return colors[status] || (hover ? '#858796' : '#dddfeb');
    }
</script>
@endsection

@php
    // Helper function for Blade template
    function getStatusColor($status, $hover = false) {
        $colors = [
            'draft' => $hover ? '#e74a3b' : '#f6c23e',
            'pending' => $hover ? '#36b9cc' : '#1cc88a',
            'sent' => $hover ? '#4e73df' : '#858796',
            'viewed' => $hover ? '#6610f2' : '#e74a3b',
            'accepted' => $hover ? '#1cc88a' : '#36b9cc',
            'rejected' => $hover ? '#e74a3b' : '#f6c23e',
            'expired' => $hover ? '#858796' : '#4e73df',
            'approved' => $hover ? '#1cc88a' : '#36b9cc',
        ];
        return $colors[$status] ?? ($hover ? '#858796' : '#dddfeb');
    }
@endphp