@extends('layouts.waf')

@section('title', 'WAF Dashboard')

@section('styles')
<style>
    /* Clean Dark Design with Dots Pattern */
    :root {
        --bg-dark: #1A1A1A;
        --bg-card: #1E1E1E;
        --bg-hover: #2A2A2A;
        --border: #333333;
        --border-light: #404040;
        --card-border: rgba(240, 233, 233, 0.42);
        --card-border-strong: rgba(226, 226, 226, 0.62);
        --text-primary: #E5E5E5;
        --text-secondary: #B3B3B3;
        --text-muted: #808080;
        --primary: #9D4EDD;
        --primary-hover: #B06FE8;
        --success: #4ADE80;
        --error: #F87171;
        --warning: #FBBF24;
        --info: #60A5FA;
    }

    html, body {
        background: var(--bg-dark) !important;
        background-color: var(--bg-dark) !important;
    }

    .content-wrapper {
        background: transparent !important;
    }

    .page-header {
        margin-bottom: 32px;
        direction: ltr;
        text-align: left;
    }

    .page-header-top {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 20px;
        flex-wrap: wrap;
        gap: 16px;
    }

    .page-title {
        font-size: 32px;
        font-weight: 600;
        color: var(--text-primary);
        margin: 0 0 12px 0;
        letter-spacing: -0.5px;
        text-align: center;
    }

    .page-timestamp {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 12px;
        color: var(--text-muted);
        font-weight: 400;
        padding: 6px 12px;
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: 6px;
    }

    .page-description {
        font-size: 14px;
        color: var(--text-secondary);
        line-height: 1.6;
        max-width: 700px;
        margin-top: 16px;
    }

    /* Stats Grid */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 20px;
        margin-bottom: 40px;
    }

    .stat-card {
        background: #1E1E1E;
        border: 1px solid var(--card-border) !important;
        outline: 1px solid rgba(255, 255, 255, 0.35) !important;
        box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.16) !important;
        border-radius: 12px;
        padding: 24px;
        position: relative;
        transition: all 0.2s ease;
    }

    .stat-card:hover {
        border-color: var(--card-border-strong);
        background: var(--bg-hover);
    }

    .stat-card-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 20px;
    }

    .stat-icon-wrapper {
        width: 32px;
        height: 32px;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        background: transparent;
        border: 1px solid var(--border);
        color: var(--text-secondary);
        margin-bottom: 12px;
    }

    .stat-label {
        font-size: 12px;
        color: var(--text-muted);
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 10px;
    }

    .stat-value {
        font-size: 36px;
        font-weight: 600;
        color: var(--text-primary);
        line-height: 1;
        margin-bottom: 12px;
        font-family: -apple-system, BlinkMacSystemFont, sans-serif;
    }

    .stat-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 500;
        border: 1px solid;
    }

    .stat-badge.success {
        background: rgba(74, 222, 128, 0.15);
        color: var(--success);
        border-color: rgba(74, 222, 128, 0.3);
    }

    .stat-badge.danger {
        background: rgba(239, 68, 68, 0.15);
        color: #EF4444;
        border-color: rgba(239, 68, 68, 0.3);
    }

    .stat-badge.warning {
        background: rgba(251, 191, 36, 0.15);
        color: var(--warning);
        border-color: rgba(251, 191, 36, 0.3);
    }

    .stat-description {
        font-size: 12px;
        color: var(--text-muted);
        line-height: 1.6;
        margin-top: 16px;
        padding-top: 16px;
        border-top: 1px solid var(--border);
    }

    /* Content Layout */
    .content-layout {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }

    @media (max-width: 1024px) {
        .content-layout {
            grid-template-columns: 1fr;
        }
    }

    /* Panels */
    .panel {
        background: #1E1E1E;
        border: 1px solid var(--card-border) !important;
        outline: 1px solid rgba(255, 255, 255, 0.35) !important;
        box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.16) !important;
        border-radius: 12px;
        overflow: hidden;
        transition: all 0.2s ease;
    }

    .panel:hover {
        border-color: var(--card-border-strong);
    }

    .panel-header {
        background: #1E1E1E;
        padding: 20px;
        border-bottom: 1px solid var(--border);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .panel-title {
        font-size: 16px;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 4px;
    }

    .panel-subtitle {
        font-size: 12px;
        color: var(--text-muted);
        font-weight: 400;
    }

    .panel-action {
        font-size: 12px;
        color: var(--primary);
        text-decoration: none;
        font-weight: 500;
        padding: 6px 12px;
        border-radius: 6px;
        transition: all 0.2s;
        border: 1px solid var(--border);
        background: transparent;
    }

    .panel-action:hover {
        background: rgba(157, 78, 221, 0.1);
        border-color: var(--primary);
        color: var(--primary-hover);
        text-decoration: none;
    }

    /* Tables */
    .panel-content {
        padding: 0;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    thead {
        background: #1E1E1E;
    }

    th {
        padding: 12px 20px;
        text-align: left;
        font-size: 11px;
        font-weight: 600;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 1px solid var(--border);
    }

    tbody tr {
        border-bottom: 1px solid var(--border);
        transition: background-color 0.15s ease;
    }

    tbody tr:hover {
        background: var(--bg-hover);
    }

    tbody tr:last-child {
        border-bottom: none;
    }

    td {
        padding: 14px 20px;
        text-align: left;
        color: var(--text-primary);
        font-size: 13px;
    }

    /* Badges */
    .badge {
        display: inline-flex;
        align-items: center;
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 500;
        gap: 6px;
        border: 1px solid;
    }

    .badge-status {
        background: rgba(179, 179, 179, 0.1);
        color: var(--text-secondary);
        border-color: rgba(179, 179, 179, 0.2);
    }

    .badge-status.active {
        background: rgba(239, 68, 68, 0.15);
        color: #EF4444;
        border-color: rgba(239, 68, 68, 0.3);
    }

    .badge-rule {
        background: rgba(157, 78, 221, 0.1);
        color: var(--primary);
        border-color: rgba(157, 78, 221, 0.2);
        font-family: 'Courier New', monospace;
        font-size: 11px;
    }

    .status-indicator {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        display: inline-block;
    }

    .status-indicator.active {
        background: #EF4444;
        box-shadow: none;
    }

    /* Empty State */
    .empty-state {
        padding: 48px 24px;
        text-align: center;
        color: var(--text-muted);
        font-size: 13px;
        font-weight: 400;
    }

    .panel-footer {
        padding: 12px 20px;
        background: #1E1E1E;
        border-top: 1px solid var(--border);
        font-size: 11px;
        color: var(--text-muted);
        font-weight: 400;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .stats-grid {
            grid-template-columns: 1fr;
        }

        .page-title {
            font-size: 24px;
        }

        .stat-value {
            font-size: 28px;
        }
    }

    td strong {
        font-weight: 600;
        color: var(--text-primary);
    }

    /* Chart Panel */
    .chart-panel {
        background: #1E1E1E;
        border: 1px solid var(--card-border) !important;
        outline: 1px solid rgba(255, 255, 255, 0.35) !important;
        box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.16) !important;
        border-radius: 12px;
        padding: 16px 24px 24px;
        margin-bottom: 40px;
    }

    .chart-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 8px;
        flex-wrap: wrap;
        gap: 16px;
    }

    .chart-title {
        font-size: 18px;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 4px;
    }

    .chart-subtitle {
        font-size: 12px;
        color: var(--text-muted);
    }

    .chart-controls {
        display: flex;
        gap: 12px;
        align-items: center;
        flex-wrap: wrap;
    }

    .status-filters {
        display: flex;
        gap: 8px;
        align-items: center;
        flex-wrap: wrap;
    }

    .status-filter-item {
        display: flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        background: var(--bg-dark);
        border: 1px solid var(--border);
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.2s;
        font-size: 12px;
    }

    .status-filter-item:hover {
        border-color: var(--border-light);
        background: var(--bg-hover);
    }

    .status-filter-item.active {
        border-color: var(--primary);
        background: rgba(157, 78, 221, 0.1);
    }

    .status-filter-item input[type="checkbox"] {
        cursor: pointer;
        width: 16px;
        height: 16px;
        accent-color: var(--primary);
    }

    .status-filter-item label {
        cursor: pointer;
        color: var(--text-primary);
        font-weight: 500;
        user-select: none;
    }

    .status-filter-item.allowed.active {
        border-color: var(--success);
        background: rgba(74, 222, 128, 0.1);
    }

    .status-filter-item.blocked.active {
        border-color: var(--error);
        background: rgba(239, 68, 68, 0.1);
    }

    .status-filter-item.notfound.active {
        border-color: var(--text-muted);
        background: rgba(179, 179, 179, 0.1);
    }

    .chart-select,
    .chart-input {
        background: var(--bg-dark);
        border: 1px solid var(--border);
        border-radius: 8px;
        color: var(--text-primary);
        font-size: 13px;
        padding: 8px 12px;
        min-width: 200px;
        transition: all 0.2s;
    }

    .chart-select:focus,
    .chart-input:focus {
        outline: none;
        border-color: var(--primary);
        background: var(--bg-hover);
        box-shadow: 0 0 0 3px rgba(157, 78, 221, 0.1);
    }

    .chart-input {
        font-family: 'Courier New', monospace;
    }

    .chart-input::placeholder {
        color: var(--text-muted);
    }

    .chart-container {
        position: relative;
        height: 360px;
        width: 100%;
    }

    .chart-loading {
        display: flex;
        align-items: center;
        justify-content: center;
        height: 360px;
        color: var(--text-muted);
        font-size: 14px;
    }

    /* ===== Top IP Addresses — WAF_GATE design ===== */
    .tip-list { display: flex; flex-direction: column; }
    .tip-row {
        display: flex; align-items: center; gap: 16px;
        padding: 13px 20px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.06);
        transition: background-color 0.15s ease;
    }
    .tip-row:last-child { border-bottom: none; }
    .tip-row:hover { background: rgba(255, 255, 255, 0.03); }
    .tip-main { flex: 1; min-width: 0; }
    .tip-id { display: flex; align-items: center; gap: 8px; }
    .tip-ip {
        font-family: ui-monospace, 'Courier New', monospace;
        font-size: 13px; font-weight: 600; color: #f1f5f9;
        overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
    }
    .tip-chip {
        flex-shrink: 0;
        font-family: ui-monospace, monospace; font-size: 10px; color: #94a3b8;
        background: rgba(255, 255, 255, 0.08); padding: 2px 6px; border-radius: 5px;
    }
    .tip-bar {
        margin-top: 9px; height: 6px; width: 100%;
        border-radius: 999px; background: rgba(255, 255, 255, 0.08); overflow: hidden;
    }
    .tip-bar > span {
        display: block; height: 100%; border-radius: 999px;
        background: linear-gradient(90deg, rgba(239,68,68,.55), rgba(239,68,68,.9));
    }
    .tip-right { text-align: right; flex-shrink: 0; min-width: 56px; }
    .tip-count { font-size: 14px; font-weight: 700; color: #f1f5f9; line-height: 1.1; }
    .tip-sub { font-size: 11px; color: #94a3b8; margin-top: 2px; }
    .tip-badge {
        flex-shrink: 0;
        display: inline-flex; align-items: center; gap: 6px;
        font-size: 11px; font-weight: 600; color: #fca5a5;
        background: rgba(239, 68, 68, 0.12); border: 1px solid rgba(239, 68, 68, 0.3);
        padding: 4px 10px; border-radius: 999px; white-space: nowrap;
    }
    .tip-badge .dot {
        width: 6px; height: 6px; border-radius: 999px; background: #ef4444;
        box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.18);
    }
    .tip-empty { padding: 40px 20px; text-align: center; color: #808080; font-size: 13px; }

    @media (max-width: 480px) {
        .tip-badge { display: none; }
    }

    /* ===== Live Attack Origins Threat Map (stat-card sized) ===== */
    .map-card {
        padding: 0;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        background: #0b1020;
    }
    .map-live-badge {
        position: absolute;
        top: 12px;
        right: 12px;
        z-index: 2;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 3px 9px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 500;
        color: #c4b5fd;
        background: rgba(11,16,32,.6);
        border: 1px solid rgba(139,92,246,.3);
        backdrop-filter: blur(4px);
        white-space: nowrap;
    }
    .map-live-dot {
        position: relative;
        width: 6px;
        height: 6px;
        border-radius: 999px;
        background: #a78bfa;
    }
    .map-live-dot::after {
        content: "";
        position: absolute;
        inset: 0;
        border-radius: 999px;
        background: #a78bfa;
        animation: mapPing 1.8s cubic-bezier(0,0,.2,1) infinite;
    }
    @keyframes mapPing { 75%, 100% { transform: scale(2.6); opacity: 0; } }

    .map-canvas-wrap {
        flex: 1;
        position: relative;
        min-height: 0;
        overflow: hidden;
    }
    #threatMapSvg { position: absolute; inset: 0; width: 100%; height: 100%; display: block; }
    .map-geo { fill: #1b2340; stroke: #2b3766; stroke-width: 0.4; }
    .map-marker-core { fill: #a78bfa; stroke: #f5f3ff; stroke-width: 0.7; cursor: pointer; }
    .map-marker-label { fill: #ede9fe; font-size: 9px; font-weight: 600; paint-order: stroke; stroke: #0b1020; stroke-width: 2.5px; pointer-events: none; }
</style>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/d3@7/dist/d3.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/topojson-client@3/dist/topojson-client.min.js"></script>
<script>
// ===== Live Attack Origins Threat Map =====
(function () {
    // Approx [lon, lat] centroid per ISO-3166 alpha-2 code
    const CENTROIDS = {
        US:[-98,39], SA:[45,24], GB:[-2,54], DE:[10,51], FR:[2,46], CN:[105,35], JP:[138,36],
        IN:[79,22], BR:[-51,-10], RU:[100,60], CA:[-106,56], AU:[134,-25], IT:[12,42], ES:[-4,40],
        NL:[5,52], SE:[15,62], NO:[8,61], DK:[10,56], FI:[26,64], PL:[19,52], KR:[128,36], MX:[-102,23],
        AR:[-64,-34], ZA:[24,-29], EG:[30,27], AE:[54,24], TR:[35,39], ID:[120,-5], TH:[101,15],
        VN:[106,16], PH:[122,13], MY:[102,4], SG:[104,1.3], NZ:[174,-41], IE:[-8,53], CH:[8,47],
        AT:[14,47], BE:[4,50], PT:[-8,39], GR:[22,39], CZ:[15,50], HU:[19,47], RO:[25,46], BG:[25,43],
        HR:[15,45], SK:[19,49], SI:[15,46], LT:[24,56], LV:[25,57], EE:[26,59], IS:[-18,65], LU:[6,50],
        MT:[14,36], CY:[33,35], KW:[48,29], QA:[51,25], BH:[50.5,26], OM:[56,21], JO:[36,31], LB:[36,34],
        IQ:[44,33], SY:[38,35], YE:[48,15], PK:[70,30], BD:[90,24], LK:[81,7], NP:[84,28], AF:[66,33],
        IR:[53,32], IL:[35,31], PS:[35,32], UA:[32,49]
    };
    const GEO_URL = "https://cdn.jsdelivr.net/npm/world-atlas@2/countries-110m.json";
    const SVG_NS = "http://www.w3.org/2000/svg";

    function compact(n) {
        if (n >= 1e6) return (n/1e6).toFixed(1).replace(/\.0$/,'') + 'M';
        if (n >= 1e3) return (n/1e3).toFixed(1).replace(/\.0$/,'') + 'k';
        return String(n);
    }
    function el(name, attrs) {
        const e = document.createElementNS(SVG_NS, name);
        for (const k in attrs) e.setAttribute(k, attrs[k]);
        return e;
    }

    async function initThreatMap() {
        const card = document.getElementById('threatMapCard');
        const svg = document.getElementById('threatMapSvg');
        if (!card || !svg || typeof d3 === 'undefined' || typeof topojson === 'undefined') return;

        let origins = [];
        try { origins = JSON.parse(card.dataset.origins || '[]'); } catch (e) { origins = []; }
        // Keep only origins we can place on the map
        origins = origins
            .map(o => ({ country: String(o.country || '').toUpperCase(), cnt: Number(o.cnt) || 0 }))
            .filter(o => CENTROIDS[o.country]);

        const max = Math.max(...origins.map(o => o.cnt), 1);

        const W = 900, H = 420;
        const projection = d3.geoEqualEarth().fitExtent([[0, 0], [W, H]], { type: "Sphere" });
        const path = d3.geoPath(projection);

        let world;
        try {
            world = await d3.json(GEO_URL);
        } catch (e) {
            return;
        }
        const countries = topojson.feature(world, world.objects.countries).features;

        // base geographies
        const gGeo = el('g', {});
        countries.forEach(f => {
            const d = path(f);
            if (d) gGeo.appendChild(el('path', { d: d, class: 'map-geo' }));
        });
        svg.appendChild(gGeo);

        // markers
        const gMark = el('g', {});
        origins.forEach((o, i) => {
            const p = projection(CENTROIDS[o.country]);
            if (!p) return;
            const r = 2.5 + (o.cnt / max) * 6;
            const g = el('g', { transform: `translate(${p[0]}, ${p[1]})`, style: 'cursor:pointer' });

            // expanding radar ring
            const ring = el('circle', { r: r, fill: 'none', stroke: '#c4b5fd', 'stroke-width': 1, opacity: 0.8 });
            const begin = (Math.abs(CENTROIDS[o.country][0]) % 6) * 0.25 + 's';
            const a1 = el('animate', { attributeName: 'r', values: `${r};${r*3.4}`, dur: '2.6s', begin: begin, repeatCount: 'indefinite' });
            const a2 = el('animate', { attributeName: 'opacity', values: '0.75;0', dur: '2.6s', begin: begin, repeatCount: 'indefinite' });
            ring.appendChild(a1); ring.appendChild(a2);
            g.appendChild(ring);

            // solid core
            const core = el('circle', { r: r, class: 'map-marker-core' });
            g.appendChild(core);

            // hover label
            const label = el('text', { 'text-anchor': 'middle', y: -r - 6, class: 'map-marker-label', opacity: 0 });
            label.textContent = o.country + ' · ' + compact(o.cnt);
            g.appendChild(label);

            g.addEventListener('mouseenter', () => { label.setAttribute('opacity', 1); core.setAttribute('r', r + 1.2); });
            g.addEventListener('mouseleave', () => { label.setAttribute('opacity', 0); core.setAttribute('r', r); });

            gMark.appendChild(g);
        });
        svg.appendChild(gMark);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initThreatMap);
    } else {
        initThreatMap();
    }
})();
</script>
<script>
let chartInstance = null;

function loadChartData(host = '') {
    const loadingEl = document.getElementById('chartLoading');
    const canvasEl = document.getElementById('chartCanvas');
    
    // Get selected status filters
    const statusFilters = {
        200: document.getElementById('filter200')?.checked ?? true,
        403: document.getElementById('filter403')?.checked ?? true,
        404: document.getElementById('filter404')?.checked ?? true,
    };
    
    if (loadingEl) loadingEl.style.display = 'flex';
    if (canvasEl) canvasEl.style.display = 'none';
    
    fetch(`/waf/api/chart-data?host=${encodeURIComponent(host)}&hours=24`)
        .then(response => response.json())
        .then(data => {
            if (loadingEl) loadingEl.style.display = 'none';
            if (canvasEl) canvasEl.style.display = 'block';
            
            // Filter datasets based on selected status
            const filteredDatasets = data.datasets.filter((dataset, index) => {
                if (index === 0) return statusFilters[200]; // Allowed
                if (index === 1) return statusFilters[403]; // Blocked
                if (index === 2) return statusFilters[404]; // Not Found
                return true;
            });
            
            const filteredData = {
                labels: data.labels,
                datasets: filteredDatasets
            };
            
            const ctx = document.getElementById('chartCanvas').getContext('2d');
            
            if (chartInstance) {
                chartInstance.destroy();
            }
            
            chartInstance = new Chart(ctx, {
                type: 'line',
                data: filteredData,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    plugins: {
                        legend: {
                            display: false,
                        },
                        tooltip: {
                            backgroundColor: 'rgba(30, 30, 30, 0.95)',
                            titleColor: '#E5E5E5',
                            bodyColor: '#B3B3B3',
                            borderColor: '#333333',
                            borderWidth: 1,
                            padding: 12,
                            displayColors: true,
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                color: 'rgba(51, 51, 51, 0.5)',
                                borderColor: '#333333',
                            },
                            ticks: {
                                color: '#808080',
                                font: {
                                    size: 11
                                }
                            }
                        },
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(51, 51, 51, 0.5)',
                                borderColor: '#333333',
                            },
                            ticks: {
                                color: '#808080',
                                font: {
                                    size: 11
                                },
                                callback: function(value) {
                                    return value;
                                }
                            }
                        }
                    }
                }
            });
        })
        .catch(error => {
            console.error('Error loading chart data:', error);
            if (loadingEl) {
                loadingEl.textContent = 'Error loading chart data';
                loadingEl.style.color = '#F87171';
            }
        });
}

