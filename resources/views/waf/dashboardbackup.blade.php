<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <title>لوحة مراقبة WAF</title>
    <style>
        :root {
            --bg: #0f172a;
            --bg-soft: #111827;
            --card: #020617;
            --primary: #22c55e;
            --primary-soft: #16a34a;
            --danger: #ef4444;
            --text: #e5e7eb;
            --text-muted: #9ca3af;
            --border: #1f2937;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        body {
            background: radial-gradient(circle at top, #1e293b 0, #020617 40%);
            color: var(--text);
            min-height: 100vh;
        }

        .page {
            max-width: 1100px;
            margin: 0 auto;
            padding: 24px 16px 40px;
        }

        .header {
            margin-bottom: 24px;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(34,197,94,0.1);
            color: var(--primary);
            border-radius: 999px;
            padding: 4px 12px;
            font-size: 12px;
            margin-bottom: 10px;
        }

        .badge-dot {
            width: 8px;
            height: 8px;
            border-radius: 999px;
            background: var(--primary);
        }

        .title-row {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            gap: 8px;
            flex-wrap: wrap;
        }

        .title-row h1 {
            font-size: 26px;
            font-weight: 600;
        }

        .title-row span {
            font-size: 12px;
            color: var(--text-muted);
        }

        .subtitle {
            font-size: 13px;
            color: var(--text-muted);
            margin-top: 8px;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
            margin-top: 20px;
            margin-bottom: 24px;
        }

        @media (max-width: 900px) {
            .grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 640px) {
            .grid {
                grid-template-columns: 1fr;
            }
        }

        .card {
            background: linear-gradient(135deg, #020617, #020617 60%, #082f49);
            border-radius: 14px;
            border: 1px solid rgba(148,163,184,0.2);
            padding: 14px 16px;
            box-shadow: 0 18px 40px rgba(15,23,42,0.7);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            margin-bottom: 6px;
        }

        .card-label {
            font-size: 12px;
            color: var(--text-muted);
        }

        .card-value {
            font-size: 22px;
            font-weight: 600;
        }

        .card-tag {
            font-size: 11px;
            color: var(--text-muted);
        }

        .card-trend {
            font-size: 11px;
            padding: 3px 8px;
            border-radius: 999px;
            background: rgba(34,197,94,0.08);
            color: var(--primary);
        }

        .card-trend.danger {
            background: rgba(248,113,113,0.1);
            color: var(--danger);
        }

        .layout {
            display: grid;
            grid-template-columns: 2fr 1.4fr;
            gap: 16px;
        }

        @media (max-width: 900px) {
            .layout {
                grid-template-columns: 1fr;
            }
        }

        .panel {
            background: var(--bg-soft);
            border-radius: 14px;
            border: 1px solid var(--border);
            padding: 14px 16px;
        }

        .panel-header {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            margin-bottom: 10px;
        }

        .panel-title {
            font-size: 14px;
            font-weight: 500;
        }

        .panel-subtitle {
            font-size: 11px;
            color: var(--text-muted);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }

        th, td {
            padding: 6px 4px;
            text-align: right;
        }

        th {
            text-align: right;
            color: var(--text-muted);
            font-weight: 500;
            border-bottom: 1px solid var(--border);
        }

        tr + tr td {
            border-top: 1px solid rgba(15,23,42,0.9);
        }

        tbody tr:hover {
            background: rgba(15,23,42,0.8);
        }

        .pill {
            display: inline-flex;
            align-items: center;
            padding: 2px 8px;
            border-radius: 999px;
            font-size: 11px;
        }

        .pill-danger {
            background: rgba(239,68,68,0.12);
            color: var(--danger);
        }

        .pill-muted {
            background: rgba(148,163,184,0.12);
            color: var(--text-muted);
        }

        .pill-rule {
            background: rgba(59,130,246,0.16);
            color: #93c5fd;
        }

        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 999px;
            margin-left: 4px;
        }

        .status-dot.red { background: var(--danger); }
        .status-dot.green { background: var(--primary); }

        .small-muted {
            font-size: 11px;
            color: var(--text-muted);
        }

        .link {
            font-size: 11px;
            color: #60a5fa;
            text-decoration: none;
        }

        .link:hover {
            text-decoration: underline;
        }

        .toolbar {
            margin-top: 10px;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .chip {
            font-size: 11px;
            padding: 4px 10px;
            border-radius: 999px;
            background: rgba(15,23,42,0.9);
            border: 1px solid var(--border);
            color: var(--text-muted);
        }

    </style>
</head>
<body>
<div class="page">

    <div class="header">
        <div class="badge">
            <span class="badge-dot"></span>
            WAF · Real-time Protection
        </div>

        <div class="title-row">
            <h1>لوحة مراقبة جدار الحماية للتطبيقات</h1>
            <span>الآن · {{ now()->format('Y-m-d H:i') }}</span>
        </div>

        <p class="subtitle">
            هذه الصفحة تعرض ملخّص الهجمات التي تم رصدها ومنعها على مستوى WAF، مع نظرة عامة على أكثر العناوين
            والمصادر استهدافًا للتطبيق.
        </p>
    </div>

    {{-- Cards --}}
    <div class="grid">
        <div class="card">
            <div class="card-header">
                <div>
                    <div class="card-label">إجمالي الأحداث اليوم</div>
                    <div class="card-value">{{ $total }}</div>
                </div>
                <span class="card-trend {{ $total > 0 ? 'danger' : '' }}">
                    {{ $total > 0 ? 'هجمات يتم رصدها' : 'لا توجد هجمات حالياً' }}
                </span>
            </div>
            <div class="small-muted">
                يشمل جميع الطلبات التي تم تحليلها عبر ModSecurity و OWASP CRS خلال هذا اليوم.
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <div>
                    <div class="card-label">المعاملات المحجوبة (403)</div>
                    <div class="card-value">{{ $blocked }}</div>
                </div>
                <span class="card-trend">
                    {{ $total > 0 ? round(($blocked / max($total,1)) * 100) : 0 }}٪ حظر
                </span>
            </div>
            <div class="small-muted">
                عدد الطلبات التي تم منعها قبل وصولها للتطبيق، مثل محاولات SQLi أو XSS.
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <div>
                    <div class="card-label">أعلى مصدر استهداف</div>
                    <div class="card-value" style="font-size:18px;">
                        {{ optional($topIps->first())->client_ip ?? 'لا يوجد' }}
                    </div>
                </div>
                <span class="card-tag">
                    {{ optional($topIps->first())->cnt ? optional($topIps->first())->cnt . ' طلب' : 'في انتظار بيانات' }}
                </span>
            </div>
            <div class="small-muted">
                أكثر عنوان IP قام بمحاولات وصول تم رصدها عبر WAF خلال هذا اليوم.
            </div>
        </div>
    </div>

    <div class="layout">
        {{-- Left: Top IPs --}}
        <div class="panel">
            <div class="panel-header">
                <div>
                    <div class="panel-title">أعلى عناوين IP من حيث عدد المحاولات</div>
                    <div class="panel-subtitle">
                        رصد لأكثر المصادر استهدافاً لنظامك خلال اليوم.
                    </div>
                </div>
                <a href="/waf/events" class="link">عرض جميع الأحداث</a>
            </div>

            <table>
                <thead>
                <tr>
                    <th>IP</th>
                    <th>عدد المحاولات</th>
                    <th>حالة عامة</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($topIps as $ip)
                    <tr>
                        <td>{{ $ip->client_ip }}</td>
                        <td>{{ $ip->cnt }}</td>
                        <td>
                            <span class="pill pill-muted">
                                <span class="status-dot red"></span>
                                نشاط مرتفع
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="small-muted">لا توجد بيانات لليوم الحالي.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        {{-- Right: Top Rules --}}
        <div class="panel">
            <div class="panel-header">
                <div>
                    <div class="panel-title">أكثر القواعد تفعيلًا (Rule IDs)</div>
                    <div class="panel-subtitle">
                        يوضّح نوع الهجمات الأكثر شيوعًا (SQLi, XSS, Protocol، وغيرها).
                    </div>
                </div>
            </div>

            <table>
                <thead>
                <tr>
                    <th>Rule ID</th>
                    <th>عدد مرات التفعيل</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($topRules as $rule)
                    <tr>
                        <td>
                            <span class="pill pill-rule">
                                {{ $rule->rule_id ?? 'غير محدد' }}
                            </span>
                        </td>
                        <td>{{ $rule->cnt }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2" class="small-muted">لا توجد قواعد مفعّلة لليوم الحالي.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>

            <div class="toolbar">
                <span class="chip">وضع القراءة فقط · Demo</span>
            </div>
        </div>
    </div>

</div>
</body>
</html>
