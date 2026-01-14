<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeoIpService
{
    /**
     * Get country code from IP address using ip-api.com (free API)
     * 
     * @param string|null $ip
     * @return string|null Country code (e.g., 'US', 'SA', 'GB') or null if not found
     */
    public function getCountryFromIp(?string $ip): ?string
    {
        if (empty($ip) || !filter_var($ip, FILTER_VALIDATE_IP)) {
            return null;
        }

        // Skip private/local IPs
        if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            return 'LOCAL';
        }

        // Cache for 24 hours to reduce API calls
        $cacheKey = "geoip:{$ip}";
        
        return Cache::remember($cacheKey, now()->addHours(24), function () use ($ip) {
            try {
                // Using ip-api.com free API (no key required, 45 requests/minute limit)
                $response = Http::timeout(3)
                    ->get("http://ip-api.com/json/{$ip}", [
                        'fields' => 'status,countryCode,country',
                    ]);

                if ($response->successful()) {
                    $data = $response->json();
                    
                    if ($data['status'] === 'success' && isset($data['countryCode'])) {
                        return $data['countryCode'];
                    }
                }
            } catch (\Exception $e) {
                Log::warning("GeoIP lookup failed for IP {$ip}: " . $e->getMessage());
            }

            return null;
        });
    }

    /**
     * Get country name from IP address
     * 
     * @param string|null $ip
     * @return string|null Country name or null
     */
    public function getCountryNameFromIp(?string $ip): ?string
    {
        if (empty($ip) || !filter_var($ip, FILTER_VALIDATE_IP)) {
            return null;
        }

        // Skip private/local IPs
        if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            return 'Local Network';
        }

        $cacheKey = "geoip:name:{$ip}";
        
        return Cache::remember($cacheKey, now()->addHours(24), function () use ($ip) {
            try {
                $response = Http::timeout(3)
                    ->get("http://ip-api.com/json/{$ip}", [
                        'fields' => 'status,country',
                    ]);

                if ($response->successful()) {
                    $data = $response->json();
                    
                    if ($data['status'] === 'success' && isset($data['country'])) {
                        return $data['country'];
                    }
                }
            } catch (\Exception $e) {
                Log::warning("GeoIP name lookup failed for IP {$ip}: " . $e->getMessage());
            }

            return null;
        });
    }

    /**
     * Batch lookup countries for multiple IPs (with rate limiting)
     * 
     * @param array $ips
     * @return array [ip => countryCode]
     */
    public function getCountriesForIps(array $ips): array
    {
        $results = [];
        $ips = array_unique(array_filter($ips));
        
        // Process in batches to respect rate limits
        $batches = array_chunk($ips, 10);
        
        foreach ($batches as $batch) {
            foreach ($batch as $ip) {
                $results[$ip] = $this->getCountryFromIp($ip);
            }
            
            // Small delay between batches to respect rate limits
            if (count($batches) > 1) {
                usleep(200000); // 0.2 seconds
            }
        }
        
        return $results;
    }
}

