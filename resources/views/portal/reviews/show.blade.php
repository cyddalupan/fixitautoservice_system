@extends('layouts.app')

@section('title', 'Review Details')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Review Details</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('portal.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('portal.reviews.index') }}">Reviews</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Review #{{ $review->id }}</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('portal.reviews.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Reviews
            </a>
            @if($review->user_id === auth()->id() && $review->status === 'published')
            <a href="{{ route('portal.reviews.edit', $review->id) }}" class="btn btn-outline-primary">
                <i class="bi bi-pencil me-1"></i> Edit Review
            </a>
            @endif
            <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteReviewModal">
                <i class="bi bi-trash me-1"></i> Delete
            </button>
        </div>
    </div>

    <!-- Review Details -->
    <div class="row">
        <div class="col-lg-8">
            <!-- Review Card -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-4">
                        <div>
                            <div class="d-flex align-items-center mb-2">
                                <h4 class="card-title mb-0 me-3">{{ $review->title }}</h4>
                                <span class="badge bg-{{ $review->status === 'published' ? 'success' : ($review->status === 'pending' ? 'warning text-dark' : 'secondary') }}">
                                    {{ ucfirst($review->status) }}
                                </span>
                            </div>
                            <div class="d-flex align-items-center text-muted mb-3">
                                <div class="d-flex align-items-center me-3">
                                    <i class="bi bi-calendar me-1"></i>
                                    <span>{{ $review->created_at->format('M j, Y') }}</span>
                                </div>
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-clock me-1"></i>
                                    <span>{{ $review->created_at->format('g:i A') }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="text-end">
                            <div class="display-4 fw-bold text-{{ $review->rating >= 4 ? 'success' : ($review->rating >= 3 ? 'warning' : 'danger') }}">
                                {{ number_format($review->rating, 1) }}
                            </div>
                            <div class="text-muted small">Overall Rating</div>
                        </div>
                    </div>

                    <!-- Rating Breakdown -->
                    <div class="mb-4">
                        <h6 class="mb-3">Rating Breakdown</h6>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span>Quality of Service</span>
                                    <span class="fw-medium">{{ $review->quality_rating ?? 'N/A' }}/5</span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-success" role="progressbar" 
                                         style="width: {{ ($review->quality_rating ?? 0) * 20 }}%" 
                                         aria-valuenow="{{ $review->quality_rating ?? 0 }}" aria-valuemin="0" aria-valuemax="5"></div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span>Timeliness</span>
                                    <span class="fw-medium">{{ $review->timeliness_rating ?? 'N/A' }}/5</span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-info" role="progressbar" 
                                         style="width: {{ ($review->timeliness_rating ?? 0) * 20 }}%" 
                                         aria-valuenow="{{ $review->timeliness_rating ?? 0 }}" aria-valuemin="0" aria-valuemax="5"></div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span>Communication</span>
                                    <span class="fw-medium">{{ $review->communication_rating ?? 'N/A' }}/5</span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-primary" role="progressbar" 
                                         style="width: {{ ($review->communication_rating ?? 0) * 20 }}%" 
                                         aria-valuenow="{{ $review->communication_rating ?? 0 }}" aria-valuemin="0" aria-valuemax="5"></div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span>Value for Money</span>
                                    <span class="fw-medium">{{ $review->value_rating ?? 'N/A' }}/5</span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-warning" role="progressbar" 
                                         style="width: {{ ($review->value_rating ?? 0) * 20 }}%" 
                                         aria-valuenow="{{ $review->value_rating ?? 0 }}" aria-valuemin="0" aria-valuemax="5"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Review Content -->
                    <div class="mb-4">
                        <h6 class="mb-3">Review Content</h6>
                        <div class="border rounded p-4 bg-light">
                            <p class="mb-0">{{ $review->content }}</p>
                        </div>
                    </div>

                    <!-- Recommendations -->
                    @if($review->recommendation)
                    <div class="mb-4">
                        <h6 class="mb-3">Recommendation</h6>
                        <div class="alert alert-{{ $review->recommendation === 'yes' ? 'success' : ($review->recommendation === 'no' ? 'danger' : 'warning') }}">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-{{ $review->recommendation === 'yes' ? 'check-circle' : ($review->recommendation === 'no' ? 'x-circle' : 'question-circle') }} me-2 fs-4"></i>
                                <div>
                                    <strong class="d-block">
                                        @if($review->recommendation === 'yes')
                                        Would recommend to others
                                        @elseif($review->recommendation === 'no')
                                        Would not recommend to others
                                        @else
                                        Not sure about recommending
                                        @endif
                                    </strong>
                                    @if($review->recommendation_reason)
                                    <p class="mb-0 mt-1">{{ $review->recommendation_reason }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Tags -->
                    @if($review->tags && count($review->tags) > 0)
                    <div class="mb-4">
                        <h6 class="mb-3">Tags</h6>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($review->tags as $tag)
                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25">
                                {{ $tag }}
                            </span>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <!-- Photos -->
                    @if($review->photos && count($review->photos) > 0)
                    <div class="mb-4">
                        <h6 class="mb-3">Photos ({{ count($review->photos) }})</h6>
                        <div class="row g-3">
                            @foreach($review->photos as $photo)
                            <div class="col-md-4 col-6">
                                <a href="{{ $photo['url'] }}" data-fancybox="review-photos" data-caption="{{ $photo['caption'] ?? 'Review Photo' }}">
                                    <img src="{{ $photo['thumbnail_url'] ?? $photo['url'] }}" 
                                         alt="{{ $photo['caption'] ?? 'Review Photo' }}" 
                                         class="img-fluid rounded border" style="height: 150px; object-fit: cover;">
                                </a>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Service Details -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Service Details</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">Service Type</label>
                            <div class="fw-medium">{{ $review->service_type ?? 'N/A' }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">Service Date</label>
                            <div class="fw-medium">{{ $review->service_date ? $review->service_date->format('M j, Y') : 'N/A' }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">Vehicle</label>
                            <div class="fw-medium">{{ $review->vehicle ? $review->vehicle->make . ' ' . $review->vehicle->model . ' (' . $review->vehicle->year . ')' : 'N/A' }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">Service Cost</label>
                            <div class="fw-medium">
                                @if($review->service_cost)
                                ${{ number_format($review->service_cost, 2) }}
                                @else
                                N/A
                                @endif
                            </div>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label text-muted">Service Description</label>
                            <div class="fw-medium">{{ $review->service_description ?? 'N/A' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Status Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Review Status</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label text-muted">Status</label>
                        <div>
                            <span class="badge bg-{{ $review->status === 'published' ? 'success' : ($review->status === 'pending' ? 'warning text-dark' : 'secondary') }} fs-6">
                                {{ ucfirst($review->status) }}
                            </span>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label text-muted">Visibility</label>
                        <div class="fw-medium">
                            @if($review->is_public)
                            <i class="bi bi-globe text-success me-1"></i> Public
                            @else
                            <i class="bi bi-lock text-secondary me-1"></i> Private
                            @endif
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label text-muted">Last Updated</label>
                        <div class="fw-medium">{{ $review->updated_at->format('M j, Y g:i A') }}</div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label text-muted">Helpful Votes</label>
                        <div class="fw-medium">
                            <i class="bi bi-hand-thumbs-up text-success me-1"></i>
                            {{ $review->helpful_count ?? 0 }} found this helpful
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label text-muted">Comments</label>
                        <div class="fw-medium">
                            <i class="bi bi-chat-text text-primary me-1"></i>
                            {{ $review->comments_count ?? 0 }} comments
                        </div>
                    </div>
                    
                    @if($review->published_at)
                    <div class="mb-3">
                        <label class="form-label text-muted">Published Date</label>
                        <div class="fw-medium">{{ $review->published_at->format('M j, Y g:i A') }}</div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Actions Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @if($review->status === 'draft')
                        <form method="POST" action="{{ route('portal.reviews.publish', $review->id) }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-success w-100">
                                <i class="bi bi-send-check me-1"></i> Publish Review
                            </button>
                        </form>
                        @endif
                        
                        @if($review->status === 'published')
                        <a href="{{ route('portal.reviews.share', $review->id) }}" class="btn btn-outline-primary w-100">
                            <i class="bi bi-share me-1"></i> Share Review
                        </a>
                        @endif
                        
                        <a href="{{ route('portal.reviews.edit', $review->id) }}" class="btn btn-outline-secondary w-100">
                            <i class="bi bi-pencil me-1"></i> Edit Review
                        </a>
                        
                        <button type="button" class="btn btn-outline-danger w-100" data-bs-toggle="modal" data-bs-target="#deleteReviewModal">
                            <i class="bi bi-trash me-1"></i> Delete Review
                        </button>
                    </div>
                </div>
            </div>

            <!-- Related Service -->
            @if($review->service_record)
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Related Service</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label text-muted">Service Record</label>
                        <div class="fw-medium">
                            <a href="{{ route('portal.service-requests.show', $review->service_record->id) }}" class="text-decoration-none">
                                #{{ $review->service_record->id }} - {{ $review->service_record->service_type }}
                            </a>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label text-muted">Service Date</label>
                        <div class="fw-medium">{{ $review->service_record->service_date->format('M j, Y') }}</div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label text-muted">Technician</label>
                        <div class="fw-medium">{{ $review->service_record->technician->name ?? 'N/A' }}</div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label text-muted">Status</label>
                        <div>
                            <span class="badge bg-{{ $review->service_record->status === 'completed' ? 'success' : ($review->service_record->status === 'in_progress' ? 'warning text-dark' : 'secondary') }}">
                                {{ ucfirst($review->service_record->status) }}
                            </span>
                        </div>
                    </div>
                    
                    <a href="{{ route('portal.service-requests.show', $review->service_record->id) }}" class="btn btn-outline-primary w-100">
                        <i class="bi bi-eye me-1"></i> View Service Details
                    </a>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Delete Review Modal -->
<div class="modal fade" id="deleteReviewModal" tabindex="-1" aria-labelledby="deleteReviewModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteReviewModalLabel">Delete Review</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('portal.reviews.destroy', $review->id) }}">
                @csrf
                @method('DELETE')
                <div class="modal-body">
                    <div class="text-center mb-4">
                        <i class="bi bi-exclamation-triangle display-1 text-warning mb-3"></i>
                        <h5>Are you sure?</h5>
                        <p class="text-muted">This action cannot be undone. This will permanently delete your review.</p>
                    </div>
                    
                    <div class="alert alert-warning">
                        <i class="bi bi-info-circle me-2"></i>
                        Deleting this review will remove it from your account and it will no longer be visible to others.
                    </div>
                    
                    <div class="mb-3">
                        <label for="delete_reason" class="form-label">Reason for deletion (optional)</label>
                        <textarea class="form-control" id="delete_reason" name="reason" rows="3" 
                                  placeholder="Please provide a reason for deleting this review..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash me-1"></i> Delete Review
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    // Initialize fancybox for photo gallery
    if (typeof Fancybox !== 'undefined') {
        Fancybox.bind("[data-fancybox]", {
            // Options
        });
    }

    // Delete confirmation
    const deleteForm = document.querySelector('form[action*="destroy"]');
    if (deleteForm) {
        deleteForm.addEventListener('submit', function(e) {
            if (!confirm('Are you sure you want to delete this review? This action cannot be undone.')) {
                e.preventDefault();
                return false;
            }
        });
    }

    // Share functionality
    document.querySelectorAll('.share-btn').forEach(button => {
        button.addEventListener('click', function() {
            const platform = this.getAttribute('data-platform');
            const url = window.location.href;
            const title = document.querySelector('h1').textContent;
            
            let shareUrl = '';
            
            switch(platform) {
                case 'facebook':
                    shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}`;
                    break;
                case 'twitter':
                    shareUrl = `https://twitter.com/intent/tweet?url=${encodeURIComponent(url)}&text=${encodeURIComponent(title)}`;
                    break;
                case 'linkedin':
                    shareUrl = `https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent(url)}`;
                    break;
                case 'email':
                    shareUrl = `mailto:?subject=${encodeURIComponent(title)}&body=${encodeURIComponent(`Check out this review: ${url}`)}`;
                    break;
            }
            
            if (shareUrl) {
                window.open(shareUrl, '_blank', 'width=600,height=400');
            }
        });
    });

    // Helpful vote
    const helpfulForm = document.getElementById('helpfulVoteForm');
    if (helpfulForm) {
        helpfulForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const form = this;
            const button = form.querySelector('button');
            const originalText = button.innerHTML;
            
            button.disabled = true;
            button.innerHTML = '<i class="bi bi-hourglass-split me-1"></i> Processing...';
            
            fetch(form.action, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    helpful: document.getElementById('helpful').checked
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    button.innerHTML = '<i class="bi bi-hand-thumbs-up-fill me-1"></i> Thank you for your feedback!';
                    button.classList.remove('btn-outline-success');
                    button.classList.add('btn-success');
                    
                    // Update count
                    const countElement = document.querySelector('.helpful-count');
                    if (countElement) {
                        countElement.textContent = data.new_count;
                    }
                } else {
                    button.innerHTML = originalText;
                    button.disabled = false;
                    alert(data.message || 'An error occurred');
                }
            })
            .catch(error => {
                button.innerHTML = originalText;
                button.disabled = false;
                alert('An error occurred. Please try again.');
            });
        });
    }
</script>
@endsection