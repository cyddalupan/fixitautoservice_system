@extends('layouts.app')

@section('title', 'Appointments - Fix-It Auto Services')

@section('content')
@push('styles')
<!-- FullCalendar CSS -->
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />
<style>
/* ============================================
   COLOR CODING FOR WORKFLOW STATUS
   ============================================ */

/* ALL APPOINTMENTS - LIGHT GREY BACKGROUND (per user request) */
.fc-event-scheduled-default,
.fc-event-warning,
.fc-event-repair-order,
.fc-event-estimate,
.fc-event-job-order-paid,
.fc-event-scheduled,
.fc-event-arrived,
.fc-event-in_progress,
.fc-event-completed,
.fc-event-cancelled,
.fc-event-no_show,
.fc-event-rescheduled {
    background-color: #f8f9fa !important; /* Bootstrap light grey */
    border: 1px solid #dee2e6 !important; /* Light border */
}

/* Also target all FullCalendar events for completeness */
.fc-event {
    background-color: #f8f9fa !important;
    border-color: #dee2e6 !important;
}

/* Text colors are handled by JavaScript eventContent function */
/* Green text for scheduled/confirmed, Red text for cancelled */
.fc-event-no_show {
    background-color: #6c757d !important;
    border-color: #6c757d !important;
}

/* FIX: Ensure calendar container has proper dimensions */
#appointmentsCalendar {
    min-height: 700px;
    width: 100%;
    background-color: white;
    border-radius: 0 0 8px 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

/* Calendar header cells - MAKE DATES VISIBLE! */
.fc-col-header-cell {
    background-color: #f8f9fa !important;
    color: #495057 !important;
    font-weight: 700 !important;
    padding: 12px 0 !important;
    border-color: #dee2e6 !important;
    font-size: 14px !important;
    text-align: center !important;
}

/* Day view header - EXTRA VISIBLE! */
.fc-timeGridDay-view .fc-col-header-cell {
    background-color: #e9ecef !important;
    font-size: 16px !important;
    font-weight: 800 !important;
    color: #212529 !important;
    padding: 15px 0 !important;
    border-bottom: 3px solid #0d6efd !important;
}

/* Week view headers */
.fc-timeGridWeek-view .fc-col-header-cell {
    background-color: #f1f3f4 !important;
    font-size: 15px !important;
    font-weight: 700 !important;
}

/* Today's header highlight */
.fc-col-header-cell.fc-day-today {
    background-color: #e7f1ff !important;
    color: #0d6efd !important;
    border-bottom: 3px solid #0d6efd !important;
}

/* Calendar day cells */
.fc-daygrid-day {
    border-color: #dee2e6 !important;
}

.fc-daygrid-day.fc-day-today {
    background-color: #e7f1ff !important;
}

/* Week and day view time slots */
.fc-timegrid-slot {
    border-color: #dee2e6 !important;
}

.fc-timegrid-slot.fc-timegrid-slot-label {
    background-color: #f8f9fa !important;
}

/* List view styling */
.fc-list {
    border: 1px solid #dee2e6 !important;
    border-radius: 4px !important;
}

.fc-list-day {
    background-color: #f8f9fa !important;
    font-weight: 600 !important;
    color: #495057 !important;
}

/* Calendar help card toggle styling */
#calendarHelpCard {
    transition: all 0.3s ease;
}

#calendarHelpCard .card-header {
    cursor: pointer;
    user-select: none;
}

#toggleHelpCard {
    transition: all 0.2s ease;
    font-size: 12px;
    padding: 4px 10px;
}

#toggleHelpCard:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

#helpCardBody {
    transition: all 0.3s ease;
    overflow: hidden;
}

/* Ensure event text is visible */
.fc-event {
    font-weight: bold !important;
    font-size: 12px !important;
    padding: 2px 4px !important;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    /* Text color is now handled by JavaScript based on appointment status */
    /* text-shadow removed per user request */
}

/* Special case for light backgrounds */
.fc-event-in_progress {
    color: #000000 !important; /* Black text for yellow background */
    /* text-shadow removed per user request */
}

/* Ensure tooltips work */
.fc-event {
    cursor: pointer;
}

/* Make sure event content is visible */
.fc-event-title {
    font-weight: bold !important;
    font-size: 12px !important;
    line-height: 1.2 !important;
    padding: 1px 2px !important;
}

/* For month view with many events */
.fc-daygrid-event {
    margin-top: 1px !important;
    margin-bottom: 1px !important;
}

/* For week/day view */
.fc-timegrid-event {
    min-height: 24px !important;
}

/* For list view */
.fc-list-event {
    padding: 4px 8px !important;
}

/* Make calendar toolbar more visible */
.fc-toolbar {
    background-color: #f8f9fa !important;
    padding: 10px 15px !important;
    border-radius: 8px 8px 0 0 !important;
    border-bottom: 1px solid #dee2e6 !important;
    margin-bottom: 0 !important;
}

.fc-toolbar-title {
    font-size: 1.5rem !important;
    font-weight: 600 !important;
    color: #343a40 !important;
}

.fc-button {
    background-color: #0d6efd !important;
    border-color: #0d6efd !important;
    color: white !important;
    font-weight: 600 !important;
    padding: 8px 16px !important;
    border-radius: 6px !important;
    font-size: 14px !important;
    transition: all 0.2s ease !important;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1) !important;
}

.fc-button:hover {
    background-color: #0b5ed7 !important;
    border-color: #0a58ca !important;
    transform: translateY(-1px) !important;
    box-shadow: 0 4px 8px rgba(0,0,0,0.15) !important;
}

.fc-button:active {
    transform: translateY(0) !important;
    box-shadow: 0 1px 2px rgba(0,0,0,0.1) !important;
}

.fc-button-active {
    background-color: #0a58ca !important;
    border-color: #0a53be !important;
    box-shadow: inset 0 2px 4px rgba(0,0,0,0.1) !important;
}

.fc-button-primary:not(:disabled).fc-button-active {
    background-color: #0a58ca !important;
    border-color: #0a53be !important;
}

/* Navigation arrows - make them more obvious */
.fc-prev-button, .fc-next-button {
    min-width: 40px !important;
    font-weight: bold !important;
    font-size: 16px !important;
}

.fc-prev-button::before {
    content: "◀" !important;
}

.fc-next-button::before {
    content: "▶" !important;
}

/* Today button special styling */
.fc-today-button {
    background-color: #198754 !important;
    border-color: #198754 !important;
    min-width: 80px !important;
}

.fc-today-button:hover {
    background-color: #157347 !important;
    border-color: #146c43 !important;
}

/* View buttons */
.fc-dayGridMonth-button, .fc-timeGridWeek-button, .fc-timeGridDay-button, .fc-listMonth-button {
    min-width: 70px !important;
}

/* ============================================
   ALWAYS-VISIBLE EVENT INFO (full details, no hover needed)
   ============================================ */
/* Let calendar events grow so every line stays readable */
.fc-daygrid-event {
    white-space: normal !important;
    overflow: visible !important;
    height: auto !important;
    padding: 3px 6px !important;
    margin-bottom: 3px !important;
    border-radius: 6px !important;
    box-shadow: 0 1px 2px rgba(0,0,0,0.06);
    transition: box-shadow 0.15s ease;
}
.fc-daygrid-event:hover {
    box-shadow: 0 2px 7px rgba(0,0,0,0.16);
}
.fc-daygrid-event .fc-event-main,
.fc-daygrid-event .fc-event-main-frame {
    overflow: visible !important;
}
/* Time-grid (week/day) events: allow wrapping too */
.fc-timegrid-event,
.fc-timegrid-event .fc-event-main {
    white-space: normal !important;
    border-radius: 6px !important;
}
.fc-timegrid-event {
    padding: 3px 6px !important;
}

/* Give month cells room for a multi-line event */
.fc-daygrid-day-frame { min-height: 118px; }
.fc-daygrid-day-top { margin-bottom: 2px; }
.fc-daygrid-day-number {
    font-weight: 600 !important;
    color: #495057 !important;
    font-size: 12px !important;
    padding: 2px 6px !important;
}
.fc-daygrid-day.fc-day-today .fc-daygrid-day-number {
    background: #0d6efd;
    color: #fff !important;
    border-radius: 50%;
    min-width: 22px;
    text-align: center;
    margin: 2px;
}

