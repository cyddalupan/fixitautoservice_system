<div class="col-6 col-md-4 col-lg-2">
    <div class="card summary-card h-100">
        <div class="card-body p-2">
            <div class="d-flex justify-content-between align-items-start mb-1">
                <span class="summary-label">{{ $label }}</span>
                <div class="summary-icon" style="background: {{ $color }}12;">
                    <i class="fas fa-{{ $icon }}" style="color: {{ $color }}; font-size: .8rem;"></i>
                </div>
            </div>
            <div class="summary-value">{{ $value }}</div>
            <div class="summary-sublabel">{!! $sublabel !!}</div>
        </div>
    </div>
</div>
