@extends('layouts.app')

@section('title', 'Business Intelligence - Metrics')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0">Business Intelligence Metrics</h1>
                <div class="btn-group">
                    <button type="button" class="btn btn-outline-primary" onclick="generateMetricsReport()">
                        <i class="fas fa-file-export"></i> Export Report
                    </button>
                    <button type="button" class="btn btn-outline-success" onclick="calculateAllMetrics()">
                        <i class="fas fa-calculator"></i> Calculate Metrics
                    </button>
                </div>
            </div>
            <p class="text-muted">Detailed metrics analysis and reporting</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-body">
                    <form id="metricsFilterForm">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="categoryFilter">Category</label>
                                    <select class="form-control" id="categoryFilter" name="category">
                                        @foreach($categories as $cat)
                                        <option value="{{ $cat }}" {{ $category == $cat ? 'selected' : '' }}>
                                            {{ ucfirst($cat) }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="periodFilter">Period</label>
                                    <select class="form-control" id="periodFilter" name="period">
                                        @foreach($periods as $per)
                                        <option value="{{ $per }}" {{ $period == $per ? 'selected' : '' }}>
                                            {{ ucfirst($per) }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="startDateFilter">Start Date</label>
                                    <input type="date" class="form-control" id="startDateFilter" name="start_date" value="{{ $startDate }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="endDateFilter">End Date</label>
                                    <input type="date" class="form-control" id="endDateFilter" name="end_date" value="{{ $endDate }}">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 text-right">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-filter"></i> Apply Filters
                                </button>
                                <button type="button" class="btn btn-outline-secondary" onclick="resetFilters()">
                                    <i class="fas fa-redo"></i> Reset
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Metrics Summary -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Metrics Summary</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <div class="card border-left-primary h-100">
                                <div class="card-body">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        Total Metrics
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $metrics->count() }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card border-left-success h-100">
                                <div class="card-body">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                        Total Value
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        ${{ number_format($metrics->sum('metric_value'), 2) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card border-left-info h-100">
                                <div class="card-body">
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                        Average Value
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        ${{ number_format($metrics->avg('metric_value') ?? 0, 2) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card border-left-warning h-100">
                                <div class="card-body">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                        Calculated Metrics
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        {{ $metrics->where('is_calculated', true)->count() }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Metrics Chart -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Metrics Trend</h6>
                    <div class="dropdown no-arrow">
                        <a class="dropdown-toggle" href="#" role="button" id="chartMenu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in" aria-labelledby="chartMenu">
                            <a class="dropdown-item" href="#" onclick="changeChartType('line')">Line Chart</a>
                            <a class="dropdown-item" href="#" onclick="changeChartType('bar')">Bar Chart</a>
                            <a class="dropdown-item" href="#" onclick="changeChartType('area')">Area Chart</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="metricsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Metrics Table -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Metrics Data</h6>
                    <div class="dropdown no-arrow">
                        <a class="dropdown-toggle" href="#" role="button" id="tableMenu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in" aria-labelledby="tableMenu">
                            <a class="dropdown-item" href="#" onclick="exportTableToCSV()">Export to CSV</a>
                            <a class="dropdown-item" href="#" onclick="printTable()">Print Table</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="metricsTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Metric Name</th>
                                    <th>Category</th>
                                    <th>Type</th>
                                    <th>Value</th>
                                    <th>Calculated</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($metrics as $metric)
                                <tr>
                                    <td>{{ $metric->metric_date->format('Y-m-d') }}</td>
                                    <td>
                                        <span class="badge badge-info">{{ $metric->metric_name }}</span>
                                    </td>
                                    <td>
                                        <span class="badge badge-secondary">{{ $metric->category }}</span>
                                    </td>
                                    <td>
                                        <span class="badge badge-light">{{ $metric->metric_type }}</span>
                                    </td>
                                    <td class="font-weight-bold">
                                        @if(in_array($metric->category, ['revenue', 'spent']))
                                            ${{ number_format($metric->metric_value, 2) }}
                                        @else
                                            {{ number_format($metric->metric_value) }}
                                        @endif
                                    </td>
                                    <td>
                                        @if($metric->is_calculated)
                                            <span class="badge badge-success">
                                                <i class="fas fa-check"></i> Yes
                                            </span>
                                            <small class="text-muted d-block">
                                                {{ $metric->calculated_at ? $metric->calculated_at->format('Y-m-d H:i') : 'N/A' }}
                                            </small>
                                        @else
                                            <span class="badge badge-warning">
                                                <i class="fas fa-clock"></i> Pending
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-outline-info" onclick="viewMetricDetails({{ $metric->id }})" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-primary" onclick="calculateMetric({{ $metric->id }})" title="Calculate">
                                                <i class="fas fa-calculator"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-danger" onclick="deleteMetric({{ $metric->id }})" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="4" class="text-right">Total:</th>
                                    <th>
                                        @if($category == 'revenue')
                                            ${{ number_format($metrics->sum('metric_value'), 2) }}
                                        @else
                                            {{ number_format($metrics->sum('metric_value')) }}
                                        @endif
                                    </th>
                                    <th colspan="2"></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Metric Details Modal -->
<div class="modal fade" id="metricDetailsModal" tabindex="-1" role="dialog" aria-labelledby="metricDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="metricDetailsModalLabel">Metric Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="metricDetailsContent">
                <!-- Details will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Metrics Chart
    var metricsChartCtx = document.getElementById('metricsChart').getContext('2d');
    var metricsChart = new Chart(metricsChartCtx, {
        type: 'line',
        data: {
            labels: @json($metrics->pluck('metric_date')->map(function($date) { return $date->format('M d'); })),
            datasets: [{
                label: '{{ ucfirst($category) }} Metrics',
                data: @json($metrics->pluck('metric_value')),
                borderColor: '#3498db',
                backgroundColor: 'rgba(52, 152, 219, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    callbacks: {
                        label: function(context) {
                            let value = context.parsed.y;
                            @if($category == 'revenue')
                                return '₱' + value.toFixed(2);
                            @else
                                return value.toFixed(2);
                            @endif
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    }
                },
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            @if($category == 'revenue')
                                return '₱' + value;
                            @else
                                return value;
                            @endif
                        }
                    }
                }
            }
        }
    });

    // Filter Form Submission
    document.getElementById('metricsFilterForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const params = new URLSearchParams(formData).toString();
        window.location.href = '{{ route("business-intelligence.metrics") }}?' + params;
    });

    function resetFilters() {
        window.location.href = '{{ route("business-intelligence.metrics") }}';
    }

    function changeChartType(type) {
        metricsChart.config.type = type;
        metricsChart.update();
    }

    function generateMetricsReport() {
        const formData = new FormData(document.getElementById('metricsFilterForm'));
        const params = new URLSearchParams(formData).toString();
        
        fetch('{{ route("analytics.export") }}?' + params + '&format=csv&type=metrics', {
            method: 'GET',
            headers: {
                'Accept': 'application/json'
            }
        })
        .then(response => response.blob())
        .then(blob => {
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `metrics_report_{{ $category }}_{{ $period }}_{{ $startDate }}_to_{{ $endDate }}.csv`;
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            document.body.removeChild(a);
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to generate report');
        });
    }

    function calculateAllMetrics() {
        if (confirm('Are you sure you want to calculate all metrics for the selected period? This may take a moment.')) {
            fetch('{{ route("analytics.generate-daily") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.message) {
                    alert('Metrics calculation started successfully. The page will refresh in a moment.');
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to calculate metrics');
            });
        }
    }

    function viewMetricDetails(metricId) {
        fetch(`/business-intelligence/metrics/${metricId}/details`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const modalContent = document.getElementById('metricDetailsContent');
                modalContent.innerHTML = `
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Basic Information</h6>
                            <table class="table table-sm">
                                <tr>
                                    <th>Metric Name:</th>
                                    <td>${data.metric.metric_name}</td>
                                </tr>
                                <tr>
                                    <th>Category:</th>
                                    <td><span class="badge badge-secondary">${data.metric.category}</span></td>
                                </tr>
                                <tr>
                                    <th>Type:</th>
                                    <td><span class="badge badge-light">${data.metric.metric_type}</span></td>
                                </tr>
                                <tr>
                                    <th>Date:</th>
                                    <td>${data.metric.metric_date}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6>Value Information</h6>
                            <table class="table table-sm">
                                <tr>
                                    <th>Value:</th>
                                    <td class="font-weight-bold">
                                        ${data.metric.category === 'revenue' ? '₱' + parseFloat(data.metric.metric_value).toFixed(2) : data.metric.metric_value}
                                    </td>
                                </tr>
                                <tr>
                                    <th>