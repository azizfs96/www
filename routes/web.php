<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Models\WafEvent;
use App\Http\Controllers\IpRuleController;
use App\Http\Controllers\UrlRuleController;
use App\Http\Controllers\CountryRuleController;
use App\Http\Controllers\SiteController;
use App\Http\Controllers\SitePolicyController;
use App\Http\Controllers\WafEventController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\TenantController;
use App\Http\Controllers\TenantUserController;

// Welcome/Landing Page (Public)
Route::get('/', function () {
    return view('welcome');
});
Route::get('/welcome', function () {
    return view('welcome');
});
Route::get('/ar', function () {
    return view('welcome-ar');
});

// Authentication Routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Protect all WAF routes with authentication
Route::middleware(['auth'])->group(function () {
    
Route::get('/waf', function () {
    $user = auth()->user();
    
    // Use whereBetween instead of whereDate for better index usage
    // Use Saudi Arabia timezone for display, but convert to UTC for database query
    $startOfDay = now('Asia/Riyadh')->startOfDay();
    $endOfDay = now('Asia/Riyadh')->endOfDay();
    
    // Convert to UTC for database query (data is stored in UTC)
    $startOfDayUTC = $startOfDay->copy()->setTimezone('UTC');
    $endOfDayUTC = $endOfDay->copy()->setTimezone('UTC');
    
    $today = WafEvent::whereBetween('event_time', [$startOfDayUTC, $endOfDayUTC]);
    
    // Filter events by tenant if not super admin
    if (!$user->isSuperAdmin() && $user->tenant_id) {
        $siteIds = \App\Models\Site::where('tenant_id', $user->tenant_id)->pluck('id');
        // Tenant users should only see events for their tenant's sites (not global events)
        $today->whereIn('site_id', $siteIds);
    }

    // Get unique hosts for dropdown (filtered by tenant) - use caching
    $cacheKey = 'waf_hosts_' . ($user->isSuperAdmin() ? 'all' : 'tenant_' . $user->tenant_id);
    $hosts = \Illuminate\Support\Facades\Cache::remember($cacheKey, 300, function () use ($user) {
        $hostsQuery = WafEvent::whereNotNull('host');
        if (!$user->isSuperAdmin() && $user->tenant_id) {
            $siteIds = \App\Models\Site::where('tenant_id', $user->tenant_id)->pluck('id');
            // Tenant users should only see hosts for their tenant's sites
            $hostsQuery->whereIn('site_id', $siteIds);
        }
        return $hostsQuery->distinct()
            ->orderBy('host')
            ->pluck('host')
            ->unique()
            ->values();
    });

    // Cache statistics for 60 seconds to reduce database load
    $statsCacheKey = 'waf_stats_' . ($user->isSuperAdmin() ? 'all' : 'tenant_' . $user->tenant_id) . '_' . now('Asia/Riyadh')->format('Y-m-d');
    $stats = \Illuminate\Support\Facades\Cache::remember($statsCacheKey, 60, function () use ($today) {
        $baseQuery = clone $today;
        return [
            'total' => $baseQuery->count(),
            'blocked' => (clone $baseQuery)->where('status', 403)->count(),
        ];
    });

    // Get top IPs and rules (cached for 60 seconds)
    $topDataCacheKey = 'waf_top_data_' . ($user->isSuperAdmin() ? 'all' : 'tenant_' . $user->tenant_id) . '_' . now('Asia/Riyadh')->format('Y-m-d');
    $topData = \Illuminate\Support\Facades\Cache::remember($topDataCacheKey, 60, function () use ($today) {
        $baseQuery = clone $today;
        return [
            'topIps' => (clone $baseQuery)
                ->selectRaw('client_ip, COUNT(*) as cnt')
                ->groupBy('client_ip')
                ->orderByDesc('cnt')
                ->limit(5)
                ->get(),
            'topRules' => (clone $baseQuery)
                ->selectRaw('rule_id, COUNT(*) as cnt')
                ->whereNotNull('rule_id')
                ->groupBy('rule_id')
                ->orderByDesc('cnt')
                ->limit(5)
                ->get(),
        ];
    });

    return view('waf.dashboard', [
        'total'   => $stats['total'],
        'blocked' => $stats['blocked'],
        'topIps'  => $topData['topIps'],
        'topRules' => $topData['topRules'],
        'hosts' => $hosts,
    ]);
});

// API route for chart data
Route::get('/waf/api/chart-data', function (Request $request) {
    $user = auth()->user();
    $host = $request->get('host');
    $hours = (int) $request->get('hours', 24); // Default 24 hours
    
    // Use database aggregations instead of fetching all events
    // Query database in UTC (where data is stored), then convert to Saudi Arabia timezone for display
    $startTime = now('Asia/Riyadh')->subHours($hours)->startOfHour();
    $endTime = now('Asia/Riyadh')->endOfHour();
    
    // Convert to UTC for database query (data is stored in UTC)
    $startTimeUTC = $startTime->copy()->setTimezone('UTC');
    $endTimeUTC = $endTime->copy()->setTimezone('UTC');
    
    $query = WafEvent::whereBetween('event_time', [$startTimeUTC, $endTimeUTC]);
    
    // Filter by tenant if not super admin
    if (!$user->isSuperAdmin() && $user->tenant_id) {
        $siteIds = \App\Models\Site::where('tenant_id', $user->tenant_id)->pluck('id');
        // Tenant users should only see events for their tenant's sites (not global events)
        $query->whereIn('site_id', $siteIds);
    }
    
    if ($host) {
        $query->where('host', $host);
    }
    
    // Use database aggregation for better performance
    // Group by hour and calculate counts directly in database
    $connection = \Illuminate\Support\Facades\DB::connection();
    $driver = $connection->getDriverName();
    
    // Convert event_time from UTC to Saudi Arabia timezone in database query
    if ($driver === 'sqlite') {
        // SQLite: Convert UTC to Asia/Riyadh (UTC+3)
        // Note: SQLite doesn't have timezone functions, so we'll handle conversion in PHP
        $hourlyStats = $query
            ->selectRaw('
                datetime(event_time, "+3 hours") as hour_utc,
                strftime("%Y-%m-%d %H:00:00", datetime(event_time, "+3 hours")) as hour,
                strftime("%H:%M", datetime(event_time, "+3 hours")) as label,
                SUM(CASE WHEN status = 200 THEN 1 ELSE 0 END) as allowed,
                SUM(CASE WHEN status = 403 THEN 1 ELSE 0 END) as blocked,
                SUM(CASE WHEN status = 404 THEN 1 ELSE 0 END) as notFound
            ')
            ->groupBy('hour', 'label')
            ->orderBy('hour')
            ->get();
    } else {
        // MySQL/MariaDB: Convert UTC to Asia/Riyadh (UTC+3)
        // Try CONVERT_TZ first, fallback to DATE_ADD if timezone tables not loaded
        try {
            $hourlyStats = $query
                ->selectRaw('
                    DATE_FORMAT(CONVERT_TZ(event_time, "+00:00", "+03:00"), "%Y-%m-%d %H:00:00") as hour,
                    DATE_FORMAT(CONVERT_TZ(event_time, "+00:00", "+03:00"), "%H:%i") as label,
                    SUM(CASE WHEN status = 200 THEN 1 ELSE 0 END) as allowed,
                    SUM(CASE WHEN status = 403 THEN 1 ELSE 0 END) as blocked,
                    SUM(CASE WHEN status = 404 THEN 1 ELSE 0 END) as notFound
                ')
                ->groupBy('hour', 'label')
                ->orderBy('hour')
                ->get();
        } catch (\Exception $e) {
            // Fallback: Use DATE_ADD if CONVERT_TZ fails (timezone tables not loaded)
            $hourlyStats = $query
                ->selectRaw('
                    DATE_FORMAT(DATE_ADD(event_time, INTERVAL 3 HOUR), "%Y-%m-%d %H:00:00") as hour,
                    DATE_FORMAT(DATE_ADD(event_time, INTERVAL 3 HOUR), "%H:%i") as label,
                    SUM(CASE WHEN status = 200 THEN 1 ELSE 0 END) as allowed,
                    SUM(CASE WHEN status = 403 THEN 1 ELSE 0 END) as blocked,
                    SUM(CASE WHEN status = 404 THEN 1 ELSE 0 END) as notFound
                ')
                ->groupBy('hour', 'label')
                ->orderBy('hour')
                ->get();
        }
    }
    
    // Create all hour buckets to ensure we have data for all hours
    // Use Saudi Arabia timezone for display
    $buckets = [];
    $now = now('Asia/Riyadh');
    
    for ($i = $hours - 1; $i >= 0; $i--) {
        $hour = $now->copy()->subHours($i)->startOfHour();
        $hourKey = $hour->format('Y-m-d H:00:00');
        $buckets[$hourKey] = [
            'label' => $hour->format('H:i'),
            'allowed' => 0,
            'blocked' => 0,
            'notFound' => 0,
        ];
    }
    
    // Fill buckets with actual data from database
    foreach ($hourlyStats as $stat) {
        if (isset($buckets[$stat->hour])) {
            $buckets[$stat->hour]['allowed'] = (int) $stat->allowed;
            $buckets[$stat->hour]['blocked'] = (int) $stat->blocked;
            $buckets[$stat->hour]['notFound'] = (int) $stat->notFound;
        }
    }
    
    // Extract data for chart
    $labels = [];
    $allowed = [];
    $blocked = [];
    $notFound = [];
    
    foreach ($buckets as $bucket) {
        $labels[] = $bucket['label'];
        $allowed[] = $bucket['allowed'];
        $blocked[] = $bucket['blocked'];
        $notFound[] = $bucket['notFound'];
    }
    
    return response()->json([
        'labels' => $labels,
        'datasets' => [
            [
                'label' => 'Allowed (200)',
                'data' => $allowed,
                'backgroundColor' => 'rgba(74, 222, 128, 0.3)',
                'borderColor' => 'rgba(74, 222, 128, 1)',
                'borderWidth' => 2,
                'tension' => 0.4,
            ],
            [
                'label' => 'Blocked (403)',
                'data' => $blocked,
                'backgroundColor' => 'rgba(239, 68, 68, 0.3)',
                'borderColor' => 'rgba(239, 68, 68, 1)',
                'borderWidth' => 2,
                'tension' => 0.4,
            ],
            [
                'label' => 'Not Found (404)',
                'data' => $notFound,
                'backgroundColor' => 'rgba(179, 179, 179, 0.3)',
                'borderColor' => 'rgba(179, 179, 179, 1)',
                'borderWidth' => 2,
                'tension' => 0.4,
            ],
        ],
    ]);
});

Route::get('/waf/events', function (Request $request) {
    $user = auth()->user();
    
    $baseQuery = WafEvent::query()->orderByDesc('event_time');
    
    // Filter by tenant if not super admin
    if (!$user->isSuperAdmin() && $user->tenant_id) {
        $siteIds = \App\Models\Site::where('tenant_id', $user->tenant_id)->pluck('id');
        // Tenant users should only see events for their tenant's sites (not global events)
        // Also filter by host if site_id is null (for backward compatibility)
        $siteServerNames = \App\Models\Site::where('tenant_id', $user->tenant_id)->pluck('server_name')->toArray();
        $baseQuery->where(function($q) use ($siteIds, $siteServerNames) {
            $q->whereIn('site_id', $siteIds)
              ->orWhere(function($subQ) use ($siteServerNames) {
                  $subQ->whereNull('site_id')
                       ->whereIn('host', $siteServerNames);
              });
        });
    }
    
    $query = clone $baseQuery;

    // فلتر بالحالة (status)
    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }

    // فلتر بالـ IP
    if ($request->filled('ip')) {
        $query->where('client_ip', 'like', '%'.$request->ip.'%');
    }

    // فلتر بالتاريخ (من) - use whereBetween for better index usage
    if ($request->filled('date_from')) {
        $dateFrom = \Carbon\Carbon::parse($request->date_from)->startOfDay();
        $query->where('event_time', '>=', $dateFrom);
    }

    // فلتر بالتاريخ (إلى) - use whereBetween for better index usage
    if ($request->filled('date_to')) {
        $dateTo = \Carbon\Carbon::parse($request->date_to)->endOfDay();
        $query->where('event_time', '<=', $dateTo);
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

    // Pagination - select only needed columns for better performance
    $perPage = $request->get('per_page', 25);
    $events = $query->select([
        'id',
        'event_time',
        'client_ip',
        'host',
        'uri',
        'method',
        'status',
        'rule_id',
        'severity',
        'message',
        'site_id'
    ])->paginate($perPage)->withQueryString();
    
    // إحصائيات سريعة (باستخدام base query للفلاتر فقط)
    $statsQuery = clone $baseQuery;
    if ($request->filled('status')) {
        $statsQuery->where('status', $request->status);
    }
    if ($request->filled('ip')) {
        $statsQuery->where('client_ip', 'like', '%'.$request->ip.'%');
    }
    if ($request->filled('date_from')) {
        $dateFrom = \Carbon\Carbon::parse($request->date_from)->startOfDay();
        $statsQuery->where('event_time', '>=', $dateFrom);
    }
    if ($request->filled('date_to')) {
        $dateTo = \Carbon\Carbon::parse($request->date_to)->endOfDay();
        $statsQuery->where('event_time', '<=', $dateTo);
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

Route::get('/waf/url-rules', [UrlRuleController::class, 'index'])->name('url-rules.index');
Route::get('/waf/url-rules/create', [UrlRuleController::class, 'create'])->name('url-rules.create');
Route::post('/waf/url-rules', [UrlRuleController::class, 'store'])->name('url-rules.store');
Route::delete('/waf/url-rules/{urlRule}', [UrlRuleController::class, 'destroy'])->name('url-rules.destroy');

Route::get('/waf/country-rules', [CountryRuleController::class, 'index'])->name('country-rules.index');
Route::post('/waf/country-rules', [CountryRuleController::class, 'store'])->name('country-rules.store');
Route::delete('/waf/country-rules/{countryRule}', [CountryRuleController::class, 'destroy'])->name('country-rules.destroy');

// إدارة المواقع (Sites Management)
Route::get('/waf/sites', [SiteController::class, 'index'])->name('sites.index');
Route::get('/waf/sites/create', [SiteController::class, 'create'])->name('sites.create');
Route::post('/waf/sites', [SiteController::class, 'store'])->name('sites.store');
Route::delete('/waf/sites/{site}', [SiteController::class, 'destroy'])->name('sites.destroy');
Route::patch('/waf/sites/{site}/toggle', [SiteController::class, 'toggle'])->name('sites.toggle');
Route::patch('/waf/sites/{site}/toggle-ssl', [SiteController::class, 'toggleSsl'])->name('sites.toggle-ssl');
Route::post('/waf/sites/{site}/fix-ssl', [SiteController::class, 'fixSsl'])->name('sites.fix-ssl');
Route::post('/waf/sites/regenerate', [SiteController::class, 'regenerateAll'])->name('sites.regenerate');

// إدارة Failover و Health Check
Route::get('/waf/sites/{site}/backends', [SiteController::class, 'showBackends'])->name('sites.backends');
Route::post('/waf/sites/{site}/backends/check', [SiteController::class, 'checkBackendHealth'])->name('sites.backends.check');
Route::post('/waf/sites/{site}/backends/{backendServer}/toggle-status', [SiteController::class, 'toggleBackendStatus'])->name('sites.backends.toggle-status');
Route::post('/waf/sites/{site}/backends/{backendServer}/check', [SiteController::class, 'checkSingleBackend'])->name('sites.backends.check-single');
Route::post('/waf/sites/{site}/backends/failover', [SiteController::class, 'manualFailover'])->name('sites.backends.failover');
Route::post('/waf/sites/{site}/backends/failover-mode', [SiteController::class, 'updateFailoverMode'])->name('sites.backends.failover-mode');

// إعدادات WAF لكل موقع (Site Policies)
Route::get('/waf/sites/{site}/policy', [SitePolicyController::class, 'edit'])->name('sites.policy.edit');
Route::put('/waf/sites/{site}/policy', [SitePolicyController::class, 'update'])->name('sites.policy.update');

// AI Analysis for WAF Events
Route::post('/waf/events/{event}/analyze', [WafEventController::class, 'analyze'])->name('events.analyze');
Route::post('/waf/events/analyze-pattern', [WafEventController::class, 'analyzePattern'])->name('events.analyze-pattern');

// Tenants Management (Super Admin only)
Route::middleware(['role:super_admin'])->group(function () {
    Route::resource('tenants', TenantController::class);
    Route::get('/tenants/{tenant}/users', [TenantUserController::class, 'index'])->name('tenants.users.index');
    Route::get('/tenants/{tenant}/users/create', [TenantUserController::class, 'create'])->name('tenants.users.create');
    Route::post('/tenants/{tenant}/users', [TenantUserController::class, 'store'])->name('tenants.users.store');
    Route::delete('/tenants/{tenant}/users/{user}', [TenantUserController::class, 'destroy'])->name('tenants.users.destroy');
});

}); // End of auth middleware group
