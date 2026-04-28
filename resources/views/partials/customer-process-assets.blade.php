@once
<style>
/* ===== CUSTOMER PROCESS PAGES UNIFIED STYLES ===== */

/* Customer Summary Card */
.customer-summary-card {
    background: linear-gradient(135deg, #1a237e 0%, #283593 50%, #3949ab 100%);
    border-radius: 12px;
    color: #fff;
    padding: 0;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(26, 35, 126, 0.25);
    margin-bottom: 24px;
}

.customer-summary-card .card-body {
    padding: 20px 24px;
}

.customer-summary-card .customer-avatar {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    font-weight: 700;
    flex-shrink: 0;
}

.customer-summary-card .customer-name {
    font-size: 1.25rem;
    font-weight: 700;
    margin-bottom: 2px;
}

.customer-summary-card .customer-subtitle {
    font-size: 0.85rem;
    opacity: 0.85;
}

.customer-summary-card .info-badge {
    background: rgba(255, 255, 255, 0.15);
    border-radius: 8px;
    padding: 8px 12px;
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.8rem;
    white-space: nowrap;
}

.customer-summary-card .info-badge i {
    font-size: 0.85rem;
    opacity: 0.8;
}

.customer-summary-card .vehicle-pills {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
}

.customer-summary-card .vehicle-pill {
    background: rgba(255, 255, 255, 0.12);
    border: 1px solid rgba(255, 255, 255, 0.15);
    border-radius: 20px;
    padding: 4px 12px;
    font-size: 0.75rem;
    cursor: pointer;
    transition: all 0.15s;
}

.customer-summary-card .vehicle-pill:hover {
    background: rgba(255, 255, 255, 0.2);
}

.customer-summary-card .vehicle-pill.active {
    background: rgba(255, 255, 255, 0.25);
    border-color: rgba(255, 255, 255, 0.4);
}

.customer-summary-card .stat-item {
    text-align: center;
    padding: 8px 12px;
    border-right: 1px solid rgba(255, 255, 255, 0.1);
}

.customer-summary-card .stat-item:last-child {
    border-right: none;
}

.customer-summary-card .stat-value {
    font-size: 1.3rem;
    font-weight: 700;
}

.customer-summary-card .stat-label {
    font-size: 0.7rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    opacity: 0.75;
}

.customer-summary-card .warning-badge {
    background: rgba(255, 193, 7, 0.2);
    border: 1px solid rgba(255, 193, 7, 0.3);
    border-radius: 6px;
    padding: 6px 12px;
    font-size: 0.78rem;
    color: #ffecb3;
    display: flex;
    align-items: center;
    gap: 6px;
}

.customer-summary-card .danger-badge {
    background: rgba(244, 67, 54, 0.2);
    border: 1px solid rgba(244, 67, 54, 0.3);
    border-radius: 6px;
    padding: 6px 12px;
    font-size: 0.78rem;
    color: #ffcdd2;
    display: flex;
    align-items: center;
    gap: 6px;
}

/* Form Sections */
.form-section {
    background: #fff;
    border-radius: 10px;
    border: 1px solid #e8eaf6;
    margin-bottom: 20px;
    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.04);
}

.form-section-header {
    background: #f5f7ff;
    padding: 14px 20px;
    border-bottom: 1px solid #e8eaf6;
    display: flex;
    align-items: center;
    justify-content: space-between;
    cursor: pointer;
    user-select: none;
    border-radius: 10px 10px 0 0;
}

.form-section-header:hover {
    background: #eef0ff;
}

.form-section-header h6 {
    margin: 0;
    font-weight: 600;
    font-size: 0.9rem;
    color: #1a237e;
}

.form-section-header h6 i {
    width: 20px;
    text-align: center;
    margin-right: 8px;
    color: #3949ab;
}

.form-section-body {
    padding: 20px;
}

.form-section.collapsed .form-section-body {
    display: none;
}

.form-section.collapsed .form-section-header {
    border-radius: 10px;
}

/* Technician Multi-Select */
.technician-select-wrapper {
    position: relative;
}

