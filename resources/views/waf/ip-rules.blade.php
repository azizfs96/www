@extends('layouts.waf')

@section('title', 'IP Rules Management')

@section('styles')
<style>
    /* Clean Dark Design - IP Rules Page */
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

    .alert {
        background: rgba(74, 222, 128, 0.1);
        border: 1px solid rgba(74, 222, 128, 0.3);
        border-radius: 8px;
        padding: 12px 16px;
        margin-bottom: 20px;
        color: var(--success);
        font-size: 13px;
    }

    .card {
        background: var(--bg-card);
        border-radius: 12px;
        border: 1px solid var(--border);
        padding: 24px;
        margin-bottom: 24px;
    }

    .form-row {
        display: flex;
        gap: 16px;
        flex-wrap: wrap;
        align-items: flex-end;
    }

    .form-group {
        display: flex;
        flex-direction: column;
        gap: 8px;
        flex: 1;
        min-width: 200px;
    }

    .form-group label {
        font-size: 12px;
        color: var(--text-muted);
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .form-group input,
    .form-group select {
        background: var(--bg-dark);
        border-radius: 8px;
        border: 1px solid var(--border);
        color: var(--text-primary);
        font-size: 13px;
        padding: 11px 16px;
        transition: all 0.2s;
    }

    .form-group input:focus,
    .form-group select:focus {
        outline: none;
        border-color: var(--primary);
        background: var(--bg-hover);
        box-shadow: 0 0 0 3px rgba(157, 78, 221, 0.1);
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

    .btn-danger {
        background: var(--error);
        color: white;
        border: 1px solid var(--error);
        padding: 6px 12px;
        font-size: 12px;
    }

    .btn-danger:hover {
        background: #DC2626;
        transform: translateY(-1px);
    }

    .table-container {
        background: var(--bg-card);
        border-radius: 12px;
        border: 1px solid var(--border);
        overflow: hidden;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
    }

    thead {
        background: #1E1E1E;
    }

    th {
        padding: 12px 20px;
        text-align: left;
        font-size: 11px;
        font-weight: 600;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 1px solid var(--border);
    }

    tbody tr {
        border-bottom: 1px solid var(--border);
        transition: background-color 0.15s ease;
    }

    tbody tr:hover {
        background: var(--bg-hover);
    }

    tbody tr:last-child {
        border-bottom: none;
    }

    td {
        padding: 14px 20px;
        text-align: left;
        color: var(--text-primary);
        font-size: 13px;
    }

    .pill {
        display: inline-flex;
        align-items: center;
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 500;
        border: 1px solid;
    }

    .pill-allow {
        background: rgba(74, 222, 128, 0.15);
        color: var(--success);
        border-color: rgba(74, 222, 128, 0.3);
    }

    .pill-block {
        background: rgba(248, 113, 113, 0.15);
        color: var(--error);
        border-color: rgba(248, 113, 113, 0.3);
    }

    .text-muted {
        color: var(--text-muted);
        font-size: 12px;
        font-family: 'Courier New', monospace;
    }

    .empty-state {
        text-align: center;
        padding: 48px 24px;
        color: var(--text-muted);
        font-size: 13px;
    }

    td strong {
        font-weight: 600;
        color: var(--text-primary);
        font-family: 'Courier New', monospace;
    }
</style>
@endsection

@section('content')
<div class="page-header">
    <h1>IP Rules Management</h1>
    <p class="page-subtitle">
        Manage IP addresses in your whitelist or blacklist. Any changes are automatically synchronized with ModSecurity and Nginx will be reloaded.
    </p>
</div>

@if (session('status'))
    <div class="alert">
        {{ session('status') }}
    </div>
@endif

<div class="card">
    <form method="POST" action="{{ route('ip-rules.store') }}">
        @csrf
        <div class="form-row">
            <div class="form-group">
                <label>IP Address</label>
                <input type="text" name="ip" placeholder="e.g., 137.59.230.231" required>
                @error('ip')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>
            <div class="form-group">
                <label>Type</label>
                <select name="type" required>
                    <option value="allow">Whitelist (Allow)</option>
                    <option value="block">Blacklist (Block)</option>
                </select>
                @error('type')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>
            <div class="form-group" style="flex: 0 0 auto;">
                <label>&nbsp;</label>
                <button type="submit" class="btn btn-primary">Add Rule</button>
            </div>
        </div>
    </form>
</div>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>IP Address</th>
                <th>Type</th>
                <th>Created At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        @forelse ($rules as $rule)
            <tr>
                <td><strong>{{ $rule->ip }}</strong></td>
                <td>
                    @if ($rule->type === 'allow')
                        <span class="pill pill-allow">Whitelist</span>
                    @else
                        <span class="pill pill-block">Blacklist</span>
                    @endif
                </td>
                <td class="text-muted">{{ $rule->created_at->format('Y-m-d H:i:s') }}</td>
                <td>
                    <form method="POST" action="{{ route('ip-rules.destroy', $rule) }}"
                          onsubmit="return confirm('Are you sure you want to delete this rule?');" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="4" class="empty-state">
                    No rules currently. You can add an IP address above.
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
