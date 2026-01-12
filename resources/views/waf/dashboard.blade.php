@extends('layouts.waf')

@section('title', 'WAF Dashboard')

@section('styles')
<style>
    /* Clean Dark Design with Dots Pattern */
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

    html, body {
        background: var(--bg-dark) !important;
        background-color: var(--bg-dark) !important;
    }

    .content-wrapper {
        background: transparent !important;
    }

    .page-header {
        margin-bottom: 32px;
        direction: ltr;
        text-align: left;
    }

    .page-header-top {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 20px;
        flex-wrap: wrap;
        gap: 16px;
    }

    .page-title {
        font-size: 32px;
        font-weight: 600;
        color: var(--text-primary);
        margin: 0 0 12px 0;
        letter-spacing: -0.5px;
        text-align: center;
    }

    .center-logo-container {
        display: flex;
        justify-content: center;
        align-items: center;
        width: 100%;
        margin: 10px 0 40px 0;
        padding: 20px 0;
    }

    .center-logo-container img {
        max-height: 160px;
        width: auto;
        object-fit: contain;
    }

    .page-timestamp {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 12px;
        color: var(--text-muted);
        font-weight: 400;
        padding: 6px 12px;
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: 6px;
    }

    .page-description {
        font-size: 14px;
        color: var(--text-secondary);
        line-height: 1.6;
        max-width: 700px;
        margin-top: 16px;
    }

    /* Stats Grid */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 20px;
        margin-bottom: 40px;
    }

    .stat-card {
        background: #1E1E1E;
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 24px;
        position: relative;
        transition: all 0.2s ease;
    }

    .stat-card:hover {
        border-color: var(--border-light);
        background: var(--bg-hover);
    }

    .stat-card-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 20px;
    }

    .stat-icon-wrapper {
        width: 32px;
        height: 32px;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        background: transparent;
        border: 1px solid var(--border);
        color: var(--text-secondary);
        margin-bottom: 12px;
    }

    .stat-label {
        font-size: 12px;
        color: var(--text-muted);
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 10px;
    }

    .stat-value {
        font-size: 36px;
        font-weight: 600;
        color: var(--text-primary);
        line-height: 1;
        margin-bottom: 12px;
        font-family: -apple-system, BlinkMacSystemFont, sans-serif;
    }

    .stat-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 500;
        border: 1px solid;
    }

    .stat-badge.success {
        background: rgba(74, 222, 128, 0.15);
        color: var(--success);
        border-color: rgba(74, 222, 128, 0.3);
    }

    .stat-badge.danger {
        background: rgba(239, 68, 68, 0.15);
        color: #EF4444;
        border-color: rgba(239, 68, 68, 0.3);
    }

    .stat-badge.warning {
        background: rgba(251, 191, 36, 0.15);
        color: var(--warning);
        border-color: rgba(251, 191, 36, 0.3);
    }

    .stat-description {
        font-size: 12px;
        color: var(--text-muted);
        line-height: 1.6;
        margin-top: 16px;
        padding-top: 16px;
        border-top: 1px solid var(--border);
    }

    /* Content Layout */
    .content-layout {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }

    @media (max-width: 1024px) {
        .content-layout {
            grid-template-columns: 1fr;
        }
    }

    /* Panels */
    .panel {
        background: #1E1E1E;
        border: 1px solid var(--border);
        border-radius: 12px;
        overflow: hidden;
        transition: all 0.2s ease;
    }

    .panel:hover {
        border-color: var(--border-light);
    }

    .panel-header {
        background: #1E1E1E;
        padding: 20px;
        border-bottom: 1px solid var(--border);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .panel-title {
        font-size: 16px;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 4px;
    }

    .panel-subtitle {
        font-size: 12px;
        color: var(--text-muted);
        font-weight: 400;
    }

    .panel-action {
        font-size: 12px;
        color: var(--primary);
        text-decoration: none;
        font-weight: 500;
        padding: 6px 12px;
        border-radius: 6px;
        transition: all 0.2s;
        border: 1px solid var(--border);
        background: transparent;
    }

    .panel-action:hover {
        background: rgba(157, 78, 221, 0.1);
        border-color: var(--primary);
        color: var(--primary-hover);
        text-decoration: none;
    }

    /* Tables */
    .panel-content {
        padding: 0;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    thead {
        background: #1E1E1E;
    }

    th {
        padding: 12px 20px;
        text-align: left;
        font-size: 11px;
        font-weight: 600;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 1px solid var(--border);
    }

    tbody tr {
        border-bottom: 1px solid var(--border);
        transition: background-color 0.15s ease;
    }

    tbody tr:hover {
        background: var(--bg-hover);
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

    /* Badges */
    .badge {
        display: inline-flex;
        align-items: center;
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 500;
        gap: 6px;
        border: 1px solid;
    }

    .badge-status {
        background: rgba(179, 179, 179, 0.1);
        color: var(--text-secondary);
        border-color: rgba(179, 179, 179, 0.2);
    }

    .badge-status.active {
        background: rgba(239, 68, 68, 0.15);
        color: #EF4444;
        border-color: rgba(239, 68, 68, 0.3);
    }

    .badge-rule {
        background: rgba(157, 78, 221, 0.1);
        color: var(--primary);
        border-color: rgba(157, 78, 221, 0.2);
        font-family: 'Courier New', monospace;
        font-size: 11px;
    }

    .status-indicator {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        display: inline-block;
    }

    .status-indicator.active {
        background: #EF4444;
        box-shadow: none;
    }

    /* Empty State */
    .empty-state {
        padding: 48px 24px;
        text-align: center;
        color: var(--text-muted);
        font-size: 13px;
        font-weight: 400;
    }

    .panel-footer {
        padding: 12px 20px;
        background: #1E1E1E;
        border-top: 1px solid var(--border);
        font-size: 11px;
        color: var(--text-muted);
        font-weight: 400;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .stats-grid {
            grid-template-columns: 1fr;
        }

        .page-title {
            font-size: 24px;
        }

        .stat-value {
            font-size: 28px;
        }
    }

    td strong {
        font-weight: 600;
        color: var(--text-primary);
    }
</style>
@endsection

@section('content')
{{-- Center Logo --}}
<div class="center-logo-container">
    <img src="{{ asset('images/wafgate.png') }}" alt="WAF Edge">
</div>

<div class="page-header">
    <div class="page-header-top">
        <div>
            <h1 class="page-title">WAF Dashboard</h1>
            <div class="page-timestamp">
                <span>Last Updated: {{ now()->format('Y-m-d H:i:s') }}</span>
            </div>
        </div>
    </div>
</div>

{{-- Statistics Cards --}}
<div class="stats-grid">
    <div class="stat-card total">
        <div class="stat-card-header">
            <div style="flex: 1;">
                <div class="stat-icon-wrapper">—</div>
                <div class="stat-label">Total Events</div>
                <div class="stat-value">{{ number_format($total) }}</div>
                <div>
                    <span class="stat-badge {{ $total > 0 ? 'warning' : 'success' }}">
                        {{ $total > 0 ? 'Active' : 'Safe' }}
                    </span>
                </div>
            </div>
        </div>
        <div class="stat-description">
            All requests analyzed and processed through ModSecurity and OWASP CRS in the last 24 hours.
        </div>
    </div>

    <div class="stat-card blocked">
        <div class="stat-card-header">
            <div style="flex: 1;">
                <div class="stat-icon-wrapper">▪</div>
                <div class="stat-label">Blocked Requests</div>
                <div class="stat-value">{{ number_format($blocked) }}</div>
                <div>
                    <span class="stat-badge danger">
                        {{ $total > 0 ? round(($blocked / max($total,1)) * 100) : 0 }}% of total
                    </span>
                </div>
            </div>
        </div>
        <div class="stat-description">
            Number of requests successfully blocked before reaching the application, such as SQL Injection, XSS, and CSRF attempts.
        </div>
    </div>

    <div class="stat-card attack">
        <div class="stat-card-header">
            <div style="flex: 1;">
                <div class="stat-icon-wrapper">▫</div>
                <div class="stat-label">Top Attack Source</div>
                <div class="stat-value" style="font-size: 18px; font-family: 'Courier New', monospace; line-height: 1.2;">
                    {{ optional($topIps->first())->client_ip ?? 'None' }}
                </div>
                <div>
                    <span class="stat-badge warning">
                        {{ optional($topIps->first())->cnt ?? 0 }} attempts
                    </span>
                </div>
            </div>
        </div>
        <div class="stat-description">
            IP address with the highest number of suspicious access attempts and potential attacks today.
        </div>
    </div>
</div>

{{-- Content Panels --}}
<div class="content-layout">
    {{-- Top IPs Panel --}}
    <div class="panel">
        <div class="panel-header">
            <div class="panel-title-wrapper">
                <div class="panel-title">Top IP Addresses</div>
                <div class="panel-subtitle">Most active sources by number of attempts</div>
            </div>
            <a href="/waf/events" class="panel-action">View All →</a>
        </div>
        <div class="panel-content">
            <table>
                <thead>
                    <tr>
                        <th>IP Address</th>
                        <th>Attempts</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($topIps as $ip)
                    <tr>
                        <td>
                            <strong style="font-family: 'Courier New', monospace;">
                                {{ $ip->client_ip }}
                            </strong>
                        </td>
                        <td>
                            <strong>{{ number_format($ip->cnt) }}</strong>
                        </td>
                        <td>
                            <span class="badge badge-status active">
                                <span class="status-indicator active"></span>
                                High Activity
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="empty-state">
                            No data available at this time
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Top Rules Panel --}}
    <div class="panel">
        <div class="panel-header">
            <div class="panel-title-wrapper">
                <div class="panel-title">Most Triggered Rules</div>
                <div class="panel-subtitle">Most common attack types</div>
            </div>
        </div>
        <div class="panel-content">
            <table>
                <thead>
                    <tr>
                        <th>Rule ID</th>
                        <th>Count</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($topRules as $rule)
                    <tr>
                        <td>
                            <span class="badge badge-rule">
                                {{ $rule->rule_id ?? 'N/A' }}
                            </span>
                        </td>
                        <td>
                            <strong>{{ number_format($rule->cnt) }}</strong>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2" class="empty-state">
                            No rules triggered at this time
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="panel-footer">
            Displaying most triggered rules in the last 24 hours
        </div>
    </div>
</div>
@endsection