.technician-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    margin-bottom: 8px;
}

.technician-tag {
    background: #e8eaf6;
    border: 1px solid #c5cae9;
    border-radius: 20px;
    padding: 4px 10px 4px 14px;
    font-size: 0.82rem;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    animation: tagIn 0.2s ease;
}

.technician-tag .tag-role {
    font-size: 0.7rem;
    color: #5c6bc0;
    font-weight: 500;
}

.technician-tag .remove-tech {
    width: 18px;
    height: 18px;
    border-radius: 50%;
    border: none;
    background: rgba(0, 0, 0, 0.15);
    color: #333;
    font-size: 10px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: background 0.15s;
    line-height: 1;
    padding: 0;
}

.technician-tag .remove-tech:hover {
    background: rgba(244, 67, 54, 0.3);
}

@keyframes tagIn {
    from { transform: scale(0.8); opacity: 0; }
    to { transform: scale(1); opacity: 1; }
}

.technician-dropdown {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    z-index: 1000;
    background: #fff;
    border: 1px solid #c5cae9;
    border-radius: 8px;
    box-shadow: 0 8px 24px rgba(0,0,0,0.1);
    max-height: 260px;
    overflow-y: auto;
    display: none;
    margin-top: 4px;
}

.technician-dropdown.show {
    display: block;
}

.technician-dropdown .search-input {
    width: 100%;
    border: none;
    border-bottom: 1px solid #e8eaf6;
    padding: 10px 14px;
    font-size: 0.85rem;
    outline: none;
}

.technician-dropdown .search-input:focus {
    border-bottom-color: #3949ab;
}

.technician-dropdown .tech-option {
    padding: 8px 14px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 0.85rem;
    transition: background 0.1s;
    border-bottom: 1px solid #f5f5f5;
}

.technician-dropdown .tech-option:hover {
    background: #e8eaf6;
}

.technician-dropdown .tech-option .tech-check {
    width: 18px;
    height: 18px;
    border-radius: 3px;
    border: 2px solid #9e9e9e;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    transition: all 0.15s;
}

.technician-dropdown .tech-option.selected .tech-check {
    background: #3949ab;
    border-color: #3949ab;
}

.technician-dropdown .tech-option.selected .tech-check::after {
    content: '✓';
    color: #fff;
    font-size: 11px;
    font-weight: 700;
}

.technician-dropdown .tech-option .tech-role-tag {
    font-size: 0.65rem;
    background: #f0f0f0;
    border-radius: 10px;
    padding: 1px 8px;
    color: #666;
    margin-left: auto;
}

.technician-dropdown .role-select-wrapper {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-left: auto;
}

.technician-dropdown .role-select-wrapper .role-tag {
    font-size: 0.62rem;
    color: #999;
    background: #f5f5f5;
    border: 1px solid #e0e0e0;
    border-radius: 4px;
    padding: 2px 8px;
    cursor: pointer;
}

/* Sticky Save Bar */
.sticky-save-bar {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    background: #fff;
    border-top: 1px solid #e0e0e0;
    box-shadow: 0 -4px 20px rgba(0,0,0,0.1);
    padding: 12px 24px;
    z-index: 1030;
    display: none;
    align-items: center;
    justify-content: space-between;
    transition: transform 0.3s ease;
}

.sticky-save-bar.visible {
    display: flex;
}

.sticky-save-bar .save-info {
    font-size: 0.8rem;
    color: #666;
    display: flex;
    align-items: center;
    gap: 8px;
}

.sticky-save-bar .auto-save-indicator {
    font-size: 0.75rem;
    color: #4caf50;
}

.sticky-save-bar .auto-save-indicator.saving {
    color: #ff9800;
}

/* Section Navigation */
.section-nav {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    margin-bottom: 20px;
    padding: 4px;
    background: #f5f5f5;
    border-radius: 10px;
}

.section-nav .nav-pill {
    padding: 8px 16px;
    border-radius: 8px;
    font-size: 0.82rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
    color: #666;
    background: transparent;
    border: none;
    white-space: nowrap;
}

