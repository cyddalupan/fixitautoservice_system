@extends('layouts.app')

@section('title', 'Reviews - Customer Portal')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="card-title">
                                <i class="fas fa-star me-2"></i>Reviews
                            </h3>
                            <p class="card-subtitle text-muted mb-0">Share your feedback about our services</p>
                        </div>
                        <div>
                            <a href="{{ route('portal.reviews.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-1"></i> Write a Review
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Statistics Cards -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="card-title mb-0">Average Rating</h6>
                                            <h2 class="mb-0">{{ number_format($stats['average_rating'], 1) }}/5</h2>
                                        </div>
                                        <i class="fas fa-star fa-2x opacity-50"></i>
                                    </div>
                                    <small>Based on {{ $stats['total_reviews'] }} reviews</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="card-title mb-0">5-Star Reviews</h6>
                                            <h2 class="mb-0">{{ $stats['five_star_count'] }}</h2>
                                        </div>
                                        <i class="fas fa-star fa-2x opacity-50"></i>
                                    </div>
                                    <small>{{ $stats['five_star_percentage'] }}% of total</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="card-title mb-0">Your Reviews</h6>
                                            <h2 class="mb-0">{{ $stats['your_reviews'] }}</h2>
                                        </div>
                                        <i class="fas fa-user fa-2x opacity-50"></i>
                                    </div>
                                    <small>Reviews you've written</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="card-title mb-0">Pending</h6>
                                            <h2 class="mb-0">{{ $stats['pending_reviews'] }}</h2>
                                        </div>
                                        <i class="fas fa-clock fa-2x opacity-50"></i>
                                    </div>
                                    <small>Services you can review</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Rating Distribution -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-chart-bar me-2"></i>Rating Distribution
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                @foreach([5, 4, 3, 2, 1] as $rating)
                                    <div class="col-md-12 mb-2">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0" style="width: 60px;">
                                                <div class="d-flex align-items-center">
                                                    <span class="me-2">{{ $rating }}</span>
                                                    <i class="fas fa-star text-warning"></i>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1 ms-3">
                                                <div class="progress" style="height: 20px;">
                                                    @php
                                                        $percentage = $stats['rating_distribution'][$rating] ?? 0;
                                                        $count = $stats['rating_counts'][$rating] ?? 0;
                                                    @endphp
                                                    <div class="progress-bar 
                                                        @if($rating >= 4) bg-success
                                                        @elseif($rating == 3) bg-warning
                                                        @else bg-danger @endif" 
                                                        role="progressbar" 
                                                        style="width: {{ $percentage }}%;" 
                                                        aria-valuenow="{{ $percentage }}" 
                                                        aria-valuemin="0" 
                                                        aria-valuemax="100">
                                                        {{ $count }} reviews
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="flex-shrink-0 ms-3" style="width: 80px;">
                                                <span class="text-muted">{{ number_format($percentage, 1) }}%</span>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Filter Form -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-filter me-2"></i>Filter Reviews
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="GET" action="{{ route('portal.reviews.index') }}" id="filterForm">
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <label for="rating" class="form-label">Rating</label>
                                        <select class="form-select" id="rating" name="rating">
                                            <option value="">All Ratings</option>
                                            <option value="5" {{ request('rating') == '5' ? 'selected' : '' }}>5 Stars</option>
                                            <option value="4" {{ request('rating') == '4' ? 'selected' : '' }}>4 Stars</option>
                                            <option value="3" {{ request('rating') == '3' ? 'selected' : '' }}>3 Stars</option>
                                            <option value="2" {{ request('rating') == '2' ? 'selected' : '' }}>2 Stars</option>
                                            <option value="1" {{ request('rating') == '1' ? 'selected' : '' }}>1 Star</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="type" class="form-label">Review Type</label>
                                        <select class="form-select" id="type" name="type">
                                            <option value="">All Types</option>
                                            <option value="service" {{ request('type') == 'service' ? 'selected' : '' }}>Service</option>
                                            <option value="product" {{ request('type') == 'product' ? 'selected' : '' }}>Product</option>
                                            <option value="general" {{ request('type') == 'general' ? 'selected' : '' }}>General</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="status" class="form-label">Status</label>
                                        <select class="form-select" id="status" name="status">
                                            <option value="">All Statuses</option>
                                            <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Published</option>
                                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                            <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="sort" class="form-label">Sort By</label>
                                        <select class="form-select" id="sort" name="sort">
                                            <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>Newest First</option>
                                            <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>Oldest First</option>
                                            <option value="highest" {{ request('sort') == 'highest' ? 'selected' : '' }}>Highest Rating</option>
                                            <option value="lowest" {{ request('sort') == 'lowest' ? 'selected' : '' }}>Lowest Rating</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row g-3 mt-3">
                                    <div class="col-md-12">
                                        <div class="input-group">
                                            <input type="text" class="form-control" name="search" placeholder="Search reviews..." value="{{ request('search') }}">
                                            <button class="btn btn-outline-primary" type="submit">
                                                <i class="fas fa-search"></i> Search
                                            </button>
                                            <button class="btn btn-outline-secondary" type="button" onclick="resetFilters()">
                                                <i class="fas fa-times"></i> Clear
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Reviews List -->
                    <div class="card">
                        <div class="card-header bg-light">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-list me-2"></i>Reviews ({{ $reviews->total() }})
                                </h5>
                                <div class="btn-group">
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="exportReviews()">
                                        <i class="fas fa-download me-1"></i> Export
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            @if($reviews->count() > 0)
                                <div class="list-group list-group-flush">
                                    @foreach($reviews as $review)
                                        <div class="list-group-item">
                                            <div class="row">
                                                <div class="col-md-2">
                                                    <div class="text-center">
                                                        <div class="rating-display mb-2">
                                                            @for($i = 1; $i <= 5; $i++)
                                                                <i class="fas fa-star {{ $i <= $review->rating ? 'text-warning' : 'text-muted' }}"></i>
                                                            @endfor
                                                        </div>
                                                        <small class="text-muted">{{ $review->rating }}/5</small>
                                                    </div>
                                                </div>
                                                <div class="col-md-8">
                                                    <div class="mb-2">
                                                        <h6 class="mb-1">
                                                            @if($review->work_order)
                                                                <a href="{{ route('portal.work-orders.show', $review->work_order->id) }}" class="text-decoration-none">
                                                                    {{ $review->title }}
                                                                </a>
                                                            @else
                                                                {{ $review->title }}
                                                            @endif
                                                            <span class="badge 
                                                                @if($review->status === 'published') bg-success
                                                                @elseif($review->status === 'pending') bg-warning
                                                                @elseif($review->status === 'draft') bg-secondary
                                                                @else bg-light text-dark @endif ms-2">
                                                                {{ ucfirst($review->status) }}
                                                            </span>
                                                        </h6>
                                                        <p class="mb-2 text-muted small">
                                                            <i class="fas fa-calendar me-1"></i>
                                                            {{ $review->created_at->format('F j, Y') }}
                                                            @if($review->work_order)
                                                                <span class="ms-3">
                                                                    <i class="fas fa-wrench me-1"></i>
                                                                    Work Order #{{ $review->work_order->id }}
                                                                </span>
                                                            @endif
                                                        </p>
                                                    </div>
                                                    <p class="mb-2">
                                                        {{ Str::limit($review->comment, 200) }}
                                                        @if(strlen($review->comment) > 200)
                                                            <a href="{{ route('portal.reviews.show', $review->id) }}" class="text-decoration-none">Read more</a>
                                                        @endif
                                                    </p>
                                                    @if($review->response)
                                                        <div class="alert alert-info mt-2 mb-0 py-2">
                                                            <div class="d-flex">
                                                                <div class="flex-shrink-0">
                                                                    <i class="fas fa-reply text-primary"></i>
                                                                </div>
                                                                <div class="flex-grow-1 ms-3">
                                                                    <strong>Shop Response:</strong>
                                                                    <p class="mb-0 small">{{ $review->response }}</p>
                                                                    @if($review->responded_at)
                                                                        <small class="text-muted">
                                                                            {{ $review->responded_at->format('F j, Y') }}
                                                                        </small>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="col-md-2">
                                                    <div class="d-flex flex-column align-items-end">
                                                        <div class="btn-group btn-group-sm mb-2">
                                                            <a href="{{ route('portal.reviews.show', $review->id) }}" class="btn btn-outline-primary" title="View">
                                                                <i class="fas fa-eye"></i>
                                                            </a>
                                                            @if($review->status === 'draft' || $review->status === 'pending')
                                                                <a href="{{ route('portal.reviews.edit', $review->id) }}" class="btn btn-outline-secondary" title="Edit">
                                                                    <i class="fas fa-edit"></i>
                                                                </a>
                                                            @endif
                                                            @if($review->status === 'draft')
                                                                <button type="button" class="btn btn-outline-danger" onclick="deleteReview({{ $review->id }})" title="Delete">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            @endif
                                                        </div>
                                                        @if($review->helpful_count > 0)
                                                            <small class="text-muted">
                                                                <i class="fas fa-thumbs-up me-1"></i>
                                                                {{ $review->helpful_count }} found helpful
                                                            </small>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <!-- Pagination -->
                                @if($reviews->hasPages())
                                    <div class="card-footer">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                Showing {{ $reviews->firstItem() }} to {{ $reviews->lastItem() }} of {{ $reviews->total() }} reviews
                                            </div>
                                            <div>
                                                {{ $reviews->links() }}
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @else
                                <div class="text-center py-5">
                                    <i class="fas fa-star fa-4x text-muted mb-3"></i>
                                    <h4>No Reviews Found</h4>
                                    <p class="text-muted mb-4">
                                        @if(request()->hasAny(['rating', 'type', 'status', 'search']))
                                            Try adjusting your filters or search criteria
                                        @else
                                            You haven't written any reviews yet
                                        @endif
                                    </p>
                                    <a href="{{ route('portal.reviews.create') }}" class="btn btn-primary">
                                        <i class="fas fa-plus me-1"></i> Write Your First Review
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Pending Reviews -->
                    @if($pendingServices->count() > 0)
                        <div class="card mt-4">
                            <div class="card-header bg-light">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-clock me-2"></i>Pending Reviews
                                    <span class="badge bg-warning ms-2">{{ $pendingServices->count() }}</span>
                                </h5>
                            </div>
                            <div class="card-body">
                                <p class="text-muted mb-3">You can review these completed services:</p>
                                <div class="row">
                                    @foreach($pendingServices as $service)
                                        <div class="col-md-6 mb-3">
                                            <div class="card h-100">
                                                <div class="card-body">
                                                    <div class="d-flex justify-content-between align-items-start">
                                                        <div>
                                                            <h6 class="card-title mb-1">
                                                                @if($service->work_order)
                                                                    Work Order #{{ $service->work_order->id }}
                                                                @else
                                                                    {{ $service->service_type }}
                                                                @endif
                                                            </h6>
                                                            <p class="card-text small text-muted mb-2">
                                                                Completed: {{ $service->completed_at->format('F j, Y') }}
                                                            </p>
                                                            @if($service->vehicle)
                                                                <p class="card-text small mb-0">
                                                                    <i class="fas fa-car me-1"></i>
                                                                    {{ $service->vehicle->year }} {{ $service->vehicle->make }} {{ $service->vehicle->model }}
                                                                </p>
                                                            @endif
                                                        </div>
                                                        <div>
                                                            <a href="{{ route('portal.reviews.create', ['work_order_id' => $service->work_order_id ?? null]) }}" 
                                                               class="btn btn-sm btn-primary">
                                                                <i class="fas fa-star me-1"></i> Review
                                                            </a>
                                                        </