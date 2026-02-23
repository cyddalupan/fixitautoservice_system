@extends('layouts.app')

@section('title', 'Messages - Customer Portal')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-envelope me-2"></i>Messages
                    </h3>
                    <div class="card-tools">
                        @if($unreadCount > 0)
                            <span class="badge bg-danger" id="unread-messages-badge">
                                {{ $unreadCount }} unread
                            </span>
                        @endif
                        <a href="{{ route('portal.messages.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus me-1"></i> New Message
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="row">
                        <div class="col-md-3 border-end p-0">
                            <div class="list-group list-group-flush">
                                <a href="{{ route('portal.messages.index') }}" 
                                   class="list-group-item list-group-item-action {{ request()->is('portal/messages') && !request()->has('filter') ? 'active' : '' }}">
                                    <i class="fas fa-inbox me-2"></i>All Messages
                                    <span class="badge bg-secondary float-end">{{ $messages->total() }}</span>
                                </a>
                                <a href="{{ route('portal.messages.index', ['filter' => 'unread']) }}" 
                                   class="list-group-item list-group-item-action {{ request()->get('filter') == 'unread' ? 'active' : '' }}">
                                    <i class="fas fa-envelope me-2"></i>Unread
                                    @if($unreadCount > 0)
                                        <span class="badge bg-danger float-end">{{ $unreadCount }}</span>
                                    @endif
                                </a>
                                <a href="{{ route('portal.messages.index', ['filter' => 'sent']) }}" 
                                   class="list-group-item list-group-item-action {{ request()->get('filter') == 'sent' ? 'active' : '' }}">
                                    <i class="fas fa-paper-plane me-2"></i>Sent
                                </a>
                                <a href="{{ route('portal.messages.index', ['filter' => 'important']) }}" 
                                   class="list-group-item list-group-item-action {{ request()->get('filter') == 'important' ? 'active' : '' }}">
                                    <i class="fas fa-star me-2"></i>Important
                                </a>
                                <a href="{{ route('portal.messages.index', ['filter' => 'archived']) }}" 
                                   class="list-group-item list-group-item-action {{ request()->get('filter') == 'archived' ? 'active' : '' }}">
                                    <i class="fas fa-archive me-2"></i>Archived
                                </a>
                            </div>
                            
                            <div class="p-3 border-top">
                                <h6 class="text-muted mb-3">Message Types</h6>
                                <div class="list-group list-group-flush">
                                    <a href="{{ route('portal.messages.index', ['type' => 'appointment_request']) }}" 
                                       class="list-group-item list-group-item-action small py-2 {{ request()->get('type') == 'appointment_request' ? 'active' : '' }}">
                                        <i class="fas fa-calendar-check me-2"></i>Appointment Requests
                                    </a>
                                    <a href="{{ route('portal.messages.index', ['type' => 'service_inquiry']) }}" 
                                       class="list-group-item list-group-item-action small py-2 {{ request()->get('type') == 'service_inquiry' ? 'active' : '' }}">
                                        <i class="fas fa-tools me-2"></i>Service Inquiries
                                    </a>
                                    <a href="{{ route('portal.messages.index', ['type' => 'question']) }}" 
                                       class="list-group-item list-group-item-action small py-2 {{ request()->get('type') == 'question' ? 'active' : '' }}">
                                        <i class="fas fa-question-circle me-2"></i>Questions
                                    </a>
                                    <a href="{{ route('portal.messages.index', ['type' => 'general']) }}" 
                                       class="list-group-item list-group-item-action small py-2 {{ request()->get('type') == 'general' ? 'active' : '' }}">
                                        <i class="fas fa-comments me-2"></i>General
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-9 p-0">
                            @if($messages->isEmpty())
                                <div class="text-center py-5">
                                    <i class="fas fa-envelope-open fa-4x text-muted mb-3"></i>
                                    <h4>No messages</h4>
                                    <p class="text-muted">
                                        @if(request()->has('filter') && request()->get('filter') == 'unread')
                                            You have no unread messages.
                                        @elseif(request()->has('filter') && request()->get('filter') == 'sent')
                                            You haven't sent any messages yet.
                                        @else
                                            Your messages will appear here.
                                        @endif
                                    </p>
                                    <a href="{{ route('portal.messages.create') }}" class="btn btn-primary">
                                        <i class="fas fa-plus me-1"></i> Send Your First Message
                                    </a>
                                </div>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th width="40">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" id="selectAll">
                                                    </div>
                                                </th>
                                                <th>From/To</th>
                                                <th>Subject</th>
                                                <th>Type</th>
                                                <th>Date</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($messages as $message)
                                                <tr class="{{ !$message->is_read ? 'table-info' : '' }}">
                                                    <td>
                                                        <div class="form-check">
                                                            <input class="form-check-input message-checkbox" 
                                                                   type="checkbox" value="{{ $message->id }}">
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div class="me-2">
                                                                @if($message->direction === 'incoming')
                                                                    <i class="fas fa-inbox text-primary"></i>
                                                                @else
                                                                    <i class="fas fa-paper-plane text-success"></i>
                                                                @endif
                                                            </div>
                                                            <div>
                                                                @if($message->direction === 'incoming')
                                                                    <strong>Fix-It Auto Service</strong>
                                                                    <div class="text-muted small">From: Shop</div>
                                                                @else
                                                                    <strong>You</strong>
                                                                    <div class="text-muted small">To: Shop</div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div>
                                                            @if(!$message->is_read && $message->direction === 'incoming')
                                                                <strong>{{ $message->subject }}</strong>
                                                            @else
                                                                {{ $message->subject }}
                                                            @endif
                                                        </div>
                                                        <div class="text-muted small text-truncate" style="max-width: 200px;">
                                                            {{ Str::limit($message->message, 50) }}
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-{{ $message->message_type === 'appointment_request' ? 'primary' : ($message->message_type === 'service_inquiry' ? 'success' : ($message->message_type === 'question' ? 'info' : 'secondary')) }}">
                                                            {{ ucfirst(str_replace('_', ' ', $message->message_type)) }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <div>{{ $message->created_at->format('M d') }}</div>
                                                        <div class="text-muted small">{{ $message->created_at->format('g:i A') }}</div>
                                                    </td>
                                                    <td>
                                                        @if($message->direction === 'incoming')
                                                            @if($message->is_read)
                                                                <span class="badge bg-success">
                                                                    <i class="fas fa-check me-1"></i>Read
                                                                </span>
                                                            @else
                                                                <span class="badge bg-warning">
                                                                    <i class="fas fa-clock me-1"></i>Unread
                                                                </span>
                                                            @endif
                                                        @else
                                                            <span class="badge bg-secondary">
                                                                <i class="fas fa-paper-plane me-1"></i>Sent
                                                            </span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <div class="btn-group btn-group-sm">
                                                            <a href="{{ route('portal.messages.show', $message) }}" 
                                                               class="btn btn-outline-primary">
                                                                <i class="fas fa-eye"></i>
                                                            </a>
                                                            <button type="button" class="btn btn-outline-secondary dropdown-toggle dropdown-toggle-split" 
                                                                    data-bs-toggle="dropdown" aria-expanded="false">
                                                                <span class="visually-hidden">Toggle Dropdown</span>
                                                            </button>
                                                            <ul class="dropdown-menu">
                                                                @if($message->direction === 'incoming' && !$message->is_read)
                                                                <li>
                                                                    <a class="dropdown-item mark-as-read" href="#" data-id="{{ $message->id }}">
                                                                        <i class="fas fa-check me-2"></i> Mark as Read
                                                                    </a>
                                                                </li>
                                                                @endif
                                                                <li>
                                                                    <a class="dropdown-item" href="#">
                                                                        <i class="fas fa-reply me-2"></i> Reply
                                                                    </a>
                                                                </li>
                                                                <li>
                                                                    <a class="dropdown-item" href="#">
                                                                        <i class="fas fa-archive me-2"></i> Archive
                                                                    </a>
                                                                </li>
                                                                <li>
                                                                    <a class="dropdown-item" href="#">
                                                                        <i class="fas fa-flag me-2"></i> Flag
                                                                    </a>
                                                                </li>
                                                                <li><hr class="dropdown-divider"></li>
                                                                <li>
                                                                    <a class="dropdown-item text-danger" href="#">
                                                                        <i class="fas fa-trash me-2"></i> Delete
                                                                    </a>
                                                                </li>
                                                            </ul>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                
                                <div class="p-3 border-top">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-sm btn-outline-secondary" id="markSelectedRead">
                                                    <i class="fas fa-check me-1"></i> Mark as Read
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-secondary" id="archiveSelected">
                                                    <i class="fas fa-archive me-1"></i> Archive
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-danger" id="deleteSelected">
                                                    <i class="fas fa-trash me-1"></i> Delete
                                                </button>
                                            </div>
                                        </div>
                                        <div>
                                            <div class="text-muted small me-3 d-inline-block">
                                                Showing {{ $messages->firstItem() }} to {{ $messages->lastItem() }} of {{ $messages->total() }} messages
                                            </div>
                                            <div class="d-inline-block">
                                                {{ $messages->links() }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
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
    $(document).ready(function() {
        // Select all checkbox
        $('#selectAll').change(function() {
            $('.message-checkbox').prop('checked', $(this).is(':checked'));
        });

        // Mark selected as read
        $('#markSelectedRead').click(function() {
            let selectedIds = getSelectedMessageIds();
            if (selectedIds.length === 0) {
                alert('Please select at least one message.');
                return;
            }
            
            $.ajax({
                url: '{{ route("portal.messages.bulk-read") }}',
                method: 'POST',
                data: {
                    message_ids: selectedIds,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    location.reload();
                }
            });
        });

        // Archive selected
        $('#archiveSelected').click(function() {
            let selectedIds = getSelectedMessageIds();
            if (selectedIds.length === 0) {
                alert('Please select at least one message.');
                return;
            }
            
            if (confirm('Archive selected messages?')) {
                // TODO: Implement archive
                alert('Archive feature coming soon!');
            }
        });

        // Delete selected
        $('#deleteSelected').click(function() {
            let selectedIds = getSelectedMessageIds();
            if (selectedIds.length === 0) {
                alert('Please select at least one message.');
                return;
            }
            
            if (confirm('Delete selected messages? This action cannot be undone.')) {
                // TODO: Implement delete
                alert('Delete feature coming soon!');
            }
        });

        // Mark individual message as read
        $('.mark-as-read').click(function(e) {
            e.preventDefault();
            let messageId = $(this).data('id');
            
            $.ajax({
                url: '{{ route("portal.messages.mark-read", "") }}/' + messageId,
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    location.reload();
                }
            });
        });

        // Get selected message IDs
        function getSelectedMessageIds() {
            let selectedIds = [];
            $('.message-checkbox:checked').each(function() {
                selectedIds.push($(this).val());
            });
            return selectedIds;
        }

        // Auto-refresh for new messages
        setInterval(function() {
            $.ajax({
                url: '{{ route("portal.messages.unread-count") }}',
                method: 'GET',
                success: function(data) {
                    if (data.unread_count > 0) {
                        $('#unread-messages-badge').text(data.unread_count + ' unread').show();
                    } else {
                        $('#unread-messages-badge').hide();
                    }
                }
            });
        }, 60000); // Every minute
    });
</script>
@endsection

@section('styles')
<style>
    .list-group-item.active {
        background-color: #e3f2fd;
        border-color: #bbdefb;
        color: #1565c0;
    }
    .table-info {
        background-color: #e3f2fd !important;
    }
    .table tbody tr:hover {
        background-color: #f5f5f5;
    }
</style>
@endsection