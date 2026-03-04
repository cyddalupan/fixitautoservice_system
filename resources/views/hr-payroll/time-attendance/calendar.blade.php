@extends('layouts.app')

@section('title', 'Time & Attendance Calendar')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">Time & Attendance Calendar</h1>
                    <p class="text-muted">Visual attendance tracking with daily summaries</p>
                </div>
                <div class="btn-group">
                    <a href="{{ route('hr-payroll.time-attendance') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-table"></i> Table View
                    </a>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTimeEntryModal">
                        <i class="fas fa-plus"></i> Add Time Entry
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Month Navigation & Filters -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-body">
                    <div class="row align-items-center">
                        <!-- Month Navigation -->
                        <div class="col-md-4">
                            <div class="d-flex align-items-center">
                                <a href="{{ route('hr-payroll.time-attendance', ['view' => 'calendar', 'year' => $prevMonth->year, 'month' => $prevMonth->month]) }}" 
                                   class="btn btn-outline-secondary btn-sm me-2">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                                
                                <h4 class="mb-0 me-3">
                                    {{ \Carbon\Carbon::create($year, $month, 1)->format('F Y') }}
                                </h4>
                                
                                <a href="{{ route('hr-payroll.time-attendance', ['view' => 'calendar', 'year' => $nextMonth->year, 'month' => $nextMonth->month]) }}" 
                                   class="btn btn-outline-secondary btn-sm">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                                
                                <a href="{{ route('hr-payroll.time-attendance', ['view' => 'calendar']) }}" 
                                   class="btn btn-outline-primary btn-sm ms-3">
                                    <i class="fas fa-calendar-day"></i> Today
                                </a>
                            </div>
                        </div>
                        
                        <!-- Filters -->
                        <div class="col-md-8">
                            <form method="GET" action="{{ route('hr-payroll.time-attendance') }}" class="row g-2">
                                <input type="hidden" name="view" value="calendar">
                                <input type="hidden" name="year" value="{{ $year }}">
                                <input type="hidden" name="month" value="{{ $month }}">
                                
                                <div class="col-md-5">
                                    <select name="employee_id" id="employeeFilter" class="form-control form-control-sm">
                                        <option value="">All Employees</option>
                                        @foreach($employees as $employee)
                                            <option value="{{ $employee->id }}" {{ request('employee_id') == $employee->id ? 'selected' : '' }}>
                                                {{ $employee->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="input-group input-group-sm">
                                        <input type="month" name="month_year" class="form-control" 
                                               value="{{ $year }}-{{ str_pad($month, 2, '0', STR_PAD_LEFT) }}"
                                               onchange="this.form.submit()">
                                    </div>
                                </div>
                                
                                <div class="col-md-3">
                                    <div class="d-flex">
                                        <button type="submit" class="btn btn-primary btn-sm me-2">
                                            <i class="fas fa-filter"></i> Filter
                                        </button>
                                        <a href="{{ route('hr-payroll.time-attendance', ['view' => 'calendar']) }}" class="btn btn-secondary btn-sm">
                                            <i class="fas fa-redo"></i> Reset
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Legend -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-body py-2">
                    <div class="d-flex flex-wrap align-items-center">
                        <span class="me-3"><strong>Legend:</strong></span>
                        <span class="badge bg-success me-2 mb-1"><i class="fas fa-user-check"></i> Present</span>
                        <span class="badge bg-danger me-2 mb-1"><i class="fas fa-user-times"></i> Absent</span>
                        <span class="badge bg-warning me-2 mb-1"><i class="fas fa-clock"></i> Late</span>
                        <span class="badge bg-info me-2 mb-1"><i class="fas fa-umbrella-beach"></i> On Leave</span>
                        <span class="badge bg-secondary me-2 mb-1"><i class="fas fa-calendar-times"></i> Weekend</span>
                        <span class="badge bg-primary me-2 mb-1"><i class="fas fa-calendar-day"></i> Today</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Calendar Grid -->
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-body p-0">
                    <!-- Weekday Headers -->
                    <div class="row g-0 border-bottom">
                        @foreach(['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $day)
                            <div class="col calendar-day-header">
                                <div class="text-center py-3 bg-light">
                                    <strong class="text-uppercase">{{ $day }}</strong>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
                    <!-- Calendar Days -->
                    @php
                        $weeks = array_chunk($calendar, 7);
                    @endphp
                    
                    @foreach($weeks as $week)
                        <div class="row g-0 border-bottom">
                            @foreach($week as $day)
                                @php
                                    $dayClasses = ['calendar-day'];
                                    if ($day['is_weekend']) {
                                        $dayClasses[] = 'calendar-weekend';
                                    }
                                    if ($day['is_today']) {
                                        $dayClasses[] = 'calendar-today';
                                    }
                                    if (!$day['has_data']) {
                                        $dayClasses[] = 'calendar-no-data';
                                    }
                                @endphp
                                
                                <div class="col {{ implode(' ', $dayClasses) }}" 
                                     data-bs-toggle="modal" 
                                     data-bs-target="#dayDetailsModal"
                                     data-date="{{ $day['date_key'] }}"
                                     data-present="{{ json_encode($day['attendance']['present']) }}"
                                     data-absent="{{ json_encode($day['attendance']['absent']) }}"
                                     data-late="{{ json_encode($day['attendance']['late']) }}"
                                     data-on-leave="{{ json_encode($day['attendance']['on_leave']) }}"
                                     onclick="showDayDetails(this)">
                                    <div class="calendar-day-content p-2">
                                        <!-- Date Header -->
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <div>
                                                <span class="calendar-date-number {{ $day['is_today'] ? 'calendar-today-date' : '' }}">
                                                    {{ $day['day'] }}
                                                </span>
                                                @if($day['is_today'])
                                                    <span class="badge bg-primary badge-sm ms-1">Today</span>
                                                @endif
                                            </div>
                                            <div class="calendar-day-of-week">
                                                {{ $day['day_of_week'] }}
                                            </div>
                                        </div>
                                        
                                        <!-- Attendance Summary -->
                                        @if($day['has_data'])
                                            <div class="calendar-attendance-summary">
                                                <!-- Present -->
                                                @if($day['attendance']['total_present'] > 0)
                                                    <div class="calendar-stat-item mb-1">
                                                        <div class="d-flex justify-content-between">
                                                            <span class="calendar-stat-label">
                                                                <i class="fas fa-user-check text-success me-1"></i>
                                                                Present
                                                            </span>
                                                            <span class="calendar-stat-value text-success">
                                                                {{ $day['attendance']['total_present'] }}
                                                            </span>
                                                        </div>
                                                        <div class="progress calendar-progress" style="height: 4px;">
                                                            <div class="progress-bar bg-success" 
                                                                 style="width: {{ $day['present_percentage'] }}%"></div>
                                                        </div>
                                                    </div>
                                                @endif
                                                
                                                <!-- Absent -->
                                                @if($day['attendance']['total_absent'] > 0)
                                                    <div class="calendar-stat-item mb-1">
                                                        <div class="d-flex justify-content-between">
                                                            <span class="calendar-stat-label">
                                                                <i class="fas fa-user-times text-danger me-1"></i>
                                                                Absent
                                                            </span>
                                                            <span class="calendar-stat-value text-danger">
                                                                {{ $day['attendance']['total_absent'] }}
                                                            </span>
                                                        </div>
                                                        <div class="progress calendar-progress" style="height: 4px;">
                                                            <div class="progress-bar bg-danger" 
                                                                 style="width: {{ $day['absent_percentage'] }}%"></div>
                                                        </div>
                                                    </div>
                                                @endif
                                                
                                                <!-- Late -->
                                                @if($day['attendance']['total_late'] > 0)
                                                    <div class="calendar-stat-item mb-1">
                                                        <div class="d-flex justify-content-between">
                                                            <span class="calendar-stat-label">
                                                                <i class="fas fa-clock text-warning me-1"></i>
                                                                Late
                                                            </span>
                                                            <span class="calendar-stat-value text-warning">
                                                                {{ $day['attendance']['total_late'] }}
                                                            </span>
                                                        </div>
                                                        <div class="progress calendar-progress" style="height: 4px;">
                                                            <div class="progress-bar bg-warning" 
                                                                 style="width: {{ $day['late_percentage'] }}%"></div>
                                                        </div>
                                                    </div>
                                                @endif
                                                
                                                <!-- On Leave -->
                                                @if($day['attendance']['total_on_leave'] > 0)
                                                    <div class="calendar-stat-item mb-1">
                                                        <div class="d-flex justify-content-between">
                                                            <span class="calendar-stat-label">
                                                                <i class="fas fa-umbrella-beach text-info me-1"></i>
                                                                On Leave
                                                            </span>
                                                            <span class="calendar-stat-value text-info">
                                                                {{ $day['attendance']['total_on_leave'] }}
                                                            </span>
                                                        </div>
                                                        <div class="progress calendar-progress" style="height: 4px;">
                                                            <div class="progress-bar bg-info" 
                                                                 style="width: {{ $day['on_leave_percentage'] }}%"></div>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        @else
                                            <div class="text-center text-muted py-3">
                                                <i class="fas fa-calendar-times fa-lg mb-2"></i>
                                                <div class="small">No attendance data</div>
                                            </div>
                                        @endif
                                        
                                        <!-- Total Employees -->
                                        <div class="calendar-total-employees mt-2 pt-2 border-top text-center">
                                            <small class="text-muted">
                                                <i class="fas fa-users me-1"></i>
                                                {{ $day['attendance']['total_employees'] }}/{{ $totalEmployees }}
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly Summary -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-chart-bar me-2"></i>
                        Monthly Attendance Summary - {{ \Carbon\Carbon::create($year, $month, 1)->format('F Y') }}
                    </h6>
                </div>
                <div class="card-body">
                    @php
                        $monthlyPresent = collect($calendar)->sum('attendance.total_present');
                        $monthlyAbsent = collect($calendar)->sum('attendance.total_absent');
                        $monthlyLate = collect($calendar)->sum('attendance.total_late');
                        $monthlyOnLeave = collect($calendar)->sum('attendance.total_on_leave');
                        $monthlyTotal = $monthlyPresent + $monthlyAbsent + $monthlyLate + $monthlyOnLeave;
                        $workingDays = collect($calendar)->where('is_weekend', false)->count();
                        $totalPossible = $totalEmployees * $workingDays;
                        $attendanceRate = $totalPossible > 0 ? ($monthlyPresent / $totalPossible) * 100 : 0;
                    @endphp
                    
                    <div class="row">
                        <div class="col-md-3 text-center">
                            <div class="display-4 text-success">{{ $monthlyPresent }}</div>
                            <div class="text-muted">Total Present Days</div>
                            <div class="progress mt-2" style="height: 10px;">
                                <div class="progress-bar bg-success" 
                                     style="width: {{ $monthlyTotal > 0 ? ($monthlyPresent / $monthlyTotal * 100) : 0 }}%"></div>
                            </div>
                        </div>
                        
                        <div class="col-md-3 text-center">
                            <div class="display-4 text-danger">{{ $monthlyAbsent }}</div>
                            <div class="text-muted">Total Absent Days</div>
                            <div class="progress mt-2" style="height: 10px;">
                                <div class="progress-bar bg-danger" 
                                     style="width: {{ $monthlyTotal > 0 ? ($monthlyAbsent / $monthlyTotal * 100) : 0 }}%"></div>
                            </div>
                        </div>
                        
                        <div class="col-md-3 text-center">
                            <div class="display-4 text-warning">{{ $monthlyLate }}</div>
                            <div class="text-muted">Total Late Days</div>
                            <div class="progress mt-2" style="height: 10px;">
                                <div class="progress-bar bg-warning" 
                                     style="width: {{ $monthlyTotal > 0 ? ($monthlyLate / $monthlyTotal * 100) : 0 }}%"></div>
                            </div>
                        </div>
                        
                        <div class="col-md-3 text-center">
                            <div class="display-4 text-info">{{ $monthlyOnLeave }}</div>
                            <div class="text-muted">Total Leave Days</div>
                            <div class="progress mt-2" style="height: 10px;">
                                <div class="progress-bar bg-info" 
                                     style="width: {{ $monthlyTotal > 0 ? ($monthlyOnLeave / $monthlyTotal * 100) : 0 }}%"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="card border-left-primary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                Monthly Attendance Rate
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                {{ round($attendanceRate, 1) }}%
                                            </div>
                                            <div class="mt-2 mb-0 text-muted text-xs">
                                                <span>Based on {{ $workingDays }} working days</span>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-percentage fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card border-left-success shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                                Average Daily Attendance
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                {{ $workingDays > 0 ? round($monthlyPresent / $workingDays, 1) : 0 }}
                                            </div>
                                            <div class="mt-2 mb-0 text-muted text-xs">
                                                <span>Present employees per day</span>
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
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Day Details Modal -->
<div class="modal fade" id="dayDetailsModal" tabindex="-1" aria-labelledby="dayDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="dayDetailsModalLabel">
                    <i class="fas fa-calendar-day me-2"></i>
                    <span id="modalDateTitle">Attendance Details</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <!-- Present Employees -->
                    <div class="col-md-6 mb-4">
                        <div class="card border-success h-100">
                            <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">
                                    <i class="fas fa-user-check me-2"></i>
                                    Present Employees
                                    <span class="badge bg-light text-success ms-2" id="presentCount">0</span>
                                </h6>
                            </div>
                            <div class="card-body">
                                <div id="presentList" class="employee-list">
                                    <!-- Present employees will be loaded here -->
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Absent Employees -->
                    <div class="col-md-6 mb-4">
                        <div class="card border-danger h-100">
                            <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">
                                    <i class="fas fa-user-times me-2"></i>
                                    Absent Employees
                                    <span class="badge bg-light text-danger ms-2" id="absentCount">0</span>
                                </h6>
                            </div>
                            <div class="card-body">
                                <div id="absentList" class="employee-list">
                                    <!-- Absent employees will be loaded here -->
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Late Employees -->
                    <div class="col-md-6 mb-4">
                        <div class="card border-warning h-100">
                            <div class="card-header bg-warning text-white d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">
                                    <i class="fas fa-clock me-2"></i>
                                    Late Employees
                                    <span class="badge bg-light text-warning ms-2" id="lateCount">0</span>
                                </h6>
                            </div>
                            <div class="card-body">
                                <div id="lateList" class="employee-list">
                                    <!-- Late employees will be loaded here -->
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- On Leave Employees -->
                    <div class="col-md-6 mb-4">
                        <div class="card border-info h-100">
                            <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">
                                    <i class="fas fa-umbrella-beach me-2"></i>
                                    On Leave Employees
                                    <span class="badge bg-light text-info ms-2" id="onLeaveCount">0</span>
                                </h6>
                            </div>
                            <div class="card-body">
                                <div id="onLeaveList" class="employee-list">
                                    <!-- On leave employees will be loaded here -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <a href="#" id="viewDayDetailsLink" class="btn btn-primary">
                    <i class="fas fa-external-link-alt me-2"></i>
                    View Detailed Report
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Add Time Entry Modal (Same as index) -->
<div class="modal fade" id="addTimeEntryModal" tabindex="-1" role="dialog" aria-labelledby="addTimeEntryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="addTimeEntryModalLabel">Add Time Entry</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addTimeEntryForm">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="employee_id">Employee *</label>
                                <select name="employee_id" id="employee_id" class="form-control" required>
                                    <option value="">Select Employee</option>
                                    @foreach($employees as $employee)
                                        <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="work_date">Work Date *</label>
                                <input type="date" name="work_date" id="work_date" class="form-control" 
                                       value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="clock_in">Clock In Time *</label>
                                <input type="time" name="clock_in" id="clock_in" class="form-control" 
                                       value="09:00" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="clock_out">Clock Out Time *</label>
                                <input type="time" name="clock_out" id="clock_out" class="form-control" 
                                       value="17:00" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="status">Status *</label>
                                <select name="status" id="status" class="form-control" required>
                                    <option value="present">Present</option>
                                    <option value="absent">Absent</option>
                                    <option value="late">Late</option>
                                    <option value="on_leave">On Leave</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="notes">Notes</label>
                                <textarea name="notes" id="notes" class="form-control" rows="2" 
                                          placeholder="Optional notes about this time entry"></textarea>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="submitTimeEntry()">Save Time Entry</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
    /* Calendar Styles */
    .calendar-day-header {
        border-right: 1px solid #dee2e6;
    }
    .calendar-day-header:last-child {
        border-right: none;
    }
    
    .calendar-day {
        min-height: 180px;
        border-right: 1px solid #dee2e6;
        border-bottom: 1px solid #dee2e6;
        cursor: pointer;
        transition: all 0.2s ease;
        background-color: #fff;
    }
    
    .calendar-day:hover {
        background-color: #f8f9fa;
        transform: translateY(-2px);
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        z-index: 1;
    }
    
    .calendar-day:last-child {
        border-right: none;
    }
    
    .calendar-weekend {
        background-color: #f8f9fa;
    }
    
    .calendar-today {
        background-color: #e3f2fd;
        border-left: 3px solid #2196f3;
    }
    
    .calendar-no-data {
        background-color: #fafafa;
    }
    
    .calendar-date-number {
        font-size: 1.5rem;
        font-weight: bold;
        color: #495057;
    }
    
    .calendar-today-date {
        color: #2196f3;
    }
    
    .calendar-day-of-week {
        font-size: 0.8rem;
        color: #6c757d;
        font-weight: 500;
    }
    
    .calendar-attendance-summary {
        font-size: 0.85rem;
    }
    
    .calendar-stat-item {
        padding: 2px 0;
    }
    
    .calendar-stat-label {
        display: flex;
        align-items: center;
        font-weight: 500;
    }
    
    .calendar-stat-value {
        font-weight: bold;
    }
    
    .calendar-progress {
        background-color: #e9ecef;
        border-radius: 2px;
    }
    
    .calendar-total-employees {
        font-size: 0.75rem;
    }
    
    /* Employee List Styles */
    .employee-list {
        max-height: 300px;
        overflow-y: auto;
    }
    
    .employee-item {
        padding: 10px;
        border-bottom: 1px solid #eee;
        display: flex;
        align-items: center;
        transition: background-color 0.2s;
    }
    
    .employee-item:hover {
        background-color: #f8f9fa;
    }
    
    .employee-item:last-child {
        border-bottom: none;
    }
    
    .employee-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background-color: #e9ecef;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 12px;
        font-weight: bold;
        color: #495057;
    }
    
    .employee-info {
        flex: 1;
    }
    
    .employee-name {
        font-weight: 600;
        margin-bottom: 2px;
    }
    
    .employee-time {
        font-size: 0.85rem;
        color: #6c757d;
    }
    
    .employee-status {
        font-size: 0.75rem;
        padding: 2px 8px;
        border-radius: 12px;
        font-weight: 500;
    }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
        .calendar-day {
            min-height: 150px;
        }
        
        .calendar-date-number {
            font-size: 1.2rem;
        }
        
        .calendar-stat-label,
        .calendar-stat-value {
            font-size: 0.75rem;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    // Show day details modal
    function showDayDetails(element) {
        const date = element.getAttribute('data-date');
        const present = JSON.parse(element.getAttribute('data-present'));
        const absent = JSON.parse(element.getAttribute('data-absent'));
        const late = JSON.parse(element.getAttribute('data-late'));
        const onLeave = JSON.parse(element.getAttribute('data-on-leave'));
        
        // Format date for display
        const dateObj = new Date(date);
        const formattedDate = dateObj.toLocaleDateString('en-US', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
        
        // Update modal title
        document.getElementById('modalDateTitle').textContent = `Attendance Details - ${formattedDate}`;
        
        // Update counts
        document.getElementById('presentCount').textContent = present.length;
        document.getElementById('absentCount').textContent = absent.length;
        document.getElementById('lateCount').textContent = late.length;
        document.getElementById('onLeaveCount').textContent = onLeave.length;
        
        // Update employee lists
        updateEmployeeList('presentList', present, 'success', 'Present');
        updateEmployeeList('absentList', absent, 'danger', 'Absent');
        updateEmployeeList('lateList', late, 'warning', 'Late');
        updateEmployeeList('onLeaveList', onLeave, 'info', 'On Leave');
        
        // Update detailed report link
        const reportUrl = `{{ route('hr-payroll.time-attendance') }}?date=${date}`;
        document.getElementById('viewDayDetailsLink').href = reportUrl;
    }
    
    // Update employee list in modal
    function updateEmployeeList(listId, employees, statusColor, statusText) {
        const listElement = document.getElementById(listId);
        listElement.innerHTML = '';
        
        if (employees.length === 0) {
            listElement.innerHTML = `
                <div class="text-center text-muted py-4">
                    <i class="fas fa-users-slash fa-2x mb-3"></i>
                    <p>No ${statusText.toLowerCase()} employees</p>
                </div>
            `;
            return;
        }
        
        employees.forEach(employee => {
            const initials = getInitials(employee.name);
            const clockInTime = employee.clock_in ? `Clock In: ${employee.clock_in}` : 'No clock in';
            const clockOutTime = employee.clock_out ? `Clock Out: ${employee.clock_out}` : 'No clock out';
            const hours = employee.total_hours ? `Hours: ${employee.total_hours}` : '';
            
            const employeeItem = document.createElement('div');
            employeeItem.className = 'employee-item';
            employeeItem.innerHTML = `
                <div class="employee-avatar bg-${statusColor}-subtle">
                    ${initials}
                </div>
                <div class="employee-info">
                    <div class="employee-name">${employee.name}</div>
                    <div class="employee-time">
                        <div>${clockInTime}</div>
                        <div>${clockOutTime} ${hours ? `• ${hours}` : ''}</div>
                    </div>
                </div>
                <div>
                    <span class="employee-status badge bg-${statusColor}">
                        ${statusText}
                    </span>
                    ${employee.is_approved ? 
                        '<span class="badge bg-success ms-1">Approved</span>' : 
                        '<span class="badge bg-warning ms-1">Pending</span>'
                    }
                </div>
            `;
            
            listElement.appendChild(employeeItem);
        });
    }
    
    // Get initials from name
    function getInitials(name) {
        return name.split(' ')
            .map(word => word.charAt(0))
            .join('')
            .toUpperCase()
            .substring(0, 2);
    }
    
    // Time entry submission (same as index)
    function submitTimeEntry() {
        console.log('submitTimeEntry function called!');
        
        // Get form data
        const employeeId = $('#employee_id').val() || '';
        const workDate = $('#work_date').val() || '';
        const clockIn = $('#clock_in').val() || '';
        const clockOut = $('#clock_out').val() || '';
        const status = $('#status').val() || '';
        const notes = $('#notes').val() || '';
        
        const formData = {
            employee_id: employeeId,
            work_date: workDate,
            clock_in: clockIn,
            clock_out: clockOut,
            status: status,
            notes: notes,
            _token: $('meta[name="csrf-token"]').attr('content')
        };

        console.log('Form data:', formData);

        // Validate required fields
        if (!employeeId || employeeId === '' || !workDate || !clockIn || !clockOut) {
            console.error('Validation failed - missing fields:', {
                employeeId, workDate, clockIn, clockOut
            });
            alert('Please fill in all required fields.');
            return;
        }

        // Validate clock out time is after clock in time
        if (clockIn >= clockOut) {
            alert('Clock out time must be after clock in time.');
            return;
        }

        console.log('Validation passed, submitting...');

        // Show loading state
        const submitBtn = $('#addTimeEntryModal .btn-primary');
        const originalText = submitBtn.html();
        submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Saving...').prop('disabled', true);

        console.log('AJAX URL:', '{{ route("hr-payroll.time-attendance.store") }}');

        // Submit via AJAX
        $.ajax({
            url: '{{ route("hr-payroll.time-attendance.store") }}',
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    // Show success message
                    alert('Time entry added successfully!');
                    
                    // Reset form
                    $('#addTimeEntryForm')[0].reset();
                    
                    // Close modal
                    $('#addTimeEntryModal').modal('hide');
                    
                    // Reload page to show new entry
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                } else {
                    alert('Error: ' + response.message);
                    submitBtn.html(originalText).prop('disabled', false);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', xhr.responseText);
                
                let errorMessage = 'An error occurred while saving the time entry.';
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    const errors = xhr.responseJSON.errors;
                    errorMessage = Object.values(errors).flat().join('\n');
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                
                alert(errorMessage);
                submitBtn.html(originalText).prop('disabled', false);
            }
        });
    }
    
    // Initialize tooltips
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Bootstrap tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
        
        // Auto-submit month filter
        $('#monthFilter').on('change', function() {
            $(this).closest('form').submit();
        });
        
        // Auto-submit employee filter
        $('#employeeFilter').on('change', function() {
            $(this).closest('form').submit();
        });
    });
</script>
@endpush
