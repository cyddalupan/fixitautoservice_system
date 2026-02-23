@extends('layouts.app')

@section('title', 'Message Details - Customer Portal')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="card-title mb-0">
                                <i class="fas fa-envelope me-2"></i>Message Details
                            </h3>
                            <small class="text-muted">ID: {{ $message->id }}</small>
                        </div>
                        <div class="btn-group">
                            <a href="{{ route('portal.messages.index') }}" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Back to Inbox
                            </a>
                            @if(!$message->is_read)
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="markAsRead()">
                                    <i class="fas fa-check me-1"></i> Mark as Read
                                </button>
                            @endif
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteMessage()">
                                <i class="fas fa-trash me-1"></i> Delete
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Message Status Alert -->
                    @if($message->is_read)
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            This message was read on {{ $message->read_at->format('F j, Y g:i A') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @else
                        <div class="alert alert-info alert-dismissible fade show" role="alert">
                            <i class="fas fa-envelope me-2"></i>
                            This message is unread
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <div class="row">
                        <!-- Left Column: Message Details -->
                        <div class="col-md-8">
                            <div class="card mb-4">
                                <div class="card-header bg-light">
                                    <h4 class="card-title mb-0">
                                        <i class="fas fa-comment-alt me-2"></i>Message Content
                                    </h4>
                                </div>
                                <div class="card-body">
                                    <div class="mb-4">
                                        <h5 class="mb-3">{{ $message->subject }}</h5>
                                        <div class="message-content p-3 border rounded bg-light">
                                            {!! nl2br(e($message->message)) !!}
                                        </div>
                                    </div>

                                    @if($message->message_type === 'appointment_request' && $message->appointment_details)
                                        <div class="card border-primary mb-3">
                                            <div class="card-header bg-primary text-white">
                                                <i class="fas fa-calendar-alt me-2"></i>Appointment Details
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <p><strong>Preferred Date:</strong> {{ $message->appointment_details['preferred_date'] ?? 'Not specified' }}</p>
                                                        <p><strong>Preferred Time:</strong> {{ $message->appointment_details['preferred_time'] ?? 'Any time' }}</p>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <p><strong>Service Needed:</strong> {{ $message->appointment_details['service_needed'] ?? 'Not specified' }}</p>
                                                        <p><strong>Urgency:</strong> {{ $message->appointment_details['urgency'] ?? 'Not specified' }}</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    @if($message->message_type === 'service_inquiry' && $message->inquiry_details)
                                        <div class="card border-success mb-3">
                                            <div class="card-header bg-success text-white">
                                                <i class="fas fa-tools me-2"></i>Service Inquiry Details
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <p><strong>Urgency Level:</strong> {{ $message->inquiry_details['urgency'] ?? 'Not specified' }}</p>
                                                        <p><strong>Budget:</strong> ${{ number_format($message->inquiry_details['budget'] ?? 0, 2) }}</p>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <p><strong>Symptoms/Issues:</strong></p>
                                                        <p class="text-muted">{{ $message->inquiry_details['symptoms'] ?? 'Not specified' }}</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Response Section -->
                            @if($message->responses && count($message->responses) > 0)
                                <div class="card mb-4">
                                    <div class="card-header bg-light">
                                        <h4 class="card-title mb-0">
                                            <i class="fas fa-reply me-2"></i>Responses ({{ count($message->responses) }})
                                        </h4>
                                    </div>
                                    <div class="card-body">
                                        @foreach($message->responses as $response)
                                            <div class="response-item mb-3 p-3 border rounded {{ $response->is_from_customer ? 'bg-light' : 'bg-info bg-opacity-10' }}">
                                                <div class="d-flex justify-content-between mb-2">
                                                    <div>
                                                        <strong>
                                                            @if($response->is_from_customer)
                                                                <i class="fas fa-user me-1"></i>You
                                                            @else
                                                                <i class="fas fa-store me-1"></i>Fix-It Auto Services
                                                            @endif
                                                        </strong>
                                                        <small class="text-muted ms-2">
                                                            {{ $response->created_at->format('F j, Y g:i A') }}
                                                        </small>
                                                    </div>
                                                    @if($response->is_from_customer)
                                                        <span class="badge bg-secondary">Sent</span>
                                                    @else
                                                        <span class="badge bg-primary">Received</span>
                                                    @endif
                                                </div>
                                                <p class="mb-0">{{ $response->message }}</p>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <!-- Reply Form -->
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h4 class="card-title mb-0">
                                        <i class="fas fa-reply me-2"></i>Reply to this Message
                                    </h4>
                                </div>
                                <div class="card-body">
                                    <form action="{{ route('portal.messages.reply', $message->id) }}" method="POST">
                                        @csrf
                                        <div class="mb-3">
                                            <label for="reply_message" class="form-label">Your Reply *</label>
                                            <textarea class="form-control @error('reply_message') is-invalid @enderror" 
                                                      id="reply_message" name="reply_message" rows="4" 
                                                      placeholder="Type your reply here..." required>{{ old('reply_message') }}</textarea>
                                            @error('reply_message')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <div class="form-text">
                                                Please be specific and include any additional information needed.
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
                                        <div class="d-flex justify-content-end">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-paper-plane me-1"></i> Send Reply
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column: Message Metadata -->
                        <div class="col-md-4">
                            <div class="card mb-4">
                                <div class="card-header bg-light">
                                    <h4 class="card-title mb-0">
                                        <i class="fas fa-info-circle me-2"></i>Message Information
                                    </h4>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <h6><i class="fas fa-tag me-2"></i>Type</h6>
                                        <span class="badge 
                                            @if($message->message_type === 'appointment_request') bg-primary
                                            @elseif($message->message_type === 'service_inquiry') bg-success
                                            @elseif($message->message_type === 'question') bg-info
                                            @else bg-secondary @endif">
                                            {{ ucfirst(str_replace('_', ' ', $message->message_type)) }}
                                        </span>
                                    </div>

                                    <div class="mb-3">
                                        <h6><i class="fas fa-calendar me-2"></i>Dates</h6>
                                        <p class="mb-1"><strong>Sent:</strong> {{ $message->created_at->format('F j, Y g:i A') }}</p>
                                        @if($message->read_at)
                                            <p class="mb-1"><strong>Read:</strong> {{ $message->read_at->format('F j, Y g:i A') }}</p>
                                        @endif
                                    </div>

                                    @if($message->vehicle)
                                        <div class="mb-3">
                                            <h6><i class="fas fa-car me-2"></i>Related Vehicle</h6>
                                            <div class="p-2 border rounded bg-light">
                                                <p class="mb-1"><strong>{{ $message->vehicle->year }} {{ $message->vehicle->make }} {{ $message->vehicle->model }}</strong></p>
                                                <p class="mb-0 text-muted small">License: {{ $message->vehicle->license_plate }}</p>
                                                <p class="mb-0 text-muted small">VIN: {{ $message->vehicle->vin }}</p>
                                            </div>
                                        </div>
                                    @endif

                                    @if($message->work_order)
                                        <div class="mb-3">
                                            <h6><i class="fas fa-wrench me-2"></i>Related Work Order</h6>
                                            <div class="p-2 border rounded bg-light">
                                                <p class="mb-1"><strong>WO-{{ $message->work_order->id }}</strong></p>
                                                <p class="mb-0 text-muted small">Status: {{ ucfirst($message->work_order->status) }}</p>
                                                <p class="mb-0 text-muted small">Created: {{ $message->work_order->created_at->format('M j, Y') }}</p>
                                            </div>
                                        </div>
                                    @endif

                                    <div class="mb-3">
                                        <h6><i class="fas fa-paperclip me-2"></i>Attachments</h6>
                                        @if($message->attachments && count($message->attachments) > 0)
                                            <div class="list-group">
                                                @foreach($message->attachments as $attachment)
                                                    <a href="{{ route('portal.messages.download-attachment', ['message' => $message->id, 'attachment' => $loop->index]) }}" 
                                                       class="list-group-item list-group-item-action">
                                                        <div class="d-flex align-items-center">
                                                            <i class="fas fa-file me-2 text-primary"></i>
                                                            <div class="flex-grow-1">
                                                                <div class="fw-bold">{{ $attachment['filename'] }}</div>
                                                                <small class="text-muted">{{ $attachment['size_formatted'] }}</small>
                                                            </div>
                                                            <i class="fas fa-download"></i>
                                                        </div>
                                                    </a>
                                                @endforeach
                                            </div>
                                        @else
                                            <p class="text-muted mb-0">No attachments</p>
                                        @endif
                                    </div>

                                    <div class="mb-3">
                                        <h6><i class="fas fa-history me-2"></i>Message History</h6>
                                        <div class="timeline">
                                            <div class="timeline-item {{ $message->is_read ? 'completed' : '' }}">
                                                <div class="timeline-marker"></div>
                                                <div class="timeline-content">
                                                    <p class="mb-0"><strong>Message Sent</strong></p>
                                                    <small class="text-muted">{{ $message->created_at->format('M j, g:i A') }}</small>
                                                </div>
                                            </div>
                                            @if($message->read_at)
                                                <div class="timeline-item completed">
                                                    <div class="timeline-marker"></div>
                                                    <div class="timeline-content">
                                                        <p class="mb-0"><strong>Message Read</strong></p>
                                                        <small class="text-muted">{{ $message->read_at->format('M j, g:i A') }}</small>
                                                    </div>
                                                </div>
                                            @endif
                                            @if($message->responses && count($message->responses) > 0)
                                                <div class="timeline-item completed">
                                                    <div class="timeline-marker"></div>
                                                    <div class="timeline-content">
                                                        <p class="mb-0"><strong>{{ count($message->responses) }} Response(s)</strong></p>
                                                        <small class="text-muted">Last: {{ $message->responses->last()->created_at->format('M j, g:i A') }}</small>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
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
                                        <a href="{{ route('portal.messages.create') }}" class="btn btn-outline-primary">
                                            <i class="fas fa-plus me-2"></i> New Message
                                        </a>
                                        @if($message->message_type === 'appointment_request')
                                            <a href="{{ route('portal.appointments.create') }}" class="btn btn-outline-success">
                                                <i class="fas fa-calendar-plus me-2"></i> Schedule Appointment
                                            </a>
                                        @endif
                                        @if($message->vehicle)
                                            <a href="{{ route('portal.vehicles.show', $message->vehicle->id) }}" class="btn btn-outline-info">
                                                <i class="fas fa-car me-2"></i> View Vehicle
                                            </a>
                                        @endif
                                        <button type="button" class="btn btn-outline-warning" onclick="printMessage()">
                                            <i class="fas fa-print me-2"></i> Print Message
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
                                Message ID: {{ $message->id }} | Thread: {{ $message->thread_id ?? 'N/A' }}
                            </small>
                        </div>
                        <div>
                            <small class="text-muted">
                                Last updated: {{ $message->updated_at->format('F j, Y g:i A') }}
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
    .message-content {
        white-space: pre-wrap;
        word-wrap: break-word;
        line-height: 1.6;
    }
    
    .timeline {
        position: relative;
        padding-left: 20px;
    }
    
    .timeline-item {
        position: relative;
        margin-bottom: 15px;
    }
    
    .timeline-item:last-child {
        margin-bottom: 0;
    }
    
    .timeline-marker {
        position: absolute;
        left: -20px;
        top: 0;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background-color: #6c757d;
        border: 2px solid #fff;
    }
    
    .timeline-item.completed .timeline-marker {
        background-color: #198754;
    }
    
    .timeline-item:not(:last-child)::after {
        content: '';
        position: absolute;
        left: -15px;
        top: 12px;
        bottom: -15px;
        width: 2px;
        background-color: #dee2e6;
    }
    
    .timeline-item.completed:not(:last-child)::after {
        background-color: #198754;
    }
    
    .response-item {
        position: relative;
    }
    
    .response-item::before {
        content: '';
        position: absolute;
        left: -10px;
        top: 15px;
        width: 0;
        height: 0;
        border-top: 8px solid transparent;
        border-bottom: 8px solid transparent;
        border-right: