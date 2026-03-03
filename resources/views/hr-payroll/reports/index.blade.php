@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <h1 class="h3 mb-0 text-gray-800">HR & Payroll Reports</h1>
            <p class="text-muted">Generate and view HR and payroll reports</p>
        </div>
    </div>

    <!-- Report Cards -->
    <div class="row">
        <div class="col-md-4">
            <div class="card shadow mb-4">
                <div class="card-header bg-primary text-white">
                    <h6 class="m-0 font-weight-bold">Payroll Reports</h6>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <a href="{{ route('hr-payroll.reports') }}" class="text-decoration-none">
                                <i class="fas fa-file-invoice-dollar text-primary mr-2"></i>
                                Payroll Summary
                            </a>
                            <span class="badge badge-primary badge-pill">Monthly</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <a href="#" class="text-decoration-none">
                                <i class="fas fa-users text-info mr-2"></i>
                                Employee Earnings
                            </a>
                            <span class="badge badge-info badge-pill">Custom</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <a href="#" class="text-decoration-none">
                                <i class="fas fa-percentage text-warning mr-2"></i>
                                Tax Deductions
                            </a>
                            <span class="badge badge-warning badge-pill">Quarterly</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow mb-4">
                <div class="card-header bg-success text-white">
                    <h6 class="m-0 font-weight-bold">HR Reports</h6>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <a href="#" class="text-decoration-none">
                                <i class="fas fa-user-clock text-success mr-2"></i>
                                Attendance Summary
                            </a>
                            <span class="badge badge-success badge-pill">Weekly</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <a href="#" class="text-decoration-none">
                                <i class="fas fa-calendar-alt text-info mr-2"></i>
                                Leave Analysis
                            </a>
                            <span class="badge badge-info badge-pill">Monthly</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <a href="#" class="text-decoration-none">
                                <i class="fas fa-chart-line text-warning mr-2"></i>
                                Turnover Rate
                            </a>
                            <span class="badge badge-warning badge-pill">Annual</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow mb-4">
                <div class="card-header bg-info text-white">
                    <h6 class="m-0 font-weight-bold">Compliance Reports</h6>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <a href="#" class="text-decoration-none">
                                <i class="fas fa-file-contract text-info mr-2"></i>
                                Tax Forms (W-2, 1099)
                            </a>
                            <span class="badge badge-info badge-pill">Annual</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <a href="#" class="text-decoration-none">
                                <i class="fas fa-balance-scale text-warning mr-2"></i>
                                Labor Compliance
                            </a>
                            <span class="badge badge-warning badge-pill">Quarterly</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <a href="#" class="text-decoration-none">
                                <i class="fas fa-shield-alt text-danger mr-2"></i>
                                Benefits Compliance
                            </a>
                            <span class="badge badge-danger badge-pill">Annual</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Report Generator - Temporarily removed -->
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-header bg-warning text-dark">
                    <h6 class="m-0 font-weight-bold">Payroll Report Generator</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted">Click on "Payroll Summary" above to access the payroll report generator with proper filtering options.</p>
                    <p>The payroll report system allows you to generate:</p>
                    <ul>
                        <li><strong>Summary Reports</strong> - Overview of payroll totals</li>
                        <li><strong>Detailed Reports</strong> - Employee-level breakdown</li>
                        <li><strong>Tax Reports</strong> - Tax deduction analysis</li>
                        <li><strong>Deduction Reports</strong> - Benefits and other deductions</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function exportToPDF() {
        alert('PDF export functionality would be implemented here.');
    }

    function exportToExcel() {
        alert('Excel export functionality would be implemented here.');
    }
</script>
@endpush
@endsection