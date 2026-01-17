@extends('layouts.waf')

@section('title', 'Edit Tenant - ' . $tenant->name)

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

    .form-input {
        width: 100%;
        padding: 12px 16px;
        background: var(--bg-dark);
        border: 1px solid var(--border);
        border-radius: 8px;
        color: var(--text-primary);
        font-size: 14px;
    }

    .form-input:focus {
        outline: none;
        border-color: var(--primary);
    }

    .form-textarea {
        width: 100%;
        padding: 12px 16px;
        background: var(--bg-dark);
        border: 1px solid var(--border);
        border-radius: 8px;
        color: var(--text-primary);
        min-height: 100px;
        resize: vertical;
    }

    .checkbox-item {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .checkbox-item input[type="checkbox"] {
        width: 18px;
        height: 18px;
        accent-color: var(--primary);
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
    <h1>✏️ Edit Tenant</h1>
    <a href="{{ route('tenants.index') }}" style="color: var(--text-muted); text-decoration: none; font-size: 14px;">
        ← Back to Tenants
    </a>
</div>

<div class="card">
    <form method="POST" action="{{ route('tenants.update', $tenant) }}">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label class="form-label" for="name">Tenant Name *</label>
            <input type="text" id="name" name="name" class="form-input" 
                   value="{{ old('name', $tenant->name) }}" required>
        </div>

        <div class="form-group">
            <label class="form-label" for="slug">Slug *</label>
            <input type="text" id="slug" name="slug" class="form-input" 
                   value="{{ old('slug', $tenant->slug) }}" required>
        </div>

        <div class="form-group">
            <label class="form-label" for="description">Description</label>
            <textarea id="description" name="description" class="form-textarea">{{ old('description', $tenant->description) }}</textarea>
        </div>

        <div class="form-group">
            <div class="checkbox-item">
                <input type="checkbox" id="active" name="active" value="1" 
                       {{ old('active', $tenant->active) ? 'checked' : '' }}>
                <label for="active" class="form-label" style="margin: 0; text-transform: none;">
                    Active
                </label>
            </div>
        </div>

        <div style="display: flex; gap: 12px; margin-top: 32px; direction: ltr;">
            <button type="submit" class="btn btn-primary">
                💾 Update Tenant
            </button>
            <a href="{{ route('tenants.index') }}" class="btn btn-secondary">
                ✕ Cancel
            </a>
        </div>
    </form>
</div>
@endsection

