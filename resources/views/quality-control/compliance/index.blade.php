@extends('layouts.app')

@section('title', 'Compliance Documents')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col">
                <h1 class="h3 mb-0">
                    <i class="fas fa-file-contract text-primary me-2"></i>
                    Compliance Documents
                </h1>
                <p class="text-muted mb-0">Manage regulatory compliance documents and certifications</p>
            </div>
            <div class="col-auto">
                <a href="{{ route('compliance.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i> New Document
                </a>
                <a href="{{ route('compliance.export') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-download me-1"></i> Export
                </a>
                <a href="{{ route('compliance.dashboard') }}" class="btn btn-outline-info">
                    <i class="fas fa-chart-bar me-1"></i> Dashboard
                </a>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card stat-card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-subtitle mb-2">Valid</h6>
                            <h2 class="card-title mb-0">{{ $validCount }}</h2>
                        </div>
                        <div class="card-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-subtitle mb-2">Expiring Soon</h6>
                            <h2 class="card-title mb-0">{{ $expiringSoonCount }}</h2>
                        </div>
                        <div class="card-icon">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card bg-danger text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-subtitle mb-2">Expired</h6>
                            <h2 class="card-title mb-0">{{ $expiredCount }}</h2>
                        </div>
                        <div class="card-icon">
                            <i class="fas fa-times-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-subtitle mb-2">Total Documents</h6>
                            <h2 class="card-title mb-0">{{ $documents->total() }}</h2>
                        </div>
                        <div class="card-icon">
                            <i class="fas fa-file-alt"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Critical Alerts -->
    @if($criticalAlerts->count() > 0)
        <div class="alert alert-danger mb-4">
            <div class="d-flex align-items-center">
                <i class="fas fa-exclamation-triangle fa-2x me-3"></i>
                <div class="flex-grow-1">
                    <h5 class="alert-heading mb-1">Critical Compliance Alerts</h5>
                    <p class="mb-0">
                        {{ $criticalAlerts->count() }} document(s) require immediate attention:
                        @foreach($criticalAlerts as $alert)
                            <span class="badge bg-dark ms-1">{{ $alert->document_name }}</span>
                        @endforeach
                    </p>
                </div>
                <a href="{{ route('compliance.alerts') }}" class="btn btn-outline-light">
                    View All Alerts
                </a>
            </div>
        </div>
    @endif

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('compliance.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label for="status" class="form-label">Status</label>
                    <select name="status" id="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="valid" {{ request('status') == 'valid' ? 'selected' : '' }}>Valid</option>
                        <option value="expiring_soon" {{ request('status') == 'expiring_soon' ? 'selected' : '' }}>Expiring Soon</option>
                        <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Expired</option>
                        <option value="pending_review" {{ request('status') == 'pending_review' ? 'selected' : '' }}>Pending Review</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="document_type" class="form-label">Document Type</label>
                    <select name="document_type" id="document_type" class="form-select">
                        <option value="">All Types</option>
                        @foreach(['license' => 'License', 'certification' => 'Certification', 'permit' => 'Permit', 'insurance' => 'Insurance', 'regulation' => 'Regulation', 'other' => 'Other'] as $value => $label)
                            <option value="{{ $value }}" {{ request('document_type') == $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="assigned_to" class="form-label">Assigned To</label>
                    <select name="assigned_to" id="assigned_to" class="form-select">
                        <option value="">All Users</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ request('assigned_to') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search me-1"></i> Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Documents Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th width="50">
                                <input type="checkbox" id="select-all">
                            </th>
                            <th>Document Name</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Expiration</th>
                            <th>Assigned To</th>
                            <th>Last Review</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($documents as $document)
                            <tr class="{{ $document->isExpired() ? 'table-danger' : ($document->isExpiringSoon() ? 'table-warning' : '') }}">
                                <td>
                                    <input type="checkbox" name="document_ids[]" value="{{ $document->id }}" class="document-select">
                                </td>
                                <td>
                                    <strong>{{ $document->document_name }}</strong>
                                    @if($document->description)
                                        <p class="text-muted mb-0 small">{{ Str::limit($document->description, 50) }}</p>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-secondary">{{ ucfirst($document->document_type) }}</span>
                                </td>
                                <td>
                                    @if($document->isExpired())
                                        <span class="badge bg-danger">
                                            <i class="fas fa-times-circle me-1"></i> Expired
                                        </span>
                                    @elseif($document->isExpiringSoon())
                                        <span class="badge bg-warning">
                                            <i class="fas fa-exclamation-triangle me-1"></i> Expiring Soon
                                        </span>
                                    @else
                                        <span class="badge bg-success">
                                            <i class="fas fa-check-circle me-1"></i> Valid
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if($document->expiration_date)
                                        <div class="d-flex flex-column">
                                            <span>{{ $document->expiration_date->format('M d, Y') }}</span>
                                            <small class="text-muted">
                                                @if($document->isExpired())
                                                    Expired {{ $document->expiration_date->diffForHumans() }}
                                                @else
                                                    {{ $document->daysUntilExpiry() }} days remaining
                                                @endif
                                            </small>
                                        </div>
                                    @else
                                        <span class="text-muted">No expiration</span>
                                    @endif
                                </td>
                                <td>
                                    {{ $document->assignedUser->name ?? 'Unassigned' }}
                                </td>
                                <td>
                                    @if($document->last_reviewed_at)
                                        {{ $document->last_reviewed_at->format('M d, Y') }}
                                        <br>
                                        <small class="text-muted">{{ $document->last_reviewed_at->format('h:i A') }}</small>
                                    @else
                                        <span class="text-muted">Never reviewed</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('compliance.show', $document->id) }}" 
                                           class="btn btn-sm btn-outline-primary" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('compliance.edit', $document->id) }}" 
                                           class="btn btn-sm btn-outline-secondary" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @if($document->file_path)
                                            <a href="{{ route('compliance.download', $document->id) }}" 
                                               class="btn btn-sm btn-outline-info" title="Download">
                                                <i class="fas fa-download"></i>
                                            </a>
                                        @endif
                                        @if(!$document->isExpired())
                                            <a href="{{ route('compliance.renew', $document->id) }}" 
                                               class="btn btn-sm btn-outline-warning" title="Renew"
                                               onclick="return confirm('Renew this document?')">
                                                <i class="fas fa-redo"></i>
                                            </a>
                                        @endif
                                        <form action="{{ route('compliance.destroy', $document->id) }}" 
                                              method="POST" class="d-inline" 
                                              onsubmit="return confirm('Are you sure you want to delete this document?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="fas fa-file-contract fa-3x mb-3"></i>
                                        <h5>No compliance documents found</h5>
                                        <p>Create your first compliance document to get started.</p>
                                        <a href="{{ route('compliance.create') }}" class="btn btn-primary">
                                            <i class="fas fa-plus me-1"></i> Create Document
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Bulk Actions -->
            @if($documents->count() > 0)
                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="btn-group">
                            <button type="button" class="btn btn-outline-secondary dropdown-toggle" 
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-cog me-1"></i> Bulk Actions
                            </button>
                            <ul class="dropdown-menu">
                                <li>
                                    <form action="{{ route('compliance.bulk-update') }}" method="POST" id="bulk-verify-form">
                                        @csrf
                                        <input type="hidden" name="document_ids" id="bulk-document-ids-verify">
                                        <input type="hidden" name="action" value="verify">
                                        <button type="submit" class="dropdown-item" onclick="submitBulkForm('verify')">
                                            <i class="fas fa-check-circle text-success me-2"></i> Mark as Verified
                                        </button>
                                    </form>
                                </li>
                                <li>
                                    <form action="{{ route('compliance.bulk-update') }}" method="POST" id="bulk-renew-form">
                                        @csrf
                                        <input type="hidden" name="document_ids" id="bulk-document-ids-renew">
                                        <input type="hidden" name="action" value="renew">
                                        <button type="submit" class="dropdown-item" onclick="submitBulkForm('renew')">
                                            <i class="fas fa-redo text-warning me-2"></i> Renew Selected
                                        </button>
                                    </form>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form action="{{ route('compliance.bulk-update') }}" method="POST" id="bulk-delete-form">
                                        @csrf
                                        <input type="hidden" name="document_ids" id="bulk-document-ids-delete">
                                        <input type="hidden" name="action" value="delete">
                                        <button type="submit" class="dropdown-item text-danger" 
                                                onclick="return confirm('Are you sure you want to delete selected documents?') && submitBulkForm('delete')">
                                            <i class="fas fa-trash me-2"></i> Delete Selected
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <!-- Pagination -->
                        {{ $documents->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Select all checkboxes
    document.getElementById('select-all').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.document-select');
        checkboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
    });

    // Submit bulk form
    function submitBulkForm(action) {
        const checkboxes = document.querySelectorAll('.document-select:checked');
        const documentIds = Array.from(checkboxes).map(cb => cb.value);
        
        if (documentIds.length === 0) {
            alert('Please select at least one document.');
            return false;
        }
        
        let formId;
        switch(action) {
            case 'verify':
                formId = 'bulk-verify-form';
                break;
            case 'renew':
                formId = 'bulk-renew-form';
                break;
            case 'delete':
                formId = 'bulk-delete-form';
                break;
        }
        
        document.getElementById('bulk-document-ids-verify').value = JSON.stringify(documentIds);
        document.getElementById('bulk-document-ids-renew').value = JSON.stringify(documentIds);
        document.getElementById('bulk-document-ids-delete').value = JSON.stringify(documentIds);
        
        return true;
    }

    // Auto-refresh for critical alerts
    @if($criticalAlerts->count() > 0)
        setTimeout(function() {
            location.reload();
        }, 300000); // Refresh every 5 minutes if there are critical alerts
    @endif
</script>
@endpush
@endsection