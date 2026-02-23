@extends('layouts.app')

@section('title', 'Edit Service Request - Customer Portal')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="card-title">
                                <i class="fas fa-edit me-2"></i>Edit Service Request
                            </h3>
                            <p class="card-subtitle text-muted mb-0">SR-{{ str_pad($serviceRequest->id, 6, '0', STR_PAD_LEFT) }}</p>
                        </div>
                        <div>
                            <a href="{{ route('portal.service-requests.show', $serviceRequest->id) }}" class="btn btn-sm btn-outline-secondary me-2">
                                <i class="fas fa-eye me-1"></i> View
                            </a>
                            <a href="{{ route('portal.service-requests.index') }}" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Back to Requests
                            </a>
                        </div>
                    </div>
                </div>
                <form action="{{ route('portal.service-requests.update', $serviceRequest->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <!-- Status Alert -->
                        @if($serviceRequest->status !== 'pending')
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>Note:</strong> This request is currently <strong>{{ ucfirst($serviceRequest->status) }}</strong>. 
                                Some fields may be locked for editing.
                            </div>
                        @endif

                        <div class="row">
                            <!-- Left Column: Editable Fields -->
                            <div class="col-md-8">
                                <div class="card mb-4">
                                    <div class="card-header bg-light">
                                        <h4 class="card-title mb-0">
                                            <i class="fas fa-edit me-2"></i>Edit Request Details
                                        </h4>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-8">
                                                <div class="mb-3">
                                                    <label for="subject" class="form-label">Subject *</label>
                                                    <input type="text" class="form-control @error('subject') is-invalid @enderror" 
                                                           id="subject" name="subject" 
                                                           value="{{ old('subject', $serviceRequest->subject) }}" 
                                                           {{ $serviceRequest->status !== 'pending' ? 'readonly' : '' }} required>
                                                    @error('subject')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label for="priority" class="form-label">Priority *</label>
                                                    <select class="form-select @error('priority') is-invalid @enderror" 
                                                            id="priority" name="priority" 
                                                            {{ $serviceRequest->status !== 'pending' ? 'disabled' : '' }} required>
                                                        <option value="low" {{ old('priority', $serviceRequest->priority) == 'low' ? 'selected' : '' }}>Low</option>
                                                        <option value="medium" {{ old('priority', $serviceRequest->priority) == 'medium' ? 'selected' : '' }}>Medium</option>
                                                        <option value="high" {{ old('priority', $serviceRequest->priority) == 'high' ? 'selected' : '' }}>High</option>
                                                        <option value="urgent" {{ old('priority', $serviceRequest->priority) == 'urgent' ? 'selected' : '' }}>Urgent</option>
                                                    </select>
                                                    @error('priority')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="description" class="form-label">Description *</label>
                                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                                      id="description" name="description" rows="5"
                                                      {{ $serviceRequest->status !== 'pending' ? 'readonly' : '' }} required>{{ old('description', $serviceRequest->description) }}</textarea>
                                            @error('description')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <div class="mt-2">
                                                <small class="text-muted">
                                                    <span id="charCount">{{ strlen($serviceRequest->description) }}</span> characters
                                                </small>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="preferred_date" class="form-label">Preferred Date</label>
                                                    <input type="date" class="form-control" 
                                                           id="preferred_date" name="preferred_date"
                                                           min="{{ date('Y-m-d') }}" 
                                                           value="{{ old('preferred_date', $serviceRequest->preferred_date) }}"
                                                           {{ $serviceRequest->status !== 'pending' ? 'readonly' : '' }}>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="preferred_time" class="form-label">Preferred Time</label>
                                                    <select class="form-select" id="preferred_time" name="preferred_time"
                                                            {{ $serviceRequest->status !== 'pending' ? 'disabled' : '' }}>
                                                        <option value="">Any time</option>
                                                        <option value="morning" {{ old('preferred_time', $serviceRequest->preferred_time) == 'morning' ? 'selected' : '' }}>Morning</option>
                                                        <option value="afternoon" {{ old('preferred_time', $serviceRequest->preferred_time) == 'afternoon' ? 'selected' : '' }}>Afternoon</option>
                                                        <option value="evening" {{ old('preferred_time', $serviceRequest->preferred_time) == 'evening' ? 'selected' : '' }}>Evening</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="estimated_budget" class="form-label">Estimated Budget</label>
                                            <div class="input-group">
                                                <span class="input-group-text">$</span>
                                                <input type="number" class="form-control" 
                                                       id="estimated_budget" name="estimated_budget" 
                                                       min="0" step="0.01" placeholder="0.00"
                                                       value="{{ old('estimated_budget', $serviceRequest->estimated_budget) }}"
                                                       {{ $serviceRequest->status !== 'pending' ? 'readonly' : '' }}>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="special_instructions" class="form-label">Special Instructions</label>
                                            <textarea class="form-control" id="special_instructions" 
                                                      name="special_instructions" rows="3"
                                                      {{ $serviceRequest->status !== 'pending' ? 'readonly' : '' }}>{{ old('special_instructions', $serviceRequest->special_instructions) }}</textarea>
                                        </div>
                                    </div>
                                </div>

                                <!-- Attachments Section -->
                                <div class="card mb-4">
                                    <div class="card-header bg-light">
                                        <h4 class="card-title mb-0">
                                            <i class="fas fa-paperclip me-2"></i>Attachments
                                            <small class="text-muted">({{ $serviceRequest->attachments_count }} files)</small>
                                        </h4>
                                    </div>
                                    <div class="card-body">
                                        @if($serviceRequest->attachments_count > 0)
                                            <div class="mb-3">
                                                <h6>Current Attachments:</h6>
                                                <div class="list-group">
                                                    @foreach($serviceRequest->attachments as $attachment)
                                                        <div class="list-group-item">
                                                            <div class="d-flex justify-content-between align-items-center">
                                                                <div>
                                                                    <i class="fas fa-file me-2 text-primary"></i>
                                                                    <span>{{ $attachment['filename'] }}</span>
                                                                    <small class="text-muted ms-2">{{ $attachment['size_formatted'] }}</small>
                                                                </div>
                                                                <div>
                                                                    <a href="{{ route('portal.service-requests.download-attachment', ['serviceRequest' => $serviceRequest->id, 'attachment' => $loop->index]) }}" 
                                                                       class="btn btn-sm btn-outline-primary me-1">
                                                                        <i class="fas fa-download"></i>
                                                                    </a>
                                                                    @if($serviceRequest->status === 'pending')
                                                                        <button type="button" class="btn btn-sm btn-outline-danger" 
                                                                                onclick="removeAttachment({{ $loop->index }})">
                                                                            <i class="fas fa-times"></i>
                                                                        </button>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif

                                        @if($serviceRequest->status === 'pending')
                                            <div class="mb-3">
                                                <label for="new_attachments" class="form-label">Add New Attachments</label>
                                                <input type="file" class="form-control" 
                                                       id="new_attachments" name="new_attachments[]" 
                                                       multiple accept="image/*,.pdf,.doc,.docx,.xls,.xlsx">
                                                <div class="form-text">
                                                    Max file size: 10MB each. Supported formats: Images, PDF, Word, Excel
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label for="attachment_notes" class="form-label">Attachment Notes</label>
                                                <textarea class="form-control" id="attachment_notes" 
                                                          name="attachment_notes" rows="2">{{ old('attachment_notes', $serviceRequest->attachment_notes) }}</textarea>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Right Column: Read-only Information -->
                            <div class="col-md-4">
                                <div class="card mb-4">
                                    <div class="card-header bg-light">
                                        <h4 class="card-title mb-0">
                                            <i class="fas fa-info-circle me-2"></i>Request Information
                                        </h4>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <h6>Status</h6>
                                            <span class="badge 
                                                @if($serviceRequest->status === 'pending') bg-warning
                                                @elseif($serviceRequest->status === 'in_progress') bg-info
                                                @elseif($serviceRequest->status === 'completed') bg-success
                                                @elseif($serviceRequest->status === 'cancelled') bg-danger
                                                @else bg-secondary @endif">
                                                {{ ucfirst($serviceRequest->status) }}
                                            </span>
                                        </div>

                                        <div class="mb-3">
                                            <h6>Request ID</h6>
                                            <p class="mb-0">SR-{{ str_pad($serviceRequest->id, 6, '0', STR_PAD_LEFT) }}</p>
                                        </div>

                                        <div class="mb-3">
                                            <h6>Created</h6>
                                            <p class="mb-0">{{ $serviceRequest->created_at->format('F j, Y g:i A') }}</p>
                                        </div>

                                        <div class="mb-3">
                                            <h6>Last Updated</h6>
                                            <p class="mb-0">{{ $serviceRequest->updated_at->format('F j, Y g:i A') }}</p>
                                        </div>

                                        @if($serviceRequest->vehicle)
                                            <div class="mb-3">
                                                <h6>Vehicle</h6>
                                                <div class="p-2 border rounded bg-light">
                                                    <p class="mb-1"><strong>{{ $serviceRequest->vehicle->year }} {{ $serviceRequest->vehicle->make }} {{ $serviceRequest->vehicle->model }}</strong></p>
                                                    <p class="mb-0 text-muted small">License: {{ $serviceRequest->vehicle->license_plate }}</p>
                                                    <p class="mb-0 text-muted small">VIN: {{ $serviceRequest->vehicle->vin }}</p>
                                                </div>
                                            </div>
                                        @endif

                                        @if($serviceRequest->service_categories)
                                            <div class="mb-3">
                                                <h6>Service Categories</h6>
                                                <div class="d-flex flex-wrap gap-1">
                                                    @foreach($serviceRequest->service_categories as $category)
                                                        <span class="badge bg-info">{{ $category }}</span>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif

                                        @if($serviceRequest->assigned_to)
                                            <div class="mb-3">
                                                <h6>Assigned To</h6>
                                                <p class="mb-0">{{ $serviceRequest->assigned_to }}</p>
                                            </div>
                                        @endif

                                        @if($serviceRequest->estimated_completion_date)
                                            <div class="mb-3">
                                                <h6>Estimated Completion</h6>
                                                <p class="mb-0">{{ $serviceRequest->estimated_completion_date->format('F j, Y') }}</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <!-- Quick Actions -->
                                <div class="card">
                                    <div class="card-header bg-light">
                                        <h4 class="card-title mb-0">
                                            <i class="fas fa-bolt me-2"></i>Quick Actions
                                        </h4>
                                    </div>
                                    <div class="card-body">
                                        <div class="d-grid gap-2">
                                            @if($serviceRequest->status === 'pending')
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="fas fa-save me-2"></i> Save Changes
                                                </button>
                                                <button type="button" class="btn btn-outline-danger" onclick="cancelRequest()">
                                                    <i class="fas fa-times me-2"></i> Cancel Request
                                                </button>
                                            @endif
                                            <a href="{{ route('portal.service-requests.show', $serviceRequest->id) }}" class="btn btn-outline-secondary">
                                                <i class="fas fa-eye me-2"></i> View Request
                                            </a>
                                            <button type="button" class="btn btn-outline-info" onclick="printRequest()">
                                                <i class="fas fa-print me-2"></i> Print Request
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="d-flex justify-content-between">
                            <div>
                                <small class="text-muted">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Last updated: {{ $serviceRequest->updated_at->format('F j, Y g:i A') }}
                                </small>
                            </div>
                            <div>
                                @if($serviceRequest->status === 'pending')
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i> Save Changes
                                    </button>
                                @else
                                    <div class="alert alert-warning mb-0 py-1 px-3">
                                        <i class="fas fa-lock me-1"></i> Request is locked for editing
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Character counter for description
        $('#description').on('input', function() {
            const length = $(this).val().length;
            $('#charCount').text(length);
        });

        // Enable/disable fields based on status
        if('{{ $serviceRequest->status }}' !== 'pending') {
            // Disable all form fields
            $('input, textarea, select').prop('disabled', true);
            $('button[type="submit"]').prop('disabled', true);
        }
    });

    function removeAttachment(index) {
        if(confirm('Are you sure you want to remove this attachment?')) {
            // Add hidden input to mark attachment for removal
            $('<input>').attr({
                type: 'hidden',
                name: 'remove_attachments[]',
                value: index
            }).appendTo('form');
            
            // Remove from UI
            $(`[onclick="removeAttachment(${index})"]`).closest('.list-group-item').remove();
            
            showToast('Attachment marked for removal', 'info');
        }
    }

    function cancelRequest() {
        if(confirm('Are you sure you want to cancel this service request? This action cannot be undone.')) {
            window.location.href = '{{ route("portal.service-requests.cancel", $serviceRequest->id) }}';
        }
    }

    function printRequest() {
        window.open('{{ route("portal.service-requests.print", $serviceRequest->id) }}', '_blank');
    }

    function showToast(message, type = 'info') {
        // Remove existing toast
        $('.toast').remove();
        
        const toast = $(`
            <div class="toast align-items-center text-white bg-${type === 'info' ? 'info' : 'success'} border-0" 
                 role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="