// Load chart on page load
document.addEventListener('DOMContentLoaded', function() {
    loadChartData();
    
    const domainSelect = document.getElementById('domainSelect');
    const domainInput = document.getElementById('domainInput');
    const applyBtn = document.getElementById('applyDomainBtn');
    
    // Handle domain selection change
    if (domainSelect) {
        domainSelect.addEventListener('change', function() {
            if (this.value) {
                // Clear input when selecting from dropdown
                if (domainInput) domainInput.value = '';
                loadChartData(this.value);
            } else {
                loadChartData('');
            }
        });
    }
    
    // Handle manual domain input
    if (domainInput) {
        domainInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                applyDomain();
            }
        });
    }
    
    // Handle apply button
    if (applyBtn) {
        applyBtn.addEventListener('click', applyDomain);
    }
    
    // Handle status filter checkboxes
    const statusFilters = document.querySelectorAll('.status-filter-item input[type="checkbox"]');
    statusFilters.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const item = this.closest('.status-filter-item');
            if (this.checked) {
                item.classList.add('active');
            } else {
                item.classList.remove('active');
            }
            // Reload chart with current domain
            const domain = domainInput && domainInput.value.trim() 
                ? domainInput.value.trim() 
                : (domainSelect ? domainSelect.value : '');
            loadChartData(domain);
        });
    });
    
    // Handle status filter item clicks
    const statusFilterItems = document.querySelectorAll('.status-filter-item');
    statusFilterItems.forEach(item => {
        item.addEventListener('click', function(e) {
            if (e.target.tagName !== 'INPUT' && e.target.tagName !== 'LABEL') {
                const checkbox = this.querySelector('input[type="checkbox"]');
                if (checkbox) {
                    checkbox.checked = !checkbox.checked;
                    checkbox.dispatchEvent(new Event('change'));
                }
            }
        });
    });
    
    function applyDomain() {
        const domain = domainInput ? domainInput.value.trim() : '';
        if (domain) {
            // Clear dropdown selection
            if (domainSelect) domainSelect.value = '';
            loadChartData(domain);
        } else {
            // If input is empty, load all domains
            if (domainSelect) domainSelect.value = '';
            loadChartData('');
        }
    }
});
</script>
@endsection

