@extends('layouts.app')

@section('title', 'Business Intelligence Dashboard')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0">Business Intelligence Dashboard</h1>
                <div class="btn-group">
                    <button type="button" class="btn btn-outline-primary" id="refreshDashboard">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                    <button type="button" class="btn btn-outline-secondary" id="addWidgetBtn">
                        <i class="fas fa-plus"></i> Add Widget
                    </button>
                    <button type="button" class="btn btn-outline-danger" id="resetDashboardBtn">
                        <i class="fas fa-redo"></i> Reset Dashboard
                    </button>
                </div>
            </div>
            <p class="text-muted">Monitor key metrics, trends, and business performance</p>
        </div>
    </div>

    <!-- Key Metrics Cards -->
    <div class="row mb-4" id="keyMetricsRow">
        @foreach($metrics as $key => $metric)
        <div class="col-xl-2 col-lg-4 col-md-6 mb-4">
            <div class="card border-left-{{ $metric['color'] }} shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-{{ $metric['color'] }} text-uppercase mb-1">
                                {{ str_replace('_', ' ', ucfirst($key)) }}
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                @if(in_array($key, ['daily_revenue', 'total_spent']))
                                    ${{ number_format($metric['value'], 2) }}
                                @elseif(in_array($key, ['customer_satisfaction', 'technician_productivity', 'retention_rate']))
                                    {{ number_format($metric['value'], 2) }}@if($key == 'customer_satisfaction')/5 @else% @endif
                                @else
                                    {{ number_format($metric['value']) }}
                                @endif
                            </div>
                            @if($metric['trend'])
                            <div class="mt-2">
                                <span class="text-{{ $metric['trend']['direction'] == 'up' ? 'success' : ($metric['trend']['direction'] == 'down' ? 'danger' : 'secondary') }}">
                                    <i class="fas fa-arrow-{{ $metric['trend']['direction'] == 'up' ? 'up' : ($metric['trend']['direction'] == 'down' ? 'down' : 'right') }}"></i>
                                    {{ number_format(abs($metric['trend']['percentage']), 2) }}%
                                </span>
                                <small class="text-muted">vs previous</small>
                            </div>
                            @endif
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-{{ $metric['icon'] }} fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Dashboard Widgets Grid -->
    <div class="row" id="dashboardWidgets">
        <!-- Widgets will be loaded here dynamically -->
        <div class="col-12">
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> No widgets configured. Click "Add Widget" to customize your dashboard.
            </div>
        </div>
    </div>

    <!-- Charts and Analytics -->
    <div class="row mt-4">
        <!-- Revenue Trend Chart -->
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Revenue Trend (Last 30 Days)</h6>
                    <div class="dropdown no-arrow">
                        <a class="dropdown-toggle" href="#" role="button" id="revenueChartMenu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in" aria-labelledby="revenueChartMenu">
                            <a class="dropdown-item" href="#" onclick="changeChartPeriod('revenue', '7')">Last 7 Days</a>
                            <a class="dropdown-item" href="#" onclick="changeChartPeriod('revenue', '30')">Last 30 Days</a>
                            <a class="dropdown-item" href="#" onclick="changeChartPeriod('revenue', '90')">Last 90 Days</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="revenueChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Retention Stats -->
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Customer Retention</h6>
                </div>
                <div class="card-body">
                    <div class="text-center">
                        <div class="h1 mb-0 font-weight-bold text-gray-800">{{ $retentionStats['average_retention_score'] }}/100</div>
                        <div class="text-muted">Average Retention Score</div>
                    </div>
                    <hr>
                    <div class="row">
                        @foreach(['active', 'at_risk', 'lapsed', 'lost'] as $status)
                        <div class="col-6 mb-3">
                            <div class="text-xs font-weight-bold text-uppercase mb-1">{{ ucfirst($status) }}</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $retentionStats[$status]->count ?? 0 }}</div>
                            <div class="text-xs text-muted">{{ $retentionStats[$status]->percentage ?? 0 }}% of customers</div>
                        </div>
                        @endforeach
                    </div>
                    <a href="{{ route('business-intelligence.customer-retention') }}" class="btn btn-outline-primary btn-block mt-3">
                        <i class="fas fa-chart-line"></i> View Detailed Analytics
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Technician Performance -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Technician Performance</h6>
                    <a href="{{ route('business-intelligence.technician-performance') }}" class="btn btn-sm btn-outline-primary">
                        View All <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="technicianPerformanceTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Technician</th>
                                    <th>Completed Jobs</th>
                                    <th>Total Hours</th>
                                    <th>Efficiency (Jobs/Hour)</th>
                                    <th>Rating</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($technicianPerformance as $tech)
                                <tr>
                                    <td>{{ $tech['name'] }}</td>
                                    <td>{{ $tech['completed_jobs'] }}</td>
                                    <td>{{ number_format($tech['total_hours'], 1) }}</td>
                                    <td>
                                        <div class="progress">
                                            <div class="progress-bar bg-{{ $tech['efficiency'] >= 2 ? 'success' : ($tech['efficiency'] >= 1 ? 'warning' : 'danger') }}" 
                                                 role="progressbar" 
                                                 style="width: {{ min($tech['efficiency'] * 50, 100) }}%"
                                                 aria-valuenow="{{ $tech['efficiency'] }}" 
                                                 aria-valuemin="0" 
                                                 aria-valuemax="4">
                                                {{ number_format($tech['efficiency'], 2) }}
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if($tech['rating'] > 0)
                                            <div class="star-rating">
                                                @for($i = 1; $i <= 5; $i++)
                                                    <i class="fas fa-star{{ $i <= $tech['rating'] ? ' text-warning' : ' text-gray-300' }}"></i>
                                                @endfor
                                                <small class="text-muted ml-1">{{ number_format($tech['rating'], 1) }}</small>
                                            </div>
                                        @else
                                            <span class="text-muted">No ratings</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $tech['efficiency'] >= 2 ? 'success' : ($tech['efficiency'] >= 1 ? 'warning' : 'danger') }}">
                                            {{ $tech['efficiency'] >= 2 ? 'High' : ($tech['efficiency'] >= 1 ? 'Medium' : 'Low') }}
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
</div>

