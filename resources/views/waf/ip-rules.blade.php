@extends('layouts.waf')

@section('title', 'إدارة عناوين IP')

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
    }

    .page-header {
        margin-bottom: 32px;
    }

    .page-header h1 {
        font-size: 28px;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 12px;
    }

    .page-subtitle {
        font-size: 14px;
        color: var(--text-secondary);
        line-height: 1.6;
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
        font-weight: 500;
    }

    .form-group input,
    .form-group select {
        background: var(--bg-dark);
        border-radius: 8px;
        border: 1px solid var(--border);
        color: var(--text-primary);
        font-size: 13px;
        padding: 10px 14px;
        transition: all 0.2s;
    }

    .form-group input:focus,
    .form-group select:focus {
        outline: none;
        border-color: var(--primary);
        background: var(--bg-hover);
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
        padding: 10px 20px;
        cursor: pointer;
        font-weight: 500;
        transition: all 0.2s;
        white-space: nowrap;
    }

    .btn-primary {
        background: var(--primary);
        color: white;
        border: 1px solid var(--primary);
    }

    .btn-primary:hover {
        background: var(--primary-hover);
        transform: translateY(-1px);
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

    th, td {
        padding: 14px 20px;
        text-align: right;
    }

    th {
        color: var(--text-muted);
        font-weight: 600;
        border-bottom: 1px solid var(--border);
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        background: var(--bg-card);
    }

    td {
        border-top: 1px solid var(--border);
        color: var(--text-primary);
    }

    tbody tr:nth-child(even) {
        background: rgba(255, 255, 255, 0.01);
    }

    tbody tr:hover {
        background: var(--bg-hover);
    }

    .pill {
        display: inline-flex;
        align-items: center;
        padding: 5px 12px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 500;
        border: 1px solid;
    }

    .pill-allow {
        background: rgba(74, 222, 128, 0.1);
        color: var(--success);
        border-color: rgba(74, 222, 128, 0.2);
    }

    .pill-block {
        background: rgba(248, 113, 113, 0.1);
        color: var(--error);
        border-color: rgba(248, 113, 113, 0.2);
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
</style>
@endsection

@section('content')
<div class="page-header">
    <h1>إدارة عناوين IP</h1>
    <p class="page-subtitle">
        من هنا يمكنك إضافة عناوين IP إلى قائمة السماح (Whitelist) أو الحظر (Blacklist). أي تعديل يتم
        مزامنته تلقائياً مع ModSecurity وإعادة تحميل Nginx.
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
                <label>عنوان IP</label>
                <input type="text" name="ip" placeholder="مثال: 137.59.230.231" required>
                @error('ip')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>
            <div class="form-group">
                <label>النوع</label>
                <select name="type" required>
                    <option value="allow">Whitelist (سماح)</option>
                    <option value="block">Blacklist (حظر)</option>
                </select>
                @error('type')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>
            <div class="form-group" style="flex: 0 0 auto;">
                <label>&nbsp;</label>
                <button type="submit" class="btn btn-primary">إضافة القاعدة</button>
            </div>
        </div>
    </form>
</div>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>IP</th>
                <th>النوع</th>
                <th>أضيف في</th>
                <th>إجراءات</th>
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
                <td class="text-muted">{{ $rule->created_at->format('Y-m-d H:i') }}</td>
                <td>
                    <form method="POST" action="{{ route('ip-rules.destroy', $rule) }}"
                          onsubmit="return confirm('هل أنت متأكد من حذف هذه القاعدة؟');" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">حذف</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="4" class="empty-state">
                    لا توجد قواعد حالياً. يمكنك إضافة IP أعلاه.
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
