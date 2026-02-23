@extends('layouts.app')

@section('title', 'Quality Control Checklists')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h1 class="h3 mb-0">Quality Control Checklists</h1>
            <p class="text-muted">Manage and organize quality control checklists for different service types</p>
        </div>
        <div class="col-auto">
            <a href="{{ route('quality-control.checklists.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> New Checklist
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filters</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('quality-control.checklists') }}" class="row">
                <div class="col-md-4 mb-3">
                    <label for="service_type" class="form-label">Service Type</label>
                    <select class="form-control" id="service_type" name="service_type">
                        <option value="">All Service Types</option>
                        @foreach($serviceTypes as $type)
                            <option value="{{ $type['value'] }}" {{ request('service_type') == $type['value'] ? 'selected' : '' }}>
                                {{ $type['label'] }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="is_active" class="form-label">Status</label>
                    <select class="form-control" id="is_active" name="is_active">
                        <option value="">All Status</option>
                        <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-md-4 mb-3 d-flex align-items-end">
                    <div class="btn-group w-100">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter"></i> Apply Filters
                        </button>
                        <a href="{{ route('quality-control.checklists') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times"></i> Clear
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Checklists Table -->
    <div class="card shadow">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Checklists ({{ $checklists->total() }})</h6>
            <div class="btn-group">
                <button class="btn btn-sm btn-outline-secondary" onclick="window.print()">
                    <i class="fas fa-print"></i> Print
                </button>
            </div>
        </div>
        <div class="card-body">
            @if($checklists->isEmpty())
                <div class="text-center py-5">
                    <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No checklists found</h5>
                    <p class="text-muted">Create your first quality control checklist</p>
                    <a href="{{ route('quality-control.checklists.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Create Checklist
                    </a>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Service Type</th>
                                <th>Items</th>
                                <th>Passing Score</th>
                                <th>Audits</th>
                                <th>Status</th>
                                <th>Last Updated</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($checklists as $checklist)
                            <tr>
                                <td>
                                    <strong>{{ $checklist->name }}</strong>
                                    @if($checklist->description)
                                        <br><small class="text-muted">{{ Str::limit($checklist->description, 50) }}</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-info">
                                        {{ str_replace('_', ' ', ucfirst($checklist->service_type)) }}
                                    </span>
                                </td>
                                <td>
                                    @php
                                        $items = json_decode($checklist->checklist_items, true);
                                        $itemCount = is_array($items) ? count($items) : 0;
                                    @endphp
                                    {{ $itemCount }} items
                                </td>
                                <td>
                                    <span class="badge badge-{{ $checklist->passing_score >= 80 ? 'success' : ($checklist->passing_score >= 70 ? 'warning' : 'danger') }}">
                                        {{ $checklist->passing_score }}%
                                    </span>
                                </td>
                                <td>
                                    @php
                                        $auditCount = $checklist->audits()->count();
                                        $passedCount = $checklist->audits()->whereColumn('percentage_score', '>=', 'passing_score')->count();
                                        $passRate = $auditCount > 0 ? round(($passedCount / $auditCount) * 100, 1) : 0;
                                    @endphp
                                    <div class="d-flex align-items-center">
                                        <div class="progress flex-grow-1 mr-2" style="height: 6px;">
                                            <div class="progress-bar bg-{{ $passRate >= 80 ? 'success' : ($passRate >= 70 ? 'warning' : 'danger') }}" 
                                                 style="width: {{ $passRate }}%"></div>
                                        </div>
                                        <small>{{ $passRate }}%</small>
                                    </div>
                                    <small class="text-muted">{{ $auditCount }} audits</small>
                                </td>
                                <td>
                                    @if($checklist->is_active)
                                        <span class="badge badge-success">Active</span>
                                    @else
                                        <span class="badge badge-secondary">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <small class="text-muted">
                                        {{ $checklist->updated_at->format('m/d/Y') }}<br>
                                        by {{ $checklist->updater->name ?? 'System' }}
                                    </small>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('quality-control.checklists.show', $checklist->id) }}" 
                                           class="btn btn-outline-primary" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('quality-control.checklists.edit', $checklist->id) }}" 
                                           class="btn btn-outline-warning" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-outline-info" 
                                                onclick="createNewVersion({{ $checklist->id }})" title="New Version">
                                            <i class="fas fa-copy"></i>
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
                        Showing {{ $checklists->firstItem() }} to {{ $checklists->lastItem() }} of {{ $checklists->total() }} entries
                    </div>
                    <div>
                        {{ $checklists->links() }}
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
                                Total Checklists
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $checklists->total() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
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
                                Active Checklists
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $checklists->where('is_active', true)->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
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
                                Service Types
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $serviceTypes->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-tags fa-2x text-gray-300"></i>
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
                                Avg Items per Checklist
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                @php
                                    $totalItems = 0;
                                    $totalChecklists = $checklists->count();
                                    foreach($checklists as $checklist) {
                                        $items = json_decode($checklist->checklist_items, true);
                                        $totalItems += is_array($items) ? count($items) : 0;
                                    }
                                    $avgItems = $totalChecklists > 0 ? round($totalItems / $totalChecklists, 1) : 0;
                                @endphp
                                {{ $avgItems }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-list-ol fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- New Version Modal -->
<div class="modal fade" id="newVersionModal" tabindex="-1" role="dialog" aria-labelledby="newVersionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="newVersionModalLabel">Create New Checklist Version</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="newVersionForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div id="newVersionContent">
                        <!-- Content will be loaded via AJAX -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create New Version</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Create new version modal
    function createNewVersion(checklistId) {
        fetch(`/quality-control/checklists/${checklistId}/edit?version=true`)
            .then(response => response.text())
            .then(html => {
                document.getElementById('newVersionContent').innerHTML = html;
                $('#newVersionModal').modal('show');
                
                // Update form action
                document.getElementById('newVersionForm').action = `/quality-control/checklists/${checklistId}/versions`;
            })
            .catch(error => {
                alert('Error loading checklist data.');
            });
    }
    
    // Submit new version form
    document.getElementById('newVersionForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        fetch(this.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                $('#newVersionModal').modal('hide');
                window.location.href = data.redirect;
            } else {
                alert('Error creating new version.');
            }
        })
        .catch(error => {
            alert('Error creating new version.');
        });
    });
</script>
@endpush