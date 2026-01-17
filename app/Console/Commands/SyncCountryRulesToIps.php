<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CountryRule;
use App\Services\GeoIpService;
use Illuminate\Support\Facades\Log;

class SyncCountryRulesToIps extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'waf:sync-country-rules-to-ips 
                            {--force : Force update even if IPs exist}
                            {--limit=1000 : Maximum IPs to process per country}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Convert country rules to IP rules in ModSecurity blacklist/whitelist';

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
        $this->info("🔄 Syncing country rules to IP rules...");
        
        $blockedCountries = CountryRule::where('type', 'block')
            ->where('enabled', true)
            ->pluck('country_code')
            ->toArray();
        
        $allowedCountries = CountryRule::where('type', 'allow')
            ->where('enabled', true)
            ->pluck('country_code')
            ->toArray();

        if (empty($blockedCountries) && empty($allowedCountries)) {
            $this->warn("⚠️  No country rules found!");
            return 0;
        }

        $this->warn("⚠️  This method is not practical for large-scale blocking.");
        $this->warn("⚠️  Recommended: Install GeoIP database for ModSecurity");
        $this->line("");

        // هذا الحل غير عملي لأن IPs كثيرة جداً
        // الحل الأفضل هو تثبيت قاعدة بيانات GeoIP محلية
        
        $this->error("❌ Cannot convert countries to IPs automatically.");
        $this->line("");
        $this->info("📋 Solution: Install GeoIP database for ModSecurity");
        $this->line("");
        $this->line("Steps to install GeoIP database:");
        $this->line("1. Install libmaxminddb:");
        $this->line("   sudo apt-get install libmaxminddb0 libmaxminddb-dev mmdb-bin");
        $this->line("");
        $this->line("2. Download GeoLite2 database:");
        $this->line("   wget https://download.maxmind.com/app/geoip_download?edition_id=GeoLite2-Country&license_key=YOUR_KEY&suffix=tar.gz");
        $this->line("");
        $this->line("3. Configure ModSecurity to use GeoIP database");
        $this->line("");
        $this->line("Or use the existing country-rules.conf with @geoLookup");
        
        return 1;
    }
}

