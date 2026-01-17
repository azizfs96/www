<?php

namespace App\Http\Controllers;

use App\Models\Site;
use Illuminate\Http\Request;

class SiteController extends Controller
{
    /**
     * عرض قائمة المواقع
     */
    public function index()
    {
        $sites = Site::orderByDesc('created_at')->get();

        return view('waf.sites.index', compact('sites'));
    }

    /**
     * عرض صفحة إضافة موقع جديد
     */
    public function create()
    {
        return view('waf.sites.create');
    }

    /**
     * حفظ موقع جديد
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'           => 'required|string|max:255',
            'server_name'    => 'required|string|max:255',
            'backend_ip'     => 'required|ip',
            'backend_port'   => 'required|integer|min:1|max:65535',
            'ssl_cert_path'  => 'nullable|string|max:500',
            'ssl_key_path'   => 'nullable|string|max:500',
            'notes'          => 'nullable|string',
        ]);

        $data['enabled'] = true;
        
        // Checkbox: إذا كان محدد = '1' (true)، إذا لم يكن محدد = '0' (false)
        // في Laravel، إذا كان checkbox محدد، سيتم إرسال '1'، وإذا لم يكن محدد، لن يتم إرسال أي شيء
        // لكن لدينا hidden input بقيمة '0'، لذا سنحصل على '0' أو '1'
        $sslInput = $request->input('ssl_enabled', '0');
        $sslEnabled = ($sslInput === '1' || $sslInput === 1 || $sslInput === true || $sslInput === 'on');
        
        \Log::info("Creating site", [
            'server_name' => $data['server_name'],
            'ssl_enabled_raw' => $sslInput,
            'ssl_enabled_type' => gettype($sslInput),
            'ssl_enabled_parsed' => $sslEnabled,
            'all_inputs' => $request->all()
        ]);
        
        // إذا كان SSL مفعل، نولد الشهادة تلقائياً
        if ($sslEnabled) {
            // مسارات شهادة Let's Encrypt الافتراضية
            $data['ssl_cert_path'] = "/etc/letsencrypt/live/{$data['server_name']}/fullchain.pem";
            $data['ssl_key_path'] = "/etc/letsencrypt/live/{$data['server_name']}/privkey.pem";
        } else {
            // إذا كان SSL معطل، نستخدم المسارات المقدمة (إن وجدت)
            $data['ssl_cert_path'] = $request->input('ssl_cert_path');
            $data['ssl_key_path'] = $request->input('ssl_key_path');
        }
        
        $data['ssl_enabled'] = $sslEnabled;
        
        \Log::info("Data before Site::create", [
            'ssl_enabled' => $data['ssl_enabled'],
            'ssl_enabled_type' => gettype($data['ssl_enabled'])
        ]);

        $site = Site::create($data);
        
        \Log::info("Site created", [
            'site_id' => $site->id,
            'ssl_enabled_in_db' => $site->ssl_enabled,
            'ssl_enabled_type' => gettype($site->ssl_enabled)
        ]);

        // توليد ملف Nginx أولاً (HTTP فقط) حتى يتمكن Certbot من التحقق
        $this->generateNginxConfig($site);

        // إذا كان SSL مفعل، نولد الشهادة تلقائياً
        if ($sslEnabled) {
            \Log::info("Attempting to generate SSL certificate for site: {$site->server_name}");
            
            $certResult = $this->generateSslCertificate($site);
            
            \Log::info("SSL certificate generation result", [
                'success' => $certResult['success'],
                'message' => $certResult['message'],
                'site_id' => $site->id
            ]);
            
            if (!$certResult['success']) {
                // إذا فشل توليد الشهادة، نحتفظ بـ SSL مفعل لكن نستخدم HTTP فقط
                // يمكن للمستخدم إعادة المحاولة لاحقاً
                \Log::warning("SSL certificate generation failed for site: {$site->server_name}", [
                    'error' => $certResult['message']
                ]);
                
                // نعيد توليد ملف Nginx بدون SSL (HTTP فقط)
                $site->ssl_enabled = false;
                $site->ssl_cert_path = null;
                $site->ssl_key_path = null;
                $site->save();
                $this->generateNginxConfig($site);
                
                return redirect()->route('sites.index')
                    ->with('error', 'تم إضافة الموقع بنجاح، لكن فشل توليد شهادة SSL: ' . $certResult['message'] . 
                           '<br><br>يمكنك إعادة المحاولة من لوحة التحكم بعد التأكد من: ' .
                           '<br>1. أن النطاق يشير إلى IP السيرفر' .
                           '<br>2. أن Certbot مثبت' .
                           '<br>3. أن Nginx يعمل بشكل صحيح');
            }
            
            // إذا نجح توليد الشهادة، نعيد توليد ملف Nginx مع SSL
            $this->generateNginxConfig($site);
            
            return redirect()->route('sites.index')
                ->with('status', 'تم إضافة الموقع وتوليد شهادة SSL بنجاح!');
        }

        return redirect()->route('sites.index')
            ->with('status', 'تم إضافة الموقع بنجاح! يرجى إعادة تحميل Nginx.');
    }

    /**
     * حذف موقع
     */
    public function destroy(Site $site)
    {
        // حذف ملف Nginx
        $configFile = "/etc/nginx/sites-enabled/{$site->server_name}.waf.conf";
        if (file_exists($configFile)) {
            @unlink($configFile);
        }

        $site->delete();

        // إعادة تحميل Nginx
        @exec('sudo systemctl reload nginx > /dev/null 2>&1 &');

        return redirect()->route('sites.index')
            ->with('status', 'تم حذف الموقع بنجاح.');
    }

