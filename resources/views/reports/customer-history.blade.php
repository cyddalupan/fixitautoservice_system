@extends('layouts.app')

@section('title', 'Customer History Report')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('reports.dashboard') }}">Reports</a></li>
                        <li class="breadcrumb-item active">Customer History</li>
                    </ol>
                </div>
                <h4 class="page-title">Customer History Report</h4>
            </div>
        </div>
    </div>

    <!-- Report Filters -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="header-title mb-3">Report Filters</h4>
                    <form method="GET" action="{{ route('reports.customer-history') }}" id="reportFilters">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="customer_id" class="form-label">Customer</label>
                                    <select class="form-select" id="customer_id" name="customer_id">
                                        <option value="">All Customers</option>
                                        @foreach(\App\Models\Customer::orderBy('first_name')->get() as $customer)
                                            <option value="{{ $customer->id }}" {{ ($customer_id ?? '') == $customer->id ? 'selected' : '' }}>
                                                {{ $customer->first_name }} {{ $customer->last_name }}
                                                @if($customer->email)
                                                    ({{ $customer->email }})
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="date_from" class="form-label">Date From</label>
                                    <input type="date" class="form-control" id="date_from" name="date_from" 
                                           value="{{ $filters['date_from'] ?? '' }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="date_to" class="form-label">Date To</label>
                                    <input type="date" class="form-control" id="date_to" name="date_to" 
                                           value="{{ $filters['date_to'] ?? '' }}">
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
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-filter me-1"></i> Apply Filters
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary ms-2" onclick="resetFilters()">
                                        <i class="fas fa-redo me-1"></i> Reset Filters
                                    </button>
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
                            <h5 class="text-muted fw-normal mt-0">Total Customers</h5>
                            <h3 class="my-2">{{ $report['summary']['total_customers'] ?? 0 }}</h3>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-primary rounded-circle">
                                <i class="fas fa-users fs-20"></i>
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
                            <h5 class="text-muted fw-normal mt-0">Total Services</h5>
                            <h3 class="my-2">{{ $report['summary']['total_services'] ?? 0 }}</h3>
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
                            <h5 class="text-muted fw-normal mt-0">Total Revenue</h5>
                            <h3 class="my-2">${{ number_format($report['summary']['total_spent'] ?? 0, 2) }}</h3>
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
                            <h5 class="text-muted fw-normal mt-0">Avg per Customer</h5>
                            <h3 class="my-2">${{ number_format($report['summary']['avg_spent_per_customer'] ?? 0, 2) }}</h3>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-warning rounded-circle">
                                <i class="fas fa-chart-bar fs-20"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Customer List -->
    <div class="row mt-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="header-title mb-3">Customer Service History</h4>
                    
                    @if(isset($report['customers']) && count($report['customers']) > 0)
                        @foreach($report['customers'] as $customer)
                            <div class="card mb-3 border">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <div>
                                            <h5 class="card-title mb-1">
                                                {{ $customer['customer_name'] }}
                                                <span class="badge bg-primary ms-2">{{ $customer['service_count'] }} services</span>
                                            </h5>
                                            <p class="text-muted mb-0">
                                                <i class="fas fa-envelope me-1"></i> {{ $customer['email'] }}
                                                <i class="fas fa-phone ms-3 me-1"></i> {{ $customer['phone'] }}
                                            </p>
                                        </div>
                                        <div class="text-end">
                                            <h4 class="text-success mb-0">${{ number_format($customer['total_spent'], 2) }}</h4>
                                            <small class="text-muted">Total Spent</small>
                                        </div>
                                    </div>
                                    
                                    <div class="row mb-3">
                                        <div class="col-md-3">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm bg-light rounded-circle me-3">
                                                    <i class="fas fa-calendar-alt text-primary fs-18"></i>
                                                </div>
                                                <div>
                                                    <small class="text-muted">First Service</small>
                                                    <p class="mb-0">{{ $customer['first_service'] ? \Carbon\Carbon::parse($customer['first_service'])->format('M d, Y') : 'N/A' }}</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm bg-light rounded-circle me-3">
                                                    <i class="fas fa-calendar-check text-success fs-18"></i>
                                                </div>
                                                <div>
                                                    <small class="text-muted">Last Service</small>
                                                    <p class="mb-0">{{ $customer['last_service'] ? \Carbon\Carbon::parse($customer['last_service'])->format('M d, Y') : 'N/A' }}</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm bg-light rounded-circle me-3">
                                                    <i class="fas fa-dollar-sign text-info fs-18"></i>
                                                </div>
                                                <div>
                                                    <small class="text-muted">Avg Service Cost</small>
                                                    <p class="mb-0">${{ number_format($customer['avg_service_cost'], 2) }}</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm bg-light rounded-circle me-3">
                                                    <i class="fas fa-file-invoice-dollar text-warning fs-18"></i>
                                                </div>
                                                <div>
                                                    <small class="text-muted">Invoices</small>
                                                    <p class="mb-0">{{ $customer['invoice_count'] }} invoices</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Service History Table -->
                                    @if(count($customer['services']) > 0)
                                        <div class="table-responsive">
                                            <table class="table table-sm table-hover mb-0">
                                                <thead>
                                                    <tr>
                                                        <th>Date</th>
                                                        <th>Vehicle</th>
                                                        <th>Service Type</th>
                                                        <th>Description</th>
                                                        <th>Technician</th>
                                                        <th class="text-end">Cost</th>
                                                        <th>Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($customer['services'] as $service)
                                                        <tr>
                                                            <td>{{ \Carbon\Carbon::parse($service['service_date'])->format('M d, Y') }}</td>
                                                            <td>
                                                                <div>{{ $service['vehicle'] }}</div>
                                                                <small class="text-muted">{{ $service['license_plate'] }}</small>
                                                            </td>
                                                            <td>
                                                                <span class="badge bg-light text-dark">
                                                                    {{ ucfirst(str_replace('_', ' ', $service['service_type'])) }}
                                                                </span>
                                                            </td>
                                                            <td>{{ $service['description'] }}</td>
                                                            <td>{{ $service['technician'] }}</td>
                                                            <td class="text-end">${{ number_format($service['cost'], 2) }}</td>
                                                            <td>
                                                                <span class="badge bg-{{ $service['status'] == 'completed' ? 'success' : ($service['status'] == 'in_progress' ? 'warning' : 'primary') }}">
                                                                    {{ ucfirst($service['status']) }}
                                                                </span>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <div class="alert alert-info mb-0">
                                            <i class="fas fa-info-circle me-2"></i> No service records found for this customer.
                                        </div>
                                    @endif
                                    
                                    <!-- Customer Actions -->
                                    <div class="mt-3 d-flex justify-content-end">
                                        <a href="{{ route('customers.show', $customer['customer_id']) }}" class="btn btn-sm btn-outline-primary me-2">
                                            <i class="fas fa-eye me-1"></i> View Customer
                                        </a>
                                        <a href="{{ route('appointments.create', ['customer_id' => $customer['customer_id']]) }}" class="btn btn-sm btn-outline-success me-2">
                                            <i class="fas fa-calendar-plus me-1"></i> Schedule Service
                                        </a>
                                        <button type="button" class="btn btn-sm btn-outline-info" onclick="exportCustomerHistory({{ $customer['customer_id'] }})">
                                            <i class="fas fa-file-export me-1"></i> Export History
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i> No customer data found matching the selected filters.
                        </div>
                    @endif
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
        formData.append('report_type', 'customer_history');
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
    
    function exportCustomerHistory(customerId) {
        const form = document.getElementById('reportFilters');
        const formData = new FormData(form);
        formData.append('report_type', 'customer_history');
        formData.append('format', 'pdf');
        formData.append('customer_id', customerId);
        
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
                alert('Customer history exported successfully! Download will start shortly.');
                console.log('Export data:', data.export);
            } else {
                alert('Error exporting customer history: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while exporting customer history.');
        });
    }
    
    function resetFilters() {
        document.getElementById('customer_id').value = '';
        document.getElementById('date_from').value = '';
        document.getElementById('date_to').value = '';
        document.getElementById('service_type').value = '';
        document.getElementById('reportFilters').submit();
    }
    
    // Auto-submit when customer is selected (for single customer view)
    document.getElementById('customer_id').addEventListener('change', function() {
        if (this.value) {
            // If a specific customer is selected, auto-submit to show their history
            document.getElementById('reportFilters').submit();
        }
    });
</script>
@endpush
@endsection
