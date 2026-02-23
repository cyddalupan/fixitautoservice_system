@extends('layouts.app')

@section('title', 'Edit Review')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Edit Review</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('portal.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('portal.reviews.index') }}">Reviews</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('portal.reviews.show', $review->id) }}">Review #{{ $review->id }}</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Edit</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('portal.reviews.show', $review->id) }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Cancel
            </a>
        </div>
    </div>

    <!-- Edit Form -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('portal.reviews.update', $review->id) }}" id="editReviewForm" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        <!-- Basic Information -->
                        <div class="mb-4">
                            <h5 class="mb-3">Basic Information</h5>
                            
                            <div class="row">
                                <div class="col-md-8 mb-3">
                                    <label for="title" class="form-label">Review Title *</label>
                                    <input type="text" class="form-control @error('title') is-invalid @enderror" 
                                           id="title" name="title" value="{{ old('title', $review->title) }}" required>
                                    @error('title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">A clear, concise title for your review</div>
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <label for="service_date" class="form-label">Service Date *</label>
                                    <input type="date" class="form-control @error('service_date') is-invalid @enderror" 
                                           id="service_date" name="service_date" value="{{ old('service_date', $review->service_date ? $review->service_date->format('Y-m-d') : '') }}" required>
                                    @error('service_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="service_type" class="form-label">Service Type *</label>
                                    <select class="form-select @error('service_type') is-invalid @enderror" id="service_type" name="service_type" required>
                                        <option value="">Select service type</option>
                                        <option value="oil_change" {{ old('service_type', $review->service_type) === 'oil_change' ? 'selected' : '' }}>Oil Change</option>
                                        <option value="tire_rotation" {{ old('service_type', $review->service_type) === 'tire_rotation' ? 'selected' : '' }}>Tire Rotation</option>
                                        <option value="brake_service" {{ old('service_type', $review->service_type) === 'brake_service' ? 'selected' : '' }}>Brake Service</option>
                                        <option value="engine_repair" {{ old('service_type', $review->service_type) === 'engine_repair' ? 'selected' : '' }}>Engine Repair</option>
                                        <option value="transmission" {{ old('service_type', $review->service_type) === 'transmission' ? 'selected' : '' }}>Transmission Service</option>
                                        <option value="electrical" {{ old('service_type', $review->service_type) === 'electrical' ? 'selected' : '' }}>Electrical Repair</option>
                                        <option value="ac_service" {{ old('service_type', $review->service_type) === 'ac_service' ? 'selected' : '' }}>A/C Service</option>
                                        <option value="diagnostic" {{ old('service_type', $review->service_type) === 'diagnostic' ? 'selected' : '' }}>Diagnostic</option>
                                        <option value="other" {{ old('service_type', $review->service_type) === 'other' ? 'selected' : '' }}>Other</option>
                                    </select>
                                    @error('service_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="service_cost" class="form-label">Service Cost</label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" class="form-control @error('service_cost') is-invalid @enderror" 
                                               id="service_cost" name="service_cost" step="0.01" min="0" 
                                               value="{{ old('service_cost', $review->service_cost) }}">
                                    </div>
                                    @error('service_cost')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">Optional - helps others understand value</div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="service_description" class="form-label">Service Description</label>
                                <textarea class="form-control @error('service_description') is-invalid @enderror" 
                                          id="service_description" name="service_description" rows="3">{{ old('service_description', $review->service_description) }}</textarea>
                                @error('service_description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Brief description of the service performed</div>
                            </div>
                        </div>
                        
                        <!-- Ratings -->
                        <div class="mb-4">
                            <h5 class="mb-3">Ratings</h5>
                            <p class="text-muted mb-3">Rate your experience from 1 (Poor) to 5 (Excellent)</p>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Overall Rating *</label>
                                    <div class="rating-input">
                                        <div class="d-flex gap-1">
                                            @for($i = 5; $i >= 1; $i--)
                                            <input type="radio" id="rating_{{ $i }}" name="rating" value="{{ $i }}" 
                                                   {{ old('rating', $review->rating) == $i ? 'checked' : '' }} required>
                                            <label for="rating_{{ $i }}" class="rating-star">
                                                <i class="bi bi-star{{ old('rating', $review->rating) >= $i ? '-fill' : '' }}"></i>
                                            </label>
                                            @endfor
                                        </div>
                                    </div>
                                    @error('rating')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="quality_rating" class="form-label">Quality of Service</label>
                                    <select class="form-select" id="quality_rating" name="quality_rating">
                                        <option value="">Select rating</option>
                                        @for($i = 1; $i <= 5; $i++)
                                        <option value="{{ $i }}" {{ old('quality_rating', $review->quality_rating) == $i ? 'selected' : '' }}>{{ $i }} - {{ $i === 1 ? 'Poor' : ($i === 2 ? 'Fair' : ($i === 3 ? 'Good' : ($i === 4 ? 'Very Good' : 'Excellent'))) }}</option>
                                        @endfor
                                    </select>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="timeliness_rating" class="form-label">Timeliness</label>
                                    <select class="form-select" id="timeliness_rating" name="timeliness_rating">
                                        <option value="">Select rating</option>
                                        @for($i = 1; $i <= 5; $i++)
                                        <option value="{{ $i }}" {{ old('timeliness_rating', $review->timeliness_rating) == $i ? 'selected' : '' }}>{{ $i }} - {{ $i === 1 ? 'Very Slow' : ($i === 2 ? 'Slow' : ($i === 3 ? 'Average' : ($i === 4 ? 'Fast' : 'Very Fast'))) }}</option>
                                        @endfor
                                    </select>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="communication_rating" class="form-label">Communication</label>
                                    <select class="form-select" id="communication_rating" name="communication_rating">
                                        <option value="">Select rating</option>
                                        @for($i = 1; $i <= 5; $i++)
                                        <option value="{{ $i }}" {{ old('communication_rating', $review->communication_rating) == $i ? 'selected' : '' }}>{{ $i }} - {{ $i === 1 ? 'Poor' : ($i === 2 ? 'Fair' : ($i === 3 ? 'Good' : ($i === 4 ? 'Very Good' : 'Excellent'))) }}</option>
                                        @endfor
                                    </select>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="value_rating" class="form-label">Value for Money</label>
                                    <select class="form-select" id="value_rating" name="value_rating">
                                        <option value="">Select rating</option>
                                        @for($i = 1; $i <= 5; $i++)
                                        <option value="{{ $i }}" {{ old('value_rating', $review->value_rating) == $i ? 'selected' : '' }}>{{ $i }} - {{ $i === 1 ? 'Poor Value' : ($i === 2 ? 'Fair Value' : ($i === 3 ? 'Good Value' : ($i === 4 ? 'Very Good Value' : 'Excellent Value'))) }}</option>
                                        @endfor
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Review Content -->
                        <div class="mb-4">
                            <h5 class="mb-3">Review Content</h5>
                            
                            <div class="mb-3">
                                <label for="content" class="form-label">Your Review *</label>
                                <textarea class="form-control @error('content') is-invalid @enderror" 
                                          id="content" name="content" rows="6" required>{{ old('content', $review->content) }}</textarea>
                                @error('content')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Share your experience in detail. What went well? What could be improved?</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="recommendation" class="form-label">Would you recommend us? *</label>
                                <select class="form-select @error('recommendation') is-invalid @enderror" id="recommendation" name="recommendation" required>
                                    <option value="">Select recommendation</option>
                                    <option value="yes" {{ old('recommendation', $review->recommendation) === 'yes' ? 'selected' : '' }}>Yes, I would recommend</option>
                                    <option value="no" {{ old('recommendation', $review->recommendation) === 'no' ? 'selected' : '' }}>No, I would not recommend</option>
                                    <option value="unsure" {{ old('recommendation', $review->recommendation) === 'unsure' ? 'selected' : '' }}>Not sure / Maybe</option>
                                </select>
                                @error('recommendation')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="mb-3">
                                <label for="recommendation_reason" class="form-label">Reason for your recommendation</label>
                                <textarea class="form-control" id="recommendation_reason" name="recommendation_reason" rows="3">{{ old('recommendation_reason', $review->recommendation_reason) }}</textarea>
                                <div class="form-text">Optional - explain why you would or wouldn't recommend us</div>
                            </div>
                        </div>
                        
                        <!-- Photos -->
                        <div class="mb-4">
                            <h5 class="mb-3">Photos</h5>
                            
                            @if($review->photos && count($review->photos) > 0)
                            <div class="mb-3">
                                <label class="form-label">Current Photos</label>
                                <div class="row g-2 mb-3">
                                    @foreach($review->photos as $index => $photo)
                                    <div class="col-md-3 col-6">
                                        <div class="position-relative">
                                            <img src="{{ $photo['thumbnail_url'] ?? $photo['url'] }}" 
                                                 alt="Review Photo" class="img-fluid rounded border" style="height: 100px; object-fit: cover;">
                                            <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1" 
                                                    onclick="removePhoto({{ $index }})">
                                                <i class="bi bi-x"></i>
                                            </button>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @endif
                            
                            <div class="mb-3">
                                <label for="photos" class="form-label">Add More Photos</label>
                                <input type="file" class="form-control" id="photos" name="photos[]" multiple accept="image/*">
                                <div class="form-text">You can upload up to 5 photos. Max 5MB each. Supported formats: JPG, PNG, GIF</div>
                            </div>
                            
                            <div id="photoPreview" class="row g-2 mb-3"></div>
                        </div>
                        
                        <!-- Tags -->
                        <div class="mb-4">
                            <h5 class="mb-3">Tags</h5>
                            
                            <div class="mb-3">
                                <label class="form-label">Select tags that describe your experience</label>
                                <div class="d-flex flex-wrap gap-2">
                                    @php
                                        $selectedTags = old('tags', $review->tags ?? []);
                                        $allTags = ['friendly_staff', 'professional', 'timely', 'clean_facility', 'good_value', 'quality_work', 'good_communication', 'convenient', 'knowledgeable', 'transparent_pricing'];
                                        $tagLabels = [
                                            'friendly_staff' => 'Friendly Staff',
                                            'professional' => 'Professional',
                                            'timely' => 'Timely',
                                            'clean_facility' => 'Clean Facility',
                                            'good_value' => 'Good Value',
                                            'quality_work' => 'Quality Work',
                                            'good_communication' => 'Good Communication',
                                            'convenient' => 'Convenient',
                                            'knowledgeable' => 'Knowledgeable',
                                            'transparent_pricing' => 'Transparent Pricing'
                                        ];
                                    @endphp
                                    
                                    @foreach($allTags as $tag)
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" id="tag_{{ $tag }}" 
                                               name="tags[]" value="{{ $tag }}" 
                                               {{ in_array($tag, $selectedTags) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="tag_{{ $tag }}">
                                            {{ $tagLabels[$tag] }}
                                        </label>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="custom_tags" class="form-label">Custom Tags (comma separated)</label>
                                <input type="text" class="form-control" id="custom_tags" name="custom_tags" 
                                       value="{{ old('custom_tags', $review->custom_tags ?? '') }}" 
                                       placeholder="e.g., quick service, helpful advice, modern equipment">
                                <div class="form-text">Add your own tags separated by commas</div>
                            </div>
                        </div>
                        
                        <!-- Privacy Settings -->
                        <div class="mb-4">
                            <h5 class="mb-3">Privacy Settings</h5>
                            
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is_public" name="is_public" value="1" 
                                           {{ old('is_public', $review->is_public) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_public">
                                        Make this review public
                                    </label>
                                </div>
                                <div class="form-text">Public reviews are visible to other customers. Private reviews are only visible to you.</div>
                            </div>
                            
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="allow_comments" name="allow_comments" value="1" 
                                           {{ old('allow_comments', $review->allow_comments) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="allow_comments">
                                        Allow comments on this review
                                    </label>
                                </div>
                                <div class="form-text">Allow other users to comment on your review</div>
                            </div>
                        </div>
                        
                        <!-- Submit Buttons -->
                        <div class="d-flex justify-content-between">
                            <div>
                                <button type="submit" name="action" value="save_draft" class="btn btn-outline-secondary">
                                    <i class="bi bi-save me-1"></i> Save as Draft
                                </button>
                                <button type="submit" name="action" value="update" class="btn btn-primary ms-2">
                                    <i class="bi bi-check-circle me-1"></i> Update Review
                                </button>
                            </div>
                            <div>
                                <a href="{{ route('portal.reviews.show', $review->id) }}" class="btn btn-outline-danger">
                                    <i class="bi bi-x-circle me-1"></i> Cancel
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <!-- Help Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Tips for a Great Review</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6 class="text-success"><i class="bi bi-check-circle me-1"></i> Be Specific</h6>
                        <p class="small text-muted">Mention specific services, staff members, or aspects of your experience.</p>
                    </div>
                    
                    <div class="mb-3">
                        <h6 class="text-success"><i class="bi bi-check-circle me-1"></i> Be Balanced</h6>
                        <p class="small text-muted">Share both positive aspects and areas for improvement.</p>
                    </div>
                    
                    <div class="mb-3">
                        <h6 class="text-success"><i class="bi bi-check-circle me-1"></i> Be Helpful</h6>
                        <p class="small text-muted">Your review helps other customers make informed decisions.</p>
                    </div>
                    
                    <div class="mb-3">
                        <h6 class="text-success"><i class="bi bi-check-circle me-1"></i> Add Photos</h6>
                        <p class="small text-muted">Photos make your review more credible and helpful.</p>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        Your honest feedback helps us improve our services.
                    </div>
                </div>
            </div>
            
            <!-- Preview Card -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Preview</h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <div class="display-4 fw-bold text-warning" id="previewRating">0.0</div>
                        <div class="text-muted small">Overall Rating</div>
                    </div>
                    
                    <div class="mb-3">
                        <h6 id="previewTitle" class="text-truncate">Review Title</h6>
                        <p class="small text-muted mb-2" id="previewDate">Today</p>
                        <p class="small" id="previewContent">Your review content will appear here...</p>
                    </div>
                    
                    <div class="d-flex justify-content-between small text-muted">
                        <div>
                            <i class="bi bi-hand-thumbs-up me-1"></i>
                            <span id="previewHelpful">0</span> helpful
                        </div>
                        <div>
                            <i class="bi bi-chat-text me-1"></i>
                            <span id="previewComments">0</span> comments
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    // Rating stars interaction
    document.querySelectorAll('.rating-input input[type="radio"]').forEach(radio => {
        radio.addEventListener('change', function() {
            const rating = this.value;
            document.querySelectorAll('.rating-star i').forEach((star, index) => {
                const starValue = 5 - index;
                if (starValue <= rating) {
                    star.className = 'bi bi-star-fill';
                } else {
                    star.className = 'bi bi-star';
                }
            });
            updatePreview();
        });
    });
    
    // Initialize rating stars
    const initialRating = document.querySelector('.rating-input input[type="radio"]:checked');
    if (initialRating) {
        initialRating.dispatchEvent(new Event('change'));
    }
    
    // Photo preview
    document.getElementById('photos').addEventListener('change', function(e) {
        const preview = document.getElementById('photoPreview');
        preview.innerHTML = '';
        
        const files = e.target.files;
        const maxFiles = 5;
        
        if (files.length > maxFiles) {
            alert(`You can only upload up to ${maxFiles} photos. The first ${maxFiles} will be selected.`);
        }
        
        for (let i = 0; i < Math.min(files.length, maxFiles); i++) {
            const file = files[i];
            const reader = new FileReader();
            
            reader.onload = function(e) {
                const col = document.createElement('div');
                col.className = 'col-md-3 col-6';
                
                col.innerHTML = `
                    <div class="position-relative">
                        <img src="${e.target.result}" alt="Preview" class="img-fluid rounded border" style="height: 100px; object-fit: cover;">
                        <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1" onclick="removePreviewPhoto(this)">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>
                `;
                
                preview.appendChild(col);
            };
            
            reader.readAsDataURL(file);
        }
    });
    
    function removePreviewPhoto(button) {
        const col = button.closest('.col-md-3');
        col.remove();
        
        // Update file input
        const fileInput = document.getElementById('photos');
        const dt = new DataTransfer();
        const files = Array.from(fileInput.files);
        
        const previewImages = document.querySelectorAll('#photoPreview img');
        files.forEach((file, index) => {
            if (index < previewImages.length) {
                dt.items.add(file);
            }
        });
        
        fileInput.files = dt.files;
    }
    
    function removePhoto(index) {
        if (confirm('Are you sure you want to remove this photo?')) {
            // Create hidden input to mark photo for removal
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'remove_photos[]';
            input.value = index;
            document.getElementById('editReviewForm').appendChild(input);
            
            // Remove from UI
            const photoElement = document.querySelector(`[onclick="removePhoto(${index})"]`).closest('.col-md-3');
            photoElement.remove();
        }
    }
    
    // Form validation
    document.getElementById('editReviewForm').addEventListener('submit', function(e) {
        const title = document.getElementById('title').value.trim();
        const content = document.getElementById('content').value.trim();
        const rating = document.querySelector('input[name="rating"]:checked');
        const recommendation = document.getElementById('recommendation').value;
        
        if (!title) {
            e.preventDefault();
            alert('Please enter a review title');
            document.getElementById('title').focus();
            return false;
        }
        
        if (!content) {
            e.preventDefault();
            alert('Please enter your review content');
            document.getElementById('content').focus();
            return false;
        }
        
        if (!rating) {
            e.preventDefault();
            alert('Please select an overall rating');
            return false;
        }
        
        if (!recommendation) {
            e.preventDefault();
            alert('Please select whether you would recommend us');
            document.getElementById('recommendation').focus();
            return false;
        }
        
        // Check file sizes
        const fileInput = document.getElementById('photos');
        const maxSize = 5 * 1024 * 1024; // 5MB
        
        for (let file of fileInput.files) {
            if (file.size > maxSize) {
                e.preventDefault();
                alert(`File "${file.name}" is too large. Maximum size is 5MB.`);
                return false;
            }
        }
    });
    
    // Preview updates
    function updatePreview() {
        const title = document.getElementById('title').value || 'Review Title';
        const content = document.getElementById('content').value || 'Your review content will appear here...';
        const rating = document.querySelector('input[name="rating"]:checked');
        const serviceDate = document.getElementById('service_date').value;
        
        document.getElementById('previewTitle').textContent = title;
        document.getElementById('previewContent').textContent = content.length > 100 ? content.substring(0, 100) + '...' : content;
        
        if (rating) {
            document.getElementById('previewRating').textContent = rating.value + '.0';
            document.getElementById('previewRating').className = `display-4 fw-bold text-${rating.value >= 4 ? 'success' : (rating.value >= 3 ? 'warning' : 'danger')}`;
        }
        
        if (serviceDate) {
            const date = new Date(serviceDate);
            document.getElementById('previewDate').textContent = date.toLocaleDateString('en-US', { 
                month: 'short', 
                day: 'numeric', 
                year: 'numeric' 
            });
        } else {
            document.getElementById('previewDate').textContent = 'Today';
        }
    }
    
    // Add event listeners for preview updates
    document.getElementById('title').addEventListener('input', updatePreview);
    document.getElementById('content').addEventListener('input', updatePreview);
    document.getElementById('service_date').addEventListener('change', updatePreview);
    document.querySelectorAll('input[name="rating"]').forEach(radio => {
        radio.addEventListener('change', updatePreview);
    });
    
    // Initialize preview
    updatePreview();
</script>
@endsection