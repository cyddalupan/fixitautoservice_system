@extends('layouts.app')

@section('title', 'Create Quality Control Checklist')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col">
            <h1 class="h3 mb-0">Create Quality Control Checklist</h1>
            <p class="text-muted">Define a new checklist for quality assurance</p>
        </div>
        <div class="col-auto">
            <a href="{{ route('quality-control.checklists.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Back to Checklists
            </a>
        </div>
    </div>

    <!-- Main Form -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Checklist Details</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('quality-control.checklists.store') }}" id="checklistForm">
                        @csrf

                        <!-- Basic Information -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name" class="font-weight-bold">Checklist Name *</label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name') }}" 
                                           placeholder="e.g., Brake Service Quality Checklist" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">Descriptive name for this checklist</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="service_type" class="font-weight-bold">Service Type *</label>
                                    <select class="form-control @error('service_type') is-invalid @enderror" 
                                            id="service_type" name="service_type" required>
                                        <option value="">Select Service Type</option>
                                        <option value="general" {{ old('service_type') == 'general' ? 'selected' : '' }}>General</option>
                                        <option value="brake_service" {{ old('service_type') == 'brake_service' ? 'selected' : '' }}>Brake Service</option>
                                        <option value="oil_change" {{ old('service_type') == 'oil_change' ? 'selected' : '' }}>Oil Change</option>
                                        <option value="tire_service" {{ old('service_type') == 'tire_service' ? 'selected' : '' }}>Tire Service</option>
                                        <option value="engine_diagnostic" {{ old('service_type') == 'engine_diagnostic' ? 'selected' : '' }}>Engine Diagnostic</option>
                                        <option value="electrical" {{ old('service_type') == 'electrical' ? 'selected' : '' }}>Electrical</option>
                                        <option value="ac_service" {{ old('service_type') == 'ac_service' ? 'selected' : '' }}>A/C Service</option>
                                        <option value="transmission" {{ old('service_type') == 'transmission' ? 'selected' : '' }}>Transmission</option>
                                        <option value="suspension" {{ old('service_type') == 'suspension' ? 'selected' : '' }}>Suspension</option>
                                        <option value="alignment" {{ old('service_type') == 'alignment' ? 'selected' : '' }}>Alignment</option>
                                        <option value="inspection" {{ old('service_type') == 'inspection' ? 'selected' : '' }}>Inspection</option>
                                    </select>
                                    @error('service_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="form-group mb-4">
                            <label for="description" class="font-weight-bold">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="3" 
                                      placeholder="Describe the purpose and scope of this checklist">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Optional description to help users understand when to use this checklist</small>
                        </div>

                        <!-- Scoring Settings -->
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="passing_score" class="font-weight-bold">Passing Score (%) *</label>
                                    <input type="number" class="form-control @error('passing_score') is-invalid @enderror" 
                                           id="passing_score" name="passing_score" value="{{ old('passing_score', 80) }}" 
                                           min="0" max="100" step="1" required>
                                    @error('passing_score')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">Minimum score required to pass</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="weight" class="font-weight-bold">Weight</label>
                                    <input type="number" class="form-control @error('weight') is-invalid @enderror" 
                                           id="weight" name="weight" value="{{ old('weight', 1) }}" 
                                           min="0.1" max="10" step="0.1">
                                    @error('weight')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">Relative importance (1.0 = standard)</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="time_limit_minutes" class="font-weight-bold">Time Limit (minutes)</label>
                                    <input type="number" class="form-control @error('time_limit_minutes') is-invalid @enderror" 
                                           id="time_limit_minutes" name="time_limit_minutes" value="{{ old('time_limit_minutes') }}" 
                                           min="0" step="1">
                                    @error('time_limit_minutes')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">Optional time limit for completion</small>
                                </div>
                            </div>
                        </div>

                        <!-- Status -->
                        <div class="form-group mb-4">
                            <label class="font-weight-bold">Status</label>
                            <div class="custom-control custom-radio custom-control-inline">
                                <input type="radio" id="status_active" name="status" value="active" 
                                       class="custom-control-input" {{ old('status', 'active') == 'active' ? 'checked' : '' }}>
                                <label class="custom-control-label text-success" for="status_active">
                                    <i class="fas fa-check-circle"></i> Active
                                </label>
                            </div>
                            <div class="custom-control custom-radio custom-control-inline">
                                <input type="radio" id="status_draft" name="status" value="draft" 
                                       class="custom-control-input" {{ old('status') == 'draft' ? 'checked' : '' }}>
                                <label class="custom-control-label text-warning" for="status_draft">
                                    <i class="fas fa-pencil-alt"></i> Draft
                                </label>
                            </div>
                            <div class="custom-control custom-radio custom-control-inline">
                                <input type="radio" id="status_archived" name="status" value="archived" 
                                       class="custom-control-input" {{ old('status') == 'archived' ? 'checked' : '' }}>
                                <label class="custom-control-label text-secondary" for="status_archived">
                                    <i class="fas fa-archive"></i> Archived
                                </label>
                            </div>
                            @error('status')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Checklist Items Section -->
                        <div class="card border-primary mb-4">
                            <div class="card-header bg-primary text-white py-3">
                                <h6 class="m-0 font-weight-bold">
                                    <i class="fas fa-list-check"></i> Checklist Items
                                </h6>
                                <small>Define the items that will be checked during quality audits</small>
                            </div>
                            <div class="card-body">
                                <div id="items-container">
                                    <!-- Items will be added here dynamically -->
                                    <div class="text-center py-3" id="no-items-message">
                                        <i class="fas fa-list-check fa-2x text-muted mb-3"></i>
                                        <p class="text-muted">No checklist items added yet. Click "Add Item" below to start.</p>
                                    </div>
                                </div>

                                <div class="text-center mt-3">
                                    <button type="button" class="btn btn-outline-primary" id="add-item-btn">
                                        <i class="fas fa-plus"></i> Add Checklist Item
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="row mt-4">
                            <div class="col">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-save"></i> Create Checklist
                                </button>
                                <button type="button" class="btn btn-outline-secondary" id="preview-btn">
                                    <i class="fas fa-eye"></i> Preview
                                </button>
                                <a href="{{ route('quality-control.checklists.index') }}" class="btn btn-outline-danger">
                                    <i class="fas fa-times"></i> Cancel
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Right Column: Help & Preview -->
        <div class="col-lg-4">
            <!-- Help Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-info">
                        <i class="fas fa-question-circle"></i> Creating Effective Checklists
                    </h6>
                </div>
                <div class="card-body">
                    <h6>Best Practices:</h6>
                    <ul class="small">
                        <li><strong>Be Specific:</strong> Each item should check one specific thing</li>
                        <li><strong>Use Clear Language:</strong> Avoid ambiguity in item descriptions</li>
                        <li><strong>Include Standards:</strong> Reference specific quality standards when applicable</li>
                        <li><strong>Consider Weight:</strong> Assign higher weights to critical items</li>
                        <li><strong>Test Thoroughly:</strong> Pilot new checklists before making them active</li>
                    </ul>
                    
                    <hr>
                    
                    <h6>Scoring Guidelines:</h6>
                    <ul class="small">
                        <li><span class="badge badge-success">80-100%</span> Excellent quality</li>
                        <li><span class="badge badge-warning">60-79%</span> Needs improvement</li>
                        <li><span class="badge badge-danger">0-59%</span> Failed quality check</li>
                    </ul>
                </div>
            </div>

            <!-- Preview Card -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-success">
                        <i class="fas fa-eye"></i> Checklist Preview
                    </h6>
                </div>
                <div class="card-body">
                    <div id="preview-content">
                        <div class="text-center py-4">
                            <i class="fas fa-eye-slash fa-2x text-muted mb-3"></i>
                            <p class="text-muted">Preview will appear here as you fill out the form</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Item Template (Hidden) -->
<div id="item-template" class="d-none">
    <div class="card border-secondary mb-3 checklist-item">
        <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-dark">Item <span class="item-number">1</span></h6>
            <button type="button" class="btn btn-sm btn-outline-danger remove-item-btn">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <div class="form-group">
                        <label class="font-weight-bold">Description *</label>
                        <textarea class="form-control item-description" name="items[][description]" 
                                  rows="2" placeholder="What needs to be checked?" required></textarea>
                        <small class="form-text text-muted">Clear, specific description of what to check</small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="font-weight-bold">Weight *</label>
                        <select class="form-control item-weight" name="items[][weight]" required>
                            <option value="1">Standard (1.0)</option>
                            <option value="1.5">Important (1.5)</option>
                            <option value="2">Critical (2.0)</option>
                            <option value="0.5">Minor (0.5)</option>
                        </select>
                        <small class="form-text text-muted">Relative importance</small>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="font-weight-bold">Standard Reference</label>
                        <input type="text" class="form-control item-standard" name="items[][standard_reference]" 
                               placeholder="e.g., ISO 9001, OSHA 1910">
                        <small class="form-text text-muted">Optional reference to standard</small>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="font-weight-bold">Acceptance Criteria</label>
                        <input type="text" class="form-control item-criteria" name="items[][acceptance_criteria]" 
                               placeholder="e.g., No leaks, Proper torque">
                        <small class="form-text text-muted">What constitutes a pass?</small>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label class="font-weight-bold">Instructions/Notes</label>
                        <textarea class="form-control item-notes" name="items[][notes]" 
                                  rows="2" placeholder="Additional instructions for the auditor"></textarea>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .checklist-item {
        transition: all 0.3s ease;
    }
    .checklist-item:hover {
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
    }
    .remove-item-btn {
        opacity: 0.7;
        transition: opacity 0.2s;
    }
    .remove-item-btn:hover {
        opacity: 1;
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        let itemCount = 0;
        const itemsContainer = document.getElementById('items-container');
        const noItemsMessage = document.getElementById('no-items-message');
        const itemTemplate = document.getElementById('item-template');
        const addItemBtn = document.getElementById('add-item-btn');
        const previewBtn = document.getElementById('preview-btn');
        const previewContent = document.getElementById('preview-content');
        const form = document.getElementById('checklistForm');

        // Add item functionality
        addItemBtn.addEventListener('click', function() {
            addChecklistItem();
        });

        function addChecklistItem() {
            itemCount++;
            
            // Clone the template
            const newItem = itemTemplate.cloneNode(true);
            newItem.classList.remove('d-none');
            newItem.classList.add('checklist-item');
            
            // Update item number
            const itemNumberSpan = newItem.querySelector('.item-number');
            itemNumberSpan.textContent = itemCount;
            
            // Update input names with index
            const inputs = newItem.querySelectorAll('input, textarea, select');
            inputs.forEach(input => {
                const name = input.getAttribute('name');
                if (name) {
                    input.setAttribute('name', name.replace('[]', `[${itemCount - 1}]`));
                }
            });
            
            // Add remove functionality
            const removeBtn = newItem.querySelector('.remove-item-btn');
            removeBtn.addEventListener('click', function() {
                newItem.remove();
                itemCount--;
                updateItemNumbers();
                if (itemCount === 0) {
                    noItemsMessage.classList.remove('d-none');
                }
            });
            // Hide no items message
            if (noItemsMessage) {
                noItemsMessage.classList.add(d-none);
            }
            
            // Add to container
            itemsContainer.appendChild(newItem);
            
            // Update preview
            updatePreview();
        }

        function updateItemNumbers() {
            const items = itemsContainer.querySelectorAll(.checklist-item);
            items.forEach((item, index) => {
                const itemNumberSpan = item.querySelector(.item-number);
                if (itemNumberSpan) {
                    itemNumberSpan.textContent = index + 1;
                }
                
                // Update input names with new index
                const inputs = item.querySelectorAll(input, textarea, select);
                inputs.forEach(input => {
                    const name = input.getAttribute(name);
                    if (name) {
                        const match = name.match(/items\[(\d+)\]/);
                        if (match) {
                            input.setAttribute(name, name.replace(/items\[\d+\]/, `items[${index}]`));
                        }
                    }
                });
            });
        }

        // Preview functionality
        previewBtn.addEventListener(click, function() {
            updatePreview();
        });

        function updatePreview() {
            const name = document.getElementById(name).value || Untitled Checklist;
            const serviceType = document.getElementById(service_type).value || general;
            const description = document.getElementById(description).value || No description provided.;
            const passingScore = document.getElementById(passing_score).value || 80;
            const status = document.querySelector(input[name=status]:checked)?.value || active;
            
            const items = [];
            const itemElements = itemsContainer.querySelectorAll(.checklist-item);
            itemElements.forEach(item => {
                const description = item.querySelector(.item-description)?.value || ;
                const weight = item.querySelector(.item-weight)?.value || 1;
                const standard = item.querySelector(.item-standard)?.value || ;
                const criteria = item.querySelector(.item-criteria)?.value || ;
                const notes = item.querySelector(.item-notes)?.value || ;
                
                if (description.trim()) {
                    items.push({
                        description,
                        weight,
                        standard,
                        criteria,
                        notes
                    });
                }
            });

            // Generate preview HTML
            let previewHtml = `
                <div class="preview-checklist">
                    <h5 class="text-primary mb-3">${name}</h5>
                    <div class="mb-3">
                        <span class="badge badge-info">${serviceType.replace(_,  ).toUpperCase()}</span>
                        <span class="badge badge-${status === active ? success : status === draft ? warning : secondary} ml-2">
                            ${status.toUpperCase()}
                        </span>
                    </div>
                    <p class="text-muted mb-3">${description}</p>
                    <div class="alert alert-light">
                        <i class="fas fa-chart-line"></i>
                        <strong>Passing Score:</strong> ${passingScore}%
                        <span class="float-right">
                            <i class="fas fa-list-check"></i>
                            <strong>Items:</strong> ${items.length}
                        </span>
                    </div>
            `;

            if (items.length > 0) {
                previewHtml += `<div class="mt-3"><h6>Checklist Items:</h6><ul class="list-group">`;
                items.forEach((item, index) => {
                    const weightBadge = item.weight === 2 ? danger : item.weight === 1.5 ? warning : info;
                    previewHtml += `
                        <li class="list-group-item">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <strong>${index + 1}. ${item.description}</strong>
                                    ${item.criteria ? `<div class="small text-muted mt-1"><i class="fas fa-check-circle"></i> ${item.criteria}</div>` : }
                                    ${item.notes ? `<div class="small mt-1">${item.notes}</div>` : }
                                </div>
                                <span class="badge badge-${weightBadge}">Weight: ${item.weight}</span>
                            </div>
                            ${item.standard ? `<div class="small text-info mt-1"><i class="fas fa-book"></i> ${item.standard}</div>` : }
                        </li>
                    `;
                });
                previewHtml += `</ul></div>`;
            } else {
                previewHtml += `<div class="alert alert-warning mt-3">
                    <i class="fas fa-exclamation-triangle"></i>
                    No checklist items added yet.
                </div>`;
            }

            previewHtml += `</div>`;
            previewContent.innerHTML = previewHtml;
        }

        // Form validation
        form.addEventListener(submit, function(e) {
            if (itemCount === 0) {
                e.preventDefault();
                alert(Please add at least one checklist item.);
                return false;
            }
            
            // Validate that all items have descriptions
            const items = itemsContainer.querySelectorAll(.checklist-item);
            let valid = true;
            items.forEach(item => {
                const description = item.querySelector(.item-description)?.value.trim();
                if (!description) {
                    valid = false;
                    item.querySelector(.item-description).classList.add(is-invalid);
                }
            });
            
            if (!valid) {
                e.preventDefault();
                alert(Please fill in all required item descriptions.);
                return false;
            }
            
            return true;
        });

        // Auto-update preview on form changes
        const formInputs = form.querySelectorAll(input, textarea, select);
        formInputs.forEach(input => {
            input.addEventListener(input, updatePreview);
            input.addEventListener(change, updatePreview);
        });

        // Add first item automatically
        addChecklistItem();
    });
</script>
@endpush
