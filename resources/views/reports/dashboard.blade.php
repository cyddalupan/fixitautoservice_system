@extends('layouts.app')

@section('title', 'Reports Dashboard')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Reports</li>
                    </ol>
                </div>
                <h4 class="page-title">Reports Dashboard</h4>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="header-title mb-4">Essential Reports</h4>
                    <p class="text-muted">Generate and export essential business reports for daily operations, monthly performance, and customer history.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Daily Activity Report Card -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="avatar-sm bg-primary rounded-circle me-3">
                            <i class="fas fa-calendar-day fs-20 text-white"></i>
                        </div>
                        <div>
                            <h5 class="card-title mb-0">Daily Activity Report</h5>
                            <p class="text-muted mb-0">Today's business summary</p>
                        </div>
                    </div>
                    <p class="card-text">Get a complete overview of today's appointments, work orders, invoices, payments, and new customers.</p>
                    <div class="mt-3">
                        <a href="{{ route('reports.daily-activity') }}" class="btn btn-primary">
                            <i class="fas fa-chart-line me-1"></i> Generate Report
                        </a>
                        <a href="{{ route('reports.daily-activity', ['date' => \Carbon\Carbon::today()->format('Y-m-d')]) }}" class="btn btn-outline-primary ms-1">
                            <i class="fas fa-eye me-1"></i> View Today
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Monthly Performance Report Card -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="avatar-sm bg-success rounded-circle me-3">
                            <i class="fas fa-chart-bar fs-20 text-white"></i>
                        </div>
                        <div>
                            <h5 class="card-title mb-0">Monthly Performance</h5>
                            <p class="text-muted mb-0">Business performance analysis</p>
                        </div>
                    </div>
                    <p class="card-text">Analyze monthly revenue, expenses, profit, customer metrics, and compare with previous months.</p>
                    <div class="mt-3">
                        <a href="{{ route('reports.monthly-performance') }}" class="btn btn-success">
                            <i class="fas fa-chart-pie me-1"></i> Generate Report
                        </a>
                        <a href="{{ route('reports.monthly-performance', ['date' => \Carbon\Carbon::today()->format('Y-m-d')]) }}" class="btn btn-outline-success ms-1">
                            <i class="fas fa-calendar-alt me-1"></i> This Month
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Customer History Report Card -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="avatar-sm bg-info rounded-circle me-3">
                            <i class="fas fa-users fs-20 text-white"></i>
                        </div>
                        <div>
                            <h5 class="card-title mb-0">Customer History</h5>
                            <p class="text-muted mb-0">Complete service records</p>
                        </div>
                    </div>
                    <p class="card-text">View complete service history for customers, including all vehicles, services performed, and spending patterns.</p>
                    <div class="mt-3">
                        <a href="{{ route('reports.customer-history') }}" class="btn btn-info">
                            <i class="fas fa-history me-1"></i> Generate Report
                        </a>
                        <a href="{{ route('reports.customer-history') }}" class="btn btn-outline-info ms-1">
                            <i class="fas fa-search me-1"></i> Search Customer
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="header-title mb-3">Quick Actions</h4>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="d-grid">
                                <a href="{{ route('reports.daily-activity', ['date' => \Carbon\Carbon::yesterday()->format('Y-m-d')]) }}" class="btn btn-outline-primary">
                                    <i class="fas fa-arrow-left me-1"></i> Yesterday's Report
                                </a>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="d-grid">
                                <a href="{{ route('reports.monthly-performance', ['date' => \Carbon\Carbon::today()->subMonth()->format('Y-m-d')]) }}" class="btn btn-outline-success">
                                    <i class="fas fa-calendar-minus me-1"></i> Last Month
                                </a>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="d-grid">
                                <button type="button" class="btn btn-outline-info" data-bs-toggle="modal" data-bs-target="#exportModal">
                                    <i class="fas fa-file-export me-1"></i> Export Settings
                                </button>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="d-grid">
                                <button type="button" class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#scheduleModal">
                                    <i class="fas fa-clock me-1"></i> Schedule Reports
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="header-title mb-3">Recent Reports</h4>
                    <div class="table-responsive">
                        <table class="table table-hover table-centered mb-0">
                            <thead>
                                <tr>
                                    <th>Report Type</th>
                                    <th>Date Range</th>
                                    <th>Generated</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="5" class="text-center text-muted">
                                        <i class="fas fa-info-circle me-1"></i> No recent reports generated yet.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Export Settings Modal -->
<div class="modal fade" id="exportModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Export Settings</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="exportSettingsForm">
                    <div class="mb-3">
                        <label for="reportType" class="form-label">Report Type</label>
                        <select class="form-select" id="reportType" name="report_type">
                            <option value="daily_activity">Daily Activity Report</option>
                            <option value="monthly_performance">Monthly Performance Report</option>
                            <option value="customer_history">Customer History Report</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="exportFormat" class="form-label">Default Export Format</label>
                        <select class="form-select" id="exportFormat" name="format">
                            <option value="pdf">PDF</option>
                            <option value="csv">CSV</option>
                            <option value="excel">Excel</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="saveAsDefault" name="is_default">
                            <label class="form-check-label" for="saveAsDefault">Save as default settings</label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveExportSettings()">Save Settings</button>
            </div>
        </div>
    </div>
</div>

<!-- Schedule Reports Modal -->
<div class="modal fade" id="scheduleModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Schedule Automated Reports</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="scheduleForm">
                    <div class="mb-3">
                        <label for="scheduleReportType" class="form-label">Report Type</label>
                        <select class="form-select" id="scheduleReportType" name="report_type">
                            <option value="daily_activity">Daily Activity Report</option>
                            <option value="monthly_performance">Monthly Performance Report</option>
                            <option value="customer_history">Customer History Report</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="scheduleFrequency" class="form-label">Frequency</label>
                        <select class="form-select" id="scheduleFrequency" name="schedule">
                            <option value="daily">Daily</option>
                            <option value="weekly">Weekly (Monday)</option>
                            <option value="monthly">Monthly (1st of month)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="recipients" class="form-label">Email Recipients</label>
                        <input type="text" class="form-control" id="recipients" name="recipients" placeholder="email1@example.com, email2@example.com">
                        <small class="text-muted">Separate multiple emails with commas</small>
                    </div>
                    <div class="mb-3">
                        <label for="scheduleFormat" class="form-label">Export Format</label>
                        <select class="form-select" id="scheduleFormat" name="format">
                            <option value="pdf">PDF</option>
                            <option value="csv">CSV</option>
                            <option value="excel">Excel</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" onclick="scheduleReport()">Schedule Report</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function saveExportSettings() {
        const form = document.getElementById('exportSettingsForm');
        const formData = new FormData(form);
        
        fetch('{{ route("reports.settings.save") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Settings saved successfully!');
                $('#exportModal').modal('hide');
            } else {
                alert('Error saving settings: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while saving settings.');
        });
    }
    
    function scheduleReport() {
        const form = document.getElementById('scheduleForm');
        const formData = new FormData(form);
        
        fetch('{{ route("reports.schedule") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Report scheduled successfully!');
                $('#scheduleModal').modal('hide');
            } else {
                alert('Error scheduling report: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while scheduling report.');
        });
    }
    
    // Load report types on page load
    document.addEventListener('DOMContentLoaded', function() {
        fetch('{{ route("reports.types") }}')
            .then(response => response.json())
            .then(data => {
                console.log('Report types loaded:', data);
            })
            .catch(error => {
                console.error('Error loading report types:', error);
            });
    });
</script>
@endpush
@endsection