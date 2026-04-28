{{--
  ========================================================
  TECHNICIAN SELECTOR COMPONENT v2
  ========================================================
  A clean, self-contained multi-technician selector.
  
  Works on both CREATE and EDIT pages.
  
  Usage:
    @include('partials.technician-selector', [
        'technicians' => $allTechnicians,    // Collection of Users (role=technician)
        'selectedIds' => [],                  // Array of pre-selected IDs
        'fieldName' => 'technicians',         // Form field name
        'label' => 'Additional Technicians',  // Label text
        'helpText' => '...',                  // Help text below selector
    ])
  ========================================================
--}}

@php
    $selectedIds = $selectedIds ?? [];
    if (is_object($selectedIds)) {
        $selectedIds = $selectedIds->toArray();
    }
    $fieldName = $fieldName ?? 'technicians';
    $label = $label ?? 'Additional Technicians';
    $helpText = $helpText ?? 'Assign additional technicians to this job';
    $wrapperId = 'tech-select-' . md5($fieldName . '-' . uniqid());
    $selectedIds = array_map('strval', $selectedIds);
@endphp

<div class="form-group">
    <label class="form-label">{{ $label }}</label>
    
    <div class="technician-select-wrapper" id="{{ $wrapperId }}" data-tech-init="true">
        <!-- Tags container + hidden inputs -->
        <div class="technician-tags">
            @forelse($selectedIds as $selId)
                <span class="technician-tag" data-id="{{ $selId }}">
                    {{ $technicians->firstWhere('id', $selId)->name ?? 'Technician #'.$selId }}
                    <button type="button" class="remove-tech-btn" data-id="{{ $selId }}">&times;</button>
                </span>
                <input type="hidden" name="{{ $fieldName }}[]" value="{{ $selId }}">
            @empty
                <input type="hidden" name="{{ $fieldName }}[]" value="">
            @endforelse
        </div>
        
        <!-- Add button -->
        <button type="button" class="btn btn-outline-primary btn-sm tech-add-btn" style="font-size: 0.82rem;">
            <i class="fas fa-plus me-1"></i> Add Technician
        </button>
        
        <!-- Dropdown -->
        <div class="technician-dropdown" style="display:none;">
            <input type="text" class="search-input" placeholder="Search technicians by name..." autocomplete="off">
            <div class="tech-options-list">
                @foreach($technicians as $tech)
                    <div class="tech-option {{ in_array(strval($tech->id), $selectedIds) ? 'selected' : '' }}"
                         data-id="{{ $tech->id }}"
                         data-name="{{ $tech->name }}">
                        <span class="tech-check {{ in_array(strval($tech->id), $selectedIds) ? 'checked' : '' }}">@if(in_array(strval($tech->id), $selectedIds))&#10003;@endif</span>
                        <span>{{ $tech->name }}</span>
                        @if(isset($tech->specialization) && $tech->specialization)
                            <span class="tech-role-tag">{{ $tech->specialization }}</span>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    
    @if($helpText)
        <small class="text-muted">{{ $helpText }}</small>
    @endif
</div>

