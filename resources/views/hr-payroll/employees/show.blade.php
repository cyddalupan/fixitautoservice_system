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
        <!-- Main Column: All Information -->
        <div class="col-md-12">
            <!-- Monthly Attendance Calendar with Carousel -->
            <div class="card shadow mb-4">
                <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold">Monthly Attendance Calendar - {{ \Carbon\Carbon::create($year, $month, 1)->format('F Y') }}</h6>
                    <div class="month-navigation">
                        <button class="btn btn-sm btn-light mr-2" id="prev-month">
                            <i class="fas fa-chevron-left"></i> Previous Month
                        </button>
                        <span class="mx-2 text-white font-weight-bold">{{ \Carbon\Carbon::create($year, $month, 1)->format('F Y') }}</span>
                        <button class="btn btn-sm btn-light" id="next-month">
                            Next Month <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body calendar-container">
                    <div class="calendar-content-wrapper">
                        <div class="calendar-content">
                        <!-- Attendance Statistics -->
                        <div class="row mb-4 attendance-stats">
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
                        <div class="mb-4">
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

                        <!-- Calendar Table -->
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
            </div>
            </div>

            <!-- Employment Information -->
            <div class="card shadow mb-4">
                <div class="card-header bg-success text-white">
                    <h6 class="m-0 font-weight-bold">Employment Information</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Employee Name:</strong> {{ $employee->name }}</p>
                            <p><strong>Email:</strong> {{ $employee->email }}</p>
                            <p><strong>Phone:</strong> {{ $employee->phone ?? 'Not Set' }}</p>
                            <p><strong>Employee ID:</strong> {{ $employee->employee_id ?? 'Not Set' }}</p>
                            <p><strong>Department:</strong> {{ $hrDetail?->department ?? 'Not Set' }}</p>
                            <p><strong>Position:</strong> {{ $hrDetail?->position ?? 'Not Set' }}</p>
                            <p><strong>Job Title:</strong> {{ $hrDetail?->job_title ?? 'Not Set' }}</p>
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
                            <p><strong>Base Salary:</strong> ${{ number_format($hrDetail?->base_salary ?? 0, 2) }}</p>
                            <p><strong>Address:</strong> {{ $hrDetail?->address ?? 'Not Provided' }}</p>
                            <p><strong>Notes:</strong> {{ $hrDetail?->notes ?? 'No additional notes' }}</p>
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

<!-- CSS for Smooth Swipe Animation -->
<style>
/* Calendar container for animation */
.calendar-container {
    position: relative;
    overflow: hidden;
    min-height: 600px;
}

/* Calendar container for animation */
.calendar-container {
    position: relative;
    overflow: hidden;
    min-height: 600px;
}

/* Calendar content wrapper */
.calendar-content-wrapper {
    position: relative;
    width: 100%;
    height: 100%;
}

/* Calendar content - will be duplicated for animation */
.calendar-content {
    position: relative;
    width: 100%;
    transition: transform 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
    will-change: transform;
}

/* Animation states */
.calendar-content.sliding-out-left {
    transform: translateX(-100%);
}

.calendar-content.sliding-out-right {
    transform: translateX(100%);
}

.calendar-content.sliding-in-left {
    transform: translateX(100%);
    animation: slideInLeft 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94) forwards;
}

.calendar-content.sliding-in-right {
    transform: translateX(-100%);
    animation: slideInRight 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94) forwards;
}

@keyframes slideInLeft {
    to {
        transform: translateX(0);
    }
}

@keyframes slideInRight {
    to {
        transform: translateX(0);
    }
}

/* Loading overlay */
.loading-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.9);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 1000;
    opacity: 0;
    visibility: hidden;
    transition: opacity 0.3s ease;
}

.loading-overlay.active {
    opacity: 1;
    visibility: visible;
}

