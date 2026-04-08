@extends('layouts.waf')

@section('title', 'SOC Assets')

@section('styles')
@include('waf.soc._theme')
@endsection

@section('content')
<div class="soc-page-head">
    <div>
        <h1 class="soc-page-title">Assets</h1>
        <div class="soc-page-subtitle">Managed inventory and risk posture visibility.</div>
    </div>
    <span class="soc-chip">{{ count($assets) }} Assets</span>
</div>

<div class="soc-panel">
    <div class="soc-panel-head">
        <div>
            <div class="soc-panel-title">Asset Inventory</div>
            <div class="soc-panel-sub">SOC coverage map for monitored systems</div>
        </div>
    </div>
    <div class="soc-panel-body soc-table-wrap">
        <table class="soc-table">
            <thead>
                <tr><th>Asset</th><th>Type</th><th>Risk</th><th>Last Seen</th></tr>
            </thead>
            <tbody>
                @foreach($assets as $a)
                    @php $r = strtolower($a['risk']); @endphp
                    <tr>
                        <td style="font-weight:600;">{{ $a['name'] }}</td>
                        <td>{{ $a['type'] }}</td>
                        <td>
                            <span class="soc-pill {{ $r === 'high' ? 'soc-high' : ($r === 'medium' ? 'soc-medium' : 'soc-low') }}">{{ $a['risk'] }}</span>
                        </td>
                        <td>{{ $a['last_seen'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

