@extends('layouts.app')

@section('title', 'Create Quality Check')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col">
                <h1 class="h3 mb-0">
                    <i class="fas fa-clipboard-check text-primary me-2"></i>
                    Create Quality Check
                </h1>
                <p class="text-muted mb-0">Create a new quality check for a work order</p>
            </div>
            <div class="col-auto">
                <a href="{{ route('work-order-quality.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back to List
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('work-order-quality.store') }}" enctype="multipart/form-data" id="quality-check-form">
                        @csrf

                        <!-- Work Order Selection -->
                        <div class="mb-4">
                            <h5 class="mb-3">
                                <i class="fas fa-wrench me-2 text-primary"></i>
                                Work Order Information
                            </h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <label for="work_order_id" class="form-label">Work Order *</label>
                                    <select name="work_order_id" id="work_order_id" class="form-select" required>
                                        <option value="">Select Work Order</option>
                                        @foreach($workOrders as $workOrder)
                                            <option value="{{ $workOrder->id }}" 
                                                    {{ old('work_order_id') == $workOrder->id ? 'selected' : '' }}
                                                    data-customer="{{ $workOrder->customer->name ?? 'Unknown' }}"
                                                    data-vehicle="{{ $workOrder->vehicle->make ?? 'Unknown' }} {{ $workOrder->vehicle->model ?? '' }}"
                                                    data-technician="{{ $workOrder->technician->name ?? 'Unknown' }}">
                                                WO-{{ str_pad($workOrder->id, 6, '0', STR_PAD_LEFT) }} - 
                                                {{ $workOrder->customer->name ?? 'Unknown Customer' }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('work_order_id')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <h6 class="card-title">Selected Work Order Details</h6>
                                            <div id="work-order-details">
                                                <p class="text-muted mb-1">Select a work order to see details</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Quality Check Template -->
                        <div class="mb-4">
                            <h5 class="mb-3">
                                <i class="fas fa-clipboard-list me-2 text-primary"></i>
                                Quality Check Template
                            </h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <label for="quality_check_id" class="form-label">Template *</label>
                                    <select name="quality_check_id" id="quality_check_id" class="form-select" required>
                                        <option value="">Select Template</option>
                                        @foreach($qualityTemplates as $template)
                                            <option value="{{ $template->id }}" 
                                                    {{ old('quality_check_id') == $template->id ? 'selected' : '' }}
                                                    data-items="{{ count($template->checklist_items ?? []) }}"
                                                    data-category="{{ $template->category }}">
                                                {{ $template->name }} ({{ count($template->checklist_items ?? []) }} items)
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('quality_check_id')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <h6 class="card-title">Template Details</h6>
                                            <div id="template-details">
                                                <p class="text-muted mb-1">Select a template to see details</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Checklist Items -->
                        <div class="mb-4" id="checklist-section" style="display: none;">
                            <h5 class="mb-3">
                                <i class="fas fa-tasks me-2 text-primary"></i>
                                Checklist Items
                            </h5>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                Rate each item from 1-5 (1=Poor, 5=Excellent). Items marked with * are required.
                            </div>
                            <div id="checklist-items-container">
                                <!-- Dynamic checklist items will be loaded here -->
                            </div>
                        </div>

                        <!-- Photos -->
                        <div class="mb-4">
                            <h5 class="mb-3">
                                <i class="fas fa-camera me-2 text-primary"></i>
                                Photo Documentation
                            </h5>
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Upload photos to document the quality check. Maximum 10 photos, 5MB each.
                            </div>
                            <div class="mb-3">
                                <label for="photos" class="form-label">Upload Photos</label>
                                <input type="file" name="photos[]" id="photos" class="form-control" multiple
                                       accept="image/*" onchange="previewPhotos()">
                                <div class="form-text">Select multiple photos (JPEG, PNG, GIF)</div>
                            </div>
                            <div id="photo-preview" class="row g-2 mb-3"></div>
                        </div>

                        <!-- Notes -->
                        <div class="mb-4">
                            <h5 class="mb-3">
                                <i class="fas fa-sticky-note me-2 text-primary"></i>
                                Additional Notes
                            </h5>
                            <div class="mb-3">
                                <label for="notes" class="form-label">Notes</label>
                                <textarea name="notes" id="notes" class="form-control" rows="4" 
                                          placeholder="Add any additional notes or observations...">{{ old('notes') }}</textarea>
                                <div class="form-text">Optional notes about the quality check</div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="d-flex justify-content-between">
                            <button type="reset" class="btn btn-outline-secondary">
                                <i class="fas fa-redo me-1"></i> Reset
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Submit Quality Check
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Instructions -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        Instructions
                    </h6>
                </div>
                <div class="card-body">
                    <ol class="mb-0">
                        <li class="mb-2">Select a work order to associate with this quality check</li>
                        <li class="mb-2">Choose a quality check template</li>
                        <li class="mb-2">Rate each checklist item (1-5)</li>
                        <li class="mb-2">Upload photos for documentation</li>
                        <li class="mb-2">Add any additional notes</li>
                        <li>Submit for supervisor approval</li>
                    </ol>
                </div>
            </div>

            <!-- Scoring Guide -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="fas fa-star me-2"></i>
                        Scoring Guide
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <span class="badge bg-success">5 - Excellent</span>
                        <small class="text-muted">Exceeds all expectations</small>
                    </div>
                    <div class="mb-2">
                        <span class="badge bg-info">4 - Good</span>
                        <small class="text-muted">Meets all requirements</small>
                    </div>
                    <div class="mb-2">
                        <span class="badge bg-warning">3 - Average</span>
                        <small class="text-muted">Meets basic requirements</small>
                    </div>
                    <div class="mb-2">
                        <span class="badge bg-danger">2 - Poor</span>
                        <small class="text-muted">Needs improvement</small>
                    </div>
                    <div class="mb-2">
                        <span class="badge bg-dark">1 - Unacceptable</span>
                        <small class="text-muted">Does not meet requirements</small>
                    </div>
                </div>
            </div>

            <!-- Preview Score -->
            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="fas fa-chart-line me-2"></i>
                        Score Preview
                    </h6>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <div class="display-4 fw-bold" id="score-preview">0%</div>
                        <div class="text-muted">Current Score</div>
                    </div>
                    <div class="progress mb-3" style="height: 25px;">
                        <div class="progress-bar" id="score-bar" role="progressbar" style="width: 0%"></div>
                    </div>
                    <div id="score-status" class="text-center">
                        <span class="badge bg-secondary">No items rated</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Work Order Details
    document.getElementById('work_order_id').addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const detailsDiv = document.getElementById('work-order-details');
        
        if (selectedOption.value) {
            const customer = selectedOption.getAttribute('data-customer');
            const vehicle = selectedOption.getAttribute('data-vehicle');
            const technician = selectedOption.getAttribute('data-technician');
            
            detailsDiv.innerHTML = `
                <div class="row">
                    <div class="col-12">
                        <p class="mb-1"><strong>Customer:</strong> ${customer}</p>
                        <p class="mb-1"><strong>Vehicle:</strong> ${vehicle}</p>
                        <p class="mb-1"><strong>Technician:</strong> ${technician}</p>
                    </div>
                </div>
            `;
        } else {
            detailsDiv.innerHTML = '<p class="text-muted mb-1">Select a work order to see details</p>';
        }
    });

    // Template Details and Checklist Loading
    document.getElementById('quality_check_id').addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const detailsDiv = document.getElementById('template-details');
        const checklistSection = document.getElementById('checklist-section');
        const checklistContainer = document.getElementById('checklist-items-container');
        
        if (selectedOption.value) {
            const itemsCount = selectedOption.getAttribute('data-items');
            const category = selectedOption.getAttribute('data-category');
            
            detailsDiv.innerHTML = `
                <div class="row">
                    <div class="col-12">
                        <p class="mb-1"><strong>Items:</strong> ${itemsCount}</p>
                        <p class="mb-1"><strong>Category:</strong> ${category}</p>
                    </div>
                </div>
            `;
            
            // Show checklist section
            checklistSection.style.display = 'block';
            
            // Load checklist items (simulated - in real app, this would be an AJAX call)
            loadChecklistItems(selectedOption.value);
        } else {
            detailsDiv.innerHTML = '<p class="text-muted mb-1">Select a template to see details</p>';
            checklistSection.style.display = 'none';
            checklistContainer.innerHTML = '';
        }
    });

    // Simulate loading checklist items
    function loadChecklistItems(templateId) {
        const container = document.getElementById('checklist-items-container');
        
        // In a real application, this would be an AJAX call to fetch the checklist items
        // For now, we'll simulate with some sample items
        const sampleItems = [
            { id: 1, name: 'Workmanship quality', description: 'Check for proper installation and finishing', required: true },
            { id: 2, name: 'Cleanliness', description: 'Area cleaned after work', required: true },
            { id: 3, name: 'Safety compliance', description: 'All safety procedures followed', required: true },
            { id: 4, name: 'Parts installation', description: 'Parts properly installed and secured', required: false },
            { id: 5, name: 'Documentation', description: 'All paperwork completed', required: false },
        ];
        
        let html = '';
        sampleItems.forEach((item, index) => {
            html += `
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h6 class="mb-1">
                                    ${item.name}
                                    ${item.required ? '<span class="text-danger">*</span>' : ''}
                                </h6>
                                <p class="text-muted small mb-2">${item.description}</p>
                            </div>
                            <div class="col-md-4">
                                <div class="btn-group btn-group-sm" role="group">
                                    ${[1,2,3,4,5].map(rating => `
                                        <input type="radio" class="btn-check" name="ratings[${item.id}]" 
                                               id="rating-${item.id}-${rating}" value="${rating}" 
                                               onchange="updateScore()" ${item.required ? 'required' : ''}>
                                        <label class="btn btn-outline-primary" for="rating-${item.id}-${rating}">
                                            ${rating}
                                        </label>
                                    `).join('')}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });
        
        container.innerHTML = html;
        updateScore();
    }

    // Photo Preview
    function previewPhotos() {
        const preview = document.getElementById('photo-preview');
        const files = document.getElementById('photos').files;
        
        preview.innerHTML = '';
        
        if (files.length > 0) {
            for (let i = 0; i < files.length; i++) {
                const file = files[i];
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    const col = document.createElement('div');
                    col.className = 'col-6 col-md-4';
                    col.innerHTML = `
                        <div class="card">
                            <img src="${e.target.result}" class="card-img-top" alt="Preview" style="height: 100px; object-fit: cover;">
                            <div class="card-body p-2">
                                <small class="text-muted">${file.name}</small>
                            </div>
                        </div>
                    `;
                    preview.appendChild(col);
                };
                
                reader.readAsDataURL(file);
            }
        }
    }

    // Score Calculation
    function updateScore() {
        const ratings = document.querySelectorAll('input[name^="ratings"]:checked');
        const totalItems = document.querySelectorAll('input[name^="ratings"]').length / 5; // 5 ratings per item
        const requiredItems = document.querySelectorAll('input[name^="ratings"][required]').length / 5;
        
        if (ratings.length === 0) {
            document.getElementById('score-preview').textContent = '0%';
            document.getElementById('score-bar').style.width = '0%';
            document.getElementById('score-bar').className = 'progress-bar bg-secondary';
            document.getElementById('score-status').innerHTML = '<span class="badge bg-secondary">No items rated</span>';
            return;
        }
        
        let totalScore = 0;
        ratings.forEach(rating => {
            totalScore += parseInt(rating.value);
        });
        
        const maxScore = ratings.length * 5;
        const scorePercentage = Math.round((totalScore / maxScore) * 100);
        
        document.getElementById('score-preview').textContent = scorePercentage + '%';
        document.getElementById('score-bar').style.width = scorePercentage + '%';
        
        // Update color based on score
        let colorClass = 'bg-danger';
        let statusText = 'Needs Improvement';
        let statusClass = 'danger';
        
        if (scorePercentage >= 90) {
            colorClass = 'bg-success';
            statusText = 'Excellent';
            statusClass = 'success';
        } else if (scorePercentage >= 80) {
            colorClass = 'bg-info';
            statusText = 'Good';
            statusClass = 'info';
        } else if (scorePercentage >= 70) {
            colorClass = 'bg-warning';
            statusText = 'Average';
            statusClass = 'warning';
        }
        
        document.getElementById('score-bar').className = 'progress-bar ' + colorClass;
        document.getElementById('score-status').innerHTML = `<span class="badge bg-${statusClass}">${statusText}</span>`;
        
        // Check if all required items are rated
        const requiredRatings = document.querySelectorAll('input[name^="ratings"][required]:checked');
        const allRequiredRated = requiredRatings.length >= requiredItems;
        
        if (!allRequiredRated) {
            document.getElementById('score-status').innerHTML += 
                '<br><small class="text-danger mt-1">Please rate all required items</small>';
        }
    }

    // Form validation
    document.getElementById('quality-check-form').addEventListener('submit', function(e) {
        const requiredRatings = document.querySelectorAll('input[name^="ratings"][required]');
        let allRequiredRated = true;
        
        requiredRatings.forEach(input => {
            const name = input.name;
            const checked = document.querySelector(`input[name="${name}"]:checked`);
            if (!checked) {
                allRequiredRated = false;
                // Highlight the item
                const itemCard = input.closest('.card');
                if (itemCard) {
                    itemCard.classList.add('border-danger');
                }
            }
        });
        
        if (!allRequiredRated) {
            e.preventDefault();
            alert('Please rate all required checklist items before submitting.');
        }
    });

    // Initialize
    document.addEventListener('DOMContentLoaded', function() {
        // Trigger change events if there are old values
        if (document.getElementById('work_order_id').value) {
            document.getElementById('work_order_id').dispatchEvent(new Event('change'));
        }
        if (document.getElementById('quality_check_id').value) {
            document.getElementById('quality_check_id').dispatchEvent(new Event('change'));
        }
    });
</script>
@endpush
@endsection