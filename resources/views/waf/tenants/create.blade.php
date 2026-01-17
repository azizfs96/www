@extends('layouts.waf')

@section('title', 'Create Tenant')

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

    .form-input {
        width: 100%;
        padding: 12px 16px;
        background: var(--bg-dark);
        border: 1px solid var(--border);
        border-radius: 8px;
        color: var(--text-primary);
        font-size: 14px;
        transition: all 0.2s;
    }

    .form-input:focus {
        outline: none;
        border-color: var(--primary);
        background: var(--bg-hover);
        box-shadow: 0 0 0 3px rgba(157, 78, 221, 0.1);
    }

    .form-textarea {
        width: 100%;
        padding: 12px 16px;
        background: var(--bg-dark);
        border: 1px solid var(--border);
        border-radius: 8px;
        color: var(--text-primary);
        font-size: 14px;
        min-height: 100px;
        resize: vertical;
    }

    .form-help {
        font-size: 12px;
        color: var(--text-muted);
        margin-top: 6px;
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
        transition: all 0.2s;
    }

    .btn-primary {
        background: var(--primary);
        color: white;
    }

    .btn-primary:hover {
        background: var(--primary-hover);
        transform: translateY(-1px);
    }

    .btn-secondary {
        background: var(--bg-dark);
        color: var(--text-primary);
        border: 1px solid var(--border);
    }

    .section-title {
        font-size: 18px;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 20px;
        padding-bottom: 12px;
        border-bottom: 1px solid var(--border);
    }
</style>
@endsection

@section('content')
<div class="page-header">
    <h1>➕ Create New Tenant</h1>
    <a href="{{ route('tenants.index') }}" style="color: var(--text-muted); text-decoration: none; font-size: 14px;">
        ← Back to Tenants
    </a>
</div>

<div class="card">
    <form method="POST" action="{{ route('tenants.store') }}">
        @csrf

        <h2 class="section-title" style="direction: ltr; text-align: left;">Tenant Information</h2>

        <div class="form-group">
            <label class="form-label" for="name">Tenant Name *</label>
            <input type="text" id="name" name="name" class="form-input" 
                   value="{{ old('name') }}" required>
            <div class="form-help">Display name for the tenant</div>
        </div>

        <div class="form-group">
            <label class="form-label" for="slug">Slug</label>
            <input type="text" id="slug" name="slug" class="form-input" 
                   value="{{ old('slug') }}" placeholder="auto-generated">
            <div class="form-help">URL-friendly identifier (auto-generated if empty)</div>
        </div>

        <div class="form-group">
            <label class="form-label" for="description">Description</label>
            <textarea id="description" name="description" class="form-textarea">{{ old('description') }}</textarea>
        </div>

        <h2 class="section-title" style="direction: ltr; text-align: left; margin-top: 32px;">Tenant Admin Account</h2>

        <div class="form-group">
            <label class="form-label" for="admin_name">Admin Name *</label>
            <input type="text" id="admin_name" name="admin_name" class="form-input" 
                   value="{{ old('admin_name') }}" required>
        </div>

        <div class="form-group">
            <label class="form-label" for="admin_email">Admin Email *</label>
            <input type="email" id="admin_email" name="admin_email" class="form-input" 
                   value="{{ old('admin_email') }}" required>
        </div>

        <div class="form-group">
            <label class="form-label" for="admin_password">Admin Password *</label>
            <input type="password" id="admin_password" name="admin_password" class="form-input" 
                   required minlength="8">
            <div class="form-help">Minimum 8 characters</div>
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
                💾 Create Tenant
            </button>
            <a href="{{ route('tenants.index') }}" class="btn btn-secondary">
                ✕ Cancel
            </a>
        </div>
    </form>
</div>
@endsection

