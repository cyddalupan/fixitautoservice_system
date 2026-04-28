<div class="findings-card finding-item-{{ $finding->id }}" data-finding-id="{{ $finding->id }}" data-sort="{{ $finding->sort_order }}">
    <div class="severity-indicator {{ $finding->severity }}"></div>
    <div class="row align-items-start g-2">
        <div class="col-md-8">
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-{{ $categoryBadge[$finding->category] ?? 'secondary' }}" style="font-size:10px;">{{ $finding->category }}</span>
                <span class="finding-title">{{ $finding->issue_title }}</span>
            </div>
            @if($finding->detailed_notes)
                <p class="finding-meta mb-0">{{ Str::limit($finding->detailed_notes, 120) }}</p>
            @endif
            @if($finding->recommended_action)
                <p class="finding-meta mb-0">
                    <i class="fas fa-wrench me-1" style="opacity:0.5;"></i>{{ Str::limit($finding->recommended_action, 100) }}
                </p>
            @endif
        </div>
        <div class="col-md-4">
            <div class="d-flex flex-wrap gap-1 justify-content-md-end align-items-center">
                <span class="badge bg-{{ $severityColors[$finding->severity] ?? 'secondary' }}" style="font-size:9px;">
                    <i class="fas {{ $severityIcons[$finding->severity] ?? 'fa-minus' }} me-1"></i>
                    {{ ucfirst($finding->severity) }}
                </span>
                <span class="badge bg-{{ $urgencyColors[$finding->estimated_urgency] ?? 'secondary' }}" style="font-size:9px;">
                    {{ ucfirst($finding->estimated_urgency) }}
                </span>
                @if($finding->estimated_cost)
                    <span class="badge bg-light text-dark border" style="font-size:9px;">
                        ₱{{ number_format($finding->estimated_cost, 2) }}
                    </span>
                @endif
            </div>
            <div class="finding-meta mt-1 text-md-end">
                @if($finding->technician)
                    <i class="fas fa-user-cog me-1" style="opacity:0.5;"></i>{{ $finding->technician->name }}
                @endif
                @if($finding->created_at)
                    <br><small>{{ $finding->created_at->format('M j, g:i A') }}</small>
                @endif
            </div>
        </div>
    </div>
    <div class="finding-actions position-absolute" style="top:8px;right:12px;">
        <button class="btn btn-sm btn-light border" onclick="editFinding({{ $finding->id }})" title="Edit">
            <i class="fas fa-pen text-primary"></i>
        </button>
        <button class="btn btn-sm btn-light border" onclick="deleteFinding({{ $finding->id }})" title="Delete">
            <i class="fas fa-trash text-danger"></i>
        </button>
    </div>
</div>
