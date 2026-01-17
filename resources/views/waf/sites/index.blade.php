@extends('layouts.waf')

@section('title', 'إدارة المواقع')

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
        --success: #4ADE80;
        --error: #F87171;
        --warning: #FBBF24;
    }

    .page-header {
        margin-bottom: 32px;
    }

    .page-title {
        font-size: 32px;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 8px;
    }

    .page-description {
        font-size: 14px;
        color: var(--text-secondary);
    }

    .page-actions {
        display: flex;
        gap: 12px;
        margin-top: 20px;
    }

    .btn {
        padding: 10px 20px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 500;
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
        background: var(--bg-card);
        color: var(--text-primary);
        border: 1px solid var(--border);
    }

    .btn-secondary:hover {
        background: var(--bg-hover);
        border-color: var(--border-light);
    }

    .btn-sm {
        padding: 6px 12px;
        font-size: 12px;
    }

    .btn-danger {
        background: var(--error);
        color: white;
    }

    .btn-success {
        background: var(--success);
        color: white;
    }

    .alert {
        padding: 12px 16px;
        border-radius: 8px;
        margin-bottom: 24px;
        font-size: 14px;
        border: 1px solid;
    }

    .alert-success {
        background: rgba(74, 222, 128, 0.1);
        border-color: rgba(74, 222, 128, 0.3);
        color: var(--success);
    }

    .card {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: 12px;
        overflow: hidden;
    }

    .card-header {
        padding: 20px;
        border-bottom: 1px solid var(--border);
    }

    .card-title {
        font-size: 18px;
        font-weight: 600;
        color: var(--text-primary);
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    thead {
        background: var(--bg-card);
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
        padding: 16px 20px;
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
        gap: 6px;
        border: 1px solid;
    }

    .badge-success {
        background: rgba(74, 222, 128, 0.15);
        color: var(--success);
        border-color: rgba(74, 222, 128, 0.3);
    }

    .badge-warning {
        background: rgba(251, 191, 36, 0.15);
        color: var(--warning);
        border-color: rgba(251, 191, 36, 0.3);
    }

    .badge-info {
        background: rgba(157, 78, 221, 0.15);
        color: var(--primary);
        border-color: rgba(157, 78, 221, 0.3);
    }

    .empty-state {
        padding: 48px 24px;
        text-align: center;
        color: var(--text-muted);
        font-size: 14px;
    }

    .actions {
        display: flex;
        gap: 8px;
        align-items: center;
    }

    .site-name {
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 4px;
    }

    .site-domain {
        font-size: 12px;
        color: var(--text-muted);
        font-family: 'Courier New', monospace;
    }

    .backend-info {
        font-family: 'Courier New', monospace;
        font-size: 12px;
    }
</style>
@endsection

@section('content')
<div class="page-header">
    <h1 class="page-title">إدارة المواقع</h1>
    <p class="page-description">
        إضافة وإدارة المواقع المحمية بواسطة WAF Gateway. يتم توليد ملفات Nginx تلقائياً.
    </p>
    
    <div class="page-actions">
        <a href="{{ route('sites.create') }}" class="btn btn-primary">
            ➕ إضافة موقع جديد
        </a>
        <form method="POST" action="{{ route('sites.regenerate') }}" style="display: inline;">
            @csrf
            <button type="submit" class="btn btn-secondary">
                🔄 إعادة توليد جميع الملفات
            </button>
        </form>
    </div>
</div>

@if(session('status'))
    <div class="alert alert-success">
        ✓ {{ session('status') }}
    </div>
@endif

<div class="card">
    <div class="card-header">
        <div class="card-title">المواقع المُدارة ({{ $sites->count() }})</div>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>الموقع</th>
                <th>السيرفر الخلفي</th>
                <th>SSL</th>
                <th>الحالة</th>
                <th>الإجراءات</th>
            </tr>
        </thead>
        <tbody>
            @forelse($sites as $site)
                <tr>
                    <td>
                        <div class="site-name">{{ $site->name }}</div>
                        <div class="site-domain">{{ $site->server_name }}</div>
                    </td>
                    <td>
                        <div class="backend-info">
                            {{ $site->backend_ip }}:{{ $site->backend_port }}
                        </div>
                    </td>
                    <td>
                        @if($site->ssl_enabled)
                            <span class="badge badge-success">🔒 HTTPS</span>
                        @else
                            <span class="badge badge-warning">HTTP</span>
                        @endif
                    </td>
                    <td>
                        @if($site->enabled)
                            <span class="badge badge-success">✓ مفعّل</span>
                        @else
                            <span class="badge badge-warning">معطّل</span>
                        @endif
                    </td>
                    <td>
                        <div class="actions">
                            <a href="{{ route('sites.policy.edit', $site) }}" class="btn btn-sm btn-primary">
                                ⚙️ إعدادات WAF
                            </a>

                            @if($site->ssl_enabled)
                                @php
                                    $certPath = "/etc/letsencrypt/live/{$site->server_name}/fullchain.pem";
                                    $keyPath = "/etc/letsencrypt/live/{$site->server_name}/privkey.pem";
                                    $certExists = file_exists($certPath) && file_exists($keyPath);
                                @endphp
                                @if(!$certExists)
                                    <form method="POST" action="{{ route('sites.fix-ssl', $site) }}" style="display: inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-warning" 
                                                title="إصلاح SSL: توليد الشهادة المفقودة">
                                            🔧 إصلاح SSL
                                        </button>
                                    </form>
                                @endif
                            @endif
                            
                            <form method="POST" action="{{ route('sites.toggle-ssl', $site) }}" style="display: inline;">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-sm {{ $site->ssl_enabled ? 'btn-warning' : 'btn-success' }}" 
                                        title="{{ $site->ssl_enabled ? 'تعطيل SSL' : 'تفعيل SSL وتوليد الشهادة' }}">
                                    {{ $site->ssl_enabled ? '🔓 تعطيل SSL' : '🔒 تفعيل SSL' }}
                                </button>
                            </form>

                            <form method="POST" action="{{ route('sites.toggle', $site) }}" style="display: inline;">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-sm {{ $site->enabled ? 'btn-warning' : 'btn-success' }}">
                                    {{ $site->enabled ? '⏸ تعطيل' : '▶ تفعيل' }}
                                </button>
                            </form>

                            <form method="POST" action="{{ route('sites.destroy', $site) }}" 
                                  onsubmit="return confirm('هل أنت متأكد من حذف هذا الموقع؟')"
                                  style="display: inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">
                                    🗑 حذف
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="empty-state">
                        لا توجد مواقع حالياً. قم بإضافة موقع جديد للبدء.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($sites->count() > 0)
<div style="margin-top: 24px; padding: 16px; background: var(--bg-card); border: 1px solid var(--border); border-radius: 8px;">
    <h3 style="font-size: 14px; color: var(--text-primary); margin-bottom: 8px;">💡 ملاحظات مهمة:</h3>
    <ul style="font-size: 13px; color: var(--text-secondary); line-height: 1.8; margin: 0; padding-left: 20px;">
        <li>ملفات Nginx يتم حفظها في: <code style="background: var(--bg-dark); padding: 2px 6px; border-radius: 4px;">/etc/nginx/sites-enabled/</code></li>
        <li>يجب التأكد من صلاحيات الكتابة على المجلد</li>
        <li>عند التعديل، يتم إعادة تحميل Nginx تلقائياً</li>
        <li>للـ SSL: تأكد من وجود الشهادات في المسارات المحددة</li>
    </ul>
</div>
@endif

@endsection
