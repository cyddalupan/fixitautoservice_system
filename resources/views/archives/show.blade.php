@php
    $moduleLabels = [
        'work_order' => 'Repair Order',
        'estimate' => 'Estimate',
        'payment' => 'Payment',
        'invoice' => 'Invoice',
        'inspection' => 'Inspection',
        'customer' => 'Customer',
    ];
@endphp

@extends('layouts.app')

@section('title', 'Archived Record #' . $archive->id)

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-archive me-2"></i>Archived Record #{{ $archive->id }}
        </h1>
        <a href="{{ route('archives.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Archive
        </a>
    </div>

    <div class="row">
        <!-- Meta Info -->
        <div class="col-md-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Record Info</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <th>Source Module</th>
                            <td><span class="badge bg-secondary">{{ $moduleLabels[$archive->source_module] ?? ucfirst($archive->source_module) }}</span></td>
                        </tr>
                        <tr>
                            <th>Original ID</th>
                            <td><code>#{{ $archive->archivable_id }}</code></td>
                        </tr>
                        <tr>
                            <th>Archived By</th>
                            <td>{{ $archive->archivedBy->name ?? 'Unknown' }} ({{ $archive->archivedBy->role ?? 'N/A' }})</td>
                        </tr>
                        <tr>
                            <th>Archived At</th>
                            <td>{{ $archive->archived_at->format('F d, Y h:i A') }}</td>
                        </tr>
                        <tr>
                            <th>Restored At</th>
                            <td>
                                @if($archive->restored_at)
                                    {{ $archive->restored_at->format('F d, Y h:i A') }}
                                @else
                                    <span class="badge bg-warning text-dark">Not restored</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Actions -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Actions</h6>
                </div>
                <div class="card-body">
                    @if(!$archive->restored_at)
                    <form action="{{ route('archives.restore', $archive) }}" method="POST" class="mb-2"
                          onsubmit="return confirm('Restore this record back to {{ $moduleLabels[$archive->source_module] ?? $archive->source_module }}?')">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-success w-100">
                            <i class="fas fa-undo-alt me-1"></i> Restore Record
                        </button>
                    </form>
                    @else
                    <div class="alert alert-success mb-2">
                        <i class="fas fa-check-circle me-1"></i> This record has been restored.
                    </div>
                    @endif

                    @if(in_array(Auth::user()->role, ['super_admin', 'admin']))
                    <form action="{{ route('archives.destroy', $archive) }}" method="POST"
                          onsubmit="return confirm('Permanently delete this archived record? This action cannot be undone.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger w-100">
                            <i class="fas fa-trash me-1"></i> Permanently Delete
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>

        <!-- Original Data (JSON) -->
        <div class="col-md-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-database me-2"></i>Original Record Data
                    </h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-striped mb-0">
                            <tbody>
                                @foreach($archive->original_data as $key => $value)
                                <tr>
                                    <th class="text-nowrap" style="width: 220px; padding: 0.4rem 0.75rem;">
                                        {{ Str::title(str_replace('_', ' ', $key)) }}
                                    </th>
                                    <td style="padding: 0.4rem 0.75rem;">
                                        @if(is_null($value))
                                            <em class="text-muted">NULL</em>
                                        @elseif(is_bool($value))
                                            <span class="badge {{ $value ? 'bg-success' : 'bg-secondary' }}">{{ $value ? 'Yes' : 'No' }}</span>
                                        @elseif(is_array($value))
                                            <pre class="mb-0" style="font-size: 0.75rem;">{{ json_encode($value, JSON_PRETTY_PRINT) }}</pre>
                                        @elseif(\Carbon\Carbon::hasFormat((string)$value, 'Y-m-d H:i:s'))
                                            {{ \Carbon\Carbon::parse($value)->format('M d, Y h:i A') }}
                                        @else
                                            {{ $value }}
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
