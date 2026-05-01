{{--
==========================================================
 RESIZABLE SIDEBAR — Drag-Resize with localStorage
 Premium enterprise sidebar with saved preferences
 Works within existing Bootstrap grid layout
==========================================================
--}}
<style>
/* ============================================================
   SIDEBAR — Resize Handle & Collapse Toggle
   These work on top of the existing Bootstrap grid layout.
   The .sidebar element already has flex: 0 0 250px from layout CSS.
   The resize JS modifies flex-basis via localStorage.
   ============================================================ */

/* Drag resize handle on right edge of sidebar */
.sidebar-resize-handle {
    position: absolute !important;
    top: 0 !important;
    right: 0 !important;
    width: 18px !important;
    height: 100% !important;
    cursor: ew-resize !important;
    z-index: 1030 !important;
    background: rgba(148, 163, 184, 0.12) !important;
    transition: background 0.15s ease !important;
    touch-action: none !important; /* Prevent scroll interference on touch devices */
}


/* Vertical indicator line — subtle full-height accent */
.sidebar-resize-handle::after {
    content: '' !important;
    position: absolute !important;
    top: 50% !important;
    left: 95% !important;
    transform: translate(-50%, -50%) !important;
    width: 3px !important;
    height: 100% !important;
    background: rgba(148, 163, 184, 0.15) !important;
    border-radius: 2px !important;
    transition: all 0.15s ease !important;
    z-index: 1100;
    cursor: ew-resize;
}

.sidebar-resize-handle:hover,
.sidebar-resize-handle:active,
.sidebar-resize-handle.dragging {
    background: rgba(59, 130, 246, 0.4) !important;
}

.sidebar-resize-handle:hover::after,
.sidebar-resize-handle.dragging::after {
    background: rgba(59, 130, 246, 0.8) !important;
}

/* Dot grip indicator — always faintly visible */
.sidebar-resize-handle .handle-dots {
    position: absolute !important;
    top: 50% !important;
    left: 50% !important;
    transform: translate(-50%, -50%) !important;
    display: flex !important;
    flex-direction: column !important;
    gap: 3px !important;
    opacity: 0.6 !important;
    transition: opacity 0.2s ease !important;
    pointer-events: none !important;
}

.sidebar-resize-handle:hover .handle-dots,
.sidebar-resize-handle.dragging .handle-dots {
    opacity: 1 !important;
}

.sidebar-resize-handle .handle-dots span {
    display: block !important;
    width: 3px !important;
    height: 3px !important;
    background: rgba(148, 163, 184, 0.5) !important;
    border-radius: 50% !important;
}

/* Collapse toggle button */
.sidebar-collapse-toggle {
    position: absolute !important;
    bottom: 12px !important;
    right: -14px !important;
    width: 28px !important;
    height: 28px !important;
    background: #334155 !important;
    border: 2px solid #1e293b !important;
    border-radius: 50% !important;
    color: #94a3b8 !important;
    cursor: pointer !important;
    z-index: 160 !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    font-size: 0.65rem !important;
    transition: all 0.2s ease !important;
    box-shadow: 0 2px 6px rgba(0,0,0,0.3) !important;
}

.sidebar-collapse-toggle:hover {
    background: #475569 !important;
    color: #e2e8f0 !important;
}

/* ============================================================
   COLLAPSED MODE
   ============================================================ */

/* When sidebar is collapsed to a narrow icon bar */
.sidebar-outer.sidebar-collapsed {
    flex: 0 0 55px !important;
}

.sidebar-outer.sidebar-collapsed #sidebar .nav-link span:not(.sidebar-badge):not(.sidebar-badge-danger) {
    display: none !important;
}

.sidebar-outer.sidebar-collapsed #sidebar .sidebar-badge,
.sidebar-outer.sidebar-collapsed #sidebar .sidebar-badge-danger {
    position: absolute !important;
    top: 2px !important;
    right: 4px !important;
    font-size: 0.5rem !important;
    padding: 0.1rem 0.25rem !important;
    min-width: 14px !important;
    height: 14px !important;
    line-height: 1 !important;
}

.sidebar-outer.sidebar-collapsed #sidebar .sidebar-section-label {
    display: none !important;
}

.sidebar-outer.sidebar-collapsed #sidebar .quick-stats {
    display: none !important;
}

.sidebar-outer.sidebar-collapsed #sidebar .nav-chevron {
    display: none !important;
}

/* In collapsed mode, the Bootstrap collapse must remain visible */
.sidebar-outer.sidebar-collapsed #sidebar #serviceManagementCollapse.collapse {
    display: block !important;
    visibility: visible !important;
    height: auto !important;
}

