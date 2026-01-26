@extends('layouts.waf')

@section('title', 'IP Rules Management')

@section('styles')
<style>
    /* Clean Dark Design - IP Rules Page */
    :root {
        --bg-dark: #1A1A1A;
        --bg-card: #1E1E1E;
        --bg-hover: #2A2A2A;
        --border: #333333;
        --border-light: #404040;
        --text-primary: #E5E5E5;
        --text-secondary: #B3B3B3;
        --text-muted: #808080;
        --primary: #9D4EDD;
        --primary-hover: #B06FE8;
        --success: #4ADE80;
        --error: #F87171;
        --warning: #FBBF24;
        --info: #60A5FA;
    }

    .page-header {
        margin-bottom: 32px;
        direction: ltr;
        text-align: left;
    }

    .page-header h1 {
        font-size: 32px;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 12px;
        letter-spacing: -0.5px;
    }

    .page-subtitle {
        font-size: 14px;
        color: var(--text-secondary);
        line-height: 1.6;
        max-width: 700px;
    }

    .alert {
        background: rgba(74, 222, 128, 0.1);
        border: 1px solid rgba(74, 222, 128, 0.3);
        border-radius: 8px;
        padding: 12px 16px;
        margin-bottom: 20px;
        color: var(--success);
        font-size: 13px;
    }

    .card {
        background: rgba(30, 30, 30, 0.6);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border-radius: 12px;
        border: 1px solid rgba(255, 255, 255, 0.1);
        padding: 24px;
        margin-bottom: 24px;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.2);
    }

    .form-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 16px;
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
    .form-group select {
        background: rgba(26, 26, 26, 0.6);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        border-radius: 8px;
        border: 1px solid rgba(255, 255, 255, 0.1);
        color: var(--text-primary);
        font-size: 13px;
        padding: 11px 16px;
        transition: all 0.2s;
    }

    .form-group input:focus,
    .form-group select:focus {
        outline: none;
        border-color: var(--primary);
        background: rgba(26, 26, 26, 0.8);
        box-shadow: 0 0 0 3px rgba(157, 78, 221, 0.1);
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

    .site-tabs .btn {
        font-size: 12px;
        padding: 8px 14px;
        border-radius: 6px;
    }

    .btn-primary {
        background: rgba(157, 78, 221, 0.2);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        color: var(--primary);
        border: 1px solid rgba(157, 78, 221, 0.3);
        box-shadow: 0 2px 8px rgba(157, 78, 221, 0.15);
    }

    .btn-primary:hover {
        background: rgba(157, 78, 221, 0.3);
        border-color: rgba(157, 78, 221, 0.4);
        box-shadow: 0 4px 12px rgba(157, 78, 221, 0.25);
        transform: translateY(-1px);
    }

    .btn-secondary {
        background: rgba(42, 42, 42, 0.4);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        color: var(--text-primary);
        border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .btn-secondary:hover {
        background: rgba(42, 42, 42, 0.6);
        border-color: rgba(255, 255, 255, 0.15);
        transform: translateY(-1px);
    }

    .btn-danger {
        background: rgba(248, 113, 113, 0.2);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        color: var(--error);
        border: 1px solid rgba(248, 113, 113, 0.3);
        padding: 6px 12px;
        font-size: 12px;
        box-shadow: 0 2px 8px rgba(248, 113, 113, 0.15);
    }

    .btn-danger:hover {
        background: rgba(248, 113, 113, 0.3);
        border-color: rgba(248, 113, 113, 0.4);
        box-shadow: 0 4px 12px rgba(248, 113, 113, 0.25);
        transform: translateY(-1px);
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

    /* Search/Filter Section */
    .search-filter-section {
        background: rgba(30, 30, 30, 0.6);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 8px;
        padding: 16px;
        margin-bottom: 24px;
        display: flex;
        gap: 12px;
        align-items: center;
        flex-wrap: wrap;
    }

    .search-input {
        flex: 1;
        min-width: 200px;
        padding: 10px 16px;
        background: rgba(26, 26, 26, 0.6);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 6px;
        color: var(--text-primary);
        font-size: 13px;
        transition: all 0.2s;
    }

    .search-input:focus {
        outline: none;
        border-color: var(--primary);
        background: rgba(26, 26, 26, 0.8);
        box-shadow: 0 0 0 3px rgba(157, 78, 221, 0.1);
    }

    .filter-select {
        padding: 10px 16px;
        background: rgba(26, 26, 26, 0.6);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 6px;
        color: var(--text-primary);
        font-size: 13px;
        cursor: pointer;
        transition: all 0.2s;
    }

    .filter-select:focus {
        outline: none;
        border-color: var(--primary);
        background: rgba(26, 26, 26, 0.8);
    }

    .table-container {
        background: rgba(30, 30, 30, 0.6);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border-radius: 12px;
        border: 1px solid rgba(255, 255, 255, 0.1);
        overflow: hidden;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.2);
    }

    table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
    }

    thead {
        background: rgba(26, 26, 26, 0.4);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
    }

    th {
        padding: 12px 20px;
        text-align: left;
        font-size: 11px;
        font-weight: 600;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    }

    tbody tr {
        border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        transition: all 0.2s ease;
        background: transparent;
    }

    tbody tr:hover {
        background: rgba(42, 42, 42, 0.4);
        transform: translateX(4px);
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
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 500;
        border: 1px solid;
    }

    .pill-allow {
        background: rgba(74, 222, 128, 0.15);
        color: var(--success);
        border-color: rgba(74, 222, 128, 0.3);
    }

    .pill-block {
        background: rgba(248, 113, 113, 0.15);
        color: var(--error);
        border-color: rgba(248, 113, 113, 0.3);
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
</style>
@endsection

@section('content')
<div class="page-header">
    <h1>IP Rules Management</h1>
    <p class="page-subtitle">
        Manage IP addresses in your whitelist or blacklist. Any changes are automatically synchronized with ModSecurity and Nginx will be reloaded.
    </p>
</div>

@if (session('status'))
    <div class="alert">
        {{ session('status') }}
    </div>
@endif

{{-- Statistics --}}
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-value">{{ $rules->count() }}</div>
        <div class="stat-label">Total Rules</div>
    </div>
    <div class="stat-card">
        <div class="stat-value" style="color: var(--success);">{{ $rules->where('type', 'allow')->count() }}</div>
        <div class="stat-label">Whitelist</div>
    </div>
    <div class="stat-card">
        <div class="stat-value" style="color: var(--error);">{{ $rules->where('type', 'block')->count() }}</div>
        <div class="stat-label">Blacklist</div>
    </div>
    <div class="stat-card">
        <div class="stat-value" style="color: var(--info);">{{ $rules->whereNull('site_id')->count() }}</div>
        <div class="stat-label">Global Rules</div>
    </div>
</div>

{{-- Search and Filter --}}
<div class="search-filter-section">
    <input type="text" id="rule-search" class="search-input" placeholder="Search by IP address or site..." onkeyup="filterRules()">
    <select id="type-filter" class="filter-select" onchange="filterRules()">
        <option value="">All Types</option>
        <option value="allow">Whitelist</option>
        <option value="block">Blacklist</option>
    </select>
    <select id="site-filter" class="filter-select" onchange="filterRules()">
        <option value="">All Sites</option>
        <option value="global">Global</option>
        @foreach($sites as $site)
            <option value="site-{{ $site->id }}">{{ $site->name }}</option>
        @endforeach
    </select>
</div>

<div class="card">
    <div style="margin-bottom: 24px; padding-bottom: 20px; border-bottom: 1px solid rgba(255, 255, 255, 0.08);">
        <label style="display: block; font-size: 11px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600; margin-bottom: 12px;">Filter by Site</label>
        <div style="display: flex; gap: 8px; flex-wrap: wrap; align-items: center;">
            @if(auth()->user()->isSuperAdmin())
                <a href="{{ route('ip-rules.index', ['site_id' => 'global']) }}" 
                   class="btn {{ $siteId === 'global' ? 'btn-primary' : 'btn-secondary' }}">
                    Global Rules
                </a>
            @endif
            <a href="{{ route('ip-rules.index', ['site_id' => 'all']) }}" 
               class="btn {{ $siteId === 'all' ? 'btn-primary' : 'btn-secondary' }}">
                All Rules
            </a>
            @foreach($sites as $s)
                <a href="{{ route('ip-rules.index', ['site_id' => $s->id]) }}" 
                   class="btn {{ $siteId == $s->id ? 'btn-primary' : 'btn-secondary' }}">
                    {{ $s->name }}
                </a>
            @endforeach
        </div>
    </div>

    <form method="POST" action="{{ route('ip-rules.store') }}">
        @csrf
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
            <div class="form-group" style="display: flex; align-items: end;">
                <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 11px 16px; font-size: 13px;">Add Rule</button>
            </div>
        </div>
    </form>
</div>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>Site</th>
                <th>IP Address</th>
                <th>Type</th>
                <th>Created At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        @forelse ($rules as $rule)
            <tr>
                <td data-site-id="{{ $rule->site_id ?? 'global' }}">
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
                    <form method="POST" action="{{ route('ip-rules.destroy', $rule) }}"
                          onsubmit="return confirm('Are you sure you want to delete this rule?');" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="4" class="empty-state">
                    No rules currently. You can add an IP address above.
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection

@section('scripts')
<script>
    // Filter rules
    function filterRules() {
        const searchTerm = document.getElementById('rule-search').value.toLowerCase();
        const typeFilter = document.getElementById('type-filter').value;
        const siteFilter = document.getElementById('site-filter').value;
        
        const rows = document.querySelectorAll('tbody tr');
        
        rows.forEach(row => {
            const ip = row.querySelector('td:nth-child(2) strong')?.textContent.toLowerCase() || '';
            const siteBadge = row.querySelector('td:nth-child(1) .badge')?.textContent.toLowerCase() || '';
            const type = row.querySelector('.pill')?.classList.contains('pill-allow') ? 'allow' : 'block';
            const isGlobal = siteBadge.includes('global');
            const siteId = row.querySelector('td:nth-child(1)')?.getAttribute('data-site-id') || '';
            
            const matchesSearch = ip.includes(searchTerm) || siteBadge.includes(searchTerm);
            const matchesType = !typeFilter || type === typeFilter;
            const matchesSite = !siteFilter || 
                (siteFilter === 'global' && isGlobal) ||
                (siteFilter.startsWith('site-') && siteId === siteFilter.replace('site-', ''));
            
            if (matchesSearch && matchesType && matchesSite) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    // Add loading state to buttons
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