/* Event inner layout: [status bar][text block] */
.fc-ev-wrap { display: flex; align-items: stretch; gap: 5px; width: 100%; line-height: 1.2; }
.fc-ev-text { min-width: 0; flex: 1; overflow: hidden; }
.fc-ev-l1 {
    font-weight: 700;
    font-size: 10.5px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.fc-ev-l2 {
    font-weight: 500;
    font-size: 10px;
    color: #212529;
    overflow-wrap: anywhere;
}
.fc-ev-l3 {
    font-size: 9.5px;
    color: #495057;
    overflow-wrap: anywhere;
}
.fc-ev-l4 {
    font-size: 9px;
    color: #6c757d;
    font-style: italic;
    overflow-wrap: anywhere;
}
.fc-ev-plate {
    font-weight: 700;
    letter-spacing: 0.3px;
    color: #343a40;
}
.fc-ev-chip {
    display: inline-block;
    padding: 0 4px;
    border-radius: 3px;
    background: #e9ecef;
    font-size: 9px;
    font-weight: 700;
    line-height: 1.4;
    color: #495057;
    vertical-align: 1px;
}
.fc-ev-fuel { background: #e7f1ff; color: #0b5ed7; }
.fc-ev-trans { background: #eaf7ee; color: #157347; }
.fc-ev-sep { opacity: 0.4; }

/* ============================================
   RICH HOVER DETAILS CARD (appointment peek)
   ============================================ */
.fc-details-tooltip .tooltip-inner {
    max-width: 360px;
    text-align: left;
    padding: 0;
    background: #1f2937;
    border-radius: 10px;
    box-shadow: 0 10px 28px rgba(0,0,0,0.35);
    overflow: hidden;
}
.fc-tt-card {
    padding: 10px 12px;
    color: #f8fafc;
    font-size: 12px;
    line-height: 1.35;
}
.fc-tt-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    font-weight: 700;
    font-size: 13px;
    padding-bottom: 6px;
    margin-bottom: 6px;
    border-bottom: 1px solid rgba(255,255,255,0.22);
}
.fc-tt-time i { margin-right: 3px; }
.fc-tt-row {
    display: flex;
    gap: 8px;
    margin: 2px 0;
}
.fc-tt-k {
    color: #cbd5e1;
    min-width: 84px;
    font-weight: 600;
    flex-shrink: 0;
}
.fc-tt-v {
    color: #ffffff;
    flex: 1;
    word-break: break-word;
}
.fc-tt-badge {
    display: inline-block;
    padding: 1px 8px;
    border-radius: 10px;
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    white-space: nowrap;
}
.fc-tt-badge.bg-scheduled { background: #198754; color: #fff; }
.fc-tt-badge.bg-cancelled { background: #dc3545; color: #fff; }
.fc-tt-badge.bg-completed { background: #0d6efd; color: #fff; }
.fc-tt-badge.bg-progress  { background: #fd7e14; color: #fff; }
.fc-tt-badge.bg-default   { background: #6c757d; color: #fff; }
.fc-tt-hint {
    margin-top: 7px;
    padding-top: 6px;
    border-top: 1px solid rgba(255,255,255,0.15);
    color: #93c5fd;
    font-size: 10.5px;
}
.fc-event:hover {
    filter: brightness(0.97);
}
</style>
@endpush

@push('scripts')
<!-- FullCalendar JS -->
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    const calendarEl = document.getElementById("appointmentsCalendar");
    
    if (calendarEl) {
        const calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: "dayGridMonth",
            headerToolbar: {
                left: "prev,next today",
                center: "title",
                right: "dayGridMonth,timeGridWeek,timeGridDay,listMonth"
            },
            buttonText: {
                today: "Today",
                month: "Month",
                week: "Week",
                day: "Day",
                list: "List"
            },
            // Enhanced navigation
            navLinks: true, // Make dates clickable for navigation
            navLinkDayClick: function(date, jsEvent) {
                calendar.changeView('timeGridDay', date);
            },
            navLinkWeekClick: function(weekStart, jsEvent) {
                calendar.changeView('timeGridWeek', weekStart);
            },
            // Custom month dropdown when title is clicked
            titleFormat: { year: 'numeric', month: 'long' },
            // Date formatting for different views
            views: {
                dayGridMonth: {
                    titleFormat: { year: 'numeric', month: 'long' },
                    dayHeaderFormat: { weekday: 'short', day: 'numeric' }  // Month view: "Wed 26"
                },
                timeGridWeek: {
                    titleFormat: { year: 'numeric', month: 'long', day: 'numeric' },
                    dayHeaderFormat: { weekday: 'long', month: 'short', day: 'numeric' }  // Week view: "Wednesday, Mar 26"
                },
                timeGridDay: {
                    titleFormat: { year: 'numeric', month: 'long', day: 'numeric', weekday: 'long' },  // Toolbar: "Wednesday, March 26, 2026"
                    dayHeaderFormat: { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' }  // Header: "Wednesday, March 26, 2026"
                },
                listMonth: {
                    titleFormat: { year: 'numeric', month: 'long' }
                }
            },
            // Week headers with dates
            weekText: 'Week {week}',
            // Day cell dates
            dayCellContent: function(arg) {
                return { html: '<div class="fc-daygrid-day-number">' + arg.dayNumberText + '</div>' };
            },
            themeSystem: "bootstrap5",
            navLinks: true,
            editable: false,
            selectable: false,
            nowIndicator: true,
            dayMaxEvents: 4, // show up to 4 appointments per day (full info); extra -> "+N more"
            height: "auto",
            windowResize: function(view) {
                calendar.updateSize();
            },
            eventContent: function(arg) {
                // Always-visible info block — shows EVERYTHING except address,
                // phone number and notes (those stay in the hover card / detail page).
                //   line 1: time · customer        (bold, status-colored)
                //   line 2: vehicle · plate
                //   line 3: service · technician
                //   line 4: progress (workflow) · appt #
                var p = arg.event.extendedProps || {};
                var esc = function(s) {
                    if (s === null || s === undefined) return '';
                    return String(s).replace(/[&<>"']/g, function(c) {
                        return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
                    });
                };
                var titleCase = function(s) {
                    return String(s || '').replace(/_/g, ' ').replace(/\b\w/g, function(c) { return c.toUpperCase(); });
                };

                // Status -> text color + icon
                var status = p.status || '';
                var textColor = '#198754';
                var icon = '\u2705';
                var isStrikethrough = false;
                var opacity = '1';
                switch (status) {
                    case 'scheduled':
                    case 'confirmed': textColor = '#198754'; icon = '\u2705'; break;
                    case 'cancelled': textColor = '#dc3545'; icon = '\u274C'; isStrikethrough = true; opacity = '0.92'; break;
                    case 'no_show': textColor = '#6c757d'; icon = '\uD83D\uDEAB'; isStrikethrough = true; opacity = '0.85'; break;
                    case 'rescheduled': textColor = '#fd7e14'; icon = '\uD83D\uDD04'; break;
                    case 'completed': textColor = '#198754'; icon = '\u2714\uFE0F'; break;
                    case 'in_progress': textColor = '#0d6efd'; icon = '\u2699\uFE0F'; break;
                    case 'arrived': textColor = '#20c997'; icon = '\uD83D\uDE97'; break;
                    default: textColor = '#6c757d'; icon = '\uD83D\uDCC5';
                }

                var time = p.time_display || (arg.event.start
                    ? arg.event.start.toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' })
                    : '');
                var name = p.customer_short || (p.customer_name ? String(p.customer_name).split(' ')[0] : '') || 'Walk-in';
                var veh = (p.vehicle_info && p.vehicle_info !== 'Vehicle info not available') ? p.vehicle_info : '';
                var plate = p.plate || '';
                var svc = p.service_label || (p.service_type ? titleCase(p.service_type) : '');
                var tech = (p.technician_name && p.technician_name !== 'Not Assigned') ? p.technician_name : '';
                var progress = p.workflow_status_display || '';
                var number = p.number || '';

                var sep = '<span class="fc-ev-sep"> \u00b7 </span>';
                var join = function(arr) { return arr.join(sep); };

                var l1 = '<div class="fc-ev-l1" style="color:' + textColor +
                    (isStrikethrough ? ';text-decoration:line-through' : '') + ';">' +
                    icon + ' ' + esc(time) + sep + esc(name) + '</div>';

                // line 2: vehicle · fuel (Gas/Diesel) · transmission (MT/AT)
                var fuelMap = { gasoline: 'Gas', gas: 'Gas', diesel: 'Diesel', hybrid: 'Hybrid', electric: 'EV' };
                var transMap = { automatic: 'AT', auto: 'AT', manual: 'MT' };
                var fuelRaw = String(p.fuel_type || '').toLowerCase();
                var transRaw = String(p.transmission || '').toLowerCase();
                var fuel = fuelMap[fuelRaw] || '';
                var trans = transMap[transRaw] || '';
                var vehBits = [];
                if (veh) vehBits.push(esc(veh));
                if (fuel) vehBits.push('<span class="fc-ev-chip fc-ev-fuel">' + esc(fuel) + '</span>');
                if (trans) vehBits.push('<span class="fc-ev-chip fc-ev-trans">' + esc(trans) + '</span>');
                var l2 = vehBits.length ? '<div class="fc-ev-l2">' + join(vehBits) + '</div>' : '';

                // line 3: service type · technician
                var svcBits = [];
                if (svc) svcBits.push(esc(svc));
                if (tech) svcBits.push(esc(tech));
                var l3 = svcBits.length ? '<div class="fc-ev-l3">' + join(svcBits) + '</div>' : '';

                // line 4: service description (falls back to workflow progress)
                var desc = p.service_description ? String(p.service_description) : '';
                if (desc.length > 90) desc = desc.slice(0, 90) + '\u2026';
                var metaBits = [];
                if (desc) metaBits.push(esc(desc));
                else if (progress) metaBits.push(esc(progress));
                var l4 = metaBits.length ? '<div class="fc-ev-l4">' + join(metaBits) + '</div>' : '';

                var wrap = document.createElement('div');
                wrap.className = 'fc-ev-wrap';
                wrap.style.cssText = 'opacity:' + opacity + ';';

                var bar = document.createElement('span');
                bar.className = 'fc-ev-bar';
                bar.style.cssText = 'flex:0 0 3px; width:3px; border-radius:3px; background:' + textColor + ';';
                wrap.appendChild(bar);

                var txt = document.createElement('div');
                txt.className = 'fc-ev-text';
                txt.innerHTML = l1 + l2 + l3 + l4;
                wrap.appendChild(txt);

                return { domNodes: [wrap] };
            },
            events: function(fetchInfo, successCallback, failureCallback) {
                fetchAppointments(fetchInfo.start, fetchInfo.end)
                    .then(events => successCallback(events))
                    .catch(error => {
                        console.error("Error fetching appointments:", error);
                        failureCallback(error);
                    });
            },
            eventDidMount: function(info) {
                // ===== Rich hover details card =====
                // Build an HTML tooltip from extendedProps so peeking at an
                // appointment reveals full details, not just time + customer name.
                var p = info.event.extendedProps || {};
                var esc = function(s) {
                    if (s === null || s === undefined) return '';
                    return String(s).replace(/[&<>"']/g, function(c) {
                        return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
                    });
                };
                var row = function(label, value) {
                    if (value === null || value === undefined || value === '') return '';
                    return '<div class="fc-tt-row"><span class="fc-tt-k">' + esc(label) + '</span><span class="fc-tt-v">' + esc(value) + '</span></div>';
                };
                var titleCase = function(s) {
                    return String(s || '').replace(/_/g, ' ').replace(/\b\w/g, function(c) { return c.toUpperCase(); });
                };

                var status = (p.status || '').toLowerCase();
                var badgeClass = 'fc-tt-badge bg-default';
                if (status === 'scheduled' || status === 'confirmed') badgeClass = 'fc-tt-badge bg-scheduled';
                else if (status === 'cancelled' || status === 'no_show') badgeClass = 'fc-tt-badge bg-cancelled';
                else if (status === 'completed') badgeClass = 'fc-tt-badge bg-completed';
                else if (status === 'in_progress') badgeClass = 'fc-tt-badge bg-progress';

                var timeStr = '';
                if (info.event.start) {
                    timeStr = info.event.start.toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' });
                }

                var head = '<div class="fc-tt-head">' +
                    '<span class="fc-tt-time"><i class="fas fa-clock"></i> ' + esc(timeStr) + '</span>' +
                    (p.status_display ? '<span class="' + badgeClass + '">' + esc(p.status_display) + '</span>' : '') +
                    '</div>';

                var body = '';
                body += row('Appt #', p.number);
                body += row('Customer', p.customer_name);
                body += row('Contact', p.customer_phone);
                body += row('Vehicle', p.vehicle_info);
                body += row('Service', p.service_label || titleCase(p.service_type));
                if (p.service_description) body += row('Details', p.service_description);
                body += row('Technician', p.technician_name);
                body += row('Progress', p.workflow_status_display);
                if (p.notes) {
                    var notes = String(p.notes);
                    if (notes.length > 140) notes = notes.substring(0, 140) + '…';
                    body += row('Notes', notes);
                }

                var tipHtml = '<div class="fc-tt-card">' + head + body +
                    '<div class="fc-tt-hint"><i class="fas fa-mouse-pointer"></i> Click to open appointment</div></div>';

                // Bootstrap tooltip with HTML content (values are escaped above)
                new bootstrap.Tooltip(info.el, {
                    html: true,
                    sanitize: false,
                    title: tipHtml,
                    container: 'body',
                    customClass: 'fc-details-tooltip',
                    placement: 'auto',
                    fallbackPlacements: ['top', 'bottom', 'left', 'right'],
                    delay: { show: 120, hide: 80 }
                });

                // Add click handler
                info.el.addEventListener("click", function() {
                    const appointmentId = info.event.id;
                    window.location.href = `/appointments/${appointmentId}`;
                });
            }
        });
        
        calendar.render();
        
        // ============================================
        // ADD MONTH DROPDOWN WHEN CLICKING CALENDAR TITLE
        // ============================================
        setTimeout(function() {
            const titleEl = document.querySelector('.fc-toolbar-title');
            if (titleEl) {
                // Make title clickable
                titleEl.style.cursor = 'pointer';
                titleEl.title = 'Click to select month';
                
                titleEl.addEventListener('click', function(e) {
                    e.stopPropagation();
                    
                    // Create month selection dropdown
                    const dropdown = document.createElement('div');
                    dropdown.className = 'month-dropdown-container';
                    dropdown.style.cssText = `
                        position: absolute;
                        background: white;
                        border: 1px solid #ddd;
                        border-radius: 4px;
                        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                        z-index: 1000;
                        padding: 10px;
                        min-width: 200px;
                        max-height: 300px;
                        overflow-y: auto;
                    `;
                    
                    // Position near title
                    const rect = titleEl.getBoundingClientRect();
                    dropdown.style.top = (rect.bottom + 5) + 'px';
                    dropdown.style.left = (rect.left) + 'px';
                    
                    // Add months
                    const months = [
                        'January', 'February', 'March', 'April', 'May', 'June',
                        'July', 'August', 'September', 'October', 'November', 'December'
                    ];
                    
                    const currentDate = calendar.getDate();
                    const currentYear = currentDate.getFullYear();
                    
                    // Add year selection
                    const yearSelect = document.createElement('select');
                    yearSelect.className = 'form-select form-select-sm mb-2';
                    yearSelect.style.cssText = 'width: 100%;';
                    
                    // Add years (current year ± 2 years)
                    for (let year = currentYear - 2; year <= currentYear + 2; year++) {
                        const option = document.createElement('option');
                        option.value = year;
                        option.textContent = year;
                        if (year === currentYear) option.selected = true;
                        yearSelect.appendChild(option);
                    }
                    
                    dropdown.appendChild(yearSelect);
                    
                    // Add months grid
                    const monthsGrid = document.createElement('div');
                    monthsGrid.className = 'row g-1';
                    monthsGrid.style.cssText = 'margin: 0;';
                    
                    months.forEach((month, index) => {
                        const monthCol = document.createElement('div');
                        monthCol.className = 'col-4';
                        
                        const monthBtn = document.createElement('button');
                        monthBtn.className = 'btn btn-sm w-100 mb-1';
                        monthBtn.textContent = month.substring(0, 3); // Jan, Feb, etc
                        monthBtn.title = month;
                        
                        // Highlight current month
                        if (index === currentDate.getMonth() && currentYear === currentDate.getFullYear()) {
                            monthBtn.className = 'btn btn-primary btn-sm w-100 mb-1';
                        } else {
                            monthBtn.className = 'btn btn-outline-secondary btn-sm w-100 mb-1';
                        }
                        
                        monthBtn.addEventListener('click', function() {
                            const selectedYear = parseInt(yearSelect.value);
                            calendar.gotoDate(new Date(selectedYear, index, 1));
                            document.body.removeChild(dropdown);
                        });
                        
                        monthCol.appendChild(monthBtn);
                        monthsGrid.appendChild(monthCol);
                    });
                    
                    dropdown.appendChild(monthsGrid);
                    
                    // Add close button
                    const closeBtn = document.createElement('button');
                    closeBtn.className = 'btn btn-secondary btn-sm w-100 mt-2';
                    closeBtn.textContent = 'Close';
                    closeBtn.addEventListener('click', function() {
                        document.body.removeChild(dropdown);
                    });
                    
                    dropdown.appendChild(closeBtn);
                    
                    // Add to body
                    document.body.appendChild(dropdown);
                    
                    // Close dropdown when clicking outside
                    setTimeout(() => {
                        const closeDropdown = function(e) {
                            if (!dropdown.contains(e.target) && e.target !== titleEl) {
                                document.body.removeChild(dropdown);
                                document.removeEventListener('click', closeDropdown);
                            }
                        };
                        document.addEventListener('click', closeDropdown);
                    }, 10);
                });
            }
        }, 500); // Wait for calendar to render
        
        // ============================================
        // END MONTH DROPDOWN
        // ============================================
        
        // FIX: Update calendar size when tab becomes visible
        document.getElementById('calendar-tab').addEventListener('shown.bs.tab', function() {
            setTimeout(function() {
                calendar.updateSize();
            }, 50);
        });
        
        // Also fix on window resize
        window.addEventListener('resize', function() {
            calendar.updateSize();
        });
        
        // Initial size fix (in case calendar tab is already active)
        setTimeout(function() {
            if (document.getElementById('calendar').classList.contains('active')) {
                calendar.updateSize();
            }
        }, 200);
        
        // Refresh button
        document.getElementById("refreshCalendar")?.addEventListener("click", function() {
            calendar.refetchEvents();
            showToast("success", "Calendar refreshed");
        });
        
        // Fetch appointments from server
        async function fetchAppointments(start, end) {
            try {
                const response = await fetch(`/appointments/calendar-data?start=${start.toISOString()}&end=${end.toISOString()}`);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                const data = await response.json();
                return data.events || [];
            } catch (error) {
                console.error("Error fetching calendar data:", error);
                showToast("error", "Failed to load appointments");
                return [];
            }
        }
        
        // Toast function
        function showToast(type, message) {
            const toast = `<div class="toast align-items-center text-bg-${type} border-0" role="alert">
                <div class="d-flex">
                    <div class="toast-body">${message}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>`;
            
            const toastContainer = document.getElementById("toastContainer") || (() => {
                const container = document.createElement("div");
                container.id = "toastContainer";
                container.className = "toast-container position-fixed bottom-0 end-0 p-3";
                document.body.appendChild(container);
                return container;
            })();
            
            toastContainer.innerHTML += toast;
            const toastEl = toastContainer.lastElementChild;
            const bsToast = new bootstrap.Toast(toastEl, { delay: 3000 });
            bsToast.show();
            
            toastEl.addEventListener("hidden.bs.toast", function() {
                toastEl.remove();
            });
        }
    }
});
</script>
@endpush

<script>
// Simple check-in function that will definitely work
function checkInCustomer(appointmentId, appointmentNumber, customerName) {
    console.log('Check-in button clicked!', appointmentId, appointmentNumber, customerName);
    
    // Check if SweetAlert2 is loaded
    if (typeof Swal === 'undefined') {
        alert('Error: SweetAlert2 not loaded. Please refresh page.');
        return;
    }
    
    // Check if jQuery is loaded
    if (typeof jQuery === 'undefined') {
        alert('Error: jQuery not loaded. Please refresh page.');
        return;
    }
    
    Swal.fire({
        title: 'Check In Customer',
        html: 'Are you sure you want to check in <strong>' + customerName + '</strong> for appointment <strong>' + appointmentNumber + '</strong>?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, Check In',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#198754',
    }).then((result) => {
        if (result.isConfirmed) {
            console.log('Sending AJAX request for appointment:', appointmentId);
            
            $.ajax({
                url: '/appointments/' + appointmentId + '/ajax-check-in',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    console.log('AJAX success:', response);
                    if (response.success) {
                        Swal.fire({
                            title: 'Success!',
                            html: response.message + '<br><br>' + (response.redirect_message || ''),
                            icon: 'success',
                            showCancelButton: true,
                            confirmButtonText: 'Go to Inspection',
                            cancelButtonText: 'Stay Here',
                            confirmButtonColor: '#198754',
                        }).then((result) => {
                            if (result.isConfirmed && response.inspection_url) {
                                // Redirect to the vehicle inspection
                                window.location.href = response.inspection_url;
                            } else {
                                // Reload the page to show updated status
                                location.reload();
                            }
                        });
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function(xhr) {
                    console.error('AJAX error:', xhr);
                    Swal.fire('Error', 'Failed to check in appointment. Please try again.', 'error');
                }
            });
        }
    });
}

// Cancel appointment function
function cancelAppointment(appointmentId, appointmentNumber, customerName) {
    console.log('Cancel button clicked!', appointmentId, appointmentNumber, customerName);
    
    // Check if SweetAlert2 is loaded
    if (typeof Swal === 'undefined') {
        alert('Error: SweetAlert2 not loaded. Please refresh page.');
        return;
    }
    
    // Check if jQuery is loaded
    if (typeof jQuery === 'undefined') {
        alert('Error: jQuery not loaded. Please refresh page.');
        return;
    }
    
    Swal.fire({
        title: 'Cancel Appointment',
        html: 'Are you sure you want to cancel appointment <strong>' + appointmentNumber + '</strong> for <strong>' + customerName + '</strong>?<br><br><small class="text-muted">This will move the appointment to the Cancelled tab.</small>',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, Cancel',
        cancelButtonText: 'No, Keep Scheduled',
        confirmButtonColor: '#dc3545',
        reverseButtons: true,
        input: 'text',
        inputLabel: 'Cancellation Reason (Optional)',
        inputPlaceholder: 'Enter reason for cancellation...',
        inputAttributes: {
            maxlength: 255
        },
        showLoaderOnConfirm: true,
        preConfirm: (reason) => {
            return new Promise((resolve, reject) => {
                $.ajax({
                    url: '/appointments/' + appointmentId + '/ajax-cancel',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        cancellation_reason: reason || ''
                    },
                    success: function(response) {
                        console.log('AJAX success:', response);
                        resolve(response);
                    },
                    error: function(xhr) {
                        console.error('AJAX error:', xhr);
                        reject('Failed to cancel appointment. Please try again.');
                    }
                });
            });
        },
        allowOutsideClick: () => !Swal.isLoading()
    }).then((result) => {
        if (result.isConfirmed) {
            if (result.value && result.value.success) {
                Swal.fire({
                    title: 'Cancelled!',
                    html: 'Appointment <strong>' + appointmentNumber + '</strong> has been cancelled.<br><br>' + 
                          (result.value.cancelled_at ? 'Cancelled on: <strong>' + result.value.cancelled_at + '</strong><br>' : '') +
                          (result.value.cancellation_reason ? 'Reason: ' + result.value.cancellation_reason : ''),
                    icon: 'success',
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#dc3545',
                }).then(() => {
                    // Reload the page to show updated status
                    location.reload();
                });
            } else {
                Swal.fire('Error', result.value?.message || 'Failed to cancel appointment.', 'error');
            }
        }
    });
}

// Confirm appointment (from customer_booked to confirmed)
function confirmAppointment(appointmentId, appointmentNumber, customerName) {
    console.log('Confirm button clicked!', appointmentId, appointmentNumber, customerName);
    
    // Check if SweetAlert2 is loaded
    if (typeof Swal === 'undefined') {
        alert('Error: SweetAlert2 not loaded. Please refresh page.');
        return;
    }
    
    // Check if jQuery is loaded
    if (typeof jQuery === 'undefined') {
        alert('Error: jQuery not loaded. Please refresh page.');
        return;
    }
    
    Swal.fire({
        title: 'Confirm Appointment',
        html: 'Confirm appointment <strong>' + appointmentNumber + '</strong> for <strong>' + customerName + '</strong>?<br><br><small class="text-muted">This will set the status to Confirmed.</small>',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, Confirm',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#198754',
        reverseButtons: true,
        showLoaderOnConfirm: true,
        preConfirm: () => {
            return new Promise((resolve, reject) => {
                $.ajax({
                    url: '/appointments/' + appointmentId + '/confirm-booking',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        console.log('AJAX success:', response);
                        resolve(response);
                    },
                    error: function(xhr) {
                        console.error('AJAX error:', xhr);
                        reject('Failed to confirm appointment. Please try again.');
                    }
                });
            });
        },
        allowOutsideClick: () => !Swal.isLoading()
    }).then((result) => {
        if (result.isConfirmed) {
            if (result.value && result.value.success) {
                Swal.fire({
                    title: 'Confirmed!',
                    html: 'Appointment <strong>' + appointmentNumber + '</strong> has been confirmed.',
                    icon: 'success',
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#198754',
                }).then(() => {
                    // Reload the page to show updated status
                    location.reload();
                });
            } else {
                Swal.fire('Error', result.value?.message || 'Failed to confirm appointment.', 'error');
            }
        }
    });
}

// Restore cancelled appointment function
function restoreAppointment(appointmentId, appointmentNumber, customerName) {
    console.log('Restore button clicked!', appointmentId, appointmentNumber, customerName);
    
    // Check if SweetAlert2 is loaded
    if (typeof Swal === 'undefined') {
        alert('Error: SweetAlert2 not loaded. Please refresh page.');
        return;
    }
    
    // Check if jQuery is loaded
    if (typeof jQuery === 'undefined') {
        alert('Error: jQuery not loaded. Please refresh page.');
        return;
    }
    
    Swal.fire({
        title: 'Restore Appointment',
        html: 'Are you sure you want to restore appointment <strong>' + appointmentNumber + '</strong> for <strong>' + customerName + '</strong>?<br><br><small class="text-muted">This will move the appointment back to the Scheduled tab.</small>',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, Restore',
        cancelButtonText: 'No, Keep Cancelled',
        confirmButtonColor: '#198754',
        reverseButtons: true,
        showLoaderOnConfirm: true,
        preConfirm: () => {
            return new Promise((resolve, reject) => {
                $.ajax({
                    url: '/appointments/' + appointmentId + '/ajax-restore',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        console.log('AJAX success:', response);
                        resolve(response);
                    },
                    error: function(xhr) {
                        console.error('AJAX error:', xhr);
                        reject('Failed to restore appointment. Please try again.');
                    }
                });
            });
        },
        allowOutsideClick: () => !Swal.isLoading()
    }).then((result) => {
        if (result.isConfirmed) {
            if (result.value && result.value.success) {
                Swal.fire({
                    title: 'Restored!',
                    html: 'Appointment <strong>' + appointmentNumber + '</strong> has been restored to Scheduled status.',
                    icon: 'success',
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#198754',
                }).then(() => {
                    // Reload the page to show updated status
                    location.reload();
                });
            } else {
                Swal.fire('Error', result.value?.message || 'Failed to restore appointment.', 'error');
            }
        }
    });
}

// Start appointment: ipakita muna lahat ng info para ma-verify bago mag-start
function startAppointment(appointmentId) {
    var infoEl = document.getElementById('start-info-' + appointmentId);
    var html = infoEl ? infoEl.innerHTML : '';
    var form = document.getElementById('start-form-' + appointmentId);
    if (!form) { return; }

    if (typeof Swal === 'undefined') {
        if (confirm('Simulan na ang trabaho? Siguraduhin tama lahat ng information.')) {
            form.submit();
        }
        return;
    }

    Swal.fire({
        title: 'Tama ba lahat ng information?',
        html: '<div style="text-align:left">' + html + '</div>',
        icon: 'question',
        width: 620,
        showCancelButton: true,
        confirmButtonText: 'Oo, Start',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#0dcaf0',
    }).then(function(result) {
        if (result.isConfirmed) {
            form.submit();
        }
    });
}
</script>

<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-calendar-alt me-2"></i>Appointments
            </h1>
            <p class="text-muted mb-0">Scheduled bookings | Status: Scheduled | Cancelled</p>
        </div>
        <div>
            <a href="{{ route('appointments.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> Schedule Appointment
            </a>
        </div>
    </div>
</div>

<!-- Filter Bar -->
<div class="card mb-3">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('appointments.index') }}" class="row g-2 align-items-end">
            <div class="col-md-2">
                <label class="form-label small mb-1">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Statuses</option>
                    @foreach(['scheduled', 'confirmed', 'customer_booked', 'checked_in', 'in_progress', 'completed', 'cancelled', 'no_show', 'rescheduled'] as $st)
                        <option value="{{ $st }}" {{ ($filters['status'] ?? '') == $st ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $st)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">From</label>
                <input type="date" name="date_from" class="form-control form-control-sm" value="{{ $filters['date_from'] ?? '' }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">To</label>
                <input type="date" name="date_to" class="form-control form-control-sm" value="{{ $filters['date_to'] ?? '' }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">Customer</label>
                <select name="customer_id" class="form-select form-select-sm">
                    <option value="">All Customers</option>
                    @foreach($customers as $customer)
                        <option value="{{ $customer->id }}" {{ ($filters['customer_id'] ?? '') == $customer->id ? 'selected' : '' }}>{{ $customer->first_name }} {{ $customer->last_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">Vehicle</label>
                <select name="vehicle_id" class="form-select form-select-sm">
                    <option value="">All Vehicles</option>
                    @foreach($vehicles as $vehicle)
                        <option value="{{ $vehicle->id }}" {{ ($filters['vehicle_id'] ?? '') == $vehicle->id ? 'selected' : '' }}>{{ $vehicle->year }} {{ $vehicle->make }} {{ $vehicle->model }}{{ $vehicle->license_plate ? ' ['.$vehicle->license_plate.']' : '' }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-filter me-1"></i> Filter</button>
                <a href="{{ route('appointments.index') }}" class="btn btn-outline-secondary btn-sm">Clear</a>
            </div>
        </form>
    </div>
</div>

<!-- Status Tabs -->
<div class="card mb-4">
    <div class="card-body p-0">
        <ul class="nav nav-tabs nav-tabs-custom" id="appointmentTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="scheduled-tab" data-bs-toggle="tab" data-bs-target="#scheduled" type="button" role="tab">
                    <i class="fas fa-clock me-1"></i> Scheduled ({{ $stats['scheduled'] }})
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="arrived-tab" data-bs-toggle="tab" data-bs-target="#arrived" type="button" role="tab">
                    <i class="fas fa-car me-1"></i> Arrived ({{ $stats['arrived'] }})
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="calendar-tab" data-bs-toggle="tab" data-bs-target="#calendar" type="button" role="tab">
                    <i class="fas fa-calendar-alt me-1"></i> Calendar
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="cancelled-tab" data-bs-toggle="tab" data-bs-target="#cancelled" type="button" role="tab">
                    <i class="fas fa-times-circle me-1"></i> Cancelled ({{ $stats['cancelled'] }})
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="online-tab" data-bs-toggle="tab" data-bs-target="#online" type="button" role="tab">
                    <i class="fas fa-globe me-1"></i> Online Bookings ({{ $stats['online'] }})
                </button>
            </li>
        </ul>
        
        <div class="tab-content p-3" id="appointmentTabsContent">
            <!-- Scheduled Tab -->
            <div class="tab-pane fade show active" id="scheduled" role="tabpanel">
                @if($scheduledAppointments->count() > 0)
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <span class="text-muted">{{ $scheduledAppointments->count() }} appointments</span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            @if($scheduledAppointments->whereNull('viewed_at')->count() > 0 && $scheduledAppointments->count() > 0)
                                <button class="btn-mark-all-read" id="markAllReadBtn" onclick="markAllAsRead('appointments', this)">
                                    <i class="fas fa-check-double"></i> Mark All as Read
                                </button>
                            @endif
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover" id="appointmentsTable">
                            <thead>
                                <tr>
                                    <th style="width:30px;"></th>
                                    <th>Appointment #</th>
                                    <th>Customer & Vehicle</th>
                                    <th>Date & Time</th>
                                    <th>Service Type</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($scheduledAppointments as $appointment)
                                <tr class="{{ $appointment->viewed_at === null ? 'tr-unread' : '' }}" data-id="{{ $appointment->id }}">
                                    <td>
                                        @if($appointment->viewed_at === null)
                                            <span class="unread-dot" title="New"></span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="{{ $appointment->viewed_at === null ? 'unread-primary-text' : '' }}">
                                            <strong>{{ $appointment->appointment_number }}</strong>
                                            @if($appointment->viewed_at === null)
                                                <span class="badge-new-record">NEW</span>
                                            @endif
                                        </span>
                                        @if($appointment->is_waitlist)
                                            <br>
                                            <span class="badge bg-warning">Waitlist</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div>
                                            @php
                                                $nameIcon = '';
                                                $nameClass = '';
                                                switch($appointment->appointment_status) {
                                                    case 'cancelled':
                                                        $nameIcon = '❌ ';
                                                        $nameClass = 'text-decoration-line-through text-danger';
                                                        break;
                                                    case 'no_show':
                                                        $nameIcon = '👤❌ ';
                                                        $nameClass = 'text-decoration-line-through text-secondary';
                                                        break;
                                                    case 'scheduled':
                                                    case 'confirmed':
                                                        $nameIcon = '✅ ';
                                                        $nameClass = 'text-success';
                                                        break;
                                                    case 'completed':
                                                        $nameIcon = '✔️ ';
                                                        $nameClass = 'text-success';
                                                        break;
                                                    case 'in_progress':
                                                        $nameIcon = '⚙️ ';
                                                        $nameClass = 'text-info';
                                                        break;
                                                    case 'arrived':
                                                        $nameIcon = '🚗 ';
                                                        $nameClass = 'text-teal';
                                                        break;
                                                    case 'customer_booked':
                                                        $nameIcon = '📋 ';
                                                        $nameClass = 'text-purple';
                                                        break;
                                                    case 'rescheduled':
                                                        $nameIcon = '🔄 ';
                                                        $nameClass = 'text-warning';
                                                        break;
                                                    default:
                                                        $nameIcon = '📅 ';
                                                        $nameClass = '';
                                                }
                                            @endphp
                                            <span class="{{ $appointment->viewed_at === null ? 'unread-primary-text' : '' }}">
                                                <strong class="{{ $nameClass }}">
                                                    {!! $nameIcon !!}{{ $appointment->customer->full_name }}
                                                </strong>
                                            </span>
                                            <br>
                                            <small class="text-muted">
                                                <i class="fas fa-car me-1"></i>
                                                {{ $appointment->vehicle_description }}
                                            </small>
                                            <br>
                                            <small class="text-muted"></small>
                                        </div>
                                    </td>
                                    <td>
                                        <strong>{{ $appointment->appointment_date->format('M d, Y') }}</strong>
                                        <br>
                                        <span class="text-muted">{{ date('g:i A', strtotime($appointment->appointment_time)) }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ ucfirst(str_replace('_', ' ', $appointment->appointment_type)) }}</span>
                                    </td>
                                    <td>
                                        @php
                                            $statusIcon = '';
                                            switch($appointment->appointment_status) {
                                                case 'scheduled':
                                                case 'confirmed':
                                                    $statusIcon = '✅ ';
                                                    break;
                                                case 'cancelled':
                                                    $statusIcon = '❌ ';
                                                    break;
                                                case 'no_show':
                                                    $statusIcon = '👤❌ ';
                                                    break;
                                                case 'rescheduled':
                                                    $statusIcon = '🔄 ';
                                                    break;
                                                case 'completed':
                                                    $statusIcon = '✔️ ';
                                                    break;
                                                case 'in_progress':
                                                    $statusIcon = '⚙️ ';
                                                    break;
                                                case 'arrived':
                                                    $statusIcon = '🚗 ';
                                                    break;
                                                case 'customer_booked':
                                                    $statusIcon = '📋 ';
                                                    break;
                                                default:
                                                    $statusIcon = '📅 ';
                                            }
                                        @endphp
                                        <span class="badge bg-{{ $appointment->status_color }}">
                                            {!! $statusIcon !!}{{ ucfirst(str_replace('_', ' ', $appointment->appointment_status)) }}
                                        </span>
                                        @if(in_array($appointment->booking_source, ['website', 'online']))
                                            <br><small class="badge bg-info mt-1" style="font-weight:400;font-size:10px;"><i class="fas fa-globe me-1"></i>Online Booking</small>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            @if($appointment->appointment_status === 'customer_booked')
                                                <!-- QUICK ACTIONS for Customer Booked -->
                                                <button type="button" class="btn btn-success btn-sm" 
                                                        onclick="confirmAppointment({{ $appointment->id }}, '{{ $appointment->appointment_number }}', '{{ addslashes($appointment->customer->full_name) }}')">
                                                    <i class="fas fa-check me-1"></i> Confirm
                                                </button>
                                                <a href="{{ route('appointments.edit', $appointment) }}" class="btn btn-warning btn-sm">
                                                    <i class="fas fa-calendar-alt me-1"></i> Reschedule
                                                </a>
                                                <button type="button" class="btn btn-success btn-sm check-in-btn" 
                                                        onclick="checkInCustomer({{ $appointment->id }}, '{{ $appointment->appointment_number }}', '{{ addslashes($appointment->customer->full_name) }}')">
                                                    <i class="fas fa-check-circle me-1"></i> Check In
                                                </button>
                                            @else
                                                <!-- PRIMARY ACTION: Check In Customer -->
                                                <button type="button" class="btn btn-success btn-sm check-in-btn" 
                                                        onclick="checkInCustomer({{ $appointment->id }}, '{{ $appointment->appointment_number }}', '{{ addslashes($appointment->customer->full_name) }}')"
                                                        data-appointment-id="{{ $appointment->id }}"
                                                        data-appointment-number="{{ $appointment->appointment_number }}"
                                                        data-customer-name="{{ $appointment->customer->full_name }}">
                                                    <i class="fas fa-check-circle me-1"></i> Check In
                                                </button>
                                            @endif
                                            
                                            <!-- Convert to Estimate -->
                                            @php
                                                $inspection = $appointment->vehicleInspection;
                                                $estimateParams = $inspection 
                                                    ? ['inspection_id' => $inspection->id] 
                                                    : ['appointment_id' => $appointment->id];
                                            @endphp
                                            <a href="{{ route('estimates.create', $estimateParams) }}" 
                                               class="btn btn-primary btn-sm">
                                                <i class="fas fa-file-invoice-dollar me-1"></i> Create Estimate
                                            </a>
                                            
                                            <!-- View Details -->
                                            <a href="{{ route('appointments.show', $appointment) }}" class="btn btn-outline-primary btn-sm">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            
                                            <!-- Cancel Appointment -->
                                            <button type="button" class="btn btn-outline-danger btn-sm cancel-btn"
                                                    onclick="cancelAppointment({{ $appointment->id }}, '{{ $appointment->appointment_number }}', '{{ addslashes($appointment->customer->full_name) }}')"
                                                    data-appointment-id="{{ $appointment->id }}"
                                                    data-appointment-number="{{ $appointment->appointment_number }}"
                                                    title="Cancel Appointment">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-calendar-times fa-4x text-muted mb-3"></i>
                        <h4 class="text-muted">No scheduled appointments</h4>
                        <p class="text-muted">All appointments have been processed or cancelled</p>
                    </div>
                @endif
            </div>
            
            <!-- Arrived Tab -->
            <div class="tab-pane fade" id="arrived" role="tabpanel">
                @if($arrivedAppointments->count() > 0)
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <span class="text-muted">{{ $arrivedAppointments->count() }} arrived / checked-in appointments</span>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Appointment #</th>
                                    <th>Customer & Vehicle</th>
                                    <th>Date & Time</th>
                                    <th>Service Type</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($arrivedAppointments as $appointment)
                                <tr data-id="{{ $appointment->id }}">
                                    <td>
                                        <strong>{{ $appointment->appointment_number }}</strong>
                                    </td>
                                    <td>
                                        <strong class="text-teal">🚗 {{ $appointment->customer->full_name }}</strong>
                                        <br>
                                        <small class="text-muted">
                                            <i class="fas fa-car me-1"></i>
                                            {{ $appointment->vehicle_description }}
                                        </small>
                                    </td>
                                    <td>
                                        <strong>{{ $appointment->appointment_date->format('M d, Y') }}</strong>
                                        <br>
                                        <span class="text-muted">
                                            @if($appointment->checked_in_at)
                                                Checked in {{ $appointment->checked_in_at->format('g:i A') }}
                                            @else
                                                {{ date('g:i A', strtotime($appointment->appointment_time)) }}
                                            @endif
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ ucfirst(str_replace('_', ' ', $appointment->appointment_type)) }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-warning text-dark">🚗 Arrived</span>
                                    </td>
                                    <td>
                                        <div id="start-info-{{ $appointment->id }}" class="d-none">
                                            <table class="table table-sm mb-0" style="font-size:12.5px">
                                                <tr><th class="text-start">Appointment #</th><td>{{ $appointment->appointment_number }}</td></tr>
                                                <tr><th class="text-start">Schedule</th><td>{{ $appointment->appointment_date ? $appointment->appointment_date->format('M d, Y') : '' }} {{ $appointment->appointment_time }}</td></tr>
                                                <tr><th class="text-start">Customer</th><td>{{ $appointment->customer->full_name ?? '' }}</td></tr>
                                                <tr><th class="text-start">Phone</th><td>{{ $appointment->customer->phone ?? '' }}</td></tr>
                                                <tr><th class="text-start">Address</th><td>{{ $appointment->customer->address ?? '' }}</td></tr>
                                                <tr><th class="text-start">Vehicle</th><td>{{ trim(($appointment->vehicle->year ?? '') . ' ' . ($appointment->vehicle->make ?? '') . ' ' . ($appointment->vehicle->model ?? '')) ?: ($appointment->vehicle_description ?? '') }}</td></tr>
                                                <tr><th class="text-start">Plate No.</th><td>{{ $appointment->vehicle->license_plate ?? '' }}</td></tr>
                                                <tr><th class="text-start">VIN</th><td>{{ $appointment->vehicle->vin ?? '' }}</td></tr>
                                                <tr><th class="text-start">Engine No.</th><td>{{ $appointment->vehicle->engine_no ?? '' }}</td></tr>
                                                <tr><th class="text-start">Transmission</th><td>{{ $appointment->vehicle->transmission ?? '' }}</td></tr>
                                                <tr><th class="text-start">Fuel</th><td>{{ $appointment->vehicle->fuel_type ?? '' }}</td></tr>
                                                <tr><th class="text-start">Odometer</th><td>{{ $appointment->vehicle->odometer ?? '' }}</td></tr>
                                                <tr><th class="text-start">Service</th><td>{{ ucfirst(str_replace('_', ' ', $appointment->appointment_type)) }}</td></tr>
                                                <tr><th class="text-start">Request</th><td>{{ $appointment->service_request ?? '' }}</td></tr>
                                            </table>
                                        </div>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('appointments.update-info.form', $appointment) }}" class="btn btn-warning btn-sm">
                                                <i class="fas fa-edit me-1"></i> Update Info
                                            </a>
                                            <button type="button" class="btn btn-info btn-sm"
                                                    onclick="startAppointment({{ $appointment->id }})">
                                                <i class="fas fa-play me-1"></i> Start
                                            </button>
                                            <a href="{{ route('appointments.show', $appointment) }}" class="btn btn-outline-primary btn-sm">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <button type="button" class="btn btn-outline-danger btn-sm cancel-btn"
                                                    onclick="cancelAppointment({{ $appointment->id }}, '{{ $appointment->appointment_number }}', '{{ addslashes($appointment->customer->full_name) }}')"
                                                    data-appointment-id="{{ $appointment->id }}"
                                                    data-appointment-number="{{ $appointment->appointment_number }}"
                                                    title="Cancel Appointment">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                        <form id="start-form-{{ $appointment->id }}" action="{{ route('appointments.start', $appointment) }}" method="POST" class="d-none">
                                            @csrf
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-car fa-4x text-muted mb-3"></i>
                        <h4 class="text-muted">No arrived appointments</h4>
                        <p class="text-muted">Checked-in appointments will appear here</p>
                    </div>
                @endif
            </div>
            
            <!-- Calendar Tab -->
            <div class="tab-pane fade" id="calendar" role="tabpanel">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0">Appointments Calendar</h5>
                            <button class="btn btn-primary btn-sm" id="refreshCalendar">
                                <i class="fas fa-sync-alt me-2"></i>Refresh
                            </button>
                        </div>
                        
                        <!-- Calendar Navigation Help - WITH TOGGLE -->
                        <div class="card mb-3 border-info" id="calendarHelpCard">
                            <div class="card-header bg-info text-white d-flex justify-content-between align-items-center py-2">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-info-circle fa-lg me-2"></i>
                                    <h6 class="mb-0">How to Navigate the Calendar</h6>
                                </div>
                                <button type="button" class="btn btn-sm btn-light" id="toggleHelpCard">
                                    <i class="fas fa-chevron-up" id="helpCardIcon"></i> Hide
                                </button>
                            </div>
                            <div class="card-body" id="helpCardBody">
                                <div class="row">
                                    <div class="col-md-6">
                                        <ul class="mb-0">
                                            <li><strong>Month Navigation:</strong>
                                                <ul>
                                                    <li>Use <span class="badge bg-primary">◀</span> and <span class="badge bg-primary">▶</span> arrow buttons</li>
                                                    <li><strong>CLICK "March 2026" title</strong> for month/year dropdown</li>
                                                    <li>Select any month from dropdown</li>
                                                </ul>
                                            </li>
                                            <li><strong>Week View:</strong> Use <span class="badge bg-primary">◀</span> <span class="badge bg-primary">▶</span> to navigate weeks</li>
                                            <li><strong>Day View:</strong> Use <span class="badge bg-primary">◀</span> <span class="badge bg-primary">▶</span> to navigate days</li>
                                            <li><strong>Today Button:</strong> Click <span class="badge bg-success">Today</span> to return to current date</li>
                                        </ul>
                                    </div>
                                    <div class="col-md-6">
                                        <ul class="mb-0">
                                            <li><strong>Switch Views:</strong> Use: <span class="badge bg-secondary">Month</span> <span class="badge bg-secondary">Week</span> <span class="badge bg-secondary">Day</span> <span class="badge bg-secondary">List</span></li>
                                            <li><strong>Quick Navigation:</strong>
                                                <ul>
                                                    <li>Click any <strong>date</strong> → Switch to Day view</li>
                                                    <li>Click any <strong>week number</strong> → Switch to Week view</li>
                                                    <li>Click any <strong>appointment</strong> → View details</li>
                                                </ul>
                                            </li>
                                            <li><strong>Hover:</strong> Hover over events for more information</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div id="appointmentsCalendar"></div>
                    </div>
                </div>
                
                <!-- Legend -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card shadow-sm border-0">
                            <div class="card-body">
                                <h6 class="mb-3"><i class="fas fa-palette me-2"></i>Status Legend</h6>
                                <div class="d-flex flex-wrap gap-3">
                                    <div class="d-flex align-items-center">
                                        <span class="badge bg-primary me-2" style="width: 20px; height: 20px;"></span>
                                        <span>Scheduled</span>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <span class="badge bg-success me-2" style="width: 20px; height: 20px;"></span>
                                        <span>Arrived</span>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <span class="badge bg-warning me-2" style="width: 20px; height: 20px;"></span>
                                        <span>In Progress</span>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <span class="badge bg-info me-2" style="width: 20px; height: 20px;"></span>
                                        <span>Completed</span>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <span class="badge bg-danger me-2" style="width: 20px; height: 20px;"></span>
                                        <span>Cancelled</span>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <span class="badge bg-secondary me-2" style="width: 20px; height: 20px;"></span>
                                        <span>No Show</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Cancelled Tab -->
            <div class="tab-pane fade" id="cancelled" role="tabpanel">
                @if($cancelledAppointments->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Appointment #</th>
                                    <th>Customer & Vehicle</th>
                                    <th>Original Date</th>
                                    <th>Cancelled On</th>
                                    <th>Reason</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($cancelledAppointments as $appointment)
                                <tr>
                                    <td>
                                        <strong>{{ $appointment->appointment_number }}</strong>
                                    </td>
                                    <td>
                                        <div>
                                            <strong class="text-decoration-line-through text-danger">
                                                ❌ {{ $appointment->customer->full_name }}
                                            </strong>
                                            <br>
                                            <small class="text-muted">
                                                <i class="fas fa-car me-1"></i>
                                                {{ $appointment->vehicle_description }}
                                            </small>
                                        </div>
                                    </td>
                                    <td>
                                        <strong>{{ $appointment->appointment_date->format('M d, Y') }}</strong>
                                        <br>
                                        <span class="text-muted">{{ date('g:i A', strtotime($appointment->appointment_time)) }}</span>
                                    </td>
                                    <td>
                                        @if($appointment->cancelled_at)
                                            <strong>{{ $appointment->cancelled_at->format('M d, Y') }}</strong>
                                            <br>
                                            <span class="text-muted">{{ $appointment->cancelled_at->format('g:i A') }}</span>
                                        @else
                                            <span class="text-muted">Not recorded</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-danger">{{ $appointment->cancellation_reason ?? 'No reason given' }}</span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <!-- Restore Button -->
                                            <button type="button" 
                                                    class="btn btn-outline-success btn-sm restore-btn"
                                                    onclick="restoreAppointment({{ $appointment->id }}, '{{ $appointment->appointment_number }}', '{{ addslashes($appointment->customer->full_name) }}')"
                                                    title="Restore to Scheduled">
                                                <i class="fas fa-undo me-1"></i> Restore
                                            </button>
                                            
                                            <!-- Reschedule -->
                                            <a href="{{ route('appointments.edit', $appointment) }}" class="btn btn-outline-primary btn-sm">
                                                <i class="fas fa-calendar-plus me-1"></i> Reschedule
                                            </a>
                                            
                                            <!-- View Details -->
                                            <a href="{{ route('appointments.show', $appointment) }}" class="btn btn-outline-secondary btn-sm">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-ban fa-4x text-muted mb-3"></i>
                        <h4 class="text-muted">No cancelled appointments</h4>
                        <p class="text-muted">Great! No appointments have been cancelled</p>
                    </div>
                @endif
            </div>

            <!-- Online Bookings Tab -->
            <div class="tab-pane fade" id="online" role="tabpanel">
                @if($onlineBookings->count() > 0)
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <span class="text-muted">{{ $onlineBookings->count() }} online bookings</span>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Appointment #</th>
                                    <th>Customer & Vehicle</th>
                                    <th>Date & Time</th>
                                    <th>Service Type</th>
                                    <th>Status</th>
                                    <th>Booked</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($onlineBookings as $appointment)
                                <tr>
                                    <td><strong>{{ $appointment->appointment_number }}</strong></td>
                                    <td>
                                        <strong>{{ $appointment->customer->full_name }}</strong>
                                        <br>
                                        <small class="text-muted"><i class="fas fa-car me-1"></i>{{ $appointment->vehicle_description }}</small>
                                    </td>
                                    <td>
                                        <strong>{{ $appointment->appointment_date->format("M d, Y") }}</strong>
                                        <br>
                                        <span class="text-muted">{{ date("g:i A", strtotime($appointment->appointment_time)) }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ ucfirst(str_replace("_", " ", $appointment->appointment_type)) }}</span>
                                    </td>
                                    <td>
                                        @php
                                            $statusIcon = match($appointment->appointment_status) {
                                                "customer_booked" => "\xF0\x9F\x93\x8B ",
                                                "confirmed" => "\xE2\x9C\x85 ",
                                                "scheduled" => "\xE2\x9C\x85 ",
                                                "rescheduled" => "\xF0\x9F\x94\x84 ",
                                                "cancelled" => "\xE2\x9D\x8C ",
                                                default => "\xF0\x9F\x93\x85 ",
                                            };
                                        @endphp
                                        <span class="badge bg-{{ $appointment->status_color }}">
                                            {!! $statusIcon !!}{{ ucfirst(str_replace("_", " ", $appointment->appointment_status)) }}
                                        </span>
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ $appointment->created_at->diffForHumans() }}</small>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route("appointments.show", $appointment) }}" class="btn btn-outline-primary btn-sm">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @if($appointment->appointment_status === "customer_booked")
                                                <button type="button" class="btn btn-success btn-sm" 
                                                        onclick="confirmAppointment({{ $appointment->id }}, {{ json_encode($appointment->appointment_number) }}, {{ json_encode($appointment->customer->full_name) }})">
                                                    <i class="fas fa-check me-1"></i> Confirm
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-globe fa-3x text-muted mb-3"></i>
                        <h4 class="text-muted">No online bookings yet</h4>
                        <p class="text-muted">Online bookings from the customer portal will appear here</p>
                    </div>
                @endif
            </div>

        </div>
    </div>
