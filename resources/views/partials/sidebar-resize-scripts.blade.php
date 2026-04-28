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

    var sidebar = document.getElementById('sidebar');
    var handle = document.getElementById('sidebarResizeHandle');
    var collapseBtn = document.getElementById('sidebarCollapseBtn');
    var mobileToggle = document.getElementById('sidebarToggle');
    var mobileOverlay = document.getElementById('sidebarOverlay');

    if (!sidebar) return;

    // ============================================================
    //  RESTORE WIDTH & COLLAPSED STATE
    // ============================================================

    function restore() {
        if (window.innerWidth < 768) return;

        var w = parseInt(localStorage.getItem(KEY), 10);
        if (w && w >= MIN_W && w <= MAX_W) {
            sidebar.style.flex = '0 0 ' + w + 'px';
        } else {
            sidebar.style.flex = '0 0 ' + DEFAULT_W + 'px';
        }

        if (localStorage.getItem(COLLAPSED_KEY) === 'true') {
            sidebar.classList.add('sidebar-collapsed');
            if (collapseBtn) {
                collapseBtn.innerHTML = '<i class="fas fa-chevron-right"></i>';
            }
        } else {
            sidebar.classList.remove('sidebar-collapsed');
            if (collapseBtn) {
                collapseBtn.innerHTML = '<i class="fas fa-chevron-left"></i>';
            }
        }
    }

    function saveWidth() {
        localStorage.setItem(KEY, sidebar.offsetWidth);
    }

    // ============================================================
    //  DRAG RESIZE
    // ============================================================

    if (handle) {
        var isResizing = false;
        var startX, startW;

        function onDown(e) {
            if (window.innerWidth < 768) return;
            if (sidebar.classList.contains('sidebar-collapsed')) return;
            e.preventDefault();
            isResizing = true;
            startX = e.clientX || (e.touches && e.touches[0].clientX);
            startW = sidebar.offsetWidth;
            handle.classList.add('dragging');
            document.body.style.cursor = 'col-resize';
            document.body.style.userSelect = 'none';
        }

        function onMove(e) {
            if (!isResizing) return;
            var cx = e.clientX || (e.touches && e.touches[0].clientX);
            if (!cx) return;
            var newW = Math.min(Math.max(startW + (cx - startX), MIN_W), MAX_W);
            sidebar.style.flex = '0 0 ' + newW + 'px';
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
            var isCollapsed = sidebar.classList.contains('sidebar-collapsed');

            if (isCollapsed) {
                // Expand
                sidebar.classList.remove('sidebar-collapsed');
                localStorage.setItem(COLLAPSED_KEY, 'false');
                // Restore width
                var saved = parseInt(localStorage.getItem(KEY), 10);
                var w = (saved && saved >= MIN_W) ? saved : DEFAULT_W;
                sidebar.style.flex = '0 0 ' + w + 'px';
                collapseBtn.innerHTML = '<i class="fas fa-chevron-left"></i>';
            } else {
                // Collapse
                saveWidth(); // save current expanded width
                sidebar.classList.add('sidebar-collapsed');
                localStorage.setItem(COLLAPSED_KEY, 'true');
                sidebar.style.flex = '0 0 55px';
                collapseBtn.innerHTML = '<i class="fas fa-chevron-right"></i>';
            }
        });
    }

    // ============================================================
    //  MOBILE TOGGLE
    // ============================================================

    if (mobileToggle && mobileOverlay) {
        mobileToggle.addEventListener('click', function() {
            sidebar.classList.toggle('show');
            mobileOverlay.classList.toggle('show');
        });

        mobileOverlay.addEventListener('click', function() {
            sidebar.classList.remove('show');
            mobileOverlay.classList.remove('show');
        });
    }

    // ============================================================
    //  SUBMENU STATE MEMORY
    // ============================================================

    var submenu = document.getElementById('serviceManagementCollapse');
    if (submenu) {
        var savedSub = localStorage.getItem(SUBMENU_KEY);
        var toggle = document.getElementById('serviceManagementToggle');

        if (savedSub === 'expanded') {
            submenu.classList.add('show');
            if (toggle) toggle.setAttribute('aria-expanded', 'true');
        }

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
            sidebar.style.flex = '';
        } else {
            restore();
        }
    });

    // ============================================================
    //  INIT
    // ============================================================

    restore();
    console.log('[Sidebar] Resize initialized');
})();
</script>
@endpush
