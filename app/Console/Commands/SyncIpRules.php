<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\IpRule;
use App\Http\Controllers\IpRuleController;

class SyncIpRules extends Command
{
    protected $signature = 'waf:sync-ip-rules {--site-id= : Site ID to sync (optional, syncs global if not provided)}';

    protected $description = 'Sync IP rules from database to ModSecurity files';

    public function handle(): int
    {
        $siteId = $this->option('site-id');
        
        $controller = new IpRuleController();
        $reflection = new \ReflectionClass($controller);
        
        if ($siteId) {
            $this->info("Syncing IP rules for site ID: {$siteId}");
            $method = $reflection->getMethod('syncSiteFiles');
            $method->setAccessible(true);
            $method->invoke($controller, $siteId);
        } else {
            $this->info("Syncing global IP rules...");
            
            // عرض القواعد قبل المزامنة
            $whitelistCount = IpRule::global()->where('type', 'allow')->count();
            $blacklistCount = IpRule::global()->where('type', 'block')->count();
            
            $this->info("Found {$whitelistCount} whitelist rules and {$blacklistCount} blacklist rules");
            
            if ($whitelistCount > 0 || $blacklistCount > 0) {
                $whitelistIps = IpRule::global()->where('type', 'allow')->pluck('ip')->toArray();
                $blacklistIps = IpRule::global()->where('type', 'block')->pluck('ip')->toArray();
                
                if (!empty($whitelistIps)) {
                    $this->line("Whitelist IPs: " . implode(', ', $whitelistIps));
                }
                if (!empty($blacklistIps)) {
                    $this->line("Blacklist IPs: " . implode(', ', $blacklistIps));
                }
            }
            
            $method = $reflection->getMethod('syncGlobalFiles');
            $method->setAccessible(true);
            $method->invoke($controller);
        }
        
        $this->info("✅ IP rules synced successfully!");
        
        return 0;
    }
}

