@extends('layouts.waf')

@section('title', 'Add User to Tenant')

@section('styles')
<style>
    :root {
        --bg-dark: #1A1A1A;
        --bg-card: #1E1E1E;
        --border: #333333;
        --text-primary: #E5E5E5;
        --text-muted: #808080;
        --primary: #9D4EDD;
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
    }

    .form-group {
        margin-bottom: 24px;
    }

    .form-label {
        display: block;
        font-size: 13px;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 8px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .form-input, .form-select {
        width: 100%;
        padding: 12px 16px;
        background: var(--bg-dark);
        border: 1px solid var(--border);
        border-radius: 8px;
        color: var(--text-primary);
        font-size: 14px;
    }

    .form-input:focus, .form-select:focus {
        outline: none;
        border-color: var(--primary);
    }

    .btn {
        padding: 12px 24px;
        border-radius: 8px;
        font-size: 14px;
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

    .btn-secondary {
        background: var(--bg-dark);
        color: var(--text-primary);
        border: 1px solid var(--border);
    }
</style>
@endsection

@section('content')
<div class="page-header">
    <h1>➕ Add User to Tenant</h1>
    <div style="color: var(--text-muted); font-size: 14px; margin-bottom: 8px;">{{ $tenant->name }}</div>
    <a href="{{ route('tenants.users.index', $tenant) }}" style="color: var(--text-muted); text-decoration: none; font-size: 14px;">
        ← Back to Users
    </a>
</div>

<div class="card">
    <form method="POST" action="{{ route('tenants.users.store', $tenant) }}">
        @csrf

        <div class="form-group">
            <label class="form-label" for="name">Name *</label>
            <input type="text" id="name" name="name" class="form-input" 
                   value="{{ old('name') }}" required>
        </div>

        <div class="form-group">
            <label class="form-label" for="email">Email *</label>
            <input type="email" id="email" name="email" class="form-input" 
                   value="{{ old('email') }}" required>
        </div>

        <div class="form-group">
            <label class="form-label" for="password">Password *</label>
            <input type="password" id="password" name="password" class="form-input" 
                   required minlength="8">
        </div>

        <div class="form-group">
            <label class="form-label" for="role">Role *</label>
            <select id="role" name="role" class="form-select" required>
                <option value="user" {{ old('role') === 'user' ? 'selected' : '' }}>User</option>
                <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Admin</option>
            </select>
        </div>

        @if($errors->any())
            <div style="padding: 12px 16px; background: rgba(248, 113, 113, 0.1); border: 1px solid rgba(248, 113, 113, 0.3); border-radius: 8px; color: #F87171; margin-bottom: 24px;">
                <ul style="margin: 0; padding-left: 20px;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div style="display: flex; gap: 12px; margin-top: 32px; direction: ltr;">
            <button type="submit" class="btn btn-primary">
                💾 Add User
            </button>
            <a href="{{ route('tenants.users.index', $tenant) }}" class="btn btn-secondary">
                ✕ Cancel
            </a>
        </div>
    </form>
</div>
@endsection

