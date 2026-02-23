@extends('layouts.app')

@section('title', 'Write a Review - Customer Portal')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-star me-2"></i>Write a Review
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('portal.reviews.index') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Back to Reviews
                        </a>
                    </div>
                </div>
                <form action="{{ route('portal.reviews.store') }}" method="POST" id="reviewForm">
                    @csrf
                    <div class="card-body">
                        <!-- Service Selection -->
                        @if($workOrder)
                            <div class="card border-primary mb-4">
                                <div class="card-header bg-primary text-white">
                                    <h4 class="card-title mb-0">
                                        <i class="fas fa-wrench me-2"></i>Reviewing Service
                                    </h4>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-8">
                                            <h5>Work Order #{{ $workOrder->id }}</h5>
                                            <p class="text-muted mb-2">
                                                Completed: {{ $workOrder->completed_at->format('F j, Y') }}
                                            </p>
                                            @if($workOrder->vehicle)
                                                <p class="mb-2">
                                                    <i class="fas fa-car me-2"></i>
                                                    {{ $workOrder->vehicle->year }} {{ $workOrder->vehicle->make }} {{ $workOrder->vehicle->model }}
                                                </p>
                                            @endif
                                            @if($workOrder->services)
                                                <p class="mb-0">
                                                    <strong>Services Performed:</strong>
                                                    {{ implode(', ', $workOrder->services) }}
                                                </p>
                                            @endif
                                        </div>
                                        <div class="col-md-4 text-end">
                                            <div class="card bg-light">
                                                <div class="card-body">
                                                    <h6 class="card-title">Invoice Total</h6>
                                                    <h4 class="text-primary">${{ number_format($workOrder->total_amount ?? 0, 2) }}</h4>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <input type="hidden" name="work_order_id" value="{{ $workOrder->id }}">
                                </div>
                            </div>
                        @else
                            <div class="card border-info mb-4">
                                <div class="card-header bg-info text-white">
                                    <h4 class="card-title mb-0">
                                        <i class="fas fa-info-circle me-2"></i>What are you reviewing?
                                    </h4>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="review_type" class="form-label">Review Type *</label>
                                                <select class="form-select @error('review_type') is-invalid @enderror" 
                                                        id="review_type" name="review_type" required>
                                                    <option value="">Select type...</option>
                                                    <option value="service" {{ old('review_type') == 'service' ? 'selected' : '' }}>Service Experience</option>
                                                    <option value="product" {{ old('review_type') == 'product' ? 'selected' : '' }}>Product/Part</option>
                                                    <option value="general" {{ old('review_type') == 'general' ? 'selected' : '' }}>General Experience</option>
                                                </select>
                                                @error('review_type')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="related_service" class="form-label">Related Service (Optional)</label>
                                                <select class="form-select" id="related_service" name="related_service">
                                                    <option value="">Select a service...</option>
                                                    @foreach($recentServices as $service)
                                                        <option value="{{ $service->id }}" {{ old('related_service') == $service->id ? 'selected' : '' }}>
                                                            {{ $service->service_type }} - {{ $service->completed_at->format('M j, Y') }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Rating Section -->
                        <div class="card border-warning mb-4">
                            <div class="card-header bg-warning text-white">
                                <h4 class="card-title mb-0">
                                    <i class="fas fa-star me-2"></i>Your Rating
                                </h4>
                            </div>
                            <div class="card-body">
                                <div class="text-center mb-4">
                                    <div class="rating-input mb-3">
                                        @for($i = 1; $i <= 5; $i++)
                                            <i class="fas fa-star fa-3x rating-star" data-rating="{{ $i }}"></i>
                                        @endfor
                                    </div>
                                    <input type="hidden" name="rating" id="rating" value="{{ old('rating', 0) }}" required>
                                    <h4 id="ratingText">Select a rating</h4>
                                    <p class="text-muted" id="ratingDescription">Click on the stars to rate your experience</p>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="card">
                                            <div class="card-body">
                                                <h6 class="card-title">Rating Guidelines</h6>
                                                <ul class="small mb-0">
                                                    <li><strong>5 Stars:</strong> Excellent service, exceeded expectations</li>
                                                    <li><strong>4 Stars:</strong> Very good service, met expectations</li>
                                                    <li><strong>3 Stars:</strong> Average service, room for improvement</li>
                                                    <li><strong>2 Stars:</strong> Below average, needs significant improvement</li>
                                                    <li><strong>1 Star:</strong> Poor service, did not meet basic standards</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card">
                                            <div class="card-body">
                                                <h6 class="card-title">What makes a good review?</h6>
                                                <ul class="small mb-0">
                                                    <li>Be specific about what you liked or didn't like</li>
                                                    <li>Mention specific staff members if appropriate</li>
                                                    <li>Focus on facts rather than emotions</li>
                                                    <li>Suggest improvements constructively</li>
                                                    <li>Keep it honest and fair</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @error('rating')
                                    <div class="text-danger mt-2">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Review Details -->
                        <div class="card border-success mb-4">
                            <div class="card-header bg-success text-white">
                                <h4 class="card-title mb-0">
                                    <i class="fas fa-edit me-2"></i>Review Details
                                </h4>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="title" class="form-label">Review Title *</label>
                                    <input type="text" class="form-control @error('title') is-invalid @enderror" 
                                           id="title" name="title" value="{{ old('title') }}" 
                                           placeholder="Brief summary of your review..." required>
                                    @error('title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">
                                        Keep it short and descriptive (e.g., "Great brake service", "Friendly staff")
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="comment" class="form-label">Detailed Review *</label>
                                    <textarea class="form-control @error('comment') is-invalid @enderror" 
                                              id="comment" name="comment" rows="8" 
                                              placeholder="Share your experience in detail..." required>{{ old('comment') }}</textarea>
                                    @error('comment')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">
                                        Tell us about your experience. What went well? What could be improved?
                                    </div>
                                    <div class="mt-2">
                                        <small class="text-muted">
                                            <span id="charCount">0</span> characters (minimum 50 recommended)
                                        </small>
                                    </div>
                                </div>

                                <!-- Category Ratings -->
                                <div class="mb-4">
                                    <h6>Rate Specific Aspects (Optional)</h6>
                                    <div class="row">
                                        @foreach($ratingCategories as $category)
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">{{ $category['name'] }}</label>
                                                <div class="category-rating">
                                                    @for($i = 1; $i <= 5; $i++)
                                                        <i class="fas fa-star category-star" 
                                                           data-category="{{ $category['id'] }}" 
                                                           data-rating="{{ $i }}"></i>
                                                    @endfor
                                                    <input type="hidden" name="category_ratings[{{ $category['id'] }}]" 
                                                           id="category_{{ $category['id'] }}" 
                                                           value="{{ old('category_ratings.' . $category['id'], 0) }}">
                                                </div>
                                                <small class="text-muted">{{ $category['description'] }}</small>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                <!-- Recommendations -->
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="would_recommend" name="would_recommend" value="1" 
                                               {{ old('would_recommend') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="would_recommend">
                                            <i class="fas fa-thumbs-up me-1"></i>
                                            I would recommend Fix-It Auto Services to others
                                        </label>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="would_return" name="would_return" value="1"
                                               {{ old('would_return') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="would_return">
                                            <i class="fas fa-redo me-1"></i>
                                            I would return for future services
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Privacy & Submission -->
                        <div class="card border-info mb-4">
                            <div class="card-header bg-info text-white">
                                <h4 class="card-title mb-0">
                                    <i class="fas fa-user-shield me-2"></i>Privacy & Submission
                                </h4>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="display_name" class="form-label">Display Name</label>
                                            <input type="text" class="form-control" 
                                                   id="display_name" name="display_name" 
                                                   value="{{ old('display_name', $customer->first_name) }}"
                                                   placeholder="How you want to appear in the review">
                                            <div class="form-text">
                                                Leave blank to use "{{ $customer->first_name }}"
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="status" class="form-label">Review Status</label>
                                            <select class="form-select" id="status" name="status">
                                                <option value="published" {{ old('status') == 'published' ? 'selected' : '' }}>Publish Immediately</option>
                                                <option value="draft" {{ old('status') == 'draft' ? 'selected' : '' }}>Save as Draft</option>
                                            </select>
                                            <div class="form-text">
                                                Drafts can be edited and published later
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="allow_response" name="allow_response" value="1" checked>
                                        <label class="form-check-label" for="allow_response">
                                            Allow the shop to respond to this review
                                        </label>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="terms" name="terms" value="1" required>
                                        <label class="form-check-label" for="terms">
                                            I agree to the <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal">Review Guidelines</a> *
                                        </label>
                                        @error('terms')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <strong>Important:</strong> Reviews are moderated. We reserve the right to remove reviews that violate our guidelines.
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="d-flex justify-content-between">
                            <div>
                                <button type="button" class="btn btn-outline-secondary" onclick="saveDraft()">
                                    <i class="fas fa-save me-1"></i> Save Draft
                                </button>
                            </div>
                            <div>
                                <button type="reset" class="btn btn-outline-danger me-2">
                                    <i class="fas fa-times me-1"></i> Clear Form
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-paper-plane me-1"></i> Submit Review
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Tips Card -->
            <div class="card mt-4">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-lightbulb me-2"></i>Writing Tips
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6><i class="fas fa-check-circle text-success me-2"></i>Do:</h6>
                            <ul class="small">
                                <li>Be specific about your experience</li>
                                <li>Mention staff members by name if they were helpful</li>
                                <li>Describe the quality of work performed</li>
                                <li>Note the cleanliness and professionalism</li>
                                <li>Share how the service met your expectations</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6><i class="fas fa-times-circle text-danger me-2"></i>Don't:</h6>
                            <ul class="small">
                                <li>Use offensive or inappropriate language</li>
                                <li>Include personal contact information</li>
                                <li>Make false or misleading statements</li>
                                <li>Review services you didn't receive</li>
                                <li>Include pricing complaints (contact us directly)</li>
                            </ul>
                        </div>
                    </div>
                    <div class="alert alert-info mt-3">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Note:</strong> Your review helps us improve our services and helps other customers make informed decisions.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Terms Modal -->
<div class="modal fade" id="termsModal" tabindex="-1" aria-labelledby="termsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="termsModalLabel">
                    <i class="fas fa-file-contract me-2"></i>Review Guidelines
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h6>By submitting a review, you agree to:</h6>
                <ol class="small">
                    <li>Provide honest and accurate feedback based on your personal experience</li>
                    <li>Not include any personal or confidential information</li>
                    <li>Not use offensive, discriminatory, or inappropriate language</li>
                    <li>Not make false or misleading statements</li>
                    <li>Allow Fix-It Auto Services to publish your review on our website and marketing materials</li>
                    <li>Allow Fix-It Auto Services to respond to your review publicly</li>
                    <li>Understand that reviews are moderated and may be removed if they violate these guidelines</li>
                </ol>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Reviews containing pricing complaints or specific financial details will be redirected to our customer service team for resolution.
                </div