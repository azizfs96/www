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
     * ملاحظات مهمة:
     * 1. يتم التحقق من حظر الدول أيضاً عبر Middleware (CheckCountryBlock)
     *    الذي يستخدم GeoIpService (نفس الـ service المستخدم في Events)
     * 2. ModSecurity يتطلب قاعدة بيانات GeoIP محلية (مثل GeoLite2)
     *    يمكن تثبيتها من: https://dev.maxmind.com/geoip/geoip2/geolite2/
     * 3. الـ Middleware يعمل بشكل فوري ولا يحتاج قاعدة بيانات GeoIP محلية
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

        // Reload Nginx
        @exec('sudo systemctl reload nginx > /dev/null 2>&1 &');
    }
}
