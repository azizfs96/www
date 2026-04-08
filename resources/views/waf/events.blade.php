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
    
    .filter-group select {
        appearance: none;
        -webkit-appearance: none;
        -moz-appearance: none;
        background-image:
            linear-gradient(45deg, transparent 50%, var(--text-secondary) 50%),
            linear-gradient(135deg, var(--text-secondary) 50%, transparent 50%);
        background-position:
            calc(100% - 18px) calc(50% - 2px),
            calc(100% - 12px) calc(50% - 2px);
        background-size: 6px 6px, 6px 6px;
        background-repeat: no-repeat;
        padding-right: 34px;
        color-scheme: dark;
    }
    
    .filter-group select option {
        background: #101010;
        color: #f1f1f1;
    }
    
    .filter-group select option:checked,
    .filter-group select option:hover,
    .filter-group select option:focus,
    .filter-group select option:active {
        background: #9D4EDD !important;
        color: #ffffff;
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

    /* Log table: thead/tbody share column geometry — fixes header/body misalignment */
    .events-table-scroll {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        direction: ltr;
    }

    .events-log-table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
        direction: ltr;
    }

    .events-log-table col.col-w-time { width: 14%; }
    .events-log-table col.col-w-ip { width: 13%; }
    .events-log-table col.col-w-method { width: 7%; }
    .events-log-table col.col-w-path { width: 24%; }
    .events-log-table col.col-w-destination { width: 16%; }
    .events-log-table col.col-w-rule { width: 10%; }
    .events-log-table col.col-w-status { width: 16%; }

    .events-log-table thead th {
        text-align: left;
        padding: 12px 14px;
        font-size: 11px;
        font-weight: 700;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.6px;
        background: var(--bg-card);
        border-bottom: 1px solid var(--border);
        vertical-align: middle;
    }

    .events-log-table tbody.event-group tr.event-main-row {
        cursor: pointer;
        transition: background 0.2s ease;
    }

    .events-log-table tbody.event-group:hover tr.event-main-row {
        background: var(--bg-hover);
    }

    .events-log-table tbody.event-group tr.event-main-row td {
        padding: 14px 14px;
        vertical-align: middle;
        border-bottom: 1px solid var(--border);
        text-align: left;
    }
    
    /* Clear separator line between each event group */
    .events-log-table tbody.event-group + tbody.event-group tr.event-main-row td {
        border-top: 1px solid rgba(255, 255, 255, 0.14);
    }

    .td-ip-inner {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 8px;
        min-width: 0;
    }

    .td-rule {
        font-family: 'Courier New', monospace;
        font-size: 12px;
        font-weight: 600;
        color: var(--text-secondary);
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .td-rule.has-rule {
        color: #EF4444;
    }

    .event-url-path {
        direction: ltr;
        font-family: 'Courier New', monospace;
        font-size: 12px;
        color: var(--text-primary);
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        min-width: 0;
    }

    .event-url-host {
        direction: ltr;
        font-family: 'Courier New', monospace;
        font-size: 12px;
        color: var(--text-secondary);
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        min-width: 0;
    }

    .event-time {
        display: flex;
        flex-direction: column;
        gap: 4px;
        min-width: 0;
        text-align: left;
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


    .event-method-text {
        font-family: 'Courier New', monospace;
        font-size: 12px;
        font-weight: 600;
        color: var(--text-secondary);
        letter-spacing: 0.3px;
        text-transform: uppercase;
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

    .event-status {
        display: inline-block;
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 700;
        white-space: nowrap;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border: 1px solid;
        min-width: 80px;
        text-align: center;
        box-sizing: border-box;
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

    .event-details-inner {
        display: grid;
        grid-template-columns: auto 1fr;
        gap: 20px;
        align-items: start;
        padding: 16px 24px 20px 24px;
        padding-right: min(200px, 8vw);
        background: var(--bg-details);
        border-top: 1px solid var(--border);
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

    .event-detail-actions {
        grid-column: 1 / -1;
        margin-top: 16px;
        padding-top: 16px;
        border-top: 1px solid var(--border);
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

    /* Collapsible detail row (<tr>) */
    tr.event-details-row {
        display: none;
    }

    tr.event-details-row.expanded {
        display: table-row;
    }

    .event-details-cell {
        padding: 0 !important;
        border-bottom: 1px solid var(--border);
        vertical-align: top;
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

    @media (max-width: 1400px) {
        .event-details-inner {
            padding-right: 24px;
        }
    }

    @media (max-width: 900px) {
        .event-details-inner {
            grid-template-columns: 1fr;
            padding-right: 24px;
        }

        .event-details-label {
            min-width: auto;
            margin-bottom: 8px;
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

{{-- Events List (semantic table: header cells share column widths with body) --}}
<div class="events-list">
    @if(isset($events) && $events->isNotEmpty())
    <div class="events-table-scroll">
        <table class="events-log-table">
            <colgroup>
                <col class="col-w-time" span="1" />
                <col class="col-w-ip" span="1" />
                <col class="col-w-method" span="1" />
                <col class="col-w-path" span="1" />
                <col class="col-w-destination" span="1" />
                <col class="col-w-rule" span="1" />
                <col class="col-w-status" span="1" />
            </colgroup>
            <thead>
                <tr>
                    <th scope="col">Time</th>
                    <th scope="col">IP</th>
                    <th scope="col">Method</th>
                    <th scope="col">Path</th>
                    <th scope="col">Destination</th>
                    <th scope="col">Rule</th>
                    <th scope="col">Status</th>
                </tr>
            </thead>
            @foreach ($events as $event)
        @php
            $status = (int) $event->status;
            $rule = $event->rule_id;
            $desc = $rule ? ($ruleDescriptions[$rule] ?? null) : null;
            $eventTime = $event->event_time ? $event->event_time->setTimezone('Asia/Riyadh') : null;
            $methodLabel = strtoupper($event->method ?? 'GET');
        @endphp
            <tbody class="event-group" data-event-id="{{ $event->id ?? '' }}">
                <tr class="event-main-row" onclick="toggleEventDetails(this)">
                    <td class="td-time">
                        <div class="event-time">
                            <div class="event-timestamp">{{ $eventTime ? $eventTime->format('Y-m-d H:i:s') : '' }}</div>
                        </div>
                    </td>
                    <td class="td-ip">
                        <div class="td-ip-inner">
                            <strong class="event-ip">{{ $event->client_ip }}</strong>
                            @if($event->country)
                                <span class="event-country"
                                      data-country-code="{{ $event->country }}"
                                      onclick="showCountryTooltip(this, event)">
                                    {{ $event->country }}
                                    <span class="country-tooltip" id="tooltip-{{ $event->id }}"></span>
                                </span>
                            @endif
                        </div>
                    </td>
                    <td class="td-method">
                        <span class="event-method-text">{{ $methodLabel }}</span>
                    </td>
                    <td class="td-path">
                        <span class="event-url-path" title="{{ ($event->uri !== null && $event->uri !== '') ? $event->uri : '/' }}">{{ $event->uri !== null && $event->uri !== '' ? $event->uri : '/' }}</span>
                    </td>
                    <td class="td-destination">
                        <span class="event-url-host" title="{{ $event->host ?? '' }}">{{ $event->host ? Str::limit($event->host, 64) : '—' }}</span>
                    </td>
                    <td class="td-rule {{ $rule ? 'has-rule' : '' }}" title="{{ $rule ? (string) $rule : '' }}">
                        {{ $rule ?: '—' }}
                    </td>
                    <td class="td-status">
                        <span class="event-status {{ $status === 403 ? 'blocked' : ($status === 200 ? 'allowed' : 'other') }}">
                            @if ($status === 403)
                                Blocked
                            @elseif ($status === 200)
                                Allowed
                            @else
                                {{ $status }}
                            @endif
                        </span>
                    </td>
                </tr>
                <tr class="event-details-row" id="details-{{ $event->id ?? '' }}">
                    <td colspan="7" class="event-details-cell">
                        <div class="event-details-inner">
                <div class="event-details-label">Attack Details</div>
                <div class="event-details-content">
                    @if ($desc)
                        <div class="event-detail-item">
                            <span class="event-detail-label">Type:</span>
                            <span class="event-detail-value">{{ $desc }}</span>
                        </div>
                    @endif
                    @if ($rule)
                        <div class="event-detail-item">
                            <span class="event-detail-label">Rule ID:</span>
                            <span class="event-detail-value highlight">{{ $rule }}</span>
                        </div>
                    @endif
                    @if ($event->method)
                        <div class="event-detail-item">
                            <span class="event-detail-label">Method:</span>
                            <span class="event-detail-value">{{ $event->method }}</span>
                        </div>
                    @endif
                    @if ($event->severity)
                        <div class="event-detail-item">
                            <span class="event-detail-label">Severity:</span>
                            <span class="event-detail-value severity severity-{{ $event->severity }}">{{ $event->severity }}</span>
                        </div>
                    @endif
                    @if ($event->country)
                        @php
                            $countryNames = [
                                'US' => 'United States',
                                'SA' => 'Saudi Arabia',
                                'GB' => 'United Kingdom',
                                'DE' => 'Germany',
                                'FR' => 'France',
                                'CN' => 'China',
                                'JP' => 'Japan',
                                'IN' => 'India',
                                'BR' => 'Brazil',
                                'RU' => 'Russia',
                                'CA' => 'Canada',
                                'AU' => 'Australia',
                                'IT' => 'Italy',
                                'ES' => 'Spain',
                                'NL' => 'Netherlands',
                                'SE' => 'Sweden',
                                'NO' => 'Norway',
                                'DK' => 'Denmark',
                                'FI' => 'Finland',
                                'PL' => 'Poland',
                                'KR' => 'South Korea',
                                'MX' => 'Mexico',
                                'AR' => 'Argentina',
                                'ZA' => 'South Africa',
                                'EG' => 'Egypt',
                                'AE' => 'United Arab Emirates',
                                'TR' => 'Turkey',
                                'ID' => 'Indonesia',
                                'TH' => 'Thailand',
                                'VN' => 'Vietnam',
                                'PH' => 'Philippines',
                                'MY' => 'Malaysia',
                                'SG' => 'Singapore',
                                'NZ' => 'New Zealand',
                                'IE' => 'Ireland',
                                'CH' => 'Switzerland',
                                'AT' => 'Austria',
                                'BE' => 'Belgium',
                                'PT' => 'Portugal',
                                'GR' => 'Greece',
                                'CZ' => 'Czech Republic',
                                'HU' => 'Hungary',
                                'RO' => 'Romania',
                                'BG' => 'Bulgaria',
                                'HR' => 'Croatia',
                                'SK' => 'Slovakia',
                                'SI' => 'Slovenia',
                                'LT' => 'Lithuania',
                                'LV' => 'Latvia',
                                'EE' => 'Estonia',
                                'IS' => 'Iceland',
                                'LU' => 'Luxembourg',
                                'MT' => 'Malta',
                                'CY' => 'Cyprus',
                                'KW' => 'Kuwait',
                                'QA' => 'Qatar',
                                'BH' => 'Bahrain',
                                'OM' => 'Oman',
                                'JO' => 'Jordan',
                                'LB' => 'Lebanon',
                                'IQ' => 'Iraq',
                                'SY' => 'Syria',
                                'YE' => 'Yemen',
                                'PK' => 'Pakistan',
                                'BD' => 'Bangladesh',
                                'LK' => 'Sri Lanka',
                                'NP' => 'Nepal',
                                'AF' => 'Afghanistan',
                                'IR' => 'Iran',
                                'IL' => 'Israel',
                                'PS' => 'Palestine',
                                'LOCAL' => 'Local Network',
                                'PRIVATE' => 'Private Network',
                                'UNKNOWN' => 'Unknown Country',
                            ];
                            $countryName = $countryNames[strtoupper($event->country)] ?? $event->country;
                        @endphp
                        <div class="event-detail-item">
                            <span class="event-detail-label">Country:</span>
                            <span class="event-detail-value">{{ $countryName }}</span>
                        </div>
                    @endif
                    @if ($event->message)
                        <div class="event-message">
                            <strong style="color: var(--text-muted);">Message:</strong> {{ Str::limit($event->message, 150) }}
                        </div>
                    @endif
                </div>
                
                {{-- AI Analysis Button --}}
                <div class="event-detail-actions">
                    <button type="button" onclick="event.stopPropagation(); analyzeEvent({{ $event->id }})" class="btn-ai-analyze" title="تحليل بواسطة AI">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 2L2 7l10 5 10-5-10-5z"/>
                            <path d="M2 17l10 5 10-5M2 12l10 5 10-5"/>
                        </svg>
                        AI Analysis
                    </button>
                </div>
                        </div>
                    </td>
                </tr>
            </tbody>
            @endforeach
        </table>
    </div>
    @else
        <div class="empty-state">
            No events found matching the current filters.
        </div>
    @endif
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
    
    const countryCode = element.getAttribute('data-country-code');
    const tooltip = element.querySelector('.country-tooltip');
    
    if (!tooltip) return;
    
    const countryName = countryNames[countryCode] || countryCode;
    
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

// Toggle event details (each log entry is <tbody class="event-group">)
function toggleEventDetails(element) {
    const tbody = element.closest('tbody.event-group');
    if (!tbody) return;
    const detailsRow = tbody.querySelector('tr.event-details-row');
    if (!detailsRow) return;

    if (detailsRow.classList.contains('expanded')) {
        detailsRow.classList.remove('expanded');
        tbody.classList.remove('expanded');
    } else {
        document.querySelectorAll('tbody.event-group.expanded').forEach(tb => {
            tb.classList.remove('expanded');
            const dr = tb.querySelector('tr.event-details-row');
            if (dr) dr.classList.remove('expanded');
        });
        detailsRow.classList.add('expanded');
        tbody.classList.add('expanded');
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
