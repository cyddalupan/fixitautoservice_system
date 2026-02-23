@extends('layouts.app')

@section('title', 'Edit Audit')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h1 class="h3 mb-0">Edit Quality Audit</h1>
            <p class="text-muted">Update audit details and results</p>
        </div>
        <div class="col-auto">
            <a href="{{ route('audit.show', $audit->id) }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Back to Audit
            </a>
        </div>
    </div>

    <!-- Audit Edit Form -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Audit Details</h6>
                    <span class="badge badge-{{ $audit->status === 'completed' ? 'success' : ($audit->status === 'in_progress' ? 'warning' : 'secondary') }}">
                        {{ str_replace('_', ' ', ucfirst($audit->status)) }}
                    </span>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('audit.update', $audit->id) }}" id="auditForm">
                        @csrf
                        @method('PUT')

                        <!-- Basic Information -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="title" class="form-label">Audit Title *</label>
                                    <input type="text" class="form-control @error('title') is-invalid @enderror" 
                                           id="title" name="title" value="{{ old('title', $audit->title) }}" required>
                                    @error('title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="audit_date" class="form-label">Audit Date *</label>
                                    <input type="date" class="form-control @error('audit_date') is-invalid @enderror" 
                                           id="audit_date" name="audit_date" value="{{ old('audit_date', $audit->audit_date) }}" required>
                                    @error('audit_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Status -->
                        <div class="form-group mb-4">
                            <label for="status" class="form-label">Status *</label>
                            <select class="form-control @error('status') is-invalid @enderror" 
                                    id="status" name="status" required>
                                <option value="scheduled" {{ old('status', $audit->status) == 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                                <option value="in_progress" {{ old('status', $audit->status) == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                <option value="completed" {{ old('status', $audit->status) == 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="cancelled" {{ old('status', $audit->status) == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Results Section (only show if completed) -->
                        @if($audit->status === 'completed' || old('status') === 'completed')
                        <div class="form-group mb-4" id="resultsSection">
                            <label class="form-label">Audit Results</label>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="score" class="form-label">Score (%)</label>
                                        <input type="number" class="form-control @error('score') is-invalid @enderror" 
                                               id="score" name="score" min="0" max="100" 
                                               value="{{ old('score', $audit->score) }}">
                                        @error('score')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="passed_items" class="form-label">Passed Items</label>
                                        <input type="number" class="form-control @error('passed_items') is-invalid @enderror" 
                                               id="passed_items" name="passed_items" min="0" 
                                               value="{{ old('passed_items', $audit->passed_items) }}">
                                        @error('passed_items')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="total_items" class="form-label">Total Items</label>
                                        <input type="number" class="form-control @error('total_items') is-invalid @enderror" 
                                               id="total_items" name="total_items" min="0" 
                                               value="{{ old('total_items', $audit->total_items) }}">
                                        @error('total_items')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Findings -->
                        <div class="form-group mb-4">
                            <label for="findings" class="form-label">Findings & Observations</label>
                            <textarea class="form-control @error('findings') is-invalid @enderror" 
                                      id="findings" name="findings" rows="4">{{ old('findings', $audit->findings) }}</textarea>
                            @error('findings')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Detailed observations from the audit</small>
                        </div>

                        <!-- Recommendations -->
                        <div class="form-group mb-4">
                            <label for="recommendations" class="form-label">Recommendations</label>
                            <textarea class="form-control @error('recommendations') is-invalid @enderror" 
                                      id="recommendations" name="recommendations" rows="3">{{ old('recommendations', $audit->recommendations) }}</textarea>
                            @error('recommendations')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Suggested improvements or corrective actions</small>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="form-group">
                            <div class="btn-group">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Update Audit
                                </button>
                                @if($audit->status !== 'completed')
                                    <button type="button" class="btn btn-success" onclick="completeAudit()">
                                        <i class="fas fa-check-circle"></i> Mark as Complete
                                    </button>
                                @endif
                                <button type="button" class="btn btn-outline-danger" onclick="confirmDelete()">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </div>
                        </div>
                    </form>

                    <!-- Delete Form -->
                    <form method="POST" action="{{ route('audit.destroy', $audit->id) }}" id="deleteForm" class="d-none">
                        @csrf
                        @method('DELETE')
                    </form>
                </div>
            </div>

            <!-- Audit History -->
            <div class="card shadow mt-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-info">Audit History</h6>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        @if($audit->created_at)
                        <div class="timeline-item">
                            <div class="timeline-marker bg-primary"></div>
                            <div class="timeline-content">
                                <h6 class="mb-1">Audit Created</h6>
                                <p class="text-muted mb-0">{{ $audit->created_at->format('M d, Y H:i') }}</p>
                                <small>By: {{ $audit->creator->name ?? 'System' }}</small>
                            </div>
                        </div>
                        @endif
                        
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

        <!-- Sidebar: Audit Summary -->
        <div class="col-lg-4">
            <!-- Checklist Info -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Checklist Information</h6>
                </div>
                <div class="card-body">
                    <h6>{{ $audit->checklist->name ?? 'No Checklist' }}</h6>
                    <p class="text-muted mb-2">
                        <i class="fas fa-wrench mr-1"></i>
                        {{ $audit->checklist->service_type ? str_replace('_', ' ', ucfirst($audit->checklist->service_type)) : 'General' }}
                    </p>
                    <div class="small text-muted">
                        <div class="mb-1">
                            <i class="fas fa-list-check mr-1"></i>
                            Items: {{ $audit->checklist->items_count ?? 0 }}
                        </div>
                        <div>
                            <i class="fas fa-percent mr-1"></i>
                            Passing Score: {{ $audit->checklist->passing_score ?? 80 }}%
                        </div>
                    </div>
                </div>
            </div>

            <!-- Personnel -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-info">Personnel</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6 class="small text-muted mb-1">Technician</h6>
                        <p class="mb-0">
                            <i class="fas fa-user-hard-hat mr-1"></i>
                            {{ $audit->technician->name ?? 'Not Assigned' }}
                        </p>
                    </div>
                    <div>
                        <h6 class="small text-muted mb-1">Auditor</h6>
                        <p class="mb-0">
                            <i class="fas fa-user-check mr-1"></i>
                            {{ $audit->auditor->name ?? 'Not Assigned' }}
                        </p>
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
                    <div class="mb-3">
                        <h6 class="small text-muted mb-1">Vehicle</h6>
                        <p class="mb-0">
                            <i class="fas fa-car mr-1"></i>
                            {{ $audit->vehicle->make }} {{ $audit->vehicle->model }}
                            <small class="d-block text-muted">{{ $audit->vehicle->license_plate }}</small>
                        </p>
                    </div>
                    @endif
                    
                    @if($audit->workOrder)
                    <div>
                        <h6 class="small text-muted mb-1">Work Order</h6>
                        <p class="mb-0">
                            <i class="fas fa-clipboard-list mr-1"></i>
                            WO-{{ $audit->workOrder->id }}
                            <small class="d-block text-muted">{{ $audit->workOrder->customer->name ?? 'Unknown Customer' }}</small>
                        </p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-warning">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <a href="{{ route('audit.show', $audit->id) }}" class="list-group-item list-group-item-action">
                            <i class="fas fa-eye mr-2"></i> View Audit Details
                        </a>
                        @if($audit->status === 'completed')
                            <a href="{{ route('audit.export', ['id' => $audit->id, 'format' => 'pdf']) }}" class="list-group-item list-group-item-action">
                                <i class="fas fa-file-pdf mr-2"></i> Export as PDF
                            </a>
                        @endif
                        @if($audit->nonConformanceReports->count() > 0)
                            <a href="{{ route('ncr.index', ['audit_id' => $audit->id]) }}" class="list-group-item list-group-item-action">
                                <i class="fas fa-exclamation-triangle mr-2"></i> View NCRs ({{ $audit->nonConformanceReports->count() }})
                            </a>
                        @endif
                        <a href="{{ route('audit.clone', $audit->id) }}" class="list-group-item list-group-item-action">
                            <i class="fas fa-copy mr-2"></i> Clone Audit
                        </a>
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
                <button type="button" class="btn btn-danger" onclick="document.getElementById('deleteForm').submit()">
                    <i class="fas fa-trash"></i> Delete Audit
                </button>
            </div>
        </div>
    </div>
</div>
</div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="passed_items" class="form-label">Passed Items</label>
                                <input type="number" class="form-control" id="passed_items" name="passed_items" min="0" value="{{ old(passed_items, $audit->passed_items) }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="total_items" class="form-label">Total Items</label>
                                <input type="number" class="form-control" id="total_items" name="total_items" min="0" value="{{ old(total_items, $audit->total_items) }}">
                            </div>
                        </div>
                    </div>
                `;
                statusField.after(newSection);
            } else {
                resultsSection.style.display = block;
            }
        } else if (resultsSection) {
            resultsSection.style.display = none;
        }
    });
    
    // Complete audit function
    function completeAudit() {
        if (confirm(Mark this audit as completed? This will enable results entry.)) {
            document.getElementById(status).value = completed;
            document.getElementById(auditForm).submit();
        }
    }
    
    // Confirm delete
    function confirmDelete() {
        $(#deleteModal).modal(show);
    }
    
    // Auto-calculate score if passed_items and total_items are entered
    document.addEventListener(input, function(e) {
        if (e.target.id === passed_items || e.target.id === total_items) {
            const passed = parseInt(document.getElementById(passed_items).value) || 0;
            const total = parseInt(document.getElementById(total_items).value) || 0;
            
            if (total > 0) {
                const score = Math.round((passed / total) * 100);
                document.getElementById(score).value = score;
            }
        }
    });
    
    // Initialize based on current status
    document.addEventListener(DOMContentLoaded, function() {
        const status = document.getElementById(status).value;
        if (status !== completed) {
            const resultsSection = document.getElementById(resultsSection);
            if (resultsSection) {
                resultsSection.style.display = none;
            }
        }
    });
</script>
@endpush
