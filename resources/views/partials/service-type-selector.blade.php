{{-- 
===================================================================
  Shared Service Type Selector (Professional Chip Card Style)
  ===================================================================
  Usage:
    @include('partials.service-type-selector', [
        'selected' => old('service_type', $service_type ?? $record->service_type ?? ''),
        'name' => 'service_type',
        'label' => 'Service Type',
        'required' => true,
        'showIcons' => true,
        'multiple' => true,
        'placeholder' => 'Select Service Type',
        'module' => 'appointments',
    ])
===================================================================
--}}

@php
    $serviceTypes = \App\Models\ServiceType::list();
    $serviceIcons = \App\Models\ServiceType::icons();
    $name = $name ?? 'service_type';
    $label = $label ?? 'SERVICE TYPE';
    $required = $required ?? false;
    $showIcons = $showIcons ?? true;
    $multiple = $multiple ?? true;
    $placeholder = $placeholder ?? 'Select Service Type';
    $module = $module ?? 'appointments';
    $selected = $selected ?? '';
    $inputId = str_replace(['[',']', '.'], '_', $name);
    $inputMode = $multiple ? 'checkbox' : 'radio';
@endphp

<div class="form-group mb-4">
    <label class="form-label fw-semibold {{ $required ? 'field-required' : '' }}" style="font-size:0.85rem; letter-spacing:0.3px; color:#495057; margin-bottom:10px;">
        <i class="fas fa-tools me-1" style="color:#6c5ce7;"></i>{{ $label }}
    </label>

    <div class="service-card-grid">
        @foreach($serviceTypes as $key => $displayName)
            @php
                if ($multiple) {
                    $isSelected = is_array($selected) ? in_array($key, $selected) : (is_string($selected) && $selected === $key);
                } else {
                    $isSelected = (string)$selected === (string)$key;
                }
                $icon = $showIcons && isset($serviceIcons[$key]) ? $serviceIcons[$key] : 'fas fa-wrench';
            @endphp
            <label class="service-card {{ $isSelected ? 'selected' : '' }}">
                <input type="{{ $inputMode }}" 
                       name="{{ $name }}{{ $multiple ? '[]' : '' }}" 
                       value="{{ $key }}"
                       {{ $isSelected ? 'checked' : '' }}
                       {{ $required && !$multiple ? 'required' : '' }}>
                <div class="service-card-content">
                    <span class="service-card-icon"><i class="{{ $icon }}"></i></span>
                    <span class="service-card-text">{{ $displayName }}</span>
                </div>
                <span class="service-card-check"><i class="fas fa-check-circle"></i></span>
            </label>
        @endforeach
    </div>

    @error($name)
        <div class="invalid-feedback d-block mt-1">{{ $message }}</div>
    @enderror
    @error(str_replace('.', '_', $name) . '.*')
        <div class="invalid-feedback d-block mt-1">{{ $message }}</div>
    @enderror
    <small class="text-muted d-block mt-1" style="font-size:0.78rem;">
        <i class="fas fa-info-circle me-1"></i>{{ $multiple ? 'You may select one or more service types.' : 'Choose the type of service being performed.' }}
    </small>
</div>

@push('styles')
<style>
.service-card-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(175px, 1fr));
    gap: 10px;
}

.service-card {
    position: relative;
    cursor: pointer;
    border: 1.5px solid #e0e0e0;
    border-radius: 10px;
    padding: 14px 12px 14px 14px;
    background: #fff;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    justify-content: space-between;
    user-select: none;
    min-height: 52px;
}

.service-card:hover {
    border-color: #a29bfe;
    background: #f8f7ff;
    box-shadow: 0 2px 8px rgba(108,92,231,0.08);
}

.service-card input {
    position: absolute;
    opacity: 0;
    width: 0;
    height: 0;
}

.service-card.selected {
    border-color: #6c5ce7;
    background: linear-gradient(135deg, #f5f3ff 0%, #ede9fe 100%);
    box-shadow: 0 2px 12px rgba(108,92,231,0.12);
}

.service-card-content {
    display: flex;
    align-items: center;
    gap: 10px;
    flex: 1;
    min-width: 0;
}

.service-card-icon {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    background: #f0edff;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    transition: all 0.2s ease;
}

.service-card.selected .service-card-icon {
    background: #6c5ce7;
    color: #fff;
}

.service-card-icon i {
    font-size: 0.85rem;
    color: #6c5ce7;
}

.service-card.selected .service-card-icon i {
    color: #fff;
}

.service-card-text {
    font-size: 0.82rem;
    font-weight: 500;
    color: #495057;
    line-height: 1.3;
    transition: color 0.2s;
}

.service-card.selected .service-card-text {
    color: #2d1b69;
    font-weight: 600;
}

.service-card-check {
    opacity: 0;
    transition: all 0.2s ease;
    flex-shrink: 0;
    margin-left: 6px;
}

.service-card.selected .service-card-check {
    opacity: 1;
}

.service-card-check i {
    font-size: 1rem;
    color: #6c5ce7;
}

/* Reduced grid for smaller containers */
.service-card-grid.small {
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const cards = document.querySelectorAll('.service-card');
    cards.forEach(function(card) {
        const input = card.querySelector('input');
        input.addEventListener('change', function() {
            if (this.type === 'radio') {
                // For radio/ single-select: uncheck siblings
                const siblings = this.closest('.service-card-grid').querySelectorAll('.service-card');
                siblings.forEach(function(sib) { sib.classList.remove('selected'); });
                if (this.checked) card.classList.add('selected');
            } else {
                // For checkbox: toggle
                card.classList.toggle('selected');
            }
        });
    });
});
</script>
@endpush