@section('content')
<div class="page-header">
    <div class="page-header-top" style="align-items: center;">
        <h1 class="page-title" style="text-align: left; margin: 0;">WAF Dashboard</h1>
        <div class="page-timestamp">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
            <span>Last Updated: {{ now('Asia/Riyadh')->format('Y-m-d H:i:s') }} <span style="opacity:.7;">(KSA)</span></span>
        </div>
    </div>
</div>

{{-- Statistics Cards --}}
<div class="stats-grid">
    {{-- Live Attack Origins Threat Map (replaces the old Total Events card) --}}
    <div class="stat-card map-card" id="threatMapCard"
         data-origins='@json($attackOrigins)'>
        <div class="map-canvas-wrap">
            <span class="map-live-badge"><span class="map-live-dot"></span>Live</span>
            <svg id="threatMapSvg" viewBox="0 0 900 420" preserveAspectRatio="xMidYMid slice"></svg>
        </div>
    </div>

    <div class="stat-card blocked">
        <div class="stat-card-header">
            <div style="flex: 1;">
                <div class="stat-icon-wrapper">▪</div>
                <div class="stat-label">Blocked Requests</div>
                <div class="stat-value">{{ number_format($blocked) }}</div>
                <div>
                    <span class="stat-badge danger">
                        {{ $total > 0 ? round(($blocked / max($total,1)) * 100) : 0 }}% of total
                    </span>
                </div>
            </div>
        </div>
        <div class="stat-description">
            Number of requests successfully blocked before reaching the application, such as SQL Injection, XSS, and CSRF attempts.
        </div>
    </div>

    <div class="stat-card attack">
        <div class="stat-card-header">
            <div style="flex: 1;">
                <div class="stat-icon-wrapper">▫</div>
                <div class="stat-label">Top Attack Source</div>
                <div class="stat-value" style="font-size: 18px; font-family: 'Courier New', monospace; line-height: 1.2;">
                    {{ optional($topIps->first())->client_ip ?? 'None' }}
                </div>
                <div>
                    <span class="stat-badge warning">
                        {{ optional($topIps->first())->cnt ?? 0 }} attempts
                    </span>
                </div>
            </div>
        </div>
        <div class="stat-description">
            IP address with the highest number of suspicious access attempts and potential attacks today.
        </div>
    </div>
