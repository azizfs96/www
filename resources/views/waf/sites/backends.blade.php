@extends('layouts.waf')

@section('title', 'Backend Servers Management - ' . $site->name)

@section('styles')
<style>
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
        --success: #10b981;
        --warning: #f59e0b;
        --error: #F87171;
        --info: #3b82f6;
    }

    .page-header {
        margin-bottom: 32px;
    }

    .page-title {
        font-size: 32px;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 8px;
    }

    .page-subtitle {
        font-size: 14px;
        color: var(--text-secondary);
    }

    .back-link {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: var(--text-muted);
        text-decoration: none;
        font-size: 13px;
        margin-top: 12px;
        transition: color 0.2s;
    }

    .back-link:hover {
        color: var(--primary);
    }

    .card {
        background: rgba(30, 30, 30, 0.6);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 12px;
        padding: 24px;
        margin-bottom: 24px;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.2);
    }

    .server-card {
        background: rgba(30, 30, 30, 0.5);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 12px;
        padding: 24px;
        margin-bottom: 20px;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
    }

    .server-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 4px;
        height: 100%;
        background: var(--border);
        transition: all 0.2s;
    }

    .server-card:hover {
        border-color: rgba(255, 255, 255, 0.15);
        transform: translateY(-4px) scale(1.01);
        box-shadow: 0 12px 32px rgba(0, 0, 0, 0.25);
        background: rgba(30, 30, 30, 0.7);
    }

    .server-card.active::before {
        background: var(--success);
    }

    .server-card.standby::before {
        background: var(--warning);
    }

    .server-card.unhealthy::before {
        background: var(--error);
    }

    .server-card.active {
        border-color: rgba(16, 185, 129, 0.25);
        background: linear-gradient(135deg, rgba(16, 185, 129, 0.08) 0%, rgba(30, 30, 30, 0.5) 100%);
        box-shadow: 0 4px 20px rgba(16, 185, 129, 0.1);
    }

    .server-card.active:hover {
        border-color: rgba(16, 185, 129, 0.35);
        background: linear-gradient(135deg, rgba(16, 185, 129, 0.12) 0%, rgba(30, 30, 30, 0.65) 100%);
        box-shadow: 0 12px 32px rgba(16, 185, 129, 0.15);
    }

    .server-card.standby {
        border-color: rgba(245, 158, 11, 0.25);
        background: linear-gradient(135deg, rgba(245, 158, 11, 0.08) 0%, rgba(30, 30, 30, 0.5) 100%);
        box-shadow: 0 4px 20px rgba(245, 158, 11, 0.1);
    }

    .server-card.standby:hover {
        border-color: rgba(245, 158, 11, 0.35);
        background: linear-gradient(135deg, rgba(245, 158, 11, 0.12) 0%, rgba(30, 30, 30, 0.65) 100%);
        box-shadow: 0 12px 32px rgba(245, 158, 11, 0.15);
    }

    .server-card.unhealthy {
        border-color: rgba(248, 113, 113, 0.25);
        background: linear-gradient(135deg, rgba(248, 113, 113, 0.08) 0%, rgba(30, 30, 30, 0.5) 100%);
        box-shadow: 0 4px 20px rgba(248, 113, 113, 0.1);
    }

    .server-card.unhealthy:hover {
        border-color: rgba(248, 113, 113, 0.35);
        background: linear-gradient(135deg, rgba(248, 113, 113, 0.12) 0%, rgba(30, 30, 30, 0.65) 100%);
        box-shadow: 0 12px 32px rgba(248, 113, 113, 0.15);
    }

    .server-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 20px;
        padding-bottom: 16px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        transition: all 0.2s;
    }

    .server-card:hover .server-header {
        border-bottom-color: rgba(255, 255, 255, 0.12);
    }

    .server-title-section {
        flex: 1;
    }

    .server-title {
        font-size: 18px;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 4px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .server-address {
        font-size: 14px;
        color: var(--text-secondary);
        font-family: 'Courier New', monospace;
        margin-top: 4px;
    }

    .server-status {
        display: inline-flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 4px;
        padding: 8px 14px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 600;
        text-align: right;
        min-width: 100px;
    }

    .server-status.active {
        background: rgba(16, 185, 129, 0.15);
        color: var(--success);
        border: 1px solid rgba(16, 185, 129, 0.3);
    }

    .server-status.standby {
        background: rgba(245, 158, 11, 0.15);
        color: var(--warning);
        border: 1px solid rgba(245, 158, 11, 0.3);
    }

    .server-status.unhealthy {
        background: rgba(248, 113, 113, 0.15);
        color: var(--error);
        border: 1px solid rgba(248, 113, 113, 0.3);
    }

    .server-info {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
        margin-bottom: 20px;
        padding: 20px;
        background: rgba(26, 26, 26, 0.3);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        border-radius: 8px;
        border: 1px solid rgba(255, 255, 255, 0.06);
        transition: all 0.2s;
    }

    .server-card:hover .server-info {
        background: rgba(26, 26, 26, 0.4);
        border-color: rgba(255, 255, 255, 0.1);
    }

    @media (max-width: 768px) {
        .server-info {
            grid-template-columns: 1fr;
        }
    }

    .info-item {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .info-label {
        font-size: 11px;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 600;
        margin-bottom: 2px;
    }

    .info-value {
        font-size: 15px;
        color: var(--text-primary);
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .info-value-icon {
        font-size: 16px;
    }

    .server-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        padding-top: 16px;
        border-top: 1px solid rgba(255, 255, 255, 0.08);
        margin-top: 20px;
        transition: all 0.2s;
    }

    .server-card:hover .server-actions {
        border-top-color: rgba(255, 255, 255, 0.12);
    }

    .server-actions .btn {
        flex: 1;
        min-width: 140px;
        justify-content: center;
    }

    .btn {
        padding: 8px 16px;
        border-radius: 6px;
        font-size: 13px;
        font-weight: 500;
        border: none;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s;
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

    .btn-success {
        background: rgba(16, 185, 129, 0.2);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        color: var(--success);
        border: 1px solid rgba(16, 185, 129, 0.3);
        box-shadow: 0 2px 8px rgba(16, 185, 129, 0.15);
    }

    .btn-success:hover {
        background: rgba(16, 185, 129, 0.3);
        border-color: rgba(16, 185, 129, 0.4);
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.25);
        transform: translateY(-1px);
    }

    .btn-warning {
        background: rgba(245, 158, 11, 0.2);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        color: var(--warning);
        border: 1px solid rgba(245, 158, 11, 0.3);
        box-shadow: 0 2px 8px rgba(245, 158, 11, 0.15);
    }

    .btn-warning:hover {
        background: rgba(245, 158, 11, 0.3);
        border-color: rgba(245, 158, 11, 0.4);
        box-shadow: 0 4px 12px rgba(245, 158, 11, 0.25);
        transform: translateY(-1px);
    }

    .btn-secondary {
        background: var(--bg-dark);
        color: var(--text-primary);
        border: 1px solid var(--border);
    }

    .btn-secondary:hover {
        background: var(--bg-hover);
        border-color: var(--border-light);
    }

    .btn-sm {
        padding: 10px 16px;
        font-size: 13px;
        font-weight: 600;
    }

    .servers-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 20px;
    }

    @media (min-width: 1200px) {
        .servers-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    .alert {
        padding: 12px 16px;
        border-radius: 8px;
        margin-bottom: 24px;
        background: rgba(16, 185, 129, 0.1);
        border: 1px solid rgba(16, 185, 129, 0.3);
        color: var(--success);
        font-size: 14px;
    }

    .alert-error {
        background: rgba(248, 113, 113, 0.1);
        border-color: rgba(248, 113, 113, 0.3);
        color: var(--error);
    }

    .page-actions {
        display: flex;
        gap: 12px;
        margin-top: 16px;
    }

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

    /* Progress Bar for Fail Count */
    .fail-count-progress {
        width: 100%;
        height: 6px;
        background: var(--bg-dark);
        border-radius: 3px;
        overflow: hidden;
        margin-top: 4px;
    }

    .fail-count-bar {
        height: 100%;
        background: var(--error);
        transition: width 0.3s ease;
        border-radius: 3px;
    }

    .fail-count-bar.warning {
        background: var(--warning);
    }

    .fail-count-bar.danger {
        background: var(--error);
    }

    /* Health Status Indicator */
    .health-indicator {
        display: inline-block;
        width: 8px;
        height: 8px;
        border-radius: 50%;
        margin-right: 6px;
        animation: pulse 2s infinite;
    }

    .health-indicator.healthy {
        background: var(--success);
        box-shadow: 0 0 8px rgba(16, 185, 129, 0.5);
    }

    .health-indicator.unhealthy {
        background: var(--error);
        box-shadow: 0 0 8px rgba(248, 113, 113, 0.5);
    }

    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.5; }
    }

    /* Priority Badge */
    .priority-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: var(--bg-dark);
        border: 2px solid var(--primary);
        color: var(--primary);
        font-weight: 700;
        font-size: 14px;
    }

    /* Auto-refresh indicator */
    .auto-refresh-indicator {
        position: fixed;
        bottom: 20px;
        right: 20px;
        background: rgba(30, 30, 30, 0.8);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 8px;
        padding: 8px 12px;
        font-size: 11px;
        color: var(--text-muted);
        z-index: 1000;
        display: flex;
        align-items: center;
        gap: 6px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
    }

    .refresh-spinner {
        width: 12px;
        height: 12px;
        border: 2px solid var(--border);
        border-top-color: var(--primary);
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        to { transform: rotate(360deg); }
    }


    /* Loading state for buttons */
    .btn.loading {
        opacity: 0.6;
        cursor: not-allowed;
        pointer-events: none;
    }

    .btn.loading::after {
        content: '';
        display: inline-block;
        width: 12px;
        height: 12px;
        border: 2px solid currentColor;
        border-top-color: transparent;
        border-radius: 50%;
        animation: spin 0.8s linear infinite;
        margin-left: 6px;
    }

    /* Improved Failover Mode Selector */
    .failover-mode-selector {
        background: rgba(30, 30, 30, 0.6);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 8px;
        padding: 16px;
        margin-top: 16px;
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
    }

    .failover-mode-selector label {
        font-size: 14px;
        font-weight: 600;
        color: var(--text-primary);
    }

    .failover-mode-selector select {
        padding: 8px 12px;
        background: var(--bg-dark);
        border: 1px solid var(--border);
        border-radius: 6px;
        color: var(--text-primary);
        font-size: 14px;
        cursor: pointer;
        transition: all 0.2s;
    }

    .failover-mode-selector select:hover {
        border-color: var(--primary);
    }

    .failover-mode-selector select:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(157, 78, 221, 0.1);
    }

    .failover-mode-info {
        font-size: 12px;
        color: var(--text-muted);
        flex: 1;
        min-width: 300px;
    }

    .failover-mode-badge {
        display: inline-block;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        margin-left: 8px;
    }

    .failover-mode-badge.auto {
        background: rgba(16, 185, 129, 0.15);
        color: var(--success);
        border: 1px solid rgba(16, 185, 129, 0.3);
    }

    .failover-mode-badge.manual {
        background: rgba(245, 158, 11, 0.15);
        color: var(--warning);
        border: 1px solid rgba(245, 158, 11, 0.3);
    }
