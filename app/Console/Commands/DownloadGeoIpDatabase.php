<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class DownloadGeoIpDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'waf:download-geoip-database';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Download GeoLite2-Country database from MaxMind';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("📥 Downloading GeoLite2-Country database...");
        
        // الحصول على المفاتيح من متغيرات البيئة
        $accountId = env('MAXMIND_ACCOUNT_ID');
        $licenseKey = env('MAXMIND_LICENSE_KEY');
        
        if (empty($licenseKey)) {
            $this->error("❌ MAXMIND_LICENSE_KEY not set in .env file!");
            $this->line("");
            $this->line("Please add to your .env file:");
            $this->line("MAXMIND_ACCOUNT_ID=your_account_id");
            $this->line("MAXMIND_LICENSE_KEY=your_license_key");
            $this->line("");
            $this->line("Get your free license key from:");
            $this->line("https://www.maxmind.com/en/accounts/current/license-key");
            return 1;
        }
        
        $url = "https://download.maxmind.com/app/geoip_download?edition_id=GeoLite2-Country&license_key={$licenseKey}&suffix=tar.gz";
        
        $downloadFile = '/tmp/GeoLite2-Country.tar.gz';
        $targetDir = '/usr/share/GeoIP';
        $targetFile = "{$targetDir}/GeoLite2-Country.mmdb";
        
        // Download
        $this->info("Downloading from MaxMind...");
        $command = "wget -q '{$url}' -O {$downloadFile}";
        exec($command, $output, $returnVar);
        
        if ($returnVar !== 0 || !file_exists($downloadFile)) {
            $this->error("❌ Download failed!");
            $this->line("");
            $this->line("Please download manually:");
            $this->line("wget '{$url}' -O GeoLite2-Country.tar.gz");
            return 1;
        }
        
        $this->info("✅ Download complete");
        
        // Extract
        $this->info("Extracting...");
        $extractDir = '/tmp/geolite2-extract';
        @exec("rm -rf {$extractDir}");
        @exec("mkdir -p {$extractDir}");
        @exec("cd {$extractDir} && tar -xzf {$downloadFile} 2>&1", $extractOutput, $extractReturn);
        
        // Find the .mmdb file
        $mmdbFile = null;
        $files = glob("{$extractDir}/*/GeoLite2-Country.mmdb");
        if (empty($files)) {
            $files = glob("{$extractDir}/**/GeoLite2-Country.mmdb");
        }
        
        if (empty($files)) {
            $this->error("❌ Could not find GeoLite2-Country.mmdb in archive");
            @exec("rm -rf {$extractDir} {$downloadFile}");
            return 1;
        }
        
        $mmdbFile = $files[0];
        $this->info("✅ Found: {$mmdbFile}");
        
        // Copy to target location
        $this->info("Installing to {$targetDir}...");
        @exec("sudo mkdir -p {$targetDir}");
        @exec("sudo cp '{$mmdbFile}' '{$targetFile}'");
        @exec("sudo chmod 644 '{$targetFile}'");
        
        if (!file_exists($targetFile)) {
            $this->error("❌ Installation failed (may need sudo)");
            $this->line("");
            $this->line("Please run manually:");
            $this->line("sudo mkdir -p {$targetDir}");
            $this->line("sudo cp '{$mmdbFile}' '{$targetFile}'");
            $this->line("sudo chmod 644 '{$targetFile}'");
            @exec("rm -rf {$extractDir} {$downloadFile}");
            return 1;
        }
        
        $size = filesize($targetFile);
        $this->info("✅ Installed successfully!");
        $this->line("   Location: {$targetFile}");
        $this->line("   Size: " . number_format($size / 1024 / 1024, 2) . " MB");
        
        // Test
        $this->info("Testing database...");
        $testResult = shell_exec("mmdblookup --file '{$targetFile}' --ip 8.8.8.8 country iso_code 2>&1");
        if (strpos($testResult, 'US') !== false || strpos($testResult, 'iso_code') !== false) {
            $this->info("✅ Database test passed!");
        } else {
            $this->warn("⚠️  Database test failed (but file exists)");
        }
        
        // Cleanup
        @exec("rm -rf {$extractDir} {$downloadFile}");
        
        $this->line("");
        $this->info("📋 Next steps:");
        $this->line("1. Add to modsecurity.conf:");
        $this->line("   SecGeoLookupDb {$targetFile}");
        $this->line("");
        $this->line("2. Test nginx config:");
        $this->line("   sudo nginx -t");
        $this->line("");
        $this->line("3. Reload nginx:");
        $this->line("   sudo systemctl reload nginx");
        
        return 0;
    }
}