    /**
     * تفعيل/تعطيل SSL لموقع
     */
    public function toggleSsl(Site $site)
    {
        if (!$site->ssl_enabled) {
            // تفعيل SSL
            $site->ssl_enabled = true;
            $site->ssl_cert_path = "/etc/letsencrypt/live/{$site->server_name}/fullchain.pem";
            $site->ssl_key_path = "/etc/letsencrypt/live/{$site->server_name}/privkey.pem";
            $site->save();
            
            // توليد ملف Nginx أولاً (HTTP فقط)
            $this->generateNginxConfig($site);
            
            // توليد الشهادة
            \Log::info("Starting SSL certificate generation for site: {$site->server_name}");
            $certResult = $this->generateSslCertificate($site);
            
            \Log::info("SSL certificate generation completed", [
                'success' => $certResult['success'],
                'message' => $certResult['message']
            ]);
            
            if (!$certResult['success']) {
                // نحتفظ بـ SSL مفعل لكن نعرض رسالة خطأ
                // يمكن للمستخدم إعادة المحاولة لاحقاً
                return redirect()->route('sites.index')
                    ->with('error', '⚠️ فشل توليد شهادة SSL: ' . $certResult['message'] . 
                           '<br><br><strong>التحقق من:</strong>' .
                           '<br>1. أن النطاق ' . $site->server_name . ' يشير إلى IP السيرفر' .
                           '<br>2. أن Certbot مثبت: <code>sudo apt-get install certbot python3-certbot-nginx</code>' .
                           '<br>3. أن Nginx يعمل: <code>sudo systemctl status nginx</code>' .
                           '<br>4. أن الموقع متاح على HTTP (port 80)' .
                           '<br><br>يمكنك إعادة المحاولة بالضغط على زر "تفعيل SSL" مرة أخرى.');
            }
            
            // إعادة توليد ملف Nginx مع SSL
            $this->generateNginxConfig($site);
            
            return redirect()->route('sites.index')
                ->with('status', '✅ تم تفعيل SSL وتوليد الشهادة بنجاح! تم تحديث ملف Nginx.');
        } else {
            // تعطيل SSL
            $site->ssl_enabled = false;
            $site->ssl_cert_path = null;
            $site->ssl_key_path = null;
            $site->save();
            
            $this->generateNginxConfig($site);
            
            return redirect()->route('sites.index')
                ->with('status', 'تم تعطيل SSL بنجاح.');
        }
    }

