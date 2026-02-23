@extends('layouts.app')

@section('title', 'Loyalty Rewards')

@section('content')
<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col">
            <h1 class="h2 mb-0">Loyalty Rewards</h1>
            <p class="text-muted mb-0">Browse and redeem available rewards</p>
        </div>
        <div class="col-auto">
            <div class="d-flex gap-2">
                <a href="{{ route('portal.loyalty.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Back to Loyalty
                </a>
                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#filterModal">
                    <i class="bi bi-funnel me-1"></i> Filter
                </button>
            </div>
        </div>
    </div>

    <!-- Points Summary -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card border-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title text-muted mb-1">Your Points Balance</h5>
                            <div class="display-4 fw-bold text-primary">{{ number_format($loyaltyPoints) }}</div>
                        </div>
                        <div class="text-end">
                            <div class="badge bg-primary fs-6 mb-2">Available</div>
                            <p class="text-muted small mb-0">Last updated: {{ now()->format('M d, Y') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title text-muted mb-1">Rewards Redeemed</h5>
                            <div class="display-4 fw-bold text-success">{{ $redeemedCount }}</div>
                        </div>
                        <div class="text-end">
                            <div class="badge bg-success fs-6 mb-2">Total</div>
                            <p class="text-muted small mb-0">{{ $availableRewards }} rewards available</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Rewards Grid -->
    <div class="row">
        @if($rewards->count() > 0)
            @foreach($rewards as $reward)
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100 reward-card {{ $loyaltyPoints >= $reward->points_required ? 'border-primary' : 'border-secondary' }}">
                    <div class="card-body">
                        <!-- Reward Header -->
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <span class="badge {{ $reward->category === 'discount' ? 'bg-success' : ($reward->category === 'service' ? 'bg-primary' : 'bg-warning text-dark') }} mb-2">
                                    {{ ucfirst($reward->category) }}
                                </span>
                                <h5 class="card-title mb-1">{{ $reward->name }}</h5>
                                <p class="text-muted small mb-0">{{ $reward->description }}</p>
                            </div>
                            <div class="text-end">
                                <div class="display-6 fw-bold text-primary">{{ number_format($reward->points_required) }}</div>
                                <small class="text-muted">points</small>
                            </div>
                        </div>
                        
                        <!-- Reward Details -->
                        <div class="mb-3">
                            <div class="d-flex align-items-center mb-2">
                                <i class="bi bi-calendar text-muted me-2"></i>
                                <small class="text-muted">Valid until: {{ $reward->valid_until ? $reward->valid_until->format('M d, Y') : 'No expiration' }}</small>
                            </div>
                            <div class="d-flex align-items-center mb-2">
                                <i class="bi bi-tag text-muted me-2"></i>
                                <small class="text-muted">Category: {{ ucfirst($reward->category) }}</small>
                            </div>
                            @if($reward->restrictions)
                            <div class="d-flex align-items-start mb-2">
                                <i class="bi bi-exclamation-triangle text-muted me-2 mt-1"></i>
                                <small class="text-muted">{{ $reward->restrictions }}</small>
                            </div>
                            @endif
                        </div>
                        
                        <!-- Reward Value -->
                        <div class="bg-light rounded p-3 mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <small class="text-muted d-block">Value</small>
                                    <strong class="fs-5">{{ $reward->value }}</strong>
                                </div>
                                <div class="text-end">
                                    <small class="text-muted d-block">Stock</small>
                                    <strong class="fs-5 {{ $reward->stock_quantity <= 5 ? 'text-danger' : 'text-success' }}">
                                        {{ $reward->stock_quantity > 0 ? $reward->stock_quantity : 'Out of stock' }}
                                    </strong>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Action Buttons -->
                        <div class="d-grid gap-2">
                            @if($reward->stock_quantity > 0)
                                @if($loyaltyPoints >= $reward->points_required)
                                    <button type="button" class="btn btn-primary" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#redeemModal"
                                            data-reward-id="{{ $reward->id }}"
                                            data-reward-name="{{ $reward->name }}"
                                            data-points-required="{{ $reward->points_required }}"
                                            data-reward-value="{{ $reward->value }}">
                                        <i class="bi bi-gift me-1"></i> Redeem Now
                                    </button>
                                @else
                                    <button type="button" class="btn btn-outline-secondary" disabled>
                                        <i class="bi bi-lock me-1"></i> Need {{ $reward->points_required - $loyaltyPoints }} more points
                                    </button>
                                @endif
                            @else
                                <button type="button" class="btn btn-outline-danger" disabled>
                                    <i class="bi bi-x-circle me-1"></i> Out of Stock
                                </button>
                            @endif
                            
                            <button type="button" class="btn btn-outline-primary" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#rewardDetailsModal"
                                    data-reward-id="{{ $reward->id }}">
                                <i class="bi bi-info-circle me-1"></i> View Details
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
            
            <!-- Pagination -->
            @if($rewards->hasPages())
            <div class="col-12">
                <div class="d-flex justify-content-center mt-4">
                    {{ $rewards->links() }}
                </div>
            </div>
            @endif
        @else
            <div class="col-12">
                <div class="text-center py-5">
                    <div class="mb-3">
                        <i class="bi bi-gift display-1 text-muted"></i>
                    </div>
                    <h4 class="text-muted mb-2">No rewards available</h4>
                    <p class="text-muted mb-4">Check back soon for new rewards!</p>
                    <a href="{{ route('portal.loyalty.index') }}" class="btn btn-primary">
                        <i class="bi bi-arrow-left me-1"></i> Back to Loyalty Program
                    </a>
                </div>
            </div>
        @endif
    </div>

    <!-- Tier Comparison -->
    <div class="row mt-5">
        <div class="col">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Reward Tiers Comparison</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Tier</th>
                                    <th>Points Required</th>
                                    <th>Reward Examples</th>
                                    <th>Redemption Limit</th>
                                    <th>Your Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($tiers as $tier)
                                <tr class="{{ $currentTier === $tier->name ? 'table-primary' : '' }}">
                                    <td>
                                        <span class="badge {{ $tier->badge_class }} fs-6">{{ $tier->name }}</span>
                                    </td>
                                    <td>{{ number_format($tier->points_required) }}+ points</td>
                                    <td>{{ $tier->reward_examples }}</td>
                                    <td>{{ $tier->redemption_limit }}</td>
                                    <td>
                                        @if($currentTier === $tier->name)
                                            <span class="badge bg-success">
                                                <i class="bi bi-check-circle me-1"></i> Current Tier
                                            </span>
                                        @elseif($loyaltyPoints >= $tier->points_required)
                                            <span class="badge bg-info">
                                                <i class="bi bi-check-circle me-1"></i> Unlocked
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">
                                                {{ $tier->points_required - $loyaltyPoints }} points to unlock
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filter Modal -->
<div class="modal fade" id="filterModal" tabindex="-1" aria-labelledby="filterModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="filterModalLabel">Filter Rewards</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="GET" action="{{ route('portal.loyalty.rewards') }}">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="category" class="form-label">Category</label>
                        <select class="form-select" id="category" name="category">
                            <option value="">All Categories</option>
                            <option value="discount" {{ request('category') === 'discount' ? 'selected' : '' }}>Discounts</option>
                            <option value="service" {{ request('category') === 'service' ? 'selected' : '' }}>Services</option>
                            <option value="product" {{ request('category') === 'product' ? 'selected' : '' }}>Products</option>
                            <option value="other" {{ request('category') === 'other' ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="points_range" class="form-label">Points Range</label>
                        <select class="form-select" id="points_range" name="points_range">
                            <option value="">All Points</option>
                            <option value="0-500" {{ request('points_range') === '0-500' ? 'selected' : '' }}>0-500 points</option>
                            <option value="501-1000" {{ request('points_range') === '501-1000' ? 'selected' : '' }}>501-1,000 points</option>
                            <option value="1001-2500" {{ request('points_range') === '1001-2500' ? 'selected' : '' }}>1,001-2,500 points</option>
                            <option value="2501+" {{ request('points_range') === '2501+' ? 'selected' : '' }}>2,501+ points</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="availability" class="form-label">Availability</label>
                        <select class="form-select" id="availability" name="availability">
                            <option value="">All</option>
                            <option value="available" {{ request('availability') === 'available' ? 'selected' : '' }}>Available Now</option>
                            <option value="affordable" {{ request('availability') === 'affordable' ? 'selected' : '' }}>Within My Points</option>
                            <option value="soon" {{ request('availability') === 'soon' ? 'selected' : '' }}>Almost There</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="sort_by" class="form-label">Sort By</label>
                        <select class="form-select" id="sort_by" name="sort_by">
                            <option value="points_asc" {{ request('sort_by') === 'points_asc' ? 'selected' : '' }}>Points: Low to High</option>
                            <option value="points_desc" {{ request('sort_by') === 'points_desc' ? 'selected' : '' }}>Points: High to Low</option>
                            <option value="newest" {{ request('sort_by') === 'newest' ? 'selected' : '' }}>Newest First</option>
                            <option value="popular" {{ request('sort_by') === 'popular' ? 'selected' : '' }}>Most Popular</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <a href="{{ route('portal.loyalty.rewards') }}" class="btn btn-outline-danger">Clear Filters</a>
                    <button type="submit" class="btn btn-primary">Apply Filters</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reward Details Modal -->
<div class="modal fade" id="rewardDetailsModal" tabindex="-1" aria-labelledby="rewardDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="rewardDetailsModalLabel">Reward Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="rewardDetailsContent">
                <!-- Content will be loaded via AJAX -->
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Redeem Modal (same as index) -->
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
                                <br>
                                <strong>Reward Value:</strong> <span id="rewardValue"></span>
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
                    <button type="button" class="</button>
                    <button type="submit" class="btn btn-primary" id="redeemButton" disabled>
                        <i class="bi bi-gift me-1"></i> Redeem Reward
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push("scripts")
<script>
    // Redeem modal setup
    document.addEventListener("DOMContentLoaded", function() {
        // Redeem modal
        const redeemModal = document.getElementById("redeemModal");
        if (redeemModal) {
            redeemModal.addEventListener("show.bs.modal", function(event) {
                const button = event.relatedTarget;
                const rewardId = button.getAttribute("data-reward-id");
                const rewardName = button.getAttribute("data-reward-name");
                const pointsRequired = button.getAttribute("data-points-required");
                const rewardValue = button.getAttribute("data-reward-value");
                
                document.getElementById("rewardId").value = rewardId;
                document.getElementById("rewardName").textContent = rewardName;
                document.getElementById("pointsRequired").textContent = pointsRequired;
                document.getElementById("rewardValue").textContent = rewardValue;
                
                // Reset confirmation field
                document.getElementById("confirmation").value = "";
                document.getElementById("redeemButton").disabled = true;
            });
        }
        
        // Confirmation validation
        const confirmationInput = document.getElementById("confirmation");
        const redeemButton = document.getElementById("redeemButton");
        
        if (confirmationInput && redeemButton) {
            confirmationInput.addEventListener("input", function() {
                redeemButton.disabled = this.value.toUpperCase() !== "REDEEM";
            });
        }
        
        // Form submission
        const redeemForm = document.getElementById("redeemForm");
        if (redeemForm) {
            redeemForm.addEventListener("submit", function(e) {
                e.preventDefault();
                
                if (confirm("Are you sure you want to redeem this reward? This action cannot be undone.")) {
                    this.submit();
                }
            });
        }
        
        // Reward details modal
        const detailsModal = document.getElementById("rewardDetailsModal");
        if (detailsModal) {
            detailsModal.addEventListener("show.bs.modal", function(event) {
                const button = event.relatedTarget;
                const rewardId = button.getAttribute("data-reward-id");
                const contentDiv = document.getElementById("rewardDetailsContent");
                
                // Show loading spinner
                contentDiv.innerHTML = `
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                `;
                
                // Load reward details via AJAX
                fetch(`/portal/loyalty/rewards/${rewardId}/details`)
                    .then(response => response.text())
                    .then(html => {
                        contentDiv.innerHTML = html;
                    })
                    .catch(error => {
                        contentDiv.innerHTML = `
                            <div class="alert alert-danger">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                Failed to load reward details. Please try again.
                            </div>
                        `;
                        console.error("Error loading reward details:", error);
                    });
            });
        }
        
        // Filter form submission
        const filterForm = document.querySelector("#filterModal form");
        if (filterForm) {
            filterForm.addEventListener("submit", function(e) {
                // Close modal on submit
                const modal = bootstrap.Modal.getInstance(document.getElementById("filterModal"));
                if (modal) {
                    modal.hide();
                }
            });
        }
    });
    
    // Update URL with filters without page reload
    function updateFilters() {
        const category = document.getElementById("category").value;
        const pointsRange = document.getElementById("points_range").value;
        const availability = document.getElementById("availability").value;
        const sortBy = document.getElementById("sort_by").value;
        
        const params = new URLSearchParams();
        if (category) params.append("category", category);
        if (pointsRange) params.append("points_range", pointsRange);
        if (availability) params.append("availability", availability);
        if (sortBy) params.append("sort_by", sortBy);
        
        window.location.href = `${window.location.pathname}?${params.toString()}`;
    }
</script>
@endpush
@endsection
