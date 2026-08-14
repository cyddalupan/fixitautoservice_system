@extends('layouts.app')

@section('title', 'Inbox - Contact Us Leads | Fix-It Auto Services')

@section('content')
<div class="container-fluid py-4 px-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
        <div>
            <h1 class="h3 mb-1">Inbox — Contact Us Leads</h1>
            <p class="text-muted mb-0">Submitted via the Contact Us form on fixitautoservices.com</p>
        </div>
        <form method="GET" action="{{ route('inbox.index') }}" class="d-flex gap-2">
            <input type="text" name="q" value="{{ $q ?? '' }}" class="form-control" placeholder="Search name, email, subject, message…" style="min-width:260px;">
            <button class="btn btn-outline-primary" type="submit"><i class="fas fa-search me-1"></i>Search</button>
        </form>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($messages->count() === 0)
        <div class="alert alert-info">No contact messages found{{ !empty($q) ? ' for "' . e($q) . '"' : '.' }}</div>
    @else
        <div class="table-responsive bg-white rounded-3 shadow-sm">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th style="width:40px;"></th>
                        <th>Name / Email</th>
                        <th>Phone</th>
                        <th>Subject</th>
                        <th>Message</th>
                        <th>Received</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($messages as $m)
                        <tr class="{{ $m->is_read ? '' : 'table-warning' }}">
                            <td>
                                @if(!$m->is_read)
                                    <span class="badge bg-danger" title="Unread">NEW</span>
                                @endif
                            </td>
                            <td>
                                <strong>{{ $m->name }}</strong><br>
                                <a href="mailto:{{ $m->email }}" class="small text-muted">{{ $m->email }}</a>
                            </td>
                            <td>{{ $m->phone ?? '—' }}</td>
                            <td><strong>{{ $m->subject ?? '—' }}</strong></td>
                            <td class="text-muted" style="max-width:300px;">
                                <span class="d-inline-block text-truncate" style="max-width:100%;">{{ $m->message }}</span>
                            </td>
                            <td class="small text-muted">{{ $m->created_at->format('M d, Y g:i A') }}</td>
                            <td>
                                @if(!$m->is_read)
                                    <form method="POST" action="{{ route('inbox.read', $m) }}">
                                        @csrf
                                        <button class="btn btn-sm btn-outline-success" title="Mark as read"><i class="fas fa-check"></i></button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-3 d-flex justify-content-center">
            {{ $messages->withQueryString()->links() }}
        </div>
    @endif
</div>
@endsection
