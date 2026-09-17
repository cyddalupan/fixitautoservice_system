@extends('layouts.app')

@section('title', 'Standard Details')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h1 class="h3 mb-0">{{ $standard->name }}</h1>
            <p class="text-muted">{{ $standard->code ?? '' }} — {{ ucfirst(str_replace('_', ' ', $standard->category ?? 'General')) }}</p>
        </div>
        <div class="col-auto">
            <a href="{{ route('compliance.standards.edit', $standard->id) }}" class="btn btn-outline-primary">
                <i class="fas fa-edit"></i> Edit
            </a>
            <a href="{{ route('compliance.standards.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Description</h6></div>
                <div class="card-body">
                    <p>{{ $standard->description ?? 'No description provided.' }}</p>
                </div>
            </div>
            <div class="card shadow mb-4">
                <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Requirements</h6></div>
                <div class="card-body">
                    @php
                        $reqs = is_array($standard->requirements)
                            ? $standard->requirements
                            : (is_string($standard->requirements) && json_decode($standard->requirements, true)
                                ? json_decode($standard->requirements, true)
                                : array_filter(array_map('trim', explode("\n", (string) $standard->requirements))));
                    @endphp
                    @if(!empty($reqs))
                        <ul class="mb-0">
                            @foreach($reqs as $req)
                                <li class="mb-1">{{ $req }}</li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-muted mb-0">No requirements listed.</p>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Details</h6></div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-6">Version</dt>
                        <dd class="col-6">v{{ $standard->revision_number ?? 1 }}</dd>
                        <dt class="col-6">Category</dt>
                        <dd class="col-6">{{ ucfirst(str_replace('_', ' ', $standard->category ?? 'N/A')) }}</dd>
                        <dt class="col-6">Mandatory</dt>
                        <dd class="col-6">{{ $standard->is_mandatory ? 'Yes' : 'No' }}</dd>
                        <dt class="col-6">Effective</dt>
                        <dd class="col-6">{{ $standard->effective_date ? \Carbon\Carbon::parse($standard->effective_date)->format('M d, Y') : 'N/A' }}</dd>
                        <dt class="col-6">Expires</dt>
                        <dd class="col-6">{{ $standard->expiration_date ? \Carbon\Carbon::parse($standard->expiration_date)->format('M d, Y') : 'Never' }}</dd>
                        <dt class="col-6">Status</dt>
                        <dd class="col-6">
                            @if($standard->is_active)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-secondary">Archived</span>
                            @endif
                        </dd>
                    </dl>
                </div>
            </div>
            @if(isset($complianceStats) && count($complianceStats) > 0)
            <div class="card shadow mb-4">
                <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Compliance</h6></div>
                <div class="card-body">
                    @foreach($complianceStats as $label => $value)
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">{{ $label }}</span>
                            <strong>{{ $value }}</strong>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
