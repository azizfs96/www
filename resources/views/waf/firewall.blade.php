@extends('layouts.waf')

@section('title', 'Firewall Rules Management')

@section('styles')
<style>
    /* Radical redesign - Firewall UX */
    :root {
        --bg-dark: #0f1220;
        --bg-card: #151a2d;
        --bg-hover: #1b2138;
        --bg-active: #232c49;
        --border: #2b3557;
        --border-light: #3a4772;
        --border-hover: #546292;
        --text-primary: #f5f7ff;
        --text-secondary: #d9def0;
        --text-muted: #98a3c7;
        --primary: #7c8bff;
        --primary-hover: #9aa6ff;
        --primary-light: rgba(124, 139, 255, 0.2);
        --success: #38d39f;
        --success-light: rgba(56, 211, 159, 0.2);
        --error: #ff6b8a;
        --error-light: rgba(255, 107, 138, 0.18);
        --warning: #ffc857;
        --warning-light: rgba(255, 200, 87, 0.2);
        --info: #58a6ff;
        --info-light: rgba(88, 166, 255, 0.2);
    }

    .page-header {
        margin-bottom: 24px;
        direction: ltr;
        text-align: left;
        padding: 8px 2px 14px;
        border-bottom: 1px solid var(--border);
    }

    .page-header h1 {
        font-size: 28px;
        font-weight: 800;
        color: var(--text-primary);
        margin-bottom: 8px;
        letter-spacing: -0.3px;
        background: linear-gradient(135deg, #f7f9ff 0%, #aeb8ff 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .page-subtitle {
        font-size: 14px;
        color: var(--text-secondary);
        line-height: 1.7;
        max-width: 700px;
        font-weight: 400;
    }

    .alert {
        background: var(--success-light);
        border: 1px solid rgba(16, 185, 129, 0.3);
        border-left: 4px solid var(--success);
        border-radius: 8px;
        padding: 14px 18px;
        margin-bottom: 24px;
        color: var(--success);
        font-size: 14px;
        font-weight: 500;
        box-shadow: 0 2px 8px rgba(16, 185, 129, 0.1);
    }

    .alert-error {
        background: var(--error-light);
        border: 1px solid rgba(239, 68, 68, 0.3);
        border-left: 4px solid var(--error);
        color: var(--error);
        box-shadow: 0 2px 8px rgba(239, 68, 68, 0.1);
    }

    .card {
        background: var(--bg-card);
        border-radius: 16px;
        border: 1px solid var(--border);
        padding: 20px;
        margin-bottom: 24px;
        box-shadow: 0 18px 36px rgba(2, 6, 23, 0.4);
        transition: all 0.3s ease;
    }

    .card:hover {
        border-color: var(--border-hover);
        box-shadow: 0 6px 24px rgba(0, 0, 0, 0.6);
        transform: translateY(-2px);
    }

    /* Tabs */
    .rule-tabs {
        display: flex;
        gap: 6px;
        margin-bottom: 0;
        background: #10172a;
        padding: 8px;
        border-radius: 12px;
        border: 1px solid var(--border);
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.06);
    }
    .rule-tabs::before {
        content: 'Rule Type';
        color: var(--text-muted);
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .6px;
        text-transform: uppercase;
        margin: 0 10px 0 4px;
        display: inline-flex;
        align-items: center;
    }

    .rule-tab {
        padding: 10px 16px;
        background: transparent;
        border: none;
        border-radius: 10px;
        color: var(--text-muted);
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        cursor: pointer;
        transition: all 0.25s ease;
        position: relative;
    }

    .rule-tab:hover {
        color: var(--text-primary);
        background: var(--bg-hover);
    }

    .rule-tab.active {
        color: #ffffff;
        background: linear-gradient(135deg, rgba(124, 139, 255, 0.32), rgba(88, 166, 255, 0.22));
        border: 1px solid rgba(124, 139, 255, 0.45);
        box-shadow: 0 6px 18px rgba(124, 139, 255, 0.25);
    }

    .tab-content {
        display: none;
    }

    .tab-content.active {
        display: block;
    }

    /* Form Styles */
    .form-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 14px;
        align-items: end;
    }

    @media (max-width: 768px) {
        .form-row {
            grid-template-columns: 1fr;
        }
    }

    .form-group {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .form-group label {
        font-size: 12px;
        color: var(--text-muted);
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .form-group input,
    .form-group select,
    .form-group textarea {
        background: #0f1526;
        border-radius: 10px;
        border: 1px solid var(--border);
        color: var(--text-primary);
        font-size: 14px;
        padding: 11px 14px;
        transition: all 0.25s ease;
        font-weight: 400;
    }

    .form-group input:hover,
    .form-group select:hover,
    .form-group textarea:hover {
        border-color: var(--border-hover);
        background: var(--bg-hover);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
    }

    .form-group textarea {
        min-height: 90px;
        resize: vertical;
        font-family: inherit;
    }

    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: var(--primary);
        background: var(--bg-hover);
        box-shadow: 0 0 0 3px var(--primary-light);
    }

    .error-message {
        color: var(--error);
        font-size: 11px;
        margin-top: 4px;
    }

    .btn {
        border-radius: 6px;
        border: none;
        font-size: 12px;
        padding: 8px 14px;
        cursor: pointer;
        font-weight: 600;
        transition: all 0.2s;
        white-space: nowrap;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
    }

    .btn-primary {
        background: linear-gradient(135deg, #7c8bff 0%, #58a6ff 100%);
        color: white;
        border: 1px solid rgba(124, 139, 255, 0.6);
        box-shadow: 0 10px 22px rgba(88, 166, 255, 0.28);
        font-weight: 600;
    }

    .btn-primary:hover {
        background: var(--primary-hover);
        border-color: var(--primary-hover);
        box-shadow: 0 4px 16px rgba(139, 92, 246, 0.4);
        transform: translateY(-2px);
    }

    .btn-primary:active {
        transform: translateY(0);
        box-shadow: 0 2px 8px rgba(139, 92, 246, 0.3);
    }

    .btn-secondary {
        background: var(--bg-hover);
        color: var(--text-secondary);
        border: 1px solid var(--border);
        font-weight: 500;
    }

    .btn-secondary:hover {
        background: var(--bg-active);
        border-color: var(--border-hover);
        color: var(--text-primary);
        transform: translateY(-1px);
    }

    .btn-secondary.active {
        background: var(--primary-light);
        border-color: var(--primary);
        color: var(--primary);
    }

    .btn-danger {
        background: var(--error-light);
        color: var(--error);
        border: 1px solid rgba(239, 68, 68, 0.3);
        padding: 6px 14px;
        font-size: 12px;
        font-weight: 600;
        box-shadow: 0 2px 6px rgba(239, 68, 68, 0.2);
    }

    .btn-danger:hover {
        background: var(--error);
        color: white;
        border-color: var(--error);
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
        transform: translateY(-1px);
    }

    .btn-danger:active {
        transform: translateY(0);
    }

    /* Stats Grid */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }

    .stat-card {
        background: rgba(30, 30, 30, 0.6);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 14px;
        padding: 16px;
        text-align: center;
        box-shadow: 0 8px 22px rgba(2, 6, 23, 0.35);
        transition: all 0.2s;
    }

    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);
        border-color: rgba(255, 255, 255, 0.15);
    }

    .stat-value {
        font-size: 24px;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 4px;
    }

    .stat-label {
        font-size: 12px;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* Create Rule - clean structure */
    .rule-builder {
        display: grid;
        gap: 16px;
    }
    .fw-main-layout {
        display: grid;
        grid-template-columns: minmax(340px, 1.05fr) minmax(280px, .95fr);
        gap: 16px;
        margin-bottom: 20px;
    }
    @media (max-width: 1180px) {
        .fw-main-layout {
            grid-template-columns: 1fr;
        }
    }

    .builder-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
        padding-bottom: 14px;
        border-bottom: 1px solid var(--border);
    }

    .builder-title {
        font-size: 18px;
        font-weight: 700;
        color: var(--text-primary);
        margin: 0;
    }

    .builder-subtitle {
        font-size: 13px;
        color: var(--text-muted);
        margin-top: 4px;
        line-height: 1.5;
    }

    .builder-step-title {
        font-size: 11px;
        color: var(--text-muted);
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.7px;
        margin-bottom: 10px;
    }

    .site-tabs {
        padding: 14px;
        border: 1px solid var(--border);
        background: rgba(255, 255, 255, 0.02);
        border-radius: 10px;
    }

    .site-tabs .btn {
        font-size: 12px;
        padding: 8px 14px;
        border-radius: 6px;
        transition: all 0.2s ease;
        font-weight: 600;
    }

    .rule-tabs {
        margin-bottom: 0;
        border: 1px solid var(--border);
        background: rgba(255, 255, 255, 0.02);
        border-radius: 10px;
        padding: 6px;
    }

    .rule-tabs::before {
        content: none;
    }

    .tab-content {
        margin-top: 10px;
        background: rgba(255, 255, 255, 0.02);
        border: 1px solid var(--border);
        border-radius: 10px;
        padding: 16px;
    }

    .form-actions {
        display: flex;
        align-items: end;
    }

    .form-actions .btn {
        width: 100%;
        justify-content: center;
        padding: 11px 16px;
        font-size: 13px;
    }

    /* Table Styles */
    .table-container {
        background: var(--bg-card);
        border-radius: 16px;
        border: 1px solid var(--border);
        overflow: hidden;
        box-shadow: 0 16px 32px rgba(2, 6, 23, 0.4);
        margin-bottom: 24px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
    }

    thead {
        background: #10172a;
        border-bottom: 1px solid var(--border-hover);
    }

    th {
        padding: 14px 20px;
        text-align: left;
        font-size: 11px;
        font-weight: 700;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.8px;
    }

    tbody tr {
        border-bottom: 1px solid var(--border);
        transition: all 0.2s ease;
        background: transparent;
    }

    tbody tr:nth-child(even) {
        background: rgba(255, 255, 255, 0.02);
    }

    tbody tr:hover {
        background: var(--bg-hover);
        border-left: 3px solid var(--primary);
    }

    tbody tr:last-child {
        border-bottom: none;
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

    .pill {
        display: inline-flex;
        align-items: center;
        padding: 5px 12px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 600;
        border: 1px solid;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .pill-allow {
        background: var(--success-light);
        color: var(--success);
        border-color: rgba(16, 185, 129, 0.4);
    }

    .pill-block {
        background: var(--error-light);
        color: var(--error);
        border-color: rgba(239, 68, 68, 0.4);
    }

    .badge {
        display: inline-flex;
        align-items: center;
        padding: 5px 12px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 600;
        border: 1px solid;
    }

    .badge-info {
        background: var(--info-light);
        color: var(--info);
        border-color: rgba(59, 130, 246, 0.4);
    }

    .badge-secondary {
        background: var(--bg-hover);
        color: var(--text-secondary);
        border-color: var(--border);
    }

    .badge-success {
        background: var(--success-light);
        color: var(--success);
        border-color: rgba(16, 185, 129, 0.4);
    }

    .text-muted {
        color: var(--text-muted);
        font-size: 12px;
        font-family: 'Courier New', monospace;
    }

    .empty-state {
        text-align: center;
        padding: 48px 24px;
        color: var(--text-muted);
        font-size: 13px;
    }

    td strong {
        font-weight: 600;
        color: var(--text-primary);
        font-family: 'Courier New', monospace;
    }

    td code {
        background: rgba(157, 78, 221, 0.1);
        border: 1px solid rgba(157, 78, 221, 0.2);
        padding: 4px 8px;
        border-radius: 4px;
        font-family: 'Courier New', monospace;
        font-size: 12px;
        color: var(--primary);
    }

    .rule-type-badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        margin-right: 10px;
        letter-spacing: 0.5px;
        border: 1px solid;
    }

    .rule-type-badge.ip {
        background: var(--info-light);
        color: var(--info);
        border-color: rgba(59, 130, 246, 0.4);
    }

    .rule-type-badge.url {
        background: var(--primary-light);
        color: var(--primary);
        border-color: rgba(139, 92, 246, 0.4);
    }

    .rule-type-badge.country {
        background: var(--warning-light);
        color: var(--warning);
        border-color: rgba(245, 158, 11, 0.4);
    }
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

<div class="page-header">
    <h1>Firewall Rules</h1>
    <p class="page-subtitle">
        Build and manage firewall policy from one control center.
    </p>
</div>

@if (session('status'))
    <div class="alert">
        {{ session('status') }}
    </div>
@endif

@if ($errors->any())
    <div class="alert alert-error">
        @foreach ($errors->all() as $error)
            {{ $error }}<br>
        @endforeach
    </div>
@endif

<div class="fw-main-layout">
    <div class="card" style="margin-bottom:0;">
        <div style="display:flex; align-items:center; justify-content:space-between; gap:10px; margin-bottom:14px; border-bottom:1px solid var(--border); padding-bottom:12px;">
            <div>
                <h2 style="font-size:18px; font-weight:800; margin:0; color:var(--text-primary);">Rule Composer</h2>
                <div style="font-size:12px; color:var(--text-muted); margin-top:4px;">Create and apply security rules quickly.</div>
            </div>
            <span class="badge badge-info" style="font-size:11px;">Scope: {{ $currentScope }}</span>
        </div>

        <div style="display:grid; gap:12px;">
            <div style="padding:12px; border:1px solid var(--border); border-radius:12px; background:rgba(255,255,255,.02);">
                <div class="builder-step-title" style="margin-bottom:8px;">Scope</div>
                <div style="display:flex; gap:8px; flex-wrap:wrap; align-items:center;">
                    @if(auth()->user()->isSuperAdmin())
                        <a href="{{ route('firewall.index', ['site_id' => 'global']) }}" class="btn {{ $siteId === 'global' ? 'btn-primary' : 'btn-secondary' }}">Global</a>
                    @endif
                    <a href="{{ route('firewall.index', ['site_id' => 'all']) }}" class="btn {{ $siteId === 'all' ? 'btn-primary' : 'btn-secondary' }}">All Sites</a>
                    @foreach($sites as $s)
                        <a href="{{ route('firewall.index', ['site_id' => $s->id]) }}" class="btn {{ $siteId == $s->id ? 'btn-primary' : 'btn-secondary' }}">{{ $s->name }}</a>
                    @endforeach
                </div>
            </div>

            <div style="padding:12px; border:1px solid var(--border); border-radius:12px; background:rgba(255,255,255,.02);">
                <div class="builder-step-title" style="margin-bottom:8px;">Rule Type</div>
                <div class="rule-tabs">
                    <button type="button" class="rule-tab active" onclick="switchTab('ip', this)">IP</button>
                    <button type="button" class="rule-tab" onclick="switchTab('url', this)">URL</button>
                    <button type="button" class="rule-tab" onclick="switchTab('country', this)">Country</button>
                </div>
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
                        <div class="form-group"><label>Host</label><input type="text" name="host" placeholder="e.g., rabbitclean.sa"></div>
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

    <div class="card" style="margin-bottom:0;">
        <h2 style="font-size:16px; font-weight:800; margin:0 0 14px; color:var(--text-primary);">Policy Snapshot</h2>
        <div class="stats-grid" style="grid-template-columns:1fr 1fr; margin-bottom:0;">
            <div class="stat-card"><div class="stat-value">{{ $totalRules }}</div><div class="stat-label">Total</div></div>
            <div class="stat-card"><div class="stat-value">{{ $ipCount }}</div><div class="stat-label">IP</div></div>
            <div class="stat-card"><div class="stat-value">{{ $urlCount }}</div><div class="stat-label">URL</div></div>
            <div class="stat-card"><div class="stat-value">{{ $countryCount }}</div><div class="stat-label">Country</div></div>
        </div>
    </div>
</div>

{{-- All Rules Table --}}
<div style="display:flex; justify-content:space-between; align-items:center; gap:12px; margin-bottom:10px; flex-wrap:wrap;">
    <h2 style="font-size:16px; color:var(--text-primary); font-weight:700; margin:0;">Rules List</h2>
    <span style="font-size:12px; color:var(--text-muted);">Showing {{ $totalRules }} rule(s)</span>
</div>
<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>Type</th>
                <th>Site</th>
                <th>Details</th>
                <th>Action</th>
                <th>Created At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        {{-- IP Rules --}}
        @foreach ($ipRules as $rule)
            <tr>
                <td><span class="rule-type-badge ip">IP</span></td>
                <td>
                    @if($rule->site_id)
                        <span class="badge badge-info">🌐 {{ $rule->site->name }}</span>
                    @else
                        <span class="badge badge-secondary">🌍 Global</span>
                    @endif
                </td>
                <td><strong>{{ $rule->ip }}</strong></td>
                <td>
                    @if ($rule->type === 'allow')
                        <span class="pill pill-allow">Whitelist</span>
                    @else
                        <span class="pill pill-block">Blacklist</span>
                    @endif
                </td>
                <td class="text-muted">{{ $rule->created_at->format('Y-m-d H:i:s') }}</td>
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

        {{-- URL Rules --}}
        @foreach ($urlRules as $rule)
            <tr>
                <td><span class="rule-type-badge url">URL</span></td>
                <td>
                    @if($rule->site_id)
                        <span class="badge badge-info">🌐 {{ $rule->site->name }}</span>
                    @elseif($rule->host)
                        <code style="background: rgba(96, 165, 250, 0.1); border-color: rgba(96, 165, 250, 0.2); color: var(--info);">
                            {{ $rule->host }}
                        </code>
                    @else
                        <span class="badge badge-secondary">🌍 Global</span>
                    @endif
                </td>
                <td>
                    <div><strong>{{ $rule->name ?? '-' }}</strong></div>
                    <div style="margin-top: 4px;"><code>{{ $rule->path }}</code></div>
                    <div style="margin-top: 4px; font-size: 11px; color: var(--text-muted);">
                        IPs: {{ $rule->allowed_ips }}
                    </div>
                </td>
                <td>
                    @if($rule->enabled)
                        <span class="badge badge-success">Enabled</span>
                    @else
                        <span class="badge badge-secondary">Disabled</span>
                    @endif
                </td>
                <td class="text-muted">{{ $rule->created_at->format('Y-m-d H:i:s') }}</td>
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

        {{-- Country Rules --}}
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
                <td><span class="rule-type-badge country">Country</span></td>
                <td>
                    @if($rule->site_id)
                        <span class="badge badge-info">🌐 {{ $rule->site->name }}</span>
                    @else
                        <span class="badge badge-secondary">🌍 Global</span>
                    @endif
                </td>
                <td>
                    <strong>{{ strtoupper($rule->country_code) }}</strong>
                    <span style="color: var(--text-muted); font-size: 11px; margin-left: 8px;">{{ $countryName }}</span>
                </td>
                <td>
                    @if ($rule->type === 'allow')
                        <span class="pill pill-allow">Allow</span>
                    @else
                        <span class="pill pill-block">Block</span>
                    @endif
                </td>
                <td class="text-muted">{{ $rule->created_at->format('Y-m-d H:i:s') }}</td>
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
                <td colspan="6" class="empty-state">
                    No rules currently. You can add rules using the forms above.
                </td>
            </tr>
        @endif
        </tbody>
    </table>
</div>
@endsection

@section('scripts')
<script>
    function switchTab(tabName, clickedButton = null) {
        // Hide all tabs
        document.querySelectorAll('.tab-content').forEach(tab => {
            tab.classList.remove('active');
        });
        document.querySelectorAll('.rule-tab').forEach(tab => {
            tab.classList.remove('active');
        });

        // Show selected tab
        document.getElementById('tab-' + tabName).classList.add('active');
        if (clickedButton) {
            clickedButton.classList.add('active');
        }
    }

    // Add loading state to forms
    document.addEventListener('DOMContentLoaded', function() {
        const forms = document.querySelectorAll('form');
        forms.forEach(form => {
            form.addEventListener('submit', function(e) {
                const button = form.querySelector('button[type="submit"]');
                if (button) {
                    button.classList.add('loading');
                }
            });
        });
    });
</script>
@endsection

