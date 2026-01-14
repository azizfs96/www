<?php

namespace Database\Seeders;

use App\Models\WafEvent;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Carbon\Carbon;

class WafEventsTodaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $ruleIds = [
            '920350' => 'Protocol enforcement · Host header as IP',
            '942100' => 'SQL Injection detected via libinjection',
            '942110' => 'SQL Injection attack',
            '930100' => 'Path traversal attack',
            '931100' => 'Remote command execution attempt',
            '932100' => 'Remote file inclusion attempt',
            '941100' => 'XSS Attack Detected',
            '942200' => 'SQL Injection bypass attempt',
            '932160' => 'Remote Code Execution attempt',
            null => null,
        ];

        $ipAddresses = [
            '192.168.1.100',
            '10.0.0.45',
            '172.16.0.23',
            '203.0.113.15',
            '198.51.100.42',
            '137.59.230.231',
            '185.220.101.12',
            '45.146.165.88',
            '103.152.112.162',
            '185.220.100.255',
            '185.56.83.83',
            '45.79.107.106',
            '167.94.138.51',
            '162.247.72.201',
            '185.220.102.4',
            '51.252.199.169',
            '1.2.3.4',
        ];

        // تضمين rabbitclean.sa و domains أخرى
        $hosts = [
            'rabbitclean.sa',
            'example.com',
            'test-domain.org',
            'demo-site.net',
            'api.example.com',
            'admin.test.com',
            'www.demo.org',
        ];

        $methods = ['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'OPTIONS'];

        $uris = [
            '/admin/login',
            '/api/users',
            '/wp-admin/wp-login.php',
            '/phpmyadmin/index.php',
            '/.env',
            '/config/database.php',
            '/../../etc/passwd',
            '/api/auth/login',
            '/search?q=<script>alert(1)</script>',
            '/products?id=1;DROP TABLE users--',
            '/index.php?id=1\' OR \'1\'=\'1',
            '/api/v1/users',
            '/dashboard',
            '/login?redirect=/admin',
            '/upload?file=../../../etc/passwd',
            '/api/export',
            '/admin/config',
            '/backup.sql',
            '/server-status',
            '/cgi-bin/test.cgi',
            '/',
            '/home',
            '/about',
            '/contact',
        ];

        $userAgents = [
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36',
            'curl/7.68.0',
            'python-requests/2.28.1',
            'Go-http-client/1.1',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)',
            'PostmanRuntime/7.29.0',
            'sqlmap/1.6',
        ];

        $statuses = [200, 403, 404, 500];
        $severities = ['CRITICAL', 'HIGH', 'MEDIUM', 'LOW'];

        $messages = [
            'SQL injection detected',
            'XSS attack blocked',
            'Path traversal attempt',
            'Remote file inclusion blocked',
            'Command injection attempt',
            'Suspicious user agent',
            'Rate limit exceeded',
            'Invalid request format',
            'Missing security headers',
            'Suspicious parameter value',
        ];

        // إنشاء 200 حدث وهمي موزعة على مدار اليوم (24 ساعة)
        $now = Carbon::now();
        $startOfDay = $now->copy()->startOfDay();
        
        // توزيع الأحداث على مدار 24 ساعة
        // المزيد من الأحداث في ساعات الذروة (9 صباحاً - 5 مساءً)
        $eventsCount = 200;
        
        for ($i = 0; $i < $eventsCount; $i++) {
            // توزيع عشوائي مع تركيز أكبر في ساعات العمل
            $hour = rand(0, 23);
            $minute = rand(0, 59);
            $second = rand(0, 59);
            
            // زيادة احتمالية الأحداث في ساعات 9-17 (ساعات العمل)
            if (rand(1, 100) <= 60 && $hour >= 9 && $hour <= 17) {
                // 60% من الأحداث في ساعات العمل
            } else {
                $hour = rand(0, 23);
            }
            
            $eventTime = $startOfDay->copy()->addHours($hour)->addMinutes($minute)->addSeconds($second);
            
            // إذا كان الوقت في المستقبل، نجعله في الماضي
            if ($eventTime->isFuture()) {
                $eventTime = $now->copy()->subMinutes(rand(1, 60));
            }
            
            $status = $statuses[array_rand($statuses)];
            
            // توزيع أفضل للحالات: 60% allowed, 25% blocked, 10% not found, 5% error
            $statusRand = rand(1, 100);
            if ($statusRand <= 60) {
                $status = 200; // Allowed
            } elseif ($statusRand <= 85) {
                $status = 403; // Blocked
            } elseif ($statusRand <= 95) {
                $status = 404; // Not Found
            } else {
                $status = 500; // Server Error
            }
            
            $ruleId = array_rand($ruleIds);
            $hasRule = ($status === 403 && rand(0, 1)) || ($ruleId !== null && rand(0, 1));
            $selectedRuleId = $hasRule && $ruleId !== null ? $ruleId : null;

            $message = null;
            $severity = null;
            if ($status === 403 && $selectedRuleId) {
                $message = $messages[array_rand($messages)];
                $severity = $severities[array_rand($severities)];
            }

            // زيادة احتمالية rabbitclean.sa
            $host = rand(1, 100) <= 40 ? 'rabbitclean.sa' : $hosts[array_rand($hosts)];

            WafEvent::create([
                'event_time' => $eventTime,
                'client_ip' => $ipAddresses[array_rand($ipAddresses)],
                'host' => $host,
                'uri' => $uris[array_rand($uris)],
                'method' => $methods[array_rand($methods)],
                'status' => $status,
                'rule_id' => $selectedRuleId,
                'severity' => $severity,
                'message' => $message,
                'action' => $status === 403 ? 'blocked' : ($status === 200 ? 'allowed' : 'other'),
                'user_agent' => $userAgents[array_rand($userAgents)],
                'unique_id' => Str::uuid()->toString() . '-' . time() . '-' . $i . '-' . rand(1000, 9999),
                'raw' => [
                    'request' => [
                        'method' => $methods[array_rand($methods)],
                        'uri' => $uris[array_rand($uris)],
                        'headers' => [
                            'Host' => $host,
                            'User-Agent' => $userAgents[array_rand($userAgents)],
                        ],
                    ],
                ],
            ]);
        }

        $this->command->info("تم إنشاء {$eventsCount} حدث وهمي بتاريخ اليوم بنجاح!");
        $this->command->info("الأحداث موزعة على مدار 24 ساعة مع تركيز على rabbitclean.sa");
    }
}
