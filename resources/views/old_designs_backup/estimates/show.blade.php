@extends('layouts.app')

@section('title', 'View Estimate - ' . $estimate->estimate_number)

@section('content')
<div class="container-fluid">
    <!-- Service Progress Bar -->
    @if($estimate->safeServiceProgress)
        <div class="row mb-4">
            <div class="col-12">
                @include('components.service-progress-bar', [
    'progress' => $estimate->safeServiceProgress,
    'currentStage' => 'estimate'
])
            </div>
        </div>
    @endif

    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Estimate #{{ $estimate->estimate_number }}</h2>
                <div>
                    <a href="{{ route('estimates.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                    @if($estimate->status !== 'accepted' && $estimate->status !== 'rejected')
                    <a href="{{ route('estimates.edit', $estimate->id) }}" class="btn btn-primary">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                    @endif
                    <!-- View Repair Order Button -->
                    <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#repairOrderModal">
                        <i class="fas fa-eye"></i> View Repair Order
                    </button>
                    <button type="button" class="btn btn-success" onclick="window.print()">
                        <i class="fas fa-print"></i> Print
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Customer & Vehicle Info -->
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-user"></i> Customer Information</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th>Name:</th>
                            <td>{{ $estimate->customer->first_name }} {{ $estimate->customer->last_name }}</td>
                        </tr>
                        <tr>
                            <th>Email:</th>
                            <td>{{ $estimate->customer->email }}</td>
                        </tr>
                        <tr>
                            <th>Phone:</th>
                            <td>{{ $estimate->customer->phone }}</td>
                        </tr>
                        <tr>
                            <th>Address:</th>
                            <td>{{ $estimate->customer->address ?? 'N/A' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-car"></i> Vehicle Information</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th>Make:</th>
                            <td>{{ $estimate->vehicle->make }}</td>
                        </tr>
                        <tr>
                            <th>Model:</th>
                            <td>{{ $estimate->vehicle->model }}</td>
                        </tr>
                        <tr>
                            <th>Year:</th>
                            <td>{{ $estimate->vehicle->year }}</td>
                        </tr>
                        <tr>
                            <th>VIN:</th>
                            <td>{{ $estimate->vehicle->vin ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>License Plate:</th>
                            <td>{{ $estimate->vehicle->license_plate }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0"><i class="fas fa-list"></i> Estimate Details</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <strong>Estimate Date:</strong> {{ \Carbon\Carbon::parse($estimate->estimate_date)->format('M d, Y') }}
                        </div>
                        <div class="col-md-3">
                            <strong>Valid Until:</strong> {{ \Carbon\Carbon::parse($estimate->valid_until)->format('M d, Y') }}
                        </div>
                        <div class="col-md-3">
                            <strong>Status:</strong> 
                            @php
                                $statusClass = match($estimate->status) {
                                    'draft' => 'secondary',
                                    'pending' => 'info',
                                    'viewed' => 'warning',
                                    'accepted' => 'success',
                                    'rejected' => 'danger',
                                    default => 'secondary'
                                };
                            @endphp
                            <span class="badge bg-{{ $statusClass }}">{{ ucfirst($estimate->status) }}</span>
                        </div>
                        <div class="col-md-3">
                            <strong>Technician:</strong> {{ $estimate->technician->name ?? 'Not assigned' }}
                        </div>
                    </div>

                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Item / Service</th>
                                <th>Category</th>
                                <th class="text-center">Qty</th>
                                <th class="text-end">Unit Price</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($estimate->items as $index => $item)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    {{ $item->item_name ?? $item->inventory->name ?? 'N/A' }}
                                    @if($item->description)
                                    <br><small class="text-muted">{{ $item->description }}</small>
                                    @endif
                                </td>
                                <td>{{ $item->inventory->category->name ?? 'Service' }}</td>
                                <td class="text-center">{{ $item->quantity }}</td>
                                <td class="text-end">₱{{ number_format($item->unit_price, 2) }}</td>
                                <td class="text-end">₱{{ number_format($item->total_price, 2) }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center">No items found</td>
                            </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="5" class="text-end">Subtotal:</th>
                                <th class="text-end">₱{{ number_format($estimate->subtotal, 2) }}</th>
                            </tr>
                            @if($estimate->tax_rate > 0)
                            <tr>
                                <th colspan="5" class="text-end">Tax ({{ $estimate->tax_rate }}%):</th>
                                <th class="text-end">₱{{ number_format($estimate->tax_amount, 2) }}</th>
                            </tr>
                            @endif
                            @if($estimate->discount_amount > 0)
                            <tr>
                                <th colspan="5" class="text-end">Discount:</th>
                                <th class="text-end">-₱{{ number_format($estimate->discount_amount, 2) }}</th>
                            </tr>
                            @endif
                            <tr class="table-primary">
                                <th colspan="5" class="text-end">Total:</th>
                                <th class="text-end">₱{{ number_format($estimate->total_amount, 2) }}</th>
                            </tr>
                        </tfoot>
                    </table>

                    @if($estimate->notes)
                    <div class="row mt-3">
                        <div class="col-12">
                            <strong>Notes:</strong>
                            <p>{{ $estimate->notes }}</p>
                        </div>
                    </div>
                    @endif

                    @if($estimate->terms_conditions)
                    <div class="row mt-3">
                        <div class="col-12">
                            <strong>Terms & Conditions:</strong>
                            <p>{{ $estimate->terms_conditions }}</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if($estimate->status === 'pending' || $estimate->status === 'viewed')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">Actions</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('estimates.update-status', $estimate->id) }}" class="d-inline">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="accepted">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-check"></i> Accept Estimate
                        </button>
                    </form>
                    <form method="POST" action="{{ route('estimates.update-status', $estimate->id) }}" class="d-inline">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="rejected">
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-times"></i> Reject Estimate
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

<!-- Repair Order Modal -->
<div class="modal fade" id="repairOrderModal" tabindex="-1" aria-labelledby="repairOrderModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="repairOrderModalLabel">
                    <i class="fas fa-wrench me-2"></i>Repair Order Reference
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Customer & Vehicle Information -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="fas fa-user text-primary me-2"></i>Customer Information</h6>
                            </div>
                            <div class="card-body">
                                <table class="table table-sm table-borderless">
                                    <tr>
                                        <th width="40%">Name:</th>
                                        <td>{{ $estimate->customer->first_name }} {{ $estimate->customer->last_name }}</td>
                                    </tr>
                                    <tr>
                                        <th>Email:</th>
                                        <td>{{ $estimate->customer->email }}</td>
                                    </tr>
                                    <tr>
                                        <th>Phone:</th>
                                        <td>{{ $estimate->customer->phone }}</td>
                                    </tr>
                                    <tr>
                                        <th>Address:</th>
                                        <td>{{ $estimate->customer->address ?? 'N/A' }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="fas fa-car text-info me-2"></i>Vehicle Information</h6>
                            </div>
                            <div class="card-body">
                                <table class="table table-sm table-borderless">
                                    <tr>
                                        <th width="40%">Make:</th>
                                        <td>{{ $estimate->vehicle->make }}</td>
                                    </tr>
                                    <tr>
                                        <th>Model:</th>
                                        <td>{{ $estimate->vehicle->model }}</td>
                                    </tr>
                                    <tr>
                                        <th>Year:</th>
                                        <td>{{ $estimate->vehicle->year }}</td>
                                    </tr>
                                    <tr>
                                        <th>VIN:</th>
                                        <td>{{ $estimate->vehicle->vin ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>License Plate:</th>
                                        <td>{{ $estimate->vehicle->license_plate }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Notes & Terms (Keep only if has data) -->
                @if($estimate->notes || $estimate->terms_conditions)
                <div class="row mb-4">
                    @if($estimate->notes)
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="fas fa-sticky-note text-secondary me-2"></i>Notes</h6>
                            </div>
                            <div class="card-body">
                                <p class="mb-0">{{ $estimate->notes }}</p>
                            </div>
                        </div>
                    </div>
                    @endif
                    @if($estimate->terms_conditions)
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="fas fa-file-contract text-secondary me-2"></i>Terms & Conditions</h6>
                            </div>
                            <div class="card-body">
                                <p class="mb-0">{{ $estimate->terms_conditions }}</p>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
                @endif

                <!-- Debug: Check inspection data (visible for troubleshooting) -->
                <div class="alert alert-info d-print-none small mb-3">
                    <strong><i class="fas fa-bug me-1"></i>Debug Info:</strong><br>
                    <span class="badge {{ $estimate->inspection ? 'bg-success' : 'bg-danger' }}">
                        Inspection: {{ $estimate->inspection ? 'EXISTS' : 'NOT LINKED' }}
                    </span>
                    @if($estimate->inspection)
                    <span class="badge {{ $estimate->inspection->customer_concerns ? 'bg-success' : 'bg-warning' }}">
                        Concerns: {{ $estimate->inspection->customer_concerns ? 'EXISTS' : 'EMPTY' }}
                    </span>
                    @php
                        $findingsCount = 0;
                        if (is_array($estimate->inspection->findings)) {
                            $findingsCount = count($estimate->inspection->findings);
                        } elseif (is_string($estimate->inspection->findings) && !empty($estimate->inspection->findings)) {
                            $decoded = json_decode($estimate->inspection->findings, true);
                            $findingsCount = is_array($decoded) ? count($decoded) : 0;
                        }
                    @endphp
                    <span class="badge {{ $findingsCount > 0 ? 'bg-success' : 'bg-warning' }}">
                        Findings: {{ $findingsCount }}
                    </span>
                    @php
                        $photosCount = 0;
                        if (is_array($estimate->inspection->photos)) {
                            $photosCount = count($estimate->inspection->photos);
                        } elseif (is_string($estimate->inspection->photos) && !empty($estimate->inspection->photos)) {
                            $decoded = json_decode($estimate->inspection->photos, true);
                            $photosCount = is_array($decoded) ? count($decoded) : 0;
                        }
                    @endphp
                    <span class="badge {{ $photosCount > 0 ? 'bg-success' : 'bg-warning' }}">
                        Photos: {{ $photosCount }}
                    </span>
                    @endif
                </div>

                <!-- Inspection Reference (if available) -->
                @if($estimate->inspection)
                <div class="card mt-4">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="fas fa-clipboard-check text-primary me-2"></i>Related Inspection #{{ $estimate->inspection->id }}</h6>
                    </div>
                    <div class="card-body">
                        <!-- Inspection Summary -->
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <table class="table table-sm table-borderless">
                                    <tr>
                                        <th width="50%">Date:</th>
                                        <td>{{ $estimate->inspection->created_at->format('M d, Y') }}</td>
                                    </tr>
                                    <tr>
                                        <th>Status:</th>
                                        <td>
                                            @php
                                                $status = $estimate->inspection->inspection_status;
                                                $statusColor = match($status) {
                                                    'draft' => 'warning',
                                                    'in_progress' => 'info',
                                                    'completed' => 'success',
                                                    'approved' => 'success',
                                                    'rejected' => 'danger',
                                                    'cancelled' => 'secondary',
                                                    default => 'light',
                                                };
                                            @endphp
                                            <span class="badge bg-{{ $statusColor }}">
                                                {{ ucfirst(str_replace('_', ' ', $status)) }}
                                            </span>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-8">
                                <!-- Inspection Types -->
                                @php
                                    $inspectionTypes = $estimate->inspection->inspection_type;
                                    if (is_string($inspectionTypes) && !empty($inspectionTypes)) {
                                        $inspectionTypes = json_decode($inspectionTypes, true);
                                    }
                                @endphp
                                @if(is_array($inspectionTypes) && count($inspectionTypes) > 0)
                                <h6 class="mb-2"><i class="fas fa-clipboard-list me-1"></i>Inspection Types</h6>
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach($inspectionTypes as $type)
                                    <span class="badge bg-info">
                                        <i class="fas fa-clipboard-check me-1"></i>
                                        {{ match($type) {
                                            'pre_purchase' => 'Pre-Purchase',
                                            'safety' => 'Safety',
                                            'emissions' => 'Emissions',
                                            'routine' => 'Routine',
                                            'diagnostic' => 'Diagnostic',
                                            'post_repair' => 'Post-Repair',
                                            'comprehensive' => 'Comprehensive',
                                            'custom' => 'Custom',
                                            default => ucfirst(str_replace('_', ' ', $type)),
                                        } }}
                                    </span>
                                    @endforeach
                                </div>
                                @endif
                            </div>
                        </div>

                        <!-- Customer Concerns -->
                        @if($estimate->inspection->customer_concerns)
                        <div class="mb-4">
                            <h6 class="mb-2"><i class="fas fa-comment-medical text-warning me-2"></i>Customer Concerns</h6>
                            <div class="card bg-light">
                                <div class="card-body">
                                    <p class="mb-0">{{ $estimate->inspection->customer_concerns }}</p>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Complete Findings -->
                        @if($estimate->inspection && $estimate->inspection->findings && count($estimate->inspection->findings) > 0)
                        <div class="mb-4">
                            <h6 class="mb-2"><i class="fas fa-search text-danger me-2"></i>All Findings ({{ count($estimate->inspection->findings) }})</h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th width="10%">#</th>
                                            <th width="60%">Description</th>
                                            <th width="15%">Severity</th>
                                            <th width="15%">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($estimate->inspection->findings as $index => $finding)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>
                                                {{ $finding['description'] ?? 'No description' }}
                                                @if(isset($finding['notes']) && $finding['notes'])
                                                    <br><small class="text-muted">{{ $finding['notes'] }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                @php
                                                    $severity = $finding['severity'] ?? 'medium';
                                                    $severityColor = match($severity) {
                                                        'critical' => 'danger',
                                                        'high' => 'warning',
                                                        'medium' => 'info',
                                                        'low' => 'success',
                                                        default => 'secondary'
                                                    };
                                                    $severityLabel = ucfirst($severity);
                                                @endphp
                                                <span class="badge bg-{{ $severityColor }}">{{ $severityLabel }}</span>
                                            </td>
                                            <td>
                                                @php
                                                    $status = $finding['status'] ?? 'pending';
                                                    $statusColor = match($status) {
                                                        'pending' => 'warning',
                                                        'in_progress' => 'info',
                                                        'completed' => 'success',
                                                        'approved' => 'success',
                                                        'rejected' => 'danger',
                                                        default => 'secondary'
                                                    };
                                                    $statusLabel = ucfirst(str_replace('_', ' ', $status));
                                                @endphp
                                                <span class="badge bg-{{ $statusColor }}">{{ $statusLabel }}</span>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        @endif

                        <!-- Photos -->
                        @php
                            $photos = $estimate->inspection->photos;
                            if (is_string($photos) && !empty($photos)) {
                                $photos = json_decode($photos, true);
                            }
                            if (!is_array($photos)) {
                                $photos = [];
                            }
                        @endphp
                        @if(count($photos) > 0)
                        <div class="mb-4">
                            <h6 class="mb-2"><i class="fas fa-camera text-success me-2"></i>Inspection Photos ({{ count($photos) }})</h6>
                            <div class="row g-3">
                                @foreach($photos as $index => $photo)
                                <div class="col-md-4 col-sm-6">
                                    <div class="card">
                                        <div class="card-body p-2 text-center">
                                            <div class="position-relative">
                                                @if(isset($photo['url']))
                                                <img src="{{ $photo['url'] }}" 
                                                     alt="Inspection Photo {{ $index + 1 }}" 
                                                     class="img-fluid rounded" 
                                                     style="max-height: 150px; object-fit: cover;">
                                                @elseif(isset($photo['path']))
                                                <img src="{{ asset('storage/' . $photo['path']) }}" 
                                                     alt="Inspection Photo {{ $index + 1 }}" 
                                                     class="img-fluid rounded" 
                                                     style="max-height: 150px; object-fit: cover;">
                                                @else
                                                <div class="bg-light rounded d-flex align-items-center justify-content-center" style="height: 150px;">
                                                    <i class="fas fa-image fa-2x text-muted"></i>
                                                </div>
                                                @endif
                                                <span class="position-absolute top-0 start-0 badge bg-dark m-1">#{{ $index + 1 }}</span>
                                            </div>
                                            @if(isset($photo['caption']) && $photo['caption'])
                                            <p class="small text-muted mt-2 mb-0">{{ Str::limit($photo['caption'], 50) }}</p>
                                            @endif
                                            @if(isset($photo['timestamp']) && $photo['timestamp'])
                                            <p class="small text-muted mb-0">{{ \Carbon\Carbon::parse($photo['timestamp'])->format('M d, Y') }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="window.print()">
                    <i class="fas fa-print me-1"></i>Print This View
                </button>
            </div>
        </div>
    </div>
</div>

@endsection