.sidebar-outer.sidebar-collapsed #sidebar .nav-sub {
    /* Show nav-sub items in collapsed mode but with icons only */
    display: block !important;
    padding-left: 0 !important;
    list-style: none !important;
}

/* In collapsed mode, sub-items show only icons with tooltips */
.sidebar-outer.sidebar-collapsed #sidebar .nav-sub .nav-link span:not(.sidebar-badge):not(.sidebar-badge-danger) {
    display: none !important;
}

.sidebar-outer.sidebar-collapsed #sidebar .nav-sub .nav-link .sidebar-badge,
.sidebar-outer.sidebar-collapsed #sidebar .nav-sub .nav-link .sidebar-badge-danger {
    display: none !important;
}

.sidebar-outer.sidebar-collapsed #sidebar .nav-sub .nav-link {
    justify-content: center !important;
    padding: 0.55rem 0 !important;
}

/* Also hide the section label text but keep spacing */
.sidebar-outer.sidebar-collapsed #sidebar .sidebar-section-label {
    display: block !important;
    text-align: center !important;
    padding: 0.5rem 0 0.25rem !important;
    font-size: 0.45rem !important;
    letter-spacing: 0.1em !important;
    color: rgba(255,255,255,0.25) !important;
    overflow: visible !important;
}

.sidebar-outer.sidebar-collapsed #sidebar .sidebar-section-label::before {
    content: '' !important;
    display: block !important;
    width: 50% !important;
    height: 1px !important;
    background: rgba(255,255,255,0.06) !important;
    margin: 4px auto !important;
}

.sidebar-outer.sidebar-collapsed #sidebar .nav-link {
    justify-content: center !important;
    padding: 0.75rem 0 !important;
}

.sidebar-outer.sidebar-collapsed #sidebar .nav-link i {
    margin: 0 !important;
    font-size: 1.15rem !important;
}

/* Show tooltips on hover when collapsed */
.sidebar-outer.sidebar-collapsed #sidebar .nav-link {
    position: relative !important;
}

.sidebar-outer.sidebar-collapsed #sidebar .nav-link:hover::after {
    content: attr(data-tooltip) !important;
    position: absolute !important;
    left: 100% !important;
    top: 50% !important;
    transform: translateY(-50%) !important;
    background: #0f172a !important;
    color: #e2e8f0 !important;
    padding: 6px 10px !important;
    border-radius: 6px !important;
    font-size: 0.75rem !important;
    font-weight: 500 !important;
    white-space: nowrap !important;
    z-index: 200 !important;
    margin-left: 8px !important;
    box-shadow: 0 4px 12px rgba(0,0,0,0.3) !important;
    pointer-events: none !important;
}

/* ============================================================
   IMPROVED BADGE STYLING (overrides old CSS)
   ============================================================ */
.sidebar-badge {
    background: rgba(255,255,255,0.12) !important;
    color: #94a3b8 !important;
    font-size: 0.65rem !important;
    font-weight: 600 !important;
    padding: 0.15rem 0.45rem !important;
    border-radius: 999px !important;
    margin-left: auto !important;
    min-width: 20px !important;
    text-align: center !important;
    line-height: 1.3 !important;
    flex-shrink: 0 !important;
}

.sidebar-badge-danger {
    background: #dc2626 !important;
    color: #ffffff !important;
    font-size: 0.6rem !important;
    font-weight: 700 !important;
    padding: 0.1rem 0.35rem !important;
    border-radius: 999px !important;
    margin-left: 4px !important;
    min-width: 18px !important;
    text-align: center !important;
    line-height: 1.3 !important;
    animation: badge-pulse 2s ease-in-out infinite !important;
    flex-shrink: 0 !important;
}

@keyframes badge-pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.85; }
}

/* Nav link flex layout */
.sidebar .nav-link {
    display: flex !important;
    align-items: center !important;
    gap: 0.5rem !important;
    padding-right: 30px !important; /* Room for badges */
}

.sidebar .nav-link > span:not(.sidebar-badge):not(.sidebar-badge-danger) {
    flex: 1 !important;
    white-space: nowrap !important;
    overflow: hidden !important;
    text-overflow: ellipsis !important;
    min-width: 0 !important;
}

/* ============================================================
   MOBILE — Hide resize/collapse controls, keep slide-in
   ============================================================ */
@media (max-width: 767.98px) {
    .sidebar-resize-handle,
    .sidebar-collapse-toggle {
        display: none !important;
    }
}

/* Fix: Restore Bootstrap collapse visibility over Tailwind v4's `.collapse { visibility: collapse }` */
#serviceManagementCollapse.collapse,
#serviceManagementCollapse.collapsing {
    visibility: visible;
}
#serviceManagementCollapse.collapse.show {
    visibility: visible !important;
}
</style>
