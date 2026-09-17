@extends('layouts.app')

@section('title', 'NCR Report')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h1 class="h3 mb-0">Non-Conformance Report</h1>
            <p class="text-muted">
                {{ isset($data['period']) ? \Carbon\Carbon::parse($data['period']['start_date'])->format('M d, Y') . ' to ' . \Carbon\Carbon::parse($data['period']['end_date'])->format('M d, Y') : '' }}
            </p>
        </div>
        <div class="col-auto">
            <div class="btn-group">
                <a href="{{ route('compliance.reports', ['type' => 'summary']) }}" class="btn btn-sm btn-outline-primary">Summary</a>
                <a href="{{ route('compliance.reports', ['type' => 'audits']) }}" class="btn btn-sm btn-outline-primary">Audits</a>
                <a href="{{ route('compliance.reports', ['type' => 'ncrs']) }}" class="btn btn-sm btn-primary">NCRs</a>
                <a href="{{ route('compliance.reports', ['type' => 'documents']) }}" class="btn btn-sm btn-outline-primary">Documents</a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card shadow mb-4">
                <div class="card-body text-center">
                    <div class="h2 mb-0">{{ $data['summary']['total_ncrs'] ?? 0 }}</div>
                    <div class="text-muted">Total NCRs</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow mb-4">
                <div class="card-body text-center">
                    <div class="h2 mb-0">{{ $data['summary']['avg_days_open'] ?? 0 }}</div>
                    <div class="text-muted">Avg Days Open</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow mb-4">
                <div class="card-body">
                    <h6 class="text-muted mb-2">By Status</h6>
                    @foreach($data['summary']['by_status'] ?? [] as $status => $count)
                        <div class="d-flex justify-content-between mb-1">
                            <span>{{ ucfirst(str_replace('_', ' ', $status)) }}</span>
                            <strong>{{ $count }}</strong>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">NCRs</h6></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr><th>NCR #</th><th>Title</th><th>Severity</th><th>Reported</th><th>Status</th></tr>
                    </thead>
                    <tbody>
                        @forelse($data['ncrs'] ?? [] as $ncr)
                            <tr>
                                <td>{{ $ncr->ncr_number ?? 'N/A' }}</td>
                                <td>{{ $ncr->title }}</td>
                                <td>{{ ucfirst($ncr->severity ?? 'N/A') }}</td>
                                <td>{{ $ncr->reported_date ? \Carbon\Carbon::parse($ncr->reported_date)->format('M d, Y') : 'N/A' }}</td>
                                <td>{{ ucfirst(str_replace('_', ' ', $ncr->status ?? 'N/A')) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-4">No NCRs in period.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
