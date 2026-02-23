@extends('layouts.app')

@section('title', 'Audit Management')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h1 class="h3 mb-0">Audit Management</h1>
            <p class="text-muted">View, filter, and manage all quality audits</p>
        </div>
        <div class="col-auto">
            <a href="{{ route('audit.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> New Audit
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filters</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('audit.index') }}" class="row">
                <div class="col-md-3 mb-3">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-control" id="status" name="status">
                        <option value="">All Status</option>
                        <option value="scheduled" {{ request('status') == 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                        <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label for="checklist_id" class="form-label">Checklist</label>
                    <select class="form-control" id="checklist_id" name="checklist_id">
                        <option value="">All Checklists</option>
                        @foreach($checklists as $checklist)
                            <option value="{{ $checklist->id }}" {{ request('checklist_id') == $checklist->id ? 'selected' : '' }}>
                                {{ $checklist->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label for="technician_id" class="form-label">Technician</label>
                    <select class="form-control" id="technician_id" name="technician_id">
                        <option value="">All Technicians</option>
                        @foreach($technicians as $technician)
                            <option value="{{ $technician->id }}" {{ request('technician_id') == $technician->id ? 'selected' : '' }}>
                                {{ $technician->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label for="auditor_id" class="form-label">Auditor</label>
                    <select class="form-control" id="auditor_id" name="auditor_id">
                        <option value="">All Auditors</option>
                        @foreach($auditors as $auditor)
                            <option value="{{ $auditor->id }}" {{ request('auditor_id') == $auditor->id ? 'selected' : '' }}>
                                {{ $auditor->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label for="start_date" class="form-label">Start Date</label>
                    <input type="date" class="form-control" id="start_date" name="start_date" 
                           value="{{ request('start_date') }}">
                </div>
                <div class="col-md-3 mb-3">
                    <label for="end_date" class="form-label">End Date</label>
                    <input type="date" class="form-control" id="end_date" name="end_date" 
                           value="{{ request('end_date') }}">
                </div>
                <div class="col-md-3 mb-3">
                    <label for="score_min" class="form-label">Min Score</label>
                    <input type="number" class="form-control" id="score_min" name="score_min" 
                           min="0" max="100" value="{{ request('score_min') }}" placeholder="0">
                </div>
                <div class="col-md-3 mb-3">
                    <label for="score_max" class="form-label">Max Score</label>
                    <input type="number" class="form-control" id="score_max" name="score_max" 
                           min="0" max="100" value="{{ request('score_max') }}" placeholder="100">
                </div>
                <div class="col-md-12">
                    <div class="btn-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter"></i> Apply Filters
                        </button>
                        <a href="{{ route('audit.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times"></i> Clear
                        </a>
                        <button type="button" class="btn btn-outline-info" onclick="exportAudits()">
                            <i class="fas fa-download"></i> Export
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Audits Table -->
    <div class="card shadow">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Audits ({{ $audits->total() }})</h6>
            <div class="btn-group">
                <button class="btn btn-sm btn-outline-secondary" onclick="window.print()">
                    <i class="fas fa-print"></i> Print
                </button>
            </div>
        </div>
        <div class="card-body">
            @if($audits->isEmpty())
                <div class="text-center py-5">
                    <i class="fas fa-clipboard-check fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No audits found</h5>
                    <p class="text-muted">Create your first quality audit</p>
                    <a href="{{ route('audit.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Create Audit
                    </a>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Audit #</th>
                                <th>Title</th>
                                <th>Checklist</th>
                                <th>Technician</th>
                                <th>Auditor</th>
                                <th>Score</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($audits as $audit)
                            <tr>
                                <td>
                                    <strong>{{ $audit->audit_number }}</strong>
                                </td>
                                <td>
                                    {{ Str::limit($audit->title, 30) }}
                                    @if($audit->description)
                                        <br><small class="text-muted">{{ Str::limit($audit->description, 40) }}</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-info">
                                        {{ $audit->checklist->name ?? 'N/A' }}
                                    </span>
                                </td>
                                <td>{{ $audit->technician->name ?? 'N/A' }}</td>
                                <td>{{ $audit->auditor->name ?? 'N/A' }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="progress flex-grow-1 mr-2" style="height: 6px;">
                                            <div class="progress-bar bg-{{ $audit->passed() ? 'success' : 'danger' }}" 
                                                 style="width: {{ $audit->percentage_score }}%"></div>
                                        </div>
                                        <span class="badge badge-{{ $audit->passed() ? 'success' : 'danger' }}">
                                            {{ $audit->percentage_score }}%
                                        </span>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-{{ $audit->status === 'completed' ? 'success' : ($audit->status === 'failed' ? 'danger' : ($audit->status === 'scheduled' ? 'warning' : 'info')) }}">
                                        {{ ucfirst($audit->status) }}
                                    </span>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        {{ $audit->audit_date->format('m/d/Y') }}
                                    </small>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('audit.show', $audit->id) }}" 
                                           class="btn btn-outline-primary" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('audit.edit', $audit->id) }}" 
                                           class="btn btn-outline-warning" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-outline-info" 
                                                onclick="generateReport({{ $audit->id }})" title="Generate Report">
                                            <i class="fas fa-file-pdf"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="text-muted">
                        Showing {{ $audits->firstItem() }} to {{ $audits->lastItem() }} of {{ $audits->total() }} entries
                    </div>
                    <div>
                        {{ $audits->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Statistics -->
    <div class="row mt-4">
        <div class="col-md-3">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Audits
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $audits->total() }}
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
                                @php
                                    $passed = $audits->where('percentage_score', '>=', function($audit) {
                                        return $audit->checklist->passing_score ?? 80;
                                    })->count();
                                    $passRate = $audits->count() > 0 ? round(($passed / $audits->count()) * 100, 1) : 0;
                                @endphp
                                {{ $passRate }}%
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
                                Avg Score
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                @php
                                    $avgScore = $audits->avg('percentage_score') ?? 0;
                                @endphp
                                {{ round($avgScore, 1) }}%
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
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                By Status
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $audits->where('status', 'completed')->count() }}/{{ $audits->count() }}
                            </div>
                            <div class="mt-2 mb-0 text-muted text-xs">
                                <span class="mr-2">Completed</span>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-tasks fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Report Modal -->
<div class="modal fade" id="reportModal" tabindex="-1" role="dialog" aria-labelledby="reportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="reportModalLabel">Audit Report</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="reportContent">
                    <!-- Report content will be loaded here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="printReport()">
                    <i class="fas fa-print"></i> Print
                </button>
                <button type="button" class="btn btn-success" onclick="downloadReport()">
                    <i class="fas fa-download"></i> Download PDF
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Export audits
    function exportAudits() {
        const params = new URLSearchParams(window.location.search);
        window.location.href = '{{ route("audit.export") }}?' + params.toString();
    }
    
    // Generate report
    function generateReport(auditId) {
        fetch(`/audit/${auditId}/report`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayReport(data.report);
                    $('#reportModal').modal('show');
                } else {
                    alert('Error generating report.');
                }
            })
            .catch(error => {
                alert('Error generating report.');
            });
    }
    
    // Display report
    function displayReport(report) {
        const content = document.getElementById('reportContent');
        content.innerHTML = `
            <div class="report-header mb-4">
                <h4>${report.title}</h4>
                <p class="text-muted">Audit #${report.audit_number} | Date: ${report.date}</p>
            </div>
            <div class="row mb-4">
                <div class="col-md-6">
                    <h6>Audit Details</h6>
                    <table class="table table-sm">
                        <tr><td><strong>Checklist:</strong></td><td>${report.checklist}</td></tr>
                        <tr><td><strong>Technician:</strong></td><td>${report.technician}</td></tr>
                        <tr><td><strong>Auditor:</strong></td><td>${report.auditor}</td></tr>
                        <tr><td><strong>Vehicle:</strong></td><td>${report.vehicle}</td></tr>
                        <tr><td><strong>Status:</strong></td><td><span class="badge badge-${report.status === 'completed' ? 'success' : 'danger'}">${report.status}</span></td></tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <h6>Score Summary</h6>
                    <div class="text-center">
                        <div class="display-4 text-${report.passed ? 'success' : 'danger'}">
                            ${report.score}%
                        </div>
                        <p class="text-muted">${report.passed ? 'PASSED' : 'FAILED'}</p>
                        <div class="progress" style="height: 20px;">
                            <div class="progress-bar bg-${report.passed ? 'success' : 'danger'}" 
                                 style="width: ${report.score}%">
                                ${report.score}%
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            ${report.findings ? `
            <div class="mb-4">
                <h6>Findings</h6>
                <div class="card">
                    <div class="card-body">
                        ${report.findings}
                    </div>
                </div>
            </div>
            ` : ''}
            ${report.recommendations ? `
            <div class="mb-4">
                <h6>Recommendations</h6>
                <div class="card">
                    <div class="card-body">
                        ${report.recommendations}
                    </div>
                </div>
            </div>
            ` : ''}
            <div class="mb-4">
                <h6>Audit Results</h6>
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Requirement</th>
                            <th>Score</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${report.items.map(item => `
                            <tr>
                                <td>${item.description}</td>
                                <td>${item.requirement}</td>
                                <td>${item.score}/${item.max_score}</td>
                                <td><span class="badge badge-${item.passed ? 'success' : 'danger'}">${item.passed ? 'Pass' : 'Fail'}</span></td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        `;
    }
    
    // Print report
    function printReport() {
        const printContent = document.getElementById('reportContent').innerHTML;
        const originalContent = document.body.innerHTML;
        
        document.body.innerHTML = `
            <html>
                <head>
                    <title>Audit Report</title>
                    <link rel="stylesheet" href="/css/app.css">
                    <style>
                        @media print {
                            .no-print { display: none !important; }
                            body { font-size: 12pt; }
                            .table { font-size: 10pt; }
                        }
                    </style>
                </head>
                <body>
                    ${printContent}
                    <div class="no-print text-center mt-4">
                        <button onclick="window.close()" class="btn btn-secondary">Close</button>
                    </div>
                </body>
            </html>
        `;
        
        window.print();
        document.body.innerHTML = originalContent;
        window.location.reload();
    }
    
    // Download report as PDF
    function downloadReport() {
        alert('PDF download functionality would be implemented here in production.');
        // In production, this would call a backend endpoint to generate PDF
    }
</script>
@endpush