    /**
     * تفعيل/تعطيل موقع
     */
    public function toggle(Site $site)
    {
        $site->enabled = !$site->enabled;
        $site->save();

        if ($site->enabled) {
            $this->generateNginxConfig($site);
        } else {
            // حذف ملف Nginx عند التعطيل
            $configFile = "/etc/nginx/sites-enabled/{$site->server_name}.waf.conf";
            if (file_exists($configFile)) {
                @unlink($configFile);
            }
        }

        // إعادة تحميل Nginx
        @exec('sudo systemctl reload nginx > /dev/null 2>&1 &');

        return redirect()->route('sites.index')
            ->with('status', $site->enabled ? 'تم تفعيل الموقع.' : 'تم تعطيل الموقع.');
    }

    /**
     * توليد ملف Nginx Configuration
     */
    public function generateNginxConfig(Site $site): void
    {
        $configFile = "/etc/nginx/sites-enabled/{$site->server_name}.waf.conf";

        // إنشاء المحتوى
        $content = $this->buildNginxConfigContent($site);

        // كتابة الملف
        @file_put_contents($configFile, $content);

        // إعادة تحميل Nginx
        @exec('sudo systemctl reload nginx > /dev/null 2>&1 &');
    }

    /**
     * بناء محتوى ملف Nginx بنفس تنسيق Certbot
     */
    protected function buildNginxConfigContent(Site $site): string
    {
        $backendName = str_replace('.', '_', $site->server_name) . '_backend';
        
        $content = "";
        
        // Upstream
        $content .= "upstream {$backendName} {\n";
        $content .= "    server {$site->backend_ip}:{$site->backend_port};\n";
        $content .= "}\n\n";

        // Log للتحقق من حالة SSL
        \Log::info("Building Nginx config for site", [
            'site_id' => $site->id,
            'server_name' => $site->server_name,
            'ssl_enabled' => $site->ssl_enabled,
            'ssl_enabled_type' => gettype($site->ssl_enabled),
            'ssl_cert_path' => $site->ssl_cert_path,
            'ssl_key_path' => $site->ssl_key_path
        ]);

        // HTTPS Server Block (إذا كان SSL مفعل)
        // يتم تفعيل SSL فقط إذا كان ssl_enabled = true وتم توفير مسارات الشهادة والمفتاح
        // Model يحول ssl_enabled إلى boolean تلقائياً
        $isSslEnabled = (bool) $site->ssl_enabled;
        
        \Log::info("SSL check in buildNginxConfigContent", [
            'ssl_enabled' => $site->ssl_enabled,
            'ssl_enabled_type' => gettype($site->ssl_enabled),
            'isSslEnabled' => $isSslEnabled,
            'ssl_cert_path' => $site->ssl_cert_path,
            'ssl_key_path' => $site->ssl_key_path,
            'cert_exists' => !empty($site->ssl_cert_path),
            'key_exists' => !empty($site->ssl_key_path)
        ]);
        
        if ($isSslEnabled && !empty($site->ssl_cert_path) && !empty($site->ssl_key_path)) {
            $content .= "server {\n";
            $content .= "    server_name {$site->server_name} www.{$site->server_name};\n\n";
            $content .= "    location / {\n";
            $content .= "        proxy_pass http://{$backendName};\n\n";
            $content .= "        proxy_set_header Host \$host;\n";
            $content .= "        proxy_set_header X-Real-IP \$remote_addr;\n";
            $content .= "        proxy_set_header X-Forwarded-For \$proxy_add_x_forwarded_for;\n";
            $content .= "        proxy_set_header X-Forwarded-Proto \$scheme;\n\n";
            $content .= "        proxy_read_timeout 60s;\n";
            $content .= "        proxy_send_timeout 60s;\n";
            $content .= "    }\n\n";
            $content .= "    listen 443 ssl; # managed by Certbot\n";
            $content .= "    ssl_certificate {$site->ssl_cert_path}; # managed by Certbot\n";
            $content .= "    ssl_certificate_key {$site->ssl_key_path}; # managed by Certbot\n";
            $content .= "    include /etc/letsencrypt/options-ssl-nginx.conf; # managed by Certbot\n";
            $content .= "    ssl_dhparam /etc/letsencrypt/ssl-dhparams.pem; # managed by Certbot\n\n\n";
            $content .= "}\n\n\n";
            
            // HTTP to HTTPS Redirect
            $content .= "server {\n";
            $content .= "    if (\$host = www.{$site->server_name}) {\n";
            $content .= "        return 301 https://\$host\$request_uri;\n";
            $content .= "    } # managed by Certbot\n\n\n";
            $content .= "    if (\$host = {$site->server_name}) {\n";
            $content .= "        return 301 https://\$host\$request_uri;\n";
            $content .= "    } # managed by Certbot\n\n\n";
            $content .= "    listen 80;\n";
            $content .= "    server_name {$site->server_name} www.{$site->server_name};\n";
            $content .= "    return 404; # managed by Certbot\n\n\n\n";
            $content .= "}\n";
        } else {
            // HTTP Only
            $content .= "server {\n";
            $content .= "    server_name {$site->server_name} www.{$site->server_name};\n\n";
            $content .= "    location / {\n";
            $content .= "        proxy_pass http://{$backendName};\n\n";
            $content .= "        proxy_set_header Host \$host;\n";
            $content .= "        proxy_set_header X-Real-IP \$remote_addr;\n";
            $content .= "        proxy_set_header X-Forwarded-For \$proxy_add_x_forwarded_for;\n";
            $content .= "        proxy_set_header X-Forwarded-Proto \$scheme;\n\n";
            $content .= "        proxy_read_timeout 60s;\n";
            $content .= "        proxy_send_timeout 60s;\n";
            $content .= "    }\n\n";
            $content .= "    listen 80;\n";
            $content .= "}\n";
        }
        
        return $content;
    }

