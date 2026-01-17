<?php

namespace App\Http\Controllers;

use App\Models\CountryRule;
use Illuminate\Http\Request;

class CountryRuleController extends Controller
{
    public function index()
    {
        $rules = CountryRule::orderByDesc('created_at')->get();

        return view('waf.country-rules', compact('rules'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'country_code' => 'required|string|size:2|uppercase',
            'type'         => 'required|in:allow,block',
        ]);

        // Check if rule already exists
        $exists = CountryRule::where('country_code', $data['country_code'])
            ->where('type', $data['type'])
            ->exists();

        if ($exists) {
            return redirect()->route('country-rules.index')
                ->withErrors(['country_code' => 'هذه القاعدة موجودة بالفعل.']);
        }

        $data['enabled'] = true;

        CountryRule::create($data);

        $this->syncFiles();

        return redirect()->route('country-rules.index')->with('status', 'تم حفظ القاعدة بنجاح وتمت مزامنتها مع Nginx.');
    }

    public function destroy(CountryRule $countryRule)
    {
        $countryRule->delete();

        $this->syncFiles();

        return redirect()->route('country-rules.index')->with('status', 'تم حذف القاعدة بنجاح وتمت مزامنة التغييرات مع Nginx.');
    }

    /**
     * مزامنة قاعدة البيانات مع ملف ModSecurity
     * 
     * ⚠️ مهم جداً:
     * - الطلبات تمر عبر Nginx مباشرة (لا تمر عبر Laravel)
     * - ModSecurity يحتاج قاعدة بيانات GeoIP محلية ليعمل
     * - بدون قاعدة بيانات GeoIP، قواعد @geoLookup لن تعمل
     * 
     * 📋 خطوات التثبيت:
     * 1. تثبيت المكتبات: sudo apt-get install libmaxminddb0 libmaxminddb-dev mmdb-bin
     * 2. تحميل قاعدة بيانات GeoLite2 من MaxMind
     * 3. إضافة SecGeoLookupDb في modsecurity.conf
     * 4. راجع: docs/GEOIP_SETUP.md للتفاصيل الكاملة
     */
    protected function syncFiles(): void
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
        
        // Blocked countries - deny access
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
        
        // Allowed countries - allow only these (if any)
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

        // Write file
        @file_put_contents($file, $content);

        // Ensure country-rules.conf is included in main.conf
        $this->ensureMainConfIncludes();

        // Reload Nginx
        @exec('sudo systemctl reload nginx > /dev/null 2>&1 &');
    }

    /**
     * التأكد من إضافة country-rules.conf إلى main.conf
     */
    protected function ensureMainConfIncludes(): void
    {
        $mainConf = '/etc/nginx/modsec/main.conf';
        
        if (!file_exists($mainConf)) {
            return;
        }

        $content = file_get_contents($mainConf);
        $includeLine = 'Include /etc/nginx/modsec/country-rules.conf';

        // التحقق من وجود السطر
        if (strpos($content, $includeLine) === false) {
            // إضافة السطر بعد url-rules.conf
            if (strpos($content, 'Include /etc/nginx/modsec/url-rules.conf') !== false) {
                $content = str_replace(
                    'Include /etc/nginx/modsec/url-rules.conf',
                    "Include /etc/nginx/modsec/url-rules.conf\n\nInclude /etc/nginx/modsec/country-rules.conf",
                    $content
                );
            } else {
                // إذا لم نجد url-rules.conf، نضيف في النهاية
                $content .= "\n{$includeLine}\n";
            }

            // حفظ الملف (يتطلب صلاحيات sudo)
            @file_put_contents($mainConf, $content);
        }
    }
}
