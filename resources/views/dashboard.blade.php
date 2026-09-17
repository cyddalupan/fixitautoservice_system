@extends('layouts.app')

@section('title', 'Dashboard - Fix-It Auto Services')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <h4 class="mb-0">Dashboard</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item active">Dashboard</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- ============ QUICK STATS (moved from sidebar) ============ -->
    <div class="row g-3" id="quick-stats-row">
        <div class="col-xl-3 col-md-6">
            <div class="stat-card c-red">
                <div class="stat-card-label">Today's Appointments</div>
                <div class="stat-card-value" id="qs-today-appts">–</div>
                <div class="stat-card-sub">scheduled for today</div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card c-dark">
                <div class="stat-card-label">In Progress</div>
                <div class="stat-card-value" id="qs-in-progress">–</div>
                <div class="stat-card-sub">checked-in / working</div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card c-red">
                <div class="stat-card-label">Revenue MTD</div>
                <div class="stat-card-value" id="qs-revenue-mtd">–</div>
                <div class="stat-card-sub">this month</div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card c-dark">
                <div class="stat-card-label">Avg. Ticket</div>
                <div class="stat-card-value" id="qs-avg-ticket">–</div>
                <div class="stat-card-sub">per service record</div>
            </div>
        </div>
    </div>

    <div class="row g-3 mt-1">
        <div class="col-xl-3 col-md-6">
            <div class="stat-card c-red">
                <div class="stat-card-label">Customers</div>
                <div class="stat-card-value" id="qs-customers">–</div>
                <div class="stat-card-sub">active accounts</div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card c-dark">
                <div class="stat-card-label">Vehicles</div>
                <div class="stat-card-value" id="qs-vehicles">–</div>
                <div class="stat-card-sub">registered</div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card c-red">
                <div class="stat-card-label">Services Done</div>
                <div class="stat-card-value" id="qs-services">–</div>
                <div class="stat-card-sub">service records</div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card c-dark">
                <div class="stat-card-label">Inspections</div>
                <div class="stat-card-value" id="qs-inspections">–</div>
                <div class="stat-card-sub">vehicle health checks</div>
            </div>
        </div>
    </div>

    <!-- ============ CHARTS ============ -->
    <div class="row g-3 mt-1">
        <!-- Revenue trend -->
        <div class="col-xl-8">
            <div class="card dashboard-chart-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="fas fa-chart-area me-2 text-danger"></i>Revenue Trend</h6>
                    <span class="text-muted small">last 6 months (₱)</span>
                </div>
                <div class="card-body"><div id="chart-revenue" class="d3-chart"></div></div>
            </div>
        </div>
        <!-- Appointments by status donut -->
        <div class="col-xl-4">
            <div class="card dashboard-chart-card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-calendar-check me-2 text-danger"></i>Appointments by Status</h6>
                </div>
                <div class="card-body"><div id="chart-appt-status" class="d3-chart"></div></div>
            </div>
        </div>
    </div>

    <div class="row g-3 mt-1">
        <!-- Appointments volume (30d) -->
        <div class="col-xl-7">
            <div class="card dashboard-chart-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="fas fa-chart-bar me-2 text-danger"></i>Appointments — Last 30 Days</h6>
                    <span class="text-muted small">daily volume</span>
                </div>
                <div class="card-body"><div id="chart-appt-volume" class="d3-chart"></div></div>
            </div>
        </div>
        <!-- Top services by revenue -->
        <div class="col-xl-5">
            <div class="card dashboard-chart-card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-wrench me-2 text-danger"></i>Top Services by Revenue</h6>
                </div>
                <div class="card-body"><div id="chart-services" class="d3-chart"></div></div>
            </div>
        </div>
    </div>

    <div class="row g-3 mt-1">
        <!-- Inspection outcomes -->
        <div class="col-xl-5">
            <div class="card dashboard-chart-card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-clipboard-check me-2 text-danger"></i>Inspection Outcomes</h6>
                </div>
                <div class="card-body"><div id="chart-inspections" class="d3-chart"></div></div>
            </div>
        </div>
        <!-- Quick module links -->
        <div class="col-xl-7">
            <div class="card dashboard-chart-card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-th-large me-2 text-danger"></i>Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-6 col-md-3"><a class="btn btn-outline-danger w-100 py-3" href="{{ route('appointments.index') }}"><i class="fas fa-calendar d-block mb-1 fs-5"></i>Appointments</a></div>
                        <div class="col-6 col-md-3"><a class="btn btn-outline-danger w-100 py-3" href="{{ route('analytics') }}"><i class="fas fa-chart-pie d-block mb-1 fs-5"></i>Analytics</a></div>
                        <div class="col-6 col-md-3"><a class="btn btn-outline-danger w-100 py-3" href="{{ route('customers.index') }}"><i class="fas fa-users d-block mb-1 fs-5"></i>Customers</a></div>
                        <div class="col-6 col-md-3"><a class="btn btn-outline-danger w-100 py-3" href="{{ route('vehicles.index') }}"><i class="fas fa-car d-block mb-1 fs-5"></i>Vehicles</a></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .d3-chart { width: 100%; height: 280px; position: relative; }
    #chart-appt-status, #chart-inspections { height: 260px; }
    .d3-chart svg { display: block; margin: 0 auto; }
    .d3-tooltip {
        position: absolute; background: #10131a; color: #fff; border-radius: 6px;
        padding: 8px 12px; font-size: 0.78rem; pointer-events: none; z-index: 10;
        box-shadow: 0 8px 20px rgba(0,0,0,.35); border: 1px solid #262b36;
    }
    .axis text { font-size: 11px; fill: #94a3b8; }
    .axis path, .axis line { stroke: #2a2f38; }
    .legend text { font-size: 11px; fill: #cbd5e1; }

    /* Quick stat cards (moved from sidebar, now red/dark theme) */
    .stat-card {
        border-radius: 12px; padding: 18px 20px; color: #fff; position: relative; overflow: hidden;
        border: 1px solid #262b36; min-height: 108px; display: flex; flex-direction: column; justify-content: center;
    }
    .stat-card.c-red { background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%); }
    .stat-card.c-dark { background: linear-gradient(135deg, #181c25 0%, #10131a 100%); }
    .stat-card-label { font-size: 0.72rem; font-weight: 600; text-transform: uppercase; letter-spacing: .06em; opacity: .85; }
    .stat-card-value { font-size: 1.9rem; font-weight: 700; line-height: 1.15; font-family: 'Chakra Petch', 'Inter', sans-serif; }
    .stat-card-sub { font-size: 0.72rem; opacity: .7; }

    .dashboard-chart-card { border: 1px solid #262b36 !important; background: var(--dark-surface, #151922) !important; box-shadow: 0 10px 30px -18px rgba(0,0,0,.6); }
    .dashboard-chart-card .card-header { background: transparent; border-bottom: 1px solid #262b36; }
    .dashboard-chart-card .card-header h6 { color: #e4e7eb; }
    .btn-outline-danger { border-color: var(--red-600, #dc2626); color: #f87171; }
    .btn-outline-danger:hover { background: #dc2626; color: #fff; border-color: #dc2626; }

    @media (max-width: 768px) { .stat-card-value { font-size: 1.5rem; } }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/d3@7"></script>
<script>
(function () {
    'use strict';

    var RED = '#dc2626', RED_DARK = '#991b1b';
    var PALETTE = ['#dc2626', '#f87171', '#ef4444', '#991b1b', '#fca5a5', '#450a0a'];
    var CATS = ['#dc2626', '#f87171', '#ef4444', '#fca5a5', '#991b1b', '#fb923c', '#fbbf24', '#34d399'];

    function money(v) {
        v = Number(v) || 0;
        return '₱' + v.toLocaleString('en-PH', {maximumFractionDigits: 0});
    }

    function fmt(v) { return Number(v).toLocaleString('en-PH'); }

    function tooltip(sel) {
        var t = d3.select('body').append('div').attr('class', 'd3-tooltip').style('display', 'none');
        return t;
    }

    // Colored axis label for dark theme
    function axis(scale, orient, ticks) {
        return d3.axisBottom(scale).ticks(ticks || 6);
    }

    fetch('{{ route('dashboard-data') }}')
        .then(function (r) { return r.json(); })
        .then(function (data) {
            var t = data.totals || {};

            // ---- 1. QUICK STATS cards ----
            d3.select('#qs-today-appts').text(fmt(t.appointments_today));
            d3.select('#qs-in-progress').text(fmt(t.in_progress));
            d3.select('#qs-revenue-mtd').text(money(t.revenue_mtd));
            d3.select('#qs-avg-ticket').text(money(t.avg_ticket));
            d3.select('#qs-customers').text(fmt(t.customers));
            d3.select('#qs-vehicles').text(fmt(t.vehicles));
            d3.select('#qs-services').text(fmt(t.services));
            d3.select('#qs-inspections').text(fmt(t.inspections));

            // ---- 2. Revenue area chart ----
            revenueChart(data.monthly_revenue || []);
            // ---- 3. Appointment status donut ----
            donutChart('#chart-appt-status', data.appointment_status || [], 'appointment_status', 'count');
            // ---- 4. Appointments 30d bars ----
            volumeChart(data.appointment_volume || []);
            // ---- 5. Top services horizontal bars ----
            servicesChart(data.service_profitability || []);
            // ---- 6. Inspection outcomes ----
            donutChart('#chart-inspections', data.inspection_outcomes || [], 'inspection_status', 'count');
        })
        .catch(function (e) { console.error('dashboard data error', e); });

    // ================= REVENUE AREA =================
    function revenueChart(rows) {
        var el = document.getElementById('chart-revenue');
        if (!el) return;
        var w = el.clientWidth || 700, h = 280, m = {t: 16, r: 20, b: 34, l: 56};
        var iw = w - m.l - m.r, ih = h - m.t - m.b;
        var svg = d3.select(el).append('svg').attr('width', w).attr('height', h)
            .append('g').attr('transform', 'translate(' + m.l + ',' + m.t + ')');

        var x = d3.scalePoint().domain(rows.map(function (d) { return d.label; })).range([0, iw]).padding(0.35);
        var y = d3.scaleLinear().domain([0, d3.max(rows, function (d) { return +d.revenue; }) * 1.1 || 1])
            .nice().range([ih, 0]);

        svg.append('g').attr('class', 'axis').attr('transform', 'translate(0,' + ih + ')')
            .call(d3.axisBottom(x));
        svg.append('g').attr('class', 'axis').call(d3.axisLeft(y).ticks(5).tickFormat(function (d) { return d >= 1000 ? (d/1000) + 'k' : d; }));

        var area = d3.area()
            .x(function (d) { return x(d.label); })
            .y0(ih)
            .y1(function (d) { return y(d.revenue); })
            .curve(d3.curveMonotoneX);

        var line = d3.line()
            .x(function (d) { return x(d.label); })
            .y(function (d) { return y(d.revenue); })
            .curve(d3.curveMonotoneX);

        var grad = svg.append('defs').append('linearGradient').attr('id', 'revGrad')
            .attr('x1', 0).attr('y1', 0).attr('x2', 0).attr('y2', 1);
        grad.append('stop').attr('offset', '0%').attr('stop-color', RED).attr('stop-opacity', 0.35);
        grad.append('stop').attr('offset', '100%').attr('stop-color', RED).attr('stop-opacity', 0.02);

        var tt = tooltip();
        svg.append('path').datum(rows).attr('fill', 'url(#revGrad)').attr('d', area);
        svg.append('path').datum(rows).attr('fill', 'none').attr('stroke', RED).attr('stroke-width', 2.2).attr('d', line);

        svg.selectAll('.rev-dot').data(rows).enter().append('circle')
            .attr('cx', function (d) { return x(d.label); })
            .attr('cy', function (d) { return y(d.revenue); })
            .attr('r', 4).attr('fill', RED_DARK).attr('stroke', '#fff').attr('stroke-width', 1.5)
            .on('mouseover', function (ev, d) { tt.style('display', 'block').html('<b>' + d.label + '</b><br>' + money(d.revenue) + ' · ' + fmt(d.service_count) + ' services'); })
            .on('mousemove', function (ev) { tt.style('left', (ev.offsetX + 12) + 'px').style('top', (ev.offsetY - 8) + 'px'); })
            .on('mouseout', function () { tt.style('display', 'none'); });
    }

    // ================= DONUT =================
    function donutChart(sel, rows, labelKey, valueKey) {
        var el = document.querySelector(sel);
        if (!el || !rows.length) return;
        var w = el.clientWidth || 380, h = 260, r = Math.min(w, h) / 2.4, ir = r * 0.62;
        var svg = d3.select(el).append('svg').attr('width', w).attr('height', h)
            .append('g').attr('transform', 'translate(' + (w / 2) + ',' + (h / 2 - 6) + ')');

        var pie = d3.pie().sort(null).value(function (d) { return +d[valueKey]; });
        var arc = d3.arc().innerRadius(ir).outerRadius(r);
        var color = d3.scaleOrdinal(PALETTE);

        var tt = tooltip();
        var arcs = svg.selectAll('.arc').data(pie(rows)).enter().append('path')
            .attr('d', arc).attr('fill', function (d, i) { return color(i); }).attr('stroke', '#10131a').attr('stroke-width', 2)
            .on('mouseover', function (ev, d) { tt.style('display', 'block').html('<b>' + d.data[labelKey] + '</b><br>' + fmt(d.data[valueKey])); })
            .on('mousemove', function (ev) { tt.style('left', (ev.offsetX + 12) + 'px').style('top', (ev.offsetY - 8) + 'px'); })
            .on('mouseout', function () { tt.style('display', 'none'); });

        var total = d3.sum(rows, function (d) { return +d[valueKey]; });
        svg.append('text').attr('text-anchor', 'middle').attr('dy', '-0.1em').style('font-size', '22px').style('font-weight', 700).style('fill', '#e4e7eb').text(fmt(total));
        svg.append('text').attr('text-anchor', 'middle').attr('dy', '1.6em').style('font-size', '11px').style('fill', '#94a3b8').text('total');

        // legend
        var lg = d3.select(el).append('div').style('display', 'flex').style('flex-wrap', 'wrap').style('justify-content', 'center').style('gap', '8px 14px').style('margin-top', '2px');
        rows.slice(0, 7).forEach(function (d, i) {
            lg.append('div').style('display', 'flex').style('align-items', 'center').style('gap', '5px')
                .style('font-size', '11px').style('color', '#cbd5e1')
                .html('<span style="width:10px;height:10px;border-radius:2px;background:' + color(i) + ';display:inline-block"></span>' + d[labelKey]);
        });
    }

    // ================= 30-DAY BARS =================
    function volumeChart(rows) {
        var el = document.getElementById('chart-appt-volume');
        if (!el) return;
        var w = el.clientWidth || 600, h = 280, m = {t: 16, r: 16, b: 40, l: 40};
        var iw = w - m.l - m.r, ih = h - m.t - m.b;
        var svg = d3.select(el).append('svg').attr('width', w).attr('height', h)
            .append('g').attr('transform', 'translate(' + m.l + ',' + m.t + ')');

        var x = d3.scaleBand().domain(rows.map(function (d) { return d3.timeFormat('%b %d')(new Date(d.day)); })).range([0, iw]).padding(0.25);
        var y = d3.scaleLinear().domain([0, d3.max(rows, function (d) { return +d.count; }) || 1]).nice().range([ih, 0]);

        svg.append('g').attr('class', 'axis').attr('transform', 'translate(0,' + ih + ')').call(d3.axisBottom(x).ticks(6).tickFormat(function (dd, i) { return i % 3 === 0 ? dd : ''; }));
        svg.append('g').attr('class', 'axis').call(d3.axisLeft(y).ticks(4));

        var tt = tooltip();
        svg.selectAll('.bar').data(rows).enter().append('rect')
            .attr('x', function (d) { return x(d3.timeFormat('%b %d')(new Date(d.day))); })
            .attr('y', function (d) { return y(d.count); })
            .attr('width', x.bandwidth())
            .attr('height', function (d) { return ih - y(d.count); })
            .attr('rx', 3).attr('fill', RED).attr('opacity', 0.85)
            .on('mouseover', function (ev, d) { tt.style('display', 'block').html('<b>' + d.day + '</b><br>' + fmt(d.count) + ' appointments'); })
            .on('mousemove', function (ev) { tt.style('left', (ev.offsetX + 12) + 'px').style('top', (ev.offsetY - 8) + 'px'); })
            .on('mouseout', function () { tt.style('display', 'none'); });
    }

    // ================= TOP SERVICES (horizontal bars) =================
    function servicesChart(rows) {
        var el = document.getElementById('chart-services');
        if (!el) return;
        var w = el.clientWidth || 480, h = 280, m = {t: 12, r: 46, b: 16, l: 14};
        var iw = w - m.l - m.r, ih = h - m.t - m.b;
        var svg = d3.select(el).append('svg').attr('width', w).attr('height', h)
            .append('g').attr('transform', 'translate(' + m.l + ',' + m.t + ')');

        var label = function (s) { return s.replace(/_/g, ' ').replace(/\b\w/g, function (c) { return c.toUpperCase(); }); };

        var y = d3.scaleBand().domain(rows.map(function (d) { return label(d.service_type); })).range([0, ih]).padding(0.3);
        var x = d3.scaleLinear().domain([0, d3.max(rows, function (d) { return +d.revenue; }) || 1]).range([0, iw]);

        svg.append('g').attr('class', 'axis').call(d3.axisLeft(y).tickSize(0)).call(function (g) { g.select('.domain').remove(); });
        svg.append('g').attr('class', 'axis').attr('transform', 'translate(0,0)').call(d3.axisTop(x).ticks(3).tickFormat(function (d) { return d >= 1000 ? (d/1000) + 'k' : d; })).call(function (g) { g.select('.domain').remove(); });

        var tt = tooltip();
        svg.selectAll('.sb').data(rows).enter().append('rect')
            .attr('y', function (d) { return y(label(d.service_type)); })
            .attr('height', y.bandwidth())
            .attr('x', 0).attr('width', function (d) { return x(d.revenue); })
            .attr('rx', 3).attr('fill', function (d, i) { return CATS[i] || RED; })
            .on('mouseover', function (ev, d) { tt.style('display', 'block').html('<b>' + label(d.service_type) + '</b><br>' + money(d.revenue) + ' · ' + fmt(d.count) + ' jobs'); })
            .on('mousemove', function (ev) { tt.style('left', (ev.offsetX + 12) + 'px').style('top', (ev.offsetY - 8) + 'px'); })
            .on('mouseout', function () { tt.style('display', 'none'); });

        svg.selectAll('.sv').data(rows).enter().append('text')
            .attr('x', function (d) { return x(d.revenue) + 4; })
            .attr('y', function (d) { return y(label(d.service_type)) + y.bandwidth() / 2; })
            .attr('dy', '0.32em').attr('fill', '#cbd5e1').style('font-size', '10px')
            .text(function (d) { return (d.revenue / 1000).toFixed(0) + 'k'; });
    }
})();
</script>
@endpush
