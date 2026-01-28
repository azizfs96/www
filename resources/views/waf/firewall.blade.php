@extends('layouts.waf')

@section('title', 'Firewall Rules Management')

@section('styles')
<style>
    /* Enhanced Dark Design - Firewall Page */
    :root {
        --bg-dark: #1E1E1E;
        --bg-card: #252525;
        --bg-hover: #2D2D2D;
        --bg-active: #353535;
        --border: #404040;
        --border-light: #4A4A4A;
        --border-hover: #555555;
        --text-primary: #FFFFFF;
        --text-secondary: #E5E5E5;
        --text-muted: #B0B0B0;
        --primary: #8B5CF6;
        --primary-hover: #A78BFA;
        --primary-light: rgba(139, 92, 246, 0.2);
        --success: #10B981;
        --success-light: rgba(16, 185, 129, 0.2);
        --error: #EF4444;
        --error-light: rgba(239, 68, 68, 0.2);
        --warning: #F59E0B;
        --warning-light: rgba(245, 158, 11, 0.2);
        --info: #3B82F6;
        --info-light: rgba(59, 130, 246, 0.2);
    }

    .page-header {
        margin-bottom: 32px;
        direction: ltr;
        text-align: left;
    }

    .page-header h1 {
        font-size: 32px;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 12px;
        letter-spacing: -0.5px;
        background: linear-gradient(135deg, var(--text-primary) 0%, var(--text-secondary) 100%);
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
        border-radius: 12px;
        border: 1px solid var(--border);
        padding: 28px;
        margin-bottom: 24px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.5);
        transition: all 0.3s ease;
    }

    .card:hover {
        border-color: var(--border-hover);
        box-shadow: 0 6px 24px rgba(0, 0, 0, 0.6);
        transform: translateY(-2px);
    }

    /* Tabs - Professional UX Design */
    .rule-tabs {
        display: flex;
        gap: 6px;
        margin-bottom: 28px;
        padding: 8px;
        background: #2A2A2A;
        border-radius: 10px;
        border: 2px solid #3A3A3A;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.4);
    }

    .rule-tab {
        flex: 1;
        padding: 12px 20px;
        background: transparent;
        border: 1px solid transparent;
        border-radius: 8px;
        color: var(--text-muted);
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        text-align: center;
    }

    .rule-tab:hover {
        color: var(--text-secondary);
        background: rgba(255, 255, 255, 0.05);
        border-color: #4A4A4A;
    }

    .rule-tab.active {
        color: var(--text-primary);
        background: #353535;
        border: 2px solid #555555;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.5);
        font-weight: 700;
    }

    .rule-tab.active::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 3px;
        background: #FFFFFF;
        border-radius: 8px 0 0 8px;
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
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 16px;
        align-items: start;
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
        min-height: 100%;
    }

    .form-group label {
        font-size: 12px;
        color: var(--text-muted);
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        min-height: 18px;
        display: flex;
        align-items: center;
    }

    .form-group input,
    .form-group select,
    .form-group textarea {
        background: var(--bg-dark);
        border-radius: 8px;
        border: 1px solid var(--border);
        color: var(--text-primary);
        font-size: 14px;
        padding: 12px 16px;
        transition: all 0.25s ease;
        font-weight: 400;
        width: 100%;
        min-height: 44px;
        box-sizing: border-box;
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
        line-height: 1.5;
    }

    .form-group small {
        color: var(--text-muted);
        font-size: 11px;
        margin-top: 4px;
        line-height: 1.4;
        min-height: 15px;
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
        background: var(--primary);
        color: white;
        border: 1px solid var(--primary);
        box-shadow: 0 2px 8px rgba(139, 92, 246, 0.3);
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
        border-radius: 8px;
        padding: 16px;
        text-align: center;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
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

    /* Site Tabs */
    .site-tabs {
        margin-bottom: 28px;
        padding-bottom: 20px;
        border-bottom: 2px solid var(--border);
        background: rgba(255, 255, 255, 0.03);
        padding: 16px;
        border-radius: 8px;
        margin-left: -28px;
        margin-right: -28px;
        margin-top: -28px;
        margin-bottom: 24px;
    }

    .site-tabs label {
        display: block;
        font-size: 11px;
        color: var(--text-secondary);
        text-transform: uppercase;
        letter-spacing: 1px;
        font-weight: 700;
        margin-bottom: 14px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .site-tabs label::before {
        content: '📍';
        font-size: 14px;
    }

    .site-tabs .btn {
        font-size: 12px;
        padding: 8px 16px;
        border-radius: 6px;
        transition: all 0.2s ease;
        font-weight: 600;
    }

    /* Table Styles - Professional CRM Design */
    .table-container {
        background: #1A1A1A;
        border-radius: 0;
        border: 1px solid #2A2A2A;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
        margin-bottom: 24px;
    }

    table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        font-size: 13px;
    }

    thead {
        background: #1A1A1A;
        border-bottom: 1px solid #2A2A2A;
    }

    th {
        padding: 12px 16px;
        text-align: left;
        font-size: 11px;
        font-weight: 600;
        color: #9CA3AF;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 1px solid #2A2A2A;
        white-space: nowrap;
    }

    th:first-child {
        padding-left: 20px;
    }

    th:last-child {
        padding-right: 20px;
    }

    tbody tr {
        border-bottom: 1px solid #2A2A2A;
        transition: all 0.15s ease;
        background: #1A1A1A;
    }

    tbody tr:nth-child(even) {
        background: #1E1E1E;
    }

    tbody tr:hover {
        background: #252525;
    }

    tbody tr:last-child {
        border-bottom: none;
    }

    td {
        padding: 14px 16px;
        text-align: left;
        color: #FFFFFF;
        font-size: 13px;
        vertical-align: middle;
    }

    td:first-child {
        padding-left: 20px;
    }

    td:last-child {
        padding-right: 20px;
    }

    .pill {
        display: inline-flex;
        align-items: center;
        padding: 4px 10px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 600;
        border: none;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .pill-allow {
        background: rgba(16, 185, 129, 0.15);
        color: #10B981;
    }

    .pill-block {
        background: rgba(239, 68, 68, 0.15);
        color: #EF4444;
    }

    .badge {
        display: inline-flex;
        align-items: center;
        padding: 4px 10px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 600;
        border: none;
    }

    .badge-info {
        background: rgba(59, 130, 246, 0.15);
        color: #3B82F6;
    }

    .badge-secondary {
        background: rgba(255, 255, 255, 0.1);
        color: #D1D5DB;
    }

    .badge-success {
        background: rgba(16, 185, 129, 0.15);
        color: #10B981;
    }

    .text-muted {
        color: #9CA3AF;
        font-size: 12px;
        font-family: system-ui, -apple-system, sans-serif;
    }

    /* Usage Percentage & Bar Chart */
    .usage-cell {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .usage-percentage {
        font-size: 13px;
        font-weight: 600;
        color: #FFFFFF;
        min-width: 40px;
    }

    .usage-bars {
        display: flex;
        gap: 2px;
        align-items: flex-end;
        height: 14px; /* أقصى ارتفاع للأعمدة */
    }

    .usage-bar {
        width: 3px;
        border-radius: 1px;
        transition: height 0.2s ease, background-color 0.2s ease, opacity 0.2s ease;
        background: rgba(16, 185, 129, 0.18);
    }

    .usage-bar.active {
        background: #10B981;
        opacity: 1;
    }

    .usage-bar.inactive {
        opacity: 0.35;
    }

    .empty-state {
        text-align: center;
        padding: 48px 24px;
        color: var(--text-muted);
        font-size: 13px;
    }

    td strong {
        font-weight: 600;
        color: #FFFFFF;
        font-family: system-ui, -apple-system, sans-serif;
    }

    td code {
        background: rgba(255, 255, 255, 0.08);
        border: none;
        padding: 3px 8px;
        border-radius: 4px;
        font-family: 'Courier New', monospace;
        font-size: 12px;
        color: #E5E7EB;
    }

    .rule-type-badge {
        display: inline-block;
        padding: 3px 8px;
        border-radius: 4px;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        margin-right: 8px;
        letter-spacing: 0.5px;
        border: none;
    }

    .rule-type-badge.ip {
        background: rgba(59, 130, 246, 0.2);
        color: #60A5FA;
    }

    .rule-type-badge.url {
        background: rgba(139, 92, 246, 0.2);
        color: #A78BFA;
    }

    .rule-type-badge.country {
        background: rgba(245, 158, 11, 0.2);
        color: #FBBF24;
    }
</style>
@endsection

@section('content')
<div class="page-header">
    <h1>Firewall Rules</h1>
    <p class="page-subtitle">
        Manage all security rules: IP Rules, URL Rules, and Country Rules in one place. Select the rule type when creating a new rule.
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

{{-- Site Filter Tabs --}}
<div class="card">
    <div class="site-tabs">
        <label>Filter by Site</label>
        <div style="display: flex; gap: 8px; flex-wrap: wrap; align-items: center;">
            @if(auth()->user()->isSuperAdmin())
                <a href="{{ route('firewall.index', ['site_id' => 'global']) }}" 
                   class="btn {{ $siteId === 'global' ? 'btn-primary' : 'btn-secondary' }}">
                    Global Rules
                </a>
            @endif
            <a href="{{ route('firewall.index', ['site_id' => 'all']) }}" 
               class="btn {{ $siteId === 'all' ? 'btn-primary' : 'btn-secondary' }}">
                All Rules
            </a>
            @foreach($sites as $s)
                <a href="{{ route('firewall.index', ['site_id' => $s->id]) }}" 
                   class="btn {{ $siteId == $s->id ? 'btn-primary' : 'btn-secondary' }}">
                    {{ $s->name }}
                </a>
            @endforeach
        </div>
    </div>

    {{-- Rule Type Tabs --}}
    <div class="rule-tabs">
        <button type="button" class="rule-tab active" onclick="switchTab('ip')">IP Rules</button>
        <button type="button" class="rule-tab" onclick="switchTab('url')">URL Rules</button>
        <button type="button" class="rule-tab" onclick="switchTab('country')">Country Rules</button>
    </div>

    {{-- IP Rules Form --}}
    <div id="tab-ip" class="tab-content active">
        <form method="POST" action="{{ route('firewall.store') }}" id="ip-form">
            @csrf
            <input type="hidden" name="rule_type" value="ip">
            <input type="hidden" name="site_id" value="{{ $siteId !== 'global' && $siteId !== 'all' ? $siteId : '' }}">
            <div class="form-row">
                @if($siteId === 'global' || $siteId === 'all')
                <div class="form-group">
                    <label>Site (Domain)</label>
                    <select name="site_id">
                        @if(auth()->user()->isSuperAdmin())
                            <option value="">Global (All Sites)</option>
                        @endif
                        @foreach($sites as $site)
                            <option value="{{ $site->id }}">{{ $site->name }} ({{ $site->server_name }})</option>
                        @endforeach
                    </select>
                    <small style="color: var(--text-muted); font-size: 11px; margin-top: 4px;">Select a specific site or leave it global</small>
                    @error('site_id')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
                @endif

                <div class="form-group">
                    <label>IP Address</label>
                    <input type="text" name="ip" placeholder="e.g., 137.59.230.231" required>
                    @error('ip')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label>Type</label>
                    <select name="type" required>
                        <option value="allow">Whitelist (Allow)</option>
                        <option value="block">Blacklist (Block)</option>
                    </select>
                    @error('type')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group" style="display: flex; flex-direction: column; justify-content: flex-start;">
                    <label style="visibility: hidden; height: 0; margin: 0; padding: 0;">Action</label>
                    <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 12px 16px; font-size: 14px; min-height: 44px; margin-top: 0;">Add IP Rule</button>
                </div>
            </div>
        </form>
    </div>

    {{-- URL Rules Form --}}
    <div id="tab-url" class="tab-content">
        <form method="POST" action="{{ route('firewall.store') }}" id="url-form">
            @csrf
            <input type="hidden" name="rule_type" value="url">
            <input type="hidden" name="site_id" value="{{ $siteId !== 'global' && $siteId !== 'all' ? $siteId : '' }}">
            <div class="form-row">
                @if($siteId === 'global' || $siteId === 'all')
                <div class="form-group">
                    <label>Site (Optional)</label>
                    <select name="site_id">
                        @if(auth()->user()->isSuperAdmin())
                            <option value="">Global (All Sites)</option>
                        @endif
                        @foreach($sites as $site)
                            <option value="{{ $site->id }}">{{ $site->name }} ({{ $site->server_name }})</option>
                        @endforeach
                    </select>
                    @error('site_id')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
                @endif

                <div class="form-group">
                    <label>Name (Optional)</label>
                    <input type="text" name="name" placeholder="e.g., Admin Panel Access">
                    @error('name')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label>Host (Optional)</label>
                    <input type="text" name="host" placeholder="e.g., rabbitclean.sa">
                    @error('host')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label>Path</label>
                    <input type="text" name="path" placeholder="e.g., /admin" required>
                    @error('path')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label>Allowed IPs</label>
                    <textarea name="allowed_ips" placeholder="e.g., 192.168.1.1, 10.0.0.1" required></textarea>
                    @error('allowed_ips')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group" style="display: flex; flex-direction: column; justify-content: flex-start;">
                    <label style="visibility: hidden; height: 0; margin: 0; padding: 0;">Action</label>
                    <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 12px 16px; font-size: 14px; min-height: 44px; margin-top: 0;">Add URL Rule</button>
                </div>
            </div>
        </form>
    </div>

    {{-- Country Rules Form --}}
    <div id="tab-country" class="tab-content">
        <form method="POST" action="{{ route('firewall.store') }}" id="country-form">
            @csrf
            <input type="hidden" name="rule_type" value="country">
            <input type="hidden" name="site_id" value="{{ $siteId !== 'global' && $siteId !== 'all' ? $siteId : '' }}">
            <div class="form-row">
                @if($siteId === 'global' || $siteId === 'all')
                <div class="form-group">
                    <label>Site (Optional)</label>
                    <select name="site_id">
                        @if(auth()->user()->isSuperAdmin())
                            <option value="">Global (All Sites)</option>
                        @endif
                        @foreach($sites as $site)
                            <option value="{{ $site->id }}">{{ $site->name }} ({{ $site->server_name }})</option>
                        @endforeach
                    </select>
                    @error('site_id')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
                @endif

                <div class="form-group">
                    <label>Country Code</label>
                    <input type="text" name="country_code" placeholder="e.g., SA, US, CN" maxlength="2" required style="text-transform: uppercase;">
                    <small style="color: var(--text-muted); font-size: 11px; margin-top: 4px;">Enter 2-letter country code (ISO 3166-1 alpha-2)</small>
                    @error('country_code')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label>Type</label>
                    <select name="type" required>
                        <option value="block">Block (Deny)</option>
                        <option value="allow">Allow (Whitelist)</option>
                    </select>
                    @error('type')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group" style="display: flex; flex-direction: column; justify-content: flex-start;">
                    <label style="visibility: hidden; height: 0; margin: 0; padding: 0;">Action</label>
                    <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 12px 16px; font-size: 14px; min-height: 44px; margin-top: 0;">Add Country Rule</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- All Rules Table --}}
<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>Type</th>
                <th>Site</th>
                <th>Details</th>
                <th>Action</th>
                <th>Usage</th>
                <th>Created At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        {{-- IP Rules --}}
        @foreach ($ipRules as $rule)
            @php
                $usagePercent = $rule->usage_percentage ?? 0;
                $maxBars = 10;
                $pattern = [5, 7, 10, 13, 9, 12, 8, 11, 6, 10]; // شكل الشارت
                $scale = max(0.15, ($usagePercent / 100));      // لا يكون صغير جداً
            @endphp
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
                <td>
                    <div class="usage-cell">
                        <span class="usage-percentage">{{ $usagePercent }}%</span>
                        <div class="usage-bars">
                            @for($i = 0; $i < $maxBars; $i++)
                                @php
                                    $base = $pattern[$i % count($pattern)];
                                    $height = max(2, round($base * $scale)); // ارتفاع العمود
                                    $isActive = $usagePercent > 0;
                                @endphp
                                <div
                                    class="usage-bar {{ $isActive ? 'active' : 'inactive' }}"
                                    style="height: {{ $height }}px;"
                                ></div>
                            @endfor
                        </div>
                    </div>
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
            @php
                $usagePercent = $rule->usage_percentage ?? 0;
                $maxBars = 10;
                $pattern = [5, 7, 10, 13, 9, 12, 8, 11, 6, 10];
                $scale = max(0.15, ($usagePercent / 100));
            @endphp
            <tr>
                <td><span class="rule-type-badge url">URL</span></td>
                <td>
                    @if($rule->site_id)
                        <span class="badge badge-info">🌐 {{ $rule->site->name }}</span>
                    @elseif($rule->host)
                        <code>{{ $rule->host }}</code>
                    @else
                        <span class="badge badge-secondary">🌍 Global</span>
                    @endif
                </td>
                <td>
                    <div><strong>{{ $rule->name ?? '-' }}</strong></div>
                    <div style="margin-top: 4px;"><code>{{ $rule->path }}</code></div>
                    <div style="margin-top: 4px; font-size: 11px; color: #9CA3AF;">
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
                <td>
                    <div class="usage-cell">
                        <span class="usage-percentage">{{ $usagePercent }}%</span>
                        <div class="usage-bars">
                            @for($i = 0; $i < $maxBars; $i++)
                                @php
                                    $base = $pattern[$i % count($pattern)];
                                    $height = max(2, round($base * $scale));
                                    $isActive = $usagePercent > 0;
                                @endphp
                                <div
                                    class="usage-bar {{ $isActive ? 'active' : 'inactive' }}"
                                    style="height: {{ $height }}px;"
                                ></div>
                            @endfor
                        </div>
                    </div>
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
                $usagePercent = $rule->usage_percentage ?? 0;
                $maxBars = 10;
                $pattern = [5, 7, 10, 13, 9, 12, 8, 11, 6, 10];
                $scale = max(0.15, ($usagePercent / 100));
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
                    <span style="color: #9CA3AF; font-size: 11px; margin-left: 8px;">{{ $countryName }}</span>
                </td>
                <td>
                    @if ($rule->type === 'allow')
                        <span class="pill pill-allow">Allow</span>
                    @else
                        <span class="pill pill-block">Block</span>
                    @endif
                </td>
                <td>
                    <div class="usage-cell">
                        <span class="usage-percentage">{{ $usagePercent }}%</span>
                        <div class="usage-bars">
                            @for($i = 0; $i < $maxBars; $i++)
                                @php
                                    $base = $pattern[$i % count($pattern)];
                                    $height = max(2, round($base * $scale));
                                    $isActive = $usagePercent > 0;
                                @endphp
                                <div
                                    class="usage-bar {{ $isActive ? 'active' : 'inactive' }}"
                                    style="height: {{ $height }}px;"
                                ></div>
                            @endfor
                        </div>
                    </div>
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
    function switchTab(tabName) {
        // Hide all tabs
        document.querySelectorAll('.tab-content').forEach(tab => {
            tab.classList.remove('active');
        });
        document.querySelectorAll('.rule-tab').forEach(tab => {
            tab.classList.remove('active');
        });

        // Show selected tab
        document.getElementById('tab-' + tabName).classList.add('active');
        event.target.classList.add('active');
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

