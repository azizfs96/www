@extends('layouts.waf')

@section('title', 'Sites Management')

@section('styles')
<style>
    /* Enhanced Game-style Design - Same as Events Page */
    :root {
        --bg-dark: #1A1A1A;
        --bg-card: #1E1E1E;
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

    .page-actions {
        display: flex;
        gap: 12px;
        margin-top: 20px;
        flex-wrap: wrap;
    }

    .btn {
        padding: 10px 20px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        border: none;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .btn-primary {
        background: var(--primary);
        color: white;
        border: 1px solid rgba(157, 78, 221, 0.3);
    }

    .btn-primary:hover {
        background: #B06FE8;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(157, 78, 221, 0.3);
    }

    .btn-info {
        background: #3b82f6;
        color: white;
        border: 1px solid rgba(59, 130, 246, 0.3);
    }

    .btn-info:hover {
        background: #2563eb;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
    }

    .btn-secondary {
        background: var(--bg-card);
        color: var(--text-primary);
        border: 1px solid var(--border);
    }

    .btn-secondary:hover {
        background: var(--bg-hover);
        border-color: var(--border-light);
    }

    .btn-sm {
        padding: 6px 12px;
        font-size: 11px;
    }

    .btn-danger {
        background: rgba(248, 113, 113, 0.2);
        color: var(--error);
        border: 1px solid rgba(248, 113, 113, 0.4);
    }

    .btn-danger:hover {
        background: rgba(248, 113, 113, 0.3);
        border-color: rgba(248, 113, 113, 0.6);
    }

    .btn-success {
        background: rgba(74, 222, 128, 0.15);
        color: var(--success);
        border: 1px solid rgba(74, 222, 128, 0.3);
    }

    .btn-success:hover {
        background: rgba(74, 222, 128, 0.25);
        border-color: rgba(74, 222, 128, 0.5);
    }

    .btn-warning {
        background: rgba(251, 191, 36, 0.15);
        color: var(--warning);
        border: 1px solid rgba(251, 191, 36, 0.3);
    }

    .btn-warning:hover {
        background: rgba(251, 191, 36, 0.25);
        border-color: rgba(251, 191, 36, 0.5);
    }

    .alert {
        padding: 14px 18px;
        border-radius: 8px;
        margin-bottom: 24px;
        font-size: 13px;
        border: 1px solid;
        background: rgba(74, 222, 128, 0.1);
        border-color: rgba(74, 222, 128, 0.3);
        color: var(--success);
    }

    .alert-error {
        background: rgba(248, 113, 113, 0.1);
        border-color: rgba(248, 113, 113, 0.3);
        color: var(--error);
    }

    /* Site Card Design */
    .sites-container {
        background: #1E1E1E;
        border-radius: 12px;
        border: 1px solid var(--border);
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        display: flex;
        flex-direction: column;
        gap: 0;
    }
    
    .sites-container > .site-card {
        border-radius: 0;
        border: none;
        border-bottom: 1px solid var(--border);
    }
    
    .sites-container > .site-card:last-child {
        border-bottom: none;
    }

    .site-card {
        background: #1E1E1E;
        border: 1px solid var(--border);
        border-radius: 12px;
        overflow: hidden;
        transition: all 0.2s ease;
        direction: ltr;
    }

    .site-card:hover {
        background: var(--bg-hover);
        border-color: var(--border-light);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
    }

    .site-card-header {
        background: #1E1E1E;
        padding: 20px 24px;
        border-bottom: 1px solid var(--border);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 16px;
    }

    .site-info {
        flex: 1;
        min-width: 0;
    }

    .site-name {
        font-size: 18px;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 6px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .site-domain {
        font-size: 13px;
        color: var(--text-secondary);
        font-family: 'Courier New', monospace;
        margin-top: 4px;
    }

    .site-status-badges {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        align-items: center;
    }

    .badge {
        display: inline-flex;
        align-items: center;
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 700;
        gap: 6px;
        border: 1px solid;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        white-space: nowrap;
    }

    .badge-success {
        background: rgba(74, 222, 128, 0.15);
        color: var(--success);
        border-color: rgba(74, 222, 128, 0.3);
    }

    .badge-warning {
        background: rgba(251, 191, 36, 0.15);
        color: var(--warning);
        border-color: rgba(251, 191, 36, 0.3);
    }

    .badge-info {
        background: rgba(157, 78, 221, 0.15);
        color: var(--primary);
        border-color: rgba(157, 78, 221, 0.3);
    }

    .badge-error {
        background: rgba(248, 113, 113, 0.15);
        color: var(--error);
        border-color: rgba(248, 113, 113, 0.3);
    }

    .site-details {
        padding: 20px 24px;
        background: var(--bg-details);
        border-top: 1px solid var(--border);
        display: grid;
        grid-template-columns: auto 1fr;
        gap: 20px;
        align-items: start;
    }

    .site-details-label {
        font-size: 11px;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.8px;
        min-width: 120px;
        font-weight: 700;
        padding-top: 2px;
    }

    .site-details-content {
        display: flex;
        flex-wrap: wrap;
        gap: 16px;
        font-size: 12px;
        color: var(--text-secondary);
        align-items: center;
    }

    .site-detail-item {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 6px 12px;
        background: rgba(255, 255, 255, 0.03);
        border-radius: 6px;
        border: 1px solid rgba(255, 255, 255, 0.05);
    }

    .site-detail-label {
        color: var(--text-muted);
        font-weight: 600;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .site-detail-value {
        color: var(--text-primary);
        font-weight: 600;
        font-family: 'Courier New', monospace;
        font-size: 12px;
    }

    .site-actions {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        align-items: center;
    }

    .empty-state {
        padding: 80px 24px;
        text-align: center;
        color: var(--text-muted);
        font-size: 14px;
        background: #1E1E1E;
        border: 1px solid var(--border);
        border-radius: 12px;
    }

    .info-box {
        margin-top: 24px;
        padding: 20px 24px;
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: 12px;
    }

    .info-box h3 {
        font-size: 14px;
        color: var(--text-primary);
        margin-bottom: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .info-box ul {
        font-size: 13px;
        color: var(--text-secondary);
        line-height: 1.8;
        margin: 0;
        padding-left: 20px;
    }

    .info-box code {
        background: var(--bg-dark);
        padding: 2px 6px;
        border-radius: 4px;
        font-family: 'Courier New', monospace;
        font-size: 12px;
        color: var(--primary);
    }
</style>
@endsection

@section('content')
<div class="page-header">
    <h1>Sites Management</h1>
    <p class="page-subtitle">
        Add and manage sites protected by WAF Gateway. Nginx configuration files are automatically generated when creating or modifying a site.
    </p>
    
    <div class="page-actions">
        <a href="{{ route('sites.create') }}" class="btn btn-primary">
            ➕ Add New Site
        </a>
        <form method="POST" action="{{ route('sites.regenerate') }}" style="display: inline;">
            @csrf
            <button type="submit" class="btn btn-secondary">
                🔄 Regenerate All Files
            </button>
        </form>
    </div>
</div>

@if(session('status'))
    <div class="alert">
        ✓ {{ session('status') }}
    </div>
@endif

@if(session('error'))
    <div class="alert alert-error">
        ⚠️ {{ session('error') }}
    </div>
@endif

<div class="sites-container">
    @forelse($sites as $site)
        <div class="site-card">
            <div class="site-card-header">
                <div class="site-info">
                    <div class="site-name">
                        {{ $site->name }}
                    </div>
                    <div class="site-domain">{{ $site->server_name }}</div>
                </div>
                
                <div class="site-status-badges">
                    @if($site->enabled)
                        <span class="badge badge-success">✓ Enabled</span>
                    @else
                        <span class="badge badge-warning">⏸ Disabled</span>
                    @endif
                    
                    @if($site->ssl_enabled)
                        <span class="badge badge-info">🔒 HTTPS</span>
                    @else
                        <span class="badge badge-warning">HTTP</span>
                    @endif
                </div>
            </div>
            
            <div class="site-details">
                <div class="site-details-label">Backend</div>
                <div class="site-details-content">
                    <div class="site-detail-item">
                        <span class="site-detail-label">IP:</span>
                        <span class="site-detail-value">{{ $site->backend_ip }}</span>
                    </div>
                    <div class="site-detail-item">
                        <span class="site-detail-label">Port:</span>
                        <span class="site-detail-value">{{ $site->backend_port }}</span>
                    </div>
                </div>
                
                <div class="site-details-label">SSL Certificate</div>
                <div class="site-details-content">
                    @if($site->ssl_enabled)
                        @php
                            $certPath = "/etc/letsencrypt/live/{$site->server_name}/fullchain.pem";
                            $keyPath = "/etc/letsencrypt/live/{$site->server_name}/privkey.pem";
                            $certExists = file_exists($certPath) && file_exists($keyPath);
                        @endphp
                        @if($certExists)
                            <div class="site-detail-item">
                                <span class="site-detail-label">Status:</span>
                                <span class="site-detail-value" style="color: var(--success);">✓ Available</span>
                            </div>
                            <div class="site-detail-item">
                                <span class="site-detail-label">Path:</span>
                                <span class="site-detail-value">{{ $site->ssl_cert_path }}</span>
                            </div>
                        @else
                            <div class="site-detail-item">
                                <span class="site-detail-label">Status:</span>
                                <span class="site-detail-value" style="color: var(--error);">✗ Missing</span>
                            </div>
                        @endif
                    @else
                        <div class="site-detail-item">
                            <span class="site-detail-label">Status:</span>
                            <span class="site-detail-value">Disabled</span>
                        </div>
                    @endif
                </div>
                
                <div class="site-details-label">Actions</div>
                <div class="site-details-content">
                    <div class="site-actions">
                        <a href="{{ route('sites.backends', $site) }}" class="btn btn-sm btn-info" 
                           title="عرض حالة السيرفرات الخلفية وإدارة Failover">
                            🖥️ Backend Servers
                        </a>
                        <a href="{{ route('sites.policy.edit', $site) }}" class="btn btn-sm btn-primary">
                            ⚙️ WAF Settings
                        </a>

                        @if($site->ssl_enabled)
                            @if(!$certExists)
                                <form method="POST" action="{{ route('sites.fix-ssl', $site) }}" style="display: inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-warning" 
                                            title="Fix SSL: Generate missing certificate">
                                        🔧 Fix SSL
                                    </button>
                                </form>
                            @endif
                        @endif
                        
                        <form method="POST" action="{{ route('sites.toggle-ssl', $site) }}" style="display: inline;">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-sm {{ $site->ssl_enabled ? 'btn-warning' : 'btn-success' }}" 
                                    title="{{ $site->ssl_enabled ? 'Disable SSL' : 'Enable SSL and generate certificate' }}">
                                {{ $site->ssl_enabled ? '🔓 Disable SSL' : '🔒 Enable SSL' }}
                            </button>
                        </form>

                        <form method="POST" action="{{ route('sites.toggle', $site) }}" style="display: inline;">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-sm {{ $site->enabled ? 'btn-warning' : 'btn-success' }}">
                                {{ $site->enabled ? '⏸ Disable' : '▶ Enable' }}
                            </button>
                        </form>

                        <form method="POST" action="{{ route('sites.destroy', $site) }}" 
                              onsubmit="return confirm('Are you sure you want to delete this site? Nginx files and SSL certificates will also be deleted.')"
                              style="display: inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">
                                🗑 Delete
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="empty-state">
            No sites currently. Add a new site to get started.
        </div>
    @endforelse
</div>

@endsection