    /**
     * توليد ملف ModSecurity خاص بالموقع
     */
    protected function generateModSecurityConfig(Site $site, $policy): void
    {
        $configFile = "/etc/nginx/modsec/{$site->server_name}.conf";
        
        $content = "# ModSecurity Configuration for {$site->name}\n";
        $content .= "# Generated at: " . now()->format('Y-m-d H:i:s') . "\n\n";

        // تضمين القواعد الأساسية (إن وجدت)
        $content .= "# Include base configuration\n";
        if (file_exists('/etc/nginx/modsec/main.conf')) {
            $content .= "Include /etc/nginx/modsec/main.conf\n\n";
        } elseif (file_exists('/etc/nginx/modsec/modsecurity.conf')) {
            $content .= "Include /etc/nginx/modsec/modsecurity.conf\n\n";
        } else {
            // إعدادات أساسية بديلة
            $content .= "SecRuleEngine On\n";
            $content .= "SecRequestBodyAccess On\n";
            $content .= "SecResponseBodyAccess Off\n";
            $content .= "SecRequestBodyLimit 13107200\n";
            $content .= "SecRequestBodyNoFilesLimit 131072\n";
            $content .= "SecRequestBodyInMemoryLimit 131072\n";
            $content .= "SecAuditEngine RelevantOnly\n";
            $content .= "SecAuditLogRelevantStatus \"^(?:5|4(?!04))\"\n";
            $content .= "SecAuditLogParts ABIJDEFHZ\n";
            $content .= "SecAuditLogType Serial\n";
            $content .= "SecAuditLog /var/log/modsec_audit.log\n";
            $content .= "SecArgumentSeparator &\n";
            $content .= "SecCookieFormat 0\n";
            $content .= "SecTmpDir /tmp/\n";
            $content .= "SecDataDir /tmp/\n\n";
        }

        // إعدادات مستوى الصرامة
        $content .= "# Paranoia Level\n";
        $content .= "SecAction \"id:900000,phase:1,nolog,pass,t:none,setvar:tx.paranoia_level={$policy->paranoia_level}\"\n\n";

        // عتبة الشذوذ
        $content .= "# Anomaly Threshold\n";
        $content .= "SecAction \"id:900110,phase:1,nolog,pass,t:none,setvar:tx.inbound_anomaly_score_threshold={$policy->anomaly_threshold}\"\n\n";

        // القواعد العامة إذا كانت وراثة القواعد مفعلة
        if ($policy->inherit_global_rules) {
            $content .= "# Global Rules\n";
            if (file_exists('/etc/nginx/modsec/global-rules.conf')) {
                $content .= "Include /etc/nginx/modsec/global-rules.conf\n\n";
            } else {
                $content .= "# Global rules file not found\n\n";
            }
        }

        // قواعد OWASP CRS (فقط إذا كانت مثبتة)
        $owaspPath = '/etc/nginx/modsec/owasp-crs/rules';
        if (is_dir($owaspPath)) {
            if ($policy->block_sql_injection && file_exists("$owaspPath/REQUEST-942-APPLICATION-ATTACK-SQLI.conf")) {
                $content .= "# OWASP CRS - SQL Injection\n";
                $content .= "Include $owaspPath/REQUEST-942-APPLICATION-ATTACK-SQLI.conf\n\n";
            }

            if ($policy->block_xss && file_exists("$owaspPath/REQUEST-941-APPLICATION-ATTACK-XSS.conf")) {
                $content .= "# OWASP CRS - XSS\n";
                $content .= "Include $owaspPath/REQUEST-941-APPLICATION-ATTACK-XSS.conf\n\n";
            }

            if ($policy->block_rce && file_exists("$owaspPath/REQUEST-932-APPLICATION-ATTACK-RCE.conf")) {
                $content .= "# OWASP CRS - RCE\n";
                $content .= "Include $owaspPath/REQUEST-932-APPLICATION-ATTACK-RCE.conf\n\n";
            }

            if ($policy->block_lfi && file_exists("$owaspPath/REQUEST-930-APPLICATION-ATTACK-LFI.conf")) {
                $content .= "# OWASP CRS - LFI\n";
                $content .= "Include $owaspPath/REQUEST-930-APPLICATION-ATTACK-LFI.conf\n\n";
            }

            if ($policy->block_rfi && file_exists("$owaspPath/REQUEST-931-APPLICATION-ATTACK-RFI.conf")) {
                $content .= "# OWASP CRS - RFI\n";
                $content .= "Include $owaspPath/REQUEST-931-APPLICATION-ATTACK-RFI.conf\n\n";
            }
        } else {
            $content .= "# OWASP CRS not installed - using basic rules only\n\n";
        }

        // استثناءات URLs
        if ($policy->excluded_urls) {
            $content .= "# Excluded URLs\n";
            foreach ($policy->excluded_urls_array as $url) {
                $url = trim($url);
                if ($url) {
                    $content .= "SecRule REQUEST_URI \"@beginsWith {$url}\" \"id:" . (800000 + crc32($url)) . ",phase:1,nolog,allow\"\n";
                }
            }
            $content .= "\n";
        }

        // استثناءات IPs
        if ($policy->excluded_ips) {
            $content .= "# Excluded IPs\n";
            foreach ($policy->excluded_ips_array as $ip) {
                $ip = trim($ip);
                if ($ip && filter_var($ip, FILTER_VALIDATE_IP)) {
                    $content .= "SecRule REMOTE_ADDR \"@ipMatch {$ip}\" \"id:" . (810000 + crc32($ip)) . ",phase:1,nolog,allow\"\n";
                }
            }
            $content .= "\n";
        }

        // قواعد مخصصة
        if ($policy->custom_modsec_rules) {
            $content .= "# Custom Rules\n";
            $content .= $policy->custom_modsec_rules . "\n\n";
        }

        // قواعد IP الخاصة بالموقع
        $content .= "# Site-specific IP Rules\n";
        
        // إنشاء ملفات IP Rules إذا لم تكن موجودة
        $sitesDir = '/etc/nginx/modsec/sites';
        if (!is_dir($sitesDir)) {
            @mkdir($sitesDir, 0755, true);
        }
        
        $whitelistFile = "$sitesDir/{$site->server_name}-whitelist.txt";
        $blacklistFile = "$sitesDir/{$site->server_name}-blacklist.txt";
        
        if (!file_exists($whitelistFile)) {
            @file_put_contents($whitelistFile, "# Whitelist for {$site->name}\n");
        }
        if (!file_exists($blacklistFile)) {
            @file_put_contents($blacklistFile, "# Blacklist for {$site->name}\n");
        }
        
        if (file_exists($whitelistFile)) {
            $content .= "Include $whitelistFile\n";
        }
        if (file_exists($blacklistFile)) {
            $content .= "Include $blacklistFile\n";
        }

        @file_put_contents($configFile, $content);
    }

