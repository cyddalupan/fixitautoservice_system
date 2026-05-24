{{--
==========================================================
 UNREAD RECORD SYSTEM — Shared JavaScript
 Mark-as-read via AJAX, counter sync, "Mark All" batch
==========================================================
--}}

<script>
/**
 * Mark a single record as read via AJAX.
 * Updates the row styling immediately and syncs the sidebar counter.
 *
 * @param {string} module  - Module name (appointments, inspections, estimates, work-orders, service-records)
 * @param {number} id      - Record ID
 * @param {HTMLElement|null} rowEl - Optional table row element to update in-place
 */
function markRecordAsRead(module, id, rowEl) {
    // If already marked visually, skip
    if (rowEl && !rowEl.classList.contains('tr-unread')) return;
    
    fetch('/mark-as-read', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            module: module,
            id: id
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && rowEl) {
            // Remove unread styling
            rowEl.classList.remove('tr-unread');
            
            // Remove left accent bar
            const firstTd = rowEl.querySelector('td:first-child');
            if (firstTd) {
                firstTd.style.boxShadow = 'none';
            }
            
            // Demote text styling (remove unread bold classes)
            rowEl.querySelectorAll('.unread-primary-text').forEach(el => {
                el.classList.remove('unread-primary-text');
                el.style.fontWeight = 'var(--read-text-weight, 400)';
                el.style.color = 'var(--read-text-primary, #1e293b)';
            });
            rowEl.querySelectorAll('.unread-secondary-text').forEach(el => {
                el.classList.remove('unread-secondary-text');
            });
            
            // Remove NEW badges
            rowEl.querySelectorAll('.badge-new-record, .unread-dot').forEach(el => el.remove());
            
            // Sync sidebar counter
            syncSidebarCounter(module, -1);
        }
    })
    .catch(error => {
        console.error('Error marking record as read:', error);
    });
}

/**
 * Sync sidebar counter by decrementing or refreshing.
 * @param {string} module - Module name
 * @param {number} delta  - Change amount (typically -1)
 */
function syncSidebarCounter(module, delta) {
    // Try to find the sidebar badge for this module
    // First try the new-unread badge, then the total badge
    const newBadgeSelectors = {
        'appointments': '#s-appointments-new',
        'inspections': '#s-inspections-new',
        'estimates': '#s-estimates-new',
        'work-orders': '#s-work-orders-active',
        'work_orders': '#s-work-orders-active',
        'service-records': null,
        'service_records': null,
        'invoices': '#s-invoices-new',
    };
    
    const newBadgeId = newBadgeSelectors[module] || newBadgeSelectors[module.replace(/_/g, '-')];
    
    // Update the red NEW badge
    if (newBadgeId) {
        const newBadge = document.querySelector(newBadgeId);
        if (newBadge) {
            let count = parseInt(newBadge.textContent) || 0;
            count = Math.max(0, count + delta);
            if (count > 0) {
                newBadge.textContent = count;
                newBadge.style.display = '';
            } else {
                newBadge.textContent = '0';
                newBadge.style.display = 'none';
            }
        }
    }
    
    // For batch operations (delta <= -10), refresh from server
    if (delta < -10) {
        fetch('/sidebar-counters', { method: 'GET', headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(data => {
            // The sidebar-counters route returns flat keys, not {counts: {}}
            if (data && typeof data === 'object') {
                const map = {
                    's-appointments': 'appointments_total',
                    's-appointments-new': 'appointments_new',
                    's-inspections': 'inspections_total',
                    's-inspections-new': 'inspections_new',
                    's-estimates': 'estimates_total',
                    's-estimates-new': 'estimates_new',
                    's-work-orders': 'work_orders_total',
                    's-work-orders-active': 'work_orders_active',
                    's-service-records': 'service_records_total',
                    's-invoices': 'invoices_total',
                    's-invoices-new': 'invoices_new',
                };
                for (const [elId, key] of Object.entries(map)) {
                    const el = document.getElementById(elId);
                    if (el && data[key] !== undefined) {
                        el.textContent = data[key];
                        if (key.includes('_new') || key === 'work_orders_active') {
                            el.style.display = data[key] > 0 ? '' : 'none';
                        }
                    }
                }
            }
        }).catch(() => {});
    }
}

/**
 * Mark ALL records in a module as read (batch operation).
 * @param {string} module - Module name
 * @param {HTMLElement} btn - The button element (for loading state)
 */
function markAllAsRead(module, btn) {
    if (!confirm('Mark all records as read?')) return;
    
    const originalHtml = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Marking...';
    btn.disabled = true;
    
    fetch('/mark-all-as-read', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            module: module
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update all visible unread rows
            document.querySelectorAll('.tr-unread').forEach(row => {
                row.classList.remove('tr-unread');
                const firstTd = row.querySelector('td:first-child');
                if (firstTd) firstTd.style.boxShadow = 'none';
                row.querySelectorAll('.unread-primary-text').forEach(el => {
                    el.classList.remove('unread-primary-text');
                    el.style.fontWeight = 'var(--read-text-weight, 400)';
                    el.style.color = 'var(--read-text-primary, #1e293b)';
                });
                row.querySelectorAll('.unread-secondary-text').forEach(el => {
                    el.classList.remove('unread-secondary-text');
                });
                row.querySelectorAll('.badge-new-record, .unread-dot').forEach(el => el.remove());
            });
            
            // Hide the mark-all button
            if (btn) btn.style.display = 'none';
            
            // Reset sidebar counter
            syncSidebarCounter(module, -999);
        }
    })
    .catch(error => {
        console.error('Error marking all as read:', error);
    })
    .finally(() => {
        btn.innerHTML = originalHtml;
        btn.disabled = false;
    });
}

