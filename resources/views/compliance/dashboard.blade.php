@extends('layouts.app')

@section('title', 'Compliance Dashboard')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h1 class="h3 mb-0">Compliance Dashboard</h1>
            <p class="text-muted">Monitor compliance standards, documents, and requirements</p>
        </div>
        <div class="col-auto">
            <div class="btn-group">
                <a href="{{ route('compliance.standards.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add Standard
                </a>
                <a href="{{ route('compliance.documents.create') }}" class="btn btn-success">
                    <i class="fas fa-upload"></i> Upload Document
                </a>
            </div>
        </div>
    </div>

    <!-- Compliance Metrics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Compliance Rate
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $metrics['standards']['compliance_rate'] }}%
                            </div>
                            <div class="mt-2 mb-0 text-muted text-xs">
                                <span class="text-success mr-2">
                                    <i class="fas fa-check-circle"></i> {{ $metrics['standards']['active'] }}
                                </span>
                                <span class="text-nowrap">active standards</span>
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
                                Expiring Standards
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $metrics['standards']['expiring'] }}
                            </div>
                            <div class="mt-2 mb-0 text-muted text-xs">
                                <span class="text-danger mr-2">
                                    <i class="fas fa-exclamation-triangle"></i> {{ $metrics['standards']['expired'] }} expired
                                </span>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-times fa-2x text-gray-300"></i>
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
                                Active Documents
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $metrics['documents']['active'] }}
                            </div>
                            <div class="mt-2 mb-0 text-muted text-xs">
                                <span class="text-warning mr-2">
                                    <i class="fas fa-clock"></i> {{ $metrics['documents']['expiring'] }} expiring
                                </span>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-file-alt fa-2x text-gray-300"></i>
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
                                Compliance Audits
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $metrics['audits']['passed'] }}/{{ $metrics['audits']['total'] }}
                            </div>
                            <div class="mt-2 mb-0 text-muted text-xs">
                                <span class="text-nowrap">Avg Score: {{ $metrics['audits']['avg_score'] }}%</span>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clipboard-check fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Row -->
    <div class="row">
        <!-- Expiring Standards -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-warning">Standards Expiring Soon (30 days)</h6>
                    <a href="{{ route('compliance.standards') }}" class="btn btn-sm btn-outline-warning">View All</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Name</th>
                                    <th>Category</th>
                                    <th>Expiration</th>
                                    <th>Days Left</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($expiringStandards as $standard)
                                <tr>
                                    <td>
                                        <a href="{{ route('compliance.standards.show', $standard->id) }}">
                                            {{ $standard->code }}
                                        </a>
                                    </td>
                                    <td>{{ Str::limit($standard->name, 30) }}</td>
                                    <td>
                                        <span class="badge badge-info">
                                            {{ ucfirst($standard->category) }}
                                        </span>
                                    </td>
                                    <td>{{ $standard->expiration_date->format('m/d/Y') }}</td>
                                    <td>
                                        <span class="badge badge-{{ $standard->daysToExpiry() <= 7 ? 'danger' : 'warning' }}">
                                            {{ $standard->daysToExpiry() }} days
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">No standards expiring soon</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Expired Standards -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-danger">Expired Standards</h6>
                    <a href="{{ route('compliance.standards') }}?status=expired" class="btn btn-sm btn-outline-danger">View All</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Name</th>
                                    <th>Category</th>
                                    <th>Expired</th>
                                    <th>Days Overdue</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($expiredStandards as $standard)
                                <tr>
                                    <td>
                                        <a href="{{ route('compliance.standards.show', $standard->id) }}">
                                            {{ $standard->code }}
                                        </a>
                                    </td>
                                    <td>{{ Str::limit($standard->name, 30) }}</td>
                                    <td>
                                        <span class="badge badge-info">
                                            {{ ucfirst($standard->category) }}
                                        </span>
                                    </td>
                                    <td>{{ $standard->expiration_date->format('m/d/Y') }}</td>
                                    <td>
                                        <span class="badge badge-danger">
                                            {{ abs($standard->daysToExpiry()) }} days
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">No expired standards</td>
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
        <!-- Recent Compliance Audits -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Compliance Audits</h6>
                    <a href="{{ route('compliance.reports') }}?type=audits" class="btn btn-sm btn-outline-primary">View Reports</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>Audit #</th>
                                    <th>Checklist</th>
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
                                    <td>{{ $audit->checklist->name ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge badge-{{ $audit->percentage_score >= 80 ? 'success' : 'danger' }}">
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
                                    <td colspan="5" class="text-center text-muted">No recent compliance audits</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Documents Needing Renewal -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-info">Documents Needing Renewal (60 days)</h6>
                    <a href="{{ route('compliance.documents') }}" class="btn btn-sm btn-outline-info">View All</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>Document</th>
                                    <th>Standard</th>
                                    <th>Type</th>
                                    <th>Expiry</th>
                                    <th>Days Left</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($documentsNeedingRenewal as $document)
                                <tr>
                                    <td>
                                        <a href="{{ route('compliance.documents.show', $document->id) }}">
                                            {{ Str::limit($document->title, 25) }}
                                        </a>
                                    </td>
                                    <td>{{ $document->standard->code ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge badge-secondary">
                                            {{ ucfirst($document->document_type) }}
                                        </span>
                                    </td>
                                    <td>{{ $document->expiry_date->format('m/d/Y') }}</td>
                                    <td>
                                        <span class="badge badge-{{ $document->daysToExpiry() <= 30 ? 'warning' : 'info' }}">
                                            {{ $document->daysToExpiry() }} days
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">No documents needing renewal</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Compliance Status Summary -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Compliance Status Summary</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="card border-left-success h-100">
                                <div class="card-body">
                                    <h5 class="card-title text-success">
                                        <i class="fas fa-check-circle"></i> Active Standards
                                    </h5>
                                    <p class="card-text">
                                        {{ $metrics['standards']['active'] }} of {{ $metrics['standards']['total'] }} standards are active and compliant.
                                    </p>
                                    <div class="progress">
                                        <div class="progress-bar bg-success" role="progressbar" 
                                             style="width: {{ $metrics['standards']['active'] > 0 ? ($metrics['standards']['active'] / $metrics['standards']['total'] * 100) : 0 }}%">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card border-left-warning h-100">
                                <div class="card-body">
                                    <h5 class="card-title text-warning">
                                        <i class="fas fa-exclamation-triangle"></i> Attention Required
                                    </h5>
                                    <p class="card-text">
                                        {{ $metrics['standards']['expiring'] + $metrics['standards']['expired'] }} standards need attention.
                                    </p>
                                    <div class="progress">
                                        <div class="progress-bar bg-warning" role="progressbar" 
                                             style="width: {{ $metrics['standards']['total'] > 0 ? (($metrics['standards']['expiring'] + $metrics['standards']['expired']) / $metrics['standards']['total'] * 100) : 0 }}%">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card border-left-info h-100">
                                <div class="card-body">
                                    <h5 class="card-title text-info">
                                        <i class="fas fa-file-alt"></i> Document Status
                                    </h5>
                                    <p class="card-text">
                                        {{ $metrics['documents']['active'] }} of {{ $metrics['documents']['total'] }} documents are active.
                                    </p>
                                    <div class="progress">
                                        <div class="progress-bar bg-info" role="progressbar" 
                                             style="width: {{ $metrics['documents']['total'] > 0 ? ($metrics['documents']['active'] / $metrics['documents']['total'] * 100) : 0 }}%">
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
                            <a href="{{ route('compliance.standards.create') }}" class="btn btn-outline-primary btn-block">
                                <i class="fas fa-plus-circle fa-2x mb-2"></i><br>
                                Add Standard
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('compliance.documents.create') }}" class="btn btn-outline-success btn-block">
                                <i class="fas fa-upload fa-2x mb-2"></i><br>
                                Upload Document
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('compliance.reports') }}" class="btn btn-outline-info btn-block">
                                <i class="fas fa-chart-bar fa-2x mb-2"></i><br>
                                View Reports
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('compliance.send-alerts') }}" class="btn btn-outline-warning btn-block" onclick="return confirm('Send compliance alerts to relevant personnel?')">
                                <i class="fas fa-bell fa-2x mb-2"></i><br>
                                Send Alerts
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
    // Auto-refresh dashboard every 10 minutes
    setTimeout(function() {
        window.location.reload();
    }, 600000);
    
    // Send alerts confirmation
    function sendAlerts() {
        if (confirm('Send compliance alerts to relevant personnel?')) {
            fetch('{{ route("compliance.send-alerts") }}')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Alerts sent successfully!');
                    } else {
                        alert('Error sending alerts.');
                    }
                })
                .catch(error => {
                    alert('Error sending alerts.');
                });
        }
    }
</script>
@endpush