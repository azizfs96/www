<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use App\Models\WafEvent;
use App\Models\Site;
use App\Services\GeoIpService;

class ImportWafLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * You will call it like: php artisan waf:import-logs
     */
    protected $signature = 'waf:import-logs
        {path=/var/log/modsecurity/audit : Root path of ModSecurity audit logs}';

    /**
     * The console command description.
     */
    protected $description = 'Import ModSecurity JSON audit logs into the database';

    /**
     * Execute the console command.
     */
public function handle(): int
{
    $rootPath = $this->argument('path');

    if (! is_dir($rootPath)) {
        $this->error("Path not found: {$rootPath}");
        return self::FAILURE;
    }

    $this->info("Scanning logs under: {$rootPath}");

    $files = File::allFiles($rootPath);
    $count = 0;

    foreach ($files as $file) {
        $content = File::get($file->getRealPath());
        if (! $content) {
            continue;
        }

        $data = json_decode($content, true);
        if (! is_array($data) || ! isset($data['transaction'])) {
            continue;
        }

        $tx       = $data['transaction'];
        $req      = $tx['request']  ?? [];
        $res      = $tx['response'] ?? [];
        // ✅ هنا التعديل المهم
        $messages = $tx['messages'] ?? [];

        // نبحث عن أول رسالة فيها ruleId
        $primaryMessage = null;
        $primaryDetails = [];

        if (is_array($messages)) {
            foreach ($messages as $m) {
                if (! is_array($m)) {
                    continue;
                }
                $d = $m['details'] ?? [];
                if (isset($d['ruleId'])) {
                    $primaryMessage = $m;
                    $primaryDetails = $d;
                    break;
                }
            }

            // لو ما لقينا ruleId، ناخذ أول رسالة كـ fallback
            if (! $primaryMessage && isset($messages[0]) && is_array($messages[0])) {
                $primaryMessage = $messages[0];
                $primaryDetails = $messages[0]['details'] ?? [];
            }
        }

        $uniqueId = $tx['unique_id'] ?? null;
        if (! $uniqueId) {
            continue;
        }

        // تخطي السجل إذا كان مستورد من قبل
        if (WafEvent::where('unique_id', $uniqueId)->exists()) {
            continue;
        }

        $eventTime = isset($tx['time_stamp'])
            ? date('Y-m-d H:i:s', strtotime($tx['time_stamp']))
            : now();

        $clientIp = $tx['client_ip'] ?? null;
        $host = $req['headers']['Host'] ?? null;
        
        // Get country from IP using GeoIP service
        $geoIpService = app(GeoIpService::class);
        $country = $geoIpService->getCountryFromIp($clientIp);

        // Find site by host (server_name)
        $siteId = null;
        if ($host) {
            // Remove www. prefix if exists
            $hostWithoutWww = preg_replace('/^www\./', '', $host);
            
            // Try to find site by server_name
            $site = Site::where('server_name', $host)
                ->orWhere('server_name', $hostWithoutWww)
                ->orWhere('server_name', 'like', '%' . $host . '%')
                ->first();
            
            if ($site) {
                $siteId = $site->id;
            }
        }

        WafEvent::create([
            'site_id'    => $siteId,
            'event_time' => $eventTime,
            'client_ip'  => $clientIp,
            'country'    => $country,
            'host'       => $host,
            'uri'        => $req['uri'] ?? null,
            'method'     => $req['method'] ?? null,
            'status'     => $res['http_code'] ?? null,
            'rule_id'    => $primaryDetails['ruleId'] ?? null,
            'severity'   => $primaryDetails['severity'] ?? null,
            'message'    => $primaryMessage['message'] ?? null,
            'user_agent' => $req['headers']['User-Agent'] ?? null,
            'action'     => $primaryDetails['action'] ?? null,
            'unique_id'  => $uniqueId,
            'raw'        => $data,
        ]);

        $count++;
    }

    $this->info("Imported {$count} events.");

    return self::SUCCESS;
  }

}