</style>
@endsection

@section('scripts')
<script>
    // Auto-refresh every 30 seconds
    let refreshInterval;
    let timeUntilRefresh = 30;

    function startAutoRefresh() {
        const indicator = document.getElementById('auto-refresh-indicator');
        if (!indicator) return;

        refreshInterval = setInterval(() => {
            timeUntilRefresh--;
            indicator.textContent = `Auto-refresh in ${timeUntilRefresh}s`;
            
            if (timeUntilRefresh <= 0) {
                clearInterval(refreshInterval);
                window.location.reload();
            }
        }, 1000);
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

        // Start auto-refresh
        startAutoRefresh();
    });
</script>
@endsection

@section('content')
<div class="page-header">
    <h1 class="page-title">Backend Servers Management</h1>
    <p class="page-subtitle">
        View backend servers status and manage Failover for site: <strong>{{ $site->name }}</strong> ({{ $site->server_name }})
    </p>
    <a href="{{ route('sites.index') }}" class="back-link">← Back to Sites List</a>
    
    {{-- Failover Mode Selector --}}
    <div class="failover-mode-selector">
        <form method="POST" action="{{ route('sites.backends.failover-mode', $site) }}" style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap; flex: 1;">
            @csrf
            <label>Failover Mode:</label>
            <select name="failover_mode" onchange="this.form.submit()">
                <option value="auto" {{ $site->failover_mode === 'auto' ? 'selected' : '' }}>Automatic</option>
                <option value="manual" {{ $site->failover_mode === 'manual' ? 'selected' : '' }}>Manual</option>
            </select>
            <span class="failover-mode-badge {{ $site->failover_mode }}">
                {{ ucfirst($site->failover_mode) }}
            </span>
            <span class="failover-mode-info">
                @if($site->failover_mode === 'auto')
                    Failover will be performed automatically when a server fails 3 times consecutively.
                @else
                    Failover must be performed manually. Automatic failover is disabled.
                @endif
            </span>
        </form>
    </div>
    
    <div class="page-actions">
        <form method="POST" action="{{ route('sites.backends.check', $site) }}" style="display: inline;">
            @csrf
            <button type="submit" class="btn btn-primary">
                Check All Servers
            </button>
        </form>
        
        @if($backendServers->where('status', 'active')->count() > 0 && $backendServers->where('status', 'standby')->count() > 0)
            <form method="POST" action="{{ route('sites.backends.failover', $site) }}" style="display: inline;" 
                  onsubmit="return confirm('Are you sure you want to perform Failover?\n\nThe following will happen:\n- All active servers will be disabled\n- First standby server will be activated\n- Nginx configuration will be regenerated');">
                @csrf
                <button type="submit" class="btn btn-warning">
                    Perform Manual Failover
                </button>
            </form>
        @endif
    </div>