.section-nav .nav-pill:hover {
    background: rgba(57, 73, 171, 0.08);
    color: #3949ab;
}

.section-nav .nav-pill.active {
    background: #3949ab;
    color: #fff;
    box-shadow: 0 2px 8px rgba(57, 73, 171, 0.25);
}

.section-nav .nav-pill i {
    margin-right: 6px;
}

/* History Section */
.history-section {
    margin-top: 24px;
}

.history-card {
    background: #fff;
    border: 1px solid #e0e0e0;
    border-radius: 10px;
    overflow: hidden;
}

.history-card-header {
    background: #fafafa;
    padding: 12px 16px;
    border-bottom: 1px solid #e0e0e0;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.history-card-header h6 {
    margin: 0;
    font-size: 0.85rem;
    font-weight: 600;
}

.history-card-body {
    padding: 12px 16px;
    max-height: 320px;
    overflow-y: auto;
}

.history-item {
    padding: 10px 12px;
    border-bottom: 1px solid #f0f0f0;
    display: flex;
    align-items: center;
    gap: 12px;
    transition: background 0.1s;
    cursor: pointer;
    border-radius: 6px;
}

.history-item:hover {
    background: #f5f7ff;
}

.history-item:last-child {
    border-bottom: none;
}

.history-item .history-icon {
    width: 36px;
    height: 36px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 0.85rem;
}

