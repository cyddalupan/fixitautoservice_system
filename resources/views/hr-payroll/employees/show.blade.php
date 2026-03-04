@php
    $hrDetail = $employee->employeeHrDetails ?? null;
    $employmentStatus = $hrDetail ? ($hrDetail->employment_status ?? ($employee->employment_type ?? 'active')) : ($employee->employment_type ?? 'active');
    $statusClass = $employmentStatus === 'active' ? 'success' : 
                  ($employmentStatus === 'inactive' ? 'secondary' : 'danger');
@endphp

@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">Employee Details</h1>
                    <p class="text-muted">Complete information for {{ $employee->name }}</p>
                </div>
                <div>
                    <a href="{{ route('hr-payroll.employees') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Employees
                    </a>
                    <a href="{{ route('hr-payroll.employees.edit', $employee->id) }}" class="btn btn-warning">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                    <form action="{{ route('hr-payroll.employees.destroy', $employee->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this employee?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="row">
        <!-- Left Column: Personal Information -->
        <div class="col-md-4">
            <div class="card shadow mb-4">
                <div class="card-header bg-primary text-white">
                    <h6 class="m-0 font-weight-bold">Personal Information</h6>
                </div>
                <div class="card-body text-center">
                    <div class="mb-4">
                        <img src="{{ $employee->profile_photo_url ?? asset('images/default-avatar.png') }}" 
                             alt="{{ $employee->name }}" 
                             class="rounded-circle border" 
                             width="150" 
                             height="150">
                    </div>
                    
                    <h4 class="mb-1">{{ $employee->name }}</h4>
                    <p class="text-muted mb-3">{{ $employee->email }}</p>
                    
                    <div class="mb-3">
                        <span class="badge badge-{{ $statusClass }} p-2">
                            {{ ucfirst($employmentStatus) }}
                        </span>
                        <span class="badge badge-info p-2 ml-2">
                            {{ ucfirst($employee->role) }}
                        </span>
                    </div>

                    <div class="text-left">
                        <p><strong>Employee ID:</strong> {{ $employee->employee_id ?? 'Not Set' }}</p>
                        <p><strong>Phone:</strong> {{ $employee->phone ?? 'Not Set' }}</p>
                        <p><strong>Hire Date:</strong> {{ $employee->hire_date ? \Carbon\Carbon::parse($employee->hire_date)->format('M d, Y') : 'Not Set' }}</p>
                        <p><strong>Account Status:</strong> 
                            <span class="badge badge-{{ $employee->is_active ? 'success' : 'danger' }}">
                                {{ $employee->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Contact Information -->
            <div class="card shadow mb-4">
                <div class="card-header bg-info text-white">
                    <h6 class="m-0 font-weight-bold">Contact Information</h6>
                </div>
                <div class="card-body">
                    <p><strong>Email:</strong> {{ $employee->email }}</p>
                    <p><strong>Address:</strong><br>
                    {{ $hrDetail?->address ?? 'Not Provided' }}</p>
                    
                    <p><strong>Notes:</strong><br>
                    {{ $hrDetail?->notes ?? 'No additional notes' }}</p>
                </div>
            </div>
        </div>

        <!-- Right Column: Employment Details -->
        <div class="col-md-8">
            <!-- Employment Information -->
            <div class="card shadow mb-4">
                <div class="card-header bg-success text-white">
                    <h6 class="m-0 font-weight-bold">Employment Information</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Department:</strong> {{ $hrDetail?->department ?? 'Not Set' }}</p>
                            <p><strong>Position:</strong> {{ $hrDetail?->position ?? 'Not Set' }}</p>
                            <p><strong>Job Title:</strong> {{ $hrDetail?->job_title ?? 'Not Set' }}</p>
                            <p><strong>Employee Number:</strong> {{ $hrDetail?->employee_number ?? 'Not Set' }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Hire Date:</strong> {{ $employee->hire_date ? \Carbon\Carbon::parse($employee->hire_date)->format('M d, Y') : 'Not Set' }}</p>
                            <p><strong>Employment Status:</strong> {{ ucfirst($hrDetail?->employment_status ?? 'Not Set') }}</p>
                            <p><strong>Employment Type:</strong> {{ ucfirst($employee->employment_type ?? 'Not Set') }}</p>
                            <p><strong>Tenure:</strong> 
                                @if($employee->hire_date)
                                    {{ \Carbon\Carbon::parse($employee->hire_date)->diffInYears(now()) }} years
                                @else
                                    Not Available
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Compensation & Benefits -->
            <div class="card shadow mb-4">
                <div class="card-header bg-warning text-white">
                    <h6 class="m-0 font-weight-bold">Compensation & Benefits</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Base Salary:</strong> ${{ number_format($hrDetail?->base_salary ?? 0, 2) }}</p>
                            <p><strong>Created:</strong> {{ $employee->created_at ? \Carbon\Carbon::parse($employee->created_at)->format('M d, Y') : 'Not Available' }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Last Updated:</strong> {{ $hrDetail?->updated_at ? \Carbon\Carbon::parse($hrDetail?->updated_at)->format('M d, Y') : 'Not Available' }}</p>
                            <p><strong>Last Login:</strong> {{ $employee->last_login_at ? \Carbon\Carbon::parse($employee->last_login_at)->format('M d, Y H:i') : 'Never' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Attendance Statistics -->
            <div class="card shadow mb-4">
                <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold">Monthly Attendance Statistics - {{ \Carbon\Carbon::create($year, $month, 1)->format('F Y') }}</h6>
                    <div class="month-navigation">
                        <a href="{{ route('hr-payroll.employees.show', ['id' => $employee->id, 'month' => $month - 1 <= 0 ? 12 : $month - 1, 'year' => $month - 1 <= 0 ? $year - 1 : $year]) }}" 
                           class="btn btn-sm btn-light">
                            <i class="fas fa-chevron-left"></i> Previous
                        </a>
                        <span class="mx-2 text-white">{{ \Carbon\Carbon::create($year, $month, 1)->format('F Y') }}</span>
                        <a href="{{ route('hr-payroll.employees.show', ['id' => $employee->id, 'month' => $month + 1 > 12 ? 1 : $month + 1, 'year' => $month + 1 > 12 ? $year + 1 : $year]) }}" 
                           class="btn btn-sm btn-light">
                            Next <i class="fas fa-chevron-right"></i>
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 text-center">
                            <div class="card border-left-primary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                Present Days
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                {{ $attendanceStats['present_days'] ?? 0 }}
                                            </div>
                                            <div class="text-xs text-muted">
                                                out of {{ $attendanceStats['working_days'] ?? 0 }} working days
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-calendar-check fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3 text-center">
                            <div class="card border-left-danger shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                                Absent Days
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                {{ $attendanceStats['absent_days'] ?? 0 }}
                                            </div>
                                            <div class="text-xs text-muted">
                                                {{ $attendanceStats['working_days'] > 0 ? round(($attendanceStats['absent_days'] / $attendanceStats['working_days']) * 100, 1) : 0 }}% rate
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-calendar-times fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3 text-center">
                            <div class="card border-left-warning shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                                Late Days
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                {{ $attendanceStats['late_days'] ?? 0 }}
                                            </div>
                                            <div class="text-xs text-muted">
                                                {{ $attendanceStats['present_days'] > 0 ? round(($attendanceStats['late_days'] / $attendanceStats['present_days']) * 100, 1) : 0 }}% of present days
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3 text-center">
                            <div class="card border-left-success shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                                Total Hours
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                {{ $attendanceStats['total_hours'] ?? 0 }}
                                            </div>
                                            <div class="text-xs text-muted">
                                                {{ $attendanceStats['overtime_hours'] ?? 0 }} OT hours
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-business-time fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Attendance Rate Progress Bar -->
                    <div class="mt-4">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-sm font-weight-bold">Attendance Rate</span>
                            <span class="text-sm font-weight-bold">{{ $attendanceStats['attendance_rate'] ?? 0 }}%</span>
                        </div>
                        <div class="progress" style="height: 20px;">
                            <div class="progress-bar bg-success" role="progressbar" 
                                 style="width: {{ $attendanceStats['attendance_rate'] ?? 0 }}%" 
                                 aria-valuenow="{{ $attendanceStats['attendance_rate'] ?? 0 }}" 
                                 aria-valuemin="0" 
                                 aria-valuemax="100">
                                {{ $attendanceStats['attendance_rate'] ?? 0 }}%
                            </div>
                        </div>
                        <small class="text-muted">Based on {{ $attendanceStats['present_days'] ?? 0 }} present days out of {{ $attendanceStats['working_days'] ?? 0 }} working days</small>
                    </div>
                </div>
            </div>

            <!-- Monthly Attendance Calendar -->
            <div class="card shadow mb-4">
                <div class="card-header bg-info text-white">
                    <h6 class="m-0 font-weight-bold">Monthly Attendance Calendar - {{ \Carbon\Carbon::create($year, $month, 1)->format('F Y') }}</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="thead-light">
                                <tr>
                                    <th class="text-center">Sun</th>
                                    <th class="text-center">Mon</th>
                                    <th class="text-center">Tue</th>
                                    <th class="text-center">Wed</th>
                                    <th class="text-center">Thu</th>
                                    <th class="text-center">Fri</th>
                                    <th class="text-center">Sat</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $firstDay = \Carbon\Carbon::create($year, $month, 1);
                                    $startDay = $firstDay->copy()->startOfWeek();
                                    $endDay = $firstDay->copy()->endOfMonth()->endOfWeek();
                                    $currentDay = $startDay->copy();
                                    $week = [];
                                @endphp
                                
                                @while($currentDay->lte($endDay))
                                    @if($currentDay->dayOfWeek == 0 && !empty($week))
                                        <tr>
                                            @foreach($week as $day)
                                                @php
                                                    // Find day data from monthlyAttendance array
                                                    $dayData = null;
                                                    $dateKey = $day->format('Y-m-d');
                                                    foreach ($monthlyAttendance as $attendanceDay) {
                                                        if ($attendanceDay['date'] == $dateKey) {
                                                            $dayData = $attendanceDay;
                                                            break;
                                                        }
                                                    }
                                                    $isCurrentMonth = $day->month == $month;
                                                    $statusColor = $dayData ? $dayData['status_color'] : ($day->isWeekend() ? 'bg-light' : '');
                                                    $isToday = $day->isToday();
                                                @endphp
                                                <td class="text-center p-2 {{ $isCurrentMonth ? '' : 'text-muted' }} {{ $isToday ? 'border border-primary' : '' }}" 
                                                    style="height: 100px; vertical-align: top; background-color: {{ $statusColor == 'success' ? '#d4edda' : ($statusColor == 'danger' ? '#f8d7da' : ($statusColor == 'warning' ? '#fff3cd' : ($statusColor == 'info' ? '#d1ecf1' : ($statusColor == 'secondary' ? '#e2e3e5' : '#f8f9fa')))) }}">
                                                    <div class="d-flex justify-content-between">
                                                        <small class="font-weight-bold">{{ $day->day }}</small>
                                                        @if($dayData && $dayData['attendance'])
                                                            <small class="badge badge-{{ $dayData['status_color'] }}">{{ ucfirst($dayData['status']) }}</small>
                                                        @endif
                                                    </div>
                                                    
                                                    @if($dayData && $dayData['attendance'])
                                                        <div class="mt-2 text-left">
                                                            @if($dayData['clock_in'])
                                                                <small class="d-block"><i class="fas fa-sign-in-alt text-success"></i> {{ $dayData['clock_in'] }}</small>
                                                            @endif
                                                            @if($dayData['clock_out'])
                                                                <small class="d-block"><i class="fas fa-sign-out-alt text-danger"></i> {{ $dayData['clock_out'] }}</small>
                                                            @endif
                                                            @if($dayData['total_hours'] > 0)
                                                                <small class="d-block"><i class="fas fa-clock text-info"></i> {{ number_format($dayData['total_hours'], 1) }}h</small>
                                                            @endif
                                                            @if($dayData['notes'])
                                                                <small class="d-block text-truncate" title="{{ $dayData['notes'] }}">
                                                                    <i class="fas fa-sticky-note text-warning"></i> {{ Str::limit($dayData['notes'], 15) }}
                                                                </small>
                                                            @endif
                                                        </div>
                                                    @elseif($day->isWeekend())
                                                        <div class="mt-2">
                                                            <small class="text-muted">Weekend</small>
                                                        </div>
                                                    @elseif($isCurrentMonth)
                                                        <div class="mt-2">
                                                            <small class="text-muted">No record</small>
                                                        </div>
                                                    @endif
                                                </td>
                                            @endforeach
                                        </tr>
                                        @php $week = []; @endphp
                                    @endif
                                    
                                    @php
                                        $week[] = $currentDay->copy();
                                        $currentDay->addDay();
                                    @endphp
                                @endwhile
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Legend -->
                    <div class="mt-3">
                        <h6 class="font-weight-bold">Legend:</h6>
                        <div class="d-flex flex-wrap">
                            <span class="badge badge-success mr-2 mb-1">Present</span>
                            <span class="badge badge-danger mr-2 mb-1">Absent</span>
                            <span class="badge badge-warning mr-2 mb-1">Late</span>
                            <span class="badge badge-info mr-2 mb-1">On Leave</span>
                            <span class="badge badge-secondary mr-2 mb-1">Weekend</span>
                            <span class="badge badge-light mr-2 mb-1">No Record</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Attendance Records -->
            <div class="card shadow mb-4">
                <div class="card-header bg-primary text-white">
                    <h6 class="m-0 font-weight-bold">Recent Attendance Records</h6>
                </div>
                <div class="card-body">
                    @if($timeAttendance->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Day</th>
                                        <th>Clock In</th>
                                        <th>Clock Out</th>
                                        <th>Total Hours</th>
                                        <th>Status</th>
                                        <th>Location</th>
                                        <th>Approved</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($timeAttendance as $record)
                                        <tr>
                                            <td>{{ $record->work_date->format('M d, Y') }}</td>
                                            <td>{{ $record->work_date->format('l') }}</td>
                                            <td>{{ $record->clock_in ? \Carbon\Carbon::parse($record->clock_in)->format('h:i A') : 'N/A' }}</td>
                                            <td>{{ $record->clock_out ? \Carbon\Carbon::parse($record->clock_out)->format('h:i A') : 'N/A' }}</td>
                                            <td>{{ number_format($record->calculateTotalHours(), 2) }}</td>
                                            <td>
                                                <span class="badge badge-{{ $record->status_color }}">
                                                    {{ ucfirst($record->status) }}
                                                </span>
                                            </td>
                                            <td>{{ $record->location ?? 'N/A' }}</td>
                                            <td>
                                                @if($record->approved)
                                                    <span class="badge badge-success">Yes</span>
                                                @else
                                                    <span class="badge badge-warning">Pending</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No attendance records found for {{ \Carbon\Carbon::create($year, $month, 1)->format('F Y') }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection