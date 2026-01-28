@extends('layouts.waf')

@section('title', 'WAF Event Logs')

@section('styles')
<style>
    /* Enhanced Game-style Event Log Design */
    :root {
        --bg-dark: #1A1A1A;
        --bg-card: #242424;
        --bg-hover: #2A2A2A;
        --bg-details: rgba(0, 0, 0, 0.25);
        --border: #333333;
        --border-light: #404040;
        --text-primary: #E5E5E5;
        --text-secondary: #B3B3B3;
        --text-muted: #808080;
        --primary: #9D4EDD;
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
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 12px;
        letter-spacing: -0.5px;
    }

    .page-subtitle {
        font-size: 14px;
        color: var(--text-secondary);
        line-height: 1.7;
    }

    .filters-container {
        background: #1E1E1E;
        border-radius: 12px;
        border: 1px solid var(--border);
        padding: 24px;
        margin-bottom: 28px;
        direction: ltr;
    }

    .filters {
        display: flex;
        gap: 18px;
        flex-wrap: wrap;
        align-items: flex-end;
    }

    .filter-group {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .filter-group label {
        font-size: 12px;
        color: var(--text-muted);
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .filter-group input,
    .filter-group select {
        background: var(--bg-dark);
        border-radius: 8px;
        border: 1px solid var(--border);
        color: var(--text-primary);
        font-size: 13px;
        padding: 11px 16px;
        min-width: 160px;
        transition: all 0.2s;
    }

    .filter-group input:focus,
    .filter-group select:focus {
        outline: none;
        border-color: var(--primary);
        background: var(--bg-hover);
        box-shadow: 0 0 0 3px rgba(157, 78, 221, 0.1);
    }

    .filter-actions {
        display: flex;
        gap: 10px;
        margin-left: auto;
    }

    .btn {
        border-radius: 8px;
        border: none;
        font-size: 13px;
        padding: 11px 20px;
        cursor: pointer;
        font-weight: 600;
        transition: all 0.2s;
    }

    .btn-primary {
        background: var(--primary);
        color: white;
        box-shadow: 0 2px 8px rgba(157, 78, 221, 0.3);
    }

    .btn-primary:hover {
        background: #8B3ACC;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(157, 78, 221, 0.4);
    }

    .btn-secondary {
        background: transparent;
        color: var(--text-secondary);
        border: 1px solid var(--border);
    }

    .btn-secondary:hover {
        background: var(--bg-hover);
        border-color: var(--border-light);
        color: var(--text-primary);
    }

    .btn-export {
        background: #3B82F6;
        color: white;
        box-shadow: 0 2px 8px rgba(59, 130, 246, 0.3);
    }

    .btn-export:hover {
        background: #2563EB;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
    }

    /* Events List Container */
    .events-list {
        background: #1E1E1E;
        border-radius: 12px;
        border: 1px solid var(--border);
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
    }

    .event-item {
        background: #1E1E1E;
        border-bottom: 1px solid var(--border);
        transition: all 0.2s ease;
        position: relative;
    }

    .event-item:last-child {
        border-bottom: none;
    }

    .event-item:hover {
        background: var(--bg-hover);
    }

    .event-item:hover .event-icon {
        transform: scale(1.1);
    }

    /* Main Event Row */
    .event-main-row {
        display: grid;
        grid-template-columns: 100px 45px minmax(0, 1fr) 140px;
        gap: 20px;
        align-items: center;
        padding: 16px 24px;
        cursor: pointer;
    }

    .event-time {
        display: flex;
        flex-direction: column;
        gap: 4px;
        min-width: 130px;
        max-width: 140px;
        text-align: right;
    }

    .event-duration {
        font-size: 13px;
        color: var(--text-secondary);
        font-family: 'Courier New', monospace;
        font-weight: 600;
    }

    .event-timestamp {
        font-size: 11px;
        color: var(--text-muted);
        font-family: 'Courier New', monospace;
    }

    .event-icon {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: rgba(248, 113, 113, 0.2);
        border: 2px solid rgba(248, 113, 113, 0.4);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        flex-shrink: 0;
        transition: all 0.2s ease;
        position: relative;
        margin: 0 auto;
    }

    .event-icon.status-403 {
        background: transparent;
        border: none;
        box-shadow: none;
    }

    .no-symbol {
        position: relative;
        display: inline-block;
        width: 14px;
        height: 14px;
    }

    .no-symbol::before {
        content: '';
        position: absolute;
        width: 14px;
        height: 14px;
        border: 2px solid #EF4444;
        border-radius: 50%;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
    }

    .no-symbol::after {
        content: '';
        position: absolute;
        width: 2px;
        height: 18px;
        background: #EF4444;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%) rotate(45deg);
        border-radius: 1px;
    }

    .event-icon.status-200 {
        background: transparent;
        border: none;
        box-shadow: none;
    }

    .event-icon.status-404 {
        background: transparent;
        border: none;
        box-shadow: none;
    }


    .event-info {
        flex: 1;
        display: grid;
        grid-template-columns: 60px minmax(0, 1fr) auto auto 100px;
        gap: 16px;
        align-items: center;
        min-width: 0;
        overflow: hidden;
        direction: ltr;
        padding: 0 8px;
    }

    .event-info > * {
        min-width: 0;
    }

    .event-host {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 200px;
    }

    .event-info strong {
        white-space: nowrap;
    }

    .event-source {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 14px;
        color: var(--text-primary);
        font-weight: 600;
        white-space: nowrap;
    }

    .event-source strong {
        font-family: 'Courier New', monospace;
        color: var(--text-primary);
    }

    .event-host {
        color: var(--text-secondary);
        font-size: 13px;
        font-weight: 400;
    }

    .event-separator {
        color: var(--text-muted);
        font-size: 12px;
    }

    .event-target {
        font-size: 12px;
        color: var(--text-secondary);
        display: flex;
        align-items: center;
        gap: 8px;
        white-space: nowrap;
    }

    .event-method-badge {
        font-family: 'Courier New', monospace;
        color: var(--text-primary);
        font-size: 11px;
        background: rgba(157, 78, 221, 0.15);
        border: 1px solid rgba(157, 78, 221, 0.3);
        padding: 4px 8px;
        border-radius: 4px;
        font-weight: 600;
        width: 50px;
        text-align: center;
        flex-shrink: 0;
        display: inline-block;
    }

    .event-uri {
        direction: ltr;
        font-family: 'Courier New', monospace;
        font-size: 12px;
        color: var(--text-secondary);
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        min-width: 0;
    }

    .event-ip {
        font-family: 'Courier New', monospace;
        color: var(--text-primary);
        font-size: 13px;
        white-space: nowrap;
    }

    .event-country {
        display: inline-flex;
        align-items: center;
        padding: 2px 8px;
        background: rgba(96, 165, 250, 0.15);
        border: 1px solid rgba(96, 165, 250, 0.3);
        border-radius: 4px;
        font-size: 10px;
        font-weight: 600;
        color: var(--info);
        font-family: 'Courier New', monospace;
        margin-left: 8px;
        white-space: nowrap;
        cursor: pointer;
        position: relative;
        transition: all 0.2s;
    }

    .event-country:hover {
        background: rgba(96, 165, 250, 0.25);
        border-color: rgba(96, 165, 250, 0.5);
        transform: scale(1.05);
    }

    .country-tooltip {
        position: absolute;
        bottom: 100%;
        left: 50%;
        transform: translateX(-50%) translateY(-8px);
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: 6px;
        padding: 8px 12px;
        font-size: 12px;
        color: var(--text-primary);
        white-space: nowrap;
        z-index: 1000;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.2s, transform 0.2s;
        margin-bottom: 4px;
    }

    .country-tooltip::after {
        content: '';
        position: absolute;
        top: 100%;
        left: 50%;
        transform: translateX(-50%);
        border: 6px solid transparent;
        border-top-color: var(--bg-card);
    }

    .country-tooltip.show {
        opacity: 1;
        pointer-events: auto;
        transform: translateX(-50%) translateY(-12px);
    }

    .event-value {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 14px;
        font-weight: 700;
        color: #EF4444;
        font-family: 'Courier New', monospace;
        white-space: nowrap;
        width: 90px;
        flex-shrink: 0;
        justify-content: flex-start;
    }

    .event-arrow {
        font-size: 14px;
        color: #EF4444;
        opacity: 0.8;
    }

    .event-status {
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 700;
        white-space: nowrap;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border: 1px solid;
        min-width: 80px;
        max-width: 100px;
        text-align: center;
        justify-self: start;
    }

    .event-status.blocked {
        background: rgba(239, 68, 68, 0.2);
        color: #EF4444;
        border-color: rgba(239, 68, 68, 0.5);
        box-shadow: none;
    }

    .event-status.allowed {
        background: rgba(74, 222, 128, 0.15);
        color: var(--success);
        border-color: rgba(74, 222, 128, 0.4);
        box-shadow: none;
    }

    .event-status.other {
        background: rgba(179, 179, 179, 0.15);
        color: var(--text-secondary);
        border-color: rgba(179, 179, 179, 0.3);
    }

    /* Event Details Row (Sub-row) */
    .event-details-row {
        padding: 16px 24px 20px 24px;
        background: var(--bg-details);
        border-top: 1px solid var(--border);
        display: grid;
        grid-template-columns: auto 1fr;
        gap: 20px;
        align-items: start;
        padding-right: 200px;
    }

    .event-details-label {
        font-size: 11px;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.8px;
        min-width: 110px;
        font-weight: 700;
        padding-top: 2px;
    }

    .event-details-content {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        font-size: 12px;
        color: var(--text-secondary);
        align-items: center;
    }

    .event-detail-item {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 4px 10px;
        background: rgba(255, 255, 255, 0.03);
        border-radius: 6px;
        border: 1px solid rgba(255, 255, 255, 0.05);
    }

    .event-detail-label {
        color: var(--text-muted);
        font-weight: 600;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .event-detail-value {
        color: var(--text-secondary);
        font-weight: 600;
        font-family: 'Courier New', monospace;
        font-size: 12px;
    }

    .event-detail-value.highlight {
        color: #EF4444;
        background: rgba(239, 68, 68, 0.15);
        padding: 2px 6px;
        border-radius: 4px;
        font-weight: 700;
    }

    .event-detail-value.severity {
        padding: 3px 8px;
        border-radius: 4px;
        font-weight: 700;
    }

    .event-detail-value.severity-CRITICAL {
        background: rgba(248, 113, 113, 0.2);
        color: var(--error);
    }

    .event-detail-value.severity-HIGH {
        background: rgba(251, 191, 36, 0.2);
        color: var(--warning);
    }

    .event-detail-value.severity-MEDIUM {
        background: rgba(96, 165, 250, 0.2);
        color: var(--info);
    }

    .event-detail-value.severity-LOW {
        background: rgba(179, 179, 179, 0.2);
        color: var(--text-secondary);
    }

    .event-message {
        flex-basis: 100%;
        margin-top: 12px;
        padding: 10px 12px;
        background: rgba(0, 0, 0, 0.2);
        border-radius: 6px;
        border-right: 3px solid var(--primary);
        direction: ltr;
        font-family: 'Courier New', monospace;
        font-size: 11px;
        color: var(--text-secondary);
        line-height: 1.6;
    }

    .empty-state {
        padding: 80px 24px;
        text-align: center;
        color: var(--text-muted);
        font-size: 14px;
    }

    .footer-note {
        margin-top: 24px;
        font-size: 12px;
        color: var(--text-muted);
        text-align: center;
        padding: 16px;
        background: #1E1E1E;
        border-radius: 8px;
        border: 1px solid var(--border);
    }

    /* Statistics Cards */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 20px;
        margin-bottom: 28px;
    }

    .stat-card {
        background: #1E1E1E;
        border-radius: 12px;
        border: 1px solid var(--border);
        padding: 20px;
        transition: all 0.2s;
    }

    .stat-card:hover {
        border-color: var(--border-light);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
    }

    .stat-label {
        font-size: 12px;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 8px;
        font-weight: 600;
    }

    .stat-value {
        font-size: 28px;
        font-weight: 700;
        color: var(--text-primary);
        font-family: 'Courier New', monospace;
    }

    .stat-card.total .stat-value {
        color: var(--info);
    }

    .stat-card.blocked .stat-value {
        color: var(--error);
    }

    .stat-card.allowed .stat-value {
        color: var(--success);
    }

    .stat-card.ips .stat-value {
        color: var(--primary);
    }

    /* Collapsible Details */
    .event-details-row {
        display: none;
        max-height: 0;
        overflow: hidden;
        transition: all 0.3s ease;
    }

    .event-details-row.expanded {
        display: grid;
        max-height: 1000px;
        padding: 16px 24px 20px 24px;
    }

    .event-item.expanded .event-main-row::after {
        content: '▼';
        position: absolute;
        left: 24px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-muted);
        font-size: 12px;
    }


    /* Search Box */
    .filter-group.search-group {
        flex: 1;
        min-width: 250px;
    }

    .filter-group.search-group input {
        width: 100%;
    }

    /* Pagination Styles */
    .pagination {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
        margin: 24px 0;
    }

    .pagination a,
    .pagination span {
        display: inline-block;
        padding: 8px 14px;
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: 6px;
        color: var(--text-primary);
        text-decoration: none;
        font-size: 13px;
        transition: all 0.2s;
    }

    .pagination a:hover {
        background: var(--bg-hover);
        border-color: var(--primary);
        color: var(--primary);
    }

    .pagination .active span {
        background: var(--primary);
        border-color: var(--primary);
        color: white;
    }

    .pagination .disabled span {
        opacity: 0.5;
        cursor: not-allowed;
    }

    /* Responsive */
    @media (max-width: 1400px) {
        .event-details-row {
            padding-right: 24px;
        }
    }

    @media (max-width: 1200px) {
        .event-main-row {
            grid-template-columns: 90px 40px minmax(0, 1fr) 130px;
            gap: 16px;
        }
        
        .event-info {
            gap: 12px;
        }
        
        .event-info .event-value {
            display: none;
        }
    }

    @media (max-width: 900px) {
        .event-main-row {
            grid-template-columns: auto minmax(0, 1fr) 90px;
            gap: 10px;
            padding: 14px 16px;
        }
        
        .event-time {
            min-width: 85px;
            max-width: 90px;
        }
        
        .event-icon {
            display: none;
        }
        
        .event-status {
            font-size: 10px;
            padding: 5px 8px;
        }
        
        .event-info {
            grid-template-columns: 50px minmax(0, 1fr) auto auto;
            gap: 8px;
        }
        
        .event-info .event-value {
            display: none;
        }
        
        .event-details-row {
            grid-template-columns: 1fr;
            padding-right: 24px;
        }
        
        .event-details-label {
            min-width: auto;
            margin-bottom: 8px;
        }
    }

    @media (max-width: 768px) {
        .event-main-row {
            grid-template-columns: 1fr;
            gap: 12px;
            padding: 12px 16px;
        }
        
        .event-time {
            flex-direction: row;
            gap: 12px;
            align-items: center;
            max-width: 100%;
            justify-content: flex-start;
        }
        
        .event-info {
            grid-template-columns: 50px minmax(0, 1fr) auto;
            gap: 8px;
        }
        
        .event-info .event-value {
            display: none;
        }
        
        .event-status {
            justify-self: start;
        }
        
        .event-icon {
            display: none;
        }
    }

    /* AI Analysis Button */
    .btn-ai-analyze {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 16px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .btn-ai-analyze:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
    }

    /* AI Modal */
    .ai-modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.85);
        z-index: 9999;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    .ai-modal.active {
        display: flex;
    }

    .ai-modal-content {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: 16px;
        max-width: 800px;
        width: 100%;
        max-height: 90vh;
        overflow-y: auto;
    }

    .ai-modal-header {
        padding: 24px;
        border-bottom: 1px solid var(--border);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .ai-modal-title {
        font-size: 20px;
        font-weight: 600;
        color: var(--text-primary);
    }

    .ai-modal-close {
        background: none;
        border: none;
        color: var(--text-muted);
        font-size: 24px;
        cursor: pointer;
    }

    .ai-modal-body {
        padding: 24px;
        color: var(--text-primary);
        line-height: 1.8;
    }

    .ai-loading {
        text-align: center;
        padding: 40px;
    }

    .ai-loading-spinner {
        width: 40px;
        height: 40px;
        border: 3px solid var(--border);
        border-top-color: var(--primary);
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin: 0 auto 16px;
    }

    @keyframes spin {
        to { transform: rotate(360deg); }
    }
</style>
@endsection

@section('content')
<div class="page-header">
    <h1>WAF Event Logs</h1>
</div>

{{-- Statistics Cards --}}
@if(isset($stats))
<div class="stats-grid">
    <div class="stat-card total">
        <div class="stat-label">Total Events</div>
        <div class="stat-value">{{ number_format($stats['total'] ?? 0) }}</div>
    </div>
    <div class="stat-card blocked">
        <div class="stat-label">Blocked (403)</div>
        <div class="stat-value">{{ number_format($stats['blocked'] ?? 0) }}</div>
    </div>
    <div class="stat-card allowed">
        <div class="stat-label">Allowed (200)</div>
        <div class="stat-value">{{ number_format($stats['allowed'] ?? 0) }}</div>
    </div>
    <div class="stat-card ips">
        <div class="stat-label">Unique IPs</div>
        <div class="stat-value">{{ number_format($stats['unique_ips'] ?? 0) }}</div>
    </div>
</div>
@endif

{{-- Filters --}}
<div class="filters-container">
    <form method="GET" action="/waf/events" class="filters" id="filtersForm">
        <div class="filter-group search-group">
            <label>Text Search</label>
            <input type="text" name="search" placeholder="Search in IP, URI, Host, or Message..."
                   value="{{ $filters['search'] ?? '' }}">
        </div>

        <div class="filter-group">
            <label>HTTP Status</label>
            <select name="status">
                <option value="">All</option>
                <option value="403" {{ ($filters['status'] ?? '') == '403' ? 'selected' : '' }}>403 (Blocked)</option>
                <option value="200" {{ ($filters['status'] ?? '') == '200' ? 'selected' : '' }}>200 (Allowed)</option>
                <option value="404" {{ ($filters['status'] ?? '') == '404' ? 'selected' : '' }}>404 (Not Found)</option>
            </select>
        </div>

        <div class="filter-group">
            <label>IP Address</label>
            <input type="text" name="ip" placeholder="e.g., 137.59.230.231"
                   value="{{ $filters['ip'] ?? '' }}">
        </div>

        <div class="filter-group">
            <label>From Date</label>
            <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}">
        </div>

        <div class="filter-group">
            <label>To Date</label>
            <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}">
        </div>

        <div class="filter-actions">
            <button type="submit" class="btn btn-primary">Apply Filters</button>
            <a href="/waf/events">
                <button type="button" class="btn btn-secondary">Reset</button>
            </a>
            <button type="submit" name="format" value="csv" class="btn btn-export">
                Export CSV
            </button>
        </div>
    </form>
</div>

{{-- Events List --}}
<div class="events-list" id="events-list">
    @include('waf.partials.events_list', ['events' => $events, 'ruleDescriptions' => $ruleDescriptions])
</div>

{{-- Pagination --}}
@if(isset($events) && method_exists($events, 'links'))
<div style="margin-top: 24px; display: flex; justify-content: center;">
    {{ $events->links() }}
</div>
@endif

<div class="footer-note">
    Results are displayed according to the selected filters. Click on any event to show/hide details.
</div>

<script>
// Country code to country name mapping
const countryNames = {
    'US': 'United States',
    'SA': 'Saudi Arabia',
    'GB': 'United Kingdom',
    'DE': 'Germany',
    'FR': 'France',
    'CN': 'China',
    'JP': 'Japan',
    'IN': 'India',
    'BR': 'Brazil',
    'RU': 'Russia',
    'CA': 'Canada',
    'AU': 'Australia',
    'IT': 'Italy',
    'ES': 'Spain',
    'NL': 'Netherlands',
    'SE': 'Sweden',
    'NO': 'Norway',
    'DK': 'Denmark',
    'FI': 'Finland',
    'PL': 'Poland',
    'KR': 'South Korea',
    'MX': 'Mexico',
    'AR': 'Argentina',
    'ZA': 'South Africa',
    'EG': 'Egypt',
    'AE': 'United Arab Emirates',
    'TR': 'Turkey',
    'ID': 'Indonesia',
    'TH': 'Thailand',
    'VN': 'Vietnam',
    'PH': 'Philippines',
    'MY': 'Malaysia',
    'SG': 'Singapore',
    'NZ': 'New Zealand',
    'IE': 'Ireland',
    'CH': 'Switzerland',
    'AT': 'Austria',
    'BE': 'Belgium',
    'PT': 'Portugal',
    'GR': 'Greece',
    'CZ': 'Czech Republic',
    'HU': 'Hungary',
    'RO': 'Romania',
    'BG': 'Bulgaria',
    'HR': 'Croatia',
    'SK': 'Slovakia',
    'SI': 'Slovenia',
    'LT': 'Lithuania',
    'LV': 'Latvia',
    'EE': 'Estonia',
    'IS': 'Iceland',
    'LU': 'Luxembourg',
    'MT': 'Malta',
    'CY': 'Cyprus',
    'LOCAL': 'Local Network',
    'PRIVATE': 'Private Network',
    'UNKNOWN': 'Unknown Country'
};

// Show country tooltip
function showCountryTooltip(element, event) {
    event.stopPropagation(); // Prevent event details toggle
    
    // بعض البيانات قد تُخزن بحروف صغيرة (sa) أو مع مسافات، لذلك نحولها إلى Uppercase ونزيل الفراغات
    const rawCode = element.getAttribute('data-country-code') || '';
    const countryCode = rawCode.trim().toUpperCase();
    const tooltip = element.querySelector('.country-tooltip');
    
    if (!tooltip) return;
    
    const countryName = countryNames[countryCode] || countryCode || 'Unknown';
    
    // Close all other tooltips
    document.querySelectorAll('.country-tooltip.show').forEach(t => {
        if (t !== tooltip) {
            t.classList.remove('show');
        }
    });
    
    // Toggle current tooltip
    if (tooltip.classList.contains('show')) {
        tooltip.classList.remove('show');
    } else {
        tooltip.textContent = countryName;
        tooltip.classList.add('show');
        
        // Close on outside click
        setTimeout(() => {
            const closeTooltip = (e) => {
                if (!element.contains(e.target)) {
                    tooltip.classList.remove('show');
                    document.removeEventListener('click', closeTooltip);
                }
            };
            document.addEventListener('click', closeTooltip);
        }, 100);
    }
}

