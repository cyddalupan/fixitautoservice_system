@php
    $moduleLabels = [
        'work_order' => 'Repair Order',
        'estimate' => 'Estimate',
        'payment' => 'Payment',
        'invoice' => 'Invoice',
        'inspection' => 'Inspection',
        'customer' => 'Customer',
    ];
@endphp

@extends('layouts.app')

@section('title', 'Archive - Deleted Records')

@section('head')
<style>
    .archive-badge { font-size: 0.75rem; }
    .batch-card { border-left: 4px solid #e74a3b; }
    .search-highlight { background-color: #fff3cd; }
    .filter-section { background: #f8f9fc; border-radius: 0.35rem; }
    .restore-card { border-left: 4px solid #1cc88a; }
    .json-data { max-height: 200px; overflow-y: auto; font-size: 0.75rem; }
    .record-count-badge { position: absolute; top: -8px; right: -8px; font-size: 0.6rem; }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-archive me-2"></i>Archive
            <small class="text-muted fs-6">Recover deleted records</small>
        </h1>
        <div>
            <span class="badge bg-secondary me-2">{{ $archives->total() }} total archived</span>
            @foreach($moduleCounts as $mod => $cnt)
                <span class="badge bg-info archive-badge me-1">{{ $moduleLabels[$mod] ?? ucfirst($mod) }}: {{ $cnt }}</span>
            @endforeach
        </div>
    </div>

    <!-- Batch Delete Section -->
    @if(in_array(Auth::user()->role, ['super_admin', 'admin']))
    <div class="card shadow mb-4 batch-card">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-danger">
                <i class="fas fa-trash-alt me-2"></i>Batch Delete Tools
            </h6>
            <span class="badge bg-danger">Admin Only</span>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('archives.batch-by-month') }}" class="row g-2 align-items-end mb-3" id="batchMonthForm">
                @csrf
                <div class="col-auto">
                    <label class="form-label small">Month</label>
                    <select name="month" class="form-select form-select-sm" required>
                        @foreach(range(1,12) as $m)
                            <option value="{{ $m }}">{{ date('F', mktime(0,0,0,$m,1)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto">
                    <label class="form-label small">Year</label>
                    <select name="year" class="form-select form-select-sm" required>
                        @for($y = date('Y'); $y >= 2020; $y--)
                            <option value="{{ $y }}">{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-auto">
                    <button type="button" class="btn btn-danger btn-sm" onclick="confirmBatch('Delete all records for selected month/year?','batchMonthForm')">
                        <i class="fas fa-calendar-minus me-1"></i> Delete by Month
                    </button>
                </div>
            </form>

            <form method="POST" action="{{ route('archives.batch-by-year-range') }}" class="row g-2 align-items-end mb-3" id="batchYearForm">
                @csrf
                <div class="col-auto">
                    <label class="form-label small">From Year</label>
                    <select name="year_from" class="form-select form-select-sm" required>
                        @for($y = 2020; $y <= date('Y'); $y++)
                            <option value="{{ $y }}">{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-auto">
                    <label class="form-label small">To Year</label>
                    <select name="year_to" class="form-select form-select-sm" required>
                        @for($y = 2020; $y <= date('Y'); $y++)
                            <option value="{{ $y }}" {{ $y == date('Y') ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-auto">
                    <button type="button" class="btn btn-danger btn-sm" onclick="confirmBatch('Permanently delete all records from selected year range? This cannot be undone.','batchYearForm')">
                        <i class="fas fa-calendar-range me-1"></i> Delete by Year Range
                    </button>
                </div>
            </form>

            <form method="POST" action="{{ route('archives.batch-by-date-range') }}" class="row g-2 align-items-end" id="batchDateForm">
                @csrf
                <div class="col-auto">
                    <label class="form-label small">From</label>
                    <input type="date" name="date_from" class="form-control form-control-sm" required>
                </div>
                <div class="col-auto">
                    <label class="form-label small">To</label>
                    <input type="date" name="date_to" class="form-control form-control-sm" required>
                </div>
                <div class="col-auto">
                    <button type="button" class="btn btn-danger btn-sm" onclick="confirmBatch('Permanently delete all records in this date range? This cannot be undone.','batchDateForm')">
                        <i class="fas fa-calendar-day me-1"></i> Delete by Date Range
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- Search & Filter -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-search me-2"></i>Search & Filter
            </h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('archives.index') }}" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label small">Search</label>
                    <input type="text" name="search" class="form-control" placeholder="Customer name, plate #, invoice #, module..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Module</label>
                    <select name="module" class="form-select">
                        <option value="">All Modules</option>
                        @foreach(['work_order', 'estimate', 'payment', 'invoice', 'inspection', 'customer'] as $mod)
                            <option value="{{ $mod }}" {{ request('module') == $mod ? 'selected' : '' }}>
                                {{ $moduleLabels[$mod] ?? ucfirst($mod) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Date From</label>
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Date To</label>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-2 d-flex align-items-end gap-1">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter me-1"></i> Filter
                    </button>
                    <a href="{{ route('archives.index') }}" class="btn btn-secondary">
                        <i class="fas fa-undo"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Archives Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-list me-2"></i>Archived Records
            </h6>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-success btn-sm" id="batchRestoreBtn" disabled onclick="confirmBatch('Restore selected records?','batchRestoreForm')">
                    <i class="fas fa-undo-alt me-1"></i> Restore Selected
                </button>
                @if(in_array(Auth::user()->role, ['super_admin', 'admin']))
                <button type="button" class="btn btn-danger btn-sm" id="batchDeleteBtn" disabled onclick="confirmBatch('Permanently delete selected records? This cannot be undone.','batchDeleteForm')">
                    <i class="fas fa-trash me-1"></i> Delete Selected
                </button>
                @endif
            </div>
        </div>
        <div class="card-body">
            @if($archives->count() === 0)
                <div class="text-center py-5">
                    <i class="fas fa-archive fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No archived records found.</p>
                </div>
            @else
            <form id="batchRestoreForm" method="POST" action="{{ route('archives.batch-restore') }}" class="d-none"></form>
            <form id="batchDeleteForm" method="POST" action="{{ route('archives.batch-delete') }}" class="d-none">
                @csrf
            </form>

            <div class="table-responsive">
                <table class="table table-hover table-sm align-middle">
                    <thead class="table-light">
                        <tr>
                            <th width="40">
                                <input type="checkbox" id="selectAll" onchange="toggleSelectAll()">
                            </th>
                            <th>ID</th>
                            <th>Source Module</th>
                            <th>Customer Info</th>
                            <th>Identifier</th>
                            <th>Archived By</th>
                            <th>Archived At</th>
                            <th>Restored</th>
                            <th width="160">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($archives as $archive)
                        @php
                            $data = $archive->original_data;
                            $customerName = $data['customer_name']
                                ?? ($data['customer_first_name'] ?? '') . ' ' . ($data['customer_last_name'] ?? '')
                                ?? ($data['first_name'] ?? '') . ' ' . ($data['last_name'] ?? '')
                                ?? 'N/A';
                            $identifier = $data['work_order_number']
                                ?? $data['estimate_number']
                                ?? $data['invoice_number']
                                ?? $data['reference_number']
                                ?? ($archive->source_module === 'customer' ? $customerName : '#' . $archive->archivable_id);
                            $plate = $data['plate_number'] ?? '';
                        @endphp
                        <tr>
                            <td>
                                <input type="checkbox" name="archive_ids[]" value="{{ $archive->id }}" class="select-item"
                                    onchange="updateBatchButtons()"
                                    form="{{ $archive->restored_at ? '' : 'batchRestoreForm' }}">
                            </td>
                            <td>{{ $archive->id }}</td>
                            <td>
                                <span class="badge bg-secondary">{{ $moduleLabels[$archive->source_module] ?? ucfirst($archive->source_module) }}</span>
                            </td>
                            <td>
                                <strong>{{ Str::limit($customerName, 30) }}</strong>
                                @if($plate)
                                    <br><small class="text-muted">{{ $plate }}</small>
                                @endif
                            </td>
                            <td><code>{{ Str::limit($identifier, 25) }}</code></td>
                            <td>{{ $archive->archivedBy->name ?? 'Unknown' }}</td>
                            <td>{{ $archive->archived_at->format('M d, Y h:i A') }}</td>
                            <td>
                                @if($archive->restored_at)
                                    <span class="badge bg-success">Yes</span>
                                    <small class="d-block text-muted">{{ $archive->restored_at->format('M d, Y') }}</small>
                                @else
                                    <span class="badge bg-warning text-dark">No</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('archives.show', $archive) }}" class="btn btn-info" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if(!$archive->restored_at)
                                    <form action="{{ route('archives.restore', $archive) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('Restore this record back to {{ $moduleLabels[$archive->source_module] ?? $archive->source_module }}?')">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-success" title="Restore">
                                            <i class="fas fa-undo-alt"></i>
                                        </button>
                                    </form>
                                    @endif
                                    @if(in_array(Auth::user()->role, ['super_admin', 'admin']))
                                    <form action="{{ route('archives.destroy', $archive) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('Permanently delete this archived record? This cannot be undone.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger" title="Permanently Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-3">
                <small class="text-muted">
                    Showing {{ $archives->firstItem() }}-{{ $archives->lastItem() }} of {{ $archives->total() }} records
                </small>
                {{ $archives->links() }}
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Confirmation Modal -->
<div class="modal fade" id="confirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i>Confirm Action</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="confirmMessage">
                Are you sure?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmProceedBtn">Proceed</button>
            </div>
        </div>
    </div>
</div>

<script>
let pendingFormId = null;

function confirmBatch(message, formId) {
    document.getElementById('confirmMessage').textContent = message;
    pendingFormId = formId;
    document.getElementById('confirmProceedBtn').onclick = function() {
        const form = document.getElementById(pendingFormId);
        if (form) {
            // Gather selected checkboxes if it's batchRestore or batchDelete
            if (pendingFormId === 'batchRestoreForm' || pendingFormId === 'batchDeleteForm') {
                const checkedBoxes = document.querySelectorAll('.select-item:checked');
                checkedBoxes.forEach(cb => {
                    const clone = document.createElement('input');
                    clone.type = 'hidden';
                    clone.name = 'archive_ids[]';
                    clone.value = cb.value;
                    form.appendChild(clone);
                });
            }
            form.submit();
        }
        bootstrap.Modal.getInstance(document.getElementById('confirmModal')).hide();
    };
    new bootstrap.Modal(document.getElementById('confirmModal')).show();
}

function toggleSelectAll() {
    const checked = document.getElementById('selectAll').checked;
    document.querySelectorAll('.select-item').forEach(cb => cb.checked = checked);
    updateBatchButtons();
}

function updateBatchButtons() {
    const count = document.querySelectorAll('.select-item:checked').length;
    const restoreBtn = document.getElementById('batchRestoreBtn');
    const deleteBtn = document.getElementById('batchDeleteBtn');
    restoreBtn.disabled = count === 0;
    if (deleteBtn) deleteBtn.disabled = count === 0;
    restoreBtn.innerHTML = `<i class="fas fa-undo-alt me-1"></i> Restore Selected (${count})`;
    if (deleteBtn) deleteBtn.innerHTML = `<i class="fas fa-trash me-1"></i> Delete Selected (${count})`;
}
</script>
@endsection
