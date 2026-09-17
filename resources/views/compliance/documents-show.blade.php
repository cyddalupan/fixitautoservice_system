@extends('layouts.app')

@section('title', 'Document Details')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h1 class="h3 mb-0">{{ $document->document_name }}</h1>
            <p class="text-muted">{{ $document->document_number ?? '' }}</p>
        </div>
        <div class="col-auto">
            <a href="{{ route('compliance.documents.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Details</h6></div>
        <div class="card-body">
            <dl class="row mb-0">
                <dt class="col-3">Type</dt>
                <dd class="col-9">{{ ucfirst(str_replace('_', ' ', $document->document_type ?? 'N/A')) }}</dd>
                <dt class="col-3">Number</dt>
                <dd class="col-9">{{ $document->document_number ?? 'N/A' }}</dd>
                <dt class="col-3">Authority</dt>
                <dd class="col-9">{{ $document->issuing_authority ?? 'N/A' }}</dd>
                <dt class="col-3">Issue Date</dt>
                <dd class="col-9">{{ $document->issue_date ? \Carbon\Carbon::parse($document->issue_date)->format('M d, Y') : 'N/A' }}</dd>
                <dt class="col-3">Expiration</dt>
                <dd class="col-9">{{ $document->expiration_date ? \Carbon\Carbon::parse($document->expiration_date)->format('M d, Y') : 'Never' }}</dd>
                <dt class="col-3">Notes</dt>
                <dd class="col-9">{{ $document->notes ?? 'N/A' }}</dd>
            </dl>
            @if($document->file_path)
                <a href="{{ asset('storage/' . $document->file_path) }}" target="_blank" class="btn btn-sm btn-outline-primary mt-3">
                    <i class="fas fa-download"></i> Download File
                </a>
            @endif
        </div>
    </div>
</div>
@endsection
