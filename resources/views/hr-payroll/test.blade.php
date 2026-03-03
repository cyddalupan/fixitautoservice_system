@extends('layouts.app')

@section('title', 'HR Payroll System Test')

@section('content')
<div class="container-fluid">
    <div class="page-header">
        <h1><i class="fas fa-check-circle me-2 text-success"></i>HR Payroll System - Successfully Installed!</h1>
        <p class="lead">The HR Payroll System has been successfully added to Fixit Auto Services.</p>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-cogs me-2"></i>System Features</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="feature-card">
                                <div class="feature-icon bg-primary">
                                    <i class="fas fa-users"></i>
                                </div>
                                <h5>Employee Management</h5>
                                <p class="text-muted">Complete employee profiles with HR details, departments, positions, and employment status.</p>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="feature-card">
                                <div class="feature-icon bg-success">
                                    <i class="fas fa-money-bill-wave"></i>
                                </div>
                                <h5>Payroll Processing</h5>
                                <p class="text-muted">Automated payroll calculation, processing, and payment tracking with tax calculations.</p>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="feature-card">
                                <div class="feature-icon bg-warning">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <h5>Time & Attendance</h5>
                                <p class="text-muted">Clock in/out system, timesheets, overtime tracking, and attendance management.</p>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="feature-card">
                                <div class="feature-icon bg-info">
                                    <i class="fas fa-calendar-alt"></i>
                                </div>
                                <h5>Leave Management</h5>
                                <p class="text-muted">Vacation, sick leave, personal leave tracking with approval workflows.</p>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="feature-card">
                                <div class="feature-icon bg-danger">
                                    <i class="fas fa-file-invoice-dollar"></i>
                                </div>
                                <h5>Tax & Deductions</h5>
                                <p class="text-muted">Tax calculations, deductions, withholdings, and compliance reporting.</p>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="feature-card">
                                <div class="feature-icon bg-secondary">
                                    <i class="fas fa-chart-line"></i>
                                </div>
                                <h5>Reports & Analytics</h5>
                                <p class="text-muted">Payroll summaries, tax reports, employee analytics, and compliance reports.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-database me-2"></i>Database Tables Created</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            employee_hr_details
                            <span class="badge bg-success">✓</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            payroll_periods
                            <span class="badge bg-success">✓</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            payroll_records
                            <span class="badge bg-success">✓</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            time_attendance
                            <span class="badge bg-success">✓</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            leave_requests
                            <span class="badge bg-success">✓</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            leave_balances
                            <span class="badge bg-success">✓</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            tax_settings
                            <span class="badge bg-success">✓</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            deduction_settings
                            <span class="badge bg-success">✓</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            employee_deductions
                            <span class="badge bg-success">✓</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            payroll_history_logs
                            <span class="badge bg-success">✓</span>
                        </li>
                    </ul>
                </div>
            </div>
            
            <div class="card mt-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-rocket me-2"></i>Quick Start</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('hr-payroll.dashboard') }}" class="btn btn-primary">
                            <i class="fas fa-tachometer-alt me-2"></i>Go to HR Payroll Dashboard
                        </a>
                        <a href="{{ route('hr-payroll.employees') }}" class="btn btn-outline-primary">
                            <i class="fas fa-users me-2"></i>Manage Employees
                        </a>
                        <a href="{{ route('hr-payroll.payroll.periods') }}" class="btn btn-outline-success">
                            <i class="fas fa-money-bill-wave me-2"></i>Process Payroll
                        </a>
                        <a href="{{ route('hr-payroll.portal.dashboard') }}" class="btn btn-outline-info">
                            <i class="fas fa-user-circle me-2"></i>Employee Self-Service
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card mt-4">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Access Instructions</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h6><i class="fas fa-user-tie me-2"></i>For Administrators/HR:</h6>
                    <ul>
                        <li>Access via sidebar: <strong>HR Payroll</strong> menu</li>
                        <li>Manage all employees and HR details</li>
                        <li>Process payroll and approve time/leave</li>
                        <li>Generate reports and analytics</li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <h6><i class="fas fa-user me-2"></i>For Employees:</h6>
                    <ul>
                        <li>Access via Employee Self-Service portal</li>
                        <li>View payslips and payroll history</li>
                        <li>Submit leave requests</li>
                        <li>Clock in/out and view time attendance</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.feature-card {
    text-align: center;
    padding: 20px;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    height: 100%;
}
.feature-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 15px;
    color: white;
    font-size: 24px;
}
</style>
@endsection