    /**
     * إعادة توليد جميع ملفات Nginx
     */
    public function regenerateAll()
    {
        $sites = Site::where('enabled', true)->get();

        foreach ($sites as $site) {
            $this->generateNginxConfig($site);
        }

        return redirect()->route('sites.index')
            ->with('status', 'تم إعادة توليد جميع ملفات المواقع بنجاح.');
    }

    /**
     * توليد شهادة SSL تلقائياً باستخدام Certbot
     * 
     * @param Site $site
     * @return array ['success' => bool, 'message' => string]
     */
    protected function generateSslCertificate(Site $site): array
    {
        $domain = $site->server_name;
        $wwwDomain = "www.{$domain}";
        
        // التحقق من أن Certbot مثبت
        $certbotCheck = shell_exec('which certbot 2>/dev/null');
        if (empty($certbotCheck)) {
            return [
                'success' => false,
                'message' => 'Certbot غير مثبت. يرجى تثبيته: sudo apt-get install certbot python3-certbot-nginx'
            ];
        }

        // التحقق من أن Nginx يعمل
        $nginxCheck = shell_exec('sudo systemctl is-active nginx 2>/dev/null');
        if (trim($nginxCheck) !== 'active') {
            return [
                'success' => false,
                'message' => 'Nginx غير نشط. يرجى تشغيله أولاً: sudo systemctl start nginx'
            ];
        }

        // التحقق من أن الملف موجود و Nginx يمكنه قراءته
        $configFile = "/etc/nginx/sites-enabled/{$domain}.waf.conf";
        if (!file_exists($configFile)) {
            return [
                'success' => false,
                'message' => 'ملف Nginx غير موجود. يرجى التأكد من إنشاء الموقع أولاً.'
            ];
        }

        // اختبار إعدادات Nginx
        $testResult = shell_exec('sudo nginx -t 2>&1');
        if (strpos($testResult, 'successful') === false) {
            return [
                'success' => false,
                'message' => 'إعدادات Nginx غير صحيحة: ' . $testResult
            ];
        }

        // إعادة تحميل Nginx لضمان أن الملف الجديد نشط
        @exec('sudo systemctl reload nginx 2>&1');
        sleep(2); // انتظار قليل لضمان أن Nginx تم تحميله

        // توليد الشهادة باستخدام Certbot
        // نستخدم --certonly --nginx لتوليد الشهادة فقط بدون تعديل ملف Nginx
        $email = config('mail.from.address', 'admin@' . $domain);
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $email = "admin@{$domain}";
        }
        
