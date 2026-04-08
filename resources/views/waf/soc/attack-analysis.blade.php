@extends('layouts.waf')

@section('title', 'SOC Attack Analysis')

@section('styles')
@include('waf.soc._theme')
@endsection

@section('content')
<div class="soc-page-head">
    <div>
        <h1 class="soc-page-title">Attack Analysis</h1>
        <div class="soc-page-subtitle">Threat vector distribution and trend visibility.</div>
    </div>
    <span class="soc-chip">Top 4 Vectors</span>
</div>

<div class="soc-panel">
    <div class="soc-panel-head">
        <div>
            <div class="soc-panel-title">Vector Breakdown</div>
            <div class="soc-panel-sub">Threat vector analysis and trend intelligence</div>
        </div>
    </div>
    <div class="soc-panel-body" style="display:grid;gap:10px;">
        @php $max = collect($analysis)->max('count') ?: 1; @endphp
        @foreach($analysis as $a)
            @php $width = (int) (($a['count'] / $max) * 100); @endphp
            <div style="padding:10px;border:1px solid rgba(255,255,255,.1);border-radius:10px;background:rgba(255,255,255,.02);">
                <div style="display:flex;justify-content:space-between;gap:12px;align-items:center;margin-bottom:8px;">
                    <strong style="color:#e5e7eb;font-size:14px;">{{ $a['vector'] }}</strong>
                    <div style="display:flex;gap:8px;align-items:center;">
                        <span style="color:#a78bfa;font-size:18px;font-weight:800;">{{ $a['count'] }}</span>
                        <span class="soc-pill soc-open">{{ $a['trend'] }}</span>
                    </div>
                </div>
                <div class="soc-progress"><span style="width:{{ $width }}%;"></span></div>
            </div>
        @endforeach
    </div>
</div>
@endsection