// Live refresh for events list
async function refreshEventsLive() {
    try {
        const container = document.getElementById('events-list');
        if (!container) return;

        // نستخدم نفس الفلاتر الحالية في رابط الصفحة
        const params = new URLSearchParams(window.location.search);
        const url = new URL('{{ route('waf.events.live') }}', window.location.origin);
        url.search = params.toString();

        const response = await fetch(url.toString(), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        if (!response.ok) return;

        const data = await response.json();
        if (data.html) {
            container.innerHTML = data.html;
        }
    } catch (e) {
        console.error('Failed to refresh events live', e);
    }
}

// تشغيل التحديث اللايف كل 5 ثواني
document.addEventListener('DOMContentLoaded', function () {
    setInterval(refreshEventsLive, 5000);
});

// Toggle event details
function toggleEventDetails(element) {
    const eventItem = element.closest('.event-item');
    const detailsRow = eventItem.querySelector('.event-details-row');
    
    if (detailsRow.classList.contains('expanded')) {
        detailsRow.classList.remove('expanded');
        eventItem.classList.remove('expanded');
    } else {
        // Close all other expanded items
        document.querySelectorAll('.event-item.expanded').forEach(item => {
            item.classList.remove('expanded');
            item.querySelector('.event-details-row').classList.remove('expanded');
        });
        
        detailsRow.classList.add('expanded');
        eventItem.classList.add('expanded');
    }
}

// AI Analysis Function
function analyzeEvent(eventId) {
    const modal = document.getElementById('aiModal');
    const modalBody = document.getElementById('aiModalBody');
    
    // Show modal with loading state
    modal.classList.add('active');
    modalBody.innerHTML = `
        <div class="ai-loading">
            <div class="ai-loading-spinner"></div>
            <div>جاري التحليل بواسطة الذكاء الاصطناعي...</div>
        </div>
    `;
    
    // Make AJAX request
    fetch(`/waf/events/${eventId}/analyze`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Format the analysis with markdown-like styling
            const formattedAnalysis = data.analysis
                .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
                .replace(/\n/g, '<br>');
            
            modalBody.innerHTML = `
                <div class="event-info-card">
                    <div class="event-info-row">
                        <span class="event-info-label">IP Address</span>
                        <span class="event-info-value">${data.event.client_ip}</span>
                    </div>
                    <div class="event-info-row">
                        <span class="event-info-label">Time</span>
                        <span class="event-info-value">${data.event.event_time}</span>
                    </div>
                    <div class="event-info-row">
                        <span class="event-info-label">Status</span>
                        <span class="event-info-value">${data.event.status}</span>
                    </div>
                </div>
                <div class="ai-analysis-content">
                    ${formattedAnalysis}
                </div>
            `;
        } else {
            modalBody.innerHTML = `
                <div class="ai-error">
                    <strong>خطأ:</strong> ${data.error}
                </div>
            `;
        }
    })
    .catch(error => {
        modalBody.innerHTML = `
            <div class="ai-error">
                <strong>خطأ في الاتصال:</strong> ${error.message}
            </div>
        `;
    });
}

function closeAiModal() {
    document.getElementById('aiModal').classList.remove('active');
}

// Close modal when clicking outside
document.addEventListener('click', function(event) {
    const modal = document.getElementById('aiModal');
    if (event.target === modal) {
        closeAiModal();
    }
});

// Simple live auto-refresh for events page (every 15 seconds)
document.addEventListener('DOMContentLoaded', function () {
    setInterval(function () {
        // يحافظ على الفلاتر الحالية لأننا نعيد تحميل نفس الرابط
        window.location.reload();
    }, 15000); // 15000ms = 15 seconds
});

</script>

{{-- AI Analysis Modal --}}
<div id="aiModal" class="ai-modal">
    <div class="ai-modal-content">
        <div class="ai-modal-header">
            <div class="ai-modal-title">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 2L2 7l10 5 10-5-10-5z"/>
                    <path d="M2 17l10 5 10-5M2 12l10 5 10-5"/>
                </svg>
                AI Security Analysis
            </div>
            <button class="ai-modal-close" onclick="closeAiModal()">&times;</button>
        </div>
        <div class="ai-modal-body" id="aiModalBody">
            <!-- Content will be loaded here -->
        </div>
    </div>
</div>

@endsection
