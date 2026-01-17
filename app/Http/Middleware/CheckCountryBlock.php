<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\GeoIpService;
use App\Models\CountryRule;
use Symfony\Component\HttpFoundation\Response;

class CheckCountryBlock
{
    protected $geoIpService;

    public function __construct(GeoIpService $geoIpService)
    {
        $this->geoIpService = $geoIpService;
    }

    /**
     * Handle an incoming request.
     * 
     * يتحقق من قواعد حظر الدول باستخدام GeoIpService
     * نفس الـ service المستخدم في Events
     */
    public function handle(Request $request, Closure $next): Response
    {
        // تخطي التحقق لصفحات WAF نفسها (لوحة التحكم)
        if ($request->is('waf*')) {
            return $next($request);
        }

        // الحصول على IP العميل (مع دعم Proxy/Load Balancer)
        $clientIp = $request->header('X-Real-IP') 
            ?: $request->header('X-Forwarded-For') 
            ?: $request->ip();
        
        // تنظيف IP إذا كان هناك عدة IPs في X-Forwarded-For
        if (strpos($clientIp, ',') !== false) {
            $ips = explode(',', $clientIp);
            $clientIp = trim($ips[0]);
        }
        
        // تخطي التحقق إذا كان IP محلي أو غير صحيح
        if (!$clientIp || !filter_var($clientIp, FILTER_VALIDATE_IP)) {
            return $next($request);
        }
        
        // تخطي IPs المحلية (127.0.0.1, 192.168.x.x, etc.)
        if (filter_var($clientIp, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
            return $next($request);
        }

        // الحصول على كود الدولة من GeoIpService (نفس الـ service المستخدم في Events)
        $countryCode = $this->geoIpService->getCountryFromIp($clientIp);

        if ($countryCode) {
            // التحقق من الدول المحظورة
            $isBlocked = CountryRule::where('type', 'block')
                ->where('enabled', true)
                ->where('country_code', $countryCode)
                ->exists();

            if ($isBlocked) {
                // تسجيل الحدث
                \App\Models\WafEvent::create([
                    'event_time' => now(),
                    'client_ip' => $clientIp,
                    'host' => $request->getHost(),
                    'method' => $request->method(),
                    'uri' => $request->getRequestUri(),
                    'status' => 403,
                    'message' => "Blocked country: {$countryCode}",
                    'country' => $countryCode,
                ]);

                abort(403, "Access denied from your country ({$countryCode})");
            }

            // التحقق من قائمة الدول المسموحة (إذا كانت موجودة)
            $allowedCountries = CountryRule::where('type', 'allow')
                ->where('enabled', true)
                ->pluck('country_code')
                ->toArray();

            if (!empty($allowedCountries)) {
                // إذا كانت هناك قائمة مسموحة، يجب أن تكون الدولة في القائمة
                if (!in_array($countryCode, $allowedCountries)) {
                    // تسجيل الحدث
                    \App\Models\WafEvent::create([
                        'event_time' => now(),
                        'client_ip' => $clientIp,
                        'host' => $request->getHost(),
                        'method' => $request->method(),
                        'uri' => $request->getRequestUri(),
                        'status' => 403,
                        'message' => "Country not in allowed list: {$countryCode}",
                        'country' => $countryCode,
                    ]);

                    abort(403, "Access denied. Your country ({$countryCode}) is not in the allowed list.");
                }
            }
        }

        return $next($request);
    }
}

