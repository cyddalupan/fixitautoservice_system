@extends('layouts.app')

@section('title', 'Loyalty History')

@section('content')
<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col">
            <h1 class="h2 mb-0">Loyalty History</h1>
            <p class="text-muted mb-0">Complete history of your points and redemptions</p>
        </div>
        <div class="col-auto">
            <div class="d-flex gap-2">
                <a href="{{ route('portal.loyalty.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Back to Loyalty
                </a>
                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#exportModal">
                    <i class="bi bi-download me-1"></i> Export
                </button>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card border-primary">
                <div class="card-body text-center">
                    <div class="display-4 fw-bold text-primary mb-2">{{ number_format($totalPointsEarned) }}</div>
                    <h6 class="card-title text-muted mb-0">Total Points Earned</h6>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card border-success">
                <div class="card-body text-center">
                    <div class="display-4 fw-bold text-success mb-2">{{ number_format($totalPointsRedeemed) }}</div>
                    <h6 class="card-title text-muted mb-0">Total Points Redeemed</h6>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card border-warning">
                <div class="card-body text-center">
                    <div class="display-4 fw-bold text-warning mb-2">{{ number_format($totalPointsExpired) }}</div>
                    <h6 class="card-title text-muted mb-0">Points Expired</h6>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card border-info">
                <div class="card-body text-center">
                    <div class="display-4 fw-bold text-info mb-2">{{ $totalRedemptions }}</div>
                    <h6 class="card-title text-muted mb-0">Total Redemptions</h6>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="row mb-4">
        <div class="col">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="{{ route('portal.loyalty.history') }}" class="row g-3">
                        <div class="col-md-3">
                            <label for="type" class="form-label">Type</label>
                            <select class="form-select" id="type" name="type">
                                <option value="">All Types</option>
                                <option value="earned" {{ request('type') === 'earned' ? 'selected' : '' }}>Earned</option>
                                <option value="redeemed" {{ request('type') === 'redeemed' ? 'selected' : '' }}>Redeemed</option>
                                <option value="expired" {{ request('type') === 'expired' ? 'selected' : '' }}>Expired</option>
                                <option value="adjusted" {{ request('type') === 'adjusted' ? 'selected' : '' }}>Adjusted</option>
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
                        <div class="col-md-3">
                            <label for="sort" class="form-label">Sort By</label>
                            <select class="form-select" id="sort" name="sort">
                                <option value="newest" {{ request('sort') === 'newest' ? 'selected' : '' }}>Newest First</option>
                                <option value="oldest" {{ request('sort') === 'oldest' ? 'selected' : '' }}>Oldest First</option>
                                <option value="points_high" {{ request('sort') === 'points_high' ? 'selected' : '' }}>Points: High to Low</option>
                                <option value="points_low" {{ request('sort') === 'points_low' ? 'selected' : '' }}>Points: Low to High</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-funnel me-1"></i> Apply Filters
                                </button>
                                <a href="{{ route('portal.loyalty.history') }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-x-circle me-1"></i> Clear Filters
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- History Timeline -->
    <div class="row">
        <div class="col">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Points History Timeline</h5>
                </div>
                <div class="card-body">
                    @if($history->count() > 0)
                        <div class="timeline">
                            @foreach($history as $item)
                            <div class="timeline-item {{ $item->type === 'earned' ? 'timeline-item-success' : ($item->type === 'redeemed' ? 'timeline-item-warning' : 'timeline-item-danger') }}">
                                <div class="timeline-marker">
                                    @if($item->type === 'earned')
                                        <i class="bi bi-plus-circle-fill"></i>
                                    @elseif($item->type === 'redeemed')
                                        <i class="bi bi-dash-circle-fill"></i>
                                    @else
                                        <i class="bi bi-exclamation-circle-fill"></i>
                                    @endif
                                </div>
                                <div class="timeline-content">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <h6 class="mb-1">{{ $item->description }}</h6>
                                            <p class="text-muted small mb-0">
                                                {{ $item->created_at->format('F d, Y \a\t h:i A') }}
                                                @if($item->reference_type && $item->reference_id)
                                                    • Reference: {{ $item->reference_type }} #{{ $item->reference_id }}
                                                @endif
                                            </p>
                                        </div>
                                        <div class="text-end">
                                            <div class="fs-5 fw-bold {{ $item->type === 'earned' ? 'text-success' : ($item->type === 'redeemed' ? 'text-warning' : 'text-danger') }}">
                                                {{ $item->type === 'earned' ? '+' : ($item->type === 'redeemed' ? '-' : '') }}{{ number_format($item->points) }}
                                            </div>
                                            <span class="badge {{ $item->status === 'approved' ? 'bg-success' : ($item->status === 'pending' ? 'bg-warning text-dark' : 'bg-danger') }}">
                                                {{ ucfirst($item->status) }}
                                            </span>
                                        </div>
                                    </div>
                                    
                                    @if($item->notes)
                                        <div class="alert alert-light mt-2 mb-0">
                                            <small class="text-muted">{{ $item->notes }}</small>
                                        </div>
                                    @endif
                                    
                                    @if($item->type === 'redeemed' && $item->reward)
                                        <div class="card bg-light mt-2">
                                            <div class="card-body py-2">
                                                <div class="d-flex align-items-center">
                                                    <i class="bi bi-gift text-primary me-2"></i>
                                                    <div>
                                                        <small class="text-muted">Reward:</small>
                                                        <strong>{{ $item->reward->name }}</strong>
                                                        <small class="text-muted ms-2">({{ $item->reward->value }})</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                        
                        <!-- Pagination -->
                        @if($history->hasPages())
                        <div class="d-flex justify-content-center mt-4">
                            {{ $history->links() }}
                        </div>
                        @endif
                    @else
                        <div class="text-center py-5">
                            <div class="mb-3">
                                <i class="bi bi-clock-history display-1 text-muted"></i>
                            </div>
                            <h4 class="text-muted mb-2">No history found</h4>
                            <p class="text-muted mb-4">Your loyalty history will appear here</p>
                            <a href="{{ route('portal.loyalty.index') }}" class="btn btn-primary">
                                <i class="bi bi-arrow-left me-1"></i> Back to Loyalty Program
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly Statistics -->
    <div class="row mt-4">
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Points by Month</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Month</th>
                                    <th class="text-end">Earned</th>
                                    <th class="text-end">Redeemed</th>
                                    <th class="text-end">Net</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($monthlyStats as $stat)
                                <tr>
                                    <td>{{ $stat->month }}</td>
                                    <td class="text-end text-success">+{{ number_format($stat->earned) }}</td>
                                    <td class="text-end text-warning">-{{ number_format($stat->redeemed) }}</td>
                                    <td class="text-end fw-bold {{ $stat->net >= 0 ? 'text-success' : 'text-danger' }}">
                                        {{ $stat->net >= 0 ? '+' : '' }}{{ number_format($stat->net) }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Redemption History</h5>
                </div>
                <div class="card-body">
                    @if($redemptions->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($redemptions as $redemption)
                            <div class="list-group-item border-0 px-0 py-3">
                                <div class="d-flex align-items-start">
                                    <div class="flex-shrink-0">
                                        <div class="avatar avatar-lg bg-light rounded-circle d-flex align-items-center justify-content-center">
                                            <i class="bi bi-gift-fill text-primary"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <div class="d-flex justify-content-between align-items-start mb-1">
                                            <h6 class="mb-0">{{ $redemption->reward->name }}</h6>
                                            <span class="badge {{ $redemption->status === 'completed' ? 'bg-success' : ($redemption->status === 'pending' ? 'bg-warning text-dark' : 'bg-secondary') }}">
                                                {{ ucfirst($redemption->status) }}
                                            </span>
                                        </div>
                                        <p class="text-muted small mb-2">{{ $redemption->reward->description }}</p>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <small class="text-muted">Redeemed:</small>
                                                <span class="fw-medium">{{ $redemption->created_at->format('M d, Y') }}</span>
                                            </div>
                                            <div class="text-end">
                                                <small class="text-muted">Points:</small>
                                                <span class="fw-bold text-warning">-{{ number_format($redemption->points_used) }}</span>
                                            </div>
                                        </div>
                                        @if($redemption->notes)
                                            <div class="alert alert-light mt-2 mb-0">
                                                <small class="text-muted">{{ $redemption->notes }}</small>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="bi bi-gift display-1 text-muted mb-3"></i>
                            <h6 class="text-muted mb-2">No redemptions yet</h6>
                            <p class="text-muted small mb-0">Start redeeming rewards from the rewards page</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Export Modal -->
<div class="modal fade" id="exportModal" tabindex="-1" aria-labelledby="exportModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exportModalLabel">Export History</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('portal.loyalty.export') }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="export_format" class="form-label">Format</label>
                        <select class="form-select" id="export_format" name="format" required>
                            <option value="csv">CSV (Excel)</option>
                            <option value="pdf">PDF Document</option>
                            <option value="json">JSON Data</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="export_range" class="form-label">Date Range</label>
                        <select class="form-select" id="export_range" name="range" required>
                            <option value="all">All History</option>
                            <option value="year">Last 12 Months</option>
                            <option value="quarter">Last 3 Months</option>
                            <option value="month">Last 30 Days</option>
                            <option value="custom">Custom Range</option>
                        </select>
                    </div>
                    
                    <div id="customRangeFields" class="d-none">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="export_from" class="form-label">From Date</label>
                                <input type="date" class="form-control" id="export_from" name="from">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="export_to" class="form-label">To Date</label>
                                <input type="date" class="form-control" id="export_to" name="to">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="export_include" class="form-label">Include</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="include_earned" name="include[]" value="earned" checked>
                            <label class="form-check-label" for="include_earned">
                                Points Earned
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="include_redeemed" name="include[]" value="redeemed" checked>
                            <label class="form-check-label" for="include_redeemed">
                                Points Redeemed
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="include_expired" name="include[]" value="expired" checked>
                            <label class="form-check-label" for="include_expired">
                                Points Expired
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="include_redemptions" name="include[]" value="redemptions" checked>
                            <label class="form-check-label" for="include_redemptions">
                                Redemption Details
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-download me-1"></i> Export Data
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('styles')
<style>
    .timeline {
        position: relative;
        padding-left: 2rem;
    }
    
    .timeline::before {
        content: '';
        position: absolute;
        left: 1rem;
        top: 0;
        bottom: 0;
        width: 2px;
        background-color: #e9ecef;
    }
    
    .timeline-item {
        position: relative;
        margin-bottom: 2rem;
    }
    
    .timeline-marker {
        position: absolute;
        left: -2rem;
        top: 0;
        width: 2rem;
        height: 2rem;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1;
    }
    
    .timeline-item-success .timeline-marker {
        background-color: rgba(25, 135, 84, 0.1);
        color: #198754;
    }
    
    .timeline-item-warning .timeline-marker {
        background-color: rgba(255, 193, 7, 0.1);
        color: #ffc107;
    }
    
    .timeline-item-danger .timeline-marker {
        background-color: rgba(220, 53, 69, 0.1);
        color: #dc3545;
    }
    
    .timeline-content {
        padding-left: 1rem;
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Export modal date range toggle
        const exportRange = document.getElementById('export_range');
        const customRangeFields = document.getElementById('customRangeFields');
        
        if (exportRange && customRangeFields) {
            exportRange.addEventListener('change', function() {
                if (this.value === 'custom') {
                    customRangeFields.classList.remove('d-none');
                } else {
                    customRangeFields.classList.add('d-none');
                }
            });
        }
        
        // Set default dates for export
        const today = new Date();
        const thirtyDaysAgo = new Date();
        thirtyDaysAgo.setDate(today.getDate() - 30);
        
        const exportFrom = document.getElementById('export_from');
        const exportTo = document.getElementById('export_to');
        
        if (exportFrom && exportTo) {
            exportFrom.value = thirtyDaysAgo.toISOString().split('T')[0];
            exportTo.value = today.toISOString().split('T')[0];
        }
        
        // Form validation for custom range
        const exportForm = document.querySelector('#exportModal form');
        if (exportForm) {
            exportForm.addEventListener('submit', function(e) {
                const range = document.getElementById('export_range').value;
                const from = document.getElementById('export_from').value;
                const to = document.getElementById('export_to').value;
                
                if (range === 'custom' && (!from || !to)) {
                    e.preventDefault();
                    alert('Please select both start and end dates for custom range.');
                    return false;
                }
                
                if (range === 'custom' && new Date(from) > new Date(to)) {
                    e.preventDefault();
                    alert('Start date cannot be after end date.');
                    return false;
                }
                
                // Show loading indicator
                const submitBtn = this.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Exporting...';
                submitBtn.disabled = true;
                
                // Re-enable button after 5 seconds if still on page
                setTimeout(() => {
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }, 5000);
            });
        }
        
        // Auto-submit filters on change (optional)
        const filterInputs = document.querySelectorAll('#type, #date_from, #date_to, #sort');
        filterInputs.forEach(input => {
            input.addEventListener('change', function() {
                // Only auto-submit if all required fields are filled
                if (this.id === 'date_from' || this.id === 'date_to') {
                    const from = document.getElementById('date_from').value;
                    const to = document.getElementById('date_to').value;
                    
                    if (from && to && new Date(from) > new Date(to)) {
                        alert('Start date cannot be after end date.');
                        return;
                    }
                }
                
                // Submit the form
                this.closest('form').submit();
            });
        });
    });
</script>
@endpush
@endsection