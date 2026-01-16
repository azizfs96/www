@extends('layouts.waf')

@section('title', 'إعدادات WAF - ' . $site->name)

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

    .site-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 6px 12px;
        background: rgba(157, 78, 221, 0.15);
        border: 1px solid rgba(157, 78, 221, 0.3);
        border-radius: 6px;
        font-size: 13px;
        color: var(--primary);
        margin-top: 8px;
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
        padding: 32px;
        margin-bottom: 24px;
    }

    .section-title {
        font-size: 18px;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .form-group {
        margin-bottom: 24px;
    }

    .form-label {
        display: block;
        font-size: 13px;
        font-weight: 500;
        color: var(--text-primary);
        margin-bottom: 8px;
    }

    .form-help {
        font-size: 12px;
        color: var(--text-muted);
        margin-top: 6px;
        line-height: 1.5;
    }

    .form-input,
    .form-select,
    .form-textarea {
        width: 100%;
        padding: 10px 14px;
        background: var(--bg-dark);
        border: 1px solid var(--border);
        border-radius: 8px;
        color: var(--text-primary);
        font-size: 14px;
        transition: all 0.2s;
    }

    .form-input:focus,
    .form-select:focus,
    .form-textarea:focus {
        outline: none;
        border-color: var(--primary);
        background: var(--bg-hover);
        box-shadow: 0 0 0 3px rgba(157, 78, 221, 0.1);
    }

    .form-textarea {
        resize: vertical;
        min-height: 100px;
        font-family: 'Courier New', monospace;
        font-size: 12px;
    }

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }

    @media (max-width: 768px) {
        .form-row {
            grid-template-columns: 1fr;
        }
    }

    .checkbox-group {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 12px;
    }

    .checkbox-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px;
        background: var(--bg-dark);
        border: 1px solid var(--border);
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.2s;
    }

    .checkbox-item:hover {
        border-color: var(--border-light);
        background: var(--bg-hover);
    }

    .checkbox-item input[type="checkbox"] {
        width: 18px;
        height: 18px;
        cursor: pointer;
        accent-color: var(--primary);
    }

    .checkbox-label {
        font-size: 14px;
        color: var(--text-primary);
        cursor: pointer;
        flex: 1;
    }

    .btn {
        padding: 12px 24px;
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
        background: var(--bg-dark);
        color: var(--text-primary);
        border: 1px solid var(--border);
    }

    .alert-success {
        padding: 12px 16px;
        border-radius: 8px;
        margin-bottom: 24px;
        background: rgba(74, 222, 128, 0.1);
        border: 1px solid rgba(74, 222, 128, 0.3);
        color: var(--success);
        font-size: 14px;
    }

    .paranoia-levels {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 12px;
        margin-top: 8px;
    }

    .paranoia-level {
        position: relative;
    }

    .paranoia-level input[type="radio"] {
        position: absolute;
        opacity: 0;
    }

    .paranoia-level label {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 8px;
        padding: 16px;
        background: var(--bg-dark);
        border: 2px solid var(--border);
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.2s;
        text-align: center;
    }

    .paranoia-level input[type="radio"]:checked + label {
        border-color: var(--primary);
        background: rgba(157, 78, 221, 0.1);
    }

    .paranoia-level label:hover {
        border-color: var(--border-light);
        background: var(--bg-hover);
    }

    .level-number {
        font-size: 24px;
        font-weight: 600;
        color: var(--text-primary);
    }

    .level-name {
        font-size: 11px;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
</style>
@endsection

@section('content')
<div class="page-header">
    <h1 class="page-title">⚙️ إعدادات WAF</h1>
    <div class="site-badge">
        🌐 {{ $site->name }} ({{ $site->server_name }})
    </div>
    <a href="{{ route('sites.index') }}" class="back-link">← العودة للمواقع</a>
</div>

@if(session('status'))
    <div class="alert-success">
        ✓ {{ session('status') }}
    </div>
@endif

<form method="POST" action="{{ route('sites.policy.update', $site) }}">
    @csrf
    @method('PUT')

    {{-- إعدادات عامة --}}
    <div class="card">
        <h2 class="section-title">🛡️ الإعدادات العامة</h2>

        <div class="form-group">
            <div class="checkbox-item">
                <input type="checkbox" name="waf_enabled" id="waf_enabled" 
                       {{ $policy->waf_enabled ? 'checked' : '' }}>
                <label for="waf_enabled" class="checkbox-label">
                    <strong>تفعيل WAF</strong> - تفعيل/تعطيل جدار الحماية لهذا الموقع
                </label>
            </div>
        </div>

        <div class="form-group">
            <div class="checkbox-item">
                <input type="checkbox" name="inherit_global_rules" id="inherit_global_rules" 
                       {{ $policy->inherit_global_rules ? 'checked' : '' }}>
                <label for="inherit_global_rules" class="checkbox-label">
                    <strong>وراثة القواعد العامة</strong> - تطبيق القواعد العامة + القواعد الخاصة بهذا الموقع
                </label>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">مستوى الصرامة (Paranoia Level)</label>
            <div class="paranoia-levels">
                @for($i = 1; $i <= 4; $i++)
                    <div class="paranoia-level">
                        <input type="radio" name="paranoia_level" id="level{{ $i }}" value="{{ $i }}" 
                               {{ $policy->paranoia_level == $i ? 'checked' : '' }}>
                        <label for="level{{ $i }}">
                            <div class="level-number">{{ $i }}</div>
                            <div class="level-name">
                                @if($i == 1) منخفض
                                @elseif($i == 2) متوسط
                                @elseif($i == 3) عالي
                                @else شديد
                                @endif
                            </div>
                        </label>
                    </div>
                @endfor
            </div>
            <div class="form-help">
                المستوى 1 = حماية أساسية (موصى به للبدء)<br>
                المستوى 4 = حماية قصوى (قد يحظر طلبات شرعية)
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">عتبة الشذوذ (Anomaly Threshold)</label>
            <input type="text" name="anomaly_threshold" class="form-input" 
                   value="{{ $policy->anomaly_threshold }}" required>
            <div class="form-help">القيمة الافتراضية: 5. كلما قلت القيمة، زادت الصرامة</div>
        </div>
    </div>

    {{-- أنواع الهجمات المحظورة --}}
    <div class="card">
        <h2 class="section-title">🚫 أنواع الهجمات المحظورة</h2>

        <div class="checkbox-group">
            <div class="checkbox-item">
                <input type="checkbox" name="block_sql_injection" id="block_sql_injection" 
                       {{ $policy->block_sql_injection ? 'checked' : '' }}>
                <label for="block_sql_injection" class="checkbox-label">
                    SQL Injection
                </label>
            </div>

            <div class="checkbox-item">
                <input type="checkbox" name="block_xss" id="block_xss" 
                       {{ $policy->block_xss ? 'checked' : '' }}>
                <label for="block_xss" class="checkbox-label">
                    XSS (Cross-Site Scripting)
                </label>
            </div>

            <div class="checkbox-item">
                <input type="checkbox" name="block_rce" id="block_rce" 
                       {{ $policy->block_rce ? 'checked' : '' }}>
                <label for="block_rce" class="checkbox-label">
                    RCE (Remote Code Execution)
                </label>
            </div>

            <div class="checkbox-item">
                <input type="checkbox" name="block_lfi" id="block_lfi" 
                       {{ $policy->block_lfi ? 'checked' : '' }}>
                <label for="block_lfi" class="checkbox-label">
                    LFI (Local File Inclusion)
                </label>
            </div>

            <div class="checkbox-item">
                <input type="checkbox" name="block_rfi" id="block_rfi" 
                       {{ $policy->block_rfi ? 'checked' : '' }}>
                <label for="block_rfi" class="checkbox-label">
                    RFI (Remote File Inclusion)
                </label>
            </div>

            <div class="checkbox-item">
                <input type="checkbox" name="block_suspicious_user_agents" id="block_suspicious_user_agents" 
                       {{ $policy->block_suspicious_user_agents ? 'checked' : '' }}>
                <label for="block_suspicious_user_agents" class="checkbox-label">
                    User Agents المشبوهة
                </label>
            </div>
        </div>
    </div>

    {{-- Rate Limiting --}}
    <div class="card">
        <h2 class="section-title">⏱️ تحديد المعدل (Rate Limiting)</h2>

        <div class="form-group">
            <div class="checkbox-item">
                <input type="checkbox" name="rate_limiting_enabled" id="rate_limiting_enabled" 
                       {{ $policy->rate_limiting_enabled ? 'checked' : '' }}>
                <label for="rate_limiting_enabled" class="checkbox-label">
                    <strong>تفعيل Rate Limiting</strong>
                </label>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">عدد الطلبات في الدقيقة</label>
                <input type="number" name="requests_per_minute" class="form-input" 
                       value="{{ $policy->requests_per_minute }}" min="1">
            </div>

            <div class="form-group">
                <label class="form-label">Burst Size</label>
                <input type="number" name="burst_size" class="form-input" 
                       value="{{ $policy->burst_size }}" min="1">
                <div class="form-help">عدد الطلبات الإضافية المسموحة في اللحظة</div>
            </div>
        </div>
    </div>

    {{-- استثناءات --}}
    <div class="card">
        <h2 class="section-title">🔓 الاستثناءات</h2>

        <div class="form-group">
            <label class="form-label">URLs مستثناة من WAF</label>
            <textarea name="excluded_urls" class="form-textarea" 
                      placeholder="/api/webhook&#10;/admin/login">{{ $policy->excluded_urls }}</textarea>
            <div class="form-help">كل URL في سطر منفصل. هذه المسارات لن تخضع لفحص WAF</div>
        </div>

        <div class="form-group">
            <label class="form-label">IPs مستثناة (Whitelisted)</label>
            <textarea name="excluded_ips" class="form-textarea" 
                      placeholder="192.168.1.1&#10;10.0.0.5">{{ $policy->excluded_ips }}</textarea>
            <div class="form-help">كل IP في سطر منفصل. هذه العناوين لن تخضع لأي فحص WAF</div>
        </div>
    </div>

    {{-- Logging --}}
    <div class="card">
        <h2 class="section-title">📝 السجلات (Logging)</h2>

        <div class="form-group">
            <div class="checkbox-item">
                <input type="checkbox" name="detailed_logging" id="detailed_logging" 
                       {{ $policy->detailed_logging ? 'checked' : '' }}>
                <label for="detailed_logging" class="checkbox-label">
                    <strong>تفعيل السجلات التفصيلية</strong>
                </label>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">مستوى السجل</label>
            <select name="log_level" class="form-select">
                <option value="debug" {{ $policy->log_level == 'debug' ? 'selected' : '' }}>Debug (كل شيء)</option>
                <option value="info" {{ $policy->log_level == 'info' ? 'selected' : '' }}>Info</option>
                <option value="warn" {{ $policy->log_level == 'warn' ? 'selected' : '' }}>Warning (موصى به)</option>
                <option value="error" {{ $policy->log_level == 'error' ? 'selected' : '' }}>Error فقط</option>
            </select>
        </div>
    </div>

    {{-- قواعد مخصصة --}}
    <div class="card">
        <h2 class="section-title">⚡ قواعد ModSecurity مخصصة</h2>

        <div class="form-group">
            <label class="form-label">قواعد ModSecurity إضافية (اختياري)</label>
            <textarea name="custom_modsec_rules" class="form-textarea" style="min-height: 150px;"
                      placeholder="SecRule ...">{{ $policy->custom_modsec_rules }}</textarea>
            <div class="form-help">
                قواعد ModSecurity متقدمة. استخدم هذا فقط إذا كنت تعرف ما تفعل!<br>
                مثال: SecRule REQUEST_URI "@beginsWith /test" "id:100001,phase:1,deny,status:403"
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">ملاحظات</label>
            <textarea name="notes" class="form-textarea" style="min-height: 80px;">{{ $policy->notes }}</textarea>
        </div>
    </div>

    <div style="display: flex; gap: 12px;">
        <button type="submit" class="btn btn-primary">
            💾 حفظ الإعدادات
        </button>
        <a href="{{ route('sites.index') }}" class="btn btn-secondary">
            ✕ إلغاء
        </a>
    </div>
</form>

@endsection
