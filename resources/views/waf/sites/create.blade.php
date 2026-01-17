@extends('layouts.waf')

@section('title', 'إضافة موقع جديد')

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
        --error: #F87171;
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
        max-width: 800px;
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

    .form-label .required {
        color: var(--error);
    }

    .form-help {
        font-size: 12px;
        color: var(--text-muted);
        margin-top: 6px;
        line-height: 1.5;
    }

    .form-input,
    .form-textarea {
        width: 100%;
        padding: 10px 14px;
        background: var(--bg-dark);
        border: 1px solid var(--border);
        border-radius: 8px;
        color: var(--text-primary);
        font-size: 14px;
        transition: all 0.2s;
        font-family: system-ui, sans-serif;
    }

    .form-input:focus,
    .form-textarea:focus {
        outline: none;
        border-color: var(--primary);
        background: var(--bg-hover);
        box-shadow: 0 0 0 3px rgba(157, 78, 221, 0.1);
    }

    .form-textarea {
        resize: vertical;
        min-height: 80px;
    }

    .form-checkbox-wrapper {
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

    .form-checkbox-wrapper:hover {
        border-color: var(--border-light);
        background: var(--bg-hover);
    }

    .form-checkbox {
        width: 18px;
        height: 18px;
        cursor: pointer;
        accent-color: var(--primary);
    }

    .form-checkbox-label {
        font-size: 14px;
        color: var(--text-primary);
        cursor: pointer;
        flex: 1;
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

    .form-section {
        margin-bottom: 32px;
        padding-bottom: 32px;
        border-bottom: 1px solid var(--border);
    }

    .form-section:last-child {
        border-bottom: none;
        margin-bottom: 0;
        padding-bottom: 0;
    }

    .section-title {
        font-size: 18px;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 16px;
    }

    .form-actions {
        display: flex;
        gap: 12px;
        margin-top: 32px;
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

    .btn-secondary:hover {
        background: var(--bg-hover);
        border-color: var(--border-light);
    }

    .error-message {
        color: var(--error);
        font-size: 12px;
        margin-top: 6px;
    }

    .alert-error {
        padding: 12px 16px;
        border-radius: 8px;
        margin-bottom: 24px;
        background: rgba(248, 113, 113, 0.1);
        border: 1px solid rgba(248, 113, 113, 0.3);
        color: var(--error);
        font-size: 14px;
    }

    .ssl-fields {
        display: none;
        margin-top: 16px;
        padding: 16px;
        background: var(--bg-dark);
        border: 1px solid var(--border);
        border-radius: 8px;
    }

    .ssl-fields.active {
        display: block;
    }
</style>
@endsection

@section('content')
<div class="page-header">
    <h1 class="page-title">إضافة موقع جديد</h1>
    <p class="page-description">
        أضف موقع جديد لحمايته بواسطة WAF. سيتم توليد ملف Nginx تلقائياً.
    </p>
    <a href="{{ route('sites.index') }}" class="back-link">← العودة للقائمة</a>
</div>

@if($errors->any())
    <div class="alert-error">
        <strong>يرجى تصحيح الأخطاء التالية:</strong>
        <ul style="margin: 8px 0 0 0; padding-left: 20px;">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="card">
    <form method="POST" action="{{ route('sites.store') }}">
        @csrf

        {{-- معلومات أساسية --}}
        <div class="form-section">
            <h2 class="section-title">📋 المعلومات الأساسية</h2>

            <div class="form-group">
                <label class="form-label">
                    اسم الموقع <span class="required">*</span>
                </label>
                <input 
                    type="text" 
                    name="name" 
                    class="form-input" 
                    value="{{ old('name') }}"
                    placeholder="مثال: Rabbit Clean"
                    required
                >
                <div class="form-help">اسم وصفي للموقع (للعرض فقط)</div>
                @error('name')
                    <div class="error-message">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label">
                    اسم النطاق (Domain) <span class="required">*</span>
                </label>
                <input 
                    type="text" 
                    name="server_name" 
                    class="form-input" 
                    value="{{ old('server_name') }}"
                    placeholder="مثال: rabbitclean.sa"
                    required
                >
                <div class="form-help">النطاق الخاص بالموقع (بدون www أو http)</div>
                @error('server_name')
                    <div class="error-message">{{ $message }}</div>
                @enderror
            </div>
        </div>

        {{-- السيرفر الخلفي --}}
        <div class="form-section">
            <h2 class="section-title">🖥️ السيرفر الخلفي (Backend)</h2>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">
                        IP السيرفر <span class="required">*</span>
                    </label>
                    <input 
                        type="text" 
                        name="backend_ip" 
                        class="form-input" 
                        value="{{ old('backend_ip') }}"
                        placeholder="مثال: 72.60.134.86"
                        required
                    >
                    <div class="form-help">عنوان IP للسيرفر الذي يستضيف الموقع</div>
                    @error('backend_ip')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">
                        المنفذ (Port) <span class="required">*</span>
                    </label>
                    <input 
                        type="number" 
                        name="backend_port" 
                        class="form-input" 
                        value="{{ old('backend_port', 80) }}"
                        placeholder="80"
                        min="1"
                        max="65535"
                        required
                    >
                    <div class="form-help">عادة: 80 للـ HTTP أو 443 للـ HTTPS</div>
                    @error('backend_port')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        {{-- إعدادات SSL --}}
        <div class="form-section">
            <h2 class="section-title">🔒 إعدادات SSL/HTTPS</h2>

            <div class="form-group">
                <label class="form-checkbox-wrapper">
                    <input 
                        type="checkbox" 
                        name="ssl_enabled" 
                        class="form-checkbox"
                        id="sslCheckbox"
                        {{ old('ssl_enabled') ? 'checked' : '' }}
                    >
                    <span class="form-checkbox-label">
                        تفعيل HTTPS (SSL/TLS)
                    </span>
                </label>
            </div>

            <div class="ssl-fields {{ old('ssl_enabled') ? 'active' : '' }}" id="sslFields">
                <div class="alert alert-info" style="background: #e3f2fd; border: 1px solid #2196f3; border-radius: 4px; padding: 12px; margin-bottom: 16px;">
                    <strong>ℹ️ ملاحظة:</strong> سيتم توليد شهادة SSL تلقائياً باستخدام Let's Encrypt (Certbot) عند حفظ الموقع.
                    <br><br>
                    <strong>المتطلبات:</strong>
                    <ul style="margin: 8px 0 0 20px; padding: 0;">
                        <li>يجب أن يكون النطاق (Domain) يشير إلى IP السيرفر</li>
                        <li>يجب أن يكون Certbot مثبت: <code>sudo apt-get install certbot python3-certbot-nginx</code></li>
                        <li>يجب أن يكون Nginx نشط ويعمل</li>
                        <li>يجب أن يكون الموقع متاحاً على HTTP (port 80) قبل توليد الشهادة</li>
                    </ul>
                    <br>
                    <strong>مسار الشهادة:</strong> <code>/etc/letsencrypt/live/{{ old('server_name', 'example.com') }}/fullchain.pem</code>
                    <br>
                    <strong>مسار المفتاح:</strong> <code>/etc/letsencrypt/live/{{ old('server_name', 'example.com') }}/privkey.pem</code>
                </div>
            </div>
        </div>

        {{-- ملاحظات --}}
        <div class="form-section">
            <h2 class="section-title">📝 ملاحظات (اختياري)</h2>

            <div class="form-group">
                <label class="form-label">
                    ملاحظات إضافية
                </label>
                <textarea 
                    name="notes" 
                    class="form-textarea"
                    placeholder="أي ملاحظات أو معلومات إضافية عن الموقع..."
                >{{ old('notes') }}</textarea>
                @error('notes')
                    <div class="error-message">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                ✓ حفظ الموقع
            </button>
            <a href="{{ route('sites.index') }}" class="btn btn-secondary">
                ✕ إلغاء
            </a>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const sslCheckbox = document.getElementById('sslCheckbox');
    const sslFields = document.getElementById('sslFields');

    if (sslCheckbox && sslFields) {
        sslCheckbox.addEventListener('change', function() {
            if (this.checked) {
                sslFields.classList.add('active');
            } else {
                sslFields.classList.remove('active');
            }
        });
    }
});
</script>
@endsection
