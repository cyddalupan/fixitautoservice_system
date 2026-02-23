@extends('layouts.app')

@section('title', 'Audit Details')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col">
            <h1 class="h3 mb-0">Quality Audit Details</h1>
            <p class="text-muted">Complete audit information and results</p>
        </div>
        <div class="col-auto">
            <div class="btn-group">
                <a href="{{ route('audit.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Audits
                </a>
                <a href="{{ route('audit.edit', $audit->id) }}" class="btn btn-primary">
                    <i class="fas fa-edit"></i> Edit Audit
                </a>
                <div class="btn-group">
                    <button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-download"></i> Export
                    </button>
                    <div class="dropdown-menu">
                        <a class="dropdown-item" href="{{ route('audit.export', ['id' => $audit->id, 'format' => 'pdf']) }}">
                            <i class="fas fa-file-pdf text-danger"></i> PDF Report
                        </a>
                        <a class="dropdown-item" href="{{ route('audit.export', ['id' => $audit->id, 'format' => 'excel']) }}">
                            <i class="fas fa-file-excel text-success"></i> Excel
                        </a>
                        <a class="dropdown-item" href="{{ route('audit.export', ['id' => $audit->id, 'format' => 'csv']) }}">
                            <i class="fas fa-file-csv text-info"></i> CSV
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Banner -->
    <div class="row mb-4">
        <div class="col">
            <div class="card border-{{ $audit->status === 'completed' ? 'success' : ($audit->status === 'in_progress' ? 'warning' : 'secondary') }}">
                <div class="card-body py-3">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h4 class="mb-1">{{ $audit->title }}</h4>
                            <p class="text-muted mb-0">
                                <i class="fas fa-calendar-alt mr-1"></i>
                                {{ $audit->audit_date->format('F d, Y') }}
                                @if($audit->completed_at)
                                    • Completed: {{ $audit->completed_at->format('M d, Y H:i') }}
                                @endif
                            </p>
                        </div>
                        <div class="col-md-4 text-right">
                            <span class="badge badge-{{ $audit->status === 'completed' ? 'success' : ($audit->status === 'in_progress' ? 'warning' : 'secondary') }} badge-pill py-2 px-3" style="font-size: 1rem;">
                                {{ str_replace('_', ' ', ucfirst($audit->status)) }}
                            </span>
                            @if($audit->score)
                                <div class="mt-2">
                                    <span class="h4 font-weight-bold text-{{ $audit->score >= 80 ? 'success' : ($audit->score >= 60 ? 'warning' : 'danger') }}">
                                        {{ $audit->score }}%
                                    </span>
                                    <small class="text-muted">Score</small>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="row">
        <!-- Left Column: Audit Details -->
        <div class="col-lg-8">
            <!-- Results Summary -->
            @if($audit->status === 'completed')
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-success">Audit Results</h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-4 mb-3">
                            <div class="card border-success">
                                <div class="card-body">
                                    <h2 class="display-4 text-success">{{ $audit->score }}%</h2>
                                    <p class="text-muted mb-0">Overall Score</p>
                                    <small class="text-{{ $audit->score >= 80 ? 'success' : ($audit->score >= 60 ? 'warning' : 'danger') }}">
                                        {{ $audit->score >= 80 ? 'PASS' : ($audit->score >= 60 ? 'MARGINAL' : 'FAIL') }}
                                    </small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card border-primary">
                                <div class="card-body">
                                    <h2 class="display-4 text-primary">{{ $audit->passed_items }}</h2>
                                    <p class="text-muted mb-0">Passed Items</p>
                                    <small>Out of {{ $audit->total_items }} total</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card border-{{ $audit->total_items - $audit->passed_items > 0 ? 'danger' : 'success' }}">
                                <div class="card-body">
                                    <h2 class="display-4 text-{{ $audit->total_items - $audit->passed_items > 0 ? 'danger' : 'success' }}">
                                        {{ $audit->total_items - $audit->passed_items }}
                                    </h2>
                                    <p class="text-muted mb-0">Failed Items</p>
                                    <small>{{ $audit->total_items > 0 ? round((($audit->total_items - $audit->passed_items) / $audit->total_items) * 100, 1) : 0 }}% of total</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Findings & Observations -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-info">Findings & Observations</h6>
                    @if($audit->findings)
                        <span class="badge badge-info">{{ str_word_count($audit->findings) }} words</span>
                    @endif
                </div>
                <div class="card-body">
                    @if($audit->findings)
                        <div class="audit-content">
                            {!! nl2br(e($audit->findings)) !!}
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-search fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No findings recorded for this audit.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Recommendations -->
            @if($audit->recommendations)
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-warning">Recommendations</h6>
                </div>
                <div class="card-body">
                    <div class="audit-content">
                        {!! nl2br(e($audit->recommendations)) !!}
                    </div>
                </div>
            </div>
            @endif

            <!-- Non-Conformance Reports -->
            @if($audit->nonConformanceReports->count() > 0)
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-danger">Non-Conformance Reports</h6>
                    <span class="badge badge-danger">{{ $audit->nonConformanceReports->count() }} NCRs</span>
                </div>
                <div class="card-body">
                    <div class="list-group">
                        @foreach($audit->nonConformanceReports as $ncr)
                        <a href="{{ route('ncr.show', $ncr->id) }}" class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">NCR-{{ $ncr->id }}: {{ $ncr->title }}</h6>
                                <small class="text-muted">{{ $ncr->created_at->diffForHumans() }}</small>
                            </div>
                            <p class="mb-1 text-truncate">{{ Str::limit($ncr->description, 100) }}</p>
                            <small>
                                <span class="badge badge-{{ $ncr->status === 'closed' ? 'success' : ($ncr->status === 'in_progress' ? 'warning' : 'secondary') }}">
                                    {{ str_replace('_', ' ', ucfirst($ncr->status)) }}
                                </span>
                                @if($ncr->severity)
                                    <span class="badge badge-{{ $ncr->severity === 'critical' ? 'danger' : ($ncr->severity === 'major' ? 'warning' : 'info') }}">
                                        {{ ucfirst($ncr->severity) }}
                                    </span>
                                @endif
                            </small>
                        </a>
                        @endforeach
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
                    <h6 class="m-0 font-weight-bold text-primary">Checklist Information</h6>
                </div>
                <div class="card-body">
                    <h5>{{ $audit->checklist->name ?? 'No Checklist' }}</h5>
                    <div class="mb-3">
                        <span class="badge badge-info">
                            <i class="fas fa-wrench mr-1"></i>
                            {{ $audit->checklist->service_type ? str_replace('_', ' ', ucfirst($audit->checklist->service_type)) : 'General' }}
                        </span>
                    </div>
                    
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="mb-2">
                                <div class="h4 mb-0">{{ $audit->checklist->items_count ?? 0 }}</div>
                                <small class="text-muted">Total Items</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="mb-2">
                                <div class="h4 mb-0">{{ $audit->checklist->passing_score ?? 80 }}%</div>
                                <small class="text-muted">Passing Score</small>
                            </div>
                        </div>
                    </div>
                    
                    <hr>
                    <p class="small text-muted mb-0">
                        <i class="fas fa-info-circle mr-1"></i>
                        {{ $audit->checklist->description ?? 'No description available.' }}
                    </p>
                </div>
            </div>

            <!-- Personnel -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-info">Personnel</h6>
                </div>
                <div class="card-body">
                    <div class="media mb-3">
                        <div class="media-body">
                            <h6 class="mt-0 mb-1">Technician</h6>
                            <p class="mb-0">
                                <i class="fas fa-user-hard-hat mr-1"></i>
                                {{ $audit->technician->name ?? 'Not Assigned' }}
                            </p>
                            @if($audit->technician)
                                <small class="text-muted">{{ $audit->technician->email ?? '' }}</small>
                            @endif
                        </div>
                    </div>
                    
                    <div class="media">
                        <div class="media-body">
                            <h6 class="mt-0 mb-1">Auditor</h6>
                            <p class="mb-0">
                                <i class="fas fa-user-check mr-1"></i>
                                {{ $audit->auditor->name ?? 'Not Assigned' }}
                            </p>
                            @if($audit->auditor)
                                <small class="text-muted">{{ $audit->auditor->email ?? '' }}</small>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Related Information -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-success">Related Information</h6>
                </div>
                <div class="card-body">
                    @if($audit->vehicle)
                    <div class="media mb-3">
                        <div class="media-body">
                            <h6 class="mt-0 mb-1">Vehicle</h6>
                            <p class="mb-0">
                                <i class="fas fa-car mr-1"></i>
                                {{ $audit->vehicle->make }} {{ $audit->vehicle->model }}
                            </p>
                            <small class="text-muted">
                                {{ $audit->vehicle->year }} • {{ $audit->vehicle->license_plate }}
                                @if($audit->vehicle->vin)
                                    <br>VIN: {{ $audit->vehicle->vin }}
                                @endif
                            </small>
                        </div>
                    </div>
                    @endif
                    
                    @if($audit->workOrder)
                    <div class="media">
                        <div class="media-body">
                            <h6 class="mt-0 mb-1">Work Order</h6>
                            <p class="mb-0">
                                <i class="fas fa-clipboard-list mr-1"></i>
                                WO-{{ $audit->workOrder->id }}
                            </p>
                            <small class="text-muted">
                                {{ $audit->workOrder->customer->name ?? 'Unknown Customer' }}
                                @if($audit->workOrder->service_type)
                                    <br>Service: {{ $audit->workOrder->service_type }}
                                @endif
                            </small>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Audit Timeline -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-secondary">Audit Timeline</h6>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-marker bg-primary"></div>
                            <div class="timeline-content">
                                <h6 class="mb-1">Audit Created</h6>
                                <p class="text-muted mb-0">{{ $audit->created_at->format('M d, Y H:i') }}</p>
                                <small>By: {{ $audit->creator->name ?? 'System' }}</small>
                            </div>
                        </div>
                        
                        @if($audit->updated_at && $audit->updated_at != $audit->created_at)
                        <div class="timeline-item">
                            <div class="timeline-marker bg-warning"></div>
                            <div class="timeline-content">
                                <h6 class="mb-1">Last Updated</h6>
                                <p class="text-muted mb-0">{{ $audit->updated_at->format('M d, Y H:i') }}</p>
                            </div>
                        </div>
                        @endif
                        
                        @if($audit->completed_at)
                        <div class="timeline-item">
                            <div class="timeline-marker bg-success"></div>
                            <div class="timeline-content">
                                <h6 class="mb-1">Audit Completed</h6>
                                <p class="text-muted mb-0">{{ $audit->completed_at->format('M d, Y H:i') }}</p>
                                <small>Score: {{ $audit->score }}%</small>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="row mt-4">
        <div class="col">
            <div class="card shadow">
                <div class="card-body">
                    <div class="btn-group">
                        <a href="{{ route('audit.edit', $audit->id) }}" class="btn btn-primary">
                            <i class="fas fa-edit"></i> Edit Audit
                        </a>
                        @if($audit->status !== 'completed')
                            <a href="{{ route('audit.complete', $audit->id) }}" class="btn btn-success" onclick="return confirm('Mark this audit as completed?')">
                                <i class="fas fa-check-circle"></i> Mark Complete
                            </a>
                        @endif
                        <a href="{{ route('ncr.create', ['audit_id' => $audit->id]) }}" class="btn btn-danger">
                                <i class="fas fa-exclamation-triangle"></i> Create NCR
                            </a>
                            <a href="{{ route("audit.clone", $audit->id) }}" class="btn btn-outline-secondary">
                                <i class="fas fa-copy"></i> Clone Audit
                            </a>
                            <button type="button" class="btn btn-outline-danger" onclick="confirmDelete()">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </div>
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
                <p>Are you sure you want to delete this audit?</p>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    This action cannot be undone. All audit data will be permanently deleted.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" onclick="document.getElementById(deleteForm).submit()">
                    <i class="fas fa-trash"></i> Delete Audit
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Form -->
<form method="POST" action="{{ route(audit.destroy, $audit->id) }}" id="deleteForm" class="d-none">
    @csrf
    @method(DELETE)
</form>
@endsection

@push(styles)
<style>
    .audit-content {
        line-height: 1.6;
        white-space: pre-wrap;
    }
    .timeline {
        position: relative;
        padding-left: 30px;
    }
    .timeline-item {
        position: relative;
        margin-bottom: 20px;
    }
    .timeline-marker {
        position: absolute;
        left: -30px;
        top: 0;
        width: 20px;
        height: 20px;
        border-radius: 50%;
    }
    .timeline-content {
        padding-left: 10px;
    }
</style>
@endpush

@push(scripts)
<script>
    function confirmDelete() {
        $(#deleteModal).modal(show);
    }
    
    // Print functionality
    function printAudit() {
        window.print();
    }
</script>
@endpush
