@extends('layouts.app')

@section('title', $document->title . ' - Customer Portal')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-file-alt me-2"></i>{{ $document->title }}
                    </h3>
                    <div class="card-tools">
                        <span class="badge bg-{{ $document->document_type === 'invoice' ? 'primary' : ($document->document_type === 'estimate' ? 'success' : ($document->document_type === 'inspection' ? 'info' : 'secondary')) }}">
                            {{ ucfirst($document->document_type) }}
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5>Document Details</h5>
                            <table class="table table-sm">
                                <tr>
                                    <th>Document ID:</th>
                                    <td>DOC-{{ str_pad($document->id, 6, '0', STR_PAD_LEFT) }}</td>
                                </tr>
                                <tr>
                                    <th>Type:</th>
                                    <td>
                                        <span class="badge bg-{{ $document->document_type === 'invoice' ? 'primary' : ($document->document_type === 'estimate' ? 'success' : ($document->document_type === 'inspection' ? 'info' : 'secondary')) }}">
                                            {{ ucfirst($document->document_type) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Shared Date:</th>
                                    <td>{{ $document->created_at->format('F d, Y g:i A') }}</td>
                                </tr>
                                <tr>
                                    <th>File Size:</th>
                                    <td>{{ number_format($document->file_size / 1024, 2) }} KB</td>
                                </tr>
                                <tr>
                                    <th>File Format:</th>
                                    <td>{{ strtoupper(pathinfo($document->file_name, PATHINFO_EXTENSION)) }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h5>Related Information</h5>
                            <table class="table table-sm">
                                @if($document->vehicle)
                                <tr>
                                    <th>Vehicle:</th>
                                    <td>
                                        {{ $document->vehicle->make }} {{ $document->vehicle->model }}
                                        <div class="text-muted small">
                                            {{ $document->vehicle->year }} • {{ $document->vehicle->license_plate }}
                                        </div>
                                    </td>
                                </tr>
                                @endif
                                @if($document->workOrder)
                                <tr>
                                    <th>Work Order:</th>
                                    <td>
                                        <a href="{{ route('portal.work-orders.show', $document->workOrder) }}">
                                            #{{ $document->workOrder->id }}
                                        </a>
                                        <div class="text-muted small">
                                            {{ $document->workOrder->service_type }}
                                        </div>
                                    </td>
                                </tr>
                                @endif
                                @if($document->appointment)
                                <tr>
                                    <th>Appointment:</th>
                                    <td>
                                        <a href="{{ route('portal.appointments.show', $document->appointment) }}">
                                            {{ $document->appointment->appointment_date->format('M d, Y') }}
                                        </a>
                                        <div class="text-muted small">
                                            {{ $document->appointment->appointment_time }}
                                        </div>
                                    </td>
                                </tr>
                                @endif
                            </table>
                        </div>
                    </div>

                    <div class="mb-4">
                        <h5>Document Description</h5>
                        <div class="p-3 bg-light rounded">
                            {{ $document->description ?? 'No description provided.' }}
                        </div>
                    </div>

                    <div class="mb-4">
                        <h5>Document Preview</h5>
                        <div class="text-center p-4 border rounded bg-light">
                            @if(in_array(pathinfo($document->file_name, PATHINFO_EXTENSION), ['pdf']))
                                <i class="fas fa-file-pdf fa-4x text-danger mb-3"></i>
                                <p class="mb-0">PDF Document</p>
                                <p class="text-muted small">Click download to view the full document</p>
                            @elseif(in_array(pathinfo($document->file_name, PATHINFO_EXTENSION), ['jpg', 'jpeg', 'png', 'gif']))
                                <i class="fas fa-file-image fa-4x text-success mb-3"></i>
                                <p class="mb-0">Image Document</p>
                                <p class="text-muted small">Click download to view the full image</p>
                            @elseif(in_array(pathinfo($document->file_name, PATHINFO_EXTENSION), ['doc', 'docx']))
                                <i class="fas fa-file-word fa-4x text-primary mb-3"></i>
                                <p class="mb-0">Word Document</p>
                                <p class="text-muted small">Click download to view the full document</p>
                            @else
                                <i class="fas fa-file fa-4x text-secondary mb-3"></i>
                                <p class="mb-0">{{ strtoupper(pathinfo($document->file_name, PATHINFO_EXTENSION)) }} Document</p>
                                <p class="text-muted small">Click download to view the full document</p>
                            @endif
                        </div>
                    </div>

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Important:</strong> This document is for your records only. Please download and save a copy for your files.
                    </div>
                </div>
                <div class="card-footer">
                    <div class="d-flex justify-content-between">
                        <div>
                            <a href="{{ route('portal.documents.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Back to Documents
                            </a>
                        </div>
                        <div>
                            <div class="btn-group">
                                <a href="{{ route('portal.documents.download', $document) }}" 
                                   class="btn btn-primary">
                                    <i class="fas fa-download me-1"></i> Download Document
                                </a>
                                <button type="button" class="btn btn-outline-primary dropdown-toggle dropdown-toggle-split" 
                                        data-bs-toggle="dropdown" aria-expanded="false">
                                    <span class="visually-hidden">Toggle Dropdown</span>
                                </button>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a class="dropdown-item" href="#" onclick="window.print()">
                                            <i class="fas fa-print me-2"></i> Print
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#shareModal">
                                            <i class="fas fa-share me-2"></i> Share
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <a class="dropdown-item text-danger" href="#" data-bs-toggle="modal" data-bs-target="#deleteModal">
                                            <i class="fas fa-trash me-2"></i> Remove from Portal
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-history me-2"></i>Document History
                    </h3>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-icon bg-primary">
                                <i class="fas fa-share"></i>
                            </div>
                            <div class="timeline-content">
                                <h6>Document Shared</h6>
                                <p class="text-muted small mb-0">
                                    {{ $document->created_at->format('F d, Y g:i A') }}
                                </p>
                                <p class="small mb-0">
                                    Shared by shop staff
                                </p>
                            </div>
                        </div>
                        
                        @if($document->viewed_at)
                        <div class="timeline-item">
                            <div class="timeline-icon bg-success">
                                <i class="fas fa-eye"></i>
                            </div>
                            <div class="timeline-content">
                                <h6>Document Viewed</h6>
                                <p class="text-muted small mb-0">
                                    {{ $document->viewed_at->format('F d, Y g:i A') }}
                                </p>
                                <p class="small mb-0">
                                    You viewed this document
                                </p>
                            </div>
                        </div>
                        @endif
                        
                        @if($document->downloaded_at)
                        <div class="timeline-item">
                            <div class="timeline-icon bg-info">
                                <i class="fas fa-download"></i>
                            </div>
                            <div class="timeline-content">
                                <h6>Document Downloaded</h6>
                                <p class="text-muted small mb-0">
                                    {{ $document->downloaded_at->format('F d, Y g:i A') }}
                                </p>
                                <p class="small mb-0">
                                    You downloaded this document
                                </p>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-tags me-2"></i>Document Tags
                    </h3>
                </div>
                <div class="card-body">
                    @if($document->tags)
                        <div class="d-flex flex-wrap gap-2">
                            @foreach(explode(',', $document->tags) as $tag)
                                <span class="badge bg-secondary">{{ trim($tag) }}</span>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted mb-0">No tags assigned to this document.</p>
                    @endif
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-shield-alt me-2"></i>Security Information
                    </h3>
                </div>
                <div class="card-body">
                    <div class="alert alert-success small">
                        <i class="fas fa-check-circle me-2"></i>
                        <strong>Secure Access:</strong> This document is securely stored and accessible only to you.
                    </div>
                    <ul class="list-unstyled small">
                        <li class="mb-2">
                            <i class="fas fa-lock text-success me-2"></i>
                            Encrypted storage
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-user-check text-success me-2"></i>
                            Personal access only
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-clock text-success me-2"></i>
                            Available for 90 days
                        </li>
                        <li>
                            <i class="fas fa-history text-success me-2"></i>
                            Access logging enabled
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Share Modal -->
<div class="modal fade" id="shareModal" tabindex="-1" aria-labelledby="shareModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="shareModalLabel">
                    <i class="fas fa-share me-2"></i>Share Document
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Share this document via email or generate a secure link.</p>
                <div class="mb-3">
                    <label for="shareEmail" class="form-label">Email Address</label>
                    <input type="email" class="form-control" id="shareEmail" placeholder="name@example.com">
                </div>
                <div class="mb-3">
                    <label for="shareMessage" class="form-label">Message (Optional)</label>
                    <textarea class="form-control" id="shareMessage" rows="3" 
                              placeholder="Add a message..."></textarea>
                </div>
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="shareExpire">
                    <label class="form-check-label" for="shareExpire">
                        Expire link after 7 days
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary">
                    <i class="fas fa-paper-plane me-1"></i> Send
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-danger" id="deleteModalLabel">
                    <i class="fas fa-exclamation-triangle me-2"></i>Remove Document
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <strong>Warning:</strong> This will only remove the document from your portal view. The shop will still have access to the original document.
                </div>
                <p>Are you sure you want to remove this document from your portal?</p>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="confirmDelete">
                    <label class="form-check-label" for="confirmDelete">
                        Yes, I understand this action cannot be undone
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn" disabled>
                    <i class="fas fa-trash me-1"></i> Remove Document
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Enable delete button when checkbox is checked
        $('#confirmDelete').change(function() {
            $('#confirmDeleteBtn').prop('disabled', !$(this).is(':checked'));
        });

        // Handle delete confirmation
        $('#confirmDeleteBtn').click(function() {
            // TODO: Implement document removal
            alert('Document removal feature coming soon!');
            $('#deleteModal').modal('hide');
        });

        // Track document view time
        let viewStartTime = new Date();
        
        // Send view duration when leaving page
        $(window).on('beforeunload', function() {
            let viewDuration = Math.round((new Date() - viewStartTime) / 1000);
            if (viewDuration > 5) { // Only track if viewed for more than 5 seconds
                $.ajax({
                    url: '{{ route("portal.documents.track-view", $document) }}',
                    method: 'POST',
                    data: {
                        duration: viewDuration,
                        _token: '{{ csrf_token() }}'
                    },
                    async: false // Ensure request completes before page unloads
                });
            }
        });
    });
</script>
@endsection

@section('styles')
<style>
    .timeline {
        position: relative;
        padding-left: 30px;
    }
    .timeline-item {
        position: relative;
        margin-bottom: 20px;
    }
    .timeline-icon {
        position: absolute;
        left: -40px;
        top: 0;
        width: 30px;
        height: 30px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
    }
    .timeline-content {
        padding-bottom: 10px;
        border-bottom: 1px solid #eee;
    }
    .timeline-item:last-child .timeline-content {
        border-bottom: none;
    }
</style>
@endsection