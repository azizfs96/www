<?php

namespace App\Http\Controllers;

use App\Models\IpRule;
use App\Models\UrlRule;
use App\Models\CountryRule;
use App\Models\Site;
use App\Models\WafEvent;
use Illuminate\Http\Request;

class FirewallController extends Controller
{
    /**
     * عرض جميع القواعد في شاشة واحدة
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $siteId = $request->get('site_id');
        
        // Filter sites based on user role
        if ($user->isSuperAdmin()) {
            $sites = Site::orderBy('name')->get();
        } else {
            $sites = Site::where('tenant_id', $user->tenant_id)->orderBy('name')->get();
            
            // If tenant user and no site_id specified, redirect to first site or 'all'
            if (empty($siteId)) {
                if ($sites->count() > 0) {
                    return redirect()->route('firewall.index', ['site_id' => $sites->first()->id]);
                } else {
                    return redirect()->route('firewall.index', ['site_id' => 'all']);
                }
            }
        }
        
        // Default to 'global' for super admin if no site_id
        if (empty($siteId)) {
            $siteId = 'global';
        }
        
        // جلب قواعد IP
        $ipQuery = IpRule::with('site')->orderByDesc('created_at');
        if ($siteId === 'global') {
            if (!$user->isSuperAdmin()) {
                if ($sites->count() > 0) {
                    return redirect()->route('firewall.index', ['site_id' => $sites->first()->id]);
                } else {
                    return redirect()->route('firewall.index', ['site_id' => 'all']);
                }
            }
            $ipQuery->global();
        } elseif ($siteId !== 'all') {
            $ipQuery->forSite($siteId);
            if (!$user->isSuperAdmin()) {
                $site = Site::find($siteId);
                if ($site && $site->tenant_id !== $user->tenant_id) {
                    abort(403);
                }
            }
        } else {
            if (!$user->isSuperAdmin()) {
                $ipQuery->whereHas('site', function($q) use ($user) {
                    $q->where('tenant_id', $user->tenant_id);
                });
            }
        }
        $ipRules = $ipQuery->get();
        
        // جلب قواعد URL
        $urlQuery = UrlRule::with('site')->orderByDesc('created_at');
        if ($siteId === 'global') {
            $urlQuery->whereNull('site_id');
        } elseif ($siteId !== 'all') {
            $urlQuery->forSite($siteId);
        } else {
            if (!$user->isSuperAdmin()) {
                $urlQuery->whereHas('site', function($q) use ($user) {
                    $q->where('tenant_id', $user->tenant_id);
                });
            }
        }
        $urlRules = $urlQuery->get();
        
        // جلب قواعد الدول
        $countryQuery = CountryRule::with('site')->orderByDesc('created_at');
        if ($siteId === 'global') {
            $countryQuery->whereNull('site_id');
        } elseif ($siteId !== 'all') {
            $countryQuery->forSite($siteId);
        } else {
            if (!$user->isSuperAdmin()) {
                $countryQuery->whereHas('site', function($q) use ($user) {
                    $q->where('tenant_id', $user->tenant_id);
                });
            }
        }
        $countryRules = $countryQuery->get();

        /**
         * حساب استخدام كل Rule بناءً على عدد الأحداث في جدول WafEvent
         * الفكرة:
         * - نبني Query أساسي للأحداث حسب الـ site_id المختار
         * - لكل نوع Rule (IP / URL / Country) نحسب عدد الأحداث المطابقة
         * - ثم نحسب النسبة المئوية لكل Rule من مجموع أحداث نفس النوع
         */

        // 1) Query أساسي للأحداث حسب الموقع
        $baseEventsQuery = WafEvent::query();
        if ($siteId === 'global') {
            $baseEventsQuery->whereNull('site_id');
        } elseif ($siteId !== 'all') {
            $baseEventsQuery->where('site_id', $siteId);
        }

        // 2) حساب استخدام قواعد IP
        $ipUsageCounts = [];
        foreach ($ipRules as $rule) {
            $count = (clone $baseEventsQuery)
                ->where('client_ip', $rule->ip)
                ->count();
            $ipUsageCounts[$rule->id] = $count;
        }

        // 3) حساب استخدام قواعد URL
        $urlUsageCounts = [];
        foreach ($urlRules as $rule) {
            $query = (clone $baseEventsQuery);

            if (!empty($rule->host)) {
                $query->where('host', $rule->host);
            }

            if (!empty($rule->path)) {
                // نستخدم like بسيط لمطابقة الـ path
                $query->where('uri', 'like', '%' . $rule->path . '%');
            }

            $urlUsageCounts[$rule->id] = $query->count();
        }

