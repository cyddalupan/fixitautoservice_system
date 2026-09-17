@extends('layouts.app')

@section('title', 'Compliance Audits Report')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h1 class="h3 mb-0">Compliance Audits Report</h1>
            <p class="text-muted">
                {{ isset($data['period']) ? \Carbon\Carbon::parse($data['period']['start_date'])->format('M d, Y') . ' to ' . \Carbon\Carbon::parse($data['period']['end_date'])->format('M d, Y') : '' }}
            </p>
        </div>
        <div class="col-auto">
            <div class="btn-group">
                <a href="{{ route('compliance.reports', ['type' => 'summary']) }}" class="btn btn-sm btn-outline-primary">Summary</a>
                <a href="{{ route('compliance.reports', ['type' => 'audits']) }}" class="btn btn-sm btn-primary">Audits</a>
                <a href="{{ route('compliance.reports', ['type' => 'ncrs']) }}" class="btn btn-sm btn-outline-primary">NCRs</a>
                <a href="{{ route('compliance.reports', ['type' => 'documents']) }}" class="btn btn-sm btn-outline-primary">Documents</a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card shadow mb-4">
                <div class="card-body text-center">
                    <div class="h2 mb-0">{{ $data['summary']['total_audits'] ?? 0 }}</div>
                    <div class="text-muted">Total Audits</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow mb-4">
                <div class="card-body text-center">
                    <div class="h2 mb-0">{{ $data['summary']['avg_score'] ?? 0 }}%</div>
                    <div class="text-muted">Average Score</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow mb-4">
                <div class="card-body text-center">
                    <div class="h2 mb-0">{{ $data['summary']['compliance_rate'] ?? 0 }}%</div>
                    <div class="text-muted">Compliance Rate</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Audits</h6></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr><th>Audit #</th><th>Title</th><th>Date</th><th>Score</th><th>Status</th></tr>
                    </thead>
                    <tbody>
                        @forelse($data['audits'] ?? [] as $audit)
                            <tr>
                                <td>{{ $audit->audit_number ?? 'N/A' }}</td>
                                <td>{{ $audit->title }}</td>
                                <td>{{ $audit->audit_date ? \Carbon\Carbon::parse($audit->audit_date)->format('M d, Y') : 'N/A' }}</td>
                                <td>{{ $audit->percentage_score ?? 0 }}%</td>
                                <td>{{ ucfirst(str_replace('_', ' ', $audit->status ?? 'N/A')) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-4">No audits in period.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