.history-item .history-icon.completed { background: #e8f5e9; color: #2e7d32; }
.history-item .history-icon.pending { background: #fff8e1; color: #f57f17; }
.history-item .history-icon.cancelled { background: #ffebee; color: #c62828; }
.history-item .history-icon.info { background: #e3f2fd; color: #1565c0; }

.history-item .history-info {
    flex: 1;
    min-width: 0;
}

.history-item .history-title {
    font-size: 0.85rem;
    font-weight: 500;
    margin-bottom: 2px;
}

.history-item .history-meta {
    font-size: 0.72rem;
    color: #888;
    display: flex;
    gap: 12px;
}

.history-item .history-status {
    font-size: 0.7rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.history-item .history-status.completed { color: #2e7d32; }
.history-item .history-status.pending { color: #f57f17; }
.history-item .history-status.cancelled { color: #c62828; }
.history-item .history-status.scheduled { color: #1565c0; }

/* Validation feedback */
.is-invalid ~ .invalid-feedback {
    display: block;
}

.form-group .field-required::after {
    content: ' *';
    color: #e53935;
}

/* Draft auto-save indicator */
.auto-save-toast {
    position: fixed;
    top: 16px;
    right: 16px;
    background: #323232;
    color: #fff;
    padding: 10px 18px;
    border-radius: 8px;
    font-size: 0.82rem;
    z-index: 9999;
    display: none;
    animation: slideInRight 0.3s ease;
    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
}

.auto-save-toast i {
    margin-right: 6px;
}

@keyframes slideInRight {
    from { transform: translateX(100px); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
}

/* Quick note templates */
.quick-note-btn {
    font-size: 0.75rem;
    padding: 2px 10px;
    border-radius: 12px;
    background: #e8eaf6;
    border: 1px solid #c5cae9;
    color: #3949ab;
    cursor: pointer;
    transition: all 0.15s;
    white-space: nowrap;
}

.quick-note-btn:hover {
    background: #c5cae9;
}

/* Loading spinner */
.loading-spinner {
    display: inline-block;
    width: 16px;
    height: 16px;
    border: 2px solid rgba(255,255,255,0.3);
    border-radius: 50%;
    border-top-color: #fff;
    animation: spin 0.6s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

/* Section blank slate */
.blank-slate {
    text-align: center;
    padding: 30px 20px;
    color: #999;
}

.blank-slate i {
    font-size: 2rem;
    margin-bottom: 12px;
    opacity: 0.4;
}

.blank-slate p {
    font-size: 0.85rem;
    margin: 0;
}

/* Mode switcher */
.tech-mode-switcher {
    display: flex;
    gap: 4px;
    padding: 3px;
    background: #f0f0f0;
    border-radius: 6px;
    margin-bottom: 12px;
}

.tech-mode-btn {
    padding: 6px 14px;
    border: none;
    border-radius: 4px;
    font-size: 0.78rem;
    cursor: pointer;
    transition: all 0.15s;
    background: transparent;
    color: #666;
    font-weight: 500;
}

.tech-mode-btn.active {
    background: #3949ab;
    color: #fff;
}

/* Mobile adjustments */
@media (max-width: 768px) {
    .customer-summary-card .card-body {
        padding: 16px;
    }
    
    .customer-summary-card .customer-avatar {
        width: 44px;
        height: 44px;
        font-size: 18px;
    }
    
    .customer-summary-card .stat-item {
        padding: 6px 8px;
    }
    
    .customer-summary-card .stat-value {
        font-size: 1.1rem;
    }
    
    .form-section-body {
        padding: 14px;
    }
    
    .section-nav {
        overflow-x: auto;
        flex-wrap: nowrap;
        -webkit-overflow-scrolling: touch;
    }
    
    .sticky-save-bar {
        padding: 10px 16px;
        flex-direction: column;
        gap: 8px;
    }
    
    .sticky-save-bar .save-info {
        font-size: 0.72rem;
    }
    
    .edit-inspection-link {
        display: none;
    }
}
</style>

<script>
// ===== SHARED FUNCTIONALITY FOR ALL CUSTOMER PROCESS PAGES =====

$(document).ready(function() {
    console.log('=== Customer Process Assets Loaded ===');
    
    // Initialize auto-save draft
    initAutoSave();
    
    // Initialize form validation
    initFormValidation();
    
    // Initialize section collapsible
    initFormSections();
    
    // Initialize sticky save bar
    initStickySaveBar();
    
    // Initialize technician multi-select (reusable function)
    initTechnicianMultiSelect();
    
    // Initialize customer history
    initCustomerHistory();
    
    // Initialize vehicle selector
    initVehicleSelector();
});

// ===== STICKY SAVE BAR =====
function initStickySaveBar() {
    var $saveBar = $('.sticky-save-bar');
    if (!$saveBar.length) return;
    
    var formBottom = $('form').height() + $('form').offset().top;
    
    function checkSticky() {
        var scrollY = $(window).scrollTop();
        var windowHeight = $(window).height();
        
        if (formBottom > scrollY + windowHeight) {
            $saveBar.addClass('visible');
        } else {
            // Only hide if we're near the bottom of the page
            if (scrollY + windowHeight >= $(document).height() - 50) {
                $saveBar.removeClass('visible');
            } else {
                $saveBar.addClass('visible');
            }
        }
    }
    
    $(window).on('scroll resize', checkSticky);
    setTimeout(checkSticky, 100);
}

// ===== FORM SECTIONS =====
function initFormSections() {
    $('.form-section-header').off('click').on('click', function() {
        var $section = $(this).closest('.form-section');
        var isCollapsed = $section.hasClass('collapsed');
        
        if (isCollapsed) {
            $section.removeClass('collapsed');
            $(this).find('.collapse-icon i').removeClass('fa-chevron-right').addClass('fa-chevron-down');
        } else {
            $section.addClass('collapsed');
            $(this).find('.collapse-icon i').removeClass('fa-chevron-down').addClass('fa-chevron-right');
        }
    });
}

// ===== SECTION NAVIGATION =====
function scrollToSection(sectionId) {
    $('.section-nav .nav-pill').removeClass('active');
    $('.section-nav .nav-pill[data-section="' + sectionId + '"]').addClass('active');
    
    var $target = $('#' + sectionId);
    if ($target.length) {
        $('html, body').animate({
            scrollTop: $target.offset().top - 80
        }, 400);
    }
}

// ===== AUTO-SAVE DRAFT =====
function initAutoSave() {
    var pageKey = window.location.pathname + window.location.search;
    var $form = $('form');
    if (!$form.length) return;
    
    // Load saved draft
    var saved = localStorage.getItem('draft_' + pageKey);
    if (saved) {
        try {
            var data = JSON.parse(saved);
            var age = Date.now() - (data.savedAt || 0);
            var minAgo = Math.floor(age / 60000);
            
            if (minAgo < 120) { // Only restore if less than 2 hours old
                // Restore text inputs, textareas, selects
                for (var key in data.fields) {
                    var $field = $form.find('[name="' + key + '"]');
                    if ($field.length && !$field.is(':hidden, :disabled')) {
                        if ($field.is('select')) {
                            $field.val(data.fields[key]);
                        } else if ($field.is('textarea') || $field.is('input:not([type=hidden])')) {
                            // Only restore if current value is empty
                            if (!$field.val() || $field.val() === $field.data('default')) {
                                $field.val(data.fields[key]);
                            }
                        }
                    }
                }
                
                if (minAgo > 0) {
                    showAutoSaveToast('Restored draft from ' + minAgo + ' min ago');
                }
            } else {
                localStorage.removeItem('draft_' + pageKey);
            }
        } catch(e) {}
    }
    
    // Save draft on input change (debounced)
    var saveTimer;
    $form.on('input change', 'input:not([type=hidden]), select, textarea', function() {
        clearTimeout(saveTimer);
        saveTimer = setTimeout(function() {
            saveDraft(pageKey, $form);
        }, 2000);
    });
    
    // Clear draft on successful form submit
    $form.on('submit', function() {
        localStorage.removeItem('draft_' + pageKey);
    });
    
    // Prevent accidental refresh
    var formChanged = false;
    $form.find('input:not([type=hidden]), select, textarea').one('change', function() {
        formChanged = true;
    });
    
    $(window).on('beforeunload', function() {
        if (formChanged && !sessionStorage.getItem('form_submitted')) {
            return 'You have unsaved changes. Are you sure you want to leave?';
        }
    });
    
    $form.on('submit', function() {
        sessionStorage.setItem('form_submitted', '1');
    });
}

function saveDraft(pageKey, $form) {
    var fields = {};
    $form.find('[name]').each(function() {
        var $el = $(this);
        if (!$el.is(':hidden, :disabled')) {
            fields[$el.attr('name')] = $el.val();
        }
    });
    
    try {
        localStorage.setItem('draft_' + pageKey, JSON.stringify({
            fields: fields,
            savedAt: Date.now()
        }));
    } catch(e) {}
}

function showAutoSaveToast(msg) {
    var $toast = $('.auto-save-toast');
    if (!$toast.length) {
        $toast = $('<div class="auto-save-toast"><i class="fas fa-check-circle"></i> <span></span></div>');
        $('body').append($toast);
    }
    $toast.find('span').text(msg);
    $toast.fadeIn(200);
    setTimeout(function() {
        $toast.fadeOut(300);
    }, 2500);
}

// ===== FORM VALIDATION =====
function initFormValidation() {
    var $form = $('form');
    if (!$form.length) return;
    
    $form.on('submit', function(e) {
        var hasError = false;
        
        $(this).find('[required]').each(function() {
            if (!$(this).val()) {
                $(this).addClass('is-invalid');
                hasError = true;
            } else {
                $(this).removeClass('is-invalid');
            }
        });
        
        if (hasError) {
            e.preventDefault();
            // Scroll to first error
            var $firstError = $('.is-invalid:first');
            if ($firstError.length) {
                $('html, body').animate({
                    scrollTop: $firstError.offset().top - 100
                }, 300);
                $firstError.focus();
            }
            return false;
        }
    });
    
    // Clear validation on input
    $form.on('input change', '.is-invalid', function() {
        if ($(this).val()) {
            $(this).removeClass('is-invalid');
        }
    });
}

// ===== TECHNICIAN MULTI-SELECT =====
function initTechnicianMultiSelect(containerSelector) {
    var $container = containerSelector ? $(containerSelector) : $('.technician-select-wrapper');
    
    $container.each(function() {
        var $wrapper = $(this);
        
        // Skip wrappers managed by the new technician-selector component
        if ($wrapper.data('tech-component-ready') || $wrapper.data('tech-init')) {
            return;
        }
        var $input = $wrapper.find('input[name="technicians[]"]');
        var $dropdown = $wrapper.find('.technician-dropdown');
        var $tags = $wrapper.find('.technician-tags');
        var $searchInput = $dropdown.find('.search-input');
        
        if (!$input.length) return;
        
        var selected = [];
        
        // Load initial selected values
        $input.each(function() {
            var val = $(this).val();
            if (val) selected.push(val);
        });
        
        // Toggle dropdown
        $wrapper.on('click', '.tech-select-trigger', function(e) {
            e.stopPropagation();
            $dropdown.toggleClass('show');
            if ($dropdown.hasClass('show')) {
                $searchInput.focus();
            }
        });
        
        // Close dropdown on outside click
        $(document).on('click', function(e) {
            if (!$wrapper.is(e.target) && $wrapper.has(e.target).length === 0) {
                $dropdown.removeClass('show');
            }
        });
        
        // Search filter
        $searchInput.on('input', function() {
            var q = $(this).val().toLowerCase();
            $dropdown.find('.tech-option').each(function() {
                var name = $(this).data('name').toLowerCase();
                $(this).toggle(name.indexOf(q) !== -1);
            });
        });
        
        // Select/deselect technician
        $dropdown.on('click', '.tech-option', function() {
            var id = $(this).data('id');
            var name = $(this).data('name');
            var role = $(this).data('role') || '';
            var idx = selected.indexOf(id);
            
            if (idx > -1) {
                selected.splice(idx, 1);
                $(this).removeClass('selected');
                $(this).find('.tech-check').removeClass('checked');
            } else {
                selected.push(id);
                $(this).addClass('selected');
                $(this).find('.tech-check').addClass('checked');
            }
            
            updateSelectedInputs();
            updateTags();
        });
        
        // Update hidden inputs
        function updateSelectedInputs() {
            $wrapper.find('input[name="technicians[]"]').remove();
            selected.forEach(function(id) {
                $wrapper.append('<input type="hidden" name="technicians[]" value="' + id + '">');
            });
        }
        
        // Update tags display
        function updateTags() {
            $tags.empty();
            selected.forEach(function(id) {
                var $opt = $dropdown.find('.tech-option[data-id="' + id + '"]');
                var name = $opt.data('name');
                var role = $opt.data('role') || '';
                
                var $tag = $('<span class="technician-tag">' +
                    name + 
                    (role ? ' <span class="tag-role">(' + role + ')</span>' : '') +
                    ' <button type="button" class="remove-tech" data-id="' + id + '">&times;</button>' +
                    '</span>');
                $tags.append($tag);
            });
        }
        
        // Remove technician via tag
        $tags.on('click', '.remove-tech', function() {
            var id = $(this).data('id');
            var idx = selected.indexOf(id);
            if (idx > -1) {
                selected.splice(idx, 1);
                $dropdown.find('.tech-option[data-id="' + id + '"]').removeClass('selected');
                updateSelectedInputs();
                updateTags();
            }
        });
        
        // Initial render
        updateTags();
    });
}

// ===== CUSTOMER HISTORY =====
function initCustomerHistory() {
    var $historySection = $('.history-section');
    if (!$historySection.length) return;
    
    var customerId = $historySection.data('customer-id');
    var historyType = $historySection.data('type');
    
    if (!customerId) return;
    
    // Load history via AJAX
    $.get('/api/customer-history', {
        customer_id: customerId,
        type: historyType,
        limit: 10
    })
    .done(function(response) {
        renderHistory(response, $historySection);
    })
    .fail(function() {
        $historySection.find('.history-card-body').html(
            '<div class="blank-slate"><i class="fas fa-exclamation-circle"></i><p>Failed to load history</p></div>'
        );
    });
}

function renderHistory(data, $section) {
    var $body = $section.find('.history-card-body');
    if (!$body.length) return;
    
    if (!data || data.length === 0) {
        $body.html('<div class="blank-slate"><i class="fas fa-inbox"></i><p>No previous records found</p></div>');
        return;
    }
    
    var html = '';
    data.forEach(function(item) {
        var iconClass = getStatusIcon(item.status);
        var statusClass = (item.status || '').toLowerCase().replace(/\s+/g, '-');
        
        html += '<div class="history-item" onclick="window.location.href=\'' + item.url + '\'">';
        html += '<div class="history-icon ' + iconClass + '"><i class="' + item.icon + '"></i></div>';
        html += '<div class="history-info">';
        html += '<div class="history-title">' + item.title + '</div>';
        html += '<div class="history-meta">' + item.meta + '</div>';
        html += '</div>';
        html += '<div class="history-status ' + statusClass + '">' + item.status + '</div>';
        html += '</div>';
    });
    
    $body.html(html);
}

function getStatusIcon(status) {
    var s = (status || '').toLowerCase();
    if (s.indexOf('complet') > -1 || s.indexOf('done') > -1) return 'completed';
    if (s.indexOf('cancelled') > -1 || s.indexOf('cancel') > -1 || s.indexOf('no_show') > -1) return 'cancelled';
    if (s.indexOf('pend') > -1 || s.indexOf('wait') > -1) return 'pending';
    return 'info';
}

// ===== VEHICLE SELECTOR =====
function initVehicleSelector() {
    var $customerSelect = $('#customer_id');
    var $vehicleSelect = $('#vehicle_id');
    
    if (!$customerSelect.length || !$vehicleSelect.length) return;
    
    // If customer is already selected, load vehicles
    var initialCustomer = $customerSelect.val() || $customerSelect.data('selected');
    if (initialCustomer) {
        loadVehicles(initialCustomer, $vehicleSelect);
    }
    
    $customerSelect.on('change', function() {
        loadVehicles($(this).val(), $vehicleSelect);
    });
    
    // Also check for customer hidden input (pre-selected)
    var $customerHidden = $customerSelect.closest('form').find('input[name="customer_id"]');
    if ($customerHidden.length && !$customerSelect.val()) {
        loadVehicles($customerHidden.val(), $vehicleSelect);
    }
}

// ===== WORK ORDER ITEMS =====
var itemCounter = 0;
function addItem(type) {
    itemCounter++;
    var $container = $('#itemsContainer');
    var html = '<div class="work-order-item border rounded p-3 mb-2" style="background:#f8f9fa;">' +
        '<div class="row g-2 align-items-end">' +
        '<div class="col-md-3">' +
        '<label class="form-label small mb-1">Item Type</label>' +
        '<select class="form-select form-select-sm" name="items[' + itemCounter + '][type]">' +
        '<option value="labor">Labor</option>' +
        '<option value="part">Part</option>' +
        '<option value="sublet">Sublet</option>' +
        '<option value="other">Other</option>' +
        '</select></div>' +
        '<div class="col-md-4">' +
        '<label class="form-label small mb-1">Description</label>' +
        '<input type="text" class="form-control form-control-sm" name="items[' + itemCounter + '][description]" placeholder="Description">' +
        '</div>' +
        '<div class="col-md-1">' +
        '<label class="form-label small mb-1">Qty</label>' +
        '<input type="number" class="form-control form-control-sm" name="items[' + itemCounter + '][quantity]" value="1" min="1" step="1">' +
        '</div>' +
        '<div class="col-md-2">' +
        '<label class="form-label small mb-1">Unit Price (₱)</label>' +
        '<input type="number" class="form-control form-control-sm" name="items[' + itemCounter + '][unit_price]" value="0" min="0" step="0.01">' +
        '</div>' +
        '<div class="col-md-1">' +
        '<button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest(\'.work-order-item\').remove()"><i class="fas fa-trash"></i></button>' +
        '</div></div></div>';
    $container.prepend(html);
    $container.find('p.text-muted').remove();
}

// ===== TECHNICIAN ASSIGNMENTS (Work Order JSON) =====
var assignmentCounter = 0;
var selectedAssignments = [];

function addTechnicianAssignment() {
    var techId = $('#assignment_tech_select').val();
    var role = $('#assignment_role_input').val().trim();
    
    if (!techId) {
        alert('Please select a technician');
        return;
    }
    
    // Check for duplicate
    if (selectedAssignments.includes(techId)) {
        alert('Technician already assigned');
        return;
    }
    
    selectedAssignments.push(techId);
    assignmentCounter++;
    
    var techName = $('#assignment_tech_select option:selected').text();
    
    // Add hidden fields
    $('#technician_assignment_fields').append(
        '<input type="hidden" name="technician_assignments[' + assignmentCounter + '][technician_id]" value="' + techId + '">' +
        '<input type="hidden" name="technician_assignments[' + assignmentCounter + '][technician_name]" value="' + techName + '">' +
        '<input type="hidden" name="technician_assignments[' + assignmentCounter + '][role]" value="' + role + '">'
    );
    
    // Add tag
    $('#assignmentTagList').append(
        '<span class="badge bg-primary me-1 mb-1" style="font-size:0.85rem;padding:5px 12px;">' +
        techName + (role ? ' <span style="opacity:0.8">(' + role + ')</span>' : '') +
        ' <a href="#" onclick="removeTechnicianAssignment(\'' + techId + '\', this);return false;" style="color:white;margin-left:4px;">&times;</a>' +
        '</span>'
    );
    
    $('#assignment_tech_select').val('');
    $('#assignment_role_input').val('');
}

function removeTechnicianAssignment(techId, el) {
    var idx = selectedAssignments.indexOf(techId);
    if (idx > -1) {
        selectedAssignments.splice(idx, 1);
    }
    $(el).closest('.badge').remove();
    $('#technician_assignment_fields input[value="' + techId + '"]').closest('input').remove();
    // Remove associated hidden fields
    $('#technician_assignment_fields').find('input[value="' + techId + '"]').each(function() {
        $(this).remove();
    });
}

// ===== ESTIMATE CALC =====
function calculateEstimate() {
    var labor = parseFloat($('#estimated_labor_cost').val()) || 0;
    var parts = parseFloat($('#estimated_parts_cost').val()) || 0;
    var tax = parseFloat($('#estimated_tax').val()) || 0;
    var total = labor + parts + tax;
    $('#estimatedTotalDisplay').text('₱ ' + total.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ','));
}

function setEstimate(field, value) {
    if (field === 'all' && Array.isArray(value)) {
        $('#estimated_labor_cost').val(value[0] || 0);
        $('#estimated_parts_cost').val(value[1] || 0);
        $('#estimated_tax').val(value[2] || 0);
    } else if (field === 'labor') {
        $('#estimated_labor_cost').val(value);
    } else if (field === 'parts') {
        $('#estimated_parts_cost').val(value);
    } else if (field === 'tax') {
        $('#estimated_tax').val(value);
    }
    calculateEstimate();
}

function loadVehicles(customerId, $select) {
    if (!customerId) {
        $select.html('<option value="">Select Customer First</option>');
        return;
    }
    
    var selectedVehicle = $select.data('selected');
    var prevVal = selectedVehicle || $select.data('initial');
    
    $select.html('<option value="">Loading...</option>').prop('disabled', true);
    
    $.get('/api/customer-vehicles', { customer_id: customerId })
        .done(function(vehicles) {
            var html = '<option value="">Select Vehicle</option>';
            vehicles.forEach(function(v) {
                var label = v.year + ' ' + v.make + ' ' + v.model;
                if (v.license_plate) label += ' - ' + v.license_plate;
                if (v.color) label += ' [' + v.color + ']';
                var selected = (prevVal && prevVal == v.id) ? ' selected' : '';
                html += '<option value="' + v.id + '"' + selected + '>' + label + '</option>';
            });
            $select.html(html).prop('disabled', false);
        })
        .fail(function() {
            $select.html('<option value="">Error loading vehicles</option>').prop('disabled', false);
        });
}
</script>
@endonce
