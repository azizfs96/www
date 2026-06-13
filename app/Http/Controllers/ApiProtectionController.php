<?php

namespace App\Http\Controllers;

use App\Models\Site;
use Illuminate\Http\Request;

class ApiProtectionController extends Controller
{
    /**
     * التحقق من صلاحية الوصول للموقع
     */
    protected function authorizeSite(Site $site): void
    {
        $user = auth()->user();
        if (!$user->isSuperAdmin() && $site->tenant_id !== $user->tenant_id) {
            abort(403, 'Access denied.');
        }
    }

    /**
     * عرض صفحة Rate Limiting (مستقلة عن WAF Settings)
     */
    public function rateLimit()
    {
        $user = auth()->user();
        $query = Site::with('policy')->orderBy('name');
        if (!$user->isSuperAdmin() && $user->tenant_id) {
            $query->where('tenant_id', $user->tenant_id);
        }
        $sites = $query->get();

        return view('waf.api.rate-limit', compact('sites'));
    }

    /**
     * تحديث إعدادات Rate Limiting لموقع — مستقل عن سياسة WAF
     */
    public function updateRateLimit(Request $request, Site $site)
    {
        $this->authorizeSite($site);

        $data = $request->validate([
            'requests_per_minute' => 'nullable|integer|min:1|max:1000000',
            'burst_size'          => 'nullable|integer|min:1|max:100000',
            'rate_limit_path'     => 'nullable|string|max:255',
        ]);

        // الحصول على السياسة أو إنشاؤها دون المساس بإعدادات WAF الأخرى
        $policy = $site->policy ?? $site->policy()->create([
            'waf_enabled'          => true,
            'paranoia_level'       => 1,
            'anomaly_threshold'    => '5',
            'inherit_global_rules' => true,
        ]);

        $policy->rate_limiting_enabled = $request->boolean('rate_limiting_enabled');
        $policy->requests_per_minute   = $data['requests_per_minute'] ?? ($policy->requests_per_minute ?? 60);
        $policy->burst_size            = $data['burst_size'] ?? ($policy->burst_size ?? 10);
        $policy->rate_limit_path       = $data['rate_limit_path'] ?: '/api';
        $policy->save();

        // إعادة توليد إعداد nginx ليأخذ التغيير مفعوله
        try {
            (new SiteController())->generateNginxConfig($site);
        } catch (\Throwable $e) {
            \Log::warning('API Protection: nginx regen failed', ['site' => $site->id, 'error' => $e->getMessage()]);
        }

        return redirect()->route('api.rate-limit')
            ->with('status', "Rate limiting updated for {$site->name}.");
    }
}
