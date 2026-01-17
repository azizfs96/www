@extends('layouts.waf')

@section('title', 'Tenant Users - ' . $tenant->name)

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
        --error: #F87171;
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
    }

    .btn-primary {
        background: var(--primary);
        color: white;
    }

    .btn-danger {
        background: rgba(248, 113, 113, 0.2);
        color: var(--error);
        border: 1px solid rgba(248, 113, 113, 0.4);
    }

    .users-container {
        display: grid;
        gap: 16px;
    }

    .user-card {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .user-info {
        flex: 1;
    }

    .user-name {
        font-size: 16px;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 4px;
    }

    .user-email {
        font-size: 13px;
        color: var(--text-muted);
    }

    .user-role {
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        background: rgba(157, 78, 221, 0.15);
        color: var(--primary);
        border: 1px solid rgba(157, 78, 221, 0.3);
    }

    .user-actions {
        display: flex;
        gap: 8px;
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
        color: #4ADE80;
        margin-bottom: 24px;
        direction: ltr;
        text-align: left;
    }
</style>
@endsection

@section('content')
<div class="page-header">
    <h1>👥 Tenant Users</h1>
    <div style="color: var(--text-muted); font-size: 14px; margin-bottom: 8px;">{{ $tenant->name }}</div>
    <div class="page-actions">
        <a href="{{ route('tenants.users.create', $tenant) }}" class="btn btn-primary">
            ➕ Add User
        </a>
        <a href="{{ route('tenants.index') }}" style="color: var(--text-muted); text-decoration: none; font-size: 14px;">
            ← Back to Tenants
        </a>
    </div>
</div>

@if(session('status'))
    <div class="alert-success">
        ✓ {{ session('status') }}
    </div>
@endif

@if(session('errors'))
    <div style="padding: 12px 16px; background: rgba(248, 113, 113, 0.1); border: 1px solid rgba(248, 113, 113, 0.3); border-radius: 8px; color: var(--error); margin-bottom: 24px;">
        {{ session('errors')->first('error') }}
    </div>
@endif

<div class="users-container">
    @forelse($users as $user)
        <div class="user-card">
            <div class="user-info">
                <div class="user-name">{{ $user->name }}</div>
                <div class="user-email">{{ $user->email }}</div>
            </div>
            <div style="display: flex; align-items: center; gap: 12px;">
                <span class="user-role">{{ $user->pivot->role }}</span>
                @if($tenant->admin_id !== $user->id)
                    <form method="POST" action="{{ route('tenants.users.destroy', [$tenant, $user]) }}" 
                          onsubmit="return confirm('Are you sure you want to remove this user?')"
                          style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm">
                            🗑️ Remove
                        </button>
                    </form>
                @else
                    <span style="font-size: 11px; color: var(--text-muted);">Tenant Admin</span>
                @endif
            </div>
        </div>
    @empty
        <div style="text-align: center; padding: 60px 20px; color: var(--text-muted);">
            <div style="font-size: 48px; margin-bottom: 20px;">👥</div>
            <div style="font-size: 18px; margin-bottom: 8px;">No users yet</div>
            <div style="font-size: 14px;">Add users to this tenant</div>
        </div>
    @endforelse
</div>
@endsection