        // 4) حساب استخدام قواعد Country
        $countryUsageCounts = [];
        foreach ($countryRules as $rule) {
            $count = (clone $baseEventsQuery)
                ->where('country', strtoupper($rule->country_code))
                ->count();
            $countryUsageCounts[$rule->id] = $count;
        }

        // 5) نحسب إجمالي الأحداث لكل القواعد (كل الأنواع معاً)
        $totalEvents = array_sum($ipUsageCounts) + array_sum($urlUsageCounts) + array_sum($countryUsageCounts);

        // 6) نوزّع النسبة على كل رول من إجمالي الأحداث
        $ipRules = $ipRules->map(function ($rule) use ($ipUsageCounts, $totalEvents) {
            $eventsForRule = $ipUsageCounts[$rule->id] ?? 0;
            $rule->usage_percentage = $totalEvents > 0
                ? round(($eventsForRule / $totalEvents) * 100)
                : 0;
            return $rule;
        });

        $urlRules = $urlRules->map(function ($rule) use ($urlUsageCounts, $totalEvents) {
            $eventsForRule = $urlUsageCounts[$rule->id] ?? 0;
            $rule->usage_percentage = $totalEvents > 0
                ? round(($eventsForRule / $totalEvents) * 100)
                : 0;
            return $rule;
        });

        $countryRules = $countryRules->map(function ($rule) use ($countryUsageCounts, $totalEvents) {
            $eventsForRule = $countryUsageCounts[$rule->id] ?? 0;
            $rule->usage_percentage = $totalEvents > 0
                ? round(($eventsForRule / $totalEvents) * 100)
                : 0;
            return $rule;
        });

        // إجمالي القواعد (عدد الرولّات نفسه)
        $totalRules = $ipRules->count() + $urlRules->count() + $countryRules->count();
        
        $selectedSite = $siteId === 'global' ? null : ($siteId === 'all' ? 'all' : Site::find($siteId));

