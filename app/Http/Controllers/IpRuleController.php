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
        $whitelist = IpRule::global()->where('type', 'allow')
            ->pluck('ip')
            ->filter()
            ->implode(PHP_EOL);

        $blacklist = IpRule::global()->where('type', 'block')
            ->pluck('ip')
            ->filter()
            ->implode(PHP_EOL);

        // كتابة في الملفات التي يستخدمها ModSecurity
        @file_put_contents('/etc/nginx/modsec/whitelist.txt', $whitelist . PHP_EOL);
        @file_put_contents('/etc/nginx/modsec/blacklist.txt', $blacklist . PHP_EOL);
        
        // أيضاً كتابة في ملفات global- للتوافق مع الكود القديم
        @file_put_contents('/etc/nginx/modsec/global-whitelist.txt', $whitelist . PHP_EOL);
        @file_put_contents('/etc/nginx/modsec/global-blacklist.txt', $blacklist . PHP_EOL);

        @exec('sudo systemctl reload nginx > /dev/null 2>&1 &');
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

        @exec('sudo systemctl reload nginx > /dev/null 2>&1 &');
    }
}
