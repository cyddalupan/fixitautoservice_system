@extends('layouts.app')

@section('title', 'Quality Control Dashboard')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h1 class="h3 mb-0">Quality Control Dashboard</h1>
            <p class="text-muted">Monitor quality metrics, audits, and compliance</p>
        </div>
        <div class="col-auto">
            <div class="btn-group">
                <a href="{{ route('quality-control.audits.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> New Audit
                </a>
                <a href="{{ route('quality-control.export') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-download"></i> Export
                </a>
            </div>
        </div>
    </div>

    <!-- Quality Metrics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Audit Pass Rate
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $metrics['audits']['pass_rate'] }}%
                            </div>
                            <div class="mt-2 mb-0 text-muted text-xs">
                                <span class="text-success mr-2">
                                    <i class="fas fa-arrow-up"></i> {{ $metrics['audits']['passed'] }}
                                </span>
                                <span class="text-nowrap">of {{ $metrics['audits']['total'] }} audits</span>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-line fa-2x text-gray-300"></i>
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
                                Open NCRs
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $metrics['ncrs']['open'] }}
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

        <div class="col-md-3">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Corrective Actions
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $metrics['actions']['completion_rate'] }}%
                            </div>
                            <div class="mt-2 mb-0 text-muted text-xs">
                                <span class="text-danger mr-2">
                                    <i class="fas fa-clock"></i> {{ $metrics['actions']['overdue'] }} overdue
                                </span>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-tasks fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Avg Audit Score
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $metrics['audits']['avg_score'] }}%
                            </div>
                            <div class="mt-2 mb-0 text-muted text-xs">
                                <span class="text-nowrap">Last 30 days</span>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-percentage fa-2x text-gray-300"></i>
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
                    <a href="{{ route('quality-control.audits') }}" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>Audit #</th>
                                    <th>Technician</th>
                                    <th>Score</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentAudits as $audit)
                                <tr>
                                    <td>
                                        <a href="{{ route('quality-control.audits.show', $audit->id) }}">
                                            {{ $audit->audit_number }}
                                        </a>
                                    </td>
                                    <td>{{ $audit->technician->name ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge badge-{{ $audit->passed() ? 'success' : 'danger' }}">
                                            {{ $audit->percentage_score }}%
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $audit->status === 'completed' ? 'success' : ($audit->status === 'failed' ? 'danger' : 'warning') }}">
                                            {{ ucfirst($audit->status) }}
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

        <!-- Open NCRs -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-warning">Open Non-Conformance Reports</h6>
                    <a href="{{ route('quality-control.non-conformance-reports') }}" class="btn btn-sm btn-outline-warning">View All</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>NCR #</th>
                                    <th>Title</th>
                                    <th>Severity</th>
                                    <th>Assigned To</th>
                                    <th>Due Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($openNcrs as $ncr)
                                <tr>
                                    <td>
                                        <a href="{{ route('quality-control.non-conformance-reports.show', $ncr->id) }}">
                                            {{ $ncr->ncr_number }}
                                        </a>
                                    </td>
                                    <td>{{ Str::limit($ncr->title, 30) }}</td>
                                    <td>
                                        <span class="badge badge-{{ $ncr->severity === 'critical' ? 'danger' : ($ncr->severity === 'major' ? 'warning' : 'secondary') }}">
                                            {{ ucfirst($ncr->severity) }}
                                        </span>
                                    </td>
                                    <td>{{ $ncr->assignee->name ?? 'Unassigned' }}</td>
                                    <td>
                                        @if($ncr->due_date)
                                            <span class="{{ $ncr->isOverdue() ? 'text-danger' : '' }}">
                                                {{ $ncr->due_date->format('m/d/Y') }}
                                                @if($ncr->isOverdue())
                                                    <small class="text-danger">({{ $ncr->daysOverdue() }}d overdue)</small>
                                                @endif
                                            </span>
                                        @else
                                            <span class="text-muted">Not set</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">No open NCRs found</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Second Row -->
    <div class="row">
        <!-- Overdue Corrective Actions -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-danger">Overdue Corrective Actions</h6>
                    <a href="{{ route('quality-control.corrective-actions') }}" class="btn btn-sm btn-outline-danger">View All</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>Action #</th>
                                    <th>Title</th>
                                    <th>Assigned To</th>
                                    <th>Due Date</th>
                                    <th>Days Overdue</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($overdueActions as $action)
                                <tr>
                                    <td>
                                        <a href="{{ route('quality-control.corrective-actions.show', $action->id) }}">
                                            {{ $action->action_number }}
                                        </a>
                                    </td>
                                    <td>{{ Str::limit($action->title, 30) }}</td>
                                    <td>{{ $action->assignee->name ?? 'Unassigned' }}</td>
                                    <td>{{ $action->due_date->format('m/d/Y') }}</td>
                                    <td>
                                        <span class="badge badge-danger">
                                            {{ $action->daysOverdue() }} days
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">No overdue corrective actions</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Technician Performance -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-info">Technician Quality Performance</h6>
                    <a href="{{ route('quality-control.audits') }}" class="btn btn-sm btn-outline-info">View Details</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>Technician</th>
                                    <th>Audits</th>
                                    <th>Pass Rate</th>
                                    <th>Avg Score</th>
                                    <th>Performance</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($technicianPerformance as $tech)
                                <tr>
                                    <td>{{ $tech['name'] }}</td>
                                    <td>{{ $tech['total_audits'] }}</td>
                                    <td>{{ $tech['pass_rate'] }}%</td>
                                    <td>{{ $tech['avg_score'] }}%</td>
                                    <td>
                                        <span class="badge badge-{{ $tech['performance'] === 'excellent' ? 'success' : ($tech['performance'] === 'good' ? 'info' : ($tech['performance'] === 'fair' ? 'warning' : 'danger')) }}">
                                            {{ ucfirst($tech['performance']) }}
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">No technician performance data</td>
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
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('quality-control.checklists') }}" class="btn btn-outline-primary btn-block">
                                <i class="fas fa-clipboard-list fa-2x mb-2"></i><br>
                                Manage Checklists
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('quality-control.audits.create') }}" class="btn btn-outline-success btn-block">
                                <i class="fas fa-clipboard-check fa-2x mb-2"></i><br>
                                Conduct Audit
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('compliance.dashboard') }}" class="btn btn-outline-info btn-block">
                                <i class="fas fa-file-contract fa-2x mb-2"></i><br>
                                Compliance
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('audit.dashboard') }}" class="btn btn-outline-warning btn-block">
                                <i class="fas fa-chart-bar fa-2x mb-2"></i><br>
                                Audit Reports
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Auto-refresh dashboard every 5 minutes
    setTimeout(function() {
        window.location.reload();
    }, 300000);
</script>
@endpush