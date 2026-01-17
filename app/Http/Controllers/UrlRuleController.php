<?php

namespace App\Http\Controllers;

use App\Models\UrlRule;
use Illuminate\Http\Request;

class UrlRuleController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        $query = UrlRule::orderBy('id', 'desc');
        
        // Filter by tenant if not super admin
        if (!$user->isSuperAdmin()) {
            // Tenant users can only see rules for their tenant's sites (not global rules)
            $query->whereHas('site', function($q) use ($user) {
                $q->where('tenant_id', $user->tenant_id);
            });
        }
        
        return view('waf.url-rules.index', [
            'rules' => $query->get(),
        ]);
    }

    public function create()
    {
        return view('waf.url-rules.create');
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        
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
            // Global rule - only super admin can create
            if (!$user->isSuperAdmin()) {
                abort(403, 'Access denied. Only super admin can create global rules.');
            }
        } else {
            // Site-specific rule - verify site belongs to tenant
            if (!$user->isSuperAdmin()) {
                $site = Site::find($data['site_id']);
                if (!$site || $site->tenant_id !== $user->tenant_id) {
                    abort(403, 'Access denied. You can only create rules for your tenant sites.');
                }
            }
        }

        $data['enabled'] = true;

        UrlRule::create($data);

        $this->syncFiles();

        return redirect('/waf/url-rules')->with('status', 'تم إضافة القاعدة بنجاح وتمت مزامنتها مع Nginx.');
    }

    public function destroy(UrlRule $urlRule)
    {
        $user = auth()->user();
        
        // Check permissions
        if (empty($urlRule->site_id)) {
            // Global rule - only super admin can delete
            if (!$user->isSuperAdmin()) {
                abort(403, 'Access denied. Only super admin can delete global rules.');
            }
        } else {
            // Site-specific rule - verify site belongs to tenant
            if (!$user->isSuperAdmin()) {
                $site = Site::find($urlRule->site_id);
                if (!$site || $site->tenant_id !== $user->tenant_id) {
                    abort(403, 'Access denied. You can only delete rules for your tenant sites.');
                }
            }
        }
        
        $urlRule->delete();

        $this->syncFiles();

        return redirect('/waf/url-rules')->with('status', 'تم حذف القاعدة بنجاح وتمت مزامنة التغييرات مع Nginx.');
    }

    /**
     * مزامنة قاعدة البيانات مع ملف ModSecurity
     */
    protected function syncFiles(): void
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

            // نفصل IPs بأي مسافة أو فاصلة
            $ips = preg_split('/[\s,]+/', $rule->allowed_ips, -1, PREG_SPLIT_NO_EMPTY);
            if (empty($ips)) {
                continue;
            }

            // نخليها مفصولة بمسافة (ModSecurity يقبلها)
            $ipList = implode(' ', $ips);

            // نخلي ID فريد في رينج بعيد
            $baseId = 500000;
            $ruleId = $baseId + $rule->id;
            $msg    = addslashes($rule->name ?: "Restricted URL {$path}");
            
            $host = trim($rule->host ?? '');

            // نكوّن النص سطر بسطر
            // إذا كان هناك host محدد، نضيف شرط للـ host أولاً
            if ($host !== '') {
                // Chain rule: نتحقق من الـ host أولاً، ثم الـ path، ثم IP
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
                // بدون host: نطبق على كل المواقع
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

        // كتابة الملف
        @file_put_contents($file, $content);

        // إعادة تحميل Nginx (تحتاج صلاحيات sudo)
        @exec('sudo systemctl reload nginx > /dev/null 2>&1 &');
    }
}
