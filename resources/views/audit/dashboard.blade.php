@extends('layouts.app')

@section('title', 'Audit Management Dashboard')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h1 class="h3 mb-0">Audit Management Dashboard</h1>
            <p class="text-muted">Monitor audit performance, schedule, and statistics</p>
        </div>
        <div class="col-auto">
            <div class="btn-group">
                <a href="{{ route('audit.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> New Audit
                </a>
                <a href="{{ route('audit.export') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-download"></i> Export
                </a>
            </div>
        </div>
    </div>

    <!-- Audit Metrics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Audits (30d)
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $metrics['overall']['total'] }}
                            </div>
                            <div class="mt-2 mb-0 text-muted text-xs">
                                <span class="text-success mr-2">
                                    <i class="fas fa-arrow-up"></i> {{ $metrics['overall']['passed'] }} passed
                                </span>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clipboard-check fa-2x text-gray-300"></i>
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
                                Pass Rate
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $metrics['overall']['pass_rate'] }}%
                            </div>
                            <div class="mt-2 mb-0 text-muted text-xs">
                                <span class="text-nowrap">Avg Score: {{ $metrics['overall']['avg_score'] }}%</span>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-percentage fa-2x text-gray-300"></i>
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
                                Scheduled Audits
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $metrics['overall']['scheduled'] }}
                            </div>
                            <div class="mt-2 mb-0 text-muted text-xs">
                                <span class="text-info mr-2">
                                    <i class="fas fa-spinner"></i> {{ $metrics['overall']['in_progress'] }} in progress
                                </span>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-alt fa-2x text-gray-300"></i>
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
                                Audit NCRs
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $metrics['ncrs']['total'] }}
                            </div>
                            <div class="mt-2 mb-0 text-muted text-xs">
                                <span class="text-danger mr-2">
                                    <i class="fas fa-exclamation-triangle"></i> {{ $metrics['ncrs']['critical'] }} critical
                                </span>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Row -->
    <div class="row">
        <!-- Recent Audits -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Audits</h6>
                    <a href="{{ route('audit.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>Audit #</th>
                                    <th>Checklist</th>
                                    <th>Technician</th>
                                    <th>Score</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentAudits as $audit)
                                <tr>
                                    <td>
                                        <a href="{{ route('audit.show', $audit->id) }}">
                                            {{ $audit->audit_number }}
                                        </a>
                                    </td>
                                    <td>{{ $audit->checklist->name ?? 'N/A' }}</td>
                                    <td>{{ $audit->technician->name ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge badge-{{ $audit->passed() ? 'success' : 'danger' }}">
                                            {{ $audit->percentage_score }}%
                                        </span>
                                    </td>
                                    <td>{{ $audit->audit_date->format('m/d/Y') }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">No recent audits found</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Upcoming Audits -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-warning">Upcoming Audits</h6>
                    <a href="{{ route('audit.index') }}?status=scheduled" class="btn btn-sm btn-outline-warning">View All</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>Audit #</th>
                                    <th>Checklist</th>
                                    <th>Auditor</th>
                                    <th>Scheduled Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($upcomingAudits as $audit)
                                <tr>
                                    <td>
                                        <a href="{{ route('audit.show', $audit->id) }}">
                                            {{ $audit->audit_number }}
                                        </a>
                                    </td>
                                    <td>{{ $audit->checklist->name ?? 'N/A' }}</td>
                                    <td>{{ $audit->auditor->name ?? 'N/A' }}</td>
                                    <td>{{ $audit->audit_date->format('m/d/Y') }}</td>
                                    <td>
                                        <span class="badge badge-warning">
                                            {{ ucfirst($audit->status) }}
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">No upcoming audits scheduled</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Audit Schedule -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">This Week's Audit Schedule</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Day</th>
                                    <th>Auditor</th>
                                    <th>Audits</th>
                                    <th>Schedule</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                                @endphp
                                @foreach($days as $day)
                                    @if(isset($auditSchedule[$day]))
                                        @foreach($auditSchedule[$day] as $auditorSchedule)
                                            <tr>
                                                <td>{{ $day }}</td>
                                                <td>{{ $auditorSchedule['auditor'] }}</td>
                                                <td>{{ $auditorSchedule['audits'] }}</td>
                                                <td>
                                                    @foreach($auditorSchedule['details'] as $detail)
                                                        <div class="mb-1">
                                                            <small>
                                                                <strong>{{ $detail['time'] }}</strong>: 
                                                                {{ $detail['title'] }} 
                                                                ({{ $detail['technician'] }})
                                                                <span class="badge badge-{{ $detail['status'] === 'scheduled' ? 'warning' : 'info' }}">
                                                                    {{ $detail['status'] }}
                                                                </span>
                                                            </small>
                                                        </div>
                                                    @endforeach
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td>{{ $day }}</td>
                                            <td colspan="3" class="text-center text-muted">No audits scheduled</td>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Checklist Performance -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-info">Checklist Performance (Last 30 Days)</h6>
                    <a href="{{ route('quality-control.checklists') }}" class="btn btn-sm btn-outline-info">View Checklists</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>Checklist</th>
                                    <th>Service Type</th>
                                    <th>Audits</th>
                                    <th>Pass Rate</th>
                                    <th>Avg Score</th>
                                    <th>Performance</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($checklistPerformance as $checklist)
                                <tr>
                                    <td>{{ $checklist['name'] }}</td>
                                    <td>
                                        <span class="badge badge-secondary">
                                            {{ str_replace('_', ' ', ucfirst($checklist['service_type'])) }}
                                        </span>
                                    </td>
                                    <td>{{ $checklist['total_audits'] }}</td>
                                    <td>{{ $checklist['pass_rate'] }}%</td>
                                    <td>{{ $checklist['avg_score'] }}%</td>
                                    <td>
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar bg-{{ $checklist['performance'] === 'excellent' ? 'success' : ($checklist['performance'] === 'good' ? 'info' : ($checklist['performance'] === 'fair' ? 'warning' : 'danger')) }}" 
                                                 role="progressbar" 
                                                 style="width: {{ $checklist['pass_rate'] }}%"
                                                 aria-valuenow="{{ $checklist['pass_rate'] }}" 
                                                 aria-valuemin="0" 
                                                 aria-valuemax="100">
                                                {{ $checklist['pass_rate'] }}%
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted">No checklist performance data</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="row mt-4">
        <div class="col-md-4 mb-3">
            <div class="card border-left-primary h-100">
                <div class="card-body">
                    <h5 class="card-title text-primary">
                        <i class="fas fa-chart-line"></i> Audit Trend
                    </h5>
                    <p class="card-text">
                        @if($metrics['trend'] === 'improving')
                            <span class="text-success">
                                <i class="fas fa-arrow-up"></i> Audit performance is improving
                            </span>
                        @elseif($metrics['trend'] === 'declining')
                            <span class="text-danger">
                                <i class="fas fa-arrow-down"></i> Audit performance is declining
                            </span>
                        @else
                            <span class="text-info">
                                <i class="fas fa-minus"></i> Audit performance is stable
                            </span>
                        @endif
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card border-left-warning h-100">
                <div class="card-body">
                    <h5 class="card-title text-warning">
                        <i class="fas fa-exclamation-triangle"></i> Attention Needed
                    </h5>
                    <p class="card-text">
                        {{ $metrics['ncrs']['open'] }} open NCRs from audits need resolution.
                        @if($metrics['ncrs']['critical'] > 0)
                            <br><small class="text-danger">{{ $metrics['ncrs']['critical'] }} are critical.</small>
                        @endif
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card border-left-success h-100">
                <div class="card-body">
                    <h5 class="card-title text-success">
                        <i class="fas fa-calendar-check"></i> Schedule Status
                    </h5>
                    <p class="card-text">
                        {{ $metrics['overall']['scheduled'] }} audits scheduled.
                        {{ $metrics['overall']['in_progress'] }} audits in progress.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('audit.create') }}" class="btn btn-outline-primary btn-block">
                                <i class="fas fa-plus-circle fa-2x mb-2"></i><br>
                                New Audit
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('audit.statistics') }}" class="btn btn-outline-info btn-block">
                                <i class="fas fa-chart-bar fa-2x mb-2"></i><br>
                                View Statistics
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('audit.export') }}" class="btn btn-outline-success btn-block">
                                <i class="fas fa-download fa-2x mb-2"></i><br>
                                Export Data
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <button class="btn btn-outline-warning btn-block" data-toggle="modal" data-target="#scheduleModal">
                                <i class="fas fa-calendar-plus fa-2x mb-2"></i><br>
                                Schedule Recurring
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Schedule Recurring Modal -->
<div class="modal fade" id="scheduleModal" tabindex="-1" role="dialog" aria-labelledby="scheduleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5