@extends('layouts.app')

@section('title', 'HR Payroll Dashboard')

@section('content')
<div class="container-fluid">
    <div class="page-header">
        <h1><i class="fas fa-users-cog me-2"></i>HR Payroll Dashboard</h1>
        <p class="lead">Manage employees, payroll, time attendance, and leave requests</p>
    </div>

    <!-- Quick Stats -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card bg-primary text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="text-uppercase mb-0">Total Employees</h6>
                            <h2 class="mb-0">{{ $stats['total_employees'] }}</h2>
                            <small>Active: {{ $stats['active_employees'] }}</small>
                        </div>
                        <div class="card-icon">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card bg-success text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="text-uppercase mb-0">Monthly Payroll</h6>
                            <h2 class="mb-0">₱{{ number_format($stats['total_payroll_month'], 2) }}</h2>
                            <small>This month</small>
                        </div>
                        <div class="card-icon">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card bg-warning text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="text-uppercase mb-0">Pending Actions</h6>
                            <h2 class="mb-0">{{ $pendingLeaveRequests + $pendingTimeApprovals }}</h2>
                            <small>Leave: {{ $pendingLeaveRequests }}, Time: {{ $pendingTimeApprovals }}</small>
                        </div>
                        <div class="card-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card bg-info text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="text-uppercase mb-0">Leave Requests</h6>
                            <h2 class="mb-0">{{ $stats['total_leave_requests'] }}</h2>
                            <small>Approved: {{ $stats['approved_leave_requests'] }}</small>
                        </div>
                        <div class="card-icon">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Recent Payroll Periods -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-history me-2"></i>Recent Payroll Periods</h5>
                    <a href="{{ route('hr-payroll.payroll.periods') }}" class="btn btn-sm btn-primary">View All</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Period</th>
                                    <th>Pay Date</th>
                                    <th>Status</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentPayrolls as $payroll)
                                <tr>
                                    <td>
                                        <strong>{{ $payroll->period_name }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $payroll->start_date->format('M d') }} - {{ $payroll->end_date->format('M d, Y') }}</small>
                                    </td>
                                    <td>{{ $payroll->pay_date->format('M d, Y') }}</td>
                                    <td>
                                        <span class="badge bg-{{ $payroll->status_color }}">
                                            {{ ucfirst($payroll->status) }}
                                        </span>
                                    </td>
                                    <td class="text-end">₱{{ number_format($payroll->total_net, 2) }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">
                                        <i class="fas fa-info-circle me-2"></i>No payroll periods found
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Upcoming Payrolls -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-calendar-check me-2"></i>Upcoming Payrolls</h5>
                    <a href="{{ route('hr-payroll.payroll.periods') }}" class="btn btn-sm btn-primary">View All</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Period</th>
                                    <th>Pay Date</th>
                                    <th>Days Left</th>
                                    <th class="text-end">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($upcomingPayrolls as $payroll)
                                @php
                                    $daysLeft = now()->diffInDays($payroll->pay_date, false);
                                @endphp
                                <tr>
                                    <td>
                                        <strong>{{ $payroll->period_name }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $payroll->start_date->format('M d') }} - {{ $payroll->end_date->format('M d, Y') }}</small>
                                    </td>
                                    <td>{{ $payroll->pay_date->format('M d, Y') }}</td>
                                    <td>
                                        @if($daysLeft > 0)
                                            <span class="badge bg-info">{{ $daysLeft }} days</span>
                                        @else
                                            <span class="badge bg-warning">Today</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <span class="badge bg-{{ $payroll->status_color }}">
                                            {{ ucfirst($payroll->status) }}
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">
                                        <i class="fas fa-info-circle me-2"></i>No upcoming payrolls
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('hr-payroll.employees') }}" class="btn btn-outline-primary w-100 py-3">
                                <i class="fas fa-users fa-2x mb-2"></i>
                                <div>Manage Employees</div>
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('hr-payroll.payroll.periods') }}" class="btn btn-outline-success w-100 py-3">
                                <i class="fas fa-money-bill-wave fa-2x mb-2"></i>
                                <div>Process Payroll</div>
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('hr-payroll.time-attendance') }}" class="btn btn-outline-warning w-100 py-3">
                                <i class="fas fa-clock fa-2x mb-2"></i>
                                <div>Time & Attendance</div>
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('hr-payroll.leave') }}" class="btn btn-outline-info w-100 py-3">
                                <i class="fas fa-calendar-alt fa-2x mb-2"></i>
                                <div>Leave Management</div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Auto-refresh dashboard every 60 seconds
    setTimeout(function() {
        window.location.reload();
    }, 60000);
</script>
@endsection