        \Log::info("Running certbot command", [
            'domain' => $domain,
            'wwwDomain' => $wwwDomain,
            'email' => $email
        ]);
        
        $command = sprintf(
            'sudo certbot certonly --nginx --non-interactive --agree-tos --email %s -d %s -d %s 2>&1',
            escapeshellarg($email),
            escapeshellarg($domain),
            escapeshellarg($wwwDomain)
        );
        
        \Log::info("Certbot command: " . $command);

        $output = [];
        $returnVar = 0;
        exec($command, $output, $returnVar);
        
        $outputString = implode("\n", $output);
        
        \Log::info("Certbot execution result", [
            'return_code' => $returnVar,
            'output' => $outputString
        ]);

        // التحقق من وجود الملفات أولاً (حتى لو فشل الأمر)
        $certPath = "/etc/letsencrypt/live/{$domain}/fullchain.pem";
        $keyPath = "/etc/letsencrypt/live/{$domain}/privkey.pem";
        
        if (file_exists($certPath) && file_exists($keyPath)) {
            // الشهادة موجودة (سواء تم توليدها الآن أو كانت موجودة مسبقاً)
            \Log::info("SSL certificate files found", [
                'cert_path' => $certPath,
                'key_path' => $keyPath
            ]);
            return [
                'success' => true,
                'message' => 'الشهادة موجودة'
            ];
        }

