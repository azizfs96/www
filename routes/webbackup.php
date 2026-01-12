<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Models\WafEvent;
use App\Http\Controllers\IpRuleController;


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
    $query = WafEvent::query()->orderByDesc('event_time');

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

    $events = $query->limit(100)->get();

    // توصيف لبعض Rule IDs المعروفة (تقدر توسّعها)
    $ruleDescriptions = [
        '920350' => 'Protocol enforcement · Host header as IP',
        '942100' => 'SQL Injection detected via libinjection',
        '942110' => 'SQL Injection attack',
        '930100' => 'Path traversal attack',
        '931100' => 'Remote command execution attempt',
        '932100' => 'Remote file inclusion attempt',
    ];


    return view('waf.events', [
        'events'  => $events,
        'filters' => [
            'status'    => $request->status,
            'ip'        => $request->ip,
            'date_from' => $request->date_from,
            'date_to'   => $request->date_to,
        ],
        'ruleDescriptions' => $ruleDescriptions,
    ]);
});
Route::get('/waf/ip-rules', [IpRuleController::class, 'index'])->name('ip-rules.index');
Route::post('/waf/ip-rules', [IpRuleController::class, 'store'])->name('ip-rules.store');
Route::delete('/waf/ip-rules/{ipRule}', [IpRuleController::class, 'destroy'])->name('ip-rules.destroy');

