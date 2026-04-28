@if($selectedCustomer ?? null)
<div class="history-section" data-customer-id="{{ $selectedCustomer->id }}" data-type="{{ $historyType ?? 'generic' }}">
    <div class="history-card">
        <div class="history-card-header">
            <h6><i class="fas fa-history me-2"></i> {{ $historyTitle ?? 'Previous Records' }}</h6>
            <span class="text-muted" style="font-size: 0.75rem;">{{ $customerHistory->count() }} records</span>
        </div>
        <div class="history-card-body">
            @forelse($customerHistory as $record)
            <div class="history-item" onclick="window.location.href='{{ $historyRoute ?? '#' }}'">
                <div class="history-icon {{ $record->statusClass ?? 'info' }}">
                    <i class="{{ $record->iconClass ?? 'fas fa-file-alt' }}"></i>
                </div>
                <div class="history-info">
                    <div class="history-title">{{ $record->historyTitle ?? ($record->name ?? 'Record #'.$record->id) }}</div>
                    <div class="history-meta">
                        <span>{{ $record->created_at ? $record->created_at->format('M d, Y h:i A') : '' }}</span>
                        @if($record->vehicle ?? null)
                            <span>{{ $record->vehicle->year ?? '' }} {{ $record->vehicle->make ?? '' }} {{ $record->vehicle->model ?? '' }}</span>
                        @endif
                    </div>
                </div>
                <div class="history-status {{ strtolower(str_replace(' ', '-', $record->status ?? 'info')) }}">
                    {{ $record->statusLabel ?? ($record->status ?? '') }}
                </div>
            </div>
            @empty
            <div class="blank-slate">
                <i class="fas fa-inbox"></i>
                <p>No previous records found for this customer</p>
            </div>
            @endforelse
        </div>
    </div>
</div>
@endif
