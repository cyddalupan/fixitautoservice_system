@extends('layouts.app')

@section('title', 'Loyalty Program')

@section('content')
<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col">
            <h1 class="h2 mb-0">Loyalty Program</h1>
            <p class="text-muted mb-0">Earn points with every service and redeem for rewards</p>
        </div>
        <div class="col-auto">
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#howItWorksModal">
                    <i class="bi bi-question-circle me-1"></i> How It Works
                </button>
                <a href="{{ route('portal.loyalty.rewards') }}" class="btn btn-primary">
                    <i class="bi bi-gift me-1"></i> View Rewards
                </a>
            </div>
        </div>
    </div>

    <!-- Loyalty Points Summary -->
    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="card border-primary h-100">
                <div class="card-body text-center">
                    <div class="display-4 fw-bold text-primary mb-2">{{ number_format($loyaltyPoints) }}</div>
                    <h5 class="card-title text-muted mb-3">Available Points</h5>
                    <div class="progress mb-3" style="height: 8px;">
                        <div class="progress-bar bg-primary" role="progressbar" 
                             style="width: {{ min(100, ($loyaltyPoints / 1000) * 100) }}%"
                             aria-valuenow="{{ $loyaltyPoints }}" 
                             aria-valuemin="0" 
                             aria-valuemax="1000">
                        </div>
                    </div>
                    <p class="small text-muted mb-0">
                        {{ 1000 - $loyaltyPoints }} points to next reward tier
                    </p>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-3">
            <div class="card border-success h-100">
                <div class="card-body text-center">
                    <div class="display-4 fw-bold text-success mb-2">{{ $currentTier }}</div>
                    <h5 class="card-title text-muted mb-3">Current Tier</h5>
                    <div class="mb-3">
                        <span class="badge bg-success fs-6">{{ $tierName }}</span>
                    </div>
                    <p class="small text-muted mb-0">
                        {{ $tierBenefits }}
                    </p>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-3">
            <div class="card border-warning h-100">
                <div class="card-body text-center">
                    <div class="display-4 fw-bold text-warning mb-2">{{ $availableRewards }}</div>
                    <h5 class="card-title text-muted mb-3">Available Rewards</h5>
                    <div class="mb-3">
                        <span class="badge bg-warning text-dark fs-6">{{ $nextRewardPoints }} points</span>
                    </div>
                    <p class="small text-muted mb-0">
                        Next reward at {{ $nextRewardPoints }} points
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Points History & Rewards -->
    <div class="row">
        <!-- Points History -->
        <div class="col-lg-8 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Points History</h5>
                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#loyaltyFilters">
                        <i class="fas fa-filter me-1"></i> Filters
                    </button>
                </div>
                <div class="card-body">
                    <!-- Loyalty Filters -->
                    <div class="collapse mb-4" id="loyaltyFilters">
                        <div class="card card-body bg-light">
                            <form method="GET" action="{{ route('portal.loyalty.index') }}" class="row g-3">
                                <div class="col-md-4">
                                    <label for="transaction_type" class="form-label">Transaction Type</label>
                                    <select class="form-select" id="transaction_type" name="type">
                                        <option value="">All Types</option>
                                        <option value="earned" {{ request('type') === 'earned' ? 'selected' : '' }}>Points Earned</option>
                                        <option value="redeemed" {{ request('type') === 'redeemed' ? 'selected' : '' }}>Points Redeemed</option>
                                        <option value="expired" {{ request('type') === 'expired' ? 'selected' : '' }}>Points Expired</option>
                                        <option value="adjusted" {{ request('type') === 'adjusted' ? 'selected' : '' }}>Points Adjusted</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="date_from" class="form-label">From Date</label>
                                    <input type="date" class="form-control" id="date_from" name="date_from" 
                                           value="{{ request('date_from') }}">
                                </div>
                                <div class="col-md-4">
                                    <label for="date_to" class="form-label">To Date</label>
                                    <input type="date" class="form-control" id="date_to" name="date_to" 
                                           value="{{ request('date_to') }}">
                                </div>
                                <div class="col-12">
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-primary btn-sm">
                                            <i class="fas fa-filter me-1"></i> Apply Filters
                                        </button>
                                        <a href="{{ route('portal.loyalty.index') }}" class="btn btn-outline-secondary btn-sm">
                                            <i class="fas fa-times me-1"></i> Clear Filters
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    @if($pointsHistory->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Description</th>
                                        <th class="text-end">Points</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pointsHistory as $history)
                                    <tr>
                                        <td>{{ $history->created_at->format('M d, Y') }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @if($history->type === 'earned')
                                                    <i class="bi bi-plus-circle-fill text-success me-2"></i>
                                                @elseif($history->type === 'redeemed')
                                                    <i class="bi bi-dash-circle-fill text-warning me-2"></i>
                                                @else
                                                    <i class="bi bi-arrow-left-right text-info me-2"></i>
                                                @endif
                                                <div>
                                                    <div class="fw-medium">{{ $history->description }}</div>
                                                    @if($history->reference_type && $history->reference_id)
                                                        <small class="text-muted">
                                                            Reference: {{ $history->reference_type }} #{{ $history->reference_id }}
                                                        </small>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-end">
                                            @if($history->type === 'earned')
                                                <span class="text-success fw-bold">+{{ number_format($history->points) }}</span>
                                            @elseif($history->type === 'redeemed')
                                                <span class="text-warning fw-bold">-{{ number_format($history->points) }}</span>
                                            @else
                                                <span class="text-info fw-bold">{{ number_format($history->points) }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($history->status === 'pending')
                                                <span class="badge bg-warning text-dark">Pending</span>
                                            @elseif($history->status === 'approved')
                                                <span class="badge bg-success">Approved</span>
                                            @elseif($history->status === 'expired')
                                                <span class="badge bg-danger">Expired</span>
                                            @else
                                                <span class="badge bg-secondary">{{ ucfirst($history->status) }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        @if($pointsHistory->hasPages())
                        <div class="d-flex justify-content-center mt-3">
                            {{ $pointsHistory->links() }}
                        </div>
                        @endif
                    @else
                        <div class="text-center py-5">
                            <div class="mb-3">
                                <i class="bi bi-trophy display-1 text-muted"></i>
                            </div>
                            <h5 class="text-muted mb-2">No points history yet</h5>
                            <p class="text-muted mb-0">Start earning points with your next service!</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Available Rewards -->
        <div class="col-lg-4 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Available Rewards</h5>
                </div>
                <div class="card-body">
                    @if($availableRewardsList->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($availableRewardsList as $reward)
                            <div class="list-group-item border-0 px-0 py-3">
                                <div class="d-flex align-items-start">
                                    <div class="flex-shrink-0">
                                        <div class="avatar avatar-lg bg-light rounded-circle d-flex align-items-center justify-content-center">
                                            <i class="bi bi-gift-fill text-primary fs-4"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="mb-1">{{ $reward->name }}</h6>
                                        <p class="text-muted small mb-2">{{ $reward->description }}</p>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="badge bg-primary">{{ number_format($reward->points_required) }} points</span>
                                            @if($loyaltyPoints >= $reward->points_required)
                                                <button type="button" class="btn btn-sm btn-outline-primary" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#redeemModal"
                                                        data-reward-id="{{ $reward->id }}"
                                                        data-reward-name="{{ $reward->name }}"
                                                        data-points-required="{{ $reward->points_required }}">
                                                    Redeem
                                                </button>
                                            @else
                                                <span class="text-muted small">
                                                    {{ $reward->points_required - $loyaltyPoints }} more points needed
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="bi bi-gift display-1 text-muted mb-3"></i>
                            <h6 class="text-muted mb-2">No rewards available</h6>
                            <p class="text-muted small mb-0">Check back soon for new rewards!</p>
                        </div>
                    @endif
                    
                    <!-- Tier Benefits -->
                    <div class="mt-4 pt-3 border-top">
                        <h6 class="mb-3">Your Tier Benefits</h6>
                        <ul class="list-unstyled mb-0">
                            @foreach($tierBenefitsList as $benefit)
                            <li class="mb-2">
                                <i class="bi bi-check-circle-fill text-success me-2"></i>
                                <span class="small">{{ $benefit }}</span>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- How to Earn More Points -->
    <div class="row mt-4">
        <div class="col">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">How to Earn More Points</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <div class="text-center">
                                <div class="avatar avatar-xl bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3">
                                    <i class="bi bi-car-front-fill text-primary fs-3"></i>
                                </div>
                                <h6 class="mb-1">Regular Service</h6>
                                <p class="text-muted small mb-0">10 points per ₱100 spent</p>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="text-center">
                                <div class="avatar avatar-xl bg-success bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3">
                                    <i class="bi bi-calendar-check-fill text-success fs-3"></i>
                                </div>
                                <h6 class="mb-1">On-time Payment</h6>
                                <p class="text-muted small mb-0">50 points for paying on time</p>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="text-center">
                                <div class="avatar avatar-xl bg-warning bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3">
                                    <i class="bi bi-star-fill text-warning fs-3"></i>
                                </div>
                                <h6 class="mb-1">Leave a Review</h6>
                                <p class="text-muted small mb-0">25 points for 5-star reviews</p>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="text-center">
                                <div class="avatar avatar-xl bg-info bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3">
                                    <i class="bi bi-person-plus-fill text-info fs-3"></i>
                                </div>
                                <h6 class="mb-1">Refer a Friend</h6>
                                <p class="text-muted small mb-0">100 points per referral</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- How It Works Modal -->
<div class="modal fade" id="howItWorksModal" tabindex="-1" aria-labelledby="howItWorksModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="howItWorksModalLabel">How Our Loyalty Program Works</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <h6 class="mb-3">Earning Points</h6>
                        <ul class="list-unstyled">
                            <li class="mb-2">
                                <i class="bi bi-check-circle text-success me-2"></i>
                                <strong>Service Points:</strong> 10 points per ₱100 spent
                            </li>
                            <li class="mb-2">
                                <i class="bi bi-check-circle text-success me-2"></i>
                                <strong>On-time Payment:</strong> 50 points per invoice
                            </li>
                            <li class="mb-2">
                                <i class="bi bi-check-circle text-success me-2"></i>
                                <strong>Reviews:</strong> 25 points for 5-star reviews
                            </li>
                            <li class="mb-2">
                                <i class="bi bi-check-circle text-success me-2"></i>
                                <strong>Referrals:</strong> 100 points per successful referral
                            </li>
                        </ul>
                    </div>
                    <div class="col-md-6 mb-4">
                        <h6 class="mb-3">Tier Benefits</h6>
                        <ul class="list-unstyled">
                            <li class="mb-2">
                                <span class="badge bg-secondary me-2">Bronze</span>
                                <span>0-999 points - Basic rewards</span>
                            </li>
                            <li class="mb-2">
                                <span class="badge bg-success me-2">Silver</span>
                                <span>1000-2499 points - Priority scheduling</span>
                            </li>
                            <li class="mb-2">
                                <span class="badge bg-primary me-2">Gold</span>
                                <span>2500-4999 points - Free loaner car</span>
                            </li>
                            <li class="mb-2">
                                <span class="badge bg-warning text-dark me-2">Platinum</span>
                                <span>5000+ points - VIP treatment</span>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="alert alert-info">
                    <h6 class="alert-heading mb-2"><i class="bi bi-info-circle me-2"></i>Important Information</h6>
                    <ul class="mb-0">
                        <li>Points expire after 12 months of inactivity</li>
                        <li>Rewards are subject to availability</li>
                        <li>Points cannot be transferred or sold</li>
                        <li>All redemptions are final</li>
                    </ul>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Redeem Modal -->
<div class="modal fade" id="redeemModal" tabindex="-1" aria-labelledby="redeemModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="redeemModalLabel">Redeem Reward</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="redeemForm" method="POST" action="{{ route('portal.loyalty.redeem') }}">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="reward_id" id="rewardId">
                    
                    <div class="text-center mb-4">
                        <div class="avatar avatar-xl bg-light rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3">
                            <i class="bi bi-gift-fill text-primary fs-2"></i>
                        </div>
                        <h5 id="rewardName" class="mb-2"></h5>
                        <p class="text-muted mb-0">You are about to redeem this reward</p>
                    </div>
                    
                    <div class="alert alert-warning">
                        <div class="d-flex">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            <div>
                                <strong>Points Required:</strong> <span id="pointsRequired" class="fw-bold"></span> points
                                <br>
                                <strong>Your Balance:</strong> {{ number_format($loyaltyPoints) }} points
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="confirmation" class="form-label">Type "REDEEM" to confirm</label>
                        <input type="text" class="form-control" id="confirmation" name="confirmation" 
                               placeholder="Type REDEEM here" required>
                        <div class="form-text">This action cannot be undone</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="redeemButton" disabled>
                        <i class="bi bi-gift me-1"></i> Redeem Reward
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Redeem modal setup
    document.addEventListener('DOMContentLoaded', function() {
        const redeemModal = document.getElementById('redeemModal');
        if (redeemModal) {
            redeemModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const rewardId = button.getAttribute('data-reward-id');
                const rewardName = button.getAttribute('data-reward-name');
                const pointsRequired = button.getAttribute('data-points-required');
                
                document.getElementById('rewardId').value = rewardId;
                document.getElementById('rewardName').textContent = rewardName;
                document.getElementById('pointsRequired').textContent = pointsRequired;
                
                // Reset confirmation field
                document.getElementById('confirmation').value = '';
                document.getElementById('redeemButton').disabled = true;
            });
        }
        
        // Confirmation validation
        const confirmationInput = document.getElementById('confirmation');
        const redeemButton = document.getElementById('redeemButton');
        
        if (confirmationInput && redeemButton) {
            confirmationInput.addEventListener('input', function() {
                redeemButton.disabled = this.value.toUpperCase() !== 'REDEEM';
            });
        }
        
        // Form submission
        const redeemForm = document.getElementById('redeemForm');
        if (redeemForm) {
            redeemForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                if (confirm('Are you sure you want to redeem this reward? This action cannot be undone.')) {
                    this.submit();
                }
            });
        }
    });
</script>
@endpush
@endsection