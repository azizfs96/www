<?php

namespace App\Http\Controllers;

use App\Models\IpRule;
use App\Models\Site;
use Illuminate\Http\Request;

class IpRuleController extends Controller
{
    public function index(Request $request)
    {
        $siteId = $request->get('site_id', 'global');
        $sites = Site::orderBy('name')->get();
        
        // جلب القواعد حسب الموقع المختار
        $query = IpRule::with('site')->orderByDesc('created_at');
        
        if ($siteId === 'global') {
            $query->global();
        } elseif ($siteId !== 'all') {
            $query->forSite($siteId);
        }
        
        $rules = $query->get();
        $selectedSite = $siteId === 'global' ? null : ($siteId === 'all' ? 'all' : Site::find($siteId));

        return view('waf.ip-rules', compact('rules', 'sites', 'selectedSite', 'siteId'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'site_id' => 'nullable|exists:sites,id',
            'ip'   => 'required|ip',
            'type' => 'required|in:allow,block',
        ]);

        IpRule::create($data);

        $this->syncFiles($data['site_id'] ?? null);

        return redirect()->route('ip-rules.index', ['site_id' => $request->site_id ?? 'global'])
            ->with('status', 'تم حفظ القاعدة بنجاح.');
    }

    public function destroy(IpRule $ipRule)
    {
        $siteId = $ipRule->site_id;
        $ipRule->delete();

        $this->syncFiles($siteId);

        return redirect()->route('ip-rules.index', ['site_id' => $siteId ?? 'global'])
            ->with('status', 'تم حذف القاعدة.');
    }

    /**
     * مزامنة قاعدة البيانات مع ملفات ModSecurity
     */
    protected function syncFiles($siteId = null): void
    {
        if ($siteId) {
            // مزامنة خاصة بموقع معين
            $this->syncSiteFiles($siteId);
        } else {
            // مزامنة القواعد العامة
            $this->syncGlobalFiles();
        }
    }

    /**
     * مزامنة القواعد العامة
     */
    protected function syncGlobalFiles(): void
    {
        // جلب القواعد من قاعدة البيانات
        $whitelistIps = IpRule::global()->where('type', 'allow')->pluck('ip')->filter()->values();
        $blacklistIps = IpRule::global()->where('type', 'block')->pluck('ip')->filter()->values();
        
        // تحويل إلى نص
        $whitelist = $whitelistIps->implode(PHP_EOL);
        $blacklist = $blacklistIps->implode(PHP_EOL);
        
        // Logging للتحقق
        \Log::info('Syncing global IP rules', [
            'whitelist_count' => $whitelistIps->count(),
            'blacklist_count' => $blacklistIps->count(),
            'whitelist_ips' => $whitelistIps->toArray(),
            'blacklist_ips' => $blacklistIps->toArray()
        ]);

        // كتابة في الملفات التي يستخدمها ModSecurity
        $whitelistFile = '/etc/nginx/modsec/whitelist.txt';
        $blacklistFile = '/etc/nginx/modsec/blacklist.txt';
        
        // إضافة سطر جديد فقط إذا كان هناك محتوى
        $whitelistContent = $whitelist ? $whitelist . PHP_EOL : '';
        $blacklistContent = $blacklist ? $blacklist . PHP_EOL : '';
        
        $whitelistWritten = @file_put_contents($whitelistFile, $whitelistContent);
        $blacklistWritten = @file_put_contents($blacklistFile, $blacklistContent);
        
        \Log::info('IP rules files written', [
            'whitelist_file' => $whitelistFile,
            'blacklist_file' => $blacklistFile,
            'whitelist_written' => $whitelistWritten !== false,
            'blacklist_written' => $blacklistWritten !== false,
            'whitelist_content_length' => strlen($whitelistContent),
            'blacklist_content_length' => strlen($blacklistContent)
        ]);
        
        // أيضاً كتابة في ملفات global- للتوافق مع الكود القديم
        @file_put_contents('/etc/nginx/modsec/global-whitelist.txt', $whitelistContent);
        @file_put_contents('/etc/nginx/modsec/global-blacklist.txt', $blacklistContent);

        // إعادة توليد ملفات ModSecurity لجميع المواقع لأن القواعد العامة تغيرت
        $this->regenerateAllSiteConfigs();

        @exec('sudo systemctl reload nginx > /dev/null 2>&1 &');
    }
    
    /**
     * إعادة توليد ملفات ModSecurity لجميع المواقع
     */
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
     * مزامنة قواعد موقع معين
     */
    protected function syncSiteFiles($siteId): void
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

        // إعادة توليد ملف ModSecurity لهذا الموقع
        if ($site->policy) {
            $siteController = new SiteController();
            $reflection = new \ReflectionClass($siteController);
            $method = $reflection->getMethod('generateModSecurityConfig');
            $method->setAccessible(true);
            $method->invoke($siteController, $site, $site->policy);
        }

        @exec('sudo systemctl reload nginx > /dev/null 2>&1 &');
    }
}
