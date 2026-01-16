<?php

namespace App\Http\Controllers;

use App\Models\Site;
use App\Models\SitePolicy;
use Illuminate\Http\Request;

class SitePolicyController extends Controller
{
    /**
     * عرض إعدادات WAF للموقع
     */
    public function edit(Site $site)
    {
        $policy = $site->policy ?? $site->policy()->create([
            'waf_enabled' => true,
            'paranoia_level' => 1,
            'inherit_global_rules' => true,
        ]);

        return view('waf.sites.policy', compact('site', 'policy'));
    }

    /**
     * تحديث إعدادات WAF
     */
    public function update(Request $request, Site $site)
    {
        $data = $request->validate([
            'waf_enabled' => 'boolean',
            'paranoia_level' => 'required|integer|min:1|max:4',
            'anomaly_threshold' => 'required|string',
            'inherit_global_rules' => 'boolean',
            'block_suspicious_user_agents' => 'boolean',
            'block_sql_injection' => 'boolean',
            'block_xss' => 'boolean',
            'block_rce' => 'boolean',
            'block_lfi' => 'boolean',
            'block_rfi' => 'boolean',
            'rate_limiting_enabled' => 'boolean',
            'requests_per_minute' => 'nullable|integer|min:1',
            'burst_size' => 'nullable|integer|min:1',
            'excluded_urls' => 'nullable|string',
            'excluded_ips' => 'nullable|string',
            'detailed_logging' => 'boolean',
            'log_level' => 'required|in:debug,info,warn,error',
            'custom_modsec_rules' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        // تحويل checkboxes
        $data['waf_enabled'] = $request->has('waf_enabled');
        $data['inherit_global_rules'] = $request->has('inherit_global_rules');
        $data['block_suspicious_user_agents'] = $request->has('block_suspicious_user_agents');
        $data['block_sql_injection'] = $request->has('block_sql_injection');
        $data['block_xss'] = $request->has('block_xss');
        $data['block_rce'] = $request->has('block_rce');
        $data['block_lfi'] = $request->has('block_lfi');
        $data['block_rfi'] = $request->has('block_rfi');
        $data['rate_limiting_enabled'] = $request->has('rate_limiting_enabled');
        $data['detailed_logging'] = $request->has('detailed_logging');

        $policy = $site->policy ?? $site->policy()->create([]);
        $policy->update($data);

        // إعادة توليد ملف Nginx
        app(SiteController::class)->generateNginxConfig($site);

        return redirect()->route('sites.policy.edit', $site)
            ->with('status', 'تم تحديث إعدادات WAF بنجاح!');
    }
}
