@extends('layouts.waf')

@section('title', 'Tenant Details - ' . $tenant->name)

@section('styles')
<style>
    :root {
        --bg-dark: #1A1A1A;
        --bg-card: #1E1E1E;
        --border: #333333;
        --text-primary: #E5E5E5;
        --text-secondary: #B3B3B3;
        --text-muted: #808080;
        --primary: #9D4EDD;
        --success: #4ADE80;
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

    .card {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 32px;
        margin-bottom: 24px;
    }

    .detail-row {
        display: grid;
        grid-template-columns: 150px 1fr;
        gap: 16px;
        padding: 12px 0;
        border-bottom: 1px solid var(--border);
    }

    .detail-row:last-child {
        border-bottom: none;
    }

    .detail-label {
        font-size: 12px;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 600;
    }

    .detail-value {
        font-size: 14px;
        color: var(--text-primary);
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
    }

    .btn-primary {
        background: var(--primary);
        color: white;
    }
</style>
@endsection

@section('content')
<div class="page-header">
    <h1>🏢 {{ $tenant->name }}</h1>
    <a href="{{ route('tenants.index') }}" style="color: var(--text-muted); text-decoration: none; font-size: 14px;">
        ← Back to Tenants
    </a>
</div>

<div class="card">
    <h2 style="font-size: 18px; color: var(--text-primary); margin-bottom: 20px; direction: ltr; text-align: left;">Tenant Information</h2>
    
    <div class="detail-row">
        <div class="detail-label">Name</div>
        <div class="detail-value">{{ $tenant->name }}</div>
    </div>
    
    <div class="detail-row">
        <div class="detail-label">Slug</div>
        <div class="detail-value" style="font-family: 'Courier New', monospace;">{{ $tenant->slug }}</div>
    </div>
    
    <div class="detail-row">
        <div class="detail-label">Status</div>
        <div class="detail-value">
            @if($tenant->active)
                <span style="color: var(--success);">✓ Active</span>
            @else
                <span style="color: var(--text-muted);">⏸ Inactive</span>
            @endif
        </div>
    </div>
    
    @if($tenant->description)
    <div class="detail-row">
        <div class="detail-label">Description</div>
        <div class="detail-value">{{ $tenant->description }}</div>
    </div>
    @endif
    
    <div class="detail-row">
        <div class="detail-label">Admin</div>
        <div class="detail-value">
            {{ $tenant->admin->name ?? 'N/A' }} ({{ $tenant->admin->email ?? 'N/A' }})
        </div>
    </div>
    
    <div class="detail-row">
        <div class="detail-label">Users Count</div>
        <div class="detail-value">{{ $tenant->users()->count() }}</div>
    </div>
    
    <div class="detail-row">
        <div class="detail-label">Sites Count</div>
        <div class="detail-value">{{ $tenant->sites()->count() }}</div>
    </div>
    
    <div class="detail-row">
        <div class="detail-label">Created At</div>
        <div class="detail-value">{{ $tenant->created_at->format('Y-m-d H:i:s') }}</div>
    </div>
</div>

<div style="display: flex; gap: 12px; direction: ltr;">
    <a href="{{ route('tenants.edit', $tenant) }}" class="btn btn-primary">
        ✏️ Edit Tenant
    </a>
    <a href="{{ route('tenants.users.index', $tenant) }}" class="btn btn-primary">
        👥 Manage Users
    </a>
</div>
@endsection

