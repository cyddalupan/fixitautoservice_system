@extends('layouts.app')

@section('title', 'Compliance Report')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h1 class="h3 mb-0">Compliance Report — {{ ucfirst($reportType) }}</h1>
            <p class="text-muted">
                {{ isset($data['period']) ? \Carbon\Carbon::parse($data['period']['start_date'])->format('M d, Y') . ' to ' . \Carbon\Carbon::parse($data['period']['end_date'])->format('M d, Y') : '' }}
            </p>
        </div>
        <div class="col-auto">
            <div class="btn-group">
                <a href="{{ route('compliance.reports', ['type' => 'summary']) }}" class="btn btn-sm {{ $reportType == 'summary' ? 'btn-primary' : 'btn-outline-primary' }}">Summary</a>
                <a href="{{ route('compliance.reports', ['type' => 'audits']) }}" class="btn btn-sm {{ $reportType == 'audits' ? 'btn-primary' : 'btn-outline-primary' }}">Audits</a>
                <a href="{{ route('compliance.reports', ['type' => 'ncrs']) }}" class="btn btn-sm {{ $reportType == 'ncrs' ? 'btn-primary' : 'btn-outline-primary' }}">NCRs</a>
                <a href="{{ route('compliance.reports', ['type' => 'documents']) }}" class="btn btn-sm {{ $reportType == 'documents' ? 'btn-primary' : 'btn-outline-primary' }}">Documents</a>
            </div>
        </div>
    </div>

    @if($reportType == 'summary')
        <div class="row">
            <div class="col-lg-6">
                <div class="card shadow mb-4">
                    <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Standards by Category</h6></div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr><th>Category</th><th class="text-center">Total</th><th class="text-center">Active</th><th class="text-center">Expiring</th><th class="text-center">Expired</th></tr>
                                </thead>
                                <tbody>
                                    @forelse($data['standards_summary'] ?? [] as $row)
                                        <tr>
                                            <td>{{ ucfirst(str_replace('_', ' ', $row->category ?? 'N/A')) }}</td>
                                            <td class="text-center">{{ $row->total }}</td>
                                            <td class="text-center text-success">{{ $row->active }}</td>
                                            <td class="text-center text-warning">{{ $row->expiring }}</td>
                                            <td class="text-center text-danger">{{ $row->expired }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="5" class="text-center text-muted py-4">No standards found.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card shadow mb-4">
                    <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">NCR Summary by Severity</h6></div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr><th>Severity</th><th class="text-center">Total</th><th class="text-center">Open</th><th class="text-center">Avg Days Open</th></tr>
                                </thead>
                                <tbody>
                                    @forelse($data['ncr_summary'] ?? [] as $row)
                                        <tr>
                                            <td>{{ ucfirst($row['severity'] ?? 'N/A') }}</td>
                                            <td class="text-center">{{ $row['total'] }}</td>
                                            <td class="text-center">{{ $row['open'] }}</td>
                                            <td class="text-center">{{ $row['avg_days_open'] }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="4" class="text-center text-muted py-4">No NCRs in period.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card shadow mb-4">
            <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Audit Activity</h6></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr><th>Date</th><th class="text-center">Audits</th><th class="text-center">Avg Score</th><th class="text-center">Compliant (&ge;80)</th></tr>
                        </thead>
                        <tbody>
                            @forelse($data['audit_summary'] ?? [] as $row)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($row->audit_day)->format('M d, Y') }}</td>
                                    <td class="text-center">{{ $row->total_audits }}</td>
                                    <td class="text-center">{{ round($row->avg_score ?? 0, 1) }}%</td>
                                    <td class="text-center">{{ $row->compliant_audits }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted py-4">No audits in period.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @else
        <div class="card shadow mb-4">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                @foreach(array_keys((array) ($data['rows']->first() ?? [])) as $col)
                                    <th>{{ ucfirst(str_replace('_', ' ', $col)) }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($data['rows'] ?? [] as $row)
                                <tr>
                                    @foreach((array) $row as $val)
                                        <td>{{ $val }}</td>
                                    @endforeach
                                </tr>
                            @empty
                                <tr><td colspan="10" class="text-center text-muted py-4">No data for this report type.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
