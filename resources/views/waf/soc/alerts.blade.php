@extends('layouts.waf')

@section('title', 'SOC Alerts')

@section('styles')
@include('waf.soc._theme')
@endsection

@section('content')
<div class="soc-page-head">
    <div>
        <h1 class="soc-page-title">SOC Alerts</h1>
        <div class="soc-page-subtitle">Prioritized detections with triage status and source context.</div>
    </div>
    <span class="soc-chip">{{ count($alerts) }} Active Alerts</span>
</div>

<div class="soc-panel">
    <div class="soc-panel-head">
        <div>
            <div class="soc-panel-title">Alert Queue</div>
            <div class="soc-panel-sub">Live alert queue and triage view</div>
        </div>
    </div>
    <div class="soc-panel-body soc-table-wrap">
        <table class="soc-table">
            <thead>
                <tr><th>ID</th><th>Title</th><th>Severity</th><th>Status</th><th>Source</th></tr>
            </thead>
            <tbody>
                @foreach($alerts as $a)
                    @php $sev = strtolower($a['severity']); @endphp
                    <tr>
                        <td style="font-family:monospace;color:#c4b5fd;">{{ $a['id'] }}</td>
                        <td>{{ $a['title'] }}</td>
                        <td><span class="soc-pill {{ $sev === 'high' ? 'soc-high' : ($sev === 'medium' ? 'soc-medium' : 'soc-low') }}">{{ $a['severity'] }}</span></td>
                        <td><span class="soc-pill soc-open">{{ $a['status'] }}</span></td>
                        <td>{{ $a['source'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