/**
 * Attach row click handler for mark-as-read (for tables).
 * @param {string} tableId - The table element ID or selector
 * @param {string} module  - Module name
 * @param {string} dataAttr - Data attribute for record ID (default: data-id)
 */
function initUnreadRowClicks(tableId, module, dataAttr) {
    dataAttr = dataAttr || 'data-id';
    const table = typeof tableId === 'string' ? document.querySelector(tableId) : tableId;
    if (!table) return;
    
    table.querySelectorAll('tr.tr-unread').forEach(row => {
        row.addEventListener('click', function(e) {
            // Don't mark as read if clicking on a link, button, or form element
            if (e.target.closest('a') || e.target.closest('button') || e.target.closest('input') || e.target.closest('select') || e.target.closest('textarea') || e.target.closest('form')) {
                return;
            }
            const id = this.getAttribute(dataAttr);
            if (id) {
                markRecordAsRead(module, id, this);
            }
        });
        // Add cursor pointer to indicate clickable
        row.style.cursor = 'pointer';
    });
}

/**
 * Initialize unread system on a specific table.
 * Call this after DOMContentLoaded.
 * @param {string|HTMLElement} tableEl - Table element or selector
 * @param {string} module - Module name
 * @param {object} options - { dataAttr, markAllBtnId, filterBtnId, hideEmptyMarkAll }
 */
function initUnreadSystem(tableEl, module, options) {
    options = options || {};
    const table = typeof tableEl === 'string' ? document.querySelector(tableEl) : tableEl;
    if (!table) return;
    
    // Initialize row click handlers
    initUnreadRowClicks(table, module, options.dataAttr || 'data-id');
    
    // Hide "Mark All as Read" button if no unread rows
    if (options.hideEmptyMarkAll !== false) {
        const markAllBtn = document.getElementById(options.markAllBtnId || 'markAllReadBtn');
        if (markAllBtn && table.querySelectorAll('tr.tr-unread').length === 0) {
            markAllBtn.style.display = 'none';
        }
    }
}

// Export for module-specific scripts
window.markRecordAsRead = markRecordAsRead;
window.markAllAsRead = markAllAsRead;
window.syncSidebarCounter = syncSidebarCounter;
window.initUnreadSystem = initUnreadSystem;
window.initUnreadRowClicks = initUnreadRowClicks;
</script>
