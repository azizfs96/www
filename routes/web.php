<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Models\WafEvent;
use App\Http\Controllers\IpRuleController;
use App\Http\Controllers\UrlRuleController;

// اختياري: خله يحول الصفحة الرئيسية للوحة WAF
Route::get('/', function () {
    return redirect('/waf');
});

Route::get('/waf', function () {
    $today = WafEvent::whereDate('event_time', today());

    return view('waf.dashboard', [
        'total'   => $today->count(),
        'blocked' => (clone $today)->where('status', 403)->count(),

        'topIps'  => (clone $today)
            ->selectRaw('client_ip, COUNT(*) as cnt')
            ->groupBy('client_ip')
            ->orderByDesc('cnt')
            ->limit(5)
            ->get(),

        'topRules' => (clone $today)
            ->selectRaw('rule_id, COUNT(*) as cnt')
            ->whereNotNull('rule_id')
            ->groupBy('rule_id')
            ->orderByDesc('cnt')
            ->limit(5)
            ->get(),
    ]);
});

Route::get('/waf/events', function (Request $request) {
    $baseQuery = WafEvent::query()->orderByDesc('event_time');
    $query = clone $baseQuery;

    // فلتر بالحالة (status)
    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }

    // فلتر بالـ IP
    if ($request->filled('ip')) {
        $query->where('client_ip', 'like', '%'.$request->ip.'%');
    }

    // فلتر بالتاريخ (من)
    if ($request->filled('date_from')) {
        $query->whereDate('event_time', '>=', $request->date_from);
    }

    // فلتر بالتاريخ (إلى)
    if ($request->filled('date_to')) {
        $query->whereDate('event_time', '<=', $request->date_to);
    }

    // بحث نصي
    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function($q) use ($search) {
            $q->where('client_ip', 'like', '%'.$search.'%')
              ->orWhere('host', 'like', '%'.$search.'%')
              ->orWhere('uri', 'like', '%'.$search.'%')
              ->orWhere('message', 'like', '%'.$search.'%')
              ->orWhere('rule_id', 'like', '%'.$search.'%');
        });
    }

    // Export CSV
    if ($request->format === 'csv') {
        $events = $query->get();
        $filename = 'waf_events_' . date('Y-m-d_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($events) {
            $file = fopen('php://output', 'w');
            
            // BOM for Excel UTF-8 support
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Headers
            fputcsv($file, [
                'التاريخ',
                'IP',
                'Host',
                'Method',
                'URI',
                'Status',
                'Rule ID',
                'Severity',
                'Message'
            ]);

            foreach ($events as $event) {
                fputcsv($file, [
                    $event->event_time ? $event->event_time->format('Y-m-d H:i:s') : '',
                    $event->client_ip ?? '',
                    $event->host ?? '',
                    $event->method ?? '',
                    $event->uri ?? '',
                    $event->status ?? '',
                    $event->rule_id ?? '',
                    $event->severity ?? '',
                    $event->message ?? ''
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    // Pagination
    $perPage = $request->get('per_page', 25);
    $events = $query->paginate($perPage)->withQueryString();
    
    // إحصائيات سريعة (باستخدام base query للفلاتر فقط)
    $statsQuery = clone $baseQuery;
    if ($request->filled('status')) {
        $statsQuery->where('status', $request->status);
    }
    if ($request->filled('ip')) {
        $statsQuery->where('client_ip', 'like', '%'.$request->ip.'%');
    }
    if ($request->filled('date_from')) {
        $statsQuery->whereDate('event_time', '>=', $request->date_from);
    }
    if ($request->filled('date_to')) {
        $statsQuery->whereDate('event_time', '<=', $request->date_to);
    }
    if ($request->filled('search')) {
        $search = $request->search;
        $statsQuery->where(function($q) use ($search) {
            $q->where('client_ip', 'like', '%'.$search.'%')
              ->orWhere('host', 'like', '%'.$search.'%')
              ->orWhere('uri', 'like', '%'.$search.'%')
              ->orWhere('message', 'like', '%'.$search.'%')
              ->orWhere('rule_id', 'like', '%'.$search.'%');
        });
    }
    
    $totalEvents = $statsQuery->count();
    $blockedCount = (clone $statsQuery)->where('status', 403)->count();
    $allowedCount = (clone $statsQuery)->where('status', 200)->count();
    $uniqueIps = $statsQuery->distinct('client_ip')->count('client_ip');

    // توصيف لبعض Rule IDs المعروفة (تقدر توسّعها)
    $ruleDescriptions = [
        '920350' => 'Protocol enforcement · Host header as IP',
        '942100' => 'SQL Injection detected via libinjection',
        '942110' => 'SQL Injection attack',
        '930100' => 'Path traversal attack',
        '931100' => 'Remote command execution attempt',
        '932100' => 'Remote file inclusion attempt',
        '941100' => 'XSS Attack Detected',
        '942200' => 'SQL Injection bypass attempt',
        '932160' => 'Remote Code Execution attempt',
    ];


    return view('waf.events', [
        'events'  => $events,
        'filters' => [
            'status'    => $request->status,
            'ip'        => $request->ip,
            'date_from' => $request->date_from,
            'date_to'   => $request->date_to,
            'search'    => $request->search,
        ],
        'ruleDescriptions' => $ruleDescriptions,
        'stats' => [
            'total' => $totalEvents,
            'blocked' => $blockedCount,
            'allowed' => $allowedCount,
            'unique_ips' => $uniqueIps,
        ],
    ]);
});
Route::get('/waf/ip-rules', [IpRuleController::class, 'index'])->name('ip-rules.index');
Route::post('/waf/ip-rules', [IpRuleController::class, 'store'])->name('ip-rules.store');
Route::delete('/waf/ip-rules/{ipRule}', [IpRuleController::class, 'destroy'])->name('ip-rules.destroy');

Route::get('/waf/url-rules', [UrlRuleController::class, 'index']);
Route::get('/waf/url-rules/create', [UrlRuleController::class, 'create']);
Route::post('/waf/url-rules', [UrlRuleController::class, 'store']);
