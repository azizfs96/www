<?php

return [
    'kpis' => [
        'open_alerts' => 24,
        'critical_incidents' => 3,
        'assets_monitored' => 128,
        'attacks_last_24h' => 76,
    ],

    'timeline' => [
        ['time' => '14:05', 'severity' => 'High', 'source_ip' => '1.2.3.4', 'target' => '/login', 'result' => 'Blocked', 'event' => 'Brute force detected'],
        ['time' => '13:42', 'severity' => 'Medium', 'source_ip' => '185.77.22.13', 'target' => '/wp-login.php', 'result' => 'Challenged', 'event' => 'Credential stuffing pattern'],
        ['time' => '13:11', 'severity' => 'Low', 'source_ip' => '91.201.15.2', 'target' => '/api/v1/search', 'result' => 'Allowed', 'event' => 'Automated scanner fingerprinted'],
        ['time' => '12:54', 'severity' => 'High', 'source_ip' => '5.6.7.8', 'target' => '/admin/login', 'result' => 'Blocked', 'event' => 'SQLi probe detected'],
    ],

    'alerts' => [
        ['id' => 'ALT-2401', 'title' => 'Multiple blocked requests from same IP', 'severity' => 'High', 'status' => 'Open', 'source' => 'WAF Engine'],
        ['id' => 'ALT-2402', 'title' => 'Abnormal 404 spike on public assets', 'severity' => 'Medium', 'status' => 'Investigating', 'source' => 'Traffic Analytics'],
        ['id' => 'ALT-2403', 'title' => 'Possible bot scan pattern detected', 'severity' => 'Low', 'status' => 'Open', 'source' => 'Behavior Monitor'],
        ['id' => 'ALT-2404', 'title' => 'Rule 942100 hit rate increased', 'severity' => 'High', 'status' => 'Escalated', 'source' => 'ModSecurity'],
    ],

    'incidents' => [
        ['id' => 'INC-101', 'name' => 'Credential stuffing campaign', 'priority' => 'P1', 'owner' => 'SOC L2', 'status' => 'In Progress'],
        ['id' => 'INC-102', 'name' => 'Mass scanning from ASN range', 'priority' => 'P2', 'owner' => 'Threat Team', 'status' => 'Open'],
        ['id' => 'INC-103', 'name' => 'API abuse against checkout', 'priority' => 'P1', 'owner' => 'AppSec', 'status' => 'Mitigated'],
    ],

    'attack_analysis' => [
        ['vector' => 'SQL Injection', 'count' => 31, 'trend' => '+12%'],
        ['vector' => 'XSS', 'count' => 18, 'trend' => '+4%'],
        ['vector' => 'Path Traversal', 'count' => 9, 'trend' => '-6%'],
        ['vector' => 'RCE Attempts', 'count' => 6, 'trend' => '+2%'],
    ],

    'assets' => [
        ['name' => 'api.wafgate.com', 'type' => 'API Gateway', 'risk' => 'High', 'last_seen' => '2 min ago'],
        ['name' => 'portal.wafgate.com', 'type' => 'Web App', 'risk' => 'Medium', 'last_seen' => '1 min ago'],
        ['name' => 'cdn.wafgate.com', 'type' => 'Edge Service', 'risk' => 'Low', 'last_seen' => '5 min ago'],
        ['name' => 'admin.wafgate.com', 'type' => 'Admin Panel', 'risk' => 'High', 'last_seen' => 'Just now'],
    ],
];

