<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <title>إدارة عناوين IP (Whitelist / Blacklist)</title>
    <style>
        body {
            background: radial-gradient(circle at top, #0f172a 0, #020617 50%);
            color: #e5e7eb;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            min-height: 100vh;
        }
        .page {
            max-width: 900px;
            margin: 0 auto;
            padding: 24px 16px 40px;
        }
        a {
            color: #60a5fa;
            text-decoration: none;
            font-size: 12px;
        }
        a:hover { text-decoration: underline; }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            margin-bottom: 12px;
        }
        .header h1 { font-size: 20px; }

        .subtitle {
            font-size: 12px;
            color: #9ca3af;
            margin-bottom: 16px;
        }

        .card {
            background: rgba(15,23,42,0.9);
            border-radius: 12px;
            border: 1px solid #1f2937;
            padding: 14px 16px;
            margin-bottom: 16px;
        }

        label {
            font-size: 12px;
            color: #9ca3af;
        }

        input, select {
            background: #020617;
            border-radius: 999px;
            border: 1px solid #1f2937;
            color: #e5e7eb;
            font-size: 12px;
            padding: 6px 10px;
            margin-top: 4px;
            min-width: 160px;
        }

        button {
            border-radius: 999px;
            border: none;
            font-size: 12px;
            padding: 7px 14px;
            cursor: pointer;
        }

        .btn-primary {
            background: #22c55e;
            color: #022c22;
        }

        .btn-danger {
            background: #ef4444;
            color: #fee2e2;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
            margin-top: 8px;
        }
        th, td {
            padding: 7px 8px;
            text-align: right;
        }
        th {
            color: #9ca3af;
            border-bottom: 1px solid #1f2937;
        }
        td {
            border-top: 1px solid rgba(15,23,42,0.9);
        }
        tbody tr:nth-child(even) {
            background: rgba(15,23,42,0.8);
        }
        .pill {
            display: inline-flex;
            align-items: center;
            padding: 2px 8px;
            border-radius: 999px;
            font-size: 11px;
        }
        .pill-allow {
            background: rgba(34,197,94,0.16);
            color: #4ade80;
        }
        .pill-block {
            background: rgba(248,113,113,0.16);
            color: #fca5a5;
        }
        .status {
            font-size: 11px;
            color: #9ca3af;
            margin-bottom: 8px;
        }
    </style>
</head>
<body>
<div class="page">

    <div class="header">
        <h1>إدارة عناوين IP</h1>
        <a href="/waf">العودة للوحة WAF</a>
    </div>

    <p class="subtitle">
        من هنا يمكنك إضافة عناوين IP إلى قائمة السماح (Whitelist) أو الحظر (Blacklist). أي تعديل يتم
        مزامنته تلقائياً مع ModSecurity وإعادة تحميل Nginx.
    </p>

    @if (session('status'))
        <div class="status">{{ session('status') }}</div>
    @endif

    <div class="card">
        <form method="POST" action="{{ route('ip-rules.store') }}">
            @csrf
            <div style="display:flex; gap:12px; flex-wrap:wrap; align-items:flex-end;">
                <div style="display:flex; flex-direction:column;">
                    <label>عنوان IP</label>
                    <input type="text" name="ip" placeholder="مثال: 137.59.230.231" required>
                    @error('ip')
                        <span style="color:#f97316; font-size:11px;">{{ $message }}</span>
                    @enderror
                </div>
                <div style="display:flex; flex-direction:column;">
                    <label>النوع</label>
                    <select name="type" required>
                        <option value="allow">Whitelist (سماح)</option>
                        <option value="block">Blacklist (حظر)</option>
                    </select>
                    @error('type')
                        <span style="color:#f97316; font-size:11px;">{{ $message }}</span>
                    @enderror
                </div>
                <div>
                    <button type="submit" class="btn-primary">إضافة القاعدة</button>
                </div>
            </div>
        </form>
    </div>

    <div class="card">
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
                    <td>{{ $rule->ip }}</td>
                    <td>
                        @if ($rule->type === 'allow')
                            <span class="pill pill-allow">Whitelist</span>
                        @else
                            <span class="pill pill-block">Blacklist</span>
                        @endif
                    </td>
                    <td class="muted">{{ $rule->created_at }}</td>
                    <td>
                        <form method="POST" action="{{ route('ip-rules.destroy', $rule) }}"
                              onsubmit="return confirm('هل أنت متأكد من حذف هذه القاعدة؟');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-danger">حذف</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" style="text-align:center; color:#9ca3af;">
                        لا توجد قواعد حالياً. يمكنك إضافة IP أعلاه.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

</div>
</body>
</html>
