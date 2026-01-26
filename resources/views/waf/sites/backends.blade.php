@extends('layouts.waf')

@section('title', 'إدارة السيرفرات الخلفية - ' . $site->name)

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
        --success: #10b981;
        --warning: #f59e0b;
        --error: #F87171;
        --info: #3b82f6;
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

    .page-subtitle {
        font-size: 14px;
        color: var(--text-secondary);
    }

    .back-link {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: var(--text-muted);
        text-decoration: none;
        font-size: 13px;
        margin-top: 12px;
        transition: color 0.2s;
    }

    .back-link:hover {
        color: var(--primary);
    }

    .card {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 24px;
        margin-bottom: 24px;
    }

    .server-card {
        background: var(--bg-dark);
        border: 1px solid var(--border);
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 16px;
        transition: all 0.2s;
    }

    .server-card:hover {
        border-color: var(--border-light);
        background: var(--bg-hover);
    }

    .server-card.active {
        border-color: var(--success);
        background: rgba(16, 185, 129, 0.05);
    }

    .server-card.standby {
        border-color: var(--warning);
        background: rgba(245, 158, 11, 0.05);
    }

    .server-card.unhealthy {
        border-color: var(--error);
        background: rgba(248, 113, 113, 0.05);
    }

    .server-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 16px;
    }

    .server-title {
        font-size: 16px;
        font-weight: 600;
        color: var(--text-primary);
    }

    .server-status {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 500;
    }

    .server-status.active {
        background: rgba(16, 185, 129, 0.2);
        color: var(--success);
    }

    .server-status.standby {
        background: rgba(245, 158, 11, 0.2);
        color: var(--warning);
    }

    .server-status.unhealthy {
        background: rgba(248, 113, 113, 0.2);
        color: var(--error);
    }

    .server-info {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 16px;
        margin-bottom: 16px;
    }

    .info-item {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .info-label {
        font-size: 12px;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .info-value {
        font-size: 14px;
        color: var(--text-primary);
        font-weight: 500;
    }

    .server-actions {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    .btn {
        padding: 8px 16px;
        border-radius: 6px;
        font-size: 13px;
        font-weight: 500;
        border: none;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s;
    }

    .btn-primary {
        background: var(--primary);
        color: white;
    }

    .btn-primary:hover {
        background: var(--primary-hover);
    }

    .btn-success {
        background: var(--success);
        color: white;
    }

    .btn-success:hover {
        background: #059669;
    }

    .btn-warning {
        background: var(--warning);
        color: white;
    }

    .btn-warning:hover {
        background: #d97706;
    }

    .btn-secondary {
        background: var(--bg-dark);
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

    .alert {
        padding: 12px 16px;
        border-radius: 8px;
        margin-bottom: 24px;
        background: rgba(16, 185, 129, 0.1);
        border: 1px solid rgba(16, 185, 129, 0.3);
        color: var(--success);
        font-size: 14px;
    }

    .alert-error {
        background: rgba(248, 113, 113, 0.1);
        border-color: rgba(248, 113, 113, 0.3);
        color: var(--error);
    }

    .page-actions {
        display: flex;
        gap: 12px;
        margin-top: 16px;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }

    .stat-card {
        background: var(--bg-dark);
        border: 1px solid var(--border);
        border-radius: 8px;
        padding: 16px;
        text-align: center;
    }

    .stat-value {
        font-size: 24px;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 4px;
    }

    .stat-label {
        font-size: 12px;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
</style>
@endsection

@section('content')
<div class="page-header">
    <h1 class="page-title">🖥️ إدارة السيرفرات الخلفية</h1>
    <p class="page-subtitle">
        عرض حالة السيرفرات الخلفية وإدارة Failover للموقع: <strong>{{ $site->name }}</strong> ({{ $site->server_name }})
    </p>
    <a href="{{ route('sites.index') }}" class="back-link">← العودة لقائمة المواقع</a>
    
    <div class="page-actions">
        <form method="POST" action="{{ route('sites.backends.check', $site) }}" style="display: inline;">
            @csrf
            <button type="submit" class="btn btn-primary">
                🔍 فحص جميع السيرفرات
            </button>
        </form>
        
        @if($backendServers->where('status', 'active')->count() > 0 && $backendServers->where('status', 'standby')->count() > 0)
            <form method="POST" action="{{ route('sites.backends.failover', $site) }}" style="display: inline;" 
                  onsubmit="return confirm('⚠️ هل أنت متأكد من تنفيذ Failover؟\n\nسيتم:\n- تعطيل جميع السيرفرات النشطة\n- تفعيل أول سيرفر احتياطي\n- إعادة توليد ملف Nginx');">
                @csrf
                <button type="submit" class="btn btn-warning">
                    🔄 تنفيذ Failover يدوي
                </button>
            </form>
        @endif
    </div>
</div>

@if(session('status'))
    <div class="alert">
        ✓ {{ session('status') }}
    </div>
@endif

@if(session('error'))
    <div class="alert alert-error">
        ⚠️ {{ session('error') }}
    </div>
@endif

{{-- إحصائيات --}}
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-value">{{ $backendServers->count() }}</div>
        <div class="stat-label">إجمالي السيرفرات</div>
    </div>
    <div class="stat-card">
        <div class="stat-value" style="color: var(--success);">{{ $backendServers->where('status', 'active')->count() }}</div>
        <div class="stat-label">نشط (Active)</div>
    </div>
    <div class="stat-card">
        <div class="stat-value" style="color: var(--warning);">{{ $backendServers->where('status', 'standby')->count() }}</div>
        <div class="stat-label">احتياطي (Standby)</div>
    </div>
    <div class="stat-card">
        <div class="stat-value" style="color: var(--success);">{{ $backendServers->where('is_healthy', true)->count() }}</div>
        <div class="stat-label">صحي</div>
    </div>
    <div class="stat-card">
        <div class="stat-value" style="color: var(--error);">{{ $backendServers->where('is_healthy', false)->count() }}</div>
        <div class="stat-label">غير صحي</div>
    </div>
</div>

{{-- قائمة السيرفرات --}}
<div class="card">
    <h2 style="font-size: 18px; font-weight: 600; color: var(--text-primary); margin-bottom: 20px;">
        السيرفرات الخلفية
    </h2>

    @forelse($backendServers as $server)
        <div class="server-card {{ $server->status }} {{ $server->is_healthy ? '' : 'unhealthy' }}">
            <div class="server-header">
                <div class="server-title">
                    {{ $server->ip }}:{{ $server->port }}
                </div>
                <div class="server-status {{ $server->status }} {{ $server->is_healthy ? '' : 'unhealthy' }}">
                    @if($server->status === 'active')
                        ✓ نشط
                    @else
                        ○ احتياطي
                    @endif
                    @if(!$server->is_healthy)
                        - ⚠️ غير صحي
                    @endif
                </div>
            </div>

            <div class="server-info">
                <div class="info-item">
                    <div class="info-label">الحالة</div>
                    <div class="info-value">
                        <span style="color: {{ $server->status === 'active' ? 'var(--success)' : 'var(--warning)' }};">
                            {{ $server->status === 'active' ? 'نشط (Active)' : 'احتياطي (Standby)' }}
                        </span>
                    </div>
                </div>
                <div class="info-item">
                    <div class="info-label">الصحة</div>
                    <div class="info-value">
                        <span style="color: {{ $server->is_healthy ? 'var(--success)' : 'var(--error)' }};">
                            {{ $server->is_healthy ? '✓ صحي' : '✗ غير صحي' }}
                        </span>
                    </div>
                </div>
                <div class="info-item">
                    <div class="info-label">الأولوية</div>
                    <div class="info-value">{{ $server->priority }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">عدد مرات الفشل</div>
                    <div class="info-value" style="color: {{ $server->fail_count > 0 ? 'var(--error)' : 'var(--text-primary)' }};">
                        {{ $server->fail_count }}
                    </div>
                </div>
                <div class="info-item">
                    <div class="info-label">آخر فحص</div>
                    <div class="info-value">
                        {{ $server->last_health_check ? $server->last_health_check->diffForHumans() : 'لم يتم الفحص' }}
                    </div>
                </div>
                <div class="info-item">
                    <div class="info-label">فحص الصحة</div>
                    <div class="info-value">
                        {{ $server->health_check_enabled ? 'مفعل' : 'معطل' }}
                    </div>
                </div>
            </div>

            <div class="server-actions">
                <form method="POST" action="{{ route('sites.backends.check-single', [$site, $server]) }}" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn btn-secondary btn-sm">
                        🔍 فحص الصحة
                    </button>
                </form>

                @if($server->status === 'active')
                    <form method="POST" action="{{ route('sites.backends.toggle-status', [$site, $server]) }}" style="display: inline;">
                        @csrf
                        <button type="submit" class="btn btn-warning btn-sm" 
                                onclick="return confirm('هل أنت متأكد من تحويل هذا السيرفر إلى Standby?')">
                            ○ تحويل إلى Standby
                        </button>
                    </form>
                @else
                    <form method="POST" action="{{ route('sites.backends.toggle-status', [$site, $server]) }}" style="display: inline;">
                        @csrf
                        <button type="submit" class="btn btn-success btn-sm">
                            ✓ تفعيل (Active)
                        </button>
                    </form>
                @endif
            </div>
        </div>
    @empty
        <div style="text-align: center; padding: 40px; color: var(--text-muted);">
            <p>لا توجد سيرفرات خلفية لهذا الموقع.</p>
            <a href="{{ route('sites.index') }}" class="btn btn-primary" style="margin-top: 16px;">
                العودة لقائمة المواقع
            </a>
        </div>
    @endforelse
</div>

{{-- معلومات عن Failover --}}
<div class="card">
    <h2 style="font-size: 18px; font-weight: 600; color: var(--text-primary); margin-bottom: 16px;">
        ℹ️ معلومات عن Failover
    </h2>
    <div style="color: var(--text-secondary); font-size: 14px; line-height: 1.8;">
        <p><strong>كيف يعمل Failover:</strong></p>
        <ul style="margin: 12px 0; padding-left: 20px;">
            <li>النظام يفحص صحة السيرفرات تلقائياً كل دقيقة</li>
            <li>عند فشل سيرفر نشط 3 مرات متتالية، يتم التبديل تلقائياً إلى سيرفر احتياطي صحي</li>
            <li>يتم إعادة توليد ملف Nginx تلقائياً بعد Failover</li>
            <li>يمكنك فحص السيرفرات يدوياً باستخدام زر "فحص جميع السيرفرات"</li>
        </ul>
        <p style="margin-top: 16px;"><strong>ملاحظة:</strong> تأكد من تشغيل Laravel Scheduler على السيرفر:</p>
        <code style="display: block; background: var(--bg-dark); padding: 12px; border-radius: 6px; margin-top: 8px;">
            * * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
        </code>
    </div>
</div>
@endsection

