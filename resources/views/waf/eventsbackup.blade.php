<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <title>أحداث WAF</title>
    <style>
        :root {
            --bg: #020617;
            --bg-soft: #020617;
            --panel: #020617;
            --border: #1f2937;
            --text: #e5e7eb;
            --muted: #9ca3af;
            --danger: #ef4444;
            --ok: #22c55e;
            --warning: #f97316;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        body {
            background: radial-gradient(circle at top, #0f172a 0, #020617 50%);
            color: var(--text);
            min-height: 100vh;
        }

        .page {
            max-width: 1150px;
            margin: 0 auto;
            padding: 24px 16px 40px;
        }

        .header {
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            gap: 8px;
            flex-wrap: wrap;
        }

        .header h1 {
            font-size: 22px;
            font-weight: 600;
        }

        .header a {
            font-size: 12px;
            color: #60a5fa;
            text-decoration: none;
        }

        .header a:hover {
            text-decoration: underline;
        }

        .subtitle {
            font-size: 12px;
            color: var(--muted);
            margin-bottom: 14px;
        }

        .filters {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin-bottom: 14px;
            padding: 10px;
            background: rgba(15,23,42,0.9);
            border-radius: 10px;
            border: 1px solid var(--border);
        }

        .filters label {
            font-size: 11px;
            color: var(--muted);
        }

        .filters-group {
            display: flex;
            flex-direction: column;
            gap: 3px;
        }

        .filters input,
        .filters select {
            background: #020617;
            border-radius: 999px;
            border: 1px solid var(--border);
            color: var(--text);
            font-size: 12px;
            padding: 6px 10px;
            min-width: 120px;
        }

        .filters button {
            border-radius: 999px;
            border: none;
            font-size: 12px;
            padding: 7px 14px;
            cursor: pointer;
        }

        .filters .btn-primary {
            background: #22c55e;
            color: #022c22;
        }

        .filters .btn-reset {
            background: transparent;
            color: var(--muted);
            border: 1px solid var(--border);
        }

        .filters-actions {
            display: flex;
            gap: 6px;
        }

        .filters .btn-export {
            background: #0ea5e9;
            color: #022c22;
        }

        .table-wrapper {
            margin-top: 6px;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid var(--border);
            background: rgba(15,23,42,0.9);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
        }

        thead {
            background: #020617;
        }

        th, td {
            padding: 7px 8px;
            text-align: right;
            white-space: nowrap;
        }

        th {
            color: var(--muted);
            font-weight: 500;
            border-bottom: 1px solid var(--border);
        }

        td {
            border-top: 1px solid rgba(15,23,42,0.9);
        }

        tbody tr:nth-child(even) {
            background: rgba(15,23,42,0.8);
        }

        tbody tr:nth-child(odd) {
            background: rgba(15,23,42,0.6);
        }

        tbody tr:hover {
            background: rgba(15,23,42,1);
        }

        .pill {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 2px 8px;
            border-radius: 999px;
            font-size: 10px;
        }

        .pill-red {
            background: rgba(248,113,113,0.16);
            color: var(--danger);
        }

        .pill-green {
            background: rgba(34,197,94,0.14);
            color: var(--ok);
        }

        .pill-gray {
            background: rgba(148,163,184,0.14);
            color: var(--muted);
        }

        .pill-attack {
            background: rgba(59,130,246,0.18);
            color: #bfdbfe;
        }

        .dot {
            width: 6px;
            height: 6px;
            border-radius: 999px;
        }

        .dot-red { background: var(--danger); }
        .dot-green { background: var(--ok); }
        .dot-gray { background: var(--muted); }

        .uri {
            max-width: 260px;
            overflow: hidden;
            text-overflow: ellipsis;
            direction: ltr;
        }

        .host {
            max-width: 160px;
            overflow: hidden;
            text-overflow: ellipsis;
            direction: ltr;
        }

        .msg {
            max-width: 260px;
            overflow: hidden;
            text-overflow: ellipsis;
            direction: ltr;
        }

        .muted {
            color: var(--muted);
        }

        .footer-note {
            margin-top: 8px;
            font-size: 11px;
            color: var(--muted);
        }

        @media (max-width: 900px) {
            .uri, .msg {
                max-width: 160px;
            }
        }

        @media (max-width: 700px) {
            th:nth-child(2), td:nth-child(2), /* IP */
            th:nth-child(3), td:nth-child(3)  /* Host */ {
                display: none;
            }
        }
    </style>
</head>
<body>
<div class="page">

    <div class="header">
        <h1>سجل أحداث جدار الحماية للتطبيقات</h1>
        <a href="/waf">العودة للوحة المراقبة</a>
    </div>

    <p class="subtitle">
        أحدث الطلبات التي مرّت عبر WAF، مع تصنيف حسب الحالة ونوع الهجوم (إن وُجدت قاعدة مفعّلة).
    </p>

    {{-- Filters --}}
    <form method="GET" action="/waf/events" class="filters">
        <div class="filters-group">
            <label>حالة HTTP</label>
            <select name="status">
                <option value="">الكل</option>
                <option value="403" {{ ($filters['status'] ?? '') == '403' ? 'selected' : '' }}>403 (محجوبة)</option>
                <option value="200" {{ ($filters['status'] ?? '') == '200' ? 'selected' : '' }}>200 (مسموح بها)</option>
                <option value="404" {{ ($filters['status'] ?? '') == '404' ? 'selected' : '' }}>404 (غير موجود)</option>
            </select>
        </div>

        <div class="filters-group">
            <label>عنوان IP</label>
            <input type="text" name="ip" placeholder="مثال: 137.59.230.231"
                   value="{{ $filters['ip'] ?? '' }}">
        </div>

        <div class="filters-group">
            <label>من تاريخ</label>
            <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}">
        </div>

        <div class="filters-group">
            <label>إلى تاريخ</label>
            <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}">
        </div>

        <div class="filters-group" style="justify-content:flex-end;">
            <label>&nbsp;</label>
            <div class="filters-actions">
                <button type="submit" class="btn-primary">تطبيق الفلتر</button>
                <a href="/waf/events">
                    <button type="button" class="btn-reset">إعادة الضبط</button>
                </a>
                {{-- زر تصدير CSV: يعيد نفس الفلاتر مع format=csv --}}
                <button type="submit" name="format" value="csv" class="btn-export">
                    تصدير CSV
                </button>
            </div>
        </div>
    </form>

    {{-- Table --}}
    <div class="table-wrapper">
        <table>
            <thead>
            <tr>
                <th>الوقت</th>
                <th>IP</th>
                <th>Host</th>
                <th>المسار / URI</th>
                <th>الحالة</th>
                <th>Rule ID</th>
                <th>نوع الهجوم</th>
                <th>الرسالة</th>
            </tr>
            </thead>
            <tbody>
            @forelse ($events as $event)
                @php
                    $status = (int) $event->status;
                    $rule  = $event->rule_id;
                    $desc  = $rule ? ($ruleDescriptions[$rule] ?? null) : null;
                @endphp
                <tr>
                    <td class="muted">{{ $event->event_time }}</td>
                    <td>{{ $event->client_ip }}</td>
                    <td class="host">{{ $event->host }}</td>
                    <td class="uri">{{ $event->uri }}</td>
                    <td>
                        @if ($status === 403)
                            <span class="pill pill-red">
                                <span class="dot dot-red"></span>
                                403 · محجوبة
                            </span>
                        @elseif ($status === 200)
                            <span class="pill pill-green">
                                <span class="dot dot-green"></span>
                                200 · مسموح
                            </span>
                        @elseif ($status === 404)
                            <span class="pill pill-gray">
                                <span class="dot dot-gray"></span>
                                404 · غير موجود
                            </span>
                        @else
                            <span class="pill pill-gray">
                                <span class="dot dot-gray"></span>
                                {{ $status }}
                            </span>
                        @endif
                    </td>
                    <td>
                        @if ($rule)
                            <span class="pill pill-gray">{{ $rule }}</span>
                        @else
                            <span class="muted">-</span>
                        @endif
                    </td>
                    <td>
                        @if ($desc)
                            <span class="pill pill-attack">{{ $desc }}</span>
                        @else
                            <span class="muted">غير مصنّف</span>
                        @endif
                    </td>
                    <td class="msg">
                        @if ($event->message)
                            {{ $event->message }}
                        @else
                            <span class="muted">لا توجد رسالة من القاعدة</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="muted" style="text-align:center; padding:12px;">
                        لا توجد أحداث مسجلة وفق الفلاتر الحالية.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="footer-note">
        يتم عرض آخر 100 طلب فقط لأغراض المراقبة اللحظية. يمكنك توسيع المنظومة لاحقاً لدعم التصفية بالتاريخ
        مع pagination وتنبيهات فورية عند هجمات معينة.
    </div>

</div>
</body>
</html>
