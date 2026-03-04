@extends('layouts.app')

@section('title', 'Personnel Details')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="page-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="mb-0">
                            <i class="fas fa-user me-2"></i>Personnel Details
                        </h1>
                        <p class="text-muted mb-0">{{ $user->name }} - {{ $user->role }}</p>
                    </div>
                    <div>
                        <a href="{{ route('personnel.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Back to List
                        </a>
                        <a href="{{ route('personnel.edit', $user) }}" class="btn btn-primary">
                            <i class="fas fa-edit me-1"></i> Edit
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Personnel Details -->
    <div class="row">
        <div class="col-md-4">
            <!-- Basic Information Card -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-id-card me-2"></i>Basic Information</h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <div class="avatar-placeholder bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 100px; height: 100px; font-size: 2.5rem;">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                        <h4 class="mt-3 mb-1">{{ $user->name }}</h4>
                        <span class="badge bg-{{ $user->role_badge_color }}">{{ ucfirst(str_replace('_', ' ', $user->role)) }}</span>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="40%">Employee ID:</th>
                                    <td>{{ $user->employee_id ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Email:</th>
                                    <td>{{ $user->email }}</td>
                                </tr>
                                <tr>
                                    <th>Phone:</th>
                                    <td>{{ $user->phone ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Status:</th>
                                    <td>
                                        @if($user->is_active)
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-danger">Inactive</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Hire Date:</th>
                                    <td>{{ $user->hire_date ? $user->hire_date->format('M d, Y') : 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Experience:</th>
                                    <td>{{ $user->years_experience ?? 0 }} years</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Skills & Certifications -->
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-certificate me-2"></i>Skills & Certifications</h5>
                </div>
                <div class="card-body">
                    @if($user->skills && count($user->skills) > 0)
                        <h6 class="mb-2">Skills:</h6>
                        <div class="mb-3">
                            @foreach($user->skills as $skill)
                                <span class="badge bg-primary me-1 mb-1">{{ $skill }}</span>
                            @endforeach
                        </div>
                    @endif

                    @if($user->certifications && count($user->certifications) > 0)
                        <h6 class="mb-2">Certifications:</h6>
                        <div>
                            @foreach($user->certifications as $cert)
                                <span class="badge bg-success me-1 mb-1">{{ $cert }}</span>
                            @endforeach
                        </div>
                    @endif

                    @if(!$user->skills && !$user->certifications)
                        <p class="text-muted mb-0">No skills or certifications recorded.</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <!-- Role-Specific Information -->
            @if($user->isTechnician())
                <!-- Technician Details -->
                <div class="card mb-4">
                    <div class="card-header bg-warning text-white">
                        <h5 class="mb-0"><i class="fas fa-wrench me-2"></i>Technician Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <th width="50%">Specialization:</th>
                                        <td>{{ $user->specialization ?? 'General' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Hourly Rate:</th>
                                        <td>${{ number_format($user->hourly_rate ?? 0, 2) }}/hr</td>
                                    </tr>
                                    <tr>
                                        <th>Shift Schedule:</th>
                                        <td>{{ $user->shift_schedule ?? 'Standard' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Employment Type:</th>
                                        <td>
                                            <span class="badge bg-{{ $user->employment_type_badge_color }}">
                                                {{ $user->formatted_employment_type }}
                                            </span>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <th width="50%">Completed Services:</th>
                                        <td>{{ $user->completed_services_count }}</td>
                                    </tr>
                                    <tr>
                                        <th>Total Labor Hours:</th>
                                        <td>{{ $user->total_labor_hours }} hours</td>
                                    </tr>
                                    <tr>
                                        <th>Total Labor Revenue:</th>
                                        <td>${{ number_format($user->total_labor_revenue, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <th>Can Train Others:</th>
                                        <td>
                                            @if($user->can_train_others)
                                                <span class="badge bg-success">Yes</span>
                                            @else
                                                <span class="badge bg-secondary">No</span>
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Time Logs -->
                <div class="card mb-4">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0"><i class="fas fa-clock me-2"></i>Recent Time Logs</h5>
                    </div>
                    <div class="card-body">
                        @if($user->timeLogs && $user->timeLogs->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-sm table-hover">
                                    <thead>
                                        <tr>
                                            <th>Date/Time</th>
                                            <th>Type</th>
                                            <th>Work Order</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($user->timeLogs as $log)
                                            <tr>
                                                <td>{{ $log->log_time->format('M d, Y H:i') }}</td>
                                                <td>
                                                    <span class="badge bg-info">{{ $log->log_type_label }}</span>
                                                </td>
                                                <td>
                                                    @if($log->workOrder)
                                                        <a href="{{ route('work-orders.show', $log->workOrder) }}">WO-{{ $log->workOrder->id }}</a>
                                                    @else
                                                        N/A
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="badge bg-{{ $log->status == 'approved' ? 'success' : ($log->status == 'pending' ? 'warning' : 'danger') }}">
                                                        {{ $log->status_label }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-muted mb-0">No time logs recorded.</p>
                        @endif
                    </div>
                </div>

                <!-- Performance Metrics -->
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Performance Metrics</h5>
                    </div>
                    <div class="card-body">
                        @if($user->performanceMetrics && $user->performanceMetrics->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-sm table-hover">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Quality Score</th>
                                            <th>Efficiency</th>
                                            <th>Customer Satisfaction</th>
                                            <th>Safety Score</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($user->performanceMetrics as $metric)
                                            <tr>
                                                <td>{{ $metric->created_at->format('M d, Y') }}</td>
                                                <td>
                                                    <span class="badge bg-{{ $metric->quality_score >= 90 ? 'success' : ($metric->quality_score >= 80 ? 'warning' : 'danger') }}">
                                                        {{ $metric->quality_score }}%
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-{{ $metric->efficiency_score >= 90 ? 'success' : ($metric->efficiency_score >= 80 ? 'warning' : 'danger') }}">
                                                        {{ $metric->efficiency_score }}%
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-{{ $metric->customer_satisfaction_score >= 90 ? 'success' : ($metric->customer_satisfaction_score >= 80 ? 'warning' : 'danger') }}">
                                                        {{ $metric->customer_satisfaction_score }}%
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-{{ $metric->safety_score >= 90 ? 'success' : ($metric->safety_score >= 80 ? 'warning' : 'danger') }}">
                                                        {{ $metric->safety_score }}%
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-muted mb-0">No performance metrics recorded.</p>
                        @endif
                    </div>
                </div>

                <!-- Training Records -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-graduation-cap me-2"></i>Training Records</h5>
                    </div>
                    <div class="card-body">
                        @if($user->trainingRecords && $user->trainingRecords->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-sm table-hover">
                                    <thead>
                                        <tr>
                                            <th>Training Module</th>
                                            <th>Completed Date</th>
                                            <th>Score</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($user->trainingRecords as $record)
                                            <tr>
                                                <td>
                                                    @if($record->trainingModule)
                                                        {{ $record->trainingModule->title }}
                                                    @else
                                                        N/A
                                                    @endif
                                                </td>
                                                <td>{{ $record->created_at->format('M d, Y') }}</td>
                                                <td>
                                                    @if($record->score)
                                                        <span class="badge bg-{{ $record->score >= 80 ? 'success' : ($record->score >= 70 ? 'warning' : 'danger') }}">
                                                            {{ $record->score }}%
                                                        </span>
                                                    @else
                                                        <span class="badge bg-secondary">N/A</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="badge bg-{{ $record->status == 'completed' ? 'success' : ($record->status == 'in_progress' ? 'warning' : 'secondary') }}">
                                                        {{ ucfirst($record->status) }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-muted mb-0">No training records.</p>
                        @endif
                    </div>
                </div>
            @else
                <!-- Non-Technician Details -->
                <div class="card mb-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-briefcase me-2"></i>Staff Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <th width="50%">Role:</th>
                                        <td>{{ ucfirst(str_replace('_', ' ', $user->role)) }}</td>
                                    </tr>
                                    <tr>
                                        <th>Employment Type:</th>
                                        <td>
                                            <span class="badge bg-{{ $user->employment_type_badge_color }}">
                                                {{ $user->formatted_employment_type }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Shift Schedule:</th>
                                        <td>{{ $user->shift_schedule ?? 'Standard' }}</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <th width="50%">Team Lead:</th>
                                        <td>
                                            @if($user->is_team_lead)
                                                <span class="badge bg-success">Yes</span>
                                            @else
                                                <span class="badge bg-secondary">No</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Emergency Contact:</th>
                                        <td>
                                            @if($user->emergency_contact_name)
                                                {{ $user->emergency_contact_name }}<br>
                                                <small>{{ $user->emergency_contact_phone }}</small>
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Address Information -->
            <div class="card mb-4">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0"><i class="fas fa-home me-2"></i>Address Information</h5>
                </div>
                <div class="card-body">
                    @if($user->address)
                        <p class="mb-0">{{ $user->address }}</p>
                    @else
                        <p class="text-muted mb-0">No address recorded.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.avatar-placeholder {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}
.stat-card {
    transition: transform 0.2s;
}
.stat-card:hover {
    transform: translateY(-2px);
}
</style>
@endsection