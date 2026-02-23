@extends('layouts.app')

@section('title', 'Quality Check Details')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col">
                <h1 class="h3 mb-0">
                    <i class="fas fa-clipboard-check text-primary me-2"></i>
                    Quality Check Details
                </h1>
                <p class="text-muted mb-0">Quality check for work order WO-{{ str_pad($qualityCheck->work_order_id, 6, '0', STR_PAD_LEFT) }}</p>
            </div>
            <div class="col-auto">
                <a href="{{ route('work-order-quality.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back to List
                </a>
                @if($qualityCheck->status === 'pending' && auth()->user()->can('approve', $qualityCheck))
                    <div class="btn-group">
                        <a href="{{ route('work-order-quality.approve', $qualityCheck->id) }}" 
                           class="btn btn-success" onclick="return confirm('Approve this quality check?')">
                            <i class="fas fa-check me-1"></i> Approve
                        </a>
                        <a href="{{ route('work-order-quality.reject', $qualityCheck->id) }}" 
                           class="btn btn-danger" onclick="return confirm('Reject this quality check?')">
                            <i class="fas fa-times me-1"></i> Reject
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Left Column - Details -->
        <div class="col-lg-8">
            <!-- Status Card -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h5 class="card-title mb-1">Quality Check Status</h5>
                            <p class="text-muted mb-0">Created on {{ $qualityCheck->created_at->format('F d, Y \a\t h:i A') }}</p>
                        </div>
                        <div class="col-md-4 text-end">
                            @if($qualityCheck->status === 'approved')
                                <span class="badge bg-success fs-6 px-3 py-2">
                                    <i class="fas fa-check-circle me-1"></i> Approved
                                </span>
                            @elseif($qualityCheck->status === 'rejected')
                                <span class="badge bg-danger fs-6 px-3 py-2">
                                    <i class="fas fa-times-circle me-1"></i> Rejected
                                </span>
                            @else
                                <span class="badge bg-warning fs-6 px-3 py-2">
                                    <i class="fas fa-clock me-1"></i> Pending Approval
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Work Order Information -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="fas fa-wrench me-2"></i>
                        Work Order Information
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-2">
                                <strong>Work Order:</strong>
                                <span class="float-end">WO-{{ str_pad($qualityCheck->work_order_id, 6, '0', STR_PAD_LEFT) }}</span>
                            </p>
                            <p class="mb-2">
                                <strong>Customer:</strong>
                                <span class="float-end">{{ $qualityCheck->workOrder->customer->name ?? 'Unknown' }}</span>
                            </p>
                            <p class="mb-2">
                                <strong>Vehicle:</strong>
                                <span class="float-end">
                                    {{ $qualityCheck->workOrder->vehicle->make ?? 'Unknown' }} 
                                    {{ $qualityCheck->workOrder->vehicle->model ?? '' }}
                                </span>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-2">
                                <strong>Technician:</strong>
                                <span class="float-end">{{ $qualityCheck->technician->name ?? 'Unknown' }}</span>
                            </p>
                            <p class="mb-2">
                                <strong>Service Type:</strong>
                                <span class="float-end">{{ $qualityCheck->workOrder->service_type ?? 'N/A' }}</span>
                            </p>
                            <p class="mb-2">
                                <strong>Service Date:</strong>
                                <span class="float-end">
                                    {{ $qualityCheck->workOrder->created_at->format('M d, Y') ?? 'N/A' }}
                                </span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quality Score -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="fas fa-chart-line me-2"></i>
                        Quality Score
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-4 text-center">
                            <div class="display-1 fw-bold text-primary">
                                {{ $qualityCheck->calculateScore() }}%
                            </div>
                            <div class="text-muted">Overall Score</div>
                        </div>
                        <div class="col-md-8">
                            <div class="progress mb-3" style="height: 30px;">
                                <div class="progress-bar bg-success" role="progressbar" 
                                     style="width: {{ $qualityCheck->calculateScore() }}%">
                                    {{ $qualityCheck->calculateScore() }}%
                                </div>
                            </div>
                            <div class="row text-center">
                                <div class="col">
                                    <div class="h4 mb-1">{{ $qualityCheck->passed_items_count ?? 0 }}</div>
                                    <div class="text-muted small">Passed Items</div>
                                </div>
                                <div class="col">
                                    <div class="h4 mb-1">{{ $qualityCheck->failed_items_count ?? 0 }}</div>
                                    <div class="text-muted small">Failed Items</div>
                                </div>
                                <div class="col">
                                    <div class="h4 mb-1">{{ $qualityCheck->total_items_count ?? 0 }}</div>
                                    <div class="text-muted small">Total Items</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Checklist Items -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="fas fa-tasks me-2"></i>
                        Checklist Items
                    </h6>
                </div>
                <div class="card-body">
                    @if($qualityCheck->checklist_results && count($qualityCheck->checklist_results) > 0)
                        @foreach($qualityCheck->checklist_results as $index => $result)
                            <div class="card mb-3 {{ $result['passed'] ? 'border-success' : 'border-danger' }}">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col-md-8">
                                            <h6 class="mb-1">
                                                {{ $result['item_name'] ?? 'Item ' . ($index + 1) }}
                                                @if($result['required'] ?? false)
                                                    <span class="text-danger">*</span>
                                                @endif
                                            </h6>
                                            @if($result['description'] ?? false)
                                                <p class="text-muted small mb-2">{{ $result['description'] }}</p>
                                            @endif
                                        </div>
                                        <div class="col-md-4">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    @if($result['rating'] ?? false)
                                                        <div class="rating-display">
                                                            @for($i = 1; $i <= 5; $i++)
                                                                @if($i <= $result['rating'])
                                                                    <i class="fas fa-star text-warning"></i>
                                                                @else
                                                                    <i class="far fa-star text-muted"></i>
                                                                @endif
                                                            @endfor
                                                            <span class="ms-2 fw-bold">{{ $result['rating'] }}/5</span>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div>
                                                    @if($result['passed'] ?? false)
                                                        <span class="badge bg-success">
                                                            <i class="fas fa-check me-1"></i> Pass
                                                        </span>
                                                    @else
                                                        <span class="badge bg-danger">
                                                            <i class="fas fa-times me-1"></i> Fail
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No checklist items recorded</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Photos -->
            @if($qualityCheck->photos && count($qualityCheck->photos) > 0)
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">
                            <i class="fas fa-camera me-2"></i>
                            Photo Documentation
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            @foreach($qualityCheck->photos as $photo)
                                <div class="col-6 col-md-4 col-lg-3">
                                    <div class="card">
                                        <a href="{{ asset('storage/' . $photo) }}" data-lightbox="quality-photos">
                                            <img src="{{ asset('storage/' . $photo) }}" 
                                                 class="card-img-top" 
                                                 alt="Quality Check Photo"
                                                 style="height: 150px; object-fit: cover;">
                                        </a>
                                        <div class="card-body p-2 text-center">
                                            <small class="text-muted">Photo {{ $loop->iteration }}</small>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <!-- Notes -->
            @if($qualityCheck->notes)
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">
                            <i class="fas fa-sticky-note me-2"></i>
                            Additional Notes
                        </h6>
                    </div>
                    <div class="card-body">
                        <p class="mb-0">{{ $qualityCheck->notes }}</p>
                    </div>
                </div>
            @endif
        </div>

        <!-- Right Column - Sidebar -->
        <div class="col-lg-4">
            <!-- Template Information -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="fas fa-clipboard-list me-2"></i>
                        Template Information
                    </h6>
                </div>
                <div class="card-body">
                    <p class="mb-2">
                        <strong>Template:</strong>
                        <span class="float-end">{{ $qualityCheck->qualityCheck->name ?? 'N/A' }}</span>
                    </p>
                    <p class="mb-2">
                        <strong>Category:</strong>
                        <span class="float-end">{{ ucfirst($qualityCheck->qualityCheck->category ?? 'N/A') }}</span>
                    </p>
                    <p class="mb-2">
                        <strong>Items Count:</strong>
                        <span class="float-end">{{ count($qualityCheck->checklist_results ?? []) }}</span>
                    </p>
                    <p class="mb-0">
                        <strong>Pass Threshold:</strong>
                        <span class="float-end">{{ $qualityCheck->qualityCheck->pass_threshold ?? 90 }}%</span>
                    </p>
                </div>
            </div>

            <!-- Approval Information -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="fas fa-user-check me-2"></i>
                        Approval Information
                    </h6>
                </div>
                <div class="card-body">
                    @if($qualityCheck->status === 'approved' || $qualityCheck->status === 'rejected')
                        <p class="mb-2">
                            <strong>Supervisor:</strong>
                            <span class="float-end">{{ $qualityCheck->approver->name ?? 'Unknown' }}</span>
                        </p>
                        <p class="mb-2">
                            <strong>Action:</strong>
                            <span class="float-end">
                                @if($qualityCheck->status === 'approved')
                                    <span class="badge bg-success">Approved</span>
                                @else
                                    <span class="badge bg-danger">Rejected</span>
                                @endif
                            </span>
                        </p>
                        <p class="mb-2">
                            <strong>Date:</strong>
                            <span class="float-end">
                                {{ $qualityCheck->approved_at ? $qualityCheck->approved_at->format('M d, Y h:i A') : 'N/A' }}
                            </span>
                        </p>
                        @if($qualityCheck->approval_notes)
                            <p class="mb-0">
                                <strong>Notes:</strong><br>
                                <small class="text-muted">{{ $qualityCheck->approval_notes }}</small>
                            </p>
                        @endif
                    @else
                        <div class="text-center py-3">
                            <i class="fas fa-clock fa-2x text-warning mb-3"></i>
                            <p class="text-muted mb-0">Awaiting supervisor approval</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Actions -->
            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="fas fa-cog me-2"></i>
                        Actions
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @if($qualityCheck->status === 'pending' && auth()->id() == $qualityCheck->technician_id)
                            <a href="{{ route('work-order-quality.edit', $qualityCheck->id) }}" 
                               class="btn btn-outline-primary">
                                <i class="fas fa-edit me-1"></i> Edit Quality Check
                            </a>
                        @endif
                        
                        <a href="{{ route('work-order-quality.duplicate', $qualityCheck->id) }}" 
                           class="btn btn-outline-info">
                            <i class="fas fa-copy me-1"></i> Duplicate for Another WO
                        </a>
                        
                        @if(auth()->user()->can('delete', $qualityCheck))
                            <form action="{{ route('work-order-quality.destroy', $qualityCheck->id) }}" 
                                  method="POST" class="d-grid">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger" 
                                        onclick="return confirm('Are you sure you want to delete this quality check?')">
                                    <i class="fas fa-trash me-1"></i> Delete Quality Check
                                </button>
                            </form>
                        @endif
                        
                        <a href="{{ route('work-order-quality.export-pdf', $qualityCheck->id) }}" 
                           class="btn btn-outline-secondary">
                            <i class="fas fa-file-pdf me-1"></i> Export as PDF
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/css/lightbox.min.css">
@endpush

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/js/lightbox.min.js"></script>
<script>
    lightbox.option({
        'resizeDuration': 200,
        'wrapAround': true,
        'albumLabel': 'Photo %1 of %2'
    });
</script>
@endpush
@endsection