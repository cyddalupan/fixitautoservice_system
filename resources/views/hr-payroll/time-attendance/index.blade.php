@extends('layouts.app')

@section('content')
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">Time & Attendance</h1>
                    <p class="text-muted">Track employee working hours and attendance</p>
                </div>
                <div class="btn-group">
                    <a href="{{ route('hr-payroll.time-attendance', ['view' => 'calendar']) }}" class="btn btn-primary">
                        <i class="fas fa-calendar-alt"></i> Calendar View
                    </a>
                    <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addTimeEntryModal">
                        <i class="fas fa-plus"></i> Add Time Entry
                    </button>
                    <a href="{{ route('hr-payroll.time-attendance.export') }}" class="btn btn-success">
                        <i class="fas fa-file-export"></i> Export
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-body">
                    <form method="GET" action="{{ route('hr-payroll.time-attendance') }}">
                        <div class="row">
                            <div class="col-md-3">
                                <label for="employeeFilter">Employee:</label>
                                <select name="employee_id" id="employeeFilter" class="form-control">
                                    <option value="">All Employees</option>
                                    @foreach($employees as $employee)
                                        <option value="{{ $employee->id }}" {{ request('employee_id') == $employee->id ? 'selected' : '' }}>
                                            {{ $employee->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="dateFrom">Date From:</label>
                                <input type="date" name="date_from" id="dateFrom" class="form-control" 
                                       value="{{ request('date_from') }}">
                            </div>
                            <div class="col-md-3">
                                <label for="dateTo">Date To:</label>
                                <input type="date" name="date_to" id="dateTo" class="form-control" 
                                       value="{{ request('date_to') }}">
                            </div>
                            <div class="col-md-3">
                                <label for="statusFilter">Status:</label>
                                <select name="status" id="statusFilter" class="form-control">
                                    <option value="">All Status</option>
                                    <option value="present" {{ request('status') == 'present' ? 'selected' : '' }}>Present</option>
                                    <option value="absent" {{ request('status') == 'absent' ? 'selected' : '' }}>Absent</option>
                                    <option value="late" {{ request('status') == 'late' ? 'selected' : '' }}>Late</option>
                                    <option value="on_leave" {{ request('status') == 'on_leave' ? 'selected' : '' }}>On Leave</option>
                                </select>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-12 text-right">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-filter"></i> Apply Filters
                                </button>
                                <a href="{{ route('hr-payroll.time-attendance') }}" class="btn btn-secondary">
                                    <i class="fas fa-redo"></i> Reset
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Hours This Week
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $timeAttendance->where('work_date', '>=', now()->startOfWeek())->sum('total_hours') }} hrs
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
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
                                Present Today
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $timeAttendance->where('work_date', today())->where('status', 'present')->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-check fa-2x text-gray-300"></i>
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
                                Late Arrivals
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $timeAttendance->where('work_date', today())->where('status', 'late')->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Absent Today
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $timeAttendance->where('work_date', today())->where('status', 'absent')->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-times fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Time Attendance Table -->
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold">Time & Attendance Records</h6>
                    <div class="btn-group">
                        <button type="button" class="btn btn-light btn-sm" onclick="approveSelected()">
                            <i class="fas fa-check"></i> Approve Selected
                        </button>
                        <button type="button" class="btn btn-light btn-sm" onclick="rejectSelected()">
                            <i class="fas fa-times"></i> Reject Selected
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" id="timeAttendanceTable">
                            <thead class="thead-light">
                                <tr>
                                    <th width="50">
                                        <input type="checkbox" id="selectAll">
                                    </th>
                                    <th>Employee</th>
                                    <th>Date</th>
                                    <th>Clock In</th>
                                    <th>Clock Out</th>
                                    <th>Total Hours</th>
                                    <th>Regular Hours</th>
                                    <th>Overtime Hours</th>
                                    <th>Status</th>
                                    <th>Approval</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($timeAttendance as $record)
                                    <tr>
                                        <td>
                                            <input type="checkbox" class="record-checkbox" value="{{ $record->id }}">
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="mr-3">
                                                    <img src="{{ $record->employee->profile_photo_url ?? asset('images/default-avatar.png') }}" 
                                                         alt="{{ $record->employee->name }}" 
                                                         class="rounded-circle" 
                                                         width="40" 
                                                         height="40">
                                                </div>
                                                <div>
                                                    <strong>{{ $record->employee->name }}</strong>
                                                    <div class="text-muted small">{{ $record->employee->hrDetail->position ?? 'Employee' }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ \Carbon\Carbon::parse($record->work_date)->format('M d, Y') }}</td>
                                        <td>
                                            @if($record->clock_in)
                                                <span class="badge badge-success">{{ \Carbon\Carbon::parse($record->clock_in)->format('h:i A') }}</span>
                                            @else
                                                <span class="badge badge-secondary">Not Clocked In</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($record->clock_out)
                                                <span class="badge badge-info">{{ \Carbon\Carbon::parse($record->clock_out)->format('h:i A') }}</span>
                                            @else
                                                <span class="badge badge-warning">Not Clocked Out</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <strong>{{ number_format($record->total_hours, 2) }} hrs</strong>
                                        </td>
                                        <td>{{ number_format($record->regular_hours, 2) }} hrs</td>
                                        <td>
                                            @if($record->overtime_hours > 0)
                                                <span class="badge badge-warning">{{ number_format($record->overtime_hours, 2) }} hrs</span>
                                            @else
                                                <span class="text-muted">0 hrs</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($record->status === 'present')
                                                <span class="badge badge-success p-2">Present</span>
                                            @elseif($record->status === 'absent')
                                                <span class="badge badge-danger p-2">Absent</span>
                                            @elseif($record->status === 'late')
                                                <span class="badge badge-warning p-2">Late</span>
                                            @elseif($record->status === 'on_leave')
                                                <span class="badge badge-info p-2">On Leave</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($record->is_approved)
                                                <span class="badge badge-success p-2">Approved</span>
                                            @else
                                                <span class="badge badge-warning p-2">Pending</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button type="button" 
                                                        class="btn btn-sm btn-info" 
                                                        title="View Details"
                                                        onclick="viewRecord({{ $record->id }})">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                
                                                @if(!$record->is_approved)
                                                    <button type="button" 
                                                            class="btn btn-sm btn-success" 
                                                            title="Approve"
                                                            onclick="approveRecord({{ $record->id }})">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                    <button type="button" 
                                                            class="btn btn-sm btn-danger" 
                                                            title="Reject"
                                                            onclick="rejectRecord({{ $record->id }})">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                @endif
                                                
                                                <button type="button" 
                                                        class="btn btn-sm btn-warning" 
                                                        title="Edit"
                                                        onclick="editRecord({{ $record->id }})">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="11" class="text-center text-muted py-4">
                                            <i class="fas fa-clock fa-2x mb-3"></i>
                                            <p>No time attendance records found.</p>
                                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTimeEntryModal">
                                                <i class="fas fa-plus"></i> Add Time Entry
                                            </button>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($timeAttendance->hasPages())
                        <div class="mt-3">
                            {{ $timeAttendance->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Today's Attendance Summary -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-header bg-info text-white">
                    <h6 class="m-0 font-weight-bold">Today's Attendance Summary</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        @php
                            $todayRecords = $timeAttendance->where('work_date', today());
                            $presentCount = $todayRecords->where('status', 'present')->count();
                            $absentCount = $todayRecords->where('status', 'absent')->count();
                            $lateCount = $todayRecords->where('status', 'late')->count();
                            $onLeaveCount = $todayRecords->where('status', 'on_leave')->count();
                            $totalEmployees = $employees->count();
                        @endphp
                        
                        <div class="col-md-3 text-center">
                            <div class="display-4 text-success">{{ $presentCount }}</div>
                            <div class="text-muted">Present</div>
                            <div class="progress mt-2" style="height: 10px;">
                                <div class="progress-bar bg-success" 
                                     style="width: {{ $totalEmployees > 0 ? ($presentCount / $totalEmployees * 100) : 0 }}%"></div>
                            </div>
                        </div>
                        
                        <div class="col-md-3 text-center">
                            <div class="display-4 text-danger">{{ $absentCount }}</div>
                            <div class="text-muted">Absent</div>
                            <div class="progress mt-2" style="height: 10px;">
                                <div class="progress-bar bg-danger" 
                                     style="width: {{ $totalEmployees > 0 ? ($absentCount / $totalEmployees * 100) : 0 }}%"></div>
                            </div>
                        </div>
                        
                        <div class="col-md-3 text-center">
                            <div class="display-4 text-warning">{{ $lateCount }}</div>
                            <div class="text-muted">Late</div>
                            <div class="progress mt-2" style="height: 10px;">
                                <div class="progress-bar bg-warning" 
                                     style="width: {{ $totalEmployees > 0 ? ($lateCount / $totalEmployees * 100) : 0 }}%"></div>
                            </div>
                        </div>
                        
                        <div class="col-md-3 text-center">
                            <div class="display-4 text-info">{{ $onLeaveCount }}</div>
                            <div class="text-muted">On Leave</div>
                            <div class="progress mt-2" style="height: 10px;">
                                <div class="progress-bar bg-info" 
                                     style="width: {{ $totalEmployees > 0 ? ($onLeaveCount / $totalEmployees * 100) : 0 }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Time Entry Modal -->
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
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="submitTimeEntry()">Save Time Entry</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        console.log('jQuery is loaded and working!');
        console.log('$ is defined:', typeof $ !== 'undefined');
        console.log('$.ajax is defined:', typeof $.ajax !== 'undefined');
        
        // TEMPORARILY DISABLED - DataTables causing column mismatch error
        // $('#timeAttendanceTable').DataTable({
        //     pageLength: 25,
        //     order: [[2, 'desc']]
        // });

        // Select all checkbox
        $('#selectAll').click(function() {
            console.log('Select all checkbox clicked');
            $('.record-checkbox').prop('checked', this.checked);
        });

        // Test button click binding
        $('#addTimeEntryModal .btn-primary').on('click', function() {
            console.log('Save button clicked via jQuery event');
        });

        // Bootstrap 5 modal event handling
        const addTimeEntryModal = document.getElementById('addTimeEntryModal');
        if (addTimeEntryModal) {
            addTimeEntryModal.addEventListener('shown.bs.modal', function () {
                console.log('Modal shown (Bootstrap 5 event)');
                console.log('Modal has show class:', $('#addTimeEntryModal').hasClass('show'));
            });
        }
    });

    // Test if function is accessible globally
    window.testFunction = function() {
        console.log('testFunction is accessible globally');
        return true;
    };

    function viewRecord(recordId) {
        // In a real implementation, this would open a modal with record details
        alert('Viewing time attendance record ID: ' + recordId);
    }

    function editRecord(recordId) {
        // In a real implementation, this would open an edit modal
        alert('Editing time attendance record ID: ' + recordId);
    }

    function approveRecord(recordId) {
        if (confirm('Approve this time attendance record?')) {
            // In a real implementation, this would submit an approval request
            alert('Approving record ID: ' + recordId);
            // window.location.href = "/hr-payroll/time-attendance/" + recordId + "/approve";
        }
    }

    function rejectRecord(recordId) {
        if (confirm('Reject this time attendance record?')) {
            // In a real implementation, this would submit a rejection request
            alert('Rejecting record ID: ' + recordId);
            // window.location.href = "/hr-payroll/time-attendance/" + recordId + "/reject";
        }
    }

    function approveSelected() {
        const selectedIds = [];
        $('.record-checkbox:checked').each(function() {
            selectedIds.push($(this).val());
        });

        if (selectedIds.length === 0) {
            alert('Please select at least one record to approve.');
            return;
        }

        if (confirm('Approve ' + selectedIds.length + ' selected records?')) {
            // In a real implementation, this would submit a batch approval
            alert('Approving records: ' + selectedIds.join(', '));
        }
    }

    function rejectSelected() {
        const selectedIds = [];
        $('.record-checkbox:checked').each(function() {
            selectedIds.push($(this).val());
        });

        if (selectedIds.length === 0) {
            alert('Please select at least one record to reject.');
            return;
        }

        if (confirm('Reject ' + selectedIds.length + ' selected records?')) {
            // In a real implementation, this would submit a batch rejection
            alert('Rejecting records: ' + selectedIds.join(', '));
        }
    }

    function submitTimeEntry() {
        console.log('submitTimeEntry function called!');
        
        // Bootstrap 5 modal check
        const modalElement = document.getElementById('addTimeEntryModal');
        const modal = bootstrap.Modal.getInstance(modalElement);
        console.log('Bootstrap modal instance:', modal);
        console.log('Modal element has show class:', $('#addTimeEntryModal').hasClass('show'));
        console.log('Modal element visibility:', $('#addTimeEntryModal').is(':visible'));
        
        // DEBUG: Check if employee_id element exists
        console.log('employee_id element exists:', $('#employee_id').length);
        console.log('employee_id value directly:', $('#employee_id').val());
        console.log('employee_id element HTML:', $('#employee_id')[0]?.outerHTML);
        
        // Get form data - FIXED: Handle undefined values
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

        console.log('Form data (FIXED):', formData);

        // Validate required fields - FIXED: Check for empty string, not just falsy
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
            error: function(xhr) {
                let errorMessage = 'Failed to save time entry.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                    // Show validation errors
                    const errors = Object.values(xhr.responseJSON.errors).flat();
                    errorMessage = errors.join('\n');
                }
                alert(errorMessage);
                submitBtn.html(originalText).prop('disabled', false);
            }
        });
    }

    // Reset form when modal is closed
    $('#addTimeEntryModal').on('hidden.bs.modal', function () {
        $('#addTimeEntryForm')[0].reset();
    });
</script>
@endpush