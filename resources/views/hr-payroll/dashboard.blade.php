@extends('layouts.app')

@section('title', 'HR Payroll Dashboard')

@push('styles')
<style>
.fixit-hr-dash{ background:#f4f6fa; min-height:100vh; padding-top:0.5rem; padding-bottom:2rem; }
.fixit-hr-dash .page-title{ color:#1a2332; font-size:1.15rem; }
.fixit-hr-dash .accent-icon{ color:#4361ee; }
.fixit-hr-dash .status-dot{ display:inline-block; width:6px;height:6px;border-radius:50%;background:#4361ee;vertical-align:middle; }
/* Stat cards */
.fixit-hr-dash .hr-stat-card{ border-radius:10px; border:0; box-shadow:0 1px 2px rgba(0,0,0,.04); transition:box-shadow .2s,transform .15s; overflow:hidden; }
.fixit-hr-dash .hr-stat-card:hover{ box-shadow:0 3px 8px rgba(0,0,0,.06); transform:translateY(-1px); }
.fixit-hr-dash .hr-stat-icon{ width:32px;height:32px;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0; }
.fixit-hr-dash .hr-stat-label{ font-size:.68rem;color:#6c7a8d;text-transform:uppercase;letter-spacing:.4px;font-weight:600; }
.fixit-hr-dash .hr-stat-value{ font-size:1.2rem;font-weight:700;color:#1a2332;line-height:1.1; }
.fixit-hr-dash .hr-stat-sub{ font-size:.65rem;color:#8b9aab;display:block;margin-top:1px; }
/* Content cards */
.fixit-hr-dash .hr-card{ border-radius:10px; border:0; box-shadow:0 1px 2px rgba(0,0,0,.04); }
.fixit-hr-dash .hr-card-header{ background:transparent; border-bottom:1px solid #f0f2f5; padding:.65rem 1rem; font-size:.82rem; font-weight:600; color:#1a2332; display:flex; justify-content:space-between; align-items:center; }
/* Quick action buttons */
.fixit-hr-dash .hr-action-btn{ border-radius:10px; border:1px solid #e2e8f0; background:white; transition:all .15s; padding:.6rem; text-align:center; display:block; color:#475569; font-size:.75rem; font-weight:500; }
.fixit-hr-dash .hr-action-btn:hover{ border-color:#4361ee; background:#f8faff; color:#4361ee; text-decoration:none; }
.fixit-hr-dash .hr-action-btn i{ display:block; margin-bottom:3px; font-size:1.3rem; }
</style>
@endpush

@section('content')
<div class="container-fluid px-3 px-md-4 fixit-hr-dash">
    <!-- ══ PAGE HEADER ══ -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0 fw-bold page-title">
                <i class="fas fa-users-cog me-2 accent-icon"></i>HR &amp; Payroll
            </h4>
            <p class="mb-0 text-muted small"><span class="status-dot"></span> Employees, payroll, time attendance, leave</p>
        </div>
    </div>

    <!-- ══ COMPACT STAT CARDS ══ -->
    <div class="row g-3 mb-4">
        @php
            $hrStats = [
                ['label'=>'Total Employees','value'=>$stats['total_employees'],'sub'=>'Active: '.$stats['active_employees'],'icon'=>'users','color'=>'#4361ee'],
                ['label'=>'Monthly Payroll','value'=>'₱'.number_format($stats['total_payroll_month'],0),'sub'=>'This month','icon'=>'money-bill-wave','color'=>'#2ec4b6'],
                ['label'=>'Pending Actions','value'=>$pendingLeaveRequests + $pendingTimeApprovals,'sub'=>'Leave: '.$pendingLeaveRequests.', Time: '.$pendingTimeApprovals,'icon'=>'clock','color'=>'#f7a429'],
                ['label'=>'Leave Requests','value'=>$stats['total_leave_requests'],'sub'=>'Approved: '.$stats['approved_leave_requests'],'icon'=>'calendar-alt','color'=>'#e63946'],
            ];
        @endphp
        @foreach($hrStats as $s)
        <div class="col-6 col-md-3">
            <div class="card hr-stat-card h-100">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start mb-1">
                        <span class="hr-stat-label">{{ $s['label'] }}</span>
                        <div class="hr-stat-icon" style="background:{{ $s['color'] }}12;">
                            <i class="fas fa-{{ $s['icon'] }}" style="color:{{ $s['color'] }};font-size:.8rem;"></i>
                        </div>
                    </div>
                    <div class="hr-stat-value">{{ $s['value'] }}</div>
                    <small class="hr-stat-sub">{{ $s['sub'] }}</small>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- ══ RECENT & UPCOMING PAYROLLS ══ -->
    <div class="row g-3 mb-4">
        <!-- Recent Payroll Periods -->
        <div class="col-lg-6">
            <div class="card hr-card h-100">
                <div class="hr-card-header">
                    <span><i class="fas fa-history me-1"></i> Recent Payroll Periods</span>
                    <a href="{{ route('hr-payroll.payroll.periods') }}" class="btn btn-sm btn-outline-primary" style="font-size:.73rem;border-radius:6px;">View All</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" style="font-size:.78rem;">
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
                                    </td>
                                    <td>{{ $payroll->pay_date->format('M d, Y') }}</td>
                                    <td>
                                        <span class="badge bg-{{ $payroll->status_color }}" style="font-size:.68rem;font-weight:500;">
                                            {{ ucfirst($payroll->status) }}
                                        </span>
                                    </td>
                                    <td class="text-end fw-semibold">₱{{ number_format($payroll->total_net, 2) }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted small">
                                        <i class="fas fa-info-circle me-1"></i>No payroll periods found
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
        <div class="col-lg-6">
            <div class="card hr-card h-100">
                <div class="hr-card-header">
                    <span><i class="fas fa-calendar-check me-1"></i> Upcoming Payrolls</span>
                    <a href="{{ route('hr-payroll.payroll.periods') }}" class="btn btn-sm btn-outline-primary" style="font-size:.73rem;border-radius:6px;">View All</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" style="font-size:.78rem;">
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
                                    </td>
                                    <td>{{ $payroll->pay_date->format('M d, Y') }}</td>
                                    <td>
                                        @if($daysLeft > 0)
                                            <span class="badge bg-info" style="font-size:.68rem;font-weight:500;">{{ $daysLeft }} days</span>
                                        @else
                                            <span class="badge bg-warning" style="font-size:.68rem;font-weight:500;">Today</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <span class="badge bg-{{ $payroll->status_color }}" style="font-size:.68rem;font-weight:500;">
                                            {{ ucfirst($payroll->status) }}
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted small">
                                        <i class="fas fa-info-circle me-1"></i>No upcoming payrolls
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

    <!-- ══ QUICK ACTIONS ══ -->
    <div class="card hr-card">
        <div class="hr-card-header">
            <span><i class="fas fa-bolt me-1"></i> Quick Actions</span>
        </div>
        <div class="card-body p-3">
            <div class="row g-2">
                <div class="col-4 col-md-2">
                    <a href="{{ route('hr-payroll.employees') }}" class="hr-action-btn">
                        <i class="fas fa-users"></i>
                        <div>Employees</div>
                    </a>
                </div>
                <div class="col-4 col-md-2">
                    <a href="{{ route('hr-payroll.payroll.periods') }}" class="hr-action-btn">
                        <i class="fas fa-money-bill-wave"></i>
                        <div>Payroll</div>
                    </a>
                </div>
                <div class="col-4 col-md-2">
                    <a href="{{ route('hr-payroll.time-attendance') }}" class="hr-action-btn">
                        <i class="fas fa-clock"></i>
                        <div>Attendance</div>
                    </a>
                </div>
                <div class="col-4 col-md-2">
                    <a href="{{ route('hr-payroll.leave') }}" class="hr-action-btn">
                        <i class="fas fa-calendar-alt"></i>
                        <div>Leave</div>
                    </a>
                </div>
                <div class="col-4 col-md-2">
                    <a href="{{ route('hr-payroll.deductions.index') }}" class="hr-action-btn">
                        <i class="fas fa-minus-circle"></i>
                        <div>Deductions</div>
                    </a>
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