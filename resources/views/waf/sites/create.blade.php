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

    .backend-server-card {
        background: var(--bg-dark);
        border: 1px solid var(--border);
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 16px;
        position: relative;
    }

    .backend-server-card.active {
        border-color: #10b981;
        background: rgba(16, 185, 129, 0.05);
    }

    .backend-server-card.standby {
        border-color: #f59e0b;
        background: rgba(245, 158, 11, 0.05);
    }

    .backend-server-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 16px;
    }

    .backend-server-title {
        font-size: 14px;
        font-weight: 600;
        color: var(--text-primary);
    }

    .backend-server-status {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 12px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 500;
    }

    .backend-server-status.active {
        background: rgba(16, 185, 129, 0.2);
        color: #10b981;
    }

    .backend-server-status.standby {
        background: rgba(245, 158, 11, 0.2);
        color: #f59e0b;
    }

    .remove-backend-btn {
        position: absolute;
        top: 16px;
        left: 16px;
        background: var(--error);
        color: white;
        border: none;
        border-radius: 6px;
        width: 28px;
        height: 28px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        transition: all 0.2s;
    }

    .remove-backend-btn:hover {
        background: #dc2626;
        transform: scale(1.1);
    }

    .status-toggle-group {
        display: flex;
        gap: 8px;
        margin-top: 12px;
    }

    .status-toggle-btn {
        flex: 1;
        padding: 8px 12px;
        border: 1px solid var(--border);
        border-radius: 6px;
        background: var(--bg-card);
        color: var(--text-secondary);
        cursor: pointer;
        font-size: 13px;
        text-align: center;
        transition: all 0.2s;
    }

    .status-toggle-btn.active {
        background: #10b981;
        color: white;
        border-color: #10b981;
    }

    .status-toggle-btn.standby {
        background: #f59e0b;
        color: white;
        border-color: #f59e0b;
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

        {{-- السيرفرات الخلفية - High Availability --}}
        <div class="form-section">
            <h2 class="section-title">🖥️ السيرفرات الخلفية (Backend Servers) - High Availability</h2>
            
            <div class="form-help" style="margin-bottom: 20px; padding: 12px; background: rgba(157, 78, 221, 0.1); border-radius: 8px; border: 1px solid rgba(157, 78, 221, 0.3);">
                <strong>ℹ️ ملاحظة:</strong> يمكنك إضافة عدة سيرفرات خلفية لضمان High Availability. 
                حدد سيرفر واحد على الأقل كـ <strong>Active</strong> والباقي كـ <strong>Standby</strong>. 
                عند فشل السيرفر النشط، سيتم التبديل تلقائياً إلى السيرفر الاحتياطي.
            </div>

            <div id="backendServersContainer">
                {{-- سيتم إضافة السيرفرات ديناميكياً هنا --}}
            </div>

            <button type="button" id="addBackendServer" class="btn btn-secondary" style="margin-top: 16px;">
                + إضافة سيرفر خلفي
            </button>

            @error('backend_servers')
                <div class="error-message" style="margin-top: 12px;">{{ $message }}</div>
            @enderror
        </div>

        {{-- إعدادات SSL --}}
        <div class="form-section">
            <h2 class="section-title">🔒 إعدادات SSL/HTTPS</h2>

            <div class="form-group">
                <label class="form-checkbox-wrapper">
                    <input type="hidden" name="ssl_enabled" value="0">
                    <input 
                        type="checkbox" 
                        name="ssl_enabled" 
                        class="form-checkbox"
                        id="sslCheckbox"
                        value="1"
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
    const sslHiddenInput = document.querySelector('input[type="hidden"][name="ssl_enabled"]');

    if (sslCheckbox && sslFields) {
        // عند تغيير checkbox
        sslCheckbox.addEventListener('change', function() {
            if (this.checked) {
                sslFields.classList.add('active');
                // إزالة hidden input عند تحديد checkbox (لإرسال '1' فقط)
                if (sslHiddenInput) {
                    sslHiddenInput.remove();
                }
            } else {
                sslFields.classList.remove('active');
                // إعادة إضافة hidden input عند إلغاء التحديد
                if (!sslHiddenInput || !sslHiddenInput.parentNode) {
                    const hidden = document.createElement('input');
                    hidden.type = 'hidden';
                    hidden.name = 'ssl_enabled';
                    hidden.value = '0';
                    sslCheckbox.parentNode.insertBefore(hidden, sslCheckbox);
                }
            }
        });
        
        // التحقق من الحالة الأولية
        if (sslCheckbox.checked && sslHiddenInput) {
            sslHiddenInput.remove();
        }
    }

    // إدارة السيرفرات الخلفية
    let backendServerIndex = 0;
    const container = document.getElementById('backendServersContainer');
    const addBtn = document.getElementById('addBackendServer');

    function createBackendServerCard(index, data = {}) {
        const card = document.createElement('div');
        card.className = `backend-server-card ${data.status || 'standby'}`;
        card.dataset.index = index;

        const status = data.status || 'standby';
        const statusText = status === 'active' ? 'نشط (Active)' : 'احتياطي (Standby)';
        const statusClass = status === 'active' ? 'active' : 'standby';

        card.innerHTML = `
            <button type="button" class="remove-backend-btn" onclick="removeBackendServer(${index})" title="حذف">×</button>
            <div class="backend-server-header">
                <div class="backend-server-title">سيرفر خلفي #${index + 1}</div>
                <div class="backend-server-status ${statusClass}">
                    ${status === 'active' ? '✓' : '○'} ${statusText}
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">
                        IP السيرفر <span class="required">*</span>
                    </label>
                    <input 
                        type="text" 
                        name="backend_servers[${index}][ip]" 
                        class="form-input" 
                        value="${data.ip || ''}"
                        placeholder="مثال: 72.60.134.86"
                        required
                    >
                </div>
                <div class="form-group">
                    <label class="form-label">
                        المنفذ (Port) <span class="required">*</span>
                    </label>
                    <input 
                        type="number" 
                        name="backend_servers[${index}][port]" 
                        class="form-input" 
                        value="${data.port || 80}"
                        placeholder="80"
                        min="1"
                        max="65535"
                        required
                    >
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">الأولوية (Priority)</label>
                    <input 
                        type="number" 
                        name="backend_servers[${index}][priority]" 
                        class="form-input" 
                        value="${data.priority || index + 1}"
                        placeholder="1"
                        min="1"
                    >
                    <div class="form-help">كلما قل الرقم، زادت الأولوية (1 = أعلى أولوية)</div>
                </div>
            </div>
            <div class="status-toggle-group">
                <input type="hidden" name="backend_servers[${index}][status]" value="${status}" id="status_${index}">
                <button type="button" class="status-toggle-btn ${status === 'active' ? 'active' : ''}" 
                        onclick="setBackendStatus(${index}, 'active')">
                    ✓ نشط (Active)
                </button>
                <button type="button" class="status-toggle-btn ${status === 'standby' ? 'standby' : ''}" 
                        onclick="setBackendStatus(${index}, 'standby')">
                    ○ احتياطي (Standby)
                </button>
            </div>
        `;

        return card;
    }

    function addBackendServer(data = {}) {
        const card = createBackendServerCard(backendServerIndex, data);
        container.appendChild(card);
        backendServerIndex++;
        updateRemoveButtons();
    }

    function removeBackendServer(index) {
        const card = container.querySelector(`[data-index="${index}"]`);
        if (card) {
            card.remove();
            updateRemoveButtons();
            reindexBackendServers();
        }
    }

    function setBackendStatus(index, status) {
        const card = container.querySelector(`[data-index="${index}"]`);
        if (!card) return;

        const statusInput = card.querySelector(`#status_${index}`);
        const statusBadge = card.querySelector('.backend-server-status');
        const activeBtn = card.querySelector('.status-toggle-btn:first-child');
        const standbyBtn = card.querySelector('.status-toggle-btn:last-child');

        statusInput.value = status;
        card.className = `backend-server-card ${status}`;

        if (status === 'active') {
            statusBadge.textContent = '✓ نشط (Active)';
            statusBadge.className = 'backend-server-status active';
            activeBtn.classList.add('active');
            standbyBtn.classList.remove('standby');
        } else {
            statusBadge.textContent = '○ احتياطي (Standby)';
            statusBadge.className = 'backend-server-status standby';
            activeBtn.classList.remove('active');
            standbyBtn.classList.add('standby');
        }
    }

    function updateRemoveButtons() {
        const cards = container.querySelectorAll('.backend-server-card');
        cards.forEach(card => {
            const removeBtn = card.querySelector('.remove-backend-btn');
            if (cards.length <= 1) {
                removeBtn.style.display = 'none';
            } else {
                removeBtn.style.display = 'flex';
            }
        });
    }

    function reindexBackendServers() {
        const cards = Array.from(container.querySelectorAll('.backend-server-card'));
        cards.forEach((card, newIndex) => {
            const oldIndex = parseInt(card.dataset.index);
            card.dataset.index = newIndex;
            
            // تحديث جميع الحقول
            card.querySelectorAll('input, label').forEach(input => {
                if (input.name) {
                    input.name = input.name.replace(`[${oldIndex}]`, `[${newIndex}]`);
                }
                if (input.id) {
                    input.id = input.id.replace(`_${oldIndex}`, `_${newIndex}`);
                }
            });

            // تحديث الأزرار
            const title = card.querySelector('.backend-server-title');
            if (title) title.textContent = `سيرفر خلفي #${newIndex + 1}`;

            const removeBtn = card.querySelector('.remove-backend-btn');
            if (removeBtn) {
                removeBtn.setAttribute('onclick', `removeBackendServer(${newIndex})`);
            }

            const activeBtn = card.querySelector('.status-toggle-btn:first-child');
            const standbyBtn = card.querySelector('.status-toggle-btn:last-child');
            if (activeBtn) activeBtn.setAttribute('onclick', `setBackendStatus(${newIndex}, 'active')`);
            if (standbyBtn) standbyBtn.setAttribute('onclick', `setBackendStatus(${newIndex}, 'standby')`);
        });
    }

    // جعل الدوال متاحة عالمياً
    window.removeBackendServer = removeBackendServer;
    window.setBackendStatus = setBackendStatus;

    // إضافة سيرفر افتراضي عند التحميل
    addBackendServer({ status: 'active', priority: 1 });

    // إضافة سيرفر جديد عند الضغط على الزر
    addBtn.addEventListener('click', function() {
        addBackendServer({ status: 'standby', priority: backendServerIndex + 1 });
    });

    // التحقق من وجود سيرفر نشط واحد على الأقل قبل الإرسال
    const form = document.querySelector('form');
    form.addEventListener('submit', function(e) {
        const activeServers = container.querySelectorAll('.backend-server-card.active');
        if (activeServers.length === 0) {
            e.preventDefault();
            alert('⚠️ يجب تحديد سيرفر واحد على الأقل كـ Active (نشط)');
            return false;
        }
    });
});
</script>
@endsection
