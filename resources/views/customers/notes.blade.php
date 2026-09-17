@extends('layouts.app')

@section('title', 'Notes - ' . $customer->full_name . ' - Fix-It Auto Services')
@section('body-class', 'page-customers')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1"><i class="fas fa-sticky-note me-2" style="color: var(--module-active);"></i>Customer Notes</h4>
            <p class="text-muted mb-0">{{ $customer->full_name }}</p>
        </div>
        <a href="{{ route('customers.show', $customer->id) }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Back to Customer
        </a>
    </div>

    <div class="main-card">
        <div class="main-card-header">
            <i class="fas fa-sticky-note me-2" style="color: var(--module-active);"></i> All Notes
        </div>
        <div class="main-card-body px-3 py-3">
            @forelse($notes as $note)
                <div class="note-card mb-3">
                    <div class="note-card-header d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge-custom badge-note-type">{{ $note->note_type ?? 'General' }}</span>
                            <small class="text-muted">{{ $note->created_at->format('M j, Y g:i A') }}</small>
                        </div>
                        @if($note->user)
                            <small class="text-muted">by {{ $note->user->name }}</small>
                        @endif
                    </div>
                    <div class="note-card-body">
                        {{ $note->content }}
                    </div>
                </div>
            @empty
                <div class="empty-state-module py-4">
                    <div class="empty-state-icon">
                        <i class="fas fa-sticky-note"></i>
                    </div>
                    <h5>No Notes</h5>
                    <p>No notes added for this customer yet.</p>
                </div>
            @endforelse

            <div class="mt-3">
                {{ $notes->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