@push('scripts')
<script>
(function() {
    'use strict';
    
    var wrapperId = '{{ $wrapperId }}';
    var $wrapper = $('#' + wrapperId);
    if (!$wrapper.length) return;
    
    // If already initialized, skip
    if ($wrapper.data('tech-component-ready')) return;
    $wrapper.data('tech-component-ready', true);
    
    var $tags = $wrapper.find('.technician-tags');
    var $addBtn = $wrapper.find('.tech-add-btn');
    var $dropdown = $wrapper.find('.technician-dropdown');
    var $searchInput = $dropdown.find('.search-input');
    var $options = $dropdown.find('.tech-option');
    var fieldName = '{{ $fieldName }}';
    
    // Collect initially selected IDs (skip empty string slot-machine)
    var selectedIds = [];
    $wrapper.find('input[name="' + fieldName + '[]"]').each(function() {
        var val = $(this).val();
        if (val && val !== '') selectedIds.push(String(val));
    });
    
    // --- Dropdown toggle ---
    $addBtn.on('click', function(e) {
        e.stopPropagation();
        if ($dropdown.is(':visible')) {
            $dropdown.slideUp(150);
        } else {
            $dropdown.slideDown(150);
            $searchInput.focus();
            $searchInput.val('').trigger('input');
        }
    });
    
    // Close dropdown on outside click
    $(document).on('click.techclose', function(e) {
        if (!$wrapper.is(e.target) && $wrapper.has(e.target).length === 0) {
            $dropdown.slideUp(150);
        }
    });
    
    // --- Search filter ---
    $searchInput.on('input', function() {
        var q = $(this).val().toLowerCase().trim();
        $options.each(function() {
            // Don't show already-selected techs in the dropdown list
            if ($(this).hasClass('selected')) {
                $(this).hide();
                return;
            }
            var name = $(this).data('name').toLowerCase();
            $(this).toggle(name.indexOf(q) !== -1);
        });
    });
    
    // Initial: hide already-selected options on load
    $options.each(function() {
        if ($(this).hasClass('selected')) {
            $(this).hide();
            $(this).fadeTo(0, 0.4);
        }
    });
    
    // --- Select/deselect ---
    $options.on('click', function(e) {
        e.stopPropagation();
        var id = String($(this).data('id'));
        
        // Ignore click if already selected (can't double-pick)
        if ($(this).hasClass('selected')) {
            return;
        }
        
        var idx = selectedIds.indexOf(id);
        if (idx === -1) {
            selectedIds.push(id);
            $(this).addClass('selected');
            $(this).find('.tech-check').addClass('checked').html('&#10003;');
            $(this).fadeTo(200, 0.4);
            rebuild();
        }
    });
    
    // --- Remove via tag X ---
    $tags.on('click', '.remove-tech-btn', function() {
        var id = String($(this).data('id'));
        var idx = selectedIds.indexOf(id);
        if (idx > -1) {
            selectedIds.splice(idx, 1);
            rebuild();
        }
        return false;
    });
    
    // --- Show/hide options after remove via dropdown ---
    function refreshOptionsDisplay() {
        $options.each(function() {
            var id = String($(this).data('id'));
            if (selectedIds.indexOf(id) > -1) {
                $(this).addClass('selected');
                $(this).find('.tech-check').addClass('checked').html('&#10003;');
                $(this).hide();
            } else {
                $(this).removeClass('selected');
                $(this).find('.tech-check').removeClass('checked').html('');
                $(this).show();
                $(this).fadeTo(0, 1);
            }
            // Apply search filter to visible options
            var q = $searchInput.val().toLowerCase().trim();
            if (q && !$(this).hasClass('selected')) {
                var name = $(this).data('name').toLowerCase();
                if (name.indexOf(q) === -1) {
                    $(this).hide();
                }
            }
        });
    }
    
    // --- Rebuild tags + hidden inputs ---
    function rebuild() {
        // Remove old tags and hidden inputs (keep all children except those we manage)
        $tags.find('.technician-tag').remove();
        $tags.find('input[name="' + fieldName + '[]"]').remove();
        
        if (selectedIds.length === 0) {
            // Slot-machine empty input so controllers receive empty array
            $tags.append('<input type="hidden" name="' + fieldName + '[]" value="">');
        } else {
            selectedIds.forEach(function(id) {
                var $opt = $options.filter('[data-id="' + id + '"]');
                var name = $opt.length ? $opt.data('name') : 'Technician #' + id;
                
                var $tag = $('<span class="technician-tag" data-id="' + id + '">' +
                    $('<span>').text(name).html() +
                    ' <button type="button" class="remove-tech-btn" data-id="' + id + '">&times;</button>' +
                    '</span>');
                $tags.append($tag);
                $tags.append('<input type="hidden" name="' + fieldName + '[]" value="' + id + '">');
            });
        }
        
        refreshOptionsDisplay();
    }
    
    // Initial render
    rebuild();
})();
</script>
@endpush
