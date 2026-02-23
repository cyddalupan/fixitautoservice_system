@extends('layouts.app')

@section('title', 'My Documents - Customer Portal')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-file-alt me-2"></i>My Documents
                    </h3>
                    <div class="card-tools">
                        <span class="badge bg-primary">{{ $documents->total() }} documents</span>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Advanced Filters -->
                    <div class="collapse mb-4" id="documentFilters">
                        <div class="card card-body bg-light">
                            <form method="GET" action="{{ route('portal.documents.index') }}" class="row g-3">
                                <div class="col-md-3">
                                    <label for="document_type" class="form-label">Document Type</label>
                                    <select class="form-select" id="document_type" name="type">
                                        <option value="">All Types</option>
                                        <option value="invoice" {{ request('type') === 'invoice' ? 'selected' : '' }}>Invoice</option>
                                        <option value="estimate" {{ request('type') === 'estimate' ? 'selected' : '' }}>Estimate</option>
                                        <option value="inspection" {{ request('type') === 'inspection' ? 'selected' : '' }}>Inspection Report</option>
                                        <option value="report" {{ request('type') === 'report' ? 'selected' : '' }}>Service Report</option>
                                        <option value="other" {{ request('type') === 'other' ? 'selected' : '' }}>Other</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="document_status" class="form-label">Status</label>
                                    <select class="form-select" id="document_status" name="status">
                                        <option value="">All Statuses</option>
                                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending Review</option>
                                        <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                                        <option value="signed" {{ request('status') === 'signed' ? 'selected' : '' }}>Signed</option>
                                        <option value="archived" {{ request('status') === 'archived' ? 'selected' : '' }}>Archived</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="date_from" class="form-label">From Date</label>
                                    <input type="date" class="form-control" id="date_from" name="date_from" 
                                           value="{{ request('date_from') }}">
                                </div>
                                <div class="col-md-3">
                                    <label for="date_to" class="form-label">To Date</label>
                                    <input type="date" class="form-control" id="date_to" name="date_to" 
                                           value="{{ request('date_to') }}">
                                </div>
                                <div class="col-12">
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-primary btn-sm">
                                            <i class="fas fa-filter me-1"></i> Apply Filters
                                        </button>
                                        <a href="{{ route('portal.documents.index') }}" class="btn btn-outline-secondary btn-sm">
                                            <i class="fas fa-times me-1"></i> Clear Filters
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    @if($documents->isEmpty())
                        <div class="text-center py-5">
                            <i class="fas fa-file-alt fa-4x text-muted mb-3"></i>
                            <h4>No documents available</h4>
                            <p class="text-muted">Your documents will appear here once shared by the shop.</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Document</th>
                                        <th>Type</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($documents as $document)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="me-3">
                                                        @if($document->document_type === 'invoice')
                                                            <i class="fas fa-file-invoice text-primary fa-2x"></i>
                                                        @elseif($document->document_type === 'estimate')
                                                            <i class="fas fa-file-estimate text-success fa-2x"></i>
                                                        @elseif($document->document_type === 'inspection')
                                                            <i class="fas fa-clipboard-check text-info fa-2x"></i>
                                                        @elseif($document->document_type === 'report')
                                                            <i class="fas fa-chart-bar text-warning fa-2x"></i>
                                                        @else
                                                            <i class="fas fa-file text-secondary fa-2x"></i>
                                                        @endif
                                                    </div>
                                                    <div>
                                                        <strong>{{ $document->title }}</strong>
                                                        <div class="text-muted small">
                                                            @if($document->vehicle)
                                                                {{ $document->vehicle->make }} {{ $document->vehicle->model }}
                                                            @endif
                                                            @if($document->workOrder)
                                                                • Work Order #{{ $document->workOrder->id }}
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $document->document_type === 'invoice' ? 'primary' : ($document->document_type === 'estimate' ? 'success' : ($document->document_type === 'inspection' ? 'info' : 'secondary')) }}">
                                                    {{ ucfirst($document->document_type) }}
                                                </span>
                                            </td>
                                            <td>
                                                <div>{{ $document->created_at->format('M d, Y') }}</div>
                                                <div class="text-muted small">{{ $document->created_at->format('g:i A') }}</div>
                                            </td>
                                            <td>
                                                @if($document->viewed_at)
                                                    <span class="badge bg-success">
                                                        <i class="fas fa-eye me-1"></i>Viewed
                                                    </span>
                                                    <div class="text-muted small">
                                                        {{ $document->viewed_at->format('M d') }}
                                                    </div>
                                                @else
                                                    <span class="badge bg-warning">
                                                        <i class="fas fa-clock me-1"></i>Unread
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="{{ route('portal.documents.show', $document) }}" 
                                                       class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-eye"></i> View
                                                    </a>
                                                    <a href="{{ route('portal.documents.download', $document) }}" 
                                                       class="btn btn-sm btn-outline-success">
                                                        <i class="fas fa-download"></i> Download
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div class="text-muted">
                                Showing {{ $documents->firstItem() }} to {{ $documents->lastItem() }} of {{ $documents->total() }} documents
                            </div>
                            <div>
                                {{ $documents->links() }}
                            </div>
                        </div>
                    @endif
                </div>
                <div class="card-footer">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="small text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                Documents are typically available for 90 days after sharing.
                            </div>
                        </div>
                        <div class="col-md-6 text-end">
                            <div class="btn-group">
                                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#documentFilters">
                                    <i class="fas fa-filter me-1"></i> Filters
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary">
                                    <i class="fas fa-sort"></i> Sort
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Auto-refresh document status
        setInterval(function() {
            $.ajax({
                url: '{{ route("portal.documents.status") }}',
                method: 'GET',
                success: function(data) {
                    // Update unread count if needed
                    if (data.unread_count > 0) {
                        $('#unread-documents-badge').text(data.unread_count).show();
                    } else {
                        $('#unread-documents-badge').hide();
                    }
                }
            });
        }, 30000); // Every 30 seconds
    });
</script>
@endsection