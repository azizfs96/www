@extends('layouts.waf')

@section('title', 'Firewall Rules Management')

@section('styles')
    /* ===================================================================
       Cloudflare-inspired clean firewall UI (dark + purple identity)
       =================================================================== */
    :root {
        --cf-purple: #a855f7;
        --cf-purple-2: #7c3aed;
        --cf-line: rgba(255,255,255,.30);
        --cf-line-strong: rgba(255,255,255,.45);
        --cf-card: #25262c;
        --cf-card-2: #2f3037;
        --cf-input: #33343b;
        --cf-text: #ffffff;
        --cf-muted: #d2d6dd;
        --cf-faint: #a7adb8;
    }

    .cf-wrap { max-width: 1080px; }

    /* ---- Page header ---- */
    .cf-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; flex-wrap: wrap; margin-bottom: 22px; }
    .cf-title { font-size: 26px; font-weight: 800; color: #ffffff; letter-spacing: -.4px; margin: 0 0 4px; }
    .cf-subtitle { color: #d3d7de; font-size: 13.5px; max-width: 620px; line-height: 1.5; }

    /* ---- Scope selector ---- */
    .cf-scope { display: flex; flex-direction: column; gap: 6px; }
    .cf-scope-label { font-size: 10.5px; text-transform: uppercase; letter-spacing: .7px; color: var(--cf-faint); font-weight: 700; }
    .cf-scope-row { display: inline-flex; gap: 4px; flex-wrap: wrap; padding: 4px; background: var(--cf-card); border: 1px solid var(--cf-line); border-radius: 10px; }
    .cf-scope-btn {
        padding: 6px 12px; border-radius: 7px; font-size: 12.5px; font-weight: 600;
        color: var(--cf-muted); text-decoration: none; border: 1px solid transparent; white-space: nowrap; transition: all .14s;
    }
    .cf-scope-btn:hover { color: var(--cf-text); background: rgba(255,255,255,.05); }
    .cf-scope-btn.active { color: #fff; background: linear-gradient(135deg, var(--cf-purple-2), var(--cf-purple)); box-shadow: 0 4px 14px rgba(124,58,237,.3); }

    /* ---- Alerts ---- */
    .cf-alert { display: flex; align-items: flex-start; gap: 10px; border-radius: 10px; padding: 12px 14px; margin-bottom: 16px; font-size: 13.5px; border: 1px solid; line-height: 1.5; }
    .cf-alert svg { flex: 0 0 auto; margin-top: 1px; }
    .cf-alert.ok  { background: rgba(74,222,128,.08); border-color: rgba(74,222,128,.32); color: #86efac; }
    .cf-alert.err { background: rgba(248,113,113,.08); border-color: rgba(248,113,113,.32); color: #fca5a5; }

    /* ---- Tabs (underline style) ---- */
    .cf-tabs { display: flex; gap: 4px; border-bottom: 1.5px solid var(--cf-line-strong); margin-bottom: 0; flex-wrap: wrap; }
    .cf-tab {
        display: inline-flex; align-items: center; gap: 8px; padding: 12px 16px;
        background: transparent; border: none; border-bottom: 2.5px solid transparent;
        color: #c8cdd5; font-size: 13.5px; font-weight: 600; cursor: pointer; transition: color .14s, border-color .14s;
        margin-bottom: -1.5px;
    }
    .cf-tab:hover { color: #ffffff; }
    .cf-tab.active { color: #ffffff; border-bottom-color: var(--cf-purple); }
    .cf-tab .cf-count {
        font-size: 11px; font-weight: 700; padding: 1px 8px; border-radius: 999px;
        background: rgba(255,255,255,.07); color: var(--cf-muted); border: 1px solid var(--cf-line);
    }
    .cf-tab.active .cf-count { background: rgba(168,85,247,.16); color: #d8b4fe; border-color: rgba(168,85,247,.4); }

    /* ---- Tab panels ---- */
    .cf-panel { display: none; padding-top: 22px; }
    .cf-panel.active { display: block; }

    .cf-panel-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; margin-bottom: 16px; flex-wrap: wrap; }
    .cf-panel-title { font-size: 16px; font-weight: 700; color: #ffffff; margin: 0 0 3px; }
    .cf-panel-desc { font-size: 12.5px; color: #d3d7de; max-width: 560px; line-height: 1.5; }

    /* ---- Buttons ---- */
    .cf-btn { display: inline-flex; align-items: center; justify-content: center; gap: 7px; border-radius: 8px; border: 1px solid transparent; font-size: 13px; font-weight: 600; padding: 9px 15px; cursor: pointer; transition: all .14s; white-space: nowrap; text-decoration: none; }
    .cf-btn-primary { background: linear-gradient(135deg, var(--cf-purple-2), var(--cf-purple)); color: #fff; box-shadow: 0 5px 16px rgba(124,58,237,.28); }
    .cf-btn-primary:hover { filter: brightness(1.07); transform: translateY(-1px); }
    .cf-btn-ghost { background: rgba(255,255,255,.04); color: var(--cf-text); border-color: var(--cf-line-strong); }
    .cf-btn-ghost:hover { background: rgba(255,255,255,.08); }
    .cf-btn-sm { padding: 6px 11px; font-size: 12px; }

    /* ---- Create form (collapsible) ---- */
    .cf-create { border: 1px solid rgba(168,85,247,.45); border-radius: 12px; background: var(--cf-card); margin-bottom: 20px; overflow: hidden; display: none; box-shadow: 0 12px 34px rgba(0,0,0,.5), 0 0 0 1px rgba(168,85,247,.12); }
    .cf-create.open { display: block; }
    .cf-create-head { padding: 14px 18px; border-bottom: 1px solid var(--cf-line); display: flex; align-items: center; gap: 10px; background: linear-gradient(180deg, rgba(168,85,247,.12), transparent); }
    .cf-create-head .dot { width: 8px; height: 8px; border-radius: 999px; background: var(--cf-purple); box-shadow: 0 0 10px rgba(168,85,247,.6); }
    .cf-create-head h3 { font-size: 13.5px; font-weight: 700; color: #f0f1f3; margin: 0; }
    .cf-create-body { padding: 18px; }

    .cf-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 14px; }
    .cf-field { display: flex; flex-direction: column; gap: 6px; }
    .cf-field.full { grid-column: 1 / -1; }
    .cf-field label { font-size: 11.5px; color: #d4d8df; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; }
    .cf-field input, .cf-field select, .cf-field textarea {
        background: var(--cf-input); border: 1.5px solid rgba(255,255,255,.38); border-radius: 9px; color: #ffffff;
        font-size: 14px; padding: 11px 13px; transition: border-color .14s, box-shadow .14s, background .14s;
    }
    .cf-field textarea { min-height: 80px; resize: vertical; font-family: ui-monospace, monospace; font-size: 13px; }
    .cf-field input::placeholder, .cf-field textarea::placeholder { color: #9da3ae; }
    .cf-field input:hover, .cf-field select:hover, .cf-field textarea:hover { border-color: rgba(255,255,255,.55); background: #303037; }
    .cf-field input:focus, .cf-field select:focus, .cf-field textarea:focus { outline: none; background: #34343b; border-color: var(--cf-purple); box-shadow: 0 0 0 3px rgba(168,85,247,.3); }
    .cf-create-actions { display: flex; gap: 10px; justify-content: flex-end; margin-top: 18px; padding-top: 16px; border-top: 1px solid var(--cf-line); }

    /* ---- Rule list (Cloudflare expression rows) ---- */
    .cf-rules { display: flex; flex-direction: column; gap: 10px; }
    .cf-rule {
        display: flex; align-items: center; gap: 14px; padding: 14px 16px;
        background: var(--cf-card); border: 1px solid var(--cf-line-strong); border-radius: 12px;
        transition: border-color .14s, background .14s;
    }
    .cf-rule:hover { border-color: rgba(168,85,247,.32); background: var(--cf-card-2); }
    .cf-rule-order {
        flex: 0 0 auto; width: 28px; height: 28px; border-radius: 8px; display: flex; align-items: center; justify-content: center;
        font-size: 12px; font-weight: 700; color: var(--cf-muted); background: rgba(255,255,255,.04); border: 1px solid var(--cf-line);
    }
    .cf-rule-main { flex: 1; min-width: 0; }
    .cf-rule-name { font-size: 13.5px; font-weight: 600; color: #f0f1f3; margin-bottom: 5px; display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
    .cf-rule-name .site-tag { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .4px; color: var(--cf-muted); background: rgba(255,255,255,.05); border: 1px solid var(--cf-line); padding: 2px 7px; border-radius: 5px; }
    .cf-expr {
        font-family: ui-monospace, 'Courier New', monospace; font-size: 12.5px; color: #e6e9ee;
        background: #0a0a0c; border: 1px solid var(--cf-line-strong); border-radius: 8px; padding: 8px 12px; line-height: 1.5;
        word-break: break-word;
    }
    .cf-expr .kw { color: #aeb4bf; }
    .cf-expr .field { color: #93c5fd; }
    .cf-expr .op { color: #fcd34d; }
    .cf-expr .val { color: #e9d5ff; }
    .cf-rule-meta { font-size: 11px; color: #a2a8b3; margin-top: 6px; font-family: ui-monospace, monospace; }

    .cf-rule-side { flex: 0 0 auto; display: flex; align-items: center; gap: 12px; }
    .cf-action-badge { display: inline-flex; align-items: center; gap: 6px; font-size: 11.5px; font-weight: 700; padding: 5px 11px; border-radius: 999px; border: 1px solid; text-transform: capitalize; }
    .cf-action-badge.allow { color: #4ade80; border-color: rgba(74,222,128,.4); background: rgba(74,222,128,.1); }
    .cf-action-badge.block { color: #f87171; border-color: rgba(248,113,113,.42); background: rgba(248,113,113,.12); }
    .cf-action-badge .pip { width: 6px; height: 6px; border-radius: 999px; background: currentColor; }

    .cf-del { width: 32px; height: 32px; border-radius: 8px; display: inline-flex; align-items: center; justify-content: center; background: transparent; border: 1px solid var(--cf-line); color: var(--cf-muted); cursor: pointer; transition: all .14s; }
    .cf-del:hover { color: #fca5a5; border-color: rgba(248,113,113,.4); background: rgba(248,113,113,.1); }

    /* ---- Empty state ---- */
    .cf-empty { text-align: center; padding: 56px 24px; border: 1.5px solid var(--cf-line); border-radius: 14px; background: #20212600; background: #1d1e23; }
    .cf-empty svg { opacity: .55; margin-bottom: 14px; }
    .cf-empty h4 { font-size: 15px; color: #ffffff; margin: 0 0 6px; font-weight: 700; }
    .cf-empty p { font-size: 13px; color: #cdd2da; margin: 0 0 18px; }

    @media (max-width: 720px) {
        .cf-rule { flex-wrap: wrap; }
        .cf-rule-side { width: 100%; justify-content: space-between; }
    }
@endsection

@section('content')
@php
    $ipCount = $ipRules->count();
    $urlCount = $urlRules->count();
    $countryCount = $countryRules->count();
    $totalRules = $ipCount + $urlCount + $countryCount;
    $scopeSiteId = $siteId !== 'global' && $siteId !== 'all' ? $siteId : '';
    $countryNames = [
        'US' => 'United States', 'SA' => 'Saudi Arabia', 'GB' => 'United Kingdom', 'DE' => 'Germany', 'FR' => 'France',
        'CN' => 'China', 'JP' => 'Japan', 'IN' => 'India', 'BR' => 'Brazil', 'RU' => 'Russia', 'CA' => 'Canada',
        'AU' => 'Australia', 'IT' => 'Italy', 'ES' => 'Spain', 'NL' => 'Netherlands', 'SE' => 'Sweden', 'NO' => 'Norway',
        'DK' => 'Denmark', 'FI' => 'Finland', 'PL' => 'Poland', 'KR' => 'South Korea', 'MX' => 'Mexico', 'AR' => 'Argentina',
        'ZA' => 'South Africa', 'EG' => 'Egypt', 'AE' => 'United Arab Emirates', 'TR' => 'Turkey', 'ID' => 'Indonesia',
        'TH' => 'Thailand', 'VN' => 'Vietnam', 'PH' => 'Philippines', 'MY' => 'Malaysia', 'SG' => 'Singapore',
        'NZ' => 'New Zealand', 'IE' => 'Ireland', 'CH' => 'Switzerland', 'AT' => 'Austria', 'BE' => 'Belgium',
        'PT' => 'Portugal', 'GR' => 'Greece', 'CZ' => 'Czech Republic', 'HU' => 'Hungary', 'RO' => 'Romania',
        'BG' => 'Bulgaria', 'HR' => 'Croatia', 'SK' => 'Slovakia', 'SI' => 'Slovenia', 'LT' => 'Lithuania',
        'LV' => 'Latvia', 'EE' => 'Estonia', 'IS' => 'Iceland', 'LU' => 'Luxembourg', 'MT' => 'Malta', 'CY' => 'Cyprus',
        'KW' => 'Kuwait', 'QA' => 'Qatar', 'BH' => 'Bahrain', 'OM' => 'Oman', 'JO' => 'Jordan', 'LB' => 'Lebanon',
        'IQ' => 'Iraq', 'SY' => 'Syria', 'YE' => 'Yemen', 'PK' => 'Pakistan', 'BD' => 'Bangladesh', 'LK' => 'Sri Lanka',
        'NP' => 'Nepal', 'AF' => 'Afghanistan', 'IR' => 'Iran', 'IL' => 'Israel', 'PS' => 'Palestine', 'LOCAL' => 'Local Network',
    ];
@endphp

<div class="cf-wrap">

    <div class="cf-head">
        <div>
            <h1 class="cf-title">Firewall Rules</h1>
            <div class="cf-subtitle">Control which requests reach your applications. Rules are evaluated top to bottom—matching requests are allowed or blocked based on their action.</div>
        </div>
        <div class="cf-scope">
            <span class="cf-scope-label">Scope</span>
            <div class="cf-scope-row">
                @if(auth()->user()->isSuperAdmin())
                    <a href="{{ route('firewall.index', ['site_id' => 'global']) }}" class="cf-scope-btn {{ $siteId === 'global' ? 'active' : '' }}">Global</a>
                @endif
                <a href="{{ route('firewall.index', ['site_id' => 'all']) }}" class="cf-scope-btn {{ $siteId === 'all' ? 'active' : '' }}">All Sites</a>
                @foreach($sites as $s)
                    <a href="{{ route('firewall.index', ['site_id' => $s->id]) }}" class="cf-scope-btn {{ $siteId == $s->id ? 'active' : '' }}">{{ $s->name }}</a>
                @endforeach
            </div>
        </div>
    </div>

    @if (session('status'))
        <div class="cf-alert ok">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
            <span>{{ session('status') }}</span>
        </div>
    @endif

    @if ($errors->any())
        <div class="cf-alert err">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v4m0 4h.01"/></svg>
            <div>@foreach ($errors->all() as $error){{ $error }}<br>@endforeach</div>
        </div>
    @endif

    {{-- ============ Tabs ============ --}}
    <div class="cf-tabs">
        <button type="button" class="cf-tab active" data-tab="ip" onclick="cfTab('ip', this)">
            IP Access Rules <span class="cf-count">{{ $ipCount }}</span>
        </button>
        <button type="button" class="cf-tab" data-tab="url" onclick="cfTab('url', this)">
            URL Path Rules <span class="cf-count">{{ $urlCount }}</span>
        </button>
        <button type="button" class="cf-tab" data-tab="country" onclick="cfTab('country', this)">
            Country Rules <span class="cf-count">{{ $countryCount }}</span>
        </button>
    </div>

    {{-- ============ IP PANEL ============ --}}
    <div class="cf-panel active" id="panel-ip">
        <div class="cf-panel-head">
            <div>
                <h2 class="cf-panel-title">IP Access Rules</h2>
                <p class="cf-panel-desc">Allow or block individual IP addresses. Useful for whitelisting trusted sources or stopping known bad actors.</p>
            </div>
            <button type="button" class="cf-btn cf-btn-primary" onclick="cfToggleCreate('ip')">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
                Create rule
            </button>
        </div>

        <div class="cf-create" id="create-ip">
            <div class="cf-create-head"><span class="dot"></span><h3>New IP access rule</h3></div>
            <div class="cf-create-body">
                <form method="POST" action="{{ route('firewall.store') }}">
                    @csrf
                    <input type="hidden" name="rule_type" value="ip">
                    <input type="hidden" name="site_id" value="{{ $scopeSiteId }}">
                    <div class="cf-grid">
                        @if($siteId === 'global' || $siteId === 'all')
                        <div class="cf-field">
                            <label>Site</label>
                            <select name="site_id">
                                @if(auth()->user()->isSuperAdmin())<option value="">Global (All Sites)</option>@endif
                                @foreach($sites as $site)<option value="{{ $site->id }}">{{ $site->name }} ({{ $site->server_name }})</option>@endforeach
                            </select>
                        </div>
                        @endif
                        <div class="cf-field"><label>IP Address</label><input type="text" name="ip" placeholder="137.59.230.231" required></div>
                        <div class="cf-field"><label>Action</label><select name="type" required><option value="allow">Allow</option><option value="block">Block</option></select></div>
                    </div>
                    <div class="cf-create-actions">
                        <button type="button" class="cf-btn cf-btn-ghost" onclick="cfToggleCreate('ip')">Cancel</button>
                        <button type="submit" class="cf-btn cf-btn-primary">Deploy rule</button>
                    </div>
                </form>
            </div>
        </div>

        @if($ipCount)
            <div class="cf-rules">
                @foreach ($ipRules as $i => $rule)
                    <div class="cf-rule">
                        <div class="cf-rule-order">{{ $i + 1 }}</div>
                        <div class="cf-rule-main">
                            <div class="cf-rule-name">
                                IP rule
                                <span class="site-tag">{{ $rule->site_id ? $rule->site->name : 'Global' }}</span>
                            </div>
                            <div class="cf-expr"><span class="kw">when</span> <span class="field">ip.src</span> <span class="op">eq</span> <span class="val">{{ $rule->ip }}</span></div>
                            <div class="cf-rule-meta">Created {{ $rule->created_at->format('Y-m-d H:i') }}</div>
                        </div>
                        <div class="cf-rule-side">
                            <span class="cf-action-badge {{ $rule->type }}"><span class="pip"></span>{{ $rule->type }}</span>
                            <form method="POST" action="{{ route('firewall.destroy', $rule->id) }}" onsubmit="return confirm('Delete this rule?');">
                                @csrf @method('DELETE')
                                <input type="hidden" name="rule_type" value="ip">
                                <button type="submit" class="cf-del" title="Delete">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2m2 0v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/></svg>
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            @include('waf.partials._fw-empty', ['type' => 'ip', 'label' => 'IP access rules'])
        @endif
    </div>

    {{-- ============ URL PANEL ============ --}}
    <div class="cf-panel" id="panel-url">
        <div class="cf-panel-head">
            <div>
                <h2 class="cf-panel-title">URL Path Rules</h2>
                <p class="cf-panel-desc">Restrict sensitive paths so only approved IP addresses can reach them—ideal for admin panels and internal endpoints.</p>
            </div>
            <button type="button" class="cf-btn cf-btn-primary" onclick="cfToggleCreate('url')">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
                Create rule
            </button>
        </div>

        <div class="cf-create" id="create-url">
            <div class="cf-create-head"><span class="dot"></span><h3>New URL path rule</h3></div>
            <div class="cf-create-body">
                <form method="POST" action="{{ route('firewall.store') }}">
                    @csrf
                    <input type="hidden" name="rule_type" value="url">
                    <input type="hidden" name="site_id" value="{{ $scopeSiteId }}">
                    <div class="cf-grid">
                        @if($siteId === 'global' || $siteId === 'all')
                        <div class="cf-field">
                            <label>Site</label>
                            <select name="site_id">
                                @if(auth()->user()->isSuperAdmin())<option value="">Global (All Sites)</option>@endif
                                @foreach($sites as $site)<option value="{{ $site->id }}">{{ $site->name }} ({{ $site->server_name }})</option>@endforeach
                            </select>
                        </div>
                        @endif
                        <div class="cf-field"><label>Name</label><input type="text" name="name" placeholder="Admin panel lockdown"></div>
                        <div class="cf-field"><label>Host</label><input type="text" name="host" placeholder="example.com"></div>
                        <div class="cf-field"><label>Path</label><input type="text" name="path" placeholder="/admin" required></div>
                        <div class="cf-field full"><label>Allowed IPs</label><textarea name="allowed_ips" placeholder="192.168.1.1, 10.0.0.1" required></textarea></div>
                    </div>
                    <div class="cf-create-actions">
                        <button type="button" class="cf-btn cf-btn-ghost" onclick="cfToggleCreate('url')">Cancel</button>
                        <button type="submit" class="cf-btn cf-btn-primary">Deploy rule</button>
                    </div>
                </form>
            </div>
        </div>

        @if($urlCount)
            <div class="cf-rules">
                @foreach ($urlRules as $i => $rule)
                    <div class="cf-rule">
                        <div class="cf-rule-order">{{ $i + 1 }}</div>
                        <div class="cf-rule-main">
                            <div class="cf-rule-name">
                                {{ $rule->name ?: 'URL rule' }}
                                <span class="site-tag">{{ $rule->site_id ? $rule->site->name : ($rule->host ?: 'Global') }}</span>
                            </div>
                            <div class="cf-expr"><span class="kw">when</span> <span class="field">http.request.uri.path</span> <span class="op">eq</span> <span class="val">{{ $rule->path }}</span> <span class="kw">and</span> <span class="field">ip.src</span> <span class="op">in</span> <span class="val">{ {{ $rule->allowed_ips }} }</span></div>
                            <div class="cf-rule-meta">Created {{ $rule->created_at->format('Y-m-d H:i') }}</div>
                        </div>
                        <div class="cf-rule-side">
                            <span class="cf-action-badge {{ $rule->enabled ? 'allow' : 'block' }}"><span class="pip"></span>{{ $rule->enabled ? 'enabled' : 'disabled' }}</span>
                            <form method="POST" action="{{ route('firewall.destroy', $rule->id) }}" onsubmit="return confirm('Delete this rule?');">
                                @csrf @method('DELETE')
                                <input type="hidden" name="rule_type" value="url">
                                <button type="submit" class="cf-del" title="Delete">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2m2 0v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/></svg>
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            @include('waf.partials._fw-empty', ['type' => 'url', 'label' => 'URL path rules'])
        @endif
    </div>

    {{-- ============ COUNTRY PANEL ============ --}}
    <div class="cf-panel" id="panel-country">
        <div class="cf-panel-head">
            <div>
                <h2 class="cf-panel-title">Country Rules</h2>
                <p class="cf-panel-desc">Allow or block all traffic from a country based on the visitor's geographic location (ISO 3166-1 alpha-2 codes).</p>
            </div>
            <button type="button" class="cf-btn cf-btn-primary" onclick="cfToggleCreate('country')">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
                Create rule
            </button>
        </div>

        <div class="cf-create" id="create-country">
            <div class="cf-create-head"><span class="dot"></span><h3>New country rule</h3></div>
            <div class="cf-create-body">
                <form method="POST" action="{{ route('firewall.store') }}">
                    @csrf
                    <input type="hidden" name="rule_type" value="country">
                    <input type="hidden" name="site_id" value="{{ $scopeSiteId }}">
                    <div class="cf-grid">
                        @if($siteId === 'global' || $siteId === 'all')
                        <div class="cf-field">
                            <label>Site</label>
                            <select name="site_id">
                                @if(auth()->user()->isSuperAdmin())<option value="">Global (All Sites)</option>@endif
                                @foreach($sites as $site)<option value="{{ $site->id }}">{{ $site->name }} ({{ $site->server_name }})</option>@endforeach
                            </select>
                        </div>
                        @endif
                        <div class="cf-field"><label>Country Code</label><input type="text" name="country_code" placeholder="SA" maxlength="2" required style="text-transform:uppercase;"></div>
                        <div class="cf-field"><label>Action</label><select name="type" required><option value="block">Block</option><option value="allow">Allow</option></select></div>
                    </div>
                    <div class="cf-create-actions">
                        <button type="button" class="cf-btn cf-btn-ghost" onclick="cfToggleCreate('country')">Cancel</button>
                        <button type="submit" class="cf-btn cf-btn-primary">Deploy rule</button>
                    </div>
                </form>
            </div>
        </div>

        @if($countryCount)
            <div class="cf-rules">
                @foreach ($countryRules as $i => $rule)
                    @php $cc = strtoupper($rule->country_code); $cn = $countryNames[$cc] ?? $cc; @endphp
                    <div class="cf-rule">
                        <div class="cf-rule-order">{{ $i + 1 }}</div>
                        <div class="cf-rule-main">
                            <div class="cf-rule-name">
                                {{ $cn }}
                                <span class="site-tag">{{ $rule->site_id ? $rule->site->name : 'Global' }}</span>
                            </div>
                            <div class="cf-expr"><span class="kw">when</span> <span class="field">ip.geoip.country</span> <span class="op">eq</span> <span class="val">{{ $cc }}</span></div>
                            <div class="cf-rule-meta">Created {{ $rule->created_at->format('Y-m-d H:i') }}</div>
                        </div>
                        <div class="cf-rule-side">
                            <span class="cf-action-badge {{ $rule->type }}"><span class="pip"></span>{{ $rule->type }}</span>
                            <form method="POST" action="{{ route('firewall.destroy', $rule->id) }}" onsubmit="return confirm('Delete this rule?');">
                                @csrf @method('DELETE')
                                <input type="hidden" name="rule_type" value="country">
                                <button type="submit" class="cf-del" title="Delete">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2m2 0v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/></svg>
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            @include('waf.partials._fw-empty', ['type' => 'country', 'label' => 'country rules'])
        @endif
    </div>

</div>
@endsection

@section('scripts')
<script>
    function cfTab(name, btn) {
        document.querySelectorAll('.cf-panel').forEach(p => p.classList.remove('active'));
        document.querySelectorAll('.cf-tab').forEach(t => t.classList.remove('active'));
        document.getElementById('panel-' + name).classList.add('active');
        btn.classList.add('active');
    }
    function cfToggleCreate(name) {
        document.getElementById('create-' + name).classList.toggle('open');
    }
    // Re-open the relevant create form / tab on validation error
    @if($errors->any() && old('rule_type'))
        document.addEventListener('DOMContentLoaded', function () {
            var t = @json(old('rule_type'));
            var btn = document.querySelector('.cf-tab[data-tab="' + t + '"]');
            if (btn) cfTab(t, btn);
            var c = document.getElementById('create-' + t);
            if (c) c.classList.add('open');
        });
    @endif
</script>
@endsection
