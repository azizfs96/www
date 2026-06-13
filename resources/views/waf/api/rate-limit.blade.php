@extends('layouts.waf')

@section('title', 'API Protection · Rate Limiting')

@section('styles')
@include('waf.soc._theme')
<style>
    .rl-stat-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(190px, 1fr)); gap: 12px; margin-bottom: 18px; }
    .rl-stat { display: flex; align-items: center; gap: 12px; padding: 14px 16px; border-radius: 12px; border: 1px solid rgba(255,255,255,.14); background: rgba(255,255,255,.02); }
    .rl-stat-ic { width: 40px; height: 40px; border-radius: 11px; display: flex; align-items: center; justify-content: center; flex: 0 0 auto; color: #c4b5fd; background: rgba(168,85,247,.14); border: 1px solid rgba(168,85,247,.3); }
    .rl-stat-v { font-size: 22px; font-weight: 800; color: #f3f4f6; line-height: 1; }
    .rl-stat-l { font-size: 11px; color: #9ca3af; text-transform: uppercase; letter-spacing: .5px; font-weight: 600; margin-top: 3px; }

    .rl-code { font-family: ui-monospace, 'Courier New', monospace; font-size: 12px; color: #d8b4fe; background: rgba(139,92,246,.08); border: 1px solid rgba(196,181,253,.25); padding: 3px 8px; border-radius: 6px; }
    .rl-pill-on  { color: #4ade80; border-color: rgba(74,222,128,.4); background: rgba(74,222,128,.12); }
    .rl-pill-off { color: #9ca3af; border-color: rgba(255,255,255,.2); background: rgba(255,255,255,.05); }
    .rl-btn { display: inline-flex; align-items: center; gap: 6px; padding: 7px 13px; border-radius: 8px; font-size: 12px; font-weight: 600; text-decoration: none; cursor: pointer; border: 1px solid rgba(168,85,247,.4); color: #e9d5ff; background: rgba(168,85,247,.14); transition: all .15s; }
    .rl-btn:hover { background: rgba(168,85,247,.24); border-color: rgba(168,85,247,.6); }
    .rl-muted { color: #6b7280; }

    .rl-alert { display: flex; align-items: center; gap: 10px; border-radius: 10px; padding: 12px 14px; margin-bottom: 16px; font-size: 13.5px; border: 1px solid rgba(74,222,128,.32); background: rgba(74,222,128,.08); color: #86efac; }

    .rl-edit-row > td { background: rgba(0,0,0,.25); padding: 0 !important; }
    .rl-edit-inner { padding: 16px 18px; display: none; }
    .rl-edit-inner.open { display: block; }
    .rl-grid { display: grid; grid-template-columns: auto 1fr 1fr 1fr auto; gap: 14px; align-items: end; }
    @media (max-width: 820px) { .rl-grid { grid-template-columns: 1fr 1fr; } }
    .rl-field { display: flex; flex-direction: column; gap: 6px; }
    .rl-field label { font-size: 11px; color: #9ca3af; font-weight: 600; text-transform: uppercase; letter-spacing: .5px; }
    .rl-field input[type=text], .rl-field input[type=number] { background: #0b0b0d; border: 1px solid rgba(255,255,255,.16); border-radius: 8px; color: #e5e7eb; font-size: 14px; padding: 9px 11px; }
    .rl-field input:focus { outline: none; border-color: rgba(168,85,247,.55); box-shadow: 0 0 0 3px rgba(168,85,247,.15); }
    .rl-toggle { display: inline-flex; align-items: center; gap: 8px; color: #e5e7eb; font-size: 13px; font-weight: 600; cursor: pointer; white-space: nowrap; }
    .rl-toggle input { width: 16px; height: 16px; accent-color: #a855f7; cursor: pointer; }
    .rl-save { padding: 9px 16px; border-radius: 8px; border: 1px solid rgba(168,85,247,.5); background: linear-gradient(135deg,#7c3aed,#a855f7); color: #fff; font-size: 13px; font-weight: 600; cursor: pointer; white-space: nowrap; }
    .rl-save:hover { filter: brightness(1.07); }
</style>
@endsection

@section('content')
@php
    $enabledCount = $sites->filter(fn($s) => optional($s->policy)->rate_limiting_enabled)->count();
@endphp

<div class="soc-page-head">
    <div>
        <h1 class="soc-page-title">API Protection · Rate Limiting</h1>
        <div class="soc-page-subtitle">Throttle abusive clients on your API paths. Limits are applied per client IP at the edge (nginx). Managed independently of WAF rules.</div>
    </div>
    <span class="soc-chip">{{ $enabledCount }}/{{ $sites->count() }} Active</span>
</div>

@if (session('status'))
    <div class="rl-alert">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
        <span>{{ session('status') }}</span>
    </div>
@endif

<div class="rl-stat-row">
    <div class="rl-stat">
        <div class="rl-stat-ic">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20v-6M6 20v-4M18 20v-9"/><circle cx="12" cy="6" r="2"/></svg>
        </div>
        <div>
            <div class="rl-stat-v">{{ $sites->count() }}</div>
            <div class="rl-stat-l">Total Sites</div>
        </div>
    </div>
    <div class="rl-stat">
        <div class="rl-stat-ic" style="color:#4ade80;background:rgba(74,222,128,.12);border-color:rgba(74,222,128,.3);">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
        </div>
        <div>
            <div class="rl-stat-v">{{ $enabledCount }}</div>
            <div class="rl-stat-l">Rate Limiting On</div>
        </div>
    </div>
    <div class="rl-stat">
        <div class="rl-stat-ic" style="color:#9ca3af;background:rgba(255,255,255,.05);border-color:rgba(255,255,255,.16);">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 8v4m0 4h.01"/></svg>
        </div>
        <div>
            <div class="rl-stat-v">{{ $sites->count() - $enabledCount }}</div>
            <div class="rl-stat-l">Unprotected</div>
        </div>
    </div>
</div>

<div class="soc-panel">
    <div class="soc-panel-head">
        <div>
            <div class="soc-panel-title">Per-site Rate Limits</div>
            <div class="soc-panel-sub">Configure throttling for each site's API</div>
        </div>
    </div>
    <div class="soc-panel-body soc-table-wrap">
        <table class="soc-table">
            <thead>
                <tr>
                    <th>Site</th>
                    <th>Status</th>
                    <th>API Path</th>
                    <th>Req / min</th>
                    <th>Burst</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            @forelse ($sites as $site)
                @php $p = $site->policy; $on = $p && $p->rate_limiting_enabled; @endphp
                <tr>
                    <td>
                        <strong style="color:#f3f4f6;">{{ $site->name }}</strong>
                        <div style="font-size:11px;color:#9ca3af;font-family:ui-monospace,monospace;">{{ $site->server_name }}</div>
                    </td>
                    <td>
                        @if($on)
                            <span class="soc-pill rl-pill-on">Enabled</span>
                        @else
                            <span class="soc-pill rl-pill-off">Disabled</span>
                        @endif
                    </td>
                    <td>@if($on)<span class="rl-code">{{ $p->rate_limit_path ?: '/api' }}</span>@else<span class="rl-muted">—</span>@endif</td>
                    <td>{{ $on ? number_format($p->requests_per_minute ?? 0) : '—' }}</td>
                    <td>{{ $on ? number_format($p->burst_size ?? 0) : '—' }}</td>
                    <td style="text-align:right;">
                        <button type="button" class="rl-btn" onclick="rlToggle('{{ $site->id }}')">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.6 1.6 0 0 0 .3 1.8l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.6 1.6 0 0 0-1.8-.3 1.6 1.6 0 0 0-1 1.5V21a2 2 0 1 1-4 0v-.2a1.6 1.6 0 0 0-1-1.5 1.6 1.6 0 0 0-1.8.3l-.1.1a2 2 0 0 1-2.8-2.8l.1-.1a1.6 1.6 0 0 0 .3-1.8 1.6 1.6 0 0 0-1.5-1H3a2 2 0 1 1 0-4h.2a1.6 1.6 0 0 0 1.5-1z"/></svg>
                            Configure
                        </button>
                    </td>
                </tr>
                <tr class="rl-edit-row">
                    <td colspan="6">
                        <div class="rl-edit-inner" id="rl-edit-{{ $site->id }}">
                            <form method="POST" action="{{ route('api.rate-limit.update', $site) }}">
                                @csrf
                                @method('PUT')
                                <div class="rl-grid">
                                    <label class="rl-toggle">
                                        <input type="checkbox" name="rate_limiting_enabled" value="1" {{ $on ? 'checked' : '' }}>
                                        Enabled
                                    </label>
                                    <div class="rl-field">
                                        <label>API Path</label>
                                        <input type="text" name="rate_limit_path" value="{{ $p->rate_limit_path ?? '/api' }}" placeholder="/api" style="font-family:monospace;">
                                    </div>
                                    <div class="rl-field">
                                        <label>Requests / min</label>
                                        <input type="number" name="requests_per_minute" value="{{ $p->requests_per_minute ?? 60 }}" min="1">
                                    </div>
                                    <div class="rl-field">
                                        <label>Burst</label>
                                        <input type="number" name="burst_size" value="{{ $p->burst_size ?? 10 }}" min="1">
                                    </div>
                                    <button type="submit" class="rl-save">Save</button>
                                </div>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align:center;padding:40px;color:#9ca3af;">
                        No sites yet. Create a site first, then configure its API rate limits.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
    function rlToggle(id) {
        var el = document.getElementById('rl-edit-' + id);
        if (el) el.classList.toggle('open');
    }
</script>
@endsection
