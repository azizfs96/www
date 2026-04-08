@extends('layouts.waf')

@section('title', 'SOC Dashboard')

@section('styles')
@include('waf.soc._theme')
@endsection

@section('content')
<div class="soc-page-head">
    <div>
        <h1 class="soc-page-title">Security Center (SOC)</h1>
        <div class="soc-page-subtitle">SOC as a Service live security operations overview.</div>
    </div>
    <span class="soc-chip">Live SOC</span>
</div>

<div class="soc-kpi-grid" style="margin-bottom:12px;">
    <div class="soc-kpi-card"><div class="soc-kpi-label">Open Alerts</div><div class="soc-kpi-value">{{ $kpis['open_alerts'] }}</div><div class="soc-kpi-trend">+8% vs yesterday</div></div>
    <div class="soc-kpi-card"><div class="soc-kpi-label">Critical Incidents</div><div class="soc-kpi-value">{{ $kpis['critical_incidents'] }}</div><div class="soc-kpi-trend">Needs immediate action</div></div>
    <div class="soc-kpi-card"><div class="soc-kpi-label">Assets Monitored</div><div class="soc-kpi-value">{{ $kpis['assets_monitored'] }}</div><div class="soc-kpi-trend">3 under attack</div></div>
    <div class="soc-kpi-card"><div class="soc-kpi-label">Attacks (24H)</div><div class="soc-kpi-value">{{ $kpis['attacks_last_24h'] }}</div><div class="soc-kpi-trend">+32% vs yesterday · Top vector: SQLi</div></div>
    <div class="soc-kpi-card soc-kpi-exec"><div class="soc-kpi-label">Security Score</div><div class="soc-kpi-value">72 / 100</div><div class="soc-kpi-note-neg">-8 this week</div></div>
</div>

<div class="soc-panel" style="margin-bottom:12px;">
    <div class="soc-panel-head">
        <div>
            <div class="soc-panel-title">Active Incident</div>
            <div class="soc-panel-sub">Ongoing brute force attack on <strong style="color:#e5e7eb;">/login</strong></div>
        </div>
        <span class="soc-pill soc-high">High</span>
    </div>
    <div class="soc-panel-body" style="display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap;">
        <div style="color:#cbd5e1;">Affected asset: <strong style="color:#e5e7eb;">portal.wafgate.com</strong></div>
        <div style="color:#cbd5e1;">Current status: <strong style="color:#e5e7eb;">Auto-block active · SOC L2 investigating</strong></div>
    </div>
</div>

<div class="soc-two-col">
    <div class="soc-panel">
        <div class="soc-panel-head">
            <div>
                <div class="soc-panel-title">SOC Timeline</div>
                <div class="soc-panel-sub">Recent notable security activities</div>
            </div>
        </div>
        <div class="soc-panel-body">
            @foreach($timeline as $item)
                <div style="display:flex;align-items:center;gap:10px;padding:10px 0;border-top:1px solid rgba(255,255,255,.08);">
                    <div style="color:#9ca3af;font-family:monospace;width:58px;">{{ $item['time'] }}</div>
                    <div style="flex:1;color:#e5e7eb;">
                        {{ $item['event'] }} from <strong>{{ $item['source_ip'] }}</strong> targeting <strong>{{ $item['target'] }}</strong> ({{ $item['result'] }})
                    </div>
                    @php $sev = strtolower($item['severity']); @endphp
                    <span class="soc-pill {{ $sev === 'high' ? 'soc-high' : ($sev === 'medium' ? 'soc-medium' : 'soc-low') }}">{{ $item['severity'] }}</span>
                </div>
            @endforeach
        </div>
    </div>

    <div class="soc-panel">
        <div class="soc-panel-head">
            <div>
                <div class="soc-panel-title">Response Coverage</div>
                <div class="soc-panel-sub">Operational readiness indicators</div>
            </div>
        </div>
        <div class="soc-panel-body" style="display:grid;gap:12px;">
            <div>
                <div style="display:flex;justify-content:space-between;color:#cbd5e1;font-size:12px;margin-bottom:6px;"><span>Mean Response Time</span><span>92%</span></div>
                <div class="soc-progress"><span style="width:92%;"></span></div>
            </div>
            <div>
                <div style="display:flex;justify-content:space-between;color:#cbd5e1;font-size:12px;margin-bottom:6px;"><span>Detection Rate</span><span>88%</span></div>
                <div class="soc-progress"><span style="width:88%;"></span></div>
            </div>
            <div>
                <div style="display:flex;justify-content:space-between;color:#cbd5e1;font-size:12px;margin-bottom:6px;"><span>Block Efficiency</span><span>96%</span></div>
                <div class="soc-progress"><span style="width:96%;"></span></div>
            </div>
        </div>
    </div>
</div>
@endsection

