<?php

namespace App\Http\Controllers;

use App\Models\UrlRule;
use Illuminate\Http\Request;

class UrlRuleController extends Controller
{
    public function index()
    {
        return view('waf.url-rules.index', [
            'rules' => UrlRule::orderBy('id', 'desc')->get(),
        ]);
    }

    public function create()
    {
        return view('waf.url-rules.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'nullable|string|max:255',
            'host'        => 'nullable|string|max:255',
            'path'        => 'required|string|max:255',
            'allowed_ips' => 'required|string',
        ]);

        $data['enabled'] = true;

        UrlRule::create($data);

        $this->syncFiles();

        return redirect('/waf/url-rules')->with('status', 'تم إضافة القاعدة بنجاح وتمت مزامنتها مع Nginx.');
    }

    public function destroy(UrlRule $urlRule)
    {
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