        return view('waf.firewall', compact('ipRules', 'urlRules', 'countryRules', 'sites', 'selectedSite', 'siteId', 'totalRules'));
    }

    /**
     * Store a new rule (IP, URL, or Country)
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        $ruleType = $request->input('rule_type');
        
        switch ($ruleType) {
            case 'ip':
                return $this->storeIpRule($request, $user);
            case 'url':
                return $this->storeUrlRule($request, $user);
            case 'country':
                return $this->storeCountryRule($request, $user);
            default:
                return redirect()->route('firewall.index')
                    ->withErrors(['rule_type' => 'Please select a rule type.']);
        }
    }

    /**
     * حفظ قاعدة IP
     */
    protected function storeIpRule(Request $request, $user)
    {
        $data = $request->validate([
            'site_id' => 'nullable|exists:sites,id',
            'ip'   => 'required|ip',
            'type' => 'required|in:allow,block',
        ]);

        // Check permissions
        if (empty($data['site_id'])) {
            if (!$user->isSuperAdmin()) {
                abort(403, 'Access denied. Only super admin can create global rules.');
            }
        } else {
            if (!$user->isSuperAdmin()) {
                $site = Site::find($data['site_id']);
                if (!$site || $site->tenant_id !== $user->tenant_id) {
                    abort(403, 'Access denied. You can only create rules for your tenant sites.');
                }
            }
        }

        IpRule::create($data);

        $this->syncIpFiles($data['site_id'] ?? null);

        return redirect()->route('firewall.index', ['site_id' => $request->site_id ?? 'global'])
            ->with('status', 'IP rule saved successfully.');
    }

    /**
     * حفظ قاعدة URL
     */
    protected function storeUrlRule(Request $request, $user)
    {
        $data = $request->validate([
            'name'        => 'nullable|string|max:255',
            'host'        => 'nullable|string|max:255',
            'path'        => 'required|string|max:255',
            'allowed_ips' => 'required|string',
            'site_id'     => 'nullable|exists:sites,id',
        ]);

        // If host is provided, find site by host
        if (empty($data['site_id']) && !empty($data['host'])) {
            $host = $data['host'];
            $hostWithoutWww = preg_replace('/^www\./', '', $host);
            
            $site = Site::where('server_name', $host)
                ->orWhere('server_name', $hostWithoutWww)
                ->first();
            
            if ($site) {
                $data['site_id'] = $site->id;
            }
        }

        // Check permissions
        if (empty($data['site_id'])) {
            if (!$user->isSuperAdmin()) {
                abort(403, 'Access denied. Only super admin can create global rules.');
            }
        } else {
            if (!$user->isSuperAdmin()) {
                $site = Site::find($data['site_id']);
                if (!$site || $site->tenant_id !== $user->tenant_id) {
                    abort(403, 'Access denied. You can only create rules for your tenant sites.');
                }
            }
        }

        $data['enabled'] = true;

        UrlRule::create($data);

        $this->syncUrlFiles();

        return redirect()->route('firewall.index', ['site_id' => $request->site_id ?? 'all'])
            ->with('status', 'URL rule added successfully and synchronized with Nginx.');
    }

    /**
     * حفظ قاعدة Country
     */
    protected function storeCountryRule(Request $request, $user)
    {
        $data = $request->validate([
            'country_code' => 'required|string|size:2|uppercase',
            'type'         => 'required|in:allow,block',
            'site_id'      => 'nullable|exists:sites,id',
        ]);

        // Check permissions
        if (empty($data['site_id'])) {
            if (!$user->isSuperAdmin()) {
                abort(403, 'Access denied. Only super admin can create global rules.');
            }
        } else {
            if (!$user->isSuperAdmin()) {
                $site = Site::find($data['site_id']);
                if (!$site || $site->tenant_id !== $user->tenant_id) {
                    abort(403, 'Access denied. You can only create rules for your tenant sites.');
                }
            }
        }

        // Check if rule already exists
        $exists = CountryRule::where('country_code', $data['country_code'])
            ->where('type', $data['type'])
            ->where('site_id', $data['site_id'] ?? null)
            ->exists();

        if ($exists) {
            return redirect()->route('firewall.index', ['site_id' => $request->site_id ?? 'all'])
                ->withErrors(['country_code' => 'This rule already exists.']);
        }

        $data['enabled'] = true;

        CountryRule::create($data);

        $this->syncCountryFiles();

        return redirect()->route('firewall.index', ['site_id' => $request->site_id ?? 'all'])
            ->with('status', 'Country rule saved successfully and synchronized with Nginx.');
    }

    /**
     * Delete a rule
     */
    public function destroy(Request $request, $id)
    {
        $user = auth()->user();
        $ruleType = $request->input('rule_type');
        
        switch ($ruleType) {
            case 'ip':
                return $this->destroyIpRule($id, $user);
            case 'url':
                return $this->destroyUrlRule($id, $user);
            case 'country':
                return $this->destroyCountryRule($id, $user);
            default:
                return redirect()->route('firewall.index')
                    ->withErrors(['rule_type' => 'Invalid rule type.']);
        }
    }

    /**
     * حذف قاعدة IP
     */
    protected function destroyIpRule($id, $user)
    {
        $ipRule = IpRule::findOrFail($id);
        
        // Check permissions
        if (empty($ipRule->site_id)) {
            if (!$user->isSuperAdmin()) {
                abort(403, 'Access denied. Only super admin can delete global rules.');
            }
        } else {
            if (!$user->isSuperAdmin()) {
                $site = Site::find($ipRule->site_id);
                if (!$site || $site->tenant_id !== $user->tenant_id) {
                    abort(403, 'Access denied. You can only delete rules for your tenant sites.');
                }
            }
        }
        
        $siteId = $ipRule->site_id;
        $ipRule->delete();

        $this->syncIpFiles($siteId);

        return redirect()->route('firewall.index', ['site_id' => $siteId ?? 'global'])
            ->with('status', 'IP rule deleted successfully.');
    }

    /**
     * حذف قاعدة URL
     */
    protected function destroyUrlRule($id, $user)
    {
        $urlRule = UrlRule::findOrFail($id);
        
        // Check permissions
        if (empty($urlRule->site_id)) {
            if (!$user->isSuperAdmin()) {
                abort(403, 'Access denied. Only super admin can delete global rules.');
            }
        } else {
            if (!$user->isSuperAdmin()) {
                $site = Site::find($urlRule->site_id);
                if (!$site || $site->tenant_id !== $user->tenant_id) {
                    abort(403, 'Access denied. You can only delete rules for your tenant sites.');
                }
            }
        }
        
        $urlRule->delete();

        $this->syncUrlFiles();

        return redirect()->route('firewall.index', ['site_id' => $urlRule->site_id ?? 'all'])
            ->with('status', 'URL rule deleted successfully and changes synchronized with Nginx.');
    }

    /**
     * حذف قاعدة Country
     */
    protected function destroyCountryRule($id, $user)
    {
        $countryRule = CountryRule::findOrFail($id);
        
        // Check permissions
        if (empty($countryRule->site_id)) {
            if (!$user->isSuperAdmin()) {
                abort(403, 'Access denied. Only super admin can delete global rules.');
            }
        } else {
            if (!$user->isSuperAdmin()) {
                $site = Site::find($countryRule->site_id);
                if (!$site || $site->tenant_id !== $user->tenant_id) {
                    abort(403, 'Access denied. You can only delete rules for your tenant sites.');
                }
            }
        }
        
        $countryRule->delete();

        $this->syncCountryFiles();

        return redirect()->route('firewall.index', ['site_id' => $countryRule->site_id ?? 'all'])
            ->with('status', 'Country rule deleted successfully and changes synchronized with Nginx.');
    }

    /**
     * مزامنة ملفات IP Rules
     */
    protected function syncIpFiles($siteId = null): void
    {
        if ($siteId) {
            $this->syncSiteIpFiles($siteId);
        } else {
            $this->syncGlobalIpFiles();
        }
    }

    protected function syncGlobalIpFiles(): void
    {
        $whitelistIps = IpRule::global()->where('type', 'allow')->pluck('ip')->filter()->values();
        $blacklistIps = IpRule::global()->where('type', 'block')->pluck('ip')->filter()->values();
        
        $whitelist = $whitelistIps->implode(PHP_EOL);
        $blacklist = $blacklistIps->implode(PHP_EOL);
        
        $whitelistFile = '/etc/nginx/modsec/whitelist.txt';
        $blacklistFile = '/etc/nginx/modsec/blacklist.txt';
        
        $whitelistContent = $whitelist ? $whitelist . PHP_EOL : '';
        $blacklistContent = $blacklist ? $blacklist . PHP_EOL : '';
        
        @file_put_contents($whitelistFile, $whitelistContent);
        @file_put_contents($blacklistFile, $blacklistContent);
        @file_put_contents('/etc/nginx/modsec/global-whitelist.txt', $whitelistContent);
        @file_put_contents('/etc/nginx/modsec/global-blacklist.txt', $blacklistContent);

        $this->regenerateAllSiteConfigs();

        @exec('sudo systemctl reload nginx > /dev/null 2>&1 &');
    }

    protected function syncSiteIpFiles($siteId): void
    {
        $site = Site::find($siteId);
        if (!$site) return;

        $whitelist = IpRule::forSite($siteId)->where('type', 'allow')
            ->pluck('ip')
            ->filter()
            ->implode(PHP_EOL);

        $blacklist = IpRule::forSite($siteId)->where('type', 'block')
            ->pluck('ip')
            ->filter()
            ->implode(PHP_EOL);

        @file_put_contents("/etc/nginx/modsec/sites/{$site->server_name}-whitelist.txt", $whitelist . PHP_EOL);
        @file_put_contents("/etc/nginx/modsec/sites/{$site->server_name}-blacklist.txt", $blacklist . PHP_EOL);

        if ($site->policy) {
            $siteController = new SiteController();
            $reflection = new \ReflectionClass($siteController);
            $method = $reflection->getMethod('generateModSecurityConfig');
            $method->setAccessible(true);
            $method->invoke($siteController, $site, $site->policy);
        }

        @exec('sudo systemctl reload nginx > /dev/null 2>&1 &');
    }

    protected function regenerateAllSiteConfigs(): void
    {
        $sites = Site::where('enabled', true)->get();
        $siteController = new SiteController();
        $reflection = new \ReflectionClass($siteController);
        $method = $reflection->getMethod('generateModSecurityConfig');
        $method->setAccessible(true);
        
        foreach ($sites as $site) {
            if ($site->policy) {
                $method->invoke($siteController, $site, $site->policy);
            }
        }
    }

    /**
     * مزامنة ملفات URL Rules
     */
    protected function syncUrlFiles(): void
    {
        $file = '/etc/nginx/modsec/url-rules.conf';
        
        $rules = UrlRule::where('enabled', true)->get();
        
        $content = "# AUTO-GENERATED - DO NOT EDIT MANUALLY\n";
        $content .= "# Generated at: " . now() . "\n\n";

        foreach ($rules as $rule) {
            $path = trim($rule->path);
            if ($path === '') {
                continue;
            }

            $ips = preg_split('/[\s,]+/', $rule->allowed_ips, -1, PREG_SPLIT_NO_EMPTY);
            if (empty($ips)) {
                continue;
            }

            $ipList = implode(' ', $ips);
            $baseId = 500000;
            $ruleId = $baseId + $rule->id;
            $msg    = addslashes($rule->name ?: "Restricted URL {$path}");
            
            $host = trim($rule->host ?? '');

            if ($host !== '') {
                $content .= "SecRule REQUEST_HEADERS:Host \"@streq {$host}\" \\\n";
                $content .= "    \"id:{$ruleId},\\\n";
                $content .= "    phase:1,\\\n";
                $content .= "    log,\\\n";
                $content .= "    deny,\\\n";
                $content .= "    status:403,\\\n";
                $content .= "    chain,\\\n";
                $content .= "    msg:'{$msg}'\"\n";
                $content .= "    SecRule REQUEST_URI \"@beginsWith {$path}\" \\\n";
                $content .= "        \"chain\"\n";
                $content .= "    SecRule REMOTE_ADDR \"!@ipMatch {$ipList}\"\n\n";
            } else {
                $content .= "SecRule REQUEST_URI \"@beginsWith {$path}\" \\\n";
                $content .= "    \"id:{$ruleId},\\\n";
                $content .= "    phase:1,\\\n";
                $content .= "    log,\\\n";
                $content .= "    deny,\\\n";
                $content .= "    status:403,\\\n";
                $content .= "    chain,\\\n";
                $content .= "    msg:'{$msg}'\"\n";
                $content .= "    SecRule REMOTE_ADDR \"!@ipMatch {$ipList}\"\n\n";
            }
        }

        @file_put_contents($file, $content);
        @exec('sudo systemctl reload nginx > /dev/null 2>&1 &');
    }

    /**
     * مزامنة ملفات Country Rules
     */
    protected function syncCountryFiles(): void
    {
        $file = '/etc/nginx/modsec/country-rules.conf';
        
        $blockedCountries = CountryRule::where('type', 'block')
            ->where('enabled', true)
            ->pluck('country_code')
            ->toArray();
        
        $allowedCountries = CountryRule::where('type', 'allow')
            ->where('enabled', true)
            ->pluck('country_code')
            ->toArray();
        
        $content = "# Country Rules - Auto-generated\n";
        $content .= "# Generated at: " . now() . "\n";
        $content .= "# Note: Requires GeoIP database configured in ModSecurity\n\n";
        
        if (!empty($blockedCountries)) {
            $content .= "# Blocked Countries\n";
            foreach ($blockedCountries as $index => $countryCode) {
                $ruleId = 600000 + $index;
                $content .= "SecRule REMOTE_ADDR \"@geoLookup\" \\\n";
                $content .= "    \"id:{$ruleId},\\\n";
                $content .= "    phase:1,\\\n";
                $content .= "    log,\\\n";
                $content .= "    deny,\\\n";
                $content .= "    status:403,\\\n";
                $content .= "    msg:'Blocked country: {$countryCode}',\\\n";
                $content .= "    chain\"\n";
                $content .= "    SecRule GEO:COUNTRY_CODE \"@streq {$countryCode}\"\n\n";
            }
        }
        
        if (!empty($allowedCountries)) {
            $content .= "# Allowed Countries Only\n";
            $allowedList = implode('|', $allowedCountries);
            $ruleId = 601000;
            $content .= "SecRule REMOTE_ADDR \"@geoLookup\" \\\n";
            $content .= "    \"id:{$ruleId},\\\n";
            $content .= "    phase:1,\\\n";
            $content .= "    log,\\\n";
            $content .= "    deny,\\\n";
            $content .= "    status:403,\\\n";
            $content .= "    msg:'Country not in allowed list',\\\n";
            $content .= "    chain\"\n";
            $content .= "    SecRule GEO:COUNTRY_CODE \"!@rx ^({$allowedList})$\"\n\n";
        }

        @file_put_contents($file, $content);
        $this->ensureMainConfIncludes();
        @exec('sudo systemctl reload nginx > /dev/null 2>&1 &');
    }

    protected function ensureMainConfIncludes(): void
    {
        $mainConf = '/etc/nginx/modsec/main.conf';
        
        if (!file_exists($mainConf)) {
            return;
        }

        $content = file_get_contents($mainConf);
        $includeLine = 'Include /etc/nginx/modsec/country-rules.conf';

        if (strpos($content, $includeLine) === false) {
            if (strpos($content, 'Include /etc/nginx/modsec/url-rules.conf') !== false) {
                $content = str_replace(
                    'Include /etc/nginx/modsec/url-rules.conf',
                    "Include /etc/nginx/modsec/url-rules.conf\n\nInclude /etc/nginx/modsec/country-rules.conf",
                    $content
                );
            } else {
                $content .= "\n{$includeLine}\n";
            }

            @file_put_contents($mainConf, $content);
        }
    }
}

