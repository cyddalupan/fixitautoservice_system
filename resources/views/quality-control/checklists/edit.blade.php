@extends('layouts.app')

@section('title', 'Edit Quality Control Checklist')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col">
            <h1 class="h3 mb-0">Edit Quality Control Checklist</h1>
            <p class="text-muted">Update checklist details and items</p>
        </div>
        <div class="col-auto">
            <a href="{{ route('quality-control.checklists.show', $checklist->id) }}" class="btn btn-outline-secondary">
                <i class="fas fa-eye"></i> View
            </a>
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
                    <form method="POST" action="{{ route('quality-control.checklists.update', $checklist->id) }}" id="checklistForm">
                        @csrf
                        @method('PUT')

                        <!-- Basic Information -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name" class="font-weight-bold">Checklist Name *</label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name', $checklist->name) }}" 
                                           placeholder="e.g., Brake Service Quality Checklist" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="service_type" class="font-weight-bold">Service Type *</label>
                                    <select class="form-control @error('service_type') is-invalid @enderror" 
                                            id="service_type" name="service_type" required>
                                        <option value="">Select Service Type</option>
                                        <option value="general" {{ old('service_type', $checklist->service_type) == 'general' ? 'selected' : '' }}>General</option>
                                        <option value="brake_service" {{ old('service_type', $checklist->service_type) == 'brake_service' ? 'selected' : '' }}>Brake Service</option>
                                        <option value="oil_change" {{ old('service_type', $checklist->service_type) == 'oil_change' ? 'selected' : '' }}>Oil Change</option>
                                        <option value="tire_service" {{ old('service_type', $checklist->service_type) == 'tire_service' ? 'selected' : '' }}>Tire Service</option>
                                        <option value="engine_diagnostic" {{ old('service_type', $checklist->service_type) == 'engine_diagnostic' ? 'selected' : '' }}>Engine Diagnostic</option>
                                        <option value="electrical" {{ old('service_type', $checklist->service_type) == 'electrical' ? 'selected' : '' }}>Electrical</option>
                                        <option value="ac_service" {{ old('service_type', $checklist->service_type) == 'ac_service' ? 'selected' : '' }}>A/C Service</option>
                                        <option value="transmission" {{ old('service_type', $checklist->service_type) == 'transmission' ? 'selected' : '' }}>Transmission</option>
                                        <option value="suspension" {{ old('service_type', $checklist->service_type) == 'suspension' ? 'selected' : '' }}>Suspension</option>
                                        <option value="alignment" {{ old('service_type', $checklist->service_type) == 'alignment' ? 'selected' : '' }}>Alignment</option>
                                        <option value="inspection" {{ old('service_type', $checklist->service_type) == 'inspection' ? 'selected' : '' }}>Inspection</option>
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
                                      placeholder="Describe the purpose and scope of this checklist">{{ old('description', $checklist->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Scoring Settings -->
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="passing_score" class="font-weight-bold">Passing Score (%) *</label>
                                    <input type="number" class="form-control @error('passing_score') is-invalid @enderror" 
                                           id="passing_score" name="passing_score" value="{{ old('passing_score', $checklist->passing_score) }}" 
                                           min="0" max="100" step="1" required>
                                    @error('passing_score')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="weight" class="font-weight-bold">Weight</label>
                                    <input type="number" class="form-control @error('weight') is-invalid @enderror" 
                                           id="weight" name="weight" value="{{ old('weight', $checklist->weight) }}" 
                                           min="0.1" max="10" step="0.1">
                                    @error('weight')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="time_limit_minutes" class="font-weight-bold">Time Limit (minutes)</label>
                                    <input type="number" class="form-control @error('time_limit_minutes') is-invalid @enderror" 
                                           id="time_limit_minutes" name="time_limit_minutes" value="{{ old('time_limit_minutes', $checklist->time_limit_minutes) }}" 
                                           min="0" step="1">
                                    @error('time_limit_minutes')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Status -->
                        <div class="form-group mb-4">
                            <label class="font-weight-bold">Status</label>
                            <div class="custom-control custom-radio custom-control-inline">
                                <input type="radio" id="status_active" name="status" value="active" 
                                       class="custom-control-input" {{ old('status', $checklist->status) == 'active' ? 'checked' : '' }}>
                                <label class="custom-control-label text-success" for="status_active">
                                    <i class="fas fa-check-circle"></i> Active
                                </label>
                            </div>
                            <div class="custom-control custom-radio custom-control-inline">
                                <input type="radio" id="status_draft" name="status" value="draft" 
                                       class="custom-control-input" {{ old('status', $checklist->status) == 'draft' ? 'checked' : '' }}>
                                <label class="custom-control-label text-warning" for="status_draft">
                                    <i class="fas fa-pencil-alt"></i> Draft
                                </label>
                            </div>
                            <div class="custom-control custom-radio custom-control-inline">
                                <input type="radio" id="status_archived" name="status" value="archived" 
                                       class="custom-control-input" {{ old('status', $checklist->status) == 'archived' ? 'checked' : '' }}>
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
                                <small>Update the items that will be checked during quality audits</small>
                            </div>
                            <div class="card-body">
                                <div id="items-container">
                                    @if($checklist->items && count($checklist->items) > 0)
                                        @foreach($checklist->items as $index => $item)
                                        <div class="card border-secondary mb-3 checklist-item" data-item-id="{{ $item->id }}">
                                            <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center">
                                                <h6 class="m-0 font-weight-bold text-dark">Item {{ $index + 1 }}</h6>
                                                <button type="button" class="btn btn-sm btn-outline-danger remove-item-btn">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                            <div class="card-body">
                                                <input type="hidden" name="items[{{ $index }}][id]" value="{{ $item->id }}">
                                                <div class="row">
                                                    <div class="col-md-8">
                                                        <div class="form-group">
                                                            <label class="font-weight-bold">Description *</label>
                                                            <textarea class="form-control item-description" name="items[{{ $index }}][description]" 
                                                                      rows="2" placeholder="What needs to be checked?" required>{{ old('items.' . $index . '.description', $item->description) }}</textarea>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label class="font-weight-bold">Weight *</label>
                                                            <select class="form-control item-weight" name="items[{{ $index }}][weight]" required>
                                                                <option value="1" {{ $item->weight == 1 ? 'selected' : '' }}>Standard (1.0)</option>
                                                                <option value="1.5" {{ $item->weight == 1.5 ? 'selected' : '' }}>Important (1.5)</option>
                                                                <option value="2" {{ $item->weight == 2 ? 'selected' : '' }}>Critical (2.0)</option>
                                                                <option value="0.5" {{ $item->weight == 0.5 ? 'selected' : '' }}>Minor (0.5)</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label class="font-weight-bold">Standard Reference</label>
                                                            <input type="text" class="form-control item-standard" name="items[{{ $index }}][standard_reference]" 
                                                                   value="{{ old('items.' . $index . '.standard_reference', $item->standard_reference) }}"
                                                                   placeholder="e.g., ISO 9001, OSHA 1910">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label class="font-weight-bold">Acceptance Criteria</label>
                                                            <input type="text" class="form-control item-criteria" name="items[{{ $index }}][acceptance_criteria]" 
                                                                   value="{{ old('items.' . $index . '.acceptance_criteria', $item->acceptance_criteria) }}"
                                                                   placeholder="e.g., No leaks, Proper torque">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <div class="form-group">
                                                            <label class="font-weight-bold">Instructions/Notes</label>
                                                            <textarea class="form-control item-notes" name="items[{{ $index }}][notes]" 
                                                                      rows="2" placeholder="Additional instructions for the auditor">{{ old('items.' . $index . '.notes', $item->notes) }}</textarea>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                    @else
                                        <div class="text-center py-3" id="no-items-message">
                                            <i class="fas fa-list-check fa-2x text-muted mb-3"></i>
                                            <p class="text-muted">No checklist items found. Click "Add Item" below to start.</p>
                                        </div>
                                    @endif
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
                                    <i class="fas fa-save"></i> Update Checklist
                                </button>
                                <button type="button" class="btn btn-outline-secondary" id="preview-btn">
                                    <i class="fas fa-eye"></i> Preview
                                </button>
                                <a href="{{ route('quality-control.checklists.show', $checklist->id) }}" class="btn btn-outline-info">
                                    <i class="fas fa-times"></i> Cancel
                                </a>
                                <button type="button" class="btn btn-outline-danger float-right" onclick="confirmDelete()">
                                    <i class="fas fa-trash"></i> Delete Checklist
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Right Column: Help & Preview -->
        <div class="col-lg-4">
            <!-- Stats Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-info">
                        <i class="fas fa-chart-bar"></i> Checklist Statistics
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6 mb-3">
                            <div class="h4 mb-0">{{ $checklist->items_count ?? 0 }}</div>
                            <small class="text-muted">Total Items</small>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="h4 mb-0">{{ $checklist->audits_count ?? 0 }}</div>
                            <small class="text-muted">Audits Using</small>
                        </div>
                    </div>
                    <hr>
                    <div class="small">
                        <p><strong>Created:</strong> {{ $checklist->created_at->format('M d, Y') }}</p>
                        <p><strong>Last Updated:</strong> {{ $checklist->updated_at->format('M d, Y H:i') }}</p>
                        @if($checklist->last_used_at)
                            <p><strong>Last Used:</strong> {{ $checklist->last_used_at->format('M d, Y') }}</p>
                        @endif
                    </div>
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
                            <p class="text-muted">Preview will appear here as you update the form</p>
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
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="font-weight-bold">Standard Reference</label>
                        <input type="text" class="form-control item-standard" name="items[][standard_reference]" 
                               placeholder="e.g., ISO 9001, OSHA 1910">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="font-weight-bold">Acceptance Criteria</label>
                        <input type="text" class="form-control item-criteria" name="items[][acceptance_criteria]" 
                               placeholder="e.g., No leaks, Proper torque">
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

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirm Deletion</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this checklist?</p>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Warning:</strong> This action cannot be undone. All checklist data and associated audit history will be permanently deleted.
                </div>
                <p class="small text-muted">
                    <i class="fas fa-info-circle"></i>
                    This checklist has been used in {{ $checklist->audits_count ?? 0 }} audits.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <form method="POST" action="{{ route(quality-control.checklists.destroy, $checklist->id) }}" class="d-inline">
                    @csrf
                    @method(DELETE)
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash"></i> Delete Checklist
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push(styles)
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

@push(scripts)
<script>
    document.addEventListener(DOMContentLoaded, function() {
        let itemCount = {{ $checklist->items ? count($checklist->items) : 0 }};
        const itemsContainer = document.getElementById(items-container);
        const noItemsMessage = document.getElementById(no-items-message);
        const itemTemplate = document.getElementById(item-template);
        const addItemBtn = document.getElementById(add-item-btn);
        const previewBtn = document.getElementById(preview-btn);
        const previewContent = document.getElementById(preview-content);
        const form = document.getElementById(checklistForm);

        // Add item functionality
        addItemBtn.addEventListener(click, function() {
            addChecklistItem();
        });

        function addChecklistItem() {
            itemCount++;
            
            // Clone the template
            const newItem = itemTemplate.cloneNode(true);
            newItem.classList.remove(d-none);
            newItem.classList.add(checklist-item);
            
            // Update item number
            const itemNumberSpan = newItem.querySelector(.item-number);
            itemNumberSpan.textContent = itemCount;
            
            // Update input names with index
            const inputs = newItem.querySelectorAll(input, textarea, select);
            inputs.forEach(input => {
                const name = input.getAttribute(name);
                if (name) {
                    input.setAttribute(name, name.replace([], `[${itemCount - 1}]`));
                }
            });
            
            // Add remove functionality
            const removeBtn = newItem.querySelector(.remove-item-btn);
            removeBtn.addEventListener(click, function() {
                newItem.remove();
                itemCount--;
                updateItemNumbers();
                if (itemCount === 0 && noItemsMessage) {
                    noItemsMessage.classList.remove(d-none);
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

        // Remove existing item functionality
        document.querySelectorAll(.remove-item-btn).forEach(btn => {
            btn.addEventListener(click, function() {
                const item = this.closest(.checklist-item);
                item.remove();
                itemCount--;
                updateItemNumbers();
                if (itemCount === 0 && noItemsMessage) {
                    noItemsMessage.classList.remove(d-none);
                }
                updatePreview();
            });
        });

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

        // Delete confirmation
        window.confirmDelete = function() {
            $(#deleteModal).modal(show);
        };

        // Initial preview update
        updatePreview();
    });
</script>
@endpush
