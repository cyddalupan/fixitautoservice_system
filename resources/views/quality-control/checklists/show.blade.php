@extends('layouts.app')

@section('title', $checklist->name)

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col">
            <h1 class="h3 mb-0">{{ $checklist->name }}</h1>
            <p class="text-muted">Quality Control Checklist Details</p>
        </div>
        <div class="col-auto">
            <div class="btn-group">
                <a href="{{ route('quality-control.checklists.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Checklists
                </a>
                <a href="{{ route('quality-control.checklists.edit', $checklist->id) }}" class="btn btn-primary">
                    <i class="fas fa-edit"></i> Edit
                </a>
                <div class="btn-group">
                    <button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-download"></i> Export
                    </button>
                    <div class="dropdown-menu">
                        <a class="dropdown-item" href="{{ route('quality-control.checklists.export', ['id' => $checklist->id, 'format' => 'pdf']) }}">
                            <i class="fas fa-file-pdf text-danger"></i> PDF
                        </a>
                        <a class="dropdown-item" href="{{ route('quality-control.checklists.export', ['id' => $checklist->id, 'format' => 'excel']) }}">
                            <i class="fas fa-file-excel text-success"></i> Excel
                        </a>
                        <a class="dropdown-item" href="{{ route('quality-control.checklists.export', ['id' => $checklist->id, 'format' => 'csv']) }}">
                            <i class="fas fa-file-csv text-info"></i> CSV
                        </a>
                    </div>
                </div>
                <a href="{{ route('audit.create', ['checklist_id' => $checklist->id]) }}" class="btn btn-success">
                    <i class="fas fa-clipboard-check"></i> Start Audit
                </a>
            </div>
        </div>
    </div>

    <!-- Status Banner -->
    <div class="row mb-4">
        <div class="col">
            <div class="card border-{{ $checklist->status === 'active' ? 'success' : ($checklist->status === 'draft' ? 'warning' : 'secondary') }}">
                <div class="card-body py-3">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="d-flex align-items-center">
                                <div class="mr-3">
                                    <span class="badge badge-{{ $checklist->status === 'active' ? 'success' : ($checklist->status === 'draft' ? 'warning' : 'secondary') }} badge-pill py-2 px-3" style="font-size: 1rem;">
                                        {{ ucfirst($checklist->status) }}
                                    </span>
                                </div>
                                <div>
                                    <h4 class="mb-1">{{ $checklist->name }}</h4>
                                    <p class="text-muted mb-0">
                                        <i class="fas fa-wrench mr-1"></i>
                                        {{ $checklist->service_type ? str_replace('_', ' ', ucfirst($checklist->service_type)) : 'General' }}
                                        • Created: {{ $checklist->created_at->format('F d, Y') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 text-right">
                            <div class="row">
                                <div class="col-4">
                                    <div class="text-center">
                                        <div class="h3 mb-0">{{ $checklist->items_count ?? 0 }}</div>
                                        <small class="text-muted">Items</small>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="text-center">
                                        <div class="h3 mb-0">{{ $checklist->audits_count ?? 0 }}</div>
                                        <small class="text-muted">Audits</small>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="text-center">
                                        <div class="h3 mb-0">{{ $checklist->passing_score }}%</div>
                                        <small class="text-muted">Passing</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="row">
        <!-- Left Column: Checklist Details -->
        <div class="col-lg-8">
            <!-- Description -->
            @if($checklist->description)
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-info">Description</h6>
                </div>
                <div class="card-body">
                    <p class="mb-0">{{ $checklist->description }}</p>
                </div>
            </div>
            @endif

            <!-- Checklist Items -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Checklist Items</h6>
                    <span class="badge badge-primary">{{ $checklist->items_count ?? 0 }} items</span>
                </div>
                <div class="card-body">
                    @if($checklist->items && count($checklist->items) > 0)
                        <div class="list-group">
                            @foreach($checklist->items as $index => $item)
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1">
                                            <span class="badge badge-light mr-2">{{ $index + 1 }}</span>
                                            {{ $item->description }}
                                        </h6>
                                        
                                        @if($item->acceptance_criteria)
                                        <div class="mt-2">
                                            <span class="badge badge-success">
                                                <i class="fas fa-check-circle mr-1"></i> Acceptance Criteria
                                            </span>
                                            <span class="ml-2">{{ $item->acceptance_criteria }}</span>
                                        </div>
                                        @endif
                                        
                                        @if($item->standard_reference)
                                        <div class="mt-2">
                                            <span class="badge badge-info">
                                                <i class="fas fa-book mr-1"></i> Standard Reference
                                            </span>
                                            <span class="ml-2">{{ $item->standard_reference }}</span>
                                        </div>
                                        @endif
                                        
                                        @if($item->notes)
                                        <div class="mt-2">
                                            <p class="mb-0 text-muted small">
                                                <i class="fas fa-sticky-note mr-1"></i>
                                                {{ $item->notes }}
                                            </p>
                                        </div>
                                        @endif
                                    </div>
                                    <div class="ml-3">
                                        <span class="badge badge-{{ $item->weight == 2 ? 'danger' : ($item->weight == 1.5 ? 'warning' : 'info') }} badge-pill py-2 px-3">
                                            Weight: {{ $item->weight }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-list-check fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No checklist items defined.</p>
                            <a href="{{ route('quality-control.checklists.edit', $checklist->id) }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Add Items
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Recent Audits -->
            @if($checklist->recentAudits && count($checklist->recentAudits) > 0)
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-warning">Recent Audits</h6>
                    <a href="{{ route('audit.index', ['checklist_id' => $checklist->id]) }}" class="btn btn-sm btn-outline-warning">
                        View All
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Audit ID</th>
                                    <th>Date</th>
                                    <th>Technician</th>
                                    <th>Score</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($checklist->recentAudits as $audit)
                                <tr>
                                    <td>AUD-{{ $audit->id }}</td>
                                    <td>{{ $audit->audit_date->format('M d, Y') }}</td>
                                    <td>{{ $audit->technician->name ?? 'N/A' }}</td>
                                    <td>
                                        @if($audit->score)
                                            <span class="badge badge-{{ $audit->score >= 80 ? 'success' : ($audit->score >= 60 ? 'warning' : 'danger') }}">
                                                {{ $audit->score }}%
                                            </span>
                                        @else
                                            <span class="badge badge-secondary">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $audit->status === 'completed' ? 'success' : ($audit->status === 'in_progress' ? 'warning' : 'secondary') }}">
                                            {{ str_replace('_', ' ', ucfirst($audit->status)) }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('audit.show', $audit->id) }}" class="btn btn-sm btn-outline-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Right Column: Sidebar Information -->
        <div class="col-lg-4">
            <!-- Checklist Information -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-success">Checklist Information</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6>Service Type</h6>
                        <p class="mb-0">
                            <span class="badge badge-info">
                                {{ $checklist->service_type ? str_replace('_', ' ', ucfirst($checklist->service_type)) : 'General' }}
                            </span>
                        </p>
                    </div>
                    
                    <div class="mb-3">
                        <h6>Scoring Settings</h6>
                        <div class="row">
                            <div class="col-6">
                                <div class="text-center p-2 bg-light rounded">
                                    <div class="h4 mb-0">{{ $checklist->passing_score }}%</div>
                                    <small class="text-muted">Passing Score</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="text-center p-2 bg-light rounded">
                                    <div class="h4 mb-0">{{ $checklist->weight }}</div>
                                    <small class="text-muted">Weight</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    @if($checklist->time_limit_minutes)
                    <div class="mb-3">
                        <h6>Time Limit</h6>
                        <p class="mb-0">
                            <i class="fas fa-clock mr-1"></i>
                            {{ $checklist->time_limit_minutes }} minutes
                        </p>
                    </div>
                    @endif
                    
                    <hr>
                    
                    <div class="small text-muted">
                        <p><strong>Created:</strong> {{ $checklist->created_at->format('F d, Y H:i') }}</p>
                        <p><strong>Last Updated:</strong> {{ $checklist->updated_at->format('F d, Y H:i') }}</p>
                        @if($checklist->last_used_at)
                            <p><strong>Last Used:</strong> {{ $checklist->last_used_at->format('F d, Y') }}</p>
                        @endif
                        <p><strong>Created By:</strong> {{ $checklist->creator->name ?? 'System' }}</p>
                    </div>
                </div>
            </div>

            <!-- Performance Statistics -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Performance Statistics</h6>
                </div>
                <div class="card-body">
                    @if($checklist->audits_count > 0)
                        <div class="row text-center">
                            <div class="col-6 mb-3">
                                <div class="h4 mb-0">{{ $checklist->average_score ?? 0 }}%</div>
                                <small class="text-muted">Avg. Score</small>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="h4 mb-0">{{ $checklist->pass_rate ?? 0 }}%</div>
                                <small class="text-muted">Pass Rate</small>
                            </div>
                        </div>
                        
                        <div class="progress mb-3">
                            <div class="progress-bar bg-success" role="progressbar" 
                                 style="width: {{ $checklist->pass_rate ?? 0 }}%" 
                                 aria-valuenow="{{ $checklist->pass_rate ?? 0 }}" 
                                 aria-valuemin="0" aria-valuemax="100">
                                {{ $checklist->pass_rate ?? 0 }}% Pass
                            </div>
                        </div>
                        
                        <div class="small text-muted">
                            <p><i class="fas fa-chart-line mr-1"></i> Based on {{ $checklist->audits_count }} audits</p>
                        </div>
                    @else
                        <div class="text-center py-3">
                            <i class="fas fa-chart-bar fa-2x text-muted mb-3"></i>
                            <p class="text-muted">No audit data available yet.</p>
                            <a href="{{ route('audit.create', ['checklist_id' => $checklist->id]) }}" class="btn btn-sm btn-primary">
                                <i class="fas fa-clipboard-check"></i> Start First Audit
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-danger">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('audit.create', ['checklist_id' => $checklist->id]) }}" class="btn btn-success">
                            <i class="fas fa-clipboard-check"></i> Start New Audit
                        </a>
                        <a href="{{ route('quality-control.checklists.edit', $checklist->id) }}" class="btn btn-primary">
                            <i class="fas fa-edit"></i> Edit Checklist
                        </a>
                        <a href="{{ route('quality-control.checklists.clone', $checklist->id) }}" class="btn btn-outline-secondary">
                            <i class="fas fa-copy"></i> Clone Checklist
                        </a>
                        <button type="button" class="btn btn-outline-danger" onclick="confirmDelete()">
                            <i class="fas fa-trash"></i> Delete Checklist
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirm Deletion</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this checklist?</p>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Warning:</strong> This action cannot be undone. All checklist data and associated audit history will be permanently deleted.
                </div>
                @if($checklist->audits_count > 0)
                <div class="alert alert-warning">
                    <i class="fas fa-info-circle"></i>
                    This checklist has been used in {{ $checklist->audits_count }} audits. Deleting it will remove all associated audit data.
                </div>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <form method="POST" action="{{ route(quality-control.checklists.destroy, $checklist->id) }}" class="d-inline">
                    @csrf
                    @method(DELETE)
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash"></i> Delete Checklist
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push(styles)
<style>
    .list-group-item {
        border-left: 4px solid transparent;
        transition: all 0.2s;
    }
    .list-group-item:hover {
        border-left-color: #4e73df;
        background-color: #f8f9fc;
    }
