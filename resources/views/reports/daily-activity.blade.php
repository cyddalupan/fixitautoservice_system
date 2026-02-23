@extends('layouts.app')

@section('title', 'Daily Activity Report')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('reports.dashboard') }}">Reports</a></li>
                        <li class="breadcrumb-item active">Daily Activity</li>
                    </ol>
                </div>
                <h4 class="page-title">Daily Activity Report</h4>
            </div>
        </div>
    </div>

    <!-- Report Filters -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="header-title mb-3">Report Filters</h4>
                    <form method="GET" action="{{ route('reports.daily-activity') }}" id="reportFilters">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="date" class="form-label">Date</label>
                                    <input type="date" class="form-control" id="date" name="date" 
                                           value="{{ $date ?? \Carbon\Carbon::today()->format('Y-m-d') }}"
                                           max="{{ \Carbon\Carbon::today()->format('Y-m-d') }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="technician_id" class="form-label">Technician</label>
                                    <select class="form-select" id="technician_id" name="technician_id">
                                        <option value="">All Technicians</option>
                                        @foreach(\App\Models\User::where('role', 'technician')->get() as $technician)
                                            <option value="{{ $technician->id }}" {{ ($filters['technician_id'] ?? '') == $technician->id ? 'selected' : '' }}>
                                                {{ $technician->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
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
                            <div class="col-md-3">
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

    <!-- Summary Cards -->
    <div class="row">
        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="text-muted fw-normal mt-0">Appointments</h5>
                            <h3 class="my-2">{{ $report['summary']['appointments'] ?? 0 }}</h3>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-primary rounded-circle">
                                <i class="fas fa-calendar-alt fs-20"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="text-muted fw-normal mt-0">Work Orders</h5>
                            <h3 class="my-2">{{ $report['summary']['work_orders'] ?? 0 }}</h3>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-success rounded-circle">
                                <i class="fas fa-wrench fs-20"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="text-muted fw-normal mt-0">Revenue</h5>
                            <h3 class="my-2">${{ number_format($report['summary']['revenue'] ?? 0, 2) }}</h3>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-info rounded-circle">
                                <i class="fas fa-dollar-sign fs-20"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="text-muted fw-normal mt-0">New Customers</h5>
                            <h3 class="my-2">{{ $report['summary']['new_customers'] ?? 0 }}</h3>
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

    <!-- Detailed Sections -->
    <div class="row">
        <!-- Appointments Section -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-body">
                    <h4 class="header-title mb-3">Appointments</h4>
                    <div class="table-responsive">
                        <table class="table table-hover table-centered mb-0">
                            <thead>
                                <tr>
                                    <th>Time</th>
                                    <th>Customer</th>
                                    <th>Vehicle</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(isset($report['details']['appointments']) && count($report['details']['appointments']) > 0)
                                    @foreach($report['details']['appointments'] as $appointment)
                                        <tr>
                                            <td>{{ \Carbon\Carbon::parse($appointment->scheduled_date)->format('h:i A') }}</td>
                                            <td>{{ $appointment->customer->first_name ?? 'N/A' }} {{ $appointment->customer->last_name ?? '' }}</td>
                                            <td>{{ $appointment->vehicle->make ?? 'N/A' }} {{ $appointment->vehicle->model ?? '' }}</td>
                                            <td>
                                                <span class="badge bg-{{ $appointment->status == 'completed' ? 'success' : ($appointment->status == 'in_progress' ? 'warning' : 'primary') }}">
                                                    {{ ucfirst($appointment->status) }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">No appointments for this date</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Work Orders Section -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-body">
                    <h4 class="header-title mb-3">Work Orders</h4>
                    <div class="table-responsive">
                        <table class="table table-hover table-centered mb-0">
                            <thead>
                                <tr>
                                    <th>Order #</th>
                                    <th>Customer</th>
                                    <th>Service</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(isset($report['details']['work_orders']) && count($report['details']['work_orders']) > 0)
                                    @foreach($report['details']['work_orders'] as $workOrder)
                                        <tr>
                                            <td>#{{ $workOrder->id }}</td>
                                            <td>{{ $workOrder->customer->first_name ?? 'N/A' }} {{ $workOrder->customer->last_name ?? '' }}</td>
                                            <td>{{ ucfirst(str_replace('_', ' ', $workOrder->service_type)) }}</td>
                                            <td>
                                                <span class="badge bg-{{ $workOrder->status == 'completed' ? 'success' : ($workOrder->status == 'in_progress' ? 'warning' : 'primary') }}">
                                                    {{ ucfirst($workOrder->status) }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">No work orders for this date</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Invoices & Payments Section -->
    <div class="row mt-3">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-body">
                    <h4 class="header-title mb-3">Invoices</h4>
                    <div class="table-responsive">
                        <table class="table table-hover table-centered mb-0">
                            <thead>
                                <tr>
                                    <th>Invoice #</th>
                                    <th>Customer</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(isset($report['details']['invoices']) && count($report['details']['invoices']) > 0)
                                    @foreach($report['details']['invoices'] as $invoice)
                                        <tr>
                                            <td>#{{ $invoice->invoice_number }}</td>
                                            <td>{{ $invoice->customer->first_name ?? 'N/A' }} {{ $invoice->customer->last_name ?? '' }}</td>
                                            <td>${{ number_format($invoice->total_amount, 2) }}</td>
                                            <td>
                                                <span class="badge bg-{{ $invoice->status == 'paid' ? 'success' : ($invoice->status == 'partial' ? 'warning' : 'danger') }}">
                                                    {{ ucfirst($invoice->status) }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">No invoices for this date</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card">
                <div class="card-body">
                    <h4 class="header-title mb-3">Payments Received</h4>
                    <div class="table-responsive">
                        <table class="table table-hover table-centered mb-0">
                            <thead>
                                <tr>
                                    <th>Payment #</th>
                                    <th>Customer</th>
                                    <th>Amount</th>
                                    <th>Method</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(isset($report['details']['payments']) && count($report['details']['payments']) > 0)
                                    @foreach($report['details']['payments'] as $payment)
                                        <tr>
                                            <td>#{{ $payment->id }}</td>
                                            <td>{{ $payment->customer->first_name ?? 'N/A' }} {{ $payment->customer->last_name ?? '' }}</td>
                                            <td>${{ number_format($payment->amount, 2) }}</td>
                                            <td>{{ ucfirst($payment->payment_method) }}</td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">No payments for this date</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- New Customers Section -->
    <div class="row mt-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="header-title mb-3">New Customers</h4>
                    <div class="table-responsive">
                        <table class="table table-hover table-centered mb-0">
                            <thead>
                                <tr>
                                    <th>Customer</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Registered</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(isset($report['details']['new_customers']) && count($report['details']['new_customers']) > 0)
                                    @foreach($report['details']['new_customers'] as $customer)
                                        <tr>
                                            <td>{{ $customer->first_name }} {{ $customer->last_name }}</td>
                                            <td>{{ $customer->email }}</td>
                                            <td>{{ $customer->phone }}</td>
                                            <td>{{ \Carbon\Carbon::parse($customer->created_at)->format('h:i A') }}</td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">No new customers for this date</td>
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
        formData.append('report_type', 'daily_activity');
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
    
    // Auto-refresh report when date changes
    document.getElementById('date').addEventListener('change', function() {
        document.getElementById('reportFilters').submit();
    });
    
    // Quick navigation buttons
    document.addEventListener('DOMContentLoaded', function() {
        // Add quick date navigation
        const dateInput = document.getElementById('date');
        const today = new Date().toISOString().split('T')[0];