<!-- Add Widget Modal -->
<div class="modal fade" id="addWidgetModal" tabindex="-1" role="dialog" aria-labelledby="addWidgetModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addWidgetModalLabel">Add Dashboard Widget</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="addWidgetForm">
                    <div class="form-group">
                        <label for="widgetType">Widget Type</label>
                        <select class="form-control" id="widgetType" name="widget_type" required>
                            <option value="">Select widget type...</option>
                            <option value="metric_card">Metric Card</option>
                            <option value="chart">Chart</option>
                            <option value="table">Data Table</option>
                            <option value="kpi">KPI Indicator</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="widgetTitle">Widget Title</label>
                        <input type="text" class="form-control" id="widgetTitle" name="widget_title" required>
                    </div>
                    <div class="form-group" id="metricNameGroup" style="display: none;">
                        <label for="metricName">Metric</label>
                        <select class="form-control" id="metricName" name="metric_name">
                            <option value="">Select metric...</option>
                            @foreach($metrics as $key => $metric)
                            <option value="{{ $key }}">{{ str_replace('_', ' ', ucfirst($key)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="columnPosition">Column Position (0-3)</label>
                                <input type="number" class="form-control" id="columnPosition" name="column_position" min="0" max="3" value="0" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="rowPosition">Row Position (0-10)</label>
                                <input type="number" class="form-control" id="rowPosition" name="row_position" min="0" max="10" value="0" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="widgetWidth">Width (1-4 columns)</label>
                                <input type="number" class="form-control" id="widgetWidth" name="width" min="1" max="4" value="1" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="widgetHeight">Height (1-4 rows)</label>
                                <input type="number" class="form-control" id="widgetHeight" name="height" min="1" max="4" value="1" required>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveWidgetBtn">Add Widget</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Revenue Chart
    var revenueChartCtx = document.getElementById('revenueChart').getContext('2d');
    var revenueChart = new Chart(revenueChartCtx, {
        type: 'line',
        data: {
            labels: @json($revenueTrend['labels']),
            datasets: [{
                label: 'Daily Revenue',
                data: @json($revenueTrend['data']),
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
                            return '$' + context.parsed.y.toFixed(2);
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
                            return '$' + value;
                        }
                    }
                }
            }
        }
    });

    // Widget Management
    document.addEventListener('DOMContentLoaded', function() {
        // Load widgets
        loadWidgets();

        // Add Widget Button
        document.getElementById('addWidgetBtn').addEventListener('click', function() {
            $('#addWidgetModal').modal('show');
        });

        // Widget Type Change
        document.getElementById('widgetType').addEventListener('change', function() {
            const metricGroup = document.getElementById('metricNameGroup');
            if (this.value === 'metric_card' || this.value === 'chart') {
                metricGroup.style.display = 'block';
            } else {
                metricGroup.style.display = 'none';
            }
        });

        // Save Widget
        document.getElementById('saveWidgetBtn').addEventListener('click', function() {
            const form = document.getElementById('addWidgetForm');
            const formData = new FormData(form);
            
            fetch('{{ route("dashboard.widgets.create") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(Object.fromEntries(formData))
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    $('#addWidgetModal').modal('hide');
                    form.reset();
                    loadWidgets();
                    showToast('Widget added successfully!', 'success');
                } else {
                    showToast('Failed to add widget: ' + data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('An error occurred while adding the widget', 'error');
            });
        });

        // Reset Dashboard
        document.getElementById('resetDashboardBtn').addEventListener('click', function() {
            if (confirm('Are you sure you want to reset your dashboard to default configuration? This will remove all your custom widgets.')) {
                fetch('{{ route("dashboard.reset") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        loadWidgets();
                        showToast('Dashboard reset successfully!', 'success');
                    } else {
                        showToast('Failed to reset dashboard', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('An error occurred while resetting the dashboard', 'error');
                });
            }
        });

        // Refresh Dashboard
        document.getElementById('refreshDashboard').addEventListener('click', function() {
            loadWidgets();
            revenueChart.update();
            showToast('Dashboard refreshed!', 'info');
        });
    });

    function loadWidgets() {
        fetch('{{ route("dashboard.widgets.get") }}')
            .then(response => response.json())
            .then(data => {
                if (data.success && data.widgets.length > 0) {
                    renderWidgets(data.widgets);
                }
            })
            .catch(error => {
                console.error('Error loading widgets:', error);
            });
    }

    function renderWidgets(widgets) {
        const container = document.getElementById('dashboardWidgets');
        container.innerHTML = '';
        
        // Sort widgets by position
        widgets.sort((a, b) => {
            if (a.column_position === b.column_position) {
                return a.row_position - b.row_position;
            }
            return a.column_position - b.column_position;
        });
        
        // Create widget grid
        widgets.forEach(widget => {
            const colSize = getColSize(widget.width);
            const widgetElement = createWidgetElement(widget);
            container.appendChild(widgetElement);
        });
    }

    function getColSize(width) {
        const sizes = {
            1: 'col-xl-3 col-lg-4 col-md-6',
            2: 'col-xl-6 col-lg-8 col-md-12',
            3: 'col-xl-9 col-lg-12',
            4: 'col-12'
        };
        return sizes[width] || 'col-xl-3 col-lg-4 col-md-6';
    }

    function createWidgetElement(widget) {
        const colDiv = document.createElement('div');
        colDiv.className = getColSize(widget.width) + ' mb-4';
        colDiv.dataset.widgetId = widget.id;
        
        let widgetContent = '';
        
        switch(widget.widget_type) {
            case 'metric_card':
                widgetContent = createMetricCardWidget(widget);
                break;
            case 'chart':
                widgetContent = createChartWidget(widget);
                break;
            case 'table':
                widgetContent = createTableWidget(widget);
                break;
            case 'kpi':
                widgetContent = createKpiWidget(widget);
                break;
            default:
                widgetContent = createDefaultWidget(widget);
        }
        
        colDiv.innerHTML = widgetContent;
        return colDiv;
    }

    function createMetricCardWidget(widget) {
        return `
            <div class="card shadow h-100">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">${widget.widget_title}</h6>
                    <div class="widget-actions">
                        <button class="btn btn-sm btn-outline-secondary toggle-visibility" data-widget-id="${widget.id}">
                            <i class="fas fa-eye${widget.is_visible ? '' : '-slash'}"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-secondary toggle-collapsed" data-widget-id="${widget.id}">
                            <i class="fas fa-${widget.is_collapsed ? 'expand' : 'compress'}"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger delete-widget" data-widget-id="${widget.id}">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body ${widget.is_collapsed ? 'd-none' : ''}">
                    <div class="widget-content" data-widget-id="${widget.id}">
                        <div class="text-center py-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="sr-only">Loading...</span>
                            </div>
                            <p class="mt-2 text-muted">Loading widget data...</p>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    function createChartWidget(widget) {
        return `
            <div class="card shadow h-100">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">${widget.widget_title}</h6>
                    <div class="widget-actions">
                        <button class="btn btn-sm btn-outline-secondary toggle-visibility" data-widget-id="${widget.id}">
                            <i class="fas fa-eye${widget.is_visible ? '' : '-slash'}"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-secondary toggle-collapsed" data-widget-id="${widget.id}">
                            <i class="fas fa-${widget.is_collapsed ? 'expand' : 'compress'}"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger delete-widget" data-widget-id="${widget.id}">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body ${widget.is_collapsed ? 'd-none' : ''}">
                    <div class="chart-container" style="position: relative; height: ${widget.height * 100}px;">
                        <canvas id="widgetChart_${widget.id}"></canvas>
                    </div>
                </div>
            </div>
        `;
    }

    function createTableWidget(widget) {
        return `
            <div class="card shadow h-100">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">${widget.widget_title}</h6>
                    <div class="widget-actions">
                        <button class="btn btn-sm btn-outline-secondary toggle-visibility" data-widget-id="${widget.id}">
                            <i class="fas fa-eye${widget.is_visible ? '' : '-slash'}"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-secondary toggle-collapsed" data-widget-id="${widget.id}">
                            <i class="fas fa-${widget.is_collapsed ? 'expand' : 'compress'}"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger delete-widget" data-widget-id="${widget.id}">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body ${widget.is_collapsed ? 'd-none' : ''}">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="widgetTable_${widget.id}" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Loading...</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Loading data...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        `;
    }

    function createKpiWidget(widget) {
        return `
            <div class="card shadow h-100">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">${widget.widget_title}</h6>
                    <div class="widget-actions">
                        <button class="btn btn-sm btn-outline-secondary toggle-visibility" data-widget-id="${widget.id}">
                            <i class="fas fa-eye${widget.is_visible ? '' : '-slash'}"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-secondary toggle-collapsed" data-widget-id="${widget.id}">
                            <i class="fas fa-${widget.is_collapsed ? 'expand' : 'compress'}"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger delete-widget" data-widget-id="${widget.id}">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body ${widget.is_collapsed ? 'd-none' : ''}">
                    <div class="text-center py-4">
                        <div class="kpi-value" id="kpiValue_${widget.id}">
                            <div class="spinner-border text-primary" role="status">
                                <span class="sr-only">Loading...</span>
                            </div>
                        </div>
                        <div class="kpi-progress mt-3" id="kpiProgress_${widget.id}">
                            <div class="progress" style="height: 20px;">
                                <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    function createDefaultWidget(widget) {
        return `
            <div class="card shadow h-100">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">${widget.widget_title}</h6>
                    <div class="widget-actions">
                        <button class="btn btn-sm btn-outline-secondary toggle-visibility" data-widget-id="${widget.id}">
                            <i class="fas fa-eye${widget.is_visible ? '' : '-slash'}"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-secondary toggle-collapsed" data-widget-id="${widget.id}">
                            <i class="fas fa-${widget.is_collapsed ? 'expand' : 'compress'}"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger delete-widget" data-widget-id="${widget.id}">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body ${widget.is_collapsed ? 'd-none' : ''}">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> Unknown widget type: ${widget.widget_type}
                    </div>
                </div>
            </div>
        `;
    }

    function changeChartPeriod(chartType, days) {
        // This would fetch new chart data based on the period
        console.log(`Changing ${chartType} chart period to ${days} days`);
        // Implement AJAX call to update chart data
    }

    function showToast(message, type = 'info') {
        // Simple toast notification
        const toast = document.createElement('div');
        toast.className = `toast align-items-center text-white bg-${type === 'success' ? 'success' : type === 'error' ? 'danger' : 'info'} border-0`;
        toast.setAttribute('role', 'alert');
        toast.setAttribute('aria-live', 'assertive');
        toast.setAttribute('aria-atomic', 'true');
        
        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        `;
        
        const toastContainer = document.getElementById('toastContainer') || (() => {
            const container = document.createElement('div');
            container.id = 'toastContainer';
            container.className = 'toast-container position-fixed top-0 end-0 p-3';
            document.body.appendChild(container);
            return container;
        })();
        
        toastContainer.appendChild(toast);
        
        const bsToast = new bootstrap.Toast(toast);
        bsToast.show();
        
        toast.addEventListener('hidden.bs.toast', function () {
            toast.remove();
        });
    }

    // Event delegation for widget actions
    document.addEventListener('click', function(e) {
        // Toggle visibility
        if (e.target.closest('.toggle-visibility')) {
            const widgetId = e.target.closest('.toggle-visibility').dataset.widgetId;
            toggleWidgetVisibility(widgetId);
        }
        
        // Toggle collapsed
        if (e.target.closest('.toggle-collapsed')) {
            const widgetId = e.target.closest('.toggle-collapsed').dataset.widgetId;
            toggleWidgetCollapsed(widgetId);
        }
        
        // Delete widget
        if (e.target.closest('.delete-widget')) {
            const widgetId = e.target.closest('.delete-widget').dataset.widgetId;
            deleteWidget(widgetId);
        }
    });

    function toggleWidgetVisibility(widgetId) {
        fetch(`/dashboard/widgets/${widgetId}/toggle-visibility`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const btn = document.querySelector(`.toggle-visibility[data-widget-id="${widgetId}"] i`);
                btn.className = `fas fa-eye${data.is_visible ? '' : '-slash'}`;
                showToast('Widget visibility updated', 'success');
            }
        });
    }

    function toggleWidgetCollapsed(widgetId) {
        fetch(`/dashboard/widgets/${widgetId}/toggle-collapsed`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const btn = document.querySelector(`.toggle-collapsed[data-widget-id="${widgetId}"] i`);
                btn.className = `fas fa-${data.is_collapsed ? 'expand' : 'compress'}`;
                
                const cardBody = document.querySelector(`[data-widget-id="${widgetId}"] .card-body`);
                if (cardBody) {
                    if (data.is_collapsed) {
                        cardBody.classList.add('d-none');
                    } else {
                        cardBody.classList.remove('d-none');
                    }
                }
                showToast('Widget state updated', 'success');
            }
        });
    }

    function deleteWidget(widgetId) {
        if (confirm('Are you sure you want to delete this widget?')) {
            fetch(`/dashboard/widgets/${widgetId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const widgetElement = document.querySelector(`[data-widget-id="${widgetId}"]`);
                    if (widgetElement) {
                        widgetElement.remove();
                    }
                    showToast('Widget deleted successfully', 'success');
                }
            });
        }
    }
</script>
@endsection