</style>
@endpush

@push(scripts)
<script>
    function confirmDelete() {
        $(#deleteModal).modal(show);
    }
    
    // Print functionality
    function printChecklist() {
        const printContent = document.createElement(div);
        printContent.innerHTML = `
            <div style="padding: 20px;">
                <h2>${document.querySelector(h1).textContent}</h2>
                <p><strong>Service Type:</strong> {{ $checklist->service_type ? str_replace(_,  , ucfirst($checklist->service_type)) : General }}</p>
                <p><strong>Status:</strong> {{ ucfirst($checklist->status) }}</p>
                <p><strong>Passing Score:</strong> {{ $checklist->passing_score }}%</p>
                <p><strong>Created:</strong> {{ $checklist->created_at->format(F d, Y) }}</p>
                <hr>
                <h3>Checklist Items</h3>
                <ol>
                    @foreach($checklist->items as $item)
                    <li style="margin-bottom: 10px;">
                        <strong>{{ $item->description }}</strong>
                        @if($item->acceptance_criteria)
                        <br><small>Criteria: {{ $item->acceptance_criteria }}</small>
                        @endif
                        @if($item->standard_reference)
                        <br><small>Standard: {{ $item->standard_reference }}</small>
                        @endif
                        @if($item->notes)
                        <br><small>Notes: {{ $item->notes }}</small>
                        @endif
                        <br><small>Weight: {{ $item->weight }}</small>
                    </li>
                    @endforeach
                </ol>
                <hr>
                <p class="text-muted">Printed on: ${new Date().toLocaleDateString()}</p>
            </div>
        `;
        
        const printWindow = window.open(, _blank);
        printWindow.document.write(`
            <html>
                <head>
                    <title>{{ $checklist->name }} - Print</title>
                    <style>
                        body { font-family: Arial, sans-serif; margin: 20px; }
                        h2 { color: #333; }
                        h3 { color: #555; margin-top: 20px; }
                        hr { border: 1px solid #ddd; margin: 20px 0; }
                        ol { margin-left: 20px; }
                        li { margin-bottom: 15px; }
                        .text-muted { color: #6c757d; }
                    </style>
                </head>
                <body>
                    ${printContent.innerHTML}
                </body>
            </html>
        `);
        printWindow.document.close();
        printWindow.print();
    }
</script>
@endpush
