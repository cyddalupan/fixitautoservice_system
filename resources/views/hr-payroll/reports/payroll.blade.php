@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">Payroll Report</h1>
                    <p class="text-muted">Detailed payroll analysis and summary</p>
                </div>
                <div>
                    <a href="{{ route('hr-payroll.reports') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Reports
                    </a>
                    <button type="button" class="btn btn-success" onclick="printReport()">
                        <i class="fas fa-print"></i> Print Report
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Report Filters -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-body">
                    <form method="GET" action="{{ route('hr-payroll.reports.payroll') }}">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="report_type">Report Type</label>
                                    <select name="report_type" id="report_type" class="form-control">
                                        <option value="summary" {{ request('report_type', 'summary') == 'summary' ? 'selected' : '' }}>Summary</option>
                                        <option value="detailed" {{ request('report_type') == 'detailed' ? 'selected' : '' }}>Detailed</option>
                                        <option value="tax" {{ request('report_type') == 'tax' ? 'selected' : '' }}>Tax Report</option>
                                        <option value="deduction" {{ request('report_type') == 'deduction' ? 'selected' : '' }}>Deduction Report</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="report_period">Report Period</label>
                                    <select name="period" id="report_period" class="form-control">
                                        <option value="current_month" {{ request('period') == 'current_month' ? 'selected' : '' }}>Current Month</option>
                                        <option value="last_month" {{ request('period') == 'last_month' ? 'selected' : '' }}>Last Month</option>
                                        <option value="current_quarter" {{ request('period') == 'current_quarter' ? 'selected' : '' }}>Current Quarter</option>
                                        <option value="last_quarter" {{ request('period') == 'last_quarter' ? 'selected' : '' }}>Last Quarter</option>
                                        <option value="current_year" {{ request('period') == 'current_year' ? 'selected' : '' }}>Current Year</option>
                                        <option value="custom" {{ request('period') == 'custom' ? 'selected' : '' }}>Custom Range</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="start_date">Start Date</label>
                                    <input type="date" name="start_date" id="start_date" class="form-control" 
                                           value="{{ request('start_date', date('Y-m-01')) }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="end_date">End Date</label>
                                    <input type="date" name="end_date" id="end_date" class="form-control" 
                                           value="{{ request('end_date', date('Y-m-t')) }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="department">Department</label>
                                    <select name="department" id="department" class="form-control">
                                        <option value="">All Departments</option>
                                        <option value="service" {{ request('department') == 'service' ? 'selected' : '' }}>Service</option>
                                        <option value="sales" {{ request('department') == 'sales' ? 'selected' : '' }}>Sales</option>
                                        <option value="administration" {{ request('department') == 'administration' ? 'selected' : '' }}>Administration</option>
                                        <option value="management" {{ request('department') == 'management' ? 'selected' : '' }}>Management</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-12 text-right">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-filter"></i> Generate Report
                                </button>
                                <button type="button" class="btn btn-info" onclick="exportReport()">
                                    <i class="fas fa-download"></i> Export Data
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Report Summary -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Gross Pay
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                ₱{{ number_format($reportData['total_gross'] ?? 0, 2) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-peso-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Total Net Pay
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                ₱{{ number_format($reportData['total_net'] ?? 0, 2) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-money-bill-wave fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Total Deductions
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                ₱{{ number_format($reportData['total_deductions'] ?? 0, 2) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-percentage fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Employees Paid
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $reportData['employee_count'] ?? 0 }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Report -->
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h6 class="m-0 font-weight-bold">Payroll Details</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" id="payrollReportTable">
                            <thead class="thead-light">
                                <tr>
                                    <th>Employee</th>
                                    <th>Department</th>
                                    <th>Period</th>
                                    <th>Regular Hours</th>
                                    <th>Overtime Hours</th>
                                    <th>Gross Pay</th>
                                    <th>Taxes</th>
                                    <th>Deductions</th>
                                    <th>Net Pay</th>
                                    <th>Payment Method</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(isset($reportData['details']) && count($reportData['details']) > 0)
                                    @foreach($reportData['details'] as $detail)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="mr-3">
                                                        <img src="{{ asset('images/default-avatar.png') }}" 
                                                             alt="Employee" 
                                                             class="rounded-circle" 
                                                             width="30" 
                                                             height="30">
                                                    </div>
                                                    <div>
                                                        {{ $detail['employee_name'] ?? 'Unknown' }}
                                                        <div class="text-muted small">{{ $detail['employee_id'] ?? '' }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>{{ $detail['department'] ?? 'N/A' }}</td>
                                            <td>{{ $detail['period'] ?? 'N/A' }}</td>
                                            <td class="text-center">{{ $detail['regular_hours'] ?? 0 }}</td>
                                            <td class="text-center">{{ $detail['overtime_hours'] ?? 0 }}</td>
                                            <td class="text-right">₱{{ number_format($detail['gross_pay'] ?? 0, 2) }}</td>
                                            <td class="text-right">₱{{ number_format($detail['taxes'] ?? 0, 2) }}</td>
                                            <td class="text-right">₱{{ number_format($detail['deductions'] ?? 0, 2) }}</td>
                                            <td class="text-right">
                                                <strong>₱{{ number_format($detail['net_pay'] ?? 0, 2) }}</strong>
                                            </td>
                                            <td>
                                                <span class="badge badge-{{ $detail['payment_method'] == 'direct_deposit' ? 'success' : 'info' }}">
                                                    {{ ucfirst(str_replace('_', ' ', $detail['payment_method'] ?? 'unknown')) }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="10" class="text-center text-muted py-4">
                                            <i class="fas fa-file-invoice-dollar fa-2x mb-3"></i>
                                            <p>No payroll data found for the selected period.</p>
                                            <p class="small">Try adjusting your filters or generate payroll for this period first.</p>
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                            @if(isset($reportData['details']) && count($reportData['details']) > 0)
                                <tfoot class="bg-light">
                                    <tr>
                                        <th colspan="5" class="text-right">Totals:</th>
                                        <th class="text-right">₱{{ number_format($reportData['total_gross'] ?? 0, 2) }}</th>
                                        <th class="text-right">₱{{ number_format($reportData['total_taxes'] ?? 0, 2) }}</th>
                                        <th class="text-right">₱{{ number_format($reportData['total_deductions'] ?? 0, 2) }}</th>
                                        <th class="text-right text-success">₱{{ number_format($reportData['total_net'] ?? 0, 2) }}</th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts and Analysis -->
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-info text-white">
                    <h6 class="m-0 font-weight-bold">Payroll by Department</h6>
                </div>
                <div class="card-body">
                    <div class="text-center py-5">
                        <i class="fas fa-chart-pie fa-3x text-gray-300 mb-3"></i>
                        <p class="text-muted">Department breakdown chart would appear here</p>
                        <p class="small">In a real implementation, this would show a pie chart of payroll distribution by department.</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-warning text-dark">
                    <h6 class="m-0 font-weight-bold">Monthly Trend</h6>
                </div>
                <div class="card-body">
                    <div class="text-center py-5">
                        <i class="fas fa-chart-line fa-3x text-gray-300 mb-3"></i>
                        <p class="text-muted">Monthly payroll trend chart would appear here</p>
                        <p class="small">In a real implementation, this would show a line chart of payroll trends over time.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        $('#payrollReportTable').DataTable({
            pageLength: 25,
            order: [[8, 'desc']],
            dom: 'Bfrtip',
            buttons: [
                'copy', 'csv', 'excel', 'pdf', 'print'
            ]
        });

        // Update date fields based on period selection
        $('#report_period').change(function() {
            const period = $(this).val();
            const today = new Date();
            
            switch(period) {
                case 'current_month':
                    $('#start_date').val(today.getFullYear() + '-' + String(today.getMonth() + 1).padStart(2, '0') + '-01');
                    $('#end_date').val(today.getFullYear() + '-' + String(today.getMonth() + 1).padStart(2, '0') + '-' + 
                                      new Date(today.getFullYear(), today.getMonth() + 1, 0).getDate());
                    break;
                case 'last_month':
                    const lastMonth = new Date(today.getFullYear(), today.getMonth() - 1, 1);
                    $('#start_date').val(lastMonth.getFullYear() + '-' + String(lastMonth.getMonth() + 1).padStart(2, '0') + '-01');
                    $('#end_date').val(lastMonth.getFullYear() + '-' + String(lastMonth.getMonth() + 1).padStart(2, '0') + '-' + 
                                      new Date(lastMonth.getFullYear(), lastMonth.getMonth() + 1, 0).getDate());
                    break;
                case 'current_year':
                    $('#start_date').val(today.getFullYear() + '-01-01');
                    $('#end_date').val(today.getFullYear() + '-12-31');
                    break;
            }
        });
    });

    function printReport() {
        window.print();
    }

    function exportReport() {
        alert('Report export functionality would be implemented here.');
    }
</script>
@endpush
@endsection