/* Month navigation buttons with better styling */
.month-navigation .btn {
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.month-navigation .btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.month-navigation .btn:active {
    transform: translateY(0);
}

/* Touch swipe support */
.calendar-content {
    touch-action: pan-y;
}

/* Smooth fade for statistics */
.attendance-stats {
    transition: opacity 0.3s ease;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .calendar-container {
        min-height: 500px;
    }
    
    .month-navigation {
        flex-direction: column;
        gap: 10px;
    }
    
    .month-navigation .btn {
        width: 100%;
    }
}
</style>

<!-- JavaScript for Smooth Swipe Animation -->
@push('scripts')
<script>
$(document).ready(function() {
    // Elements
    const calendarContainer = $('.calendar-container');
    const calendarContent = $('.calendar-content');
    const prevMonthBtn = $('#prev-month');
    const nextMonthBtn = $('#next-month');
    const loadingOverlay = $('.loading-overlay');
    
    // Create loading overlay if not exists
    if (loadingOverlay.length === 0) {
        calendarContainer.append(`
            <div class="loading-overlay">
                <div class="text-center">
                    <div class="spinner-border text-info" style="width: 3rem; height: 3rem;" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p class="mt-3 font-weight-bold">Loading attendance data...</p>
                </div>
            </div>
        `);
    }
    
    // Variables for swipe detection
    let touchStartX = 0;
    let touchEndX = 0;
    const swipeThreshold = 50; // Minimum swipe distance in pixels
    
    // Add hover effect to month navigation buttons
    prevMonthBtn.add(nextMonthBtn).hover(
        function() {
            $(this).addClass('btn-info').removeClass('btn-light');
            $(this).find('i').addClass('fa-spin');
        },
        function() {
            $(this).removeClass('btn-info').addClass('btn-light');
            $(this).find('i').removeClass('fa-spin');
        }
    );

    // Add keyboard navigation
    $(document).keydown(function(e) {
        if (e.keyCode == 37) { // Left arrow key
            e.preventDefault();
            navigateToMonth('prev');
        } else if (e.keyCode == 39) { // Right arrow key
            e.preventDefault();
            navigateToMonth('next');
        }
    });

    // Touch swipe detection
    calendarContent.on('touchstart', function(e) {
        touchStartX = e.changedTouches[0].screenX;
    });

    calendarContent.on('touchend', function(e) {
        touchEndX = e.changedTouches[0].screenX;
        handleSwipe();
    });

    // Mouse swipe detection (for desktop)
    let mouseDownX = 0;
    let mouseUpX = 0;
    
    calendarContent.on('mousedown', function(e) {
        mouseDownX = e.clientX;
    });

    calendarContent.on('mouseup', function(e) {
        mouseUpX = e.clientX;
        handleMouseSwipe();
    });

    // Handle touch swipe
    function handleSwipe() {
        const swipeDistance = touchEndX - touchStartX;
        
        if (Math.abs(swipeDistance) > swipeThreshold) {
            if (swipeDistance > 0) {
                // Swipe right -> previous month
                navigateToMonth('prev');
            } else {
                // Swipe left -> next month
                navigateToMonth('next');
            }
        }
    }

    // Handle mouse swipe
    function handleMouseSwipe() {
        const swipeDistance = mouseUpX - mouseDownX;
        
        if (Math.abs(swipeDistance) > swipeThreshold) {
            if (swipeDistance > 0) {
                // Swipe right -> previous month
                navigateToMonth('prev');
            } else {
                // Swipe left -> next month
                navigateToMonth('next');
            }
        }
    }

    // Navigation function with smooth animation
    function navigateToMonth(direction) {
        // Prevent multiple clicks/swipes during animation
        if (calendarContent.hasClass('sliding-out-left') || calendarContent.hasClass('sliding-out-right') ||
            calendarContent.hasClass('sliding-in-left') || calendarContent.hasClass('sliding-in-right')) {
            return;
        }
        
        // Get current URL and parameters
        const currentUrl = window.location.href;
        const url = new URL(currentUrl);
        const currentMonth = parseInt(url.searchParams.get('month') || new Date().getMonth() + 1);
        const currentYear = parseInt(url.searchParams.get('year') || new Date().getFullYear());
        
        // Calculate new month and year
        let newMonth, newYear;
        if (direction === 'prev') {
            newMonth = currentMonth - 1;
            newYear = currentYear;
            if (newMonth <= 0) {
                newMonth = 12;
                newYear = currentYear - 1;
            }
        } else { // 'next'
            newMonth = currentMonth + 1;
            newYear = currentYear;
            if (newMonth > 12) {
                newMonth = 1;
                newYear = currentYear + 1;
            }
        }
        
        // Update URL parameters
        url.searchParams.set('month', newMonth);
        url.searchParams.set('year', newYear);
        
        // Show loading overlay
        $('.loading-overlay').addClass('active');
        
        // Animate current content out
        const outClass = direction === 'prev' ? 'sliding-out-right' : 'sliding-out-left';
        calendarContent.addClass(outClass);
        
        // Fetch new data
        $.ajax({
            url: url.toString(),
            method: 'GET',
            success: function(data) {
                // Parse the HTML response
                const parser = new DOMParser();
                const doc = parser.parseFromString(data, 'text/html');
                
                // Extract calendar content from response
                const newCalendarContent = $(doc).find('.calendar-content').html();
                const newMonthTitle = $(doc).find('.card-header h6').text();
                
                // Wait for slide-out animation to complete
                setTimeout(function() {
                    // Update calendar content
                    calendarContent.html(newCalendarContent);
                    
                    // Remove out class and add in class
                    calendarContent.removeClass(outClass);
                    const inClass = direction === 'prev' ? 'sliding-in-right' : 'sliding-in-left';
                    calendarContent.addClass(inClass);
                    
                    // Update month title
                    $('.card-header h6').text(newMonthTitle);
                    
                    // Wait for slide-in animation to complete
                    setTimeout(function() {
                        // Remove in class
                        calendarContent.removeClass(inClass);
                        
                        // Update browser URL without reloading page
                        window.history.pushState({}, '', url.toString());
                        
                        // Hide loading overlay
                        $('.loading-overlay').removeClass('active');
                        
                        // Re-initialize event listeners for new content
                        initializeEventListeners();
                        
                        // Show success notification
                        showNotification(`Switched to ${newMonthTitle}`, 'success');
                    }, 400); // Match slide-in animation duration
                }, 400); // Match slide-out animation duration
            },
            error: function(xhr, status, error) {
                // Hide loading overlay
                $('.loading-overlay').removeClass('active');
                
                // Remove animation class
                calendarContent.removeClass(outClass);
                
                // Show error notification
                showNotification('Failed to load attendance data. Please try again.', 'error');
                console.error('Error loading attendance data:', error);
            }
        });
    }

    // Initialize event listeners for new content
    function initializeEventListeners() {
        // Re-attach hover effects to navigation buttons
        prevMonthBtn.add(nextMonthBtn).off('hover').hover(
            function() {
                $(this).addClass('btn-info').removeClass('btn-light');
                $(this).find('i').addClass('fa-spin');
            },
            function() {
                $(this).removeClass('btn-info').addClass('btn-light');
                $(this).find('i').removeClass('fa-spin');
            }
        );
        
        // Update button click handlers
        prevMonthBtn.off('click').on('click', function(e) {
            e.preventDefault();
            navigateToMonth('prev');
        });
        
        nextMonthBtn.off('click').on('click', function(e) {
            e.preventDefault();
            navigateToMonth('next');
        });
    }

    // Show notification function
    function showNotification(message, type) {
        // Remove existing notifications
        $('.calendar-notification').remove();
        
        // Create notification
        const notificationClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const notification = $(`
            <div class="alert ${notificationClass} alert-dismissible fade show calendar-notification" role="alert" style="position: fixed; top: 20px; right: 20px; z-index: 9999; max-width: 300px;">
                ${message}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        `);
        
        // Add to page
        $('body').append(notification);
        
        // Auto-remove after 3 seconds
        setTimeout(function() {
            notification.alert('close');
        }, 3000);
    }

    // Initialize button click handlers
    prevMonthBtn.on('click', function(e) {
        e.preventDefault();
        navigateToMonth('prev');
    });
    
    nextMonthBtn.on('click', function(e) {
        e.preventDefault();
        navigateToMonth('next');
    });
    
    // Handle browser back/forward buttons
    window.addEventListener('popstate', function() {
        // Reload the page to show correct month
        window.location.reload();
    });
});
</script>
@endpush