        if ($returnVar !== 0) {
            // فشل توليد الشهادة - جرب طريقة بديلة باستخدام standalone
            \Log::warning("Certbot with --nginx failed, trying standalone method", [
                'return_code' => $returnVar,
                'output' => $outputString
            ]);
            
            // إيقاف Nginx مؤقتاً لاستخدام standalone
            @exec('sudo systemctl stop nginx 2>&1');
            sleep(1);
            
            $standaloneCommand = sprintf(
                'sudo certbot certonly --standalone --non-interactive --agree-tos --email %s -d %s -d %s 2>&1',
                escapeshellarg($email),
                escapeshellarg($domain),
                escapeshellarg($wwwDomain)
            );
            
            \Log::info("Trying certbot standalone: " . $standaloneCommand);
            
            $standaloneOutput = [];
            $standaloneReturnVar = 0;
            exec($standaloneCommand, $standaloneOutput, $standaloneReturnVar);
            $standaloneOutputString = implode("\n", $standaloneOutput);
            
            // إعادة تشغيل Nginx
            @exec('sudo systemctl start nginx 2>&1');
            
            if ($standaloneReturnVar !== 0) {
                \Log::error("SSL certificate generation failed with both methods", [
                    'nginx_method_output' => $outputString,
                    'standalone_method_output' => $standaloneOutputString
                ]);
                
                return [
                    'success' => false,
                    'message' => 'فشل توليد الشهادة باستخدام كلا الطريقتين. ' .
                                 'تفاصيل: ' . $standaloneOutputString
                ];
            }
        }

        // التحقق من وجود الملفات (بعد نجاح الأمر)
        // $certPath و $keyPath معرفة مسبقاً في السطر 584-585
        
        if (!file_exists($certPath) || !file_exists($keyPath)) {
            \Log::error("SSL certificate files not found after generation", [
                'cert_path' => $certPath,
                'key_path' => $keyPath
            ]);
            return [
                'success' => false,
                'message' => 'تم تنفيذ الأمر لكن الملفات غير موجودة في: ' . $certPath
            ];
        }

        \Log::info("SSL certificate generated successfully", [
            'cert_path' => $certPath,
            'key_path' => $keyPath
        ]);

        return [
            'success' => true,
            'message' => 'تم توليد الشهادة بنجاح'
        ];
    }
}
