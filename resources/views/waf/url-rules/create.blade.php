@extends('layouts.waf')

@section('title', 'Create URL Rule')

@section('styles')
<style>
    /* Clean Dark Design - URL Rules Create Page */
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
        --success: #4ADE80;
        --error: #F87171;
    }

    .page-header {
        margin-bottom: 32px;
        direction: ltr;
        text-align: left;
    }

    .page-header h1 {
        font-size: 32px;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 12px;
        letter-spacing: -0.5px;
    }

    .page-subtitle {
        font-size: 14px;
        color: var(--text-secondary);
        line-height: 1.6;
        max-width: 700px;
    }

    .card {
        background: var(--bg-card);
        border-radius: 12px;
        border: 1px solid var(--border);
        padding: 24px;
        margin-bottom: 24px;
    }

    .form-group {
        display: flex;
        flex-direction: column;
        gap: 8px;
        margin-bottom: 20px;
    }

    .form-group label {
        font-size: 12px;
        color: var(--text-muted);
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .form-group input,
    .form-group textarea {
        background: var(--bg-dark);
        border-radius: 8px;
        border: 1px solid var(--border);
        color: var(--text-primary);
        font-size: 13px;
        padding: 11px 16px;
        transition: all 0.2s;
        font-family: inherit;
    }

    .form-group input:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: var(--primary);
        background: var(--bg-hover);
        box-shadow: 0 0 0 3px rgba(157, 78, 221, 0.1);
    }

    .form-group textarea {
        min-height: 80px;
        resize: vertical;
    }

    .form-group small {
        color: var(--text-muted);
        font-size: 11px;
        margin-top: 4px;
    }

    .error-message {
        color: var(--error);
        font-size: 11px;
        margin-top: 4px;
    }

    .btn {
        border-radius: 8px;
        border: none;
        font-size: 13px;
        padding: 11px 20px;
        cursor: pointer;
        font-weight: 600;
        transition: all 0.2s;
        white-space: nowrap;
        text-decoration: none;
        display: inline-block;
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
        margin-right: 10px;
    }

    .btn-secondary:hover {
        background: var(--bg-hover);
        border-color: var(--border-light);
        color: var(--text-primary);
    }

    .form-actions {
        display: flex;
        gap: 10px;
        margin-top: 24px;
    }
</style>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h1>Create URL Rule</h1>
        <p class="page-subtitle">
            Add a new URL rule to allow specific IP addresses to access a particular path.
        </p>
    </div>
</div>

<div class="card">
    <form method="POST" action="{{ route('url-rules.store') }}">
        @csrf
        
        <div class="form-group">
            <label>Name (Optional)</label>
            <input type="text" name="name" placeholder="e.g., Admin Panel Access" value="{{ old('name') }}">
            <small>A descriptive name for this rule</small>
            @error('name')
                <span class="error-message">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label>Host / Domain (Optional)</label>
            <input type="text" name="host" placeholder="e.g., rabbitclean.sa" value="{{ old('host') }}">
            <small>Leave empty to apply to all sites. Specify domain to apply only to that site (e.g., rabbitclean.sa)</small>
            @error('host')
                <span class="error-message">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label>Path <span style="color: var(--error);">*</span></label>
            <input type="text" name="path" placeholder="e.g., /admin" value="{{ old('path') }}" required>
            <small>The URL path to protect (e.g., /admin, /api/dashboard)</small>
            @error('path')
                <span class="error-message">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label>Allowed IPs <span style="color: var(--error);">*</span></label>
            <textarea name="allowed_ips" placeholder="e.g., 192.168.1.1, 10.0.0.1, 172.16.0.1" required>{{ old('allowed_ips') }}</textarea>
            <small>Comma-separated list of IP addresses allowed to access this path</small>
            @error('allowed_ips')
                <span class="error-message">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Create Rule</button>
            <a href="{{ route('url-rules.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
