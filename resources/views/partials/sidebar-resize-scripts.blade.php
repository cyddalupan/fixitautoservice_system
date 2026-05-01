{{--
==========================================================
 RESIZABLE SIDEBAR — Drag-Resize JavaScript
 Works with existing Bootstrap grid (flex: 0 0 Npx on .sidebar)
==========================================================
--}}
@push('scripts')
<script>
(function() {
    'use strict';

    const KEY = 'fixit_sidebar_width';
    const COLLAPSED_KEY = 'fixit_sidebar_collapsed';
    const SUBMENU_KEY = 'serviceManagementCollapseState';
    const MIN_W = 220;
    const MAX_W = 420;
    const DEFAULT_W = 250;

    var outer = document.getElementById('sidebarOuter');
    var sidebar = document.getElementById('sidebar');
    var handle = document.getElementById('sidebarResizeHandle');
    var collapseBtn = document.getElementById('sidebarCollapseBtn');
    var mobileToggle = document.getElementById('sidebarToggle');
    var mobileOverlay = document.getElementById('sidebarOverlay');

    if (!outer || !sidebar) return;

    // ============================================================
    //  RESTORE WIDTH & COLLAPSED STATE
    // ============================================================

    function restore() {
        if (window.innerWidth < 768) return;

        var w = parseInt(localStorage.getItem(KEY), 10);
        if (w && w >= MIN_W && w <= MAX_W) {
            outer.style.flex = '0 0 ' + w + 'px';
        } else {
            outer.style.flex = '0 0 ' + DEFAULT_W + 'px';
        }

        if (localStorage.getItem(COLLAPSED_KEY) === 'true') {
            outer.classList.add('sidebar-collapsed');
            sidebar.classList.add('sidebar-collapsed');
            if (collapseBtn) {
                collapseBtn.innerHTML = '<i class="fas fa-chevron-right"></i>';
            }
        } else {
            outer.classList.remove('sidebar-collapsed');
            sidebar.classList.remove('sidebar-collapsed');
            if (collapseBtn) {
                collapseBtn.innerHTML = '<i class="fas fa-chevron-left"></i>';
            }
        }
    }

    function saveWidth() {
        localStorage.setItem(KEY, outer.offsetWidth);
    }

    // ============================================================
    //  DRAG RESIZE
    // ============================================================

    if (handle) {
        var isResizing = false;
        var startX, startW;

        function onDown(e) {
            if (window.innerWidth < 768) return;
            if (outer.classList.contains('sidebar-collapsed')) return;
            e.preventDefault();
            isResizing = true;
            startX = e.clientX || (e.touches && e.touches[0].clientX);
            startW = outer.offsetWidth;
            handle.classList.add('dragging');
            document.body.style.cursor = 'col-resize';
            document.body.style.userSelect = 'none';
        }

        function onMove(e) {
            if (!isResizing) return;
            var cx = e.clientX || (e.touches && e.touches[0].clientX);
            if (!cx) return;
            var newW = Math.min(Math.max(startW + (cx - startX), MIN_W), MAX_W);
            outer.style.flex = '0 0 ' + newW + 'px';
        }

        function onUp() {
            if (!isResizing) return;
            isResizing = false;
            handle.classList.remove('dragging');
            document.body.style.cursor = '';
            document.body.style.userSelect = '';
            saveWidth();
        }

        handle.addEventListener('mousedown', onDown);
        document.addEventListener('mousemove', onMove);
        document.addEventListener('mouseup', onUp);
        handle.addEventListener('touchstart', onDown, { passive: true });
        document.addEventListener('touchmove', onMove, { passive: true });
        document.addEventListener('touchend', onUp);
    }

    // ============================================================
    //  COLLAPSE TOGGLE
    // ============================================================

    if (collapseBtn) {
        collapseBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            var isCollapsed = outer.classList.contains('sidebar-collapsed');

            if (isCollapsed) {
                // Expand
                outer.classList.remove('sidebar-collapsed');
                sidebar.classList.remove('sidebar-collapsed');
                localStorage.setItem(COLLAPSED_KEY, 'false');
                // Restore width
                var saved = parseInt(localStorage.getItem(KEY), 10);
                var w = (saved && saved >= MIN_W) ? saved : DEFAULT_W;
                outer.style.flex = '0 0 ' + w + 'px';
                collapseBtn.innerHTML = '<i class="fas fa-chevron-left"></i>';
            } else {
                // Collapse
                saveWidth(); // save current expanded width
                outer.classList.add('sidebar-collapsed');
                sidebar.classList.add('sidebar-collapsed');
                localStorage.setItem(COLLAPSED_KEY, 'true');
                outer.style.flex = '0 0 55px';
                collapseBtn.innerHTML = '<i class="fas fa-chevron-right"></i>';
            }
        });
    }

    // ============================================================
    //  MOBILE TOGGLE
    // ============================================================

    if (mobileToggle && mobileOverlay && !mobileToggle.hasAttribute('data-sidebar-bound')) {
        mobileToggle.setAttribute('data-sidebar-bound', 'true');
        mobileToggle.addEventListener('click', function(e) {
            e.preventDefault();
            sidebar.classList.toggle('show');
            mobileOverlay.classList.toggle('show');
        });

        if (!mobileOverlay.hasAttribute('data-sidebar-bound')) {
            mobileOverlay.setAttribute('data-sidebar-bound', 'true');
            mobileOverlay.addEventListener('click', function() {
                sidebar.classList.remove('show');
                mobileOverlay.classList.remove('show');
            });
        }
    }

    // ============================================================
    //  SUBMENU STATE MEMORY
    // ============================================================

    var submenu = document.getElementById('serviceManagementCollapse');
    if (submenu) {
        var savedSub = localStorage.getItem(SUBMENU_KEY);
        var toggle = document.getElementById('serviceManagementToggle');

        // Determine initial state from localStorage.
        // Server no longer renders 'show' class — controlled entirely via JS.
        if (savedSub === 'expanded') {
            submenu.classList.add('show');
            if (toggle) toggle.setAttribute('aria-expanded', 'true');
        } else {
            // Default (no saved state, or 'collapsed') — keep collapsed
            if (toggle) toggle.setAttribute('aria-expanded', 'false');
        }

        // Toggle click handler — replaces Bootstrap's data-bs-toggle
        if (toggle) {
            toggle.removeAttribute('data-bs-toggle'); // safety: no duplicate listeners
            toggle.addEventListener('click', function(e) {
                e.preventDefault();
                var isExpanded = submenu.classList.contains('show');
                if (isExpanded) {
                    submenu.classList.remove('show');
                    toggle.setAttribute('aria-expanded', 'false');
                    localStorage.setItem(SUBMENU_KEY, 'collapsed');
                } else {
                    submenu.classList.add('show');
                    toggle.setAttribute('aria-expanded', 'true');
                    localStorage.setItem(SUBMENU_KEY, 'expanded');
                }
            });
        }

        // Still listen for Bootstrap collapse events if they happen (e.g. from other code)
        submenu.addEventListener('shown.bs.collapse', function() {
            localStorage.setItem(SUBMENU_KEY, 'expanded');
        });
        submenu.addEventListener('hidden.bs.collapse', function() {
            localStorage.setItem(SUBMENU_KEY, 'collapsed');
        });
    }

    // ============================================================
    //  WINDOW RESIZE
    // ============================================================

    window.addEventListener('resize', function() {
        if (window.innerWidth < 768) {
            outer.style.flex = '';
        } else {
            restore();
        }
    });

    // ============================================================
    //  INIT
    // ============================================================

    restore();
    console.log('[Sidebar] Resize initialized (outer container)');
})();
</script>
@endpush
