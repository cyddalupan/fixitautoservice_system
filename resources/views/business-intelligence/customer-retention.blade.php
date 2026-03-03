@extends('layouts.app')

@section('title', 'Customer Retention Analytics')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0">Customer Retention Analytics</h1>
                <div class="btn-group">
                    <button type="button" class="btn btn-outline-primary" onclick="exportRetentionReport()">
                        <i class="fas fa-file-export"></i> Export Report
                    </button>
                    <button type="button" class="btn btn-outline-success" onclick="calculateRetentionMetrics()">
                        <i class="fas fa-calculator"></i> Calculate Metrics
                    </button>
                </div>
            </div>
            <p class="text-muted">Analyze customer retention, churn rates, and loyalty metrics</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-body">
                    <form id="retentionFilterForm">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="periodFilter">Analysis Period</label>
                                    <select class="form-control" id="periodFilter" name="period">
                                        <option value="monthly" selected>Monthly</option>
                                        <option value="quarterly">Quarterly</option>
                                        <option value="yearly">Yearly</option>
                                        <option value="custom">Custom</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="startMonthFilter">Start Month</label>
                                    <input type="month" class="form-control" id="startMonthFilter" name="start_month" value="{{ date('Y-m', strtotime('-12 months')) }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="endMonthFilter">End Month</label>
                                    <input type="month" class="form-control" id="endMonthFilter" name="end_month" value="{{ date('Y-m') }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="customerTypeFilter">Customer Type</label>
                                    <select class="form-control" id="customerTypeFilter" name="customer_type">
                                        <option value="">All Customers</option>
                                        <option value="individual">Individual</option>
                                        <option value="corporate">Corporate</option>
                                        <option value="fleet">Fleet</option>
                                    </select>
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

    <!-- Retention Summary -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Retention Rate
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($summary['retention_rate'], 1) }}%</div>
                            <div class="mt-2">
                                <span class="text-{{ $summary['retention_trend'] >= 0 ? 'success' : 'danger' }}">
                                    <i class="fas fa-arrow-{{ $summary['retention_trend'] >= 0 ? 'up' : 'down' }}"></i>
                                    {{ number_format(abs($summary['retention_trend']), 1) }}%
                                </span>
                                <small class="text-muted">vs previous period</small>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-friends fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Churn Rate
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($summary['churn_rate'], 1) }}%</div>
                            <div class="mt-2">
                                <span class="text-{{ $summary['churn_trend'] <= 0 ? 'success' : 'danger' }}">
                                    <i class="fas fa-arrow-{{ $summary['churn_trend'] <= 0 ? 'down' : 'up' }}"></i>
                                    {{ number_format(abs($summary['churn_trend']), 1) }}%
                                </span>
                                <small class="text-muted">vs previous period</small>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-slash fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Avg. Lifetime Value
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">₱{{ number_format($summary['avg_lifetime_value'], 2) }}</div>
                            <div class="mt-2">
                                <span class="text-{{ $summary['lifetime_value_trend'] >= 0 ? 'success' : 'danger' }}">
                                    <i class="fas fa-arrow-{{ $summary['lifetime_value_trend'] >= 0 ? 'up' : 'down' }}"></i>
                                    {{ number_format(abs($summary['lifetime_value_trend']), 1) }}%
                                </span>
                                <small class="text-muted">vs previous period</small>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Repeat Purchase Rate
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($summary['repeat_purchase_rate'], 1) }}%</div>
                            <div class="mt-2">
                                <span class="text-{{ $summary['repeat_purchase_trend'] >= 0 ? 'success' : 'danger' }}">
                                    <i class="fas fa-arrow-{{ $summary['repeat_purchase_trend'] >= 0 ? 'up' : 'down' }}"></i>
                                    {{ number_format(abs($summary['repeat_purchase_trend']), 1) }}%
                                </span>
                                <small class="text-muted">vs previous period</small>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-redo fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Retention Charts -->
    <div class="row mb-4">
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Retention Rate Trend</h6>
                    <div class="dropdown no-arrow">
                        <a class="dropdown-toggle" href="#" role="button" id="retentionChartMenu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in" aria-labelledby="retentionChartMenu">
                            <a class="dropdown-item" href="#" onclick="changeChartType('retention', 'line')">Line Chart</a>
                            <a class="dropdown-item" href="#" onclick="changeChartType('retention', 'bar')">Bar Chart</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="retentionChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Customer Status Distribution</h6>
                </div>
                <div class="card-body">
                    <div class="chart-pie pt-4">
                        <canvas id="customerStatusChart"></canvas>
                    </div>
                    <div class="mt-4 text-center small">
                        <span class="mr-2">
                            <i class="fas fa-circle text-success"></i> Active
                        </span>
                        <span class="mr-2">
                            <i class="fas fa-circle text-warning"></i> At Risk
                        </span>
                        <span class="mr-2">
                            <i class="fas fa-circle text-danger"></i> Lapsed
                        </span>
                        <span class="mr-2">
                            <i class="fas fa-circle text-secondary"></i> Lost
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Customer Segments -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Customer Segments by Retention Score</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="customerSegmentsTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Segment</th>
                                    <th>Customers</th>
                                    <th>Avg. Retention Score</th>
                                    <th>Avg. Lifetime Value</th>
                                    <th>Churn Risk</th>
                                    <th>Recommended Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($segments as $segment)
                                <tr>
                                    <td>
                                        <span class="badge badge-{{ $segment['badge_color'] }}">{{ $segment['name'] }}</span>
                                    </td>
                                    <td class="font-weight-bold">{{ $segment['customer_count'] }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="progress flex-grow-1 mr-2" style="height: 20px;">
                                                <div class="progress-bar bg-{{ $segment['score_color'] }}" 
                                                     role="progressbar" 
                                                     style="width: {{ $segment['avg_score'] }}%"
                                                     aria-valuenow="{{ $segment['avg_score'] }}" 
                                                     aria-valuemin="0" 
                                                     aria-valuemax="100">
                                                </div>
                                            </div>
                                            <span class="font-weight-bold">{{ number_format($segment['avg_score'], 1) }}</span>
                                        </div>
                                    </td>
                                    <td class="font-weight-bold">₱{{ number_format($segment['avg_lifetime_value'], 2) }}</td>
                                    <td>
                                        <span class="badge badge-{{ $segment['risk_color'] }}">
                                            {{ ucfirst($segment['churn_risk']) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="small">
                                            @foreach($segment['recommended_actions'] as $action)
                                            <div class="mb-1">
                                                <i class="fas fa-{{ $action['icon'] }} text-{{ $action['color'] }} mr-1"></i>
                                                {{ $action['text'] }}
                                            </div>
                                            @endforeach
                                        </div>
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

    <!-- At-Risk Customers -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">At-Risk Customers</h6>
                    <div class="dropdown no-arrow">
                        <a class="dropdown-toggle" href="#" role="button" id="atRiskMenu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in" aria-labelledby="atRiskMenu">
                            <a class="dropdown-item" href="#" onclick="exportAtRiskCustomers()">Export List</a>
                            <a class="dropdown-item" href="#" onclick="sendRetentionCampaign()">Send Campaign</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="atRiskCustomersTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Customer</th>
                                    <th>Last Visit</th>
                                    <th>Total Spent</th>
                                    <th>Retention Score</th>
                                    <th>Churn Probability</th>
                                    <th>Risk Level</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($atRiskCustomers as $customer)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="mr-3">
                                                <div class="icon-circle bg-warning">
                                                    <i class="fas fa-user text-white"></i>
                                                </div>
                                            </div>
                                            <div>
                                                <div class="font-weight-bold">{{ $customer['name'] }}</div>
                                                <div class="text-muted small">{{ $customer['email'] }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $customer['last_visit'] }}</td>
                                    <td class="font-weight-bold">₱{{ number_format($customer['total_spent'], 2) }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="progress flex-grow-1 mr-2" style="height: 20px;">
                                                <div class="progress-bar bg-{{ $customer['score_color'] }}" 
                                                     role="progressbar" 
                                                     style="width: {{ $customer['retention_score'] }}%"
                                                     aria-valuenow="{{ $customer['retention_score'] }}" 
                                                     aria-valuemin="0" 
                                                     aria-valuemax="100">
                                                </div>
                                            </div>
                                            <span class="font-weight-bold">{{ number_format($customer['retention_score'], 1) }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $customer['probability_color'] }}">
                                            {{ number_format($customer['churn_probability'], 1) }}%
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $customer['risk_badge_color'] }}">
                                            {{ ucfirst($customer['risk_level']) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-outline-info" onclick="viewCustomerDetails({{ $customer['id'] }})" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-warning" onclick="sendRetentionOffer({{ $customer['id'] }})" title="Send Offer">
                                                <i class="fas fa-gift"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-primary" onclick="scheduleFollowup({{ $customer['id'] }})" title="Schedule Follow-up">
                                                <i class="fas fa-calendar-alt"></i>
                                            </button>
                                        </div>
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

<!-- Customer Details Modal -->
<div class="modal fade" id="customerDetailsModal" tabindex="-1" role="dialog" aria-labelledby="customerDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="customerDetailsModalLabel">Customer Retention Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="customerDetailsContent">
                <!-- Details will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="printCustomerReport()">Print Report</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Retention Chart
    var retentionChartCtx = document.getElementById('retentionChart').getContext('2d');
    var retentionChart = new Chart(retentionChartCtx, {
        type: 'line',
        data: {
            labels: @json($retentionTrend['labels']),
            datasets: [{
                label: 'Retention Rate',
                data: @json($retentionTrend['data']),
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
                            return context.parsed.y.toFixed(1) + '%';
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
                    max: 100,
                    ticks: {
                        callback: function(value) {
                            return value + '%';
                        }
                    }
                }
            }
        }
    });

    // Customer Status Chart
    var customerStatusChartCtx = document.getElementById('customerStatusChart').getContext('2d');
    var customerStatusChart = new Chart(customerStatusChartCtx, {
        type: 'pie',
        data: {
            labels: ['Active', 'At Risk', 'Lapsed', 'Lost'],
            datasets: [{
                data: @json($customerStatusDistribution),
                backgroundColor: ['#28a745', '#ffc107', '#dc3545', '#6c757d'],
                hoverBackgroundColor: ['#218838', '#e0a800', '#c82333', '#545b62']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.label || '';
                            if (label) {
                                label += ': ';
                            }
                            label += context.parsed + ' customers';
                            return label;
                        }
                    }
                }
            }
        }
    });

    // Filter Form Submission
    document.getElementById('retentionFilterForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const params = new URLSearchParams(formData).toString();
        window.location.href = '{{ route("business-intelligence.customer-retention") }}?' + params;
    });

    function resetFilters() {
        window.location.href = '{{ route("business-intelligence.customer-retention") }}';
    }

    function changeChartType(chartName, type) {
        if (chartName === 'retention') {
            retentionChart.config.type = type;
            retentionChart.update();
        }
    }

    function exportRetentionReport() {
        const formData = new FormData(document.getElementById('retentionFilterForm'));
        const params = new URLSearchParams(formData).toString();
        
        fetch('{{ route("analytics.export") }}?' + params + '&format=csv&type=customer-retention', {
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
            a.download = `customer_retention_report_{{ date('Y-m-d') }}.csv`;
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

    function calculateRetentionMetrics() {
        if (confirm('Are you sure you want to calculate retention metrics? This may take a moment.')) {
            fetch('{{ route("analytics.generate-retention-metrics") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.message) {
                    alert('Retention metrics calculation started successfully. The page will refresh in a moment.');
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to calculate retention metrics');
            });
        }
    }

    function viewCustomerDetails(customerId) {
        fetch(`/business-intelligence/customer-retention/${customerId}/details`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const modalContent = document.getElementById('customerDetailsContent');
                modalContent.innerHTML = `
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Customer Information</h6>
                            <table class="table table-sm">
                                <tr>
                                    <th>Name:</th>
                                    <td>${data.customer.name}</td>
                                </tr>
                                <tr>
                                    <th>Email:</th>
                                    <td>${data.customer.email}</td>
                                </tr>
                                <tr>
                                    <th>Phone:</th>
                                    <td>${data.customer.phone || 'N/A'}</td>
                                </tr>
                                <tr>
                                    <th>Customer Since:</th>
                                    <td>${data.customer.customer_since}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6>Retention Metrics</h6>
                            <table class="table table-sm">
                                <tr>
                                    <th>Retention Score:</th>
                                    <td class="font-weight-bold">${data.customer.retention_score.toFixed(1)}</td>
                                </tr>
                                <tr>
                                    <th>Churn Probability:</th>
                                    <td>${data.customer.churn_probability.toFixed(1)}%</td>
                                </tr>
                                <tr>
                                    <th>Total Spent:</th>
                                    <td class="font-weight-bold">₱${data.customer.total_spent.toFixed(2)}</td>
                                </tr>
                                <tr>
                                    <th>Last Visit:</th>
                                    <td>${data.customer.last_visit}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-12">
                            <h6>Service History</h6>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Service</th>
                                            <th>Vehicle</th>
                                            <th>Amount</th>
                                            <th>Rating</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${data.serviceHistory.map(service => `
                                            <tr>
                                                <td>${service.date}</td>
                                                <td>${service.service}</td>
                                                <td>${service.vehicle}</td>
                                                <td>₱${service.amount.toFixed(2)}</td>
                                                <td>${service.rating ? service.rating + '/5' : 'N/A'}</td>
                                            </tr>
                                        `).join('')}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                `;
                $('#customerDetailsModal').modal('show');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to load customer details');
        });
    }

    function sendRetentionOffer(customerId) {
        if (confirm('Send a retention offer to this customer?')) {
            fetch(`/business-intelligence/customer-retention/${customerId}/send-offer`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Retention offer sent successfully!');
                } else {
                    alert('Failed to send retention offer: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to send retention offer');
            });
        }
    }

    function scheduleFollowup(customerId) {
        const followupDate = prompt('Enter follow-up date (YYYY-MM-DD):', '{{ date("Y-m-d", strtotime("+7 days")) }}');
        if (followupDate) {
            fetch(`/business-intelligence/customer-retention/${customerId}/schedule-followup`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ followup_date: followupDate })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Follow-up scheduled successfully!');
                } else {
                    alert('Failed to schedule follow-up: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to schedule follow-up');
            });
        }
    }

    function exportAtRiskCustomers() {
        const table = document.getElementById('atRiskCustomersTable');
        const rows = table.querySelectorAll('tr');
        const csv = [];
        
        for (let i = 0; i < rows.length; i++) {
            const row = [], cols = rows[i].querySelectorAll('td, th');
            
            for (let j = 0; j < cols.length; j++) {
                let data = cols[j].innerText.replace(/(\r\n|\n|\r)/gm, '').replace(/(\s\s)/gm, ' ');
                data = data.replace(/"/g, '""');
                row.push('"' + data + '"');
            }
            
            csv.push(row.join(','));
        }
        
        const csvString = csv.join('\n');
        const blob = new Blob([csvString], { type: 'text/csv' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'at_risk_customers.csv';
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
        document.body.removeChild(a);
    }

    function sendRetentionCampaign() {
        if (confirm('Send retention campaign to all at-risk customers?')) {
            fetch('{{ route("analytics.send-retention-campaign") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Retention campaign sent to ' + data.sent_count + ' customers!');
                } else {
                    alert('Failed to send retention campaign: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to send retention campaign');
            });
        }
    }

    function printCustomerReport() {
        window.print();
    }
</script>
@endsection