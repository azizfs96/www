<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CountryRule;
use App\Services\GeoIpService;

class TestCountryBlock extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'waf:test-country-block {ip?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test country blocking for a specific IP';

    protected $geoIpService;

    public function __construct(GeoIpService $geoIpService)
    {
        parent::__construct();
        $this->geoIpService = $geoIpService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $ip = $this->argument('ip') ?: '8.8.8.8'; // Default to Google DNS
        
        $this->info("Testing country block for IP: {$ip}");
        
        // Get country code
        $countryCode = $this->geoIpService->getCountryFromIp($ip);
        $this->info("Country Code: " . ($countryCode ?: 'Unknown'));
        
        // Check blocked countries
        $blockedCountries = CountryRule::where('type', 'block')
            ->where('enabled', true)
            ->pluck('country_code')
            ->toArray();
        
        $this->info("Blocked Countries: " . (empty($blockedCountries) ? 'None' : implode(', ', $blockedCountries)));
        
        // Check allowed countries
        $allowedCountries = CountryRule::where('type', 'allow')
            ->where('enabled', true)
            ->pluck('country_code')
            ->toArray();
        
        $this->info("Allowed Countries: " . (empty($allowedCountries) ? 'None (all allowed)' : implode(', ', $allowedCountries)));
        
        // Check if IP should be blocked
        if ($countryCode) {
            if (in_array($countryCode, $blockedCountries)) {
                $this->error("❌ IP {$ip} from {$countryCode} SHOULD BE BLOCKED");
                return 1;
            }
            
            if (!empty($allowedCountries) && !in_array($countryCode, $allowedCountries)) {
                $this->error("❌ IP {$ip} from {$countryCode} SHOULD BE BLOCKED (not in allowed list)");
                return 1;
            }
            
            $this->info("✅ IP {$ip} from {$countryCode} is ALLOWED");
        } else {
            $this->warn("⚠️  Could not determine country for IP {$ip}");
        }
        
        return 0;
    }
}
