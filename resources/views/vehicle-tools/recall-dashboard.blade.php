@extends('layouts.app')

@section('title', 'Recall Analytics Dashboard')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h1 class="h3 mb-0">Recall Analytics Dashboard</h1>
            <p class="text-muted">Comprehensive analytics and insights for recall management</p>
        </div>
        <div class="col-auto">
            <div class="btn-group">
                <a href="{{ route('recalls.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Recalls
                </a>
                <a href="{{ route('recalls.analytics') }}" class="btn btn-info">
                    <i class="fas fa-chart-pie"></i> Detailed Analytics
                </a>
                <button class="btn btn-success" data-toggle="modal" data-target="#scheduleModal">
                    <i class="fas fa-calendar-alt"></i> Schedule Checks
                </button>
            </div>
        </div>
    </div>

    <!-- Key Metrics -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Recalls</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($totalRecalls) }}</div>
                            <div class="mt-2 mb-0">
                                <span class="text-success mr-2">
                                    <i class="fas fa-arrow-up"></i> {{ $completedRecalls }} completed
                                </span>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-list fa-2x text-gray-300"></i>
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
                                Active Recalls</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($openRecalls + $inProgressRecalls) }}</div>
                            <div class="mt-2 mb-0">
                                <span class="text-danger mr-2">
                                    <i class="fas fa-exclamation-circle"></i> {{ $urgentRecalls }} urgent
                                </span>
                            </div>
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
                                Cost Analysis</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">${{ number_format($totalEstimatedCost, 0) }}</div>
                            <div class="mt-2 mb-0">
                                <span class="{{ $costSavings >= 0 ? 'text-success' : 'text-danger' }}">
                                    <i class="fas fa-{{ $costSavings >= 0 ? 'arrow-down' : 'arrow-up' }}"></i> 
                                    ${{ number_format(abs($costSavings), 0) }} {{ $costSavings >= 0 ? 'saved' : 'over' }}
                                </span>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
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
                                Customer Notifications</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($needsNotification) }}</div>
                            <div class="mt-2 mb-0">
                                <span class="text-warning">
                                    <i class="fas fa-bell"></i> {{ $overdueRecalls }} overdue
                                </span>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-bell fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts and Graphs -->
    <div class="row">
        <!-- Recall Trends Chart -->
        <div class="col-xl-8 col-lg-7 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-line"></i> Recall Trends (Last 6 Months)
                    </h6>
                    <div class="dropdown no-arrow">
                        <a class="dropdown-toggle" href="#" role="button" id="trendsDropdown" 
                           data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in" 
                             aria-labelledby="trendsDropdown">
                            <a class="dropdown-item" href="#" onclick="exportChart('trendsChart', 'recall-trends.png')">
                                <i class="fas fa-download fa-sm fa-fw mr-2 text-gray-400"></i>
                                Export Chart
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="trendsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Status Distribution -->
        <div class="col-xl-4 col-lg-5 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-info">
                        <i class="fas fa-chart-pie"></i> Recall Status Distribution
                    </h6>
                </div>
                <div class="card-body">
                    <div class="chart-pie pt-4 pb-2">
                        <canvas id="statusChart"></canvas>
                    </div>
                    <div class="mt-4 text-center small">
                        <span class="mr-2">
                            <i class="fas fa-circle text-warning"></i> Open
                        </span>
                        <span class="mr-2">
                            <i class="fas fa-circle text-info"></i> In Progress
                        </span>
                        <span class="mr-2">
                            <i class="fas fa-circle text-success"></i> Completed
                        </span>
                        <span class="mr-2">
                            <i class="fas fa-circle text-secondary"></i> Closed
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Recalls and Top Vehicles -->
    <div class="row">
        <!-- Recent Recalls -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-warning">
                        <i class="fas fa-history"></i> Recent Recalls
                    </h6>
                    <a href="{{ route('recalls.index') }}" class="btn btn-sm btn-warning">View All</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Vehicle</th>
                                    <th>Component</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentRecalls as $recall)
                                <tr>
                                    <td>{{ $recall->recall_date->format('m/d') }}</td>
                                    <td>
                                        <a href="{{ route('recalls.show', $recall->id) }}" class="text-decoration-none">
                                            {{ $recall->vehicle->make }} {{ $recall->vehicle->model }}
                                        </a>
                                    </td>
                                    <td>{{ Str::limit($recall->component, 20) }}</td>
                                    <td>
                                        <span class="badge badge-{{ $recall->status_color }}">
                                            {{ ucfirst($recall->status) }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Vehicles with Most Recalls -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-danger">
                        <i class="fas fa-car"></i> Vehicles with Most Recalls
                    </h6>
                    <a href="{{ route('vehicle-tools.service-history') }}" class="btn btn-sm btn-danger">View Vehicles</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>Vehicle</th>
                                    <th>Recalls</th>
                                    <th>Last Recall</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($vehiclesWithMostRecalls as $vehicle)
                                <tr>
                                    <td>
                                        <a href="{{ route('vehicle-tools.service-history', ['vehicleId' => $vehicle->id]) }}" 
                                           class="text-decoration-none">
                                            {{ $vehicle->year }} {{ $vehicle->make }} {{ $vehicle->model }}
                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge badge-danger">{{ $vehicle->open_recall_count }}</span>
                                    </td>
                                    <td>
                                        @if($vehicle->last_recall_check)
                                            {{ $vehicle->last_recall_check->diffForHumans() }}
                                        @else
                                            <span class="text-muted">Never</span>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $recallStatus = $vehicle->recall_status_with_color;
                                        @endphp
                                        <span class="badge badge-{{ $recallStatus['color'] }}">
                                            {{ $recallStatus['label'] }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Cost Analysis -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-success">
                        <i class="fas fa-dollar-sign"></i> Cost Analysis
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 text-center">
                            <div class="display-4 text-primary">${{ number_format($totalEstimatedCost, 0) }}</div>
                            <p class="text-muted">Total Estimated Cost</p>
                        </div>
                        <div class="col-md-4 text-center">
                            <div class="display-4 text-info">${{ number_format($totalActualCost, 0) }}</div>
                            <p class="text-muted">Total Actual Cost</p>
                        </div>
                        <div class="col-md-4 text-center">
                            <div class="display-4 {{ $costSavings >= 0 ? 'text-success' : 'text-danger' }}">
                                ${{ number_format(abs($costSavings), 0) }}
                            </div>
                            <p class="text-muted">
                                {{ $costSavings >= 0 ? 'Cost Savings' : 'Cost Overrun' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Schedule Modal -->
<div class="modal fade" id="scheduleModal" tabindex="-1" role="dialog" aria-labelledby="scheduleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="scheduleModalLabel">Schedule Automatic Recall Checks</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('recalls.schedule-checks') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="frequency">Check Frequency</label>
                        <select name="frequency" id="frequency" class="form-control" required>
                            <option value="daily">Daily</option>
                            <option value="weekly" selected>Weekly</option>
                            <option value="monthly">Monthly</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="time">Check Time</label>
                        <input type="time" name="time" id="time" class="form-control" value="09:00" required>
                    </div>
                    <div class="form-check mb-3">
                        <input type="checkbox" name="notify_on_find" id="notify_on_find" class="form-check-input" checked>
                        <label for="notify_on_find" class="form-check-label">
                            Notify staff when new recalls are found
                        </label>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" name="notify_customer" id="notify_customer" class="form-check-input">
                        <label for="notify_customer" class="form-check-label">
                            Automatically notify customers of new recalls
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Schedule Checks</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Trends Chart
    const trendsCtx = document.getElementById('trendsChart').getContext('2d');
    const trendsChart = new Chart(trendsCtx, {
        type: 'line',
        data: {
            labels: [
                @foreach($recallTrends as $trend)
                    '{{ $trend['month'] }}',
                @endforeach
            ],
            datasets: [{
                label: 'Total Recalls',
                data: [
                    @foreach($recallTrends as $trend)
                        {{ $trend['total'] }},
                    @endforeach
                ],
                borderColor: '#4e73df',
                backgroundColor: 'rgba(78, 115, 223, 0.05)',
                borderWidth: 2,
                fill: true,
                tension: 0.4
            }, {
                label: 'Open Recalls',
                data: [
                    @foreach($recallTrends as $trend)
                        {{ $trend['open'] }},
                    @endforeach
                ],
                borderColor: '#f6c23e',
                backgroundColor: 'rgba(246, 194, 62, 0.05)',
                borderWidth: 2,
                fill: false,
                tension: 0.4
            }, {
                label: 'Completed Recalls',
                data: [
                    @foreach($recallTrends as $trend)
                        {{ $trend['completed'] }},
                    @endforeach
                ],
                borderColor: '#1cc88a',
                backgroundColor: 'rgba(28, 200, 138, 0.05)',
                borderWidth: 2,
                fill: false,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return value;
                        }
                    }
                }
            }
        }
    });
    
    // Status Chart
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    const statusChart = new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Open', 'In Progress', 'Completed', 'Closed'],
            datasets: [{
                data: [
                    {{ $openRecalls }},
                    {{ $inProgressRecalls }},
                    {{ $completedRecalls }},
                    {{ $closedRecalls }}
                ],
                backgroundColor: [
                    '#f6c23e', // Open - yellow
                    '#36b9cc', // In Progress - teal
                    '#1cc88a', // Completed - green
                    '#858796'  // Closed - gray
                ],
                hoverBackgroundColor: [
                    '#f6c23e',
                    '#36b9cc',
                    '#1cc88a',
                    '#858796'
                ],
                hoverBorderColor: "rgba(234, 236, 244, 1)",
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            cutout: '70%',
        }
    });
    
    // Export chart function
    function exportChart(chartId, filename) {
        const chart = Chart.getChart(chartId);
        if (chart) {
            const link = document.createElement('a');
            link.download = filename;
            link.href = chart.toBase64Image();
            link.click();
        }
    }
    
    // Auto-refresh dashboard every 10 minutes
    setTimeout(function() {
        window.location.reload();
    }, 600000); // 10 minutes
</script>
@endsection