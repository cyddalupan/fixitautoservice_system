@extends('layouts.app')

@section('title', 'New Message - Customer Portal')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-envelope me-2"></i>New Message
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('portal.messages.index') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Back to Messages
                        </a>
                    </div>
                </div>
                <form action="{{ route('portal.messages.send') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Messages are typically answered within 1-2 business days. For urgent matters, please call the shop directly.
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="message_type" class="form-label">
                                        <i class="fas fa-tag me-1"></i>Message Type *
                                    </label>
                                    <select class="form-select @error('message_type') is-invalid @enderror" 
                                            id="message_type" name="message_type" required>
                                        <option value="">Select a message type...</option>
                                        <option value="appointment_request" {{ old('message_type') == 'appointment_request' ? 'selected' : '' }}>
                                            Appointment Request
                                        </option>
                                        <option value="service_inquiry" {{ old('message_type') == 'service_inquiry' ? 'selected' : '' }}>
                                            Service Inquiry
                                        </option>
                                        <option value="question" {{ old('message_type') == 'question' ? 'selected' : '' }}>
                                            General Question
                                        </option>
                                        <option value="general" {{ old('message_type') == 'general' ? 'selected' : '' }}>
                                            General Message
                                        </option>
                                    </select>
                                    @error('message_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">
                                        Select the most appropriate category for your message.
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="related_vehicle" class="form-label">
                                        <i class="fas fa-car me-1"></i>Related Vehicle (Optional)
                                    </label>
                                    <select class="form-select" id="related_vehicle" name="vehicle_id">
                                        <option value="">Select a vehicle...</option>
                                        @foreach($customer->vehicles as $vehicle)
                                            <option value="{{ $vehicle->id }}" {{ old('vehicle_id') == $vehicle->id ? 'selected' : '' }}>
                                                {{ $vehicle->year }} {{ $vehicle->make }} {{ $vehicle->model }} 
                                                ({{ $vehicle->license_plate }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="form-text">
                                        Optional: Select a vehicle if your message is related to a specific vehicle.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="subject" class="form-label">
                                <i class="fas fa-heading me-1"></i>Subject *
                            </label>
                            <input type="text" class="form-control @error('subject') is-invalid @enderror" 
                                   id="subject" name="subject" value="{{ old('subject') }}" 
                                   placeholder="Enter message subject..." required>
                            @error('subject')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                Be specific about what you need help with.
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="message" class="form-label">
                                <i class="fas fa-comment-alt me-1"></i>Message *
                            </label>
                            <textarea class="form-control @error('message') is-invalid @enderror" 
                                      id="message" name="message" rows="8" 
                                      placeholder="Type your message here..." required>{{ old('message') }}</textarea>
                            @error('message')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                Please provide as much detail as possible to help us assist you better.
                            </div>
                        </div>

                        <div class="mb-3" id="appointmentFields" style="display: none;">
                            <div class="card border-primary">
                                <div class="card-header bg-primary text-white">
                                    <i class="fas fa-calendar-alt me-2"></i>Appointment Details
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="preferred_date" class="form-label">Preferred Date</label>
                                                <input type="date" class="form-control" 
                                                       id="preferred_date" name="preferred_date"
                                                       min="{{ date('Y-m-d') }}" 
                                                       value="{{ old('preferred_date') }}">
                                                <div class="form-text">
                                                    Your preferred date for the appointment
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="preferred_time" class="form-label">Preferred Time</label>
                                                <select class="form-select" id="preferred_time" name="preferred_time">
                                                    <option value="">Any time</option>
                                                    <option value="morning" {{ old('preferred_time') == 'morning' ? 'selected' : '' }}>Morning (8 AM - 12 PM)</option>
                                                    <option value="afternoon" {{ old('preferred_time') == 'afternoon' ? 'selected' : '' }}>Afternoon (12 PM - 4 PM)</option>
                                                    <option value="evening" {{ old('preferred_time') == 'evening' ? 'selected' : '' }}>Evening (4 PM - 6 PM)</option>
                                                </select>
                                                <div class="form-text">
                                                    Your preferred time of day
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="service_needed" class="form-label">Service Needed</label>
                                        <input type="text" class="form-control" 
                                               id="service_needed" name="service_needed"
                                               placeholder="e.g., Oil change, brake inspection, etc."
                                               value="{{ old('service_needed') }}">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3" id="serviceInquiryFields" style="display: none;">
                            <div class="card border-success">
                                <div class="card-header bg-success text-white">
                                    <i class="fas fa-tools me-2"></i>Service Inquiry Details
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="urgency" class="form-label">Urgency Level</label>
                                                <select class="form-select" id="urgency" name="urgency">
                                                    <option value="routine" {{ old('urgency') == 'routine' ? 'selected' : '' }}>Routine (No rush)</option>
                                                    <option value="soon" {{ old('urgency') == 'soon' ? 'selected' : '' }}>Soon (Within 1-2 weeks)</option>
                                                    <option value="urgent" {{ old('urgency') == 'urgent' ? 'selected' : '' }}>Urgent (As soon as possible)</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="budget" class="form-label">Estimated Budget (Optional)</label>
                                                <div class="input-group">
                                                    <span class="input-group-text">$</span>
                                                    <input type="number" class="form-control" 
                                                           id="budget" name="budget" min="0" step="0.01"
                                                           placeholder="0.00" value="{{ old('budget') }}">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="symptoms" class="form-label">Symptoms/Issues</label>
                                        <textarea class="form-control" id="symptoms" name="symptoms" 
                                                  rows="3" placeholder="Describe any symptoms or issues you're experiencing...">{{ old('symptoms') }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="urgent" name="urgent" value="1">
                                <label class="form-check-label" for="urgent">
                                    <i class="fas fa-exclamation-triangle text-warning me-1"></i>
                                    Mark as urgent (Requires faster response)
                                </label>
                            </div>
                            <div class="form-text">
                                Only check this if your message requires immediate attention.
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="copy_to_email" name="copy_to_email" value="1" checked>
                                <label class="form-check-label" for="copy_to_email">
                                    <i class="fas fa-envelope me-1"></i>
                                    Send a copy to my email ({{ $customer->email }})
                                </label>
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
                                    <i class="fas fa-paper-plane me-1"></i> Send Message
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-lightbulb me-2"></i>Tips for Effective Communication
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6><i class="fas fa-check-circle text-success me-2"></i>Do:</h6>
                            <ul class="small">
                                <li>Be specific about your needs</li>
                                <li>Include vehicle details if relevant</li>
                                <li>Mention any previous service history</li>
                                <li>Provide your availability for follow-up</li>
                                <li>Attach photos if helpful (describe in message)</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6><i class="fas fa-times-circle text-danger me-2"></i>Don't:</h6>
                            <ul class="small">
                                <li>Use all caps or excessive punctuation</li>
                                <li>Send multiple messages for the same issue</li>
                                <li>Include sensitive personal information</li>
                                <li>Expect immediate response outside business hours</li>
                                <li>Forget to check your email for responses</li>
                            </ul>
                        </div>
                    </div>
                    <div class="alert alert-warning mt-3">
                        <i class="fas fa-clock me-2"></i>
                        <strong>Response Time:</strong> We typically respond within 1-2 business days. For faster service, please call us at (555) 123-4567.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Show/hide additional fields based on message type
        $('#message_type').change(function() {
            const type = $(this).val();
            
            // Hide all additional fields
            $('#appointmentFields').hide();
            $('#serviceInquiryFields').hide();
            
            // Show relevant fields
            if (type === 'appointment_request') {
                $('#appointmentFields').show();
                // Auto-fill subject if empty
                if (!$('#subject').val()) {
                    $('#subject').val('Appointment Request');
                }
            } else if (type === 'service_inquiry') {
                $('#serviceInquiryFields').show();
                // Auto-fill subject if empty
                if (!$('#subject').val()) {
                    $('#subject').val('Service Inquiry');
                }
            }
        });

        // Trigger change on page load if there's a value
        if ($('#message_type').val()) {
            $('#message_type').trigger('change');
        }

        // Character counter for message
        $('#message').on('input', function() {
            const length = $(this).val().length;
            $('#charCount').text(length + ' characters');
            
            if (length > 1000) {
                $('#charCount').addClass('text-danger');
            } else {
                $('#charCount').removeClass('text-danger');
            }
        });

        // Auto-save draft every 30 seconds
        let autoSaveInterval;
        function startAutoSave() {
            autoSaveInterval = setInterval(saveDraft, 30000);
        }
        
        function stopAutoSave() {
            clearInterval(autoSaveInterval);
        }
        
        // Start auto-save when user starts typing
        $('#message, #subject').on('input', function() {
            if (!autoSaveInterval) {
                startAutoSave();
            }
        });

        // Stop auto-save when form is submitted
        $('form').on('submit', function() {
            stopAutoSave();
        });
    });

    // Save draft function
    function saveDraft() {
        const formData = {
            message_type: $('#message_type').val(),
            subject: $('#subject').val(),
            message: $('#message').val(),
            vehicle_id: $('#related_vehicle').val(),
            preferred_date: $('#preferred_date').val(),
            preferred_time: $('#preferred_time').val(),
            service_needed: $('#service_needed').val(),
            urgency: $('#urgency').val(),
            budget: $('#budget').val(),
            symptoms: $('#symptoms').val(),
            urgent: $('#urgent').is(':checked') ? 1 : 0
        };

        // Only save if there's content
        if (formData.subject || formData.message) {
            $.ajax({
                url: '{{ route("portal.messages.save-draft") }}',
                method: 'POST',
                data: {
                    ...formData,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    showToast('Draft saved successfully!', 'success');
                },
                error: function() {
                    showToast('Failed to save draft.', 'error');
                }
            });
        }
    }

    // Show toast notification
    function showToast(message, type = 'info') {
        // Remove existing toast
        $('.toast').remove();
        
        const toast = $(`
            <div class="toast align-items-center text-white bg-${type === 'success' ? 'success' : 'danger'} border-0" 
                 role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} me-2"></i>
                        ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        `);
        
        $('body').append(toast);
        const bsToast = new bootstrap.Toast(toast[0]);
        bsToast.show();
        
        // Auto-hide after 3 seconds
        setTimeout(() => {
            bsToast.hide();
        }, 3000);
    }

    // Load draft if exists
    $(window).on('load', function() {
        $.ajax({
            url: '{{ route("portal.messages.load-draft") }}',
            method: 'GET',
            success: function(draft) {
                if (draft) {
                    if (confirm('You have a saved draft. Would you like to load it?')) {
                        // Populate form fields
                        $('#message_type').val(draft.message_type).trigger('change');
                        $('#subject').val(draft.subject);
                        $('#message').val(draft.message);
                        $('#related_vehicle').val(draft.vehicle_id);
                        $('#preferred_date').val(draft.preferred_date);
                        $('#preferred_time').val(draft.preferred_time);
                        $('#service_needed').val(draft.service_needed);
                        $('#urgency').val(draft.urgency);
                        $('#budget').val(draft.budget);
                        $('#symptoms').val(draft.symptoms);
                        $('#urgent').prop('checked', draft.urgent == 1);
                        
                        showToast('Draft loaded successfully!', 'success');
                    }
                }
            }
        });