</div>

{{-- Chart Panel --}}
<div class="chart-panel">
    <div class="chart-header">
        <div class="status-filters">
            <div class="status-filter-item allowed active" data-status="200">
                <input type="checkbox" id="filter200" checked>
                <label for="filter200">Allowed (200)</label>
            </div>
            <div class="status-filter-item blocked active" data-status="403">
                <input type="checkbox" id="filter403" checked>
                <label for="filter403">Blocked (403)</label>
            </div>
            <div class="status-filter-item notfound active" data-status="404">
                <input type="checkbox" id="filter404" checked>
                <label for="filter404">Not Found (404)</label>
            </div>
        </div>
        <div class="chart-controls">
            <select id="domainSelect" class="chart-select">
                <option value="">All Domains</option>
                @foreach($hosts ?? [] as $host)
                    <option value="{{ $host }}">{{ $host }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="chart-container">
        <div id="chartLoading" class="chart-loading">Loading chart data...</div>
        <canvas id="chartCanvas" style="display: none;"></canvas>
    </div>
</div>

{{-- Content Panels --}}
<div class="content-layout">
    {{-- Top IPs Panel --}}
    <div class="panel">
        <div class="panel-header">
            <div class="panel-title-wrapper">
                <div class="panel-title">Top IP Addresses</div>
                <div class="panel-subtitle">Most active sources by number of attempts</div>
            </div>
            <a href="/waf/events" class="panel-action">View All →</a>
        </div>
        <div class="panel-content">
            @php
                $maxAtt = ($topIps->max('cnt') ?: 1);
                $tipCountryNames = [
                    'US'=>'United States','SA'=>'Saudi Arabia','GB'=>'United Kingdom','DE'=>'Germany','FR'=>'France',
                    'CN'=>'China','JP'=>'Japan','IN'=>'India','BR'=>'Brazil','RU'=>'Russia','CA'=>'Canada','AU'=>'Australia',
                    'IT'=>'Italy','ES'=>'Spain','NL'=>'Netherlands','SE'=>'Sweden','NO'=>'Norway','DK'=>'Denmark','FI'=>'Finland',
                    'PL'=>'Poland','KR'=>'South Korea','MX'=>'Mexico','AR'=>'Argentina','ZA'=>'South Africa','EG'=>'Egypt',
                    'AE'=>'United Arab Emirates','TR'=>'Turkey','ID'=>'Indonesia','TH'=>'Thailand','VN'=>'Vietnam','PH'=>'Philippines',
                    'MY'=>'Malaysia','SG'=>'Singapore','NZ'=>'New Zealand','IE'=>'Ireland','CH'=>'Switzerland','AT'=>'Austria',
                    'BE'=>'Belgium','PT'=>'Portugal','GR'=>'Greece','CZ'=>'Czechia','HU'=>'Hungary','RO'=>'Romania','UA'=>'Ukraine',
                    'KW'=>'Kuwait','QA'=>'Qatar','BH'=>'Bahrain','OM'=>'Oman','JO'=>'Jordan','LB'=>'Lebanon','IQ'=>'Iraq',
                    'PK'=>'Pakistan','BD'=>'Bangladesh','IR'=>'Iran','IL'=>'Israel','PS'=>'Palestine','LOCAL'=>'Local Network',
                ];
            @endphp
            <div class="tip-list">
                @forelse ($topIps as $ip)
                    @php
                        $share = max(4, round(($ip->cnt / $maxAtt) * 100));
                        $cc = strtoupper($ip->country ?? '');
                        $countryName = $cc ? ($tipCountryNames[$cc] ?? $cc) : 'Unknown';
                    @endphp
                    <div class="tip-row">
                        <div class="tip-main">
                            <div class="tip-id">
                                <span class="tip-ip">{{ $ip->client_ip }}</span>
                                <span class="tip-chip">{{ $countryName }}</span>
                            </div>
                            <div class="tip-bar"><span style="width: {{ $share }}%"></span></div>
                        </div>
                        <div class="tip-right">
                            <div class="tip-count">{{ number_format($ip->cnt) }}</div>
                            <div class="tip-sub">attempts</div>
                        </div>
                        <span class="tip-badge"><span class="dot"></span> High Activity</span>
                    </div>
                @empty
                    <div class="tip-empty">No data available at this time</div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Top Rules Panel --}}
    <div class="panel">
        <div class="panel-header">
            <div class="panel-title-wrapper">
                <div class="panel-title">Most Triggered Rules</div>
                <div class="panel-subtitle">Most common attack types</div>
            </div>
        </div>
        <div class="panel-content">
            <table>
                <thead>
                    <tr>
                        <th>Rule ID</th>
                        <th>Count</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($topRules as $rule)
                    <tr>
                        <td>
                            <span class="badge badge-rule">
                                {{ $rule->rule_id ?? 'N/A' }}
                            </span>
                        </td>
                        <td>
                            <strong>{{ number_format($rule->cnt) }}</strong>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2" class="empty-state">
                            No rules triggered at this time
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="panel-footer">
            Displaying most triggered rules in the last 24 hours
        </div>
    </div>
</div>
@endsection
