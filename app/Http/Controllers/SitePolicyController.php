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
        $this->checkSiteAccess($site);
        
        $policy = $site->policy ?? $site->policy()->create([
            'waf_enabled' => true,
            'paranoia_level' => 1,
            'inherit_global_rules' => true,
        ]);

        return view('waf.sites.policy', compact('site', 'policy'));
    }

    /**
     * التحقق من صلاحيات الوصول للموقع
     */
    protected function checkSiteAccess(Site $site): void
    {
        $user = auth()->user();
        
        // Super admin can access all sites
        if ($user->isSuperAdmin()) {
            return;
        }
        
        // Others can only access their tenant's sites
        if ($site->tenant_id !== $user->tenant_id) {
            abort(403, 'Access denied. You can only manage sites in your tenant.');
        }
    }

    /**
     * تحديث إعدادات WAF
     */
    public function update(Request $request, Site $site)
    {
        $this->checkSiteAccess($site);
        
        // Validate only non-checkbox fields
        $validated = $request->validate([
            'paranoia_level' => 'required|integer|min:1|max:4',
            'anomaly_threshold' => 'required|string',
            'requests_per_minute' => 'nullable|integer|min:1',
            'burst_size' => 'nullable|integer|min:1',
            'excluded_urls' => 'nullable|string',
            'excluded_ips' => 'nullable|string',
            'log_level' => 'required|in:debug,info,warn,error',
            'custom_modsec_rules' => 'nullable|string',
            'notes' => 'nullable|string',
            'custom_403_page_path' => 'nullable|string|max:500',
            'custom_403_message' => 'nullable|string',
        ]);

        // Handle checkboxes manually (they don't send values when unchecked)
        $data = $validated;
        $data['waf_enabled'] = $request->has('waf_enabled');
        $data['inherit_global_rules'] = $request->has('inherit_global_rules');
        $data['block_suspicious_user_agents'] = $request->has('block_suspicious_user_agents');
        $data['block_sql_injection'] = $request->has('block_sql_injection');
        $data['block_xss'] = $request->has('block_xss');
        $data['block_rce'] = $request->has('block_rce');
        $data['block_lfi'] = $request->has('block_lfi');
        $data['block_rfi'] = $request->has('block_rfi');
        $data['block_path_traversal'] = $request->has('block_path_traversal');
        $data['block_php_injection'] = $request->has('block_php_injection');
        $data['block_java_injection'] = $request->has('block_java_injection');
        $data['block_session_fixation'] = $request->has('block_session_fixation');
        $data['block_file_upload_attacks'] = $request->has('block_file_upload_attacks');
        $data['block_scanner_detection'] = $request->has('block_scanner_detection');
        $data['block_protocol_attacks'] = $request->has('block_protocol_attacks');
        $data['block_dos_protection'] = $request->has('block_dos_protection');
        $data['block_data_leakages'] = $request->has('block_data_leakages');
        $data['block_nodejs_injection'] = $request->has('block_nodejs_injection');
        $data['rate_limiting_enabled'] = $request->has('rate_limiting_enabled');
        $data['detailed_logging'] = $request->has('detailed_logging');

        // Get or create policy
        $policy = $site->policy ?? $site->policy()->create([
            'waf_enabled' => true,
            'paranoia_level' => 1,
            'anomaly_threshold' => '5',
            'inherit_global_rules' => true,
        ]);

        // Update policy
        $policy->update($data);

        // Log for debugging
        \Log::info('Site Policy Updated', [
            'site_id' => $site->id,
            'site_name' => $site->server_name,
            'policy_data' => $data,
        ]);

        // إعادة توليد ملف Nginx
        app(SiteController::class)->generateNginxConfig($site);

        return redirect()->route('sites.policy.edit', $site)
            ->with('status', 'WAF settings updated successfully!');
    }
}
