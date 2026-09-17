@extends('layouts.app')

@section('title', 'Compliance Documents')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h1 class="h3 mb-0">Compliance Documents</h1>
            <p class="text-muted">Upload and track compliance documents</p>
        </div>
        <div class="col-auto">
            <a href="{{ route('compliance.documents.create') }}" class="btn btn-primary">
                <i class="fas fa-upload"></i> Upload Document
            </a>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Documents ({{ $documents->total() }})</h6>
        </div>
        <div class="card-body">
            @if($documents->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Document</th>
                                <th>Type</th>
                                <th>Number</th>
                                <th>Issued</th>
                                <th>Expires</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($documents as $document)
                                <tr>
                                    <td>{{ $document->document_name }}</td>
                                    <td>{{ ucfirst(str_replace('_', ' ', $document->document_type ?? 'N/A')) }}</td>
                                    <td>{{ $document->document_number ?? 'N/A' }}</td>
                                    <td>{{ $document->issue_date ? \Carbon\Carbon::parse($document->issue_date)->format('M d, Y') : 'N/A' }}</td>
                                    <td>{{ $document->expiration_date ? \Carbon\Carbon::parse($document->expiration_date)->format('M d, Y') : 'Never' }}</td>
                                    <td>
                                        @php
                                            $days = $document->expiration_date ? \Carbon\Carbon::now()->diffInDays(\Carbon\Carbon::parse($document->expiration_date), false) : 9999;
                                            $color = $days < 0 ? 'danger' : ($days <= 60 ? 'warning' : 'success');
                                            $label = $days < 0 ? 'Expired' : ($days <= 60 ? 'Expiring' : 'Valid');
                                        @endphp
                                        <span class="badge bg-{{ $color }}">{{ $label }}</span>
                                    </td>
                                    <td>
                                        <a href="{{ route('compliance.documents.show', $document->id) }}" class="btn btn-sm btn-outline-secondary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-center mt-3">{{ $documents->links() }}</div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-file-upload fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No documents uploaded.</p>
                    <a href="{{ route('compliance.documents.create') }}" class="btn btn-primary">Upload your first document</a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
