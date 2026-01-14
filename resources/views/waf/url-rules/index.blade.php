@extends('layouts.waf')

@section('title', 'URL Rules Management')

@section('styles')
<style>
    /* Clean Dark Design - URL Rules Page */
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
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        flex-wrap: wrap;
        gap: 16px;
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

    .badge {
        display: inline-flex;
        align-items: center;
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 500;
        border: 1px solid;
    }

    .badge-success {
        background: rgba(74, 222, 128, 0.15);
        color: var(--success);
        border-color: rgba(74, 222, 128, 0.3);
    }

    .badge-secondary {
        background: rgba(179, 179, 179, 0.15);
        color: var(--text-secondary);
        border-color: rgba(179, 179, 179, 0.3);
    }

    .text-muted {
        color: var(--text-muted);
        font-size: 12px;
    }

    .empty-state {
        text-align: center;
        padding: 48px 24px;
        color: var(--text-muted);
        font-size: 13px;
    }

    td code {
        background: rgba(157, 78, 221, 0.1);
        border: 1px solid rgba(157, 78, 221, 0.2);
        padding: 4px 8px;
        border-radius: 4px;
        font-family: 'Courier New', monospace;
        font-size: 12px;
        color: var(--primary);
    }

    td strong {
        font-weight: 600;
        color: var(--text-primary);
    }
</style>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h1>🔐 URL Rules</h1>
        <p class="page-subtitle">
            Manage URL path rules with allowed IP addresses. Configure which IPs can access specific paths.
        </p>
    </div>
    <a href="/waf/url-rules/create" class="btn btn-primary">➕ Add Rule</a>
</div>

@if(session('status'))
    <div class="alert">{{ session('status') }}</div>
@endif

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Host</th>
                <th>Path</th>
                <th>Allowed IPs</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        @forelse($rules as $rule)
            <tr>
                <td><strong>{{ $rule->name ?? '-' }}</strong></td>
                <td>
                    @if($rule->host)
                        <code style="background: rgba(96, 165, 250, 0.1); border-color: rgba(96, 165, 250, 0.2); color: var(--info);">
                            {{ $rule->host }}
                        </code>
                    @else
                        <span style="color: var(--text-muted); font-size: 11px;">All Sites</span>
                    @endif
                </td>
                <td><code>{{ $rule->path }}</code></td>
                <td>
                    <span style="font-family: 'Courier New', monospace; font-size: 12px;">
                        {{ $rule->allowed_ips }}
                    </span>
                </td>
                <td>
                    @if($rule->enabled)
                        <span class="badge badge-success">Enabled</span>
                    @else
                        <span class="badge badge-secondary">Disabled</span>
                    @endif
                </td>
                <td>
                    <form method="POST" action="{{ route('url-rules.destroy', $rule) }}" 
                          onsubmit="return confirm('Are you sure you want to delete this rule?');" 
                          style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="empty-state">
                    No rules currently. You can add a URL rule above.
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
