@extends('layouts.waf')

@section('title', 'SOC Incidents')

@section('styles')
@include('waf.soc._theme')
@endsection

@section('content')
<div class="soc-page-head">
    <div>
        <h1 class="soc-page-title">SOC Incidents</h1>
        <div class="soc-page-subtitle">Case management view for escalated security events.</div>
    </div>
    <span class="soc-chip">{{ count($incidents) }} Open Cases</span>
</div>

<div class="soc-panel">
    <div class="soc-panel-head">
        <div>
            <div class="soc-panel-title">Incident Workbench</div>
            <div class="soc-panel-sub">Track ownership, priority, and current handling status</div>
        </div>
    </div>
    <div class="soc-panel-body" style="display:grid;gap:10px;">
        @foreach($incidents as $i)
            <div style="border:1px solid rgba(255,255,255,.12);background:rgba(255,255,255,.02);border-radius:12px;padding:14px;">
                <div style="display:flex;justify-content:space-between;gap:10px;flex-wrap:wrap;">
                    <div style="font-family:monospace;color:#c4b5fd;font-size:12px;">{{ $i['id'] }}</div>
                    <span class="soc-pill {{ strtolower($i['priority']) === 'p1' ? 'soc-high' : 'soc-medium' }}">{{ $i['priority'] }}</span>
                </div>
                <div style="color:#e5e7eb;font-size:16px;font-weight:700;margin:6px 0 10px;">{{ $i['name'] }}</div>
                <div style="display:flex;gap:10px;flex-wrap:wrap;color:#9ca3af;font-size:12px;">
                    <span class="soc-pill soc-open">Owner: {{ $i['owner'] }}</span>
                    <span class="soc-pill soc-open">Status: {{ $i['status'] }}</span>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection

