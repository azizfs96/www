@extends('layouts.waf')

@section('title', 'Firewall Rules Management')

@section('styles')
@include('waf.soc._theme')
<style>
    .fw-main-layout { margin-bottom: 12px; }

    .fw-alert {
        border-radius: 12px;
        padding: 14px 16px;
        margin-bottom: 14px;
        font-size: 14px;
        border: 1px solid rgba(255,255,255,.14);
    }
    .fw-alert-success {
        background: rgba(74, 222, 128, 0.08);
        border-color: rgba(74, 222, 128, 0.35);
        color: #86efac;
    }
    .fw-alert-error {
        background: rgba(248, 113, 113, 0.08);
        border-color: rgba(248, 113, 113, 0.35);
        color: #fca5a5;
    }

    .fw-step-label {
        font-size: 11px;
        color: #9ca3af;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        margin-bottom: 8px;
    }
    .fw-nested {
        padding: 12px;
        border: 1px solid rgba(255,255,255,.1);
        border-radius: 12px;
        background: rgba(255,255,255,.02);
    }
    .fw-scope-row { display: flex; gap: 8px; flex-wrap: wrap; align-items: center; }

    .rule-tabs {
        display: flex;
        gap: 6px;
        flex-wrap: wrap;
        align-items: center;
    }
    .rule-tab {
        padding: 8px 14px;
        background: transparent;
        border: 1px solid rgba(255,255,255,.12);
        border-radius: 999px;
        color: #9ca3af;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 0.4px;
        cursor: pointer;
        transition: background .15s, border-color .15s, color .15s;
    }
    .rule-tab:hover {
        color: #e5e7eb;
        border-color: rgba(168,85,247,.35);
        background: rgba(168,85,247,.08);
    }
    .rule-tab.active {
        color: #f5f3ff;
        border-color: rgba(168,85,247,.55);
        background: linear-gradient(135deg, rgba(124,58,237,.35), rgba(168,85,247,.22));
        box-shadow: 0 4px 16px rgba(124,58,237,.2);
    }

    .tab-content { display: none; margin-top: 12px; padding: 14px; border: 1px solid rgba(255,255,255,.1); border-radius: 12px; background: rgba(0,0,0,.2); }
    .tab-content.active { display: block; }

    .form-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 12px;
        align-items: end;
    }
    @media (max-width: 768px) {
        .form-row { grid-template-columns: 1fr; }
    }
    .form-group { display: flex; flex-direction: column; gap: 6px; }
    .form-group label {
        font-size: 11px;
        color: #9ca3af;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .form-group input,
    .form-group select,
    .form-group textarea {
        background: #0b0b0d;
        border-radius: 10px;
        border: 1px solid rgba(255,255,255,.14);
        color: #e5e7eb;
        font-size: 14px;
        padding: 10px 12px;
        transition: border-color .15s, box-shadow .15s;
    }
    .form-group textarea { min-height: 88px; resize: vertical; font-family: inherit; }
    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: rgba(168,85,247,.55);
        box-shadow: 0 0 0 3px rgba(168,85,237,.15);
    }

    .btn {
        border-radius: 999px;
        border: 1px solid transparent;
        font-size: 12px;
        padding: 8px 14px;
        cursor: pointer;
        font-weight: 600;
        transition: all .15s;
        white-space: nowrap;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
    }
    .btn-primary {
        background: linear-gradient(135deg, #7c3aed, #a855f7);
        color: #fff;
        border-color: rgba(168,85,247,.5);
        box-shadow: 0 6px 18px rgba(124,58,237,.25);
    }
    .btn-primary:hover { filter: brightness(1.06); transform: translateY(-1px); }
    .btn-secondary {
        background: rgba(255,255,255,.06);
        color: #d1d5db;
        border-color: rgba(255,255,255,.18);
    }
    .btn-secondary:hover { background: rgba(255,255,255,.1); color: #f3f4f6; }
    .btn-secondary.active {
        border-color: rgba(168,85,247,.5);
        color: #e9d5ff;
        background: rgba(168,85,247,.15);
    }
    .btn-danger {
        background: rgba(248,113,113,.12);
        color: #fca5a5;
        border-color: rgba(248,113,113,.35);
        padding: 6px 12px;
        font-size: 12px;
    }
    .btn-danger:hover { background: rgba(248,113,113,.22); color: #fecaca; }

    .form-actions { display: flex; align-items: end; }
    .form-actions .btn { width: 100%; justify-content: center; padding: 11px 16px; }

    .fw-type-badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border: 1px solid;
    }
    .fw-type-badge.ip { color: #93c5fd; border-color: rgba(147,197,253,.4); background: rgba(59,130,246,.12); }
    .fw-type-badge.url { color: #c4b5fd; border-color: rgba(196,181,253,.4); background: rgba(139,92,246,.12); }
    .fw-type-badge.country { color: #fcd34d; border-color: rgba(252,211,77,.4); background: rgba(245,158,11,.12); }

    .fw-pill-allow { color: #4ade80; border-color: rgba(74,222,128,.4); background: rgba(74,222,128,.12); }
    .fw-pill-block { color: #f87171; border-color: rgba(248,113,113,.42); background: rgba(248,113,113,.14); }

    .fw-table-code {
        font-family: ui-monospace, 'Courier New', monospace;
        font-size: 12px;
        padding: 3px 8px;
        border-radius: 6px;
        border: 1px solid rgba(196,181,253,.25);
        background: rgba(139,92,246,.08);
        color: #d8b4fe;
    }

    .fw-empty { text-align: center; padding: 40px 20px; color: #9ca3af; font-size: 13px; }
    .fw-mono-muted { color: #9ca3af; font-size: 12px; font-family: ui-monospace, monospace; }
</style>
@endsection

@section('content')
@php
    $ipCount = $ipRules->count();
    $urlCount = $urlRules->count();
    $countryCount = $countryRules->count();
    $totalRules = $ipCount + $urlCount + $countryCount;
    $currentScope = $siteId === 'all' ? 'All Sites' : ($siteId === 'global' ? 'Global' : ($sites->firstWhere('id', (int) $siteId)->name ?? 'Selected Site'));
@endphp

<div class="soc-page-head">
    <div>
        <h1 class="soc-page-title">Firewall Policy</h1>
        <div class="soc-page-subtitle">Define IP, URL, and country rules in one security operations view—aligned with your SOC console.</div>
    </div>
    <span class="soc-chip">Scope: {{ $currentScope }}</span>
</div>

@if (session('status'))
    <div class="fw-alert fw-alert-success">{{ session('status') }}</div>
@endif

@if ($errors->any())
    <div class="fw-alert fw-alert-error">
        @foreach ($errors->all() as $error)
            {{ $error }}<br>
        @endforeach
    </div>
@endif

<div class="fw-main-layout">
    <div class="soc-panel" style="margin-bottom:0;">
        <div class="soc-panel-head">
            <div>
                <div class="soc-panel-title">Rule Composer</div>
                <div class="soc-panel-sub">Select scope, rule type, then submit—changes apply to the selected environment.</div>
            </div>
        </div>
        <div class="soc-panel-body" style="display:grid; gap:12px;">
            <div class="fw-nested">
                <div class="fw-step-label">Scope</div>
                <div class="fw-scope-row">
                    @if(auth()->user()->isSuperAdmin())
                        <a href="{{ route('firewall.index', ['site_id' => 'global']) }}" class="btn {{ $siteId === 'global' ? 'btn-primary' : 'btn-secondary' }}">Global</a>
                    @endif
                    <a href="{{ route('firewall.index', ['site_id' => 'all']) }}" class="btn {{ $siteId === 'all' ? 'btn-primary' : 'btn-secondary' }}">All Sites</a>
                    @foreach($sites as $s)
                        <a href="{{ route('firewall.index', ['site_id' => $s->id]) }}" class="btn {{ $siteId == $s->id ? 'btn-primary' : 'btn-secondary' }}">{{ $s->name }}</a>
                    @endforeach
                </div>
            </div>

            <div class="fw-nested">
                <div class="fw-step-label">Rule Type</div>
                <div class="rule-tabs">
                    <button type="button" class="rule-tab active" onclick="switchTab('ip', this)">IP</button>
                    <button type="button" class="rule-tab" onclick="switchTab('url', this)">URL</button>
                    <button type="button" class="rule-tab" onclick="switchTab('country', this)">Country</button>
                </div>

                <div id="tab-ip" class="tab-content active">
                    <form method="POST" action="{{ route('firewall.store') }}" id="ip-form">
                        @csrf
                        <input type="hidden" name="rule_type" value="ip">
                        <input type="hidden" name="site_id" value="{{ $siteId !== 'global' && $siteId !== 'all' ? $siteId : '' }}">
                        <div class="form-row">
                            @if($siteId === 'global' || $siteId === 'all')
                            <div class="form-group">
                                <label>Site</label>
                                <select name="site_id">
                                    @if(auth()->user()->isSuperAdmin())<option value="">Global (All Sites)</option>@endif
                                    @foreach($sites as $site)<option value="{{ $site->id }}">{{ $site->name }} ({{ $site->server_name }})</option>@endforeach
                                </select>
                            </div>
                            @endif
                            <div class="form-group"><label>IP Address</label><input type="text" name="ip" placeholder="e.g., 137.59.230.231" required></div>
                            <div class="form-group"><label>Action</label><select name="type" required><option value="allow">Allow</option><option value="block">Block</option></select></div>
                            <div class="form-group form-actions"><button type="submit" class="btn btn-primary">Add IP Rule</button></div>
                        </div>
                    </form>
                </div>

                <div id="tab-url" class="tab-content">
                    <form method="POST" action="{{ route('firewall.store') }}" id="url-form">
                        @csrf
                        <input type="hidden" name="rule_type" value="url">
                        <input type="hidden" name="site_id" value="{{ $siteId !== 'global' && $siteId !== 'all' ? $siteId : '' }}">
                        <div class="form-row">
                            @if($siteId === 'global' || $siteId === 'all')
                            <div class="form-group">
                                <label>Site</label>
                                <select name="site_id">
                                    @if(auth()->user()->isSuperAdmin())<option value="">Global (All Sites)</option>@endif
                                    @foreach($sites as $site)<option value="{{ $site->id }}">{{ $site->name }} ({{ $site->server_name }})</option>@endforeach
                                </select>
                            </div>
                            @endif
                            <div class="form-group"><label>Name</label><input type="text" name="name" placeholder="Rule name"></div>
                            <div class="form-group"><label>Host</label><input type="text" name="host" placeholder="e.g., example.com"></div>
                            <div class="form-group"><label>Path</label><input type="text" name="path" placeholder="/admin" required></div>
                            <div class="form-group"><label>Allowed IPs</label><textarea name="allowed_ips" placeholder="192.168.1.1, 10.0.0.1" required></textarea></div>
                            <div class="form-group form-actions"><button type="submit" class="btn btn-primary">Add URL Rule</button></div>
                        </div>
                    </form>
                </div>

                <div id="tab-country" class="tab-content">
                    <form method="POST" action="{{ route('firewall.store') }}" id="country-form">
                        @csrf
                        <input type="hidden" name="rule_type" value="country">
                        <input type="hidden" name="site_id" value="{{ $siteId !== 'global' && $siteId !== 'all' ? $siteId : '' }}">
                        <div class="form-row">
                            @if($siteId === 'global' || $siteId === 'all')
                            <div class="form-group">
                                <label>Site</label>
                                <select name="site_id">
                                    @if(auth()->user()->isSuperAdmin())<option value="">Global (All Sites)</option>@endif
                                    @foreach($sites as $site)<option value="{{ $site->id }}">{{ $site->name }} ({{ $site->server_name }})</option>@endforeach
                                </select>
                            </div>
                            @endif
                            <div class="form-group"><label>Country Code</label><input type="text" name="country_code" placeholder="SA" maxlength="2" required style="text-transform:uppercase;"></div>
                            <div class="form-group"><label>Action</label><select name="type" required><option value="block">Block</option><option value="allow">Allow</option></select></div>
                            <div class="form-group form-actions"><button type="submit" class="btn btn-primary">Add Country Rule</button></div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="soc-panel">
    <div class="soc-panel-head">
        <div>
            <div class="soc-panel-title">Rules Inventory</div>
            <div class="soc-panel-sub">Unified list of IP, URL, and country rules</div>
        </div>
        <span class="soc-pill soc-open">{{ $totalRules }} rule(s)</span>
    </div>
    <div class="soc-panel-body soc-table-wrap">
        <table class="soc-table">
            <thead>
                <tr>
                    <th>Type</th>
                    <th>Site</th>
                    <th>Details</th>
                    <th>Action</th>
                    <th>Created</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            @foreach ($ipRules as $rule)
                <tr>
                    <td><span class="fw-type-badge ip">IP</span></td>
                    <td>
                        @if($rule->site_id)
                            <span class="soc-pill soc-open">{{ $rule->site->name }}</span>
                        @else
                            <span class="soc-pill soc-open">Global</span>
                        @endif
                    </td>
                    <td><strong style="font-family:monospace;">{{ $rule->ip }}</strong></td>
                    <td>
                        @if ($rule->type === 'allow')
                            <span class="soc-pill fw-pill-allow">Allow</span>
                        @else
                            <span class="soc-pill fw-pill-block">Block</span>
                        @endif
                    </td>
                    <td class="fw-mono-muted">{{ $rule->created_at->format('Y-m-d H:i:s') }}</td>
                    <td>
                        <form method="POST" action="{{ route('firewall.destroy', $rule->id) }}"
                              onsubmit="return confirm('Are you sure you want to delete this rule?');" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <input type="hidden" name="rule_type" value="ip">
                            <button type="submit" class="btn btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach

            @foreach ($urlRules as $rule)
                <tr>
                    <td><span class="fw-type-badge url">URL</span></td>
                    <td>
                        @if($rule->site_id)
                            <span class="soc-pill soc-open">{{ $rule->site->name }}</span>
                        @elseif($rule->host)
                            <span class="fw-table-code">{{ $rule->host }}</span>
                        @else
                            <span class="soc-pill soc-open">Global</span>
                        @endif
                    </td>
                    <td>
                        <div><strong>{{ $rule->name ?? '—' }}</strong></div>
                        <div style="margin-top:4px;"><span class="fw-table-code">{{ $rule->path }}</span></div>
                        <div style="margin-top:4px;font-size:11px;color:#9ca3af;">IPs: {{ $rule->allowed_ips }}</div>
                    </td>
                    <td>
                        @if($rule->enabled)
                            <span class="soc-pill soc-low">Enabled</span>
                        @else
                            <span class="soc-pill soc-open">Disabled</span>
                        @endif
                    </td>
                    <td class="fw-mono-muted">{{ $rule->created_at->format('Y-m-d H:i:s') }}</td>
                    <td>
                        <form method="POST" action="{{ route('firewall.destroy', $rule->id) }}"
                              onsubmit="return confirm('Are you sure you want to delete this rule?');" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <input type="hidden" name="rule_type" value="url">
                            <button type="submit" class="btn btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach

            @foreach ($countryRules as $rule)
                @php
                    $countryNames = [
                        'US' => 'United States', 'SA' => 'Saudi Arabia', 'GB' => 'United Kingdom',
                        'DE' => 'Germany', 'FR' => 'France', 'CN' => 'China', 'JP' => 'Japan',
                        'IN' => 'India', 'BR' => 'Brazil', 'RU' => 'Russia', 'CA' => 'Canada',
                        'AU' => 'Australia', 'IT' => 'Italy', 'ES' => 'Spain', 'NL' => 'Netherlands',
                        'SE' => 'Sweden', 'NO' => 'Norway', 'DK' => 'Denmark', 'FI' => 'Finland',
                        'PL' => 'Poland', 'KR' => 'South Korea', 'MX' => 'Mexico', 'AR' => 'Argentina',
                        'ZA' => 'South Africa', 'EG' => 'Egypt', 'AE' => 'United Arab Emirates',
                        'TR' => 'Turkey', 'ID' => 'Indonesia', 'TH' => 'Thailand', 'VN' => 'Vietnam',
                        'PH' => 'Philippines', 'MY' => 'Malaysia', 'SG' => 'Singapore', 'NZ' => 'New Zealand',
                        'IE' => 'Ireland', 'CH' => 'Switzerland', 'AT' => 'Austria', 'BE' => 'Belgium',
                        'PT' => 'Portugal', 'GR' => 'Greece', 'CZ' => 'Czech Republic', 'HU' => 'Hungary',
                        'RO' => 'Romania', 'BG' => 'Bulgaria', 'HR' => 'Croatia', 'SK' => 'Slovakia',
                        'SI' => 'Slovenia', 'LT' => 'Lithuania', 'LV' => 'Latvia', 'EE' => 'Estonia',
                        'IS' => 'Iceland', 'LU' => 'Luxembourg', 'MT' => 'Malta', 'CY' => 'Cyprus',
                        'KW' => 'Kuwait', 'QA' => 'Qatar', 'BH' => 'Bahrain', 'OM' => 'Oman',
                        'JO' => 'Jordan', 'LB' => 'Lebanon', 'IQ' => 'Iraq', 'SY' => 'Syria',
                        'YE' => 'Yemen', 'PK' => 'Pakistan', 'BD' => 'Bangladesh', 'LK' => 'Sri Lanka',
                        'NP' => 'Nepal', 'AF' => 'Afghanistan', 'IR' => 'Iran', 'IL' => 'Israel',
                        'PS' => 'Palestine', 'LOCAL' => 'Local Network',
                    ];
                    $countryName = $countryNames[strtoupper($rule->country_code)] ?? $rule->country_code;
                @endphp
                <tr>
                    <td><span class="fw-type-badge country">Country</span></td>
                    <td>
                        @if($rule->site_id)
                            <span class="soc-pill soc-open">{{ $rule->site->name }}</span>
                        @else
                            <span class="soc-pill soc-open">Global</span>
                        @endif
                    </td>
                    <td>
                        <strong>{{ strtoupper($rule->country_code) }}</strong>
                        <span style="color:#9ca3af;font-size:11px;margin-left:8px;">{{ $countryName }}</span>
                    </td>
                    <td>
                        @if ($rule->type === 'allow')
                            <span class="soc-pill fw-pill-allow">Allow</span>
                        @else
                            <span class="soc-pill fw-pill-block">Block</span>
                        @endif
                    </td>
                    <td class="fw-mono-muted">{{ $rule->created_at->format('Y-m-d H:i:s') }}</td>
                    <td>
                        <form method="POST" action="{{ route('firewall.destroy', $rule->id) }}"
                              onsubmit="return confirm('Are you sure you want to delete this rule?');" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <input type="hidden" name="rule_type" value="country">
                            <button type="submit" class="btn btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach

            @if($ipRules->isEmpty() && $urlRules->isEmpty() && $countryRules->isEmpty())
                <tr>
                    <td colspan="6" class="fw-empty">No rules for this scope. Add a rule using the composer above.</td>
                </tr>
            @endif
            </tbody>
        </table>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function switchTab(tabName, clickedButton = null) {
        document.querySelectorAll('.tab-content').forEach(tab => tab.classList.remove('active'));
        document.querySelectorAll('.rule-tab').forEach(tab => tab.classList.remove('active'));
        document.getElementById('tab-' + tabName).classList.add('active');
        if (clickedButton) clickedButton.classList.add('active');
    }

    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function() {
                const button = form.querySelector('button[type="submit"]');
                if (button) button.classList.add('loading');
            });
        });
    });
</script>
@endsection
