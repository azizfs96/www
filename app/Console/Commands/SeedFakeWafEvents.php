<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\WafEvent;
use App\Models\Site;

class SeedFakeWafEvents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * Usage: php artisan waf:seed-fake-events {count=50}
     */
    protected $signature = 'waf:seed-fake-events {count=50}';

    /**
     * The console command description.
     */
    protected $description = 'Insert fake WAF events for testing (today\'s date).';

    public function handle(): int
    {
        $count = (int) $this->argument('count');
        if ($count <= 0) {
            $this->error('Count must be positive.');
            return self::FAILURE;
        }

        $this->info("Seeding {$count} fake WAF events...");

        $nowSaudi = now('Asia/Riyadh');
        $site = Site::first();

        for ($i = 0; $i < $count; $i++) {
            $ip = "192.0.2." . (($i % 250) + 1);
            $countries = ['SA', 'US', 'GB', 'AE', 'DE', 'FR', 'IN', 'BR', 'JP', 'CA'];
            $methods = ['GET', 'POST', 'PUT', 'DELETE'];
            $paths = [
                '/login',
                '/admin/panel',
                '/search?q=test',
                '/api/v1/users',
                '/wp-admin',
                '/index.php',
                '/contact',
                '/.env',
                '/admin/config',
                '/about'
            ];
            $rules = ['920350', '942100', '942110', '930100', '931100', '932100', '941100', null];

            WafEvent::create([
                'site_id'    => $site ? $site->id : null,
                'event_time' => $nowSaudi->copy()->subMinutes(rand(0, 60))->setTimezone('UTC'),
                'client_ip'  => $ip,
                'country'    => $countries[array_rand($countries)],
                'host'       => $site ? $site->server_name : 'demo.example.com',
                'uri'        => $paths[array_rand($paths)],
                'method'     => $methods[array_rand($methods)],
                'status'     => rand(0, 100) < 50 ? 403 : 200,
                'rule_id'    => $rules[array_rand($rules)],
                'severity'   => (string) rand(2, 5),
                'message'    => 'Fake test event generated for UI testing',
                'user_agent' => 'FakeAgent/1.0',
                'action'     => null,
                'unique_id'  => 'FAKE-' . $nowSaudi->timestamp . '-' . $i . '-' . bin2hex(random_bytes(3)),
                'raw'        => [],
            ]);
        }

        $this->info('Done seeding fake WAF events.');
        return self::SUCCESS;
    }
}


