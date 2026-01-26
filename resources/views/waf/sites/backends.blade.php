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
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 24px;
        margin-bottom: 24px;
    }

    .server-card {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 24px;
        margin-bottom: 20px;
        transition: all 0.2s;
        position: relative;
        overflow: hidden;
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
        border-color: var(--border-light);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
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
        border-color: var(--success);
        background: linear-gradient(135deg, rgba(16, 185, 129, 0.05) 0%, var(--bg-card) 100%);
    }

    .server-card.standby {
        border-color: var(--warning);
        background: linear-gradient(135deg, rgba(245, 158, 11, 0.05) 0%, var(--bg-card) 100%);
    }

    .server-card.unhealthy {
        border-color: var(--error);
        background: linear-gradient(135deg, rgba(248, 113, 113, 0.05) 0%, var(--bg-card) 100%);
    }

    .server-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 20px;
        padding-bottom: 16px;
        border-bottom: 1px solid var(--border);
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
        background: var(--bg-dark);
        border-radius: 8px;
        border: 1px solid var(--border);
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
        border-top: 1px solid var(--border);
        margin-top: 20px;
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
        background: var(--primary);
        color: white;
    }

    .btn-primary:hover {
        background: var(--primary-hover);
    }

    .btn-success {
        background: var(--success);
        color: white;
    }

    .btn-success:hover {
        background: #059669;
    }

    .btn-warning {
        background: var(--warning);
        color: white;
    }

    .btn-warning:hover {
        background: #d97706;
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
        background: var(--bg-dark);
        border: 1px solid var(--border);
        border-radius: 8px;
        padding: 16px;
        text-align: center;
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
</style>
@endsection

@section('content')
<div class="page-header">
    <h1 class="page-title">Backend Servers Management</h1>
    <p class="page-subtitle">
        View backend servers status and manage Failover for site: <strong>{{ $site->name }}</strong> ({{ $site->server_name }})
    </p>
    <a href="{{ route('sites.index') }}" class="back-link">← Back to Sites List</a>
    
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
                        {{ $server->is_healthy ? 'Healthy' : 'Unhealthy' }}
                    </div>
                </div>
                <div class="info-item">
                    <div class="info-label">Priority</div>
                    <div class="info-value">
                        {{ $server->priority }}
                    </div>
                </div>
                <div class="info-item">
                    <div class="info-label">Fail Count</div>
                    <div class="info-value" style="color: {{ $server->fail_count > 0 ? 'var(--error)' : 'var(--text-primary)' }};">
                        {{ $server->fail_count }}
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
@endsection

