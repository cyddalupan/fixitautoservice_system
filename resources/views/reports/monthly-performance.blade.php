@extends('layouts.app')

@section('title', 'Monthly Performance Report')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('reports.dashboard') }}">Reports</a></li>
                        <li class="breadcrumb-item active">Monthly Performance</li>
                    </ol>
                </div>
                <h4 class="page-title">Monthly Performance Report</h4>
            </div>
        </div>
    </div>

    <!-- Report Filters -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="header-title mb-3">Report Filters</h4>
                    <form method="GET" action="{{ route('reports.monthly-performance') }}" id="reportFilters">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="date" class="form-label">Month</label>
                                    <input type="month" class="form-control" id="date" name="date" 
                                           value="{{ $date ?? \Carbon\Carbon::today()->format('Y-m') }}"
                                           max="{{ \Carbon\Carbon::today()->format('Y-m') }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="service_type" class="form-label">Service Type</label>
                                    <select class="form-select" id="service_type" name="service_type">
                                        <option value="">All Services</option>
                                        <option value="oil_change" {{ ($filters['service_type'] ?? '') == 'oil_change' ? 'selected' : '' }}>Oil Change</option>
                                        <option value="brake_service" {{ ($filters['service_type'] ?? '') == 'brake_service' ? 'selected' : '' }}>Brake Service</option>
                                        <option value="tire_service" {{ ($filters['service_type'] ?? '') == 'tire_service' ? 'selected' : '' }}>Tire Service</option>
                                        <option value="diagnostic" {{ ($filters['service_type'] ?? '') == 'diagnostic' ? 'selected' : '' }}>Diagnostic</option>
                                        <option value="maintenance" {{ ($filters['service_type'] ?? '') == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                                        <option value="repair" {{ ($filters['service_type'] ?? '') == 'repair' ? 'selected' : '' }}>Repair</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3 d-flex align-items-end">
                                    <div class="w-100">
                                        <button type="submit" class="btn btn-primary w-100">
                                            <i class="fas fa-filter me-1"></i> Apply Filters
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Summary -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="header-title mb-3">Performance Summary - {{ $report['metrics']['current_month']['month'] ?? 'Current Month' }}</h4>
                    
                    <div class="row">
                        <!-- Revenue Card -->
                        <div class="col-md-3">
                            <div class="card border-primary border">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h5 class="text-muted mb-0">Revenue</h5>
                                            <h3 class="mt-2">${{ number_format($report['metrics']['current_month']['revenue'] ?? 0, 2) }}</h3>
                                            @if(isset($report['metrics']['comparison']['revenue_change']))
                                                <span class="text-{{ $report['metrics']['comparison']['revenue_change'] >= 0 ? 'success' : 'danger' }}">
                                                    <i class="fas fa-arrow-{{ $report['metrics']['comparison']['revenue_change'] >= 0 ? 'up' : 'down' }} me-1"></i>
                                                    {{ number_format(abs($report['metrics']['comparison']['revenue_change']), 1) }}%
                                                </span>
                                                <small class="text-muted">vs last month</small>
                                            @endif
                                        </div>
                                        <div class="avatar-sm">
                                            <span class="avatar-title bg-primary rounded-circle">
                                                <i class="fas fa-dollar-sign fs-20"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Profit Card -->
                        <div class="col-md-3">
                            <div class="card border-success border">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h5 class="text-muted mb-0">Profit</h5>
                                            <h3 class="mt-2">${{ number_format($report['metrics']['current_month']['profit'] ?? 0, 2) }}</h3>
                                            @if(isset($report['metrics']['comparison']['profit_change']))
                                                <span class="text-{{ $report['metrics']['comparison']['profit_change'] >= 0 ? 'success' : 'danger' }}">
                                                    <i class="fas fa-arrow-{{ $report['metrics']['comparison']['profit_change'] >= 0 ? 'up' : 'down' }} me-1"></i>
                                                    {{ number_format(abs($report['metrics']['comparison']['profit_change']), 1) }}%
                                                </span>
                                                <small class="text-muted">vs last month</small>
                                            @endif
                                        </div>
                                        <div class="avatar-sm">
                                            <span class="avatar-title bg-success rounded-circle">
                                                <i class="fas fa-chart-line fs-20"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Jobs Completed Card -->
                        <div class="col-md-3">
                            <div class="card border-info border">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h5 class="text-muted mb-0">Jobs Completed</h5>
                                            <h3 class="mt-2">{{ $report['metrics']['current_month']['jobs_completed'] ?? 0 }}</h3>
                                            @if(isset($report['metrics']['comparison']['jobs_change']))
                                                <span class="text-{{ $report['metrics']['comparison']['jobs_change'] >= 0 ? 'success' : 'danger' }}">
                                                    <i class="fas fa-arrow-{{ $report['metrics']['comparison']['jobs_change'] >= 0 ? 'up' : 'down' }} me-1"></i>
                                                    {{ number_format(abs($report['metrics']['comparison']['jobs_change']), 1) }}%
                                                </span>
                                                <small class="text-muted">vs last month</small>
                                            @endif
                                        </div>
                                        <div class="avatar-sm">
                                            <span class="avatar-title bg-info rounded-circle">
                                                <i class="fas fa-wrench fs-20"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- New Customers Card -->
                        <div class="col-md-3">
                            <div class="card border-warning border">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h5 class="text-muted mb-0">New Customers</h5>
                                            <h3 class="mt-2">{{ $report['metrics']['current_month']['new_customers'] ?? 0 }}</h3>
                                            @if(isset($report['metrics']['comparison']['customer_growth']))
                                                <span class="text-{{ $report['metrics']['comparison']['customer_growth'] >= 0 ? 'success' : 'danger' }}">
                                                    <i class="fas fa-arrow-{{ $report['metrics']['comparison']['customer_growth'] >= 0 ? 'up' : 'down' }} me-1"></i>
                                                    {{ number_format(abs($report['metrics']['comparison']['customer_growth']), 1) }}%
                                                </span>
                                                <small class="text-muted">vs last month</small>
                                            @endif
                                        </div>
                                        <div class="avatar-sm">
                                            <span class="avatar-title bg-warning rounded-circle">
                                                <i class="fas fa-user-plus fs-20"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Comparison Table -->
    <div class="row mt-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="header-title mb-3">Month-over-Month Comparison</h4>
                    <div class="table-responsive">
                        <table class="table table-hover table-centered mb-0">
                            <thead>
                                <tr>
                                    <th>Metric</th>
                                    <th class="text-end">{{ $report['metrics']['current_month']['month'] ?? 'Current Month' }}</th>
                                    <th class="text-end">{{ $report['metrics']['previous_month']['month'] ?? 'Previous Month' }}</th>
                                    <th class="text-end">Change</th>
                                    <th class="text-end">Change %</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Revenue</td>
                                    <td class="text-end">${{ number_format($report['metrics']['current_month']['revenue'] ?? 0, 2) }}</td>
                                    <td class="text-end">${{ number_format($report['metrics']['previous_month']['revenue'] ?? 0, 2) }}</td>
                                    <td class="text-end">
                                        ${{ number_format(($report['metrics']['current_month']['revenue'] ?? 0) - ($report['metrics']['previous_month']['revenue'] ?? 0), 2) }}
                                    </td>
                                    <td class="text-end">
                                        <span class="text-{{ ($report['metrics']['comparison']['revenue_change'] ?? 0) >= 0 ? 'success' : 'danger' }}">
                                            {{ number_format($report['metrics']['comparison']['revenue_change'] ?? 0, 1) }}%
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Profit</td>
                                    <td class="text-end">${{ number_format($report['metrics']['current_month']['profit'] ?? 0, 2) }}</td>
                                    <td class="text-end">${{ number_format($report['metrics']['previous_month']['profit'] ?? 0, 2) }}</td>
                                    <td class="text-end">
                                        ${{ number_format(($report['metrics']['current_month']['profit'] ?? 0) - ($report['metrics']['previous_month']['profit'] ?? 0), 2) }}
                                    </td>
                                    <td class="text-end">
                                        <span class="text-{{ ($report['metrics']['comparison']['profit_change'] ?? 0) >= 0 ? 'success' : 'danger' }}">
                                            {{ number_format($report['metrics']['comparison']['profit_change'] ?? 0, 1) }}%
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Jobs Completed</td>
                                    <td class="text-end">{{ $report['metrics']['current_month']['jobs_completed'] ?? 0 }}</td>
                                    <td class="text-end">{{ $report['metrics']['previous_month']['jobs_completed'] ?? 0 }}</td>
                                    <td class="text-end">
                                        {{ ($report['metrics']['current_month']['jobs_completed'] ?? 0) - ($report['metrics']['previous_month']['jobs_completed'] ?? 0) }}
                                    </td>
                                    <td class="text-end">
                                        <span class="text-{{ ($report['metrics']['comparison']['jobs_change'] ?? 0) >= 0 ? 'success' : 'danger' }}">
                                            {{ number_format($report['metrics']['comparison']['jobs_change'] ?? 0, 1) }}%
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Avg Job Value</td>
                                    <td class="text-end">${{ number_format($report['metrics']['current_month']['avg_job_value'] ?? 0, 2) }}</td>
                                    <td class="text-end">${{ number_format($report['metrics']['previous_month']['avg_job_value'] ?? 0, 2) }}</td>
                                    <td class="text-end">
                                        ${{ number_format(($report['metrics']['current_month']['avg_job_value'] ?? 0) - ($report['metrics']['previous_month']['avg_job_value'] ?? 0), 2) }}
                                    </td>
                                    <td class="text-end">
                                        @php
                                            $avgJobChange = ($report['metrics']['previous_month']['avg_job_value'] ?? 0) > 0 
                                                ? (($report['metrics']['current_month']['avg_job_value'] ?? 0) - ($report['metrics']['previous_month']['avg_job_value'] ?? 0)) / ($report['metrics']['previous_month']['avg_job_value'] ?? 0) * 100 
                                                : 0;
                                        @endphp
                                        <span class="text-{{ $avgJobChange >= 0 ? 'success' : 'danger' }}">
                                            {{ number_format($avgJobChange, 1) }}%
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>New Customers</td>
                                    <td class="text-end">{{ $report['metrics']['current_month']['new_customers'] ?? 0 }}</td>
                                    <td class="text-end">{{ $report['metrics']['previous_month']['new_customers'] ?? 0 }}</td>
                                    <td class="text-end">
                                        {{ ($report['metrics']['current_month']['new_customers'] ?? 0) - ($report['metrics']['previous_month']['new_customers'] ?? 0) }}
                                    </td>
                                    <td class="text-end">
                                        <span class="text-{{ ($report['metrics']['comparison']['customer_growth'] ?? 0) >= 0 ? 'success' : 'danger' }}">
                                            {{ number_format($report['metrics']['comparison']['customer_growth'] ?? 0, 1) }}%
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Repeat Customers</td>
                                    <td class="text-end">{{ $report['metrics']['current_month']['repeat_customers'] ?? 0 }}</td>
                                    <td class="text-end">{{ $report['metrics']['previous_month']['repeat_customers'] ?? 0 }}</td>
                                    <td class="text-end">
                                        {{ ($report['metrics']['current_month']['repeat_customers'] ?? 0) - ($report['metrics']['previous_month']['repeat_customers'] ?? 0) }}
                                    </td>
                                    <td class="text-end">
                                        @php
                                            $repeatChange = ($report['metrics']['previous_month']['repeat_customers'] ?? 0) > 0 
                                                ? (($report['metrics']['current_month']['repeat_customers'] ?? 0) - ($report['metrics']['previous_month']['repeat_customers'] ?? 0)) / ($report['metrics']['previous_month']['repeat_customers'] ?? 0) * 100 
                                                : 0;
                                        @endphp
                                        <span class="text-{{ $repeatChange >= 0 ? 'success' : 'danger' }}">
                                            {{ number_format($repeatChange, 1) }}%
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Customer Satisfaction</td>
                                    <td class="text-end">{{ number_format($report['metrics']['current_month']['customer_satisfaction'] ?? 0, 1) }}/5</td>
                                    <td class="text-end">{{ number_format($report['metrics']['previous_month']['customer_satisfaction'] ?? 0, 1) }}/5</td>
                                    <td class="text-end">
                                        {{ number_format(($report['metrics']['current_month']['customer_satisfaction'] ?? 0) - ($report['metrics']['previous_month']['customer_satisfaction'] ?? 0), 1) }}
                                    </td>
                                    <td class="text-end">
                                        @php
                                            $satisfactionChange = ($report['metrics']['previous_month']['customer_satisfaction'] ?? 0) > 0 
                                                ? (($report['metrics']['current_month']['customer_satisfaction'] ?? 0) - ($report['metrics']['previous_month']['customer_satisfaction'] ?? 0)) / ($report['metrics']['previous_month']['customer_satisfaction'] ?? 0) * 100 
                                                : 0;
                                        @endphp
                                        <span class="text-{{ $satisfactionChange >= 0 ? 'success' : 'danger' }}">
                                            {{ number_format($satisfactionChange, 1) }}%
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Data Sections -->
    <div class="row mt-3">
        <!-- Top Invoices -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-body">
                    <h4 class="header-title mb-3">Top Invoices This Month</h4>
                    <div class="table-responsive">
                        <table class="table table-hover table-centered mb-0">
                            <thead>
                                <tr>
                                    <th>Invoice #</th>
                                    <th>Customer</th>
                                    <th>Date</th>
                                    <th class="text-end">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(isset($report['details']['invoices']) && count($report['details']['invoices']) > 0)
                                    @php
                                        $topInvoices = $report['details']['invoices']->sortByDesc('total_amount')->take(10);
                                    @endphp
                                    @foreach($topInvoices as $invoice)
                                        <tr>
                                            <td>#{{ $invoice->invoice_number }}</td>
                                            <td>{{ $invoice->customer->first_name ?? 'N/A' }} {{ $invoice->customer->last_name ?? '' }}</td>
                                            <td>{{ \Carbon\Carbon::parse($invoice->invoice_date)->format('M d') }}</td>
                                            <td class="text-end">${{ number_format($invoice->total_amount, 2) }}</td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">No invoices for this month</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Customers -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-body">
                    <h4 class="header-title mb-3">Top Customers This Month</h4>
                    <div class="table-responsive">
                        <table class="table table-hover table-centered mb-0">
                            <thead>
                                <tr>
                                    <th>Customer</th>
                                    <th>Services</th>
                                    <th class="text-end">Total Spent</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(isset($report['details']['invoices']) && count($report['details']['invoices']) > 0)
                                    @php
                                        // Group invoices by customer
                                        $customerTotals = [];
                                        foreach($report['details']['invoices'] as $invoice) {
                                            $customerId = $invoice->customer_id;
                                            if (!isset($customerTotals[$customerId])) {
                                                $customerTotals[$customerId] = [
                                                    'name' => ($invoice->customer->first_name ?? '') . ' ' . ($invoice->customer->last_name ?? ''),
                                                    'total' => 0,
                                                    'count' => 0
                                                ];
                                            }
                                            $customerTotals[$customerId]['total'] += $invoice->total_amount;
                                            $customerTotals[$customerId]['count']++;
                                        }
                                        
                                        // Sort by total spent and take top 10
                                        usort($customerTotals, function($a, $b) {
                                            return $b['total'] <=> $a['total'];
                                        });
                                        $topCustomers = array_slice($customerTotals, 0, 10);
                                    @endphp
                                    @foreach($topCustomers as $customer)
                                        <tr>
                                            <td>{{ $customer['name'] }}</td>
                                            <td>{{ $customer['count'] }} service(s)</td>
                                            <td class="text-end">${{ number_format($customer['total'], 2) }}</td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="3" class="text-center text-muted">No customer data for this month</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="row mt-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0">Export Options</h5>
                            <p class="text-muted mb-0">Export this report in various formats</p>
                        </div>
                        <div class="btn-group">
                            <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-file-export me-1"></i> Export Report
                            </button>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" href="#" onclick="exportReport('pdf')">
                                    <i class="fas fa-file-pdf me-1"></i> Export as PDF
                                </a>
                                <a class="dropdown-item" href="#" onclick="exportReport('csv')">
                                    <i class="fas fa-file-csv me-1"></i> Export as CSV
                                </a>
                                <a class="dropdown-item" href="#" onclick="exportReport('excel')">
                                    <i class="fas fa-file-excel me-1"></i> Export as Excel
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function exportReport(format) {
        const form = document.getElementById('reportFilters');
        const formData = new FormData(form);
        formData.append('report_type', 'monthly_performance');
        formData.append('format', format);
        
        fetch('{{ route("reports.export") }}', {
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
                alert('Report exported successfully! Download will start shortly.');
                // In a real implementation, this would trigger file download
                console.log('Export data:', data.export);
            } else {
                alert('Error exporting report: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while exporting the report.');
        });
    }
    
    // Auto-refresh report when month changes
    document.getElementById('date').addEventListener('change', function() {
        document.getElementById('reportFilters').submit();
    });
</script>
@endpush
@endsection