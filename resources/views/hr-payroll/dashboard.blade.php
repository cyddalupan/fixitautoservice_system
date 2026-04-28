@extends('layouts.app')

@section('title', 'HR & Payroll')

@push('styles')
<style>
/* ══ HR PAYROLL — GLOBAL STYLES ══ */
:root {
  --hr-blue: #4361ee;
  --hr-blue-light: #eef0ff;
  --hr-green: #10b981;
  --hr-green-light: #d1fae5;
  --hr-red: #ef4444;
  --hr-red-light: #fee2e2;
  --hr-yellow: #f59e0b;
  --hr-yellow-light: #fef3c7;
  --hr-purple: #8b5cf6;
  --hr-purple-light: #ede9fe;
  --hr-gold: #d97706;
  --hr-gold-light: #fef9c3;
  --hr-gray-50: #f8fafc;
  --hr-gray-100: #f1f5f9;
  --hr-gray-200: #e2e8f0;
  --hr-gray-300: #cbd5e1;
  --hr-gray-400: #94a3b8;
  --hr-gray-500: #64748b;
  --hr-gray-600: #475569;
  --hr-gray-700: #334155;
  --hr-gray-800: #1e293b;
  --hr-radius: 10px;
  --hr-shadow: 0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04);
  --hr-shadow-hover: 0 4px 12px rgba(0,0,0,0.08);
  --hr-transition: all 0.2s ease;
}
.hr-wrap { min-height:100vh; background:var(--hr-gray-50); }
.hr-tabs { display:flex; gap:0; background:white; border-bottom:1px solid var(--hr-gray-200); padding:0 1rem; overflow-x:auto; scrollbar-width:none; }
.hr-tabs::-webkit-scrollbar { display:none; }
.hr-tab { display:flex; align-items:center; gap:6px; padding:12px 20px; font-size:.82rem; font-weight:500; color:var(--hr-gray-500); border-bottom:2px solid transparent; cursor:pointer; white-space:nowrap; transition:var(--hr-transition); background:none; border-top:0; border-left:0; border-right:0; }
.hr-tab:hover { color:var(--hr-blue); background:var(--hr-blue-light); }
.hr-tab.active { color:var(--hr-blue); border-bottom-color:var(--hr-blue); background:transparent; }
.hr-tab i { font-size:.9rem; }
.hr-tab-content { display:none; }
.hr-tab-content.active { display:block; }
.hr-stat { background:white; border-radius:var(--hr-radius); border:1px solid var(--hr-gray-200); padding:1rem; transition:var(--hr-transition); }
.hr-stat:hover { box-shadow:var(--hr-shadow-hover); transform:translateY(-1px); }
.hr-stat-icon { width:38px;height:38px;border-radius:9px;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:1rem; }
.hr-stat-label { font-size:.65rem;color:var(--hr-gray-500);text-transform:uppercase;letter-spacing:.4px;font-weight:600; }
.hr-stat-val { font-size:1.35rem;font-weight:700;color:var(--hr-gray-800);line-height:1.1; }
.hr-stat-sub { font-size:.65rem;color:var(--hr-gray-400); }
.hr-card { background:white; border-radius:var(--hr-radius); border:1px solid var(--hr-gray-200); overflow:hidden; }
.hr-card-hdr { padding:.7rem 1rem; border-bottom:1px solid var(--hr-gray-100); display:flex; justify-content:space-between; align-items:center; font-size:.82rem; font-weight:600; color:var(--hr-gray-800); }
.hr-card-bd { padding:1rem; }
.hr-cal { display:grid; grid-template-columns:repeat(7,1fr); gap:4px; }
.hr-cal-day { min-height:80px; background:white; border:1px solid var(--hr-gray-200); border-radius:6px; padding:4px 5px; transition:var(--hr-transition); cursor:pointer; font-size:.72rem; position:relative; }
.hr-cal-day:hover { border-color:var(--hr-blue); box-shadow:0 0 0 2px var(--hr-blue-light); }
.hr-cal-day.other-month { opacity:.35; pointer-events:none; }
.hr-cal-day.today { border-color:var(--hr-blue); background:var(--hr-blue-light); }
.hr-cal-day.weekend { background:var(--hr-gray-50); }
.hr-cal-day .day-num { font-weight:600; font-size:.78rem; color:var(--hr-gray-700); margin-bottom:2px; }
.hr-cal-day .day-dot { display:inline-block; width:6px;height:6px;border-radius:50%;margin-right:2px; }
.hr-cal-hdr { text-align:center; font-size:.7rem; font-weight:600; color:var(--hr-gray-500); padding:6px 0; text-transform:uppercase; letter-spacing:.3px; }
.hr-teammate { display:flex; align-items:center; gap:10px; padding:10px 12px; border:1px solid var(--hr-gray-200); border-radius:8px; transition:var(--hr-transition); cursor:pointer; }
.hr-teammate:hover { border-color:var(--hr-blue); box-shadow:var(--hr-shadow); }
.hr-teammate-avatar { width:38px;height:38px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:600;font-size:.85rem;color:white;flex-shrink:0; }
.hr-teammate-name { font-size:.82rem;font-weight:600;color:var(--hr-gray-800); }
.hr-teammate-role { font-size:.7rem;color:var(--hr-gray-500); }
.hr-btn { display:inline-flex;align-items:center;gap:5px;padding:6px 14px;border-radius:7px;font-size:.75rem;font-weight:500;border:0;cursor:pointer;transition:var(--hr-transition);text-decoration:none; }
.hr-btn-primary { background:var(--hr-blue); color:white; }
.hr-btn-primary:hover { background:#3651d4; color:white; }
.hr-btn-outline { background:transparent; border:1px solid var(--hr-gray-300); color:var(--hr-gray-600); }
.hr-btn-outline:hover { border-color:var(--hr-blue); color:var(--hr-blue); background:var(--hr-blue-light); }
.hr-btn-success { background:var(--hr-green); color:white; }
.hr-btn-danger { background:var(--hr-red); color:white; }
.hr-btn-sm { padding:4px 10px; font-size:.7rem; }
.hr-table { width:100%; border-collapse:collapse; font-size:.78rem; }
.hr-table th { text-align:left; padding:8px 10px; border-bottom:2px solid var(--hr-gray-200); color:var(--hr-gray-600); font-weight:600; font-size:.72rem; text-transform:uppercase; letter-spacing:.3px; }
.hr-table td { padding:8px 10px; border-bottom:1px solid var(--hr-gray-100); color:var(--hr-gray-700); }
.hr-filter { display:flex; gap:8px; flex-wrap:wrap; align-items:center; margin-bottom:1rem; }
.hr-filter select, .hr-filter input { font-size:.75rem; padding:5px 10px; border:1px solid var(--hr-gray-300); border-radius:6px; background:white; color:var(--hr-gray-700); }
.hr-badge { display:inline-flex;align-items:center;gap:4px;padding:3px 8px;border-radius:5px;font-size:.65rem;font-weight:500; }
.hr-badge-green { background:var(--hr-green-light); color:#065f46; }
.hr-badge-red { background:var(--hr-red-light); color:#991b1b; }
.hr-badge-yellow { background:var(--hr-yellow-light); color:#92400e; }
.hr-badge-blue { background:#dbeafe; color:#1e40af; }
.hr-badge-purple { background:var(--hr-purple-light); color:#5b21b6; }
.hr-badge-gray { background:var(--hr-gray-100); color:var(--hr-gray-600); }
.hr-modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.4); z-index:1050; align-items:center; justify-content:center; padding:1rem; }
.hr-modal-overlay.show { display:flex; }
.hr-modal { background:white; border-radius:12px; width:100%; max-width:700px; max-height:85vh; overflow-y:auto; box-shadow:0 20px 60px rgba(0,0,0,0.15); }
.hr-modal-hdr { padding:1rem 1.25rem; border-bottom:1px solid var(--hr-gray-100); display:flex; justify-content:space-between; align-items:center; }
.hr-modal-hdr h5 { font-size:.95rem; font-weight:600; margin:0; }
.hr-modal-hdr .close { background:none; border:0; font-size:1.2rem; cursor:pointer; color:var(--hr-gray-400); }
.hr-modal-bd { padding:1.25rem; }
.hr-notif { padding:6px 12px; border-radius:6px; font-size:.72rem; display:inline-flex; align-items:center; gap:6px; }
.hr-notif-warning { background:var(--hr-yellow-light); color:#92400e; }
.hr-notif-danger { background:var(--hr-red-light); color:#991b1b; }
.hr-notif-info { background:#dbeafe; color:#1e40af; }
.hr-notif-success { background:var(--hr-green-light); color:#065f46; }
.hr-settings-group { margin-bottom:1.5rem; }
.hr-settings-group h6 { font-size:.8rem; font-weight:600; color:var(--hr-gray-700); margin-bottom:8px; }
.hr-settings-group label { font-size:.73rem; color:var(--hr-gray-600); margin-bottom:3px; display:block; }
.hr-settings-group .form-control, .hr-settings-group .form-select { font-size:.78rem; border:1px solid var(--hr-gray-300); border-radius:6px; }
@media(max-width:768px) {
  .hr-tab { padding:10px 12px; font-size:.75rem; }
  .hr-tab span { display:none; }
  .hr-stat { padding:.75rem; }
  .hr-stat-val { font-size:1.1rem; }
  .hr-cal-day { min-height:60px; font-size:.65rem; }
}
</style>
@endpush

@section('content')
@php
$today = now()->format('Y-m-d');
$todayAtt = \App\Models\TimeAttendance::with('employee')->whereDate('work_date', $today)->get();
$presentToday = $todayAtt->where('status','present')->count();
$lateToday = $todayAtt->where('status','late')->count();
$absentToday = $todayAtt->where('status','absent')->count();
$onLeaveToday = \App\Models\LeaveRequest::where('status','approved')->whereDate('start_date','<=',$today)->whereDate('end_date','>=',$today)->count();
$leaveRequests = \App\Models\LeaveRequest::with('employee')->latest()->get();
$allPayrollPeriods = \App\Models\PayrollPeriod::latest()->get();
$currentPayroll = \App\Models\PayrollPeriod::whereIn('status',['processing','draft'])->first();
$totalPayrollAmount = \App\Models\PayrollPeriod::whereIn('status',['processing','draft'])->sum('total_net');
$deductionSettings = \App\Models\DeductionSetting::where('is_active', true)->get();
$pendingRequests = $pendingLeaveRequests + $pendingTimeApprovals;
$absentNames = $employees->filter(function($e) use ($todayAtt) {
    return !$todayAtt->where('employee_id', $e->id)->whereIn('status',['present','late'])->count();
})->take(3);
$upcomingPayDate = $upcomingPayrolls->first()?->pay_date;
$birthdaysToday = $employees->filter(function($e) {
    return $e->employeeHrDetails && $e->employeeHrDetails->date_of_birth
        && \Carbon\Carbon::parse($e->employeeHrDetails->date_of_birth)->format('m-d') === now()->format('m-d');
});
$paydayDates = $upcomingPayrolls->pluck('pay_date')->map(fn($d) => $d->format('Y-m-d'));
@endphp

<div class="hr-wrap">
<div class="px-3 px-md-4 pt-3 pb-0 d-flex flex-wrap gap-2">
    @if($absentNames->count() > 0)
    <span class="hr-notif hr-notif-danger"><i class="fas fa-exclamation-circle"></i> {{ $absentNames->count() }} employee(s) no time-in today</span>
    @endif
    @if($upcomingPayDate && now()->diffInDays($upcomingPayDate, false) <= 2)
    <span class="hr-notif hr-notif-warning"><i class="fas fa-calendar-check"></i> Payroll {{ now()->diffInDays($upcomingPayDate)<=0?'today':'in '.now()->diffInDays($upcomingPayDate).' day(s)' }}</span>
    @endif
    @if($birthdaysToday->count() > 0)
    <span class="hr-notif hr-notif-success"><i class="fas fa-birthday-cake"></i> {{ $birthdaysToday->count() }} birthday(s) today</span>
    @endif
    @if($pendingRequests > 0)
    <span class="hr-notif hr-notif-info"><i class="fas fa-inbox"></i> {{ $pendingRequests }} pending request(s)</span>
    @endif
</div>

<!-- TABS -->
<div class="hr-tabs">
    <button class="hr-tab active" onclick="switchTab('dashboard')"><i class="fas fa-th-large"></i><span>Dashboard</span></button>
    <button class="hr-tab" onclick="switchTab('calendar')"><i class="fas fa-calendar-alt"></i><span>Calendar</span></button>
    <button class="hr-tab" onclick="switchTab('team')"><i class="fas fa-users"></i><span>Team</span></button>
    <button class="hr-tab" onclick="switchTab('payroll')"><i class="fas fa-money-bill-wave"></i><span>Payroll</span></button>
    <button class="hr-tab" onclick="switchTab('requests')"><i class="fas fa-clipboard-list"></i><span>Requests</span></button>
    <button class="hr-tab" onclick="switchTab('settings')"><i class="fas fa-cog"></i><span>Settings</span></button>
</div>

<!-- TAB: DASHBOARD -->
<div id="tab-dashboard" class="hr-tab-content active px-3 px-md-4 py-3">
<div class="row g-3 mb-4">
    @php
    $dashCards = [
        ['i'=>'users','l'=>'Total Employees','v'=>$totalEmployees,'s'=>'Active staff','c'=>'#4361ee','b'=>'#eef0ff'],
        ['i'=>'check-circle','l'=>'Present Today','v'=>$presentToday,'s'=>'On duty now','c'=>'#10b981','b'=>'#d1fae5'],
        ['i'=>'clock','l'=>'Late Today','v'=>$lateToday,'s'=>'Arrived late','c'=>'#f59e0b','b'=>'#fef3c7'],
        ['i'=>'bed','l'=>'On Leave','v'=>$onLeaveToday,'s'=>'Approved leave','c'=>'#8b5cf6','b'=>'#ede9fe'],
        ['i'=>'calendar-check','l'=>'Upcoming Payday','v'=>$upcomingPayrolls->first()?->pay_date?->format('M d') ?? '—','s'=>$upcomingPayrolls->first()?->period_name ?? 'No upcoming','c'=>'#d97706','b'=>'#fef9c3'],
        ['i'=>'inbox','l'=>'Pending Requests','v'=>$pendingRequests,'s'=>$pendingLeaveRequests.' leave / '.$pendingTimeApprovals.' time','c'=>'#ef4444','b'=>'#fee2e2'],
    ];
    @endphp
    @foreach($dashCards as $c)
    <div class="col-6 col-md-4 col-lg-2">
        <div class="hr-stat">
            <div class="d-flex justify-content-between align-items-start mb-1">
                <span class="hr-stat-label">{{ $c['l'] }}</span>
                <div class="hr-stat-icon" style="background:{{ $c['b'] }};color:{{ $c['c'] }};"><i class="fas fa-{{ $c['i'] }}"></i></div>
            </div>
            <div class="hr-stat-val">{{ $c['v'] }}</div>
            <small class="hr-stat-sub">{{ $c['s'] }}</small>
        </div>
    </div>
    @endforeach
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-6">
        <div class="hr-card">
            <div class="hr-card-hdr"><span><i class="fas fa-history me-1"></i> Recent Payrolls</span><a href="{{ route('hr-payroll.payroll.periods') }}" class="hr-btn hr-btn-outline hr-btn-sm">View All</a></div>
            <div class="hr-card-bd p-0">
                <table class="hr-table">
                    <thead><tr><th>Period</th><th>Pay Date</th><th>Status</th><th class="text-end">Total</th></tr></thead>
                    <tbody>
                        @forelse($recentPayrolls as $p)
                        <tr>
                            <td><strong>{{ $p->period_name }}</strong></td>
                            <td>{{ $p->pay_date->format('M d, Y') }}</td>
                            <td><span class="hr-badge hr-badge-{{ $p->status == 'paid' ? 'green' : ($p->status == 'processing' ? 'yellow' : 'gray') }}">{{ ucfirst($p->status) }}</span></td>
                            <td class="text-end fw-semibold">₱{{ number_format($p->total_net,2) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center py-3 text-muted small">No payrolls yet</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="hr-card">
            <div class="hr-card-hdr"><span><i class="fas fa-calendar-check me-1"></i> Upcoming Payrolls</span><a href="{{ route('hr-payroll.payroll.periods') }}" class="hr-btn hr-btn-outline hr-btn-sm">View All</a></div>
            <div class="hr-card-bd p-0">
                <table class="hr-table">
                    <thead><tr><th>Period</th><th>Pay Date</th><th>Days Left</th><th class="text-end">Status</th></tr></thead>
                    <tbody>
                        @forelse($upcomingPayrolls as $p)
                        @php $daysLeft = now()->diffInDays($p->pay_date, false); @endphp
                        <tr>
                            <td><strong>{{ $p->period_name }}</strong></td>
                            <td>{{ $p->pay_date->format('M d, Y') }}</td>
                            <td><span class="hr-badge hr-badge-{{ $daysLeft <= 0 ? 'yellow' : 'blue' }}">{{ $daysLeft <= 0 ? 'Today!' : $daysLeft.' day(s)' }}</span></td>
                            <td class="text-end"><span class="hr-badge hr-badge-{{ $p->status == 'draft' ? 'gray' : ($p->status == 'processing' ? 'yellow' : 'green') }}">{{ ucfirst($p->status) }}</span></td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center py-3 text-muted small">No upcoming</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="hr-card">
    <div class="hr-card-hdr"><span><i class="fas fa-bolt me-1"></i> Quick Actions</span></div>
    <div class="hr-card-bd">
        <div class="row g-2">
            <div class="col-4 col-md-2"><a href="#" class="hr-btn hr-btn-outline w-100" style="flex-direction:column;padding:10px 0;" onclick="switchTab('calendar');return false;"><i class="fas fa-calendar-alt" style="font-size:1.2rem;margin-bottom:4px;"></i><div>Attendance</div></a></div>
            <div class="col-4 col-md-2"><a href="#" class="hr-btn hr-btn-outline w-100" style="flex-direction:column;padding:10px 0;" onclick="switchTab('payroll');return false;"><i class="fas fa-money-bill-wave" style="font-size:1.2rem;margin-bottom:4px;"></i><div>Payroll</div></a></div>
            <div class="col-4 col-md-2"><a href="#" class="hr-btn hr-btn-outline w-100" style="flex-direction:column;padding:10px 0;" onclick="switchTab('team');return false;"><i class="fas fa-users" style="font-size:1.2rem;margin-bottom:4px;"></i><div>Team</div></a></div>
            <div class="col-4 col-md-2"><a href="#" class="hr-btn hr-btn-outline w-100" style="flex-direction:column;padding:10px 0;" onclick="switchTab('requests');return false;"><i class="fas fa-clipboard-list" style="font-size:1.2rem;margin-bottom:4px;"></i><div>Requests</div></a></div>
            <div class="col-4 col-md-2"><a href="{{ route('hr-payroll.time-attendance') }}" class="hr-btn hr-btn-outline w-100" style="flex-direction:column;padding:10px 0;"><i class="fas fa-clock" style="font-size:1.2rem;margin-bottom:4px;"></i><div>Log Time</div></a></div>
        </div>
    </div>
</div>
</div>
<!-- END DASHBOARD TAB -->

<!-- TAB: CALENDAR -->
<div id="tab-calendar" class="hr-tab-content px-3 px-md-4 py-3">
<div class="d-flex flex-wrap gap-3 mb-3" style="font-size:.75rem;">
    <span><span class="day-dot" style="background:var(--hr-green);"></span> Present</span>
    <span><span class="day-dot" style="background:var(--hr-red);"></span> Absent</span>
    <span><span class="day-dot" style="background:var(--hr-yellow);"></span> Late</span>
    <span><span class="day-dot" style="background:var(--hr-purple);"></span> Leave</span>
    <span><span class="day-dot" style="background:var(--hr-gold);"></span> Payday</span>
    <span><span class="day-dot" style="background:var(--hr-blue);"></span> Holiday</span>
</div>
<div class="hr-filter">
    <button class="hr-btn hr-btn-primary hr-btn-sm"><i class="fas fa-users"></i> All</button>
    <select class="form-select form-select-sm" style="width:auto;"><option value="">All Employees</option>@foreach($employees as $e)<option value="{{ $e->id }}">{{ $e->name }}</option>@endforeach</select>
    <select class="form-select form-select-sm" style="width:auto;"><option value="">All Positions</option><option>Technician</option><option>Office Staff</option><option>Admin</option><option>Manager</option></select>
    <span style="font-size:.75rem;color:var(--hr-gray-500);margin-left:auto;"><strong>{{ date('F Y') }}</strong></span>
</div>
<div class="hr-card">
    <div class="hr-card-hdr">
        <span><i class="fas fa-calendar-alt me-1"></i> {{ date('F Y') }}</span>
        <div class="d-flex gap-2">
            <button class="hr-btn hr-btn-sm hr-btn-outline"><i class="fas fa-chevron-left"></i></button>
            <button class="hr-btn hr-btn-sm hr-btn-outline">Today</button>
            <button class="hr-btn hr-btn-sm hr-btn-outline"><i class="fas fa-chevron-right"></i></button>
        </div>
    </div>
    <div class="hr-card-bd p-2">
        <div class="hr-cal">
            <div class="hr-cal-hdr">Sun</div><div class="hr-cal-hdr">Mon</div><div class="hr-cal-hdr">Tue</div><div class="hr-cal-hdr">Wed</div><div class="hr-cal-hdr">Thu</div><div class="hr-cal-hdr">Fri</div><div class="hr-cal-hdr">Sat</div>
            @php
            $fd = \Carbon\Carbon::create($year, $month, 1);
            $pad = $fd->dayOfWeek;
            $dim = $fd->daysInMonth;
            $total = $pad + $dim;
            $total += (7 - ($total % 7 === 0 ? 7 : $total % 7));
            @endphp
            @for($i=0;$i<$pad;$i++)
            <div class="hr-cal-day other-month"></div>
            @endfor
            @for($d=1;$d<=$dim;$d++)
            @php
            $date = \Carbon\Carbon::create($year,$month,$d);
            $dk = $date->format('Y-m-d');
            $dd = collect($calendar)->firstWhere('date_key', $dk);
            $isWk = $date->isWeekend();
            $isTd = $date->isToday();
            $hld = collect($holidays)->firstWhere('date', $dk);
            $isPd = $paydayDates->contains($dk);
            $hasData = $dd && ($dd['has_data'] ?? false);
            @endphp
            <div class="hr-cal-day {{ $isWk?'weekend':'' }} {{ $isTd?'today':'' }}" onclick="showDayDetail('{{ $dk }}',this)">
                <div class="day-num">{{ $d }}</div>
                @if($hasData)
                @if(($dd['attendance']['total_present']??0) > 0)<div><span class="day-dot" style="background:var(--hr-green);"></span>{{ $dd['attendance']['total_present'] }}</div>@endif
                @if(($dd['attendance']['total_absent']??0) > 0)<div><span class="day-dot" style="background:var(--hr-red);"></span>{{ $dd['attendance']['total_absent'] }}</div>@endif
                @if(($dd['attendance']['total_late']??0) > 0)<div><span class="day-dot" style="background:var(--hr-yellow);"></span>{{ $dd['attendance']['total_late'] }}</div>@endif
                @if(($dd['attendance']['total_on_leave']??0) > 0)<div><span class="day-dot" style="background:var(--hr-purple);"></span>{{ $dd['attendance']['total_on_leave'] }}</div>@endif
                @endif
                @if($hld)<div style="color:var(--hr-blue);font-size:.6rem;font-weight:500;">{{ Str::limit($hld['name'],10) }}</div>@endif
                @if($isPd)<div style="color:var(--hr-gold);font-size:.6rem;font-weight:600;">Payday</div>@endif
            </div>
            @endfor
            @php $used = $pad + $dim; @endphp
            @for($i=$used;$i<$total;$i++)
            <div class="hr-cal-day other-month"></div>
            @endfor
        </div>
    </div>
</div>
</div>
<!-- END CALENDAR TAB -->

<!-- TAB: TEAM -->
<div id="tab-team" class="hr-tab-content px-3 px-md-4 py-3">
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h5 class="mb-0 fw-bold" style="font-size:1rem;"><i class="fas fa-users me-2" style="color:var(--hr-blue);"></i> Team ({{ $totalEmployees }})</h5>
    <input type="text" class="form-control form-control-sm" placeholder="Search employee..." style="width:200px;border:1px solid var(--hr-gray-300);border-radius:6px;" onkeyup="filterTeam(this.value)">
</div>
<div id="team-grid" class="row g-2">
    @foreach($employees as $e)
    @php
    $hr = $e->employeeHrDetails;
    $trec = $todayAtt->firstWhere('employee_id', $e->id);
    $ac = ['#4361ee','#10b981','#f59e0b','#ef4444','#8b5cf6','#ec4899','#06b6d4','#f97316'][crc32($e->id)%8];
    $pos = $hr?->position ?? ($hr?->job_title ?? ucwords(str_replace('_',' ',$e->role)));
    $ini = collect(explode(' ',$e->name))->map(fn($w)=>substr($w,0,1))->take(2)->join('');
    @endphp
    <div class="col-6 col-md-4 col-lg-3 team-card" data-name="{{ strtolower($e->name) }}">
        <div class="hr-teammate" onclick="showEmployee('{{ $e->id }}','{{ addslashes($e->name) }}','{{ addslashes($pos) }}','{{ $ac }}','{{ $ini }}')">
            <div class="hr-teammate-avatar" style="background:{{ $ac }};">{{ $ini }}</div>
            <div class="flex-grow-1 min-w-0">
                <div class="hr-teammate-name text-truncate">{{ $e->name }}</div>
                <div class="hr-teammate-role text-truncate">{{ $pos }}</div>
                <span class="hr-badge hr-badge-{{ $trec ? ($trec->status=='present'?'green':($trec->status=='late'?'yellow':($trec->status=='absent'?'red':'gray'))) : 'gray' }}" style="font-size:.6rem;">
                    <i class="fas fa-{{ $trec && $trec->status=='present'?'check-circle':($trec && $trec->status=='late'?'clock':($trec && $trec->status=='absent'?'times-circle':'question-circle')) }}"></i>
                    {{ $trec ? ucfirst($trec->status) : 'No record' }}
                </span>
            </div>
        </div>
    </div>
    @endforeach
</div>
@if($employees->isEmpty())
<div class="text-center py-5 text-muted"><i class="fas fa-users fa-2x mb-2 d-block"></i>No employees found</div>
@endif
</div>
<!-- END TEAM TAB -->

<!-- TAB: PAYROLL -->
<div id="tab-payroll" class="hr-tab-content px-3 px-md-4 py-3">
<div class="row g-3 mb-4">
    @php
    $ps = [
        ['i'=>'calendar-alt','l'=>'Current Cutoff','v'=>$currentPayroll?->period_name ?? '—','s'=>$currentPayroll ? $currentPayroll->start_date->format('M d').' - '.$currentPayroll->end_date->format('M d, Y') : 'No active period','c'=>'#4361ee','b'=>'#eef0ff'],
        ['i'=>'users','l'=>'Employees','v'=>$currentPayroll?->employee_count ?? $totalEmployees,'s'=>'This cutoff','c'=>'#10b981','b'=>'#d1fae5'],
        ['i'=>'money-bill-wave','l'=>'Total Payroll','v'=>'₱'.number_format($totalPayrollAmount,2),'s'=>'Processing: '.$allPayrollPeriods->where('status','processing')->count().' / Draft: '.$allPayrollPeriods->where('status','draft')->count(),'c'=>'#f59e0b','b'=>'#fef3c7'],
        ['i'=>'check-circle','l'=>'Paid Periods','v'=>$allPayrollPeriods->where('status','paid')->count(),'s'=>'Completed','c'=>'#10b981','b'=>'#d1fae5'],
    ];
    @endphp
    @foreach($ps as $c)
    <div class="col-6 col-md-3">
        <div class="hr-stat">
            <div class="d-flex justify-content-between align-items-start mb-1">
                <span class="hr-stat-label">{{ $c['l'] }}</span>
                <div class="hr-stat-icon" style="background:{{ $c['b'] }};color:{{ $c['c'] }};"><i class="fas fa-{{ $c['i'] }}"></i></div>
            </div>
            <div class="hr-stat-val">{{ $c['v'] }}</div>
            <small class="hr-stat-sub">{{ $c['s'] }}</small>
        </div>
    </div>
    @endforeach
</div>

<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="hr-card">
            <div class="hr-card-hdr"><span><i class="fas fa-list me-1"></i> Payroll Periods</span>
                <div class="d-flex gap-2">
                    <button class="hr-btn hr-btn-primary hr-btn-sm" onclick="alert('Generate Payroll - coming soon')"><i class="fas fa-plus"></i> Generate</button>
                    <a href="{{ route('hr-payroll.payroll.periods') }}" class="hr-btn hr-btn-outline hr-btn-sm">Manage</a>
                </div>
            </div>
            <div class="hr-card-bd p-0">
                <table class="hr-table">
                    <thead><tr><th>Period</th><th>Cutoff</th><th>Pay Date</th><th>Status</th><th class="text-end">Total Net</th></tr></thead>
                    <tbody>
                        @forelse($allPayrollPeriods as $p)
                        <tr>
                            <td><strong>{{ $p->period_name }}</strong></td>
                            <td style="font-size:.7rem;">{{ $p->start_date->format('M d') }} - {{ $p->end_date->format('M d, Y') }}</td>
                            <td>{{ $p->pay_date->format('M d, Y') }}</td>
                            <td><span class="hr-badge hr-badge-{{ $p->status == 'paid' ? 'green' : ($p->status == 'processing' ? 'yellow' : 'gray') }}">{{ ucfirst($p->status) }}</span></td>
                            <td class="text-end fw-semibold">₱{{ number_format($p->total_net,2) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center py-3 text-muted small">No payroll periods yet</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="hr-card">
    <div class="hr-card-hdr"><span><i class="fas fa-calculator me-1"></i> Payroll Actions</span></div>
    <div class="hr-card-bd">
        <div class="row g-2">
            <div class="col-4 col-md-2"><button class="hr-btn hr-btn-outline w-100" style="flex-direction:column;padding:10px 0;" onclick="alert('View payslips - coming soon')"><i class="fas fa-file-invoice" style="font-size:1.2rem;margin-bottom:4px;"></i><div>Payslips</div></button></div>
            <div class="col-4 col-md-2"><button class="hr-btn hr-btn-outline w-100" style="flex-direction:column;padding:10px 0;" onclick="alert('Export PDF - coming soon')"><i class="fas fa-file-pdf" style="font-size:1.2rem;margin-bottom:4px;"></i><div>PDF</div></button></div>
            <div class="col-4 col-md-2"><button class="hr-btn hr-btn-outline w-100" style="flex-direction:column;padding:10px 0;" onclick="alert('Export Excel - coming soon')"><i class="fas fa-file-excel" style="font-size:1.2rem;margin-bottom:4px;"></i><div>Excel</div></button></div>
            <div class="col-4 col-md-2"><button class="hr-btn hr-btn-outline w-100" style="flex-direction:column;padding:10px 0;" onclick="alert('Payroll computation - coming soon')"><i class="fas fa-cogs" style="font-size:1.2rem;margin-bottom:4px;"></i><div>Compute</div></button></div>
        </div>
    </div>
</div>
</div>
<!-- END PAYROLL TAB -->

<!-- TAB: REQUESTS -->
<div id="tab-requests" class="hr-tab-content px-3 px-md-4 py-3">
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h5 class="mb-0 fw-bold" style="font-size:1rem;"><i class="fas fa-clipboard-list me-2" style="color:var(--hr-blue);"></i> Requests & Approvals</h5>
    <span class="hr-badge hr-badge-red">{{ $pendingRequests }} pending</span>
</div>

<div class="row g-3">
    <div class="col-lg-6">
        <div class="hr-card">
            <div class="hr-card-hdr"><span><i class="fas fa-plane me-1"></i> Leave Requests</span><a href="{{ route('hr-payroll.leave') }}" class="hr-btn hr-btn-outline hr-btn-sm">View All</a></div>
            <div class="hr-card-bd p-0">
                <table class="hr-table">
                    <thead><tr><th>Employee</th><th>Type</th><th>Dates</th><th>Status</th><th></th></tr></thead>
                    <tbody>
                        @forelse($leaveRequests->take(8) as $lr)
                        <tr>
                            <td><strong style="font-size:.73rem;">{{ $lr->employee->name ?? 'Unknown' }}</strong></td>
                            <td><span class="hr-badge hr-badge-blue" style="font-size:.6rem;">{{ ucwords(str_replace('_',' ',$lr->leave_type ?? 'Leave')) }}</span></td>
                            <td style="font-size:.68rem;">{{ \Carbon\Carbon::parse($lr->start_date)->format('M d') }} - {{ \Carbon\Carbon::parse($lr->end_date)->format('M d') }}</td>
                            <td><span class="hr-badge hr-badge-{{ $lr->status=='pending'?'yellow':($lr->status=='approved'?'green':'red') }}">{{ ucfirst($lr->status) }}</span></td>
                            <td>
                                @if($lr->status == 'pending')
                                <div class="d-flex gap-1">
                                    <button class="hr-btn hr-btn-success hr-btn-sm" onclick="alert('Approved leave #{{ $lr->id }}')"><i class="fas fa-check"></i></button>
                                    <button class="hr-btn hr-btn-danger hr-btn-sm" onclick="alert('Rejected leave #{{ $lr->id }}')"><i class="fas fa-times"></i></button>
                                </div>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center py-3 text-muted small">No leave requests yet</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="hr-card mb-3">
            <div class="hr-card-hdr"><span><i class="fas fa-clock me-1"></i> Time Attendance Approval</span><a href="{{ route('hr-payroll.time-attendance') }}" class="hr-btn hr-btn-outline hr-btn-sm">View All</a></div>
            <div class="hr-card-bd">
                @php
                $pendingTimes = \App\Models\TimeAttendance::pendingApproval()->with('employee')->latest()->take(5)->get();
                @endphp
                @forelse($pendingTimes as $tr)
                <div class="d-flex justify-content-between align-items-center py-2 border-bottom border-light">
                    <div>
                        <strong style="font-size:.78rem;">{{ $tr->employee->name ?? 'Unknown' }}</strong>
                        <div style="font-size:.68rem;color:var(--hr-gray-500);">{{ \Carbon\Carbon::parse($tr->work_date)->format('M d, Y') }} - {{ $tr->clock_in ? \Carbon\Carbon::parse($tr->clock_in)->format('h:i A') : 'No time-in' }}</div>
                    </div>
                    <div class="d-flex gap-1">
                        <button class="hr-btn hr-btn-success hr-btn-sm" onclick="alert('Approved time #{{ $tr->id }}')"><i class="fas fa-check"></i></button>
                        <button class="hr-btn hr-btn-danger hr-btn-sm" onclick="alert('Rejected time #{{ $tr->id }}')"><i class="fas fa-times"></i></button>
                    </div>
                </div>
                @empty
                <div class="text-center py-3 text-muted small">No pending time approvals</div>
                @endforelse
            </div>
        </div>

        <div class="hr-card">
            <div class="hr-card-hdr"><span><i class="fas fa-edit me-1"></i> Other Requests</span></div>
            <div class="hr-card-bd p-0">
                <table class="hr-table">
                    <thead><tr><th>Type</th><th>Description</th><th>Status</th></tr></thead>
                    <tbody>
                        <tr><td><span class="hr-badge hr-badge-blue">Overtime</span></td><td style="font-size:.73rem;">Overtime request tracking</td><td><span class="hr-badge hr-badge-gray">Setup pending</span></td></tr>
                        <tr><td><span class="hr-badge hr-badge-purple">Correction</span></td><td style="font-size:.73rem;">Attendance correction</td><td><span class="hr-badge hr-badge-gray">Setup pending</span></td></tr>
                        <tr><td><span class="hr-badge hr-badge-yellow">Schedule</span></td><td style="font-size:.73rem;">Schedule change request</td><td><span class="hr-badge hr-badge-gray">Setup pending</span></td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</div>
<!-- END REQUESTS TAB -->

<!-- TAB: SETTINGS -->
<div id="tab-settings" class="hr-tab-content px-3 px-md-4 py-3">
<div class="row g-3">
    <div class="col-lg-6">
        <div class="hr-card">
            <div class="hr-card-hdr"><span><i class="fas fa-clock me-1"></i> Work Schedule</span></div>
            <div class="hr-card-bd">
                <div class="hr-settings-group">
                    <div class="row g-2">
                        <div class="col-6">
                            <label>Morning Shift Start</label>
                            <input type="time" class="form-control form-control-sm" value="08:00">
                        </div>
                        <div class="col-6">
                            <label>Morning Shift End</label>
                            <input type="time" class="form-control form-control-sm" value="12:00">
                        </div>
                        <div class="col-6 mt-2">
                            <label>Afternoon Shift Start</label>
                            <input type="time" class="form-control form-control-sm" value="13:00">
                        </div>
                        <div class="col-6 mt-2">
                            <label>Afternoon Shift End</label>
                            <input type="time" class="form-control form-control-sm" value="17:00">
                        </div>
                        <div class="col-12 mt-2">
                            <label>Work Days</label>
                            <div class="d-flex gap-2 flex-wrap mt-1">
                                @foreach(['Mon','Tue','Wed','Thu','Fri','Sat'] as $day)
                                <label class="d-flex align-items-center gap-1" style="font-size:.73rem;"><input type="checkbox" checked> {{ $day }}</label>
                                @endforeach
                                <label class="d-flex align-items-center gap-1" style="font-size:.73rem;"><input type="checkbox"> Sun</label>
                            </div>
                        </div>
                    </div>
                    <button class="hr-btn hr-btn-primary hr-btn-sm mt-2" onclick="alert('Schedule saved')"><i class="fas fa-save"></i> Save</button>
                </div>
            </div>
        </div>

        <div class="hr-card mt-3">
            <div class="hr-card-hdr"><span><i class="fas fa-gavel me-1"></i> Late & Overtime Rules</span></div>
            <div class="hr-card-bd">
                <div class="hr-settings-group">
                    <div class="row g-2">
                        <div class="col-6">
                            <label>Grace Period (minutes)</label>
                            <input type="number" class="form-control form-control-sm" value="15">
                        </div>
                        <div class="col-6">
                            <label>Overtime Rate</label>
                            <input type="text" class="form-control form-control-sm" value="1.25x">
                        </div>
                        <div class="col-6 mt-2">
                            <label>Late Threshold (minutes)</label>
                            <input type="number" class="form-control form-control-sm" value="30">
                        </div>
                        <div class="col-6 mt-2">
                            <label>Overtime Threshold</label>
                            <input type="number" class="form-control form-control-sm" value="8" placeholder="Hours">
                        </div>
                    </div>
                    <button class="hr-btn hr-btn-primary hr-btn-sm mt-2" onclick="alert('Rules saved')"><i class="fas fa-save"></i> Save</button>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="hr-card">
            <div class="hr-card-hdr"><span><i class="fas fa-money-check me-1"></i> Payroll Dates</span></div>
            <div class="hr-card-bd">
                <div class="hr-settings-group">
                    <div class="row g-2">
                        <div class="col-6">
                            <label>1st Cutoff Start</label>
                            <input type="number" class="form-control form-control-sm" value="1" placeholder="Day of month">
                        </div>
                        <div class="col-6">
                            <label>1st Cutoff End</label>
                            <input type="number" class="form-control form-control-sm" value="15">
                        </div>
                        <div class="col-6 mt-2">
                            <label>1st Payday</label>
                            <input type="number" class="form-control form-control-sm" value="20">
                        </div>
                        <div class="col-6 mt-2">
                            <label>2nd Cutoff Start</label>
                            <input type="number" class="form-control form-control-sm" value="16">
                        </div>
                        <div class="col-6 mt-2">
                            <label>2nd Cutoff End</label>
                            <input type="text" class="form-control form-control-sm" value="Last day">
                        </div>
                        <div class="col-6 mt-2">
                            <label>2nd Payday</label>
                            <input type="number" class="form-control form-control-sm" value="5">
                        </div>
                    </div>
                    <button class="hr-btn hr-btn-primary hr-btn-sm mt-2" onclick="alert('Payroll dates saved')"><i class="fas fa-save"></i> Save</button>
                </div>
            </div>
        </div>

        <div class="hr-card mt-3">
            <div class="hr-card-hdr"><span><i class="fas fa-calculator me-1"></i> Deductions (Philippines)</span></div>
            <div class="hr-card-bd">
                <div class="hr-settings-group">
                    <div class="row g-2">
                        @php
                        $dedTypes = [
                            ['n'=>'SSS', 'd'=>'Social Security System'],
                            ['n'=>'PhilHealth', 'd'=>'Philippine Health Insurance'],
                            ['n'=>'Pag-IBIG', 'd'=>'HDMF / Pag-IBIG Fund'],
                            ['n'=>'Cash Advance', 'd'=>'Employee cash advance'],
                        ];
                        @endphp
                        @foreach($dedTypes as $dt)
                        <div class="col-6">
                            <label class="d-flex align-items-center gap-1" style="font-size:.73rem;cursor:pointer;">
                                <input type="checkbox" checked> <strong>{{ $dt['n'] }}</strong>
                                <span style="color:var(--hr-gray-400);font-size:.65rem;">{{ $dt['d'] }}</span>
                            </label>
                        </div>
                        @endforeach
                    </div>
                    <button class="hr-btn hr-btn-primary hr-btn-sm mt-2" onclick="alert('Deductions saved')"><i class="fas fa-save"></i> Save</button>
                </div>
            </div>
        </div>

        <div class="hr-card mt-3">
            <div class="hr-card-hdr"><span><i class="fas fa-calendar-day me-1"></i> Philippine Holidays ({{ date('Y') }})</span></div>
            <div class="hr-card-bd p-0">
                <table class="hr-table">
                    <thead><tr><th>Date</th><th>Holiday</th><th>Type</th></tr></thead>
                    <tbody>
                        @forelse($holidays as $h)
                        <tr>
                            <td style="font-size:.72rem;">{{ \Carbon\Carbon::parse($h['date'])->format('M d, Y') }}</td>
                            <td><strong style="font-size:.75rem;">{{ $h['name'] }}</strong></td>
                            <td><span class="hr-badge hr-badge-blue" style="font-size:.6rem;">{{ $h['type'] }}</span></td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="text-center py-3 text-muted small">No holidays loaded</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</div>
<!-- END SETTINGS TAB -->
</div>
<!-- END WRAP -->

<!-- EMPLOYEE PROFILE MODAL -->
<div id="hr-modal-overlay" class="hr-modal-overlay" onclick="if(event.target===this)closeEmployeeModal()">
<div class="hr-modal">
    <div class="hr-modal-hdr">
        <h5><i class="fas fa-user me-2" style="color:var(--hr-blue);"></i> Employee Profile</h5>
        <button class="close" onclick="closeEmployeeModal()">&times;</button>
    </div>
    <div class="hr-modal-bd" id="hr-modal-body">
        <div class="text-center py-4 text-muted">
            <i class="fas fa-spinner fa-spin fa-2x"></i>
            <div class="mt-2 small">Loading employee details...</div>
        </div>
    </div>
</div>
</div>
@endsection

@push('scripts')
<script>
function switchTab(name) {
    document.querySelectorAll('.hr-tab-content').forEach(function(t) { t.classList.remove('active'); });
    document.querySelectorAll('.hr-tab').forEach(function(t) { t.classList.remove('active'); });
    var tabEl = document.getElementById('tab-' + name);
    if (tabEl) tabEl.classList.add('active');
    document.querySelectorAll('.hr-tab').forEach(function(t) {
        if (t.getAttribute('onclick') && t.getAttribute('onclick').indexOf("'" + name + "'") > -1) {
            t.classList.add('active');
        }
    });
    window.location.hash = '#' + name;
}

window.addEventListener('DOMContentLoaded', function() {
    if (window.location.hash) {
        var tab = window.location.hash.replace('#', '');
        if (document.getElementById('tab-' + tab)) switchTab(tab);
    }
});

function filterTeam(val) {
    var q = val.toLowerCase();
    document.querySelectorAll('.team-card').forEach(function(card) {
        card.style.display = (card.getAttribute('data-name') || '').indexOf(q) > -1 ? '' : 'none';
    });
}

function showDayDetail(dateKey, el) {
    el.style.outline = '2px solid var(--hr-blue)';
    setTimeout(function() { el.style.outline = ''; }, 500);
}

function showEmployee(id, name, position, color, initials) {
    var modal = document.getElementById('hr-modal-overlay');
    var body = document.getElementById('hr-modal-body');
    modal.classList.add('show');
    body.innerHTML = '<div class="text-center mb-3">'
        + '<div style="width:56px;height:56px;border-radius:50%;background:' + color + ';display:inline-flex;align-items:center;justify-content:center;font-size:1.2rem;font-weight:700;color:white;margin-bottom:8px;">' + initials + '</div>'
        + '<h5 style="font-size:.95rem;font-weight:600;margin:0;">' + name + '</h5>'
        + '<span style="color:var(--hr-gray-500);font-size:.78rem;">' + position + '</span></div>'
        + '<div class="row g-2 mb-3">'
        + '<div class="col-4"><div class="hr-stat" style="padding:.5rem;text-align:center;"><div class="hr-stat-val" style="font-size:1rem;">—</div><div class="hr-stat-label">This Period</div></div></div>'
        + '<div class="col-4"><div class="hr-stat" style="padding:.5rem;text-align:center;"><div class="hr-stat-val" style="font-size:1rem;">—</div><div class="hr-stat-label">Leaves</div></div></div>'
        + '<div class="col-4"><div class="hr-stat" style="padding:.5rem;text-align:center;"><div class="hr-stat-val" style="font-size:1rem;">—</div><div class="hr-stat-label">Deductions</div></div></div></div>'
        + '<div style="font-size:.75rem;color:var(--hr-gray-500);">'
        + '<p><i class="fas fa-info-circle me-1"></i> Full profile with attendance calendar, payroll history, leave history, schedule, and deductions is available from the Employee Management page.</p>'
        + '<a href="{{ route('hr-payroll.employees') }}" class="hr-btn hr-btn-primary hr-btn-sm"><i class="fas fa-external-link-alt"></i> View Full Profile</a></div>';
}

function closeEmployeeModal() {
    document.getElementById('hr-modal-overlay').classList.remove('show');
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeEmployeeModal();
});
</script>
@endpush
