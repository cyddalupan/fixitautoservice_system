@extends('layouts.app')

@section('title', 'Technician Performance Analytics')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0">Technician Performance Analytics</h1>
                <div class="btn-group">
                    <button type="button" class="btn btn-outline-primary" onclick="exportTechnicianReport()">
                        <i class="fas fa-file-export"></i> Export Report
                    </button>
                    <button type="button" class="btn btn-outline-success" onclick="refreshPerformanceData()">
                        <i class="fas fa-sync-alt"></i> Refresh Data
                    </button>
                </div>
            </div>
            <p class="text-muted">Monitor and analyze technician productivity, efficiency, and quality metrics</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-body">
                    <form id="performanceFilterForm">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="dateRangeFilter">Date Range</label>
                                    <select class="form-control" id="dateRangeFilter" name="date_range">
                                        <option value="7">Last 7 Days</option>
                                        <option value="30" selected>Last 30 Days</option>
                                        <option value="90">Last 90 Days</option>
                                        <option value="custom">Custom Range</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="startDateFilter">Start Date</label>
                                    <input type="date" class="form-control" id="startDateFilter" name="start_date" value="{{ date('Y-m-d', strtotime('-30 days')) }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="endDateFilter">End Date</label>
                                    <input type="date" class="form-control" id="endDateFilter" name="end_date" value="{{ date('Y-m-d') }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="departmentFilter">Department</label>
                                    <select class="form-control" id="departmentFilter" name="department">
                                        <option value="">All Departments</option>
                                        @foreach($departments as $dept)
                                        <option value="{{ $dept }}">{{ ucfirst($dept) }}</option>
                                        @endforeach
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

    <!-- Performance Summary -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Avg. Efficiency
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($summary['avg_efficiency'], 2) }}</div>
                            <div class="mt-2">
                                <span class="text-{{ $summary['efficiency_trend'] >= 0 ? 'success' : 'danger' }}">
                                    <i class="fas fa-arrow-{{ $summary['efficiency_trend'] >= 0 ? 'up' : 'down' }}"></i>
                                    {{ number_format(abs($summary['efficiency_trend']), 2) }}%
                                </span>
                                <small class="text-muted">vs previous period</small>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-tachometer-alt fa-2x text-gray-300"></i>
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
                                Avg. Rating
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($summary['avg_rating'], 1) }}/5</div>
                            <div class="mt-2">
                                <span class="text-{{ $summary['rating_trend'] >= 0 ? 'success' : 'danger' }}">
                                    <i class="fas fa-arrow-{{ $summary['rating_trend'] >= 0 ? 'up' : 'down' }}"></i>
                                    {{ number_format(abs($summary['rating_trend']), 2) }}%
                                </span>
                                <small class="text-muted">vs previous period</small>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-star fa-2x text-gray-300"></i>
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
                                Total Jobs
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($summary['total_jobs']) }}</div>
                            <div class="mt-2">
                                <span class="text-{{ $summary['jobs_trend'] >= 0 ? 'success' : 'danger' }}">
                                    <i class="fas fa-arrow-{{ $summary['jobs_trend'] >= 0 ? 'up' : 'down' }}"></i>
                                    {{ number_format(abs($summary['jobs_trend']), 2) }}%
                                </span>
                                <small class="text-muted">vs previous period</small>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-wrench fa-2x text-gray-300"></i>
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
                                Revenue Generated
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">₱{{ number_format($summary['total_revenue'], 2) }}</div>
                            <div class="mt-2">
                                <span class="text-{{ $summary['revenue_trend'] >= 0 ? 'success' : 'danger' }}">
                                    <i class="fas fa-arrow-{{ $summary['revenue_trend'] >= 0 ? 'up' : 'down' }}"></i>
                                    {{ number_format(abs($summary['revenue_trend']), 2) }}%
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
    </div>

    <!-- Performance Charts -->
    <div class="row mb-4">
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Efficiency Trend</h6>
                    <div class="dropdown no-arrow">
                        <a class="dropdown-toggle" href="#" role="button" id="efficiencyChartMenu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in" aria-labelledby="efficiencyChartMenu">
                            <a class="dropdown-item" href="#" onclick="changeChartType('efficiency', 'line')">Line Chart</a>
                            <a class="dropdown-item" href="#" onclick="changeChartType('efficiency', 'bar')">Bar Chart</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="efficiencyChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Performance Distribution</h6>
                </div>
                <div class="card-body">
                    <div class="chart-pie pt-4">
                        <canvas id="performanceDistributionChart"></canvas>
                    </div>
                    <div class="mt-4 text-center small">
                        <span class="mr-2">
                            <i class="fas fa-circle text-success"></i> High (≥2.0)
                        </span>
                        <span class="mr-2">
                            <i class="fas fa-circle text-warning"></i> Medium (1.0-1.99)
                        </span>
                        <span class="mr-2">
                            <i class="fas fa-circle text-danger"></i> Low (<1.0)
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Technician Performance Table -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Technician Performance Details</h6>
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
                        <table class="table table-bordered" id="technicianPerformanceTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Technician</th>
                                    <th>Department</th>
                                    <th>Completed Jobs</th>
                                    <th>Total Hours</th>
                                    <th>Efficiency</th>
                                    <th>Avg. Rating</th>
                                    <th>Revenue</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($technicians as $tech)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="mr-3">
                                                <div class="icon-circle bg-primary">
                                                    <i class="fas fa-user text-white"></i>
                                                </div>
                                            </div>
                                            <div>
                                                <div class="font-weight-bold">{{ $tech['name'] }}</div>
                                                <div class="text-muted small">{{ $tech['email'] }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-secondary">{{ $tech['department'] }}</span>
                                    </td>
                                    <td class="font-weight-bold">{{ $tech['completed_jobs'] }}</td>
                                    <td>{{ number_format($tech['total_hours'], 1) }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="progress flex-grow-1 mr-2" style="height: 20px;">
                                                <div class="progress-bar bg-{{ $tech['efficiency'] >= 2 ? 'success' : ($tech['efficiency'] >= 1 ? 'warning' : 'danger') }}" 
                                                     role="progressbar" 
                                                     style="width: {{ min($tech['efficiency'] * 50, 100) }}%"
                                                     aria-valuenow="{{ $tech['efficiency'] }}" 
                                                     aria-valuemin="0" 
                                                     aria-valuemax="4">
                                                </div>
                                            </div>
                                            <span class="font-weight-bold">{{ number_format($tech['efficiency'], 2) }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        @if($tech['avg_rating'] > 0)
                                            <div class="star-rating">
                                                @for($i = 1; $i <= 5; $i++)
                                                    <i class="fas fa-star{{ $i <= $tech['avg_rating'] ? ' text-warning' : ' text-gray-300' }}"></i>
                                                @endfor
                                                <small class="text-muted ml-1">{{ number_format($tech['avg_rating'], 1) }}</small>
                                            </div>
                                        @else
                                            <span class="text-muted">No ratings</span>
                                        @endif
                                    </td>
                                    <td class="font-weight-bold">₱{{ number_format($tech['revenue'], 2) }}</td>
                                    <td>
                                        <span class="badge badge-{{ $tech['efficiency'] >= 2 ? 'success' : ($tech['efficiency'] >= 1 ? 'warning' : 'danger') }}">
                                            {{ $tech['efficiency'] >= 2 ? 'High' : ($tech['efficiency'] >= 1 ? 'Medium' : 'Low') }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-outline-info" onclick="viewTechnicianDetails({{ $tech['id'] }})" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-primary" onclick="generateReport({{ $tech['id'] }})" title="Generate Report">
                                                <i class="fas fa-chart-bar"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="2" class="text-right">Totals:</th>
                                    <th>{{ number_format(array_sum(array_column($technicians, 'completed_jobs'))) }}</th>
                                    <th>{{ number_format(array_sum(array_column($technicians, 'total_hours')), 1) }}</th>
                                    <th>{{ number_format($summary['avg_efficiency'], 2) }}</th>
                                    <th>{{ number_format($summary['avg_rating'], 1) }}/5</th>
                                    <th>₱{{ number_format(array_sum(array_column($technicians, 'revenue')), 2) }}</th>
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

<!-- Technician Details Modal -->
<div class="modal fade" id="technicianDetailsModal" tabindex="-1" role="dialog" aria-labelledby="technicianDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="technicianDetailsModalLabel">Technician Performance Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="technicianDetailsContent">
                <!-- Details will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="printTechnicianReport()">Print Report</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Efficiency Chart
    var efficiencyChartCtx = document.getElementById('efficiencyChart').getContext('2d');
    var efficiencyChart = new Chart(efficiencyChartCtx, {
        type: 'line',
        data: {
            labels: @json($efficiencyTrend['labels']),
            datasets: [{
                label: 'Average Efficiency',
                data: @json($efficiencyTrend['data']),
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
                    intersect: false
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
                            return value.toFixed(2);
                        }
                    }
                }
            }
        }
    });

    // Performance Distribution Chart
    var distributionChartCtx = document.getElementById('performanceDistributionChart').getContext('2d');
    var distributionChart = new Chart(distributionChartCtx, {
        type: 'pie',
        data: {
            labels: ['High Performance', 'Medium Performance', 'Low Performance'],
            datasets: [{
                data: @json($performanceDistribution),
                backgroundColor: ['#28a745', '#ffc107', '#dc3545'],
                hoverBackgroundColor: ['#218838', '#e0a800', '#c82333']
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
                            label += context.parsed + ' technicians';
                            return label;
                        }
                    }
                }
            }
        }
    });

    // Filter Form Submission
    document.getElementById('performanceFilterForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const params = new URLSearchParams(formData).toString();
        window.location.href = '{{ route("business-intelligence.technician-performance") }}?' + params;
    });

    function resetFilters() {
        window.location.href = '{{ route("business-intelligence.technician-performance") }}';
    }

    function changeChartType(chartName, type) {
        if (chartName === 'efficiency') {
            efficiencyChart.config.type = type;
            efficiencyChart.update();
        }
    }

    function exportTechnicianReport() {
        const formData = new FormData(document.getElementById('performanceFilterForm'));
        const params = new URLSearchParams(formData).toString();
        
        fetch('{{ route("analytics.export") }}?' + params + '&format=csv&type=technician-performance', {
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
            a.download = `technician_performance_report_{{ date('Y-m-d') }}.csv`;
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

    function refreshPerformanceData() {
        fetch('{{ route("analytics.generate-technician-performance") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.message) {
                alert('Performance data refreshed successfully. The page will reload.');
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to refresh performance data');
        });
    }

    function viewTechnicianDetails(technicianId) {
        fetch(`/business-intelligence/technician-performance/${technicianId}/details`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const modalContent = document.getElementById('technicianDetailsContent');
                modalContent.innerHTML = `
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Technician Information</h6>
                            <table class="table table-sm">
                                <tr>
                                    <th>Name:</th>
                                    <td>${data.technician.name}</td>
                                </tr>
                                <tr>
                                    <th>Department:</th>
                                    <td><span class="badge badge-secondary">${data.technician.department}</span></td>
                                </tr>
                                <tr>
                                    <th>Email:</th>
                                    <td>${data.technician.email}</td>
                                </tr>
                                <tr>
                                    <th>Phone:</th>
                                    <td>${data.technician.phone || 'N/A'}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6>Performance Summary</h6>
                            <table class="table table-sm">
                                <tr>
                                    <th>Efficiency:</th>
                                    <td class="font-weight-bold">${data.technician.efficiency.toFixed(2)}</td>
                                </tr>
                                <tr>
                                    <th>Rating:</th>
                                    <td>${data.technician.avg_rating.toFixed(1)}/5</td>
                                </tr>
                                <tr>
                                    <th>Jobs Completed:</th>
                                    <td>${data.technician.completed_jobs}</td>
                                </tr>
                                <tr>
                                    <th>Revenue:</th>
                                    <td class="font-weight-bold">$${data.technician.revenue.toFixed(2)}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-12">
                            <h6>Recent Jobs</h6>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Job ID</th>
                                            <th>Date</th>
                                            <th>Vehicle</th>
                                            <th>Service</th>
                                            <th>Hours</th>
                                            <th>Rating</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${data.recentJobs.map(job => `
                                            <tr>
                                                <td>${job.job_id}</td>
                                                <td>${job.date}</td>
                                                <td>${job.vehicle}</td>
                                                <td>${job.service}</td>
                                                <td>${job.hours}</td>
                                                <td>${job.rating ? job.rating + '/5' : 'N/A'}</td>
                                            </tr>
                                        `).join('')}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                `;
                $('#technicianDetailsModal').modal('show');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to load technician details');
        });
    }

    function generateReport(technicianId) {
        fetch(`/business-intelligence/technician-performance/${technicianId}/report`, {
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
            a.download = `technician_report_${technicianId}_{{ date('Y-m-d') }}.pdf`;
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

    function printTechnicianReport() {
        window.print();
    }

    function exportTableToCSV() {
        const table = document.getElementById('technicianPerformanceTable');
        const rows = table.querySelectorAll('tr');
        const csv = [];
        
        for (let i = 0; i < rows.length; i++) {
            const row = [], cols = rows[i].querySelectorAll('td, th');
            
            for (let j = 0; j < cols.length; j++) {
                // Clean inner text
                let data = cols[j].innerText.replace(/(\r\n|\n|\r)/gm, '').replace(/(\s\s)/gm, ' ');
                // Escape double quotes
                data = data.replace(/"/g, '""');
                // Wrap in double quotes if contains comma
                row.push('"' + data + '"');
            }
            
            csv.push(row.join(','));
        }
        
        const csvString = csv.join('\n');
        const blob = new Blob([csvString], { type: 'text/csv' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'technician_performance.csv';
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
        document.body.removeChild(a);
    }

    function printTable() {
        const printWindow = window.open('', '_blank');
        printWindow.document.write(`
            <html>
                <head>
                    <title>Technician Performance Report</title>
                    <style>
                        body { font-family: Arial, sans-serif; }
                        table { border-collapse: collapse; width: 100%; }
                        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                        th { background-color: #f2f2f2; }
                        @media print {
                            @page { size: landscape; }
                        }
                    </style>
                </head>
                <body>
                    <h2>Technician Performance Report</h2>
                    <p>Generated: ${new Date().toLocaleString()}</p>
                    ${document.getElementById('technicianPerformanceTable').outerHTML}
                </body>
            </html>
        `);
        printWindow.document.close();
        printWindow.print();
    }
</script>
@endsection