</div>

@if(session('status'))
    <div class="alert">
        {{ session('status') }}
    </div>
@endif

@if(session('error'))
    <div class="alert alert-error">
        {{ session('error') }}
    </div>
@endif

{{-- Statistics --}}
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-value">{{ $backendServers->count() }}</div>
        <div class="stat-label">Total Servers</div>
    </div>
    <div class="stat-card">
        <div class="stat-value" style="color: var(--success);">{{ $backendServers->where('status', 'active')->count() }}</div>
        <div class="stat-label">Active</div>
    </div>
    <div class="stat-card">
        <div class="stat-value" style="color: var(--warning);">{{ $backendServers->where('status', 'standby')->count() }}</div>
        <div class="stat-label">Standby</div>
    </div>
    <div class="stat-card">
        <div class="stat-value" style="color: var(--success);">{{ $backendServers->where('is_healthy', true)->count() }}</div>
        <div class="stat-label">Healthy</div>
    </div>
    <div class="stat-card">
        <div class="stat-value" style="color: var(--error);">{{ $backendServers->where('is_healthy', false)->count() }}</div>
        <div class="stat-label">Unhealthy</div>
    </div>
</div>

{{-- Servers List --}}
<div>
    <h2 style="font-size: 20px; font-weight: 700; color: var(--text-primary); margin-bottom: 24px; text-transform: uppercase; letter-spacing: 0.5px;">
        Backend Servers
    </h2>

    <div class="servers-grid">
    @forelse($backendServers as $server)
        <div class="server-card {{ $server->status }} {{ $server->is_healthy ? '' : 'unhealthy' }}">
            <div class="server-header">
                <div class="server-title-section">
                    <div class="server-title">
                        <span>Backend Server #{{ $loop->iteration }}</span>
                    </div>
                    <div class="server-address">
                        {{ $server->ip }}:{{ $server->port }}
                    </div>
                </div>
                <div class="server-status {{ $server->status }} {{ $server->is_healthy ? '' : 'unhealthy' }}">
                    @if($server->status === 'active')
                        <span style="font-size: 14px; font-weight: 700;">ACTIVE</span>
                        <span style="font-size: 11px; opacity: 0.8;">Active</span>
                    @else
                        <span style="font-size: 14px; font-weight: 700;">STANDBY</span>
                        <span style="font-size: 11px; opacity: 0.8;">Standby</span>
                    @endif
                    @if(!$server->is_healthy)
                        <span style="font-size: 11px; margin-top: 4px; display: block; color: var(--error);">Unhealthy</span>
                    @endif
                </div>
            </div>

            <div class="server-info">
                <div class="info-item">
                    <div class="info-label">Status</div>
                    <div class="info-value" style="color: {{ $server->status === 'active' ? 'var(--success)' : 'var(--warning)' }};">
                        {{ $server->status === 'active' ? 'Active' : 'Standby' }}
                    </div>
                </div>
                <div class="info-item">
                    <div class="info-label">Health</div>
                    <div class="info-value" style="color: {{ $server->is_healthy ? 'var(--success)' : 'var(--error)' }};">
                        <span class="health-indicator {{ $server->is_healthy ? 'healthy' : 'unhealthy' }}"></span>
                        {{ $server->is_healthy ? 'Healthy' : 'Unhealthy' }}
                    </div>
                </div>
                <div class="info-item">
                    <div class="info-label">Priority</div>
                    <div class="info-value">
                        <span class="priority-badge">{{ $server->priority }}</span>
                    </div>
                </div>
                <div class="info-item">
                    <div class="info-label">Fail Count</div>
                    <div class="info-value" style="color: {{ $server->fail_count > 0 ? 'var(--error)' : 'var(--text-primary)' }};">
                        {{ $server->fail_count }} / 3
                        <div class="fail-count-progress">
                            <div class="fail-count-bar {{ $server->fail_count >= 3 ? 'danger' : ($server->fail_count >= 2 ? 'warning' : '') }}" 
                                 style="width: {{ min(($server->fail_count / 3) * 100, 100) }}%"></div>
                        </div>
                    </div>
                </div>
                <div class="info-item">
                    <div class="info-label">Last Check</div>
                    <div class="info-value" style="font-size: 13px;">
                        {{ $server->last_health_check ? $server->last_health_check->diffForHumans() : 'Never' }}
                    </div>
                </div>
                <div class="info-item">
                    <div class="info-label">Health Check</div>
                    <div class="info-value" style="color: {{ $server->health_check_enabled ? 'var(--success)' : 'var(--text-muted)' }};">
                        {{ $server->health_check_enabled ? 'Enabled' : 'Disabled' }}
                    </div>
                </div>
            </div>

            <div class="server-actions">
                <form method="POST" action="{{ route('sites.backends.check-single', [$site, $server]) }}" style="flex: 1; min-width: 140px;">
                    @csrf
                    <button type="submit" class="btn btn-secondary btn-sm" style="width: 100%;">
                        Check Health
                    </button>
                </form>

                @if($server->status === 'active')
                    <form method="POST" action="{{ route('sites.backends.toggle-status', [$site, $server]) }}" style="flex: 1; min-width: 140px;">
                        @csrf
                        <button type="submit" class="btn btn-warning btn-sm" style="width: 100%;"
                                onclick="return confirm('Are you sure you want to switch this server to Standby?')">
                            Switch to Standby
                        </button>
                    </form>
                @else
                    <form method="POST" action="{{ route('sites.backends.toggle-status', [$site, $server]) }}" style="flex: 1; min-width: 140px;">
                        @csrf
                        <button type="submit" class="btn btn-success btn-sm" style="width: 100%;">
                            Activate (Active)
                        </button>
                    </form>
                @endif
            </div>
        </div>
    @empty
        <div class="card" style="text-align: center; padding: 60px 40px; color: var(--text-muted);">
            <p style="font-size: 16px; margin-bottom: 20px;">No backend servers for this site.</p>
            <a href="{{ route('sites.index') }}" class="btn btn-primary">
                Back to Sites List
            </a>
        </div>
    @endforelse
    </div>
</div>

{{-- Auto-refresh Indicator --}}
<div id="auto-refresh-indicator" class="auto-refresh-indicator">
    <div class="refresh-spinner"></div>
    <span>Auto-refresh in 30s</span>
</div>
@endsection

