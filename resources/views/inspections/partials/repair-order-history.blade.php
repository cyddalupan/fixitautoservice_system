{{-- Repair Order History — chronological "what happened" feed assembled from the
     record + its related records. Data comes from $inspection->activityHistory(). --}}
@php
    $history = $history ?? $inspection->activityHistory();
@endphp
<div class="form-section mt-4" id="repair-order-history">
    <div class="form-section-header no-collapse">
        <h6><i class="fas fa-clock-rotate-left"></i>Repair Order History</h6>
        <span class="badge bg-secondary ms-2" style="font-size:11px;">{{ count($history) }} {{ \Illuminate\Support\Str::plural('event', count($history)) }}</span>
    </div>
    <div class="form-section-body">
        @if(count($history))
            <div class="ro-history">
                @foreach($history as $entry)
                    <div class="ro-history-item d-flex">
                        <div class="ro-history-rail">
                            <span class="ro-history-dot text-{{ $entry['color'] }}">
                                <i class="fas {{ $entry['icon'] }}"></i>
                            </span>
                        </div>
                        <div class="ro-history-body pb-3">
                            <div class="fw-semibold" style="font-size:13.5px;">{{ $entry['title'] }}</div>
                            @if($entry['detail'])
                                <div class="text-muted" style="font-size:12.5px;">{{ $entry['detail'] }}</div>
                            @endif
                            <div class="text-muted" style="font-size:11.5px;">
                                <i class="far fa-clock me-1"></i>{{ $entry['time']->format('M j, Y g:i A') }}
                                <span class="text-muted">· {{ $entry['time']->diffForHumans() }}</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-muted mb-0 small">No history yet for this Repair Order.</p>
        @endif
    </div>
</div>

@push('styles')
<style>
    .ro-history-item:last-child .ro-history-body { padding-bottom: 0 !important; }
    .ro-history-rail {
        position: relative;
        width: 34px;
        flex: 0 0 34px;
        display: flex;
        justify-content: center;
    }
    /* vertical connector line */
    .ro-history-item:not(:last-child) .ro-history-rail::after {
        content: "";
        position: absolute;
        top: 26px;
        bottom: -4px;
        width: 2px;
        background: #e6ebf3;
    }
    .ro-history-dot {
        width: 26px; height: 26px;
        border-radius: 50%;
        background: #fff;
        border: 2px solid currentColor;
        display: inline-flex; align-items: center; justify-content: center;
        font-size: 11px;
        z-index: 1;
    }
</style>
@endpush
