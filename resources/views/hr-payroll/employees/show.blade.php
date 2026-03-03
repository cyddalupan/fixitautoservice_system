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

            <!-- Related Data -->
            <div class="card shadow mb-4">
                <div class="card-header bg-secondary text-white">
                    <h6 class="m-0 font-weight-bold">Related Information</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 text-center">
                            <div class="card border-left-primary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                Payroll Records
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                {{ $payrollRecords->count() }}
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-money-bill-wave fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4 text-center">
                            <div class="card border-left-success shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                                Time Attendance
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                {{ $timeAttendance->count() }}
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4 text-center">
                            <div class="card border-left-info shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                                Leave Balance
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                {{ $currentYearBalance->remaining ?? 0 }} days
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-calendar-alt fa-2x text-gray-300"></i>
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
@endsection