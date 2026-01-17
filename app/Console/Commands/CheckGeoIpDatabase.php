<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CheckGeoIpDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'waf:check-geoip-database';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check if GeoIP database is installed and configured for ModSecurity';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("🔍 Checking GeoIP database configuration...");
        $this->line("");

        // Check common GeoIP database locations
        $possiblePaths = [
            '/usr/share/GeoIP/GeoLite2-Country.mmdb',
            '/var/lib/GeoIP/GeoLite2-Country.mmdb',
            '/opt/GeoIP/GeoLite2-Country.mmdb',
            '/etc/GeoIP/GeoLite2-Country.mmdb',
        ];

        $found = false;
        foreach ($possiblePaths as $path) {
            if (file_exists($path)) {
                $this->info("✅ Found GeoIP database: {$path}");
                $size = filesize($path);
                $this->line("   Size: " . number_format($size / 1024 / 1024, 2) . " MB");
                $this->line("   Permissions: " . substr(sprintf('%o', fileperms($path)), -4));
                $found = true;
                break;
            }
        }

        if (!$found) {
            $this->error("❌ GeoIP database not found!");
            $this->line("");
            $this->warn("⚠️  Country blocking in ModSecurity will NOT work without GeoIP database.");
            $this->line("");
            $this->info("📋 To install:");
            $this->line("   1. sudo apt-get install libmaxminddb0 libmaxminddb-dev mmdb-bin");
            $this->line("   2. Download GeoLite2-Country.mmdb from MaxMind");
            $this->line("   3. Place it in /usr/share/GeoIP/");
            $this->line("   4. Add SecGeoLookupDb in modsecurity.conf");
            $this->line("");
            $this->line("📖 See docs/GEOIP_SETUP.md for detailed instructions");
            return 1;
        }

        // Check if mmdblookup is available
        $mmdbCheck = shell_exec('which mmdblookup 2>/dev/null');
        if ($mmdbCheck) {
            $this->info("✅ mmdblookup is installed");
            
            // Test with Google DNS
            $testResult = shell_exec("mmdblookup --file {$path} --ip 8.8.8.8 2>&1");
            if (strpos($testResult, 'US') !== false || strpos($testResult, 'country') !== false) {
                $this->info("✅ Database is working (tested with 8.8.8.8)");
            } else {
                $this->warn("⚠️  Database test failed");
            }
        } else {
            $this->warn("⚠️  mmdblookup not found (install: sudo apt-get install mmdb-bin)");
        }

        // Check ModSecurity configuration
        $modsecConf = '/etc/modsecurity/modsecurity.conf';
        if (file_exists($modsecConf)) {
            $content = file_get_contents($modsecConf);
            if (strpos($content, 'SecGeoLookupDb') !== false) {
                $this->info("✅ SecGeoLookupDb found in modsecurity.conf");
            } else {
                $this->warn("⚠️  SecGeoLookupDb not found in modsecurity.conf");
                $this->line("   Add this line to {$modsecConf}:");
                $this->line("   SecGeoLookupDb {$path}");
            }
        } else {
            $this->warn("⚠️  modsecurity.conf not found at {$modsecConf}");
        }

        // Check country-rules.conf
        $countryRules = '/etc/nginx/modsec/country-rules.conf';
        if (file_exists($countryRules)) {
            $this->info("✅ country-rules.conf exists");
            $content = file_get_contents($countryRules);
            if (strpos($content, '@geoLookup') !== false) {
                $this->info("✅ Rules use @geoLookup (requires GeoIP database)");
            }
        } else {
            $this->warn("⚠️  country-rules.conf not found");
        }

        $this->line("");
        $this->info("✅ Check complete!");
        
        return 0;
    }
}