</div>





@push('scripts')
<script>
// Unread system initialization for appointments table
document.addEventListener('DOMContentLoaded', function() {
    const table = document.getElementById('appointmentsTable');
    if (table) {
        initUnreadSystem(table, 'appointments', { dataAttr: 'data-id', markAllBtnId: 'markAllReadBtn' });
    }
});

// Toggle functionality for calendar help card
document.addEventListener('DOMContentLoaded', function() {
    const helpCard = document.getElementById('calendarHelpCard');
    const toggleButton = document.getElementById('toggleHelpCard');
    const helpCardBody = document.getElementById('helpCardBody');
    const helpCardIcon = document.getElementById('helpCardIcon');
    
    if (toggleButton && helpCardBody) {
        // Check localStorage for saved state
        const isCollapsed = localStorage.getItem('calendarHelpCollapsed') === 'true';
        
        // Apply initial state
        if (isCollapsed) {
            helpCardBody.style.display = 'none';
            helpCardIcon.className = 'fas fa-chevron-down';
            toggleButton.innerHTML = '<i class="fas fa-chevron-down" id="helpCardIcon"></i> Show';
        } else {
            helpCardBody.style.display = 'block';
            helpCardIcon.className = 'fas fa-chevron-up';
            toggleButton.innerHTML = '<i class="fas fa-chevron-up" id="helpCardIcon"></i> Hide';
        }
        
        // Toggle button click handler
        toggleButton.addEventListener('click', function() {
            if (helpCardBody.style.display === 'none') {
                // Show the help card
                helpCardBody.style.display = 'block';
                helpCardIcon.className = 'fas fa-chevron-up';
                toggleButton.innerHTML = '<i class="fas fa-chevron-up" id="helpCardIcon"></i> Hide';
                localStorage.setItem('calendarHelpCollapsed', 'false');
            } else {
                // Hide the help card
                helpCardBody.style.display = 'none';
                helpCardIcon.className = 'fas fa-chevron-down';
                toggleButton.innerHTML = '<i class="fas fa-chevron-down" id="helpCardIcon"></i> Show';
                localStorage.setItem('calendarHelpCollapsed', 'true');
            }
        });
        
        // Also save state when calendar tab is shown (in case user switches tabs)
        const calendarTab = document.getElementById('calendar-tab');
        if (calendarTab) {
            calendarTab.addEventListener('shown.bs.tab', function() {
                // Re-apply the saved state when calendar tab becomes active
                setTimeout(function() {
                    const isCollapsed = localStorage.getItem('calendarHelpCollapsed') === 'true';
                    if (helpCardBody) {
                        if (isCollapsed) {
                            helpCardBody.style.display = 'none';
                            helpCardIcon.className = 'fas fa-chevron-down';
                            toggleButton.innerHTML = '<i class="fas fa-chevron-down" id="helpCardIcon"></i> Show';
                        } else {
                            helpCardBody.style.display = 'block';
                            helpCardIcon.className = 'fas fa-chevron-up';
                            toggleButton.innerHTML = '<i class="fas fa-chevron-up" id="helpCardIcon"></i> Hide';
                        }
                    }
                }, 100);
            });
        }
    }
});
</script>
@endpush

@endsection