<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Site;
use App\Http\Controllers\SiteController;

class RegenerateSiteSsl extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'waf:regenerate-site-ssl {site_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Regenerate SSL certificate and Nginx config for a site';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $siteId = $this->argument('site_id');
        $site = Site::find($siteId);
        
        if (!$site) {
            $this->error("Site not found with ID: {$siteId}");
            return 1;
        }
        
        $this->info("Site: {$site->name} ({$site->server_name})");
        $this->info("SSL Enabled: " . ($site->ssl_enabled ? 'Yes' : 'No'));
        $this->info("SSL Cert: {$site->ssl_cert_path}");
        $this->info("SSL Key: {$site->ssl_key_path}");
        $this->line("");
        
        if (!$site->ssl_enabled) {
            $this->warn("SSL is not enabled for this site.");
            if ($this->confirm('Do you want to enable SSL and generate certificate?', true)) {
                $site->ssl_enabled = true;
                $site->ssl_cert_path = "/etc/letsencrypt/live/{$site->server_name}/fullchain.pem";
                $site->ssl_key_path = "/etc/letsencrypt/live/{$site->server_name}/privkey.pem";
                $site->save();
            } else {
                return 0;
            }
        }
        
        $controller = new SiteController();
        
        // Generate SSL certificate
        $this->info("Generating SSL certificate...");
        $reflection = new \ReflectionClass($controller);
        $method = $reflection->getMethod('generateSslCertificate');
        $method->setAccessible(true);
        $result = $method->invoke($controller, $site);
        
        if (!$result['success']) {
            $this->error("Failed to generate SSL certificate: " . $result['message']);
            return 1;
        }
        
        $this->info("✅ SSL certificate generated successfully!");
        
        // Regenerate Nginx config
        $this->info("Regenerating Nginx config...");
        $controller->generateNginxConfig($site);
        $this->info("✅ Nginx config regenerated!");
        
        return 0;
    }
}

