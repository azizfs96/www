@extends('layouts.waf')

@section('title', 'Tenants Management')

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
        --success: #4ADE80;
        --error: #F87171;
        --warning: #FBBF24;
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

    .btn-danger {
        background: rgba(248, 113, 113, 0.2);
        color: var(--error);
        border: 1px solid rgba(248, 113, 113, 0.4);
    }

    .btn-danger:hover {
        background: rgba(248, 113, 113, 0.3);
    }

    .tenants-container {
        display: grid;
        gap: 20px;
    }

    .tenant-card {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 24px;
        transition: all 0.2s;
    }

    .tenant-card:hover {
        border-color: var(--border-light);
        background: var(--bg-hover);
    }

    .tenant-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 16px;
    }

    .tenant-name {
        font-size: 20px;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 4px;
    }

    .tenant-slug {
        font-size: 13px;
        color: var(--text-muted);
        font-family: 'Courier New', monospace;
    }

    .tenant-badges {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    .badge {
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .badge-success {
        background: rgba(74, 222, 128, 0.15);
        color: var(--success);
        border: 1px solid rgba(74, 222, 128, 0.3);
    }

    .badge-warning {
        background: rgba(251, 191, 36, 0.15);
        color: var(--warning);
        border: 1px solid rgba(251, 191, 36, 0.3);
    }

    .tenant-details {
        margin-top: 16px;
        padding-top: 16px;
        border-top: 1px solid var(--border);
    }

    .tenant-detail-item {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 8px;
        font-size: 13px;
        color: var(--text-secondary);
    }

    .tenant-actions {
        display: flex;
        gap: 8px;
        margin-top: 16px;
    }

    .btn-sm {
        padding: 6px 12px;
        font-size: 11px;
    }

    .alert-success {
        padding: 12px 16px;
        background: rgba(74, 222, 128, 0.1);
        border: 1px solid rgba(74, 222, 128, 0.3);
        border-radius: 8px;
        color: var(--success);
        margin-bottom: 24px;
        direction: ltr;
        text-align: left;
    }
</style>
@endsection

@section('content')
<div class="page-header">
    <h1>🏢 Tenants Management</h1>
    <div class="page-actions">
        <a href="{{ route('tenants.create') }}" class="btn btn-primary">
            ➕ Create New Tenant
        </a>
    </div>
</div>

@if(session('status'))
    <div class="alert-success">
        ✓ {{ session('status') }}
    </div>
@endif

<div class="tenants-container">
    @forelse($tenants as $tenant)
        <div class="tenant-card">
            <div class="tenant-header">
                <div>
                    <div class="tenant-name">{{ $tenant->name }}</div>
                    <div class="tenant-slug">{{ $tenant->slug }}</div>
                </div>
                <div class="tenant-badges">
                    @if($tenant->active)
                        <span class="badge badge-success">Active</span>
                    @else
                        <span class="badge badge-warning">Inactive</span>
                    @endif
                </div>
            </div>

            @if($tenant->description)
                <p style="color: var(--text-secondary); font-size: 14px; margin-bottom: 16px;">
                    {{ $tenant->description }}
                </p>
            @endif

            <div class="tenant-details">
                <div class="tenant-detail-item">
                    <span>👤 Admin:</span>
                    <strong style="color: var(--text-primary);">{{ $tenant->admin->name ?? 'N/A' }}</strong>
                    <span style="color: var(--text-muted);">({{ $tenant->admin->email ?? 'N/A' }})</span>
                </div>
                <div class="tenant-detail-item">
                    <span>👥 Users:</span>
                    <strong style="color: var(--text-primary);">{{ $tenant->users()->count() }}</strong>
                </div>
                <div class="tenant-detail-item">
                    <span>🌐 Sites:</span>
                    <strong style="color: var(--text-primary);">{{ $tenant->sites()->count() }}</strong>
                </div>
                <div class="tenant-detail-item">
                    <span>📅 Created:</span>
                    <span style="color: var(--text-muted);">{{ $tenant->created_at->format('Y-m-d') }}</span>
                </div>
            </div>

            <div class="tenant-actions">
                <a href="{{ route('tenants.show', $tenant) }}" class="btn btn-primary btn-sm">
                    👁️ View
                </a>
                <a href="{{ route('tenants.edit', $tenant) }}" class="btn btn-primary btn-sm">
                    ✏️ Edit
                </a>
                <a href="{{ route('tenants.users.index', $tenant) }}" class="btn btn-primary btn-sm">
                    👥 Users
                </a>
                <form method="POST" action="{{ route('tenants.destroy', $tenant) }}" 
                      onsubmit="return confirm('Are you sure you want to delete this tenant?')"
                      style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm">
                        🗑️ Delete
                    </button>
                </form>
            </div>
        </div>
    @empty
        <div style="text-align: center; padding: 60px 20px; color: var(--text-muted);">
            <div style="font-size: 48px; margin-bottom: 20px;">🏢</div>
            <div style="font-size: 18px; margin-bottom: 8px;">No tenants yet</div>
            <div style="font-size: 14px;">Create your first tenant to get started</div>
        </div>
    @endforelse
</div>
@endsection

