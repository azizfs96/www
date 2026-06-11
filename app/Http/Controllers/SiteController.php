<?php

namespace App\Http\Controllers;

use App\Models\Site;
use App\Models\IpRule;
use App\Models\BackendServer;
use App\Services\BackendHealthCheckService;
use Illuminate\Http\Request;

class SiteController extends Controller
{
    /**
     * عرض قائمة المواقع
     */
    public function index()
    {
        $user = auth()->user();
        
        // Super admin sees all sites, others see only their tenant's sites
        if ($user->isSuperAdmin()) {
            $sites = Site::orderByDesc('created_at')->get();
        } else {
            $sites = Site::where('tenant_id', $user->tenant_id)
                ->orderByDesc('created_at')
                ->get();
        }

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
        // التحقق من وجود backend_servers (النظام الجديد) أو backend_ip (النظام القديم)
        $hasBackendServers = $request->has('backend_servers') && is_array($request->input('backend_servers')) && count($request->input('backend_servers')) > 0;
        
        if ($hasBackendServers) {
            // التحقق من صحة بيانات السيرفرات الخلفية
            $request->validate([
                'name'           => 'required|string|max:255',
                'server_name'    => 'required|string|max:255',
                'backend_servers' => 'required|array|min:1',
                'backend_servers.*.ip' => 'required|ip',
                'backend_servers.*.port' => 'required|integer|min:1|max:65535',
                'backend_servers.*.status' => 'required|in:active,standby',
                'backend_servers.*.priority' => 'nullable|integer|min:1',
                'ssl_cert_path'  => 'nullable|string|max:500',
                'ssl_key_path'   => 'nullable|string|max:500',
                'notes'          => 'nullable|string',
            ]);

            // التحقق من وجود سيرفر نشط واحد على الأقل
            $activeServers = array_filter($request->input('backend_servers'), function($server) {
                return isset($server['status']) && $server['status'] === 'active';
            });

            if (count($activeServers) === 0) {
                return redirect()->back()
                    ->withErrors(['backend_servers' => 'يجب تحديد سيرفر واحد على الأقل كـ Active (نشط)'])
                    ->withInput();
            }
        } else {
            // النظام القديم - استخدام backend_ip و backend_port
            $request->validate([
                'name'           => 'required|string|max:255',
                'server_name'    => 'required|string|max:255',
                'backend_ip'     => 'required|ip',
                'backend_port'   => 'required|integer|min:1|max:65535',
                'ssl_cert_path'  => 'nullable|string|max:500',
                'ssl_key_path'   => 'nullable|string|max:500',
                'notes'          => 'nullable|string',
            ]);
        }

        $data = $request->only([
            'name', 'server_name', 'ssl_cert_path', 'ssl_key_path', 'notes'
        ]);

        $data['enabled'] = true;
        
        // Assign tenant_id based on user role
        $user = auth()->user();
        if ($user->isSuperAdmin()) {
            // Super admin can create sites without tenant (global sites)
            $data['tenant_id'] = $request->input('tenant_id');
        } else {
            // Regular users can only create sites for their tenant
            $data['tenant_id'] = $user->tenant_id;
        }
        
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
            'has_backend_servers' => $hasBackendServers,
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

        // للحفاظ على التوافق مع النظام القديم، نحفظ backend_ip و backend_port من أول سيرفر نشط
        if ($hasBackendServers) {
            $firstActiveServer = collect($request->input('backend_servers'))
                ->where('status', 'active')
                ->sortBy('priority')
                ->first();
            
            if ($firstActiveServer) {
                $data['backend_ip'] = $firstActiveServer['ip'];
                $data['backend_port'] = $firstActiveServer['port'];
            } else {
                // إذا لم يكن هناك سيرفر نشط (لا يجب أن يحدث)، نستخدم الأول
                $firstServer = $request->input('backend_servers')[0];
                $data['backend_ip'] = $firstServer['ip'];
                $data['backend_port'] = $firstServer['port'];
            }
        } else {
            // النظام القديم
            $data['backend_ip'] = $request->input('backend_ip');
            $data['backend_port'] = $request->input('backend_port');
        }
        
        \Log::info("Data before Site::create", [
            'ssl_enabled' => $data['ssl_enabled'],
            'ssl_enabled_type' => gettype($data['ssl_enabled'])
        ]);

        $site = Site::create($data);

        // حفظ السيرفرات الخلفية
        if ($hasBackendServers) {
            \Log::info("Creating backend servers for site", [
                'site_id' => $site->id,
                'servers_count' => count($request->input('backend_servers')),
                'servers_data' => $request->input('backend_servers'),
            ]);
            
            foreach ($request->input('backend_servers') as $serverData) {
                $backendServer = $site->backendServers()->create([
                    'ip' => $serverData['ip'],
                    'port' => $serverData['port'],
                    'status' => $serverData['status'],
                    'priority' => $serverData['priority'] ?? 1,
                    'health_check_enabled' => true,
                    'is_healthy' => true,
                ]);
                
                \Log::info("Backend server created", [
                    'server_id' => $backendServer->id,
                    'site_id' => $site->id,
                    'ip' => $backendServer->ip,
                    'port' => $backendServer->port,
                    'status' => $backendServer->status,
                ]);
            }
        } else {
            // النظام القديم - إنشاء سيرفر واحد افتراضي
            \Log::info("Creating single backend server (old method)", [
                'site_id' => $site->id,
                'backend_ip' => $data['backend_ip'],
                'backend_port' => $data['backend_port'],
            ]);
            
            $backendServer = $site->backendServers()->create([
                'ip' => $data['backend_ip'],
                'port' => $data['backend_port'],
                'status' => 'active',
                'priority' => 1,
                'health_check_enabled' => true,
                'is_healthy' => true,
            ]);
            
            \Log::info("Default backend server created", [
                'server_id' => $backendServer->id,
                'site_id' => $site->id,
            ]);
        }
        
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
     * التحقق من صلاحيات الوصول للموقع
     */
    protected function checkSiteAccess(Site $site): void
    {
        $user = auth()->user();
        
        // Super admin can access all sites
        if ($user->isSuperAdmin()) {
            return;
        }
        
        // Others can only access their tenant's sites
        if ($site->tenant_id !== $user->tenant_id) {
            abort(403, 'Access denied. You can only manage sites in your tenant.');
        }
    }

    /**
     * حذف موقع
     */
    public function destroy(Site $site)
    {
        $this->checkSiteAccess($site);
        $serverName = $site->server_name;
        
        // حذف ملف Nginx
        $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
        
        if (!$isWindows) {
            $configFile = "/etc/nginx/sites-enabled/{$serverName}.waf.conf";
            
            // التحقق من المستخدم الحالي
            $currentUser = posix_getpwuid(posix_geteuid());
            $isRoot = ($currentUser['name'] === 'root' || posix_geteuid() === 0);
            
            if (file_exists($configFile)) {
                if ($isRoot) {
                    // إذا كان root، احذف مباشرة
                    @unlink($configFile);
                } else {
                    // إذا لم يكن root، استخدم sudo rm
                    shell_exec("sudo rm -f {$configFile} 2>&1");
                }
                
                \Log::info("Deleted Nginx config file", [
                    'config_file' => $configFile,
                    'site_id' => $site->id,
                    'file_exists_after_delete' => file_exists($configFile),
                ]);
            }
        } else {
            $configFile = storage_path("app/nginx/{$serverName}.waf.conf");
            if (file_exists($configFile)) {
                @unlink($configFile);
            }
        }

        // حذف شهادات SSL إذا كانت موجودة
        if ($site->ssl_enabled) {
            $certPath = "/etc/letsencrypt/live/{$serverName}/fullchain.pem";
            if (file_exists($certPath)) {
                // استخدام Certbot لحذف الشهادة (الطريقة الآمنة)
                $certbotDelete = shell_exec("sudo certbot delete --cert-name {$serverName} --non-interactive 2>&1");
                
                \Log::info("Deleting SSL certificate for site: {$serverName}", [
                    'certbot_output' => $certbotDelete
                ]);
                
                // إذا فشل Certbot، نحاول حذف المجلد يدوياً
                $certDir = "/etc/letsencrypt/live/{$serverName}";
                if (is_dir($certDir)) {
                    @exec("sudo rm -rf {$certDir} 2>&1");
                }
            }
        }

        $site->delete();

        // إعادة تحميل Nginx (فقط على Linux)
        $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
        if (!$isWindows) {
            @exec('sudo systemctl reload nginx > /dev/null 2>&1 &');
        }

        return redirect()->route('sites.index')
            ->with('status', 'تم حذف الموقع وملفات SSL بنجاح.');
    }

    /**
     * تفعيل/تعطيل SSL لموقع
     */
    public function toggleSsl(Site $site)
    {
        $this->checkSiteAccess($site);
        if (!$site->ssl_enabled) {
            // تفعيل SSL - نفس منطق fixSsl()
            // نضع مسارات الشهادات أولاً
            $certPath = "/etc/letsencrypt/live/{$site->server_name}/fullchain.pem";
            $keyPath = "/etc/letsencrypt/live/{$site->server_name}/privkey.pem";
            
            // التحقق من وجود الشهادات أولاً
            if (file_exists($certPath) && file_exists($keyPath)) {
                // الشهادات موجودة، فقط تفعيل SSL
                $site->ssl_enabled = true;
                $site->ssl_cert_path = $certPath;
                $site->ssl_key_path = $keyPath;
                $site->save();
                
                // إعادة توليد ملف Nginx مع SSL
                $this->generateNginxConfig($site);
                
                return redirect()->route('sites.index')
                    ->with('status', '✅ تم تفعيل SSL بنجاح! الشهادات موجودة.');
            }
            
            // الشهادات غير موجودة - نعيد إنشاء الملف بـ HTTP فقط أولاً
            // تعطيل SSL مؤقتاً لإعادة إنشاء الملف بـ HTTP فقط
            $site->ssl_enabled = false;
            $site->ssl_cert_path = null;
            $site->ssl_key_path = null;
            $site->save();
            
            // إعادة إنشاء ملف Nginx بـ HTTP فقط
            $this->generateNginxConfig($site);
            
            // انتظار قليل
            sleep(2);
            
            // إعداد مسارات الشهادات وتفعيل SSL
            $site->ssl_enabled = true;
            $site->ssl_cert_path = $certPath;
            $site->ssl_key_path = $keyPath;
            $site->save();
            
            // توليد الشهادة
            \Log::info("Starting SSL certificate generation for site: {$site->server_name}");
            $certResult = $this->generateSslCertificate($site);
            
            \Log::info("SSL certificate generation completed", [
                'success' => $certResult['success'],
                'message' => $certResult['message']
            ]);
            
            if (!$certResult['success']) {
                // إذا فشل، نعيد الملف إلى HTTP فقط
                $site->ssl_enabled = false;
                $site->ssl_cert_path = null;
                $site->ssl_key_path = null;
                $site->save();
                $this->generateNginxConfig($site);
                
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
     * إصلاح SSL: إعادة توليد الشهادة إذا كانت مفقودة
     */
    public function fixSsl(Site $site)
    {
        $this->checkSiteAccess($site);
        if (!$site->ssl_enabled) {
            return redirect()->route('sites.index')
                ->with('error', 'SSL غير مفعل لهذا الموقع.');
        }

        // التحقق من وجود الشهادات
        $certPath = "/etc/letsencrypt/live/{$site->server_name}/fullchain.pem";
        $keyPath = "/etc/letsencrypt/live/{$site->server_name}/privkey.pem";
        
        if (file_exists($certPath) && file_exists($keyPath)) {
            // الشهادات موجودة، فقط إعادة توليد ملف Nginx
            $this->generateNginxConfig($site);
            return redirect()->route('sites.index')
                ->with('status', '✅ الشهادات موجودة. تم تحديث ملف Nginx.');
        }

        // الشهادات غير موجودة - نعيد إنشاء الملف بـ HTTP فقط أولاً
        $originalSslEnabled = $site->ssl_enabled;
        $originalCertPath = $site->ssl_cert_path;
        $originalKeyPath = $site->ssl_key_path;
        
        // تعطيل SSL مؤقتاً لإعادة إنشاء الملف بـ HTTP فقط
        $site->ssl_enabled = false;
        $site->ssl_cert_path = null;
        $site->ssl_key_path = null;
        $site->save();
        
        // إعادة إنشاء ملف Nginx بـ HTTP فقط
        $this->generateNginxConfig($site);
        
        // انتظار قليل
        sleep(2);
        
        // إعادة تفعيل SSL
        $site->ssl_enabled = $originalSslEnabled;
        $site->ssl_cert_path = $originalCertPath;
        $site->ssl_key_path = $originalKeyPath;
        $site->save();
        
        // توليد الشهادة
        \Log::info("Fixing SSL: Generating certificate for site: {$site->server_name}");
        $certResult = $this->generateSslCertificate($site);
        
        if (!$certResult['success']) {
            // إذا فشل، نعيد الملف إلى HTTP فقط
            $site->ssl_enabled = false;
            $site->ssl_cert_path = null;
            $site->ssl_key_path = null;
            $site->save();
            $this->generateNginxConfig($site);
            
            return redirect()->route('sites.index')
                ->with('error', '⚠️ فشل توليد شهادة SSL: ' . $certResult['message']);
        }
        
        // إعادة توليد ملف Nginx مع SSL
        $this->generateNginxConfig($site);
        
        return redirect()->route('sites.index')
            ->with('status', '✅ تم إصلاح SSL وتوليد الشهادة بنجاح!');
    }

    /**
     * تفعيل/تعطيل موقع
     */
    public function toggle(Site $site)
    {
        $this->checkSiteAccess($site);
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

        // إعادة تحميل Nginx (فقط على Linux)
        $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
        if (!$isWindows) {
            @exec('sudo systemctl reload nginx > /dev/null 2>&1 &');
        }

        return redirect()->route('sites.index')
            ->with('status', $site->enabled ? 'تم تفعيل الموقع.' : 'تم تعطيل الموقع.');
    }

    /**
     * توليد ملف Nginx Configuration
     */
    public function generateNginxConfig(Site $site): void
    {
        // التحقق من نظام التشغيل
        $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
        
        // تحديد مسار الملف حسب نظام التشغيل
        if ($isWindows) {
            $configDir = storage_path('app/nginx');
            if (!is_dir($configDir)) {
                @mkdir($configDir, 0755, true);
            }
            $configFile = "{$configDir}/{$site->server_name}.waf.conf";
        } else {
            $configFile = "/etc/nginx/sites-enabled/{$site->server_name}.waf.conf";
        }

        // توليد ملف ModSecurity أولاً (إذا كان هناك policy و WAF مفعل)
        if ($site->policy && $site->policy->waf_enabled) {
            $this->generateModSecurityConfig($site, $site->policy);
        }

        // إنشاء المحتوى
        $content = $this->buildNginxConfigContent($site);

        // حفظ الملف في storage أولاً (يمكن الكتابة فيه دائماً)
        $storageDir = storage_path('app/nginx');
        if (!is_dir($storageDir)) {
            @mkdir($storageDir, 0755, true);
        }
        $storageFile = "{$storageDir}/{$site->server_name}.waf.conf";
        $storageWriteResult = @file_put_contents($storageFile, $content);
        
        \Log::info("Nginx config saved to storage", [
            'storage_file' => $storageFile,
            'site_id' => $site->id,
            'write_result' => $storageWriteResult !== false,
        ]);

        // كتابة الملف في /etc/nginx/sites-enabled
        if (!$isWindows) {
            // التحقق من المستخدم الحالي
            $currentUser = posix_getpwuid(posix_geteuid());
            $isRoot = ($currentUser['name'] === 'root' || posix_geteuid() === 0);
            
            \Log::info("Writing Nginx config file", [
                'config_file' => $configFile,
                'site_id' => $site->id,
                'current_user' => $currentUser['name'] ?? 'unknown',
                'is_root' => $isRoot,
            ]);
            
            // إذا كان المستخدم root، اكتب مباشرة
            if ($isRoot) {
                $writeResult = @file_put_contents($configFile, $content);
                
                if ($writeResult !== false && file_exists($configFile)) {
                    // تعيين الصلاحيات الصحيحة
                    @chmod($configFile, 0644);
                    @chown($configFile, 'root');
                }
            } else {
                // إذا لم يكن root، استخدم الملف من storage وانسخه
                // الملف موجود بالفعل في storageFile
                if ($storageWriteResult !== false && file_exists($storageFile)) {
                    // استخدام sudo cp لنسخ الملف من storage إلى /etc/nginx
                    $cpCommand = "sudo cp {$storageFile} {$configFile} 2>&1";
                    $cpResult = shell_exec($cpCommand);
                    
                    if (file_exists($configFile) && filesize($configFile) > 0) {
                        shell_exec("sudo chmod 644 {$configFile} 2>&1");
                        shell_exec("sudo chown root:root {$configFile} 2>&1");
                        $writeResult = true;
                    } else {
                        $writeResult = false;
                        \Log::warning("Failed to copy config file from storage to /etc/nginx", [
                            'storage_file' => $storageFile,
                            'target_file' => $configFile,
                            'cp_result' => $cpResult,
                            'note' => 'File saved in storage. Run: sudo cp ' . $storageFile . ' ' . $configFile,
                        ]);
                    }
                    
                    \Log::info("File copy result from storage", [
                        'config_file' => $configFile,
                        'storage_file' => $storageFile,
                        'file_exists' => file_exists($configFile),
                        'file_size' => file_exists($configFile) ? filesize($configFile) : 0,
                        'write_result' => $writeResult,
                        'cp_result' => $cpResult,
                    ]);
                } else {
                    $writeResult = false;
                    \Log::error("Failed to write config file to storage", [
                        'storage_file' => $storageFile,
                        'site_id' => $site->id,
                    ]);
                }
            }
        } else {
            // على Windows، كتابة مباشرة
            $writeResult = @file_put_contents($configFile, $content);
        }
        
        // التحقق من أن الملف تم كتابته بنجاح
        $fileExists = file_exists($configFile);
        $fileContent = $fileExists ? @file_get_contents($configFile) : null;
        $fileSize = $fileExists ? filesize($configFile) : 0;
        
        // إذا فشلت الكتابة في /etc/nginx، الملف موجود في storage
        if (!$fileExists && !$isWindows && $storageWriteResult !== false) {
            \Log::warning("File was not created in /etc/nginx, but saved to storage", [
                'config_file' => $configFile,
                'storage_file' => $storageFile,
                'site_id' => $site->id,
                'manual_copy_command' => "sudo cp {$storageFile} {$configFile} && sudo systemctl reload nginx",
            ]);
        }
        
        // استخراج محتوى upstream من الملف للتحقق
        $upstreamInFile = null;
        if ($fileContent) {
            $upstreamStart = strpos($fileContent, 'upstream');
            if ($upstreamStart !== false) {
                $upstreamEnd = strpos($fileContent, "\n\n", $upstreamStart);
                $upstreamInFile = $upstreamEnd !== false 
                    ? substr($fileContent, $upstreamStart, $upstreamEnd - $upstreamStart + 2)
                    : substr($fileContent, $upstreamStart, 500);
            }
        }
        
        \Log::info("Nginx config file written", [
            'site_id' => $site->id,
            'site_name' => $site->server_name,
            'config_file' => $configFile,
            'write_result' => $writeResult !== false,
            'bytes_written' => $writeResult !== false ? $writeResult : 0,
            'file_exists' => $fileExists,
            'file_size' => $fileSize,
            'upstream_in_file' => $upstreamInFile,
            'is_windows' => $isWindows,
        ]);

        // إعادة تحميل Nginx (فقط على Linux)
        if (!$isWindows) {
            $reloadResult = @exec('sudo systemctl reload nginx 2>&1');
            \Log::info("Nginx reload executed", [
                'site_id' => $site->id,
                'reload_result' => $reloadResult,
            ]);
        } else {
            \Log::info("Nginx config generated on Windows", [
                'config_file' => $configFile,
                'site_id' => $site->id
            ]);
        }
    }

    /**
     * بناء محتوى ملف Nginx بنفس تنسيق Certbot
     */
    protected function buildNginxConfigContent(Site $site): string
    {
        $backendName = str_replace('.', '_', $site->server_name) . '_backend';
        
        $content = "";
        
        // Upstream مع دعم High Availability
        $content .= "upstream {$backendName} {\n";
        
        // إعادة تحميل الموقع من قاعدة البيانات لضمان الحصول على أحدث البيانات
        $site->refresh();
        
        // الحصول على جميع السيرفرات الخلفية مرتبة حسب الأولوية (من قاعدة البيانات مباشرة)
        $backendServers = BackendServer::where('site_id', $site->id)
            ->orderBy('priority')
            ->get();
        
        \Log::info("Building Nginx config - All backend servers from DB", [
            'site_id' => $site->id,
            'site_name' => $site->server_name,
            'total_servers' => $backendServers->count(),
            'all_servers' => $backendServers->map(fn($s) => [
                'id' => $s->id,
                'ip' => $s->ip,
                'port' => $s->port,
                'status' => $s->status,
                'priority' => $s->priority,
                'is_healthy' => $s->is_healthy,
            ])->toArray(),
        ]);
        
        if ($backendServers->isEmpty()) {
            // إذا لم تكن هناك سيرفرات خلفية، نستخدم القيم القديمة
            \Log::warning("No backend servers found in database, using old backend_ip/backend_port", [
                'site_id' => $site->id,
                'site_name' => $site->server_name,
                'backend_ip' => $site->backend_ip,
                'backend_port' => $site->backend_port,
                'query_result' => BackendServer::where('site_id', $site->id)->count(),
            ]);
            $content .= "    server {$site->backend_ip}:{$site->backend_port};\n";
        } else {
            // إضافة السيرفرات النشطة أولاً
            $activeServers = $backendServers->where('status', 'active')->sortBy('priority');
            
            \Log::info("Building Nginx upstream - Active servers", [
                'site_id' => $site->id,
                'site_name' => $site->server_name,
                'active_count' => $activeServers->count(),
                'active_servers' => $activeServers->map(fn($s) => [
                    'id' => $s->id,
                    'ip' => $s->ip,
                    'port' => $s->port,
                    'status' => $s->status,
                    'priority' => $s->priority,
                ])->toArray(),
            ]);
            
            foreach ($activeServers as $server) {
                $healthCheckParams = '';
                if ($server->health_check_enabled) {
                    // إعدادات health check: max_fails = عدد مرات الفشل قبل اعتبار السيرفر غير صحي
                    // fail_timeout = الوقت بالثواني قبل إعادة المحاولة
                    $healthCheckParams = ' max_fails=3 fail_timeout=30s';
                }
                $content .= "    server {$server->ip}:{$server->port}{$healthCheckParams};\n";
            }
            
            // إضافة السيرفرات الاحتياطية (standby) كـ backup
            $standbyServers = $backendServers->where('status', 'standby')->sortBy('priority');
            
            \Log::info("Building Nginx upstream - Standby servers", [
                'site_id' => $site->id,
                'site_name' => $site->server_name,
                'standby_count' => $standbyServers->count(),
                'standby_servers' => $standbyServers->map(fn($s) => [
                    'id' => $s->id,
                    'ip' => $s->ip,
                    'port' => $s->port,
                    'status' => $s->status,
                    'priority' => $s->priority,
                ])->toArray(),
            ]);
            
            foreach ($standbyServers as $server) {
                $healthCheckParams = '';
                if ($server->health_check_enabled) {
                    $healthCheckParams = ' max_fails=3 fail_timeout=30s';
                }
                // السيرفرات الاحتياطية تُستخدم فقط عند فشل جميع السيرفرات النشطة
                $content .= "    server {$server->ip}:{$server->port}{$healthCheckParams} backup;\n";
            }
        }
        
        // حفظ محتوى upstream فقط للوج
        $upstreamEndPos = strpos($content, "\n\n", strpos($content, "upstream"));
        $upstreamContent = $upstreamEndPos !== false 
            ? substr($content, 0, $upstreamEndPos + 2) 
            : substr($content, 0, 500);
        
        \Log::info("Nginx upstream configuration generated", [
            'site_id' => $site->id,
            'site_name' => $site->server_name,
            'upstream_name' => $backendName,
            'upstream_content' => $upstreamContent,
            'full_content_length' => strlen($content),
        ]);
        
        // إعدادات load balancing (اختياري - يمكن تخصيصها لاحقاً)
        // least_conn = توزيع الاتصالات على السيرفر الأقل اتصالات
        // ip_hash = توزيع حسب IP العميل (لضمان sticky sessions)
        $content .= "    least_conn;\n";
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
            
            // إضافة ModSecurity (فقط إذا كان WAF مفعل)
            $modsecFile = "/etc/nginx/modsec/{$site->server_name}.conf";
            if ($site->policy && $site->policy->waf_enabled && file_exists($modsecFile)) {
                $content .= "    modsecurity on;\n";
                $content .= "    modsecurity_rules_file {$modsecFile};\n\n";
            }

            // صفحة حظر ModSecurity المخصصة (الكود 462)
            $this->add403ErrorPage($content, $site);

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
            
            // إضافة ModSecurity (فقط إذا كان WAF مفعل)
            $modsecFile = "/etc/nginx/modsec/{$site->server_name}.conf";
            if ($site->policy && $site->policy->waf_enabled && file_exists($modsecFile)) {
                $content .= "    modsecurity on;\n";
                $content .= "    modsecurity_rules_file {$modsecFile};\n\n";
            }

            // صفحة حظر ModSecurity المخصصة (الكود 462)
            $this->add403ErrorPage($content, $site);

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
     * إضافة error_page 403 في Nginx config
     */
    protected function add403ErrorPage(string &$content, Site $site): void
    {
        if (!$site->policy) {
            return;
        }

        // إنشاء مجلد صفحات الحظر إذا لم يكن موجوداً
        $pagesDir = '/etc/nginx/waf-403-pages';
        if (!is_dir($pagesDir)) {
            @mkdir($pagesDir, 0755, true);
        }

        // تحديد مسار صفحة الحظر
        if ($site->policy->custom_403_page_path && file_exists($site->policy->custom_403_page_path)) {
            // صفحة مخصصة من المستخدم
            $pagePath = $site->policy->custom_403_page_path;
        } else {
            // توليد صفحة افتراضية مخصصة لكل موقع
            $pagePath = "{$pagesDir}/{$site->server_name}.html";
            $this->generateDefault403Page($site, $pagePath);
        }

        $dir  = dirname($pagePath);
        $file = basename($pagePath);

        // تُعرض هذه الصفحة فقط عندما يكون مصدر الحظر ModSecurity (الكود الداخلي 462)،
        // بينما يبقى الزائر يرى حالة 403 الطبيعية. أخطاء 403 الأخرى من Nginx لا تتأثر.
        $content .= "    error_page 462 =403 @waf_blocked;\n";
        $content .= "    location @waf_blocked {\n";
        $content .= "        internal;\n";
        $content .= "        root {$dir};\n";
        $content .= "        try_files /{$file} =403;\n";
        $content .= "    }\n\n";
    }

    /**
     * توليد صفحة 403 افتراضية جميلة
     */
    protected function generateDefault403Page(Site $site, string $filePath): void
    {
        $message = $site->policy->custom_403_message 
            ?: "Access Denied - Your request has been blocked by WAF (Web Application Firewall)";

        $html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 Forbidden - Access Denied</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 600px;
            width: 100%;
            padding: 60px 40px;
            text-align: center;
        }
        .icon {
            font-size: 80px;
            margin-bottom: 30px;
        }
        h1 {
            color: #333;
            font-size: 36px;
            margin-bottom: 20px;
            font-weight: 700;
        }
        .message {
            color: #666;
            font-size: 18px;
            line-height: 1.6;
            margin-bottom: 40px;
        }
        .details {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-top: 30px;
            font-size: 14px;
            color: #888;
        }
        .site-name {
            color: #667eea;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">🚫</div>
        <h1>403 Forbidden</h1>
        <p class="message">{$message}</p>
        <div class="details">
            <p>Site: <span class="site-name">{$site->server_name}</span></p>
            <p style="margin-top: 10px;">If you believe this is an error, please contact the site administrator.</p>
        </div>
    </div>
</body>
</html>
HTML;

        @file_put_contents($filePath, $html);
        @chmod($filePath, 0644);
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
        // ملاحظة: main.conf محمّل بالفعل من nginx.conf الرئيسي، لذلك لا نضيفه هنا
        $content .= "# Base configuration is loaded from nginx.conf (main.conf)\n";
        $mainConfExists = file_exists('/etc/nginx/modsec/main.conf');
        $mainConfHasOwasp = false;
        
        // التحقق من محتوى main.conf لمعرفة إذا كان يحتوي على OWASP CRS
        if ($mainConfExists) {
            $mainConfContent = @file_get_contents('/etc/nginx/modsec/main.conf');
            $mainConfHasOwasp = $mainConfContent && (
                strpos($mainConfContent, 'owasp-crs') !== false ||
                strpos($mainConfContent, 'REQUEST-942') !== false ||
                strpos($mainConfContent, 'REQUEST-941') !== false
            );
            // لا نضيف Include main.conf هنا لأنه محمّل بالفعل من nginx.conf
        } elseif (file_exists('/etc/nginx/modsec/modsecurity.conf')) {
            // إذا لم يكن main.conf موجوداً، نستخدم modsecurity.conf كبديل
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

        // إعدادات ModSecurity الأساسية - إجبار 403 على deny
        $content .= "# Force 403 status code on deny\n";
        $content .= "SecDefaultAction \"phase:1,deny,status:403\"\n";
        $content .= "SecDefaultAction \"phase:2,deny,status:403\"\n\n";
        
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

        // قواعد OWASP CRS (فقط إذا كانت مثبتة ولم تكن موجودة في main.conf)
        // لتجنب التكرار
        if (!$mainConfHasOwasp) {
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

                // Path Traversal (part of REQUEST-930)
                if ($policy->block_path_traversal && file_exists("$owaspPath/REQUEST-930-APPLICATION-ATTACK-LFI.conf")) {
                    // Path Traversal is included in REQUEST-930, handled by block_lfi
                }

                // PHP Injection
                if ($policy->block_php_injection && file_exists("$owaspPath/REQUEST-933-APPLICATION-ATTACK-PHP.conf")) {
                    $content .= "# OWASP CRS - PHP Injection\n";
                    $content .= "Include $owaspPath/REQUEST-933-APPLICATION-ATTACK-PHP.conf\n\n";
                }

                // Node.js Injection
                if ($policy->block_nodejs_injection && file_exists("$owaspPath/REQUEST-934-APPLICATION-ATTACK-NODEJS.conf")) {
                    $content .= "# OWASP CRS - Node.js Injection\n";
                    $content .= "Include $owaspPath/REQUEST-934-APPLICATION-ATTACK-NODEJS.conf\n\n";
                }

                // Java Injection
                if ($policy->block_java_injection && file_exists("$owaspPath/REQUEST-944-APPLICATION-ATTACK-JAVA.conf")) {
                    $content .= "# OWASP CRS - Java Injection\n";
                    $content .= "Include $owaspPath/REQUEST-944-APPLICATION-ATTACK-JAVA.conf\n\n";
                }

                // Session Fixation
                if ($policy->block_session_fixation && file_exists("$owaspPath/REQUEST-943-APPLICATION-ATTACK-SESSION-FIXATION.conf")) {
                    $content .= "# OWASP CRS - Session Fixation\n";
                    $content .= "Include $owaspPath/REQUEST-943-APPLICATION-ATTACK-SESSION-FIXATION.conf\n\n";
                }

                // File Upload Attacks
                if ($policy->block_file_upload_attacks && file_exists("$owaspPath/REQUEST-914-FILE-UPLOAD-ATTACKS.conf")) {
                    $content .= "# OWASP CRS - File Upload Attacks\n";
                    $content .= "Include $owaspPath/REQUEST-914-FILE-UPLOAD-ATTACKS.conf\n\n";
                }

                // Scanner Detection
                if ($policy->block_scanner_detection && file_exists("$owaspPath/REQUEST-913-SCANNER-DETECTION.conf")) {
                    $content .= "# OWASP CRS - Scanner Detection\n";
                    $content .= "Include $owaspPath/REQUEST-913-SCANNER-DETECTION.conf\n\n";
                }

                // Protocol Attacks
                if ($policy->block_protocol_attacks) {
                    if (file_exists("$owaspPath/REQUEST-920-PROTOCOL-ENFORCEMENT.conf")) {
                        $content .= "# OWASP CRS - Protocol Enforcement\n";
                        $content .= "Include $owaspPath/REQUEST-920-PROTOCOL-ENFORCEMENT.conf\n\n";
                    }
                    if (file_exists("$owaspPath/REQUEST-921-PROTOCOL-ATTACK.conf")) {
                        $content .= "# OWASP CRS - Protocol Attack\n";
                        $content .= "Include $owaspPath/REQUEST-921-PROTOCOL-ATTACK.conf\n\n";
                    }
                }

                // DoS Protection
                if ($policy->block_dos_protection && file_exists("$owaspPath/REQUEST-912-DOS-PROTECTION.conf")) {
                    $content .= "# OWASP CRS - DoS Protection\n";
                    $content .= "Include $owaspPath/REQUEST-912-DOS-PROTECTION.conf\n\n";
                }

                // Data Leakages
                if ($policy->block_data_leakages) {
                    if (file_exists("$owaspPath/REQUEST-950-DATA-LEAKAGES.conf")) {
                        $content .= "# OWASP CRS - Data Leakages\n";
                        $content .= "Include $owaspPath/REQUEST-950-DATA-LEAKAGES.conf\n\n";
                    }
                }
            } else {
                $content .= "# OWASP CRS not installed - using basic rules only\n\n";
            }
        } else {
            // إذا كانت قواعد OWASP في main.conf، نضيف قواعد تعطيل حسب السياسة
            $content .= "# OWASP CRS rules are included in main.conf\n";
            $content .= "# Disabling specific attack types if not enabled in policy\n\n";
            
            // تعطيل SQL Injection إذا كان معطلاً
            if (!$policy->block_sql_injection) {
                $content .= "# Disable SQL Injection rules (REQUEST-942)\n";
                $content .= "SecRuleRemoveByTag \"attack-sqli\"\n\n";
            }
            
            // تعطيل XSS إذا كان معطلاً
            if (!$policy->block_xss) {
                $content .= "# Disable XSS rules (REQUEST-941)\n";
                $content .= "SecRuleRemoveByTag \"attack-xss\"\n\n";
            }
            
            // تعطيل RCE إذا كان معطلاً
            if (!$policy->block_rce) {
                $content .= "# Disable RCE rules (REQUEST-932)\n";
                $content .= "SecRuleRemoveByTag \"attack-rce\"\n\n";
            }
            
            // تعطيل LFI إذا كان معطلاً
            if (!$policy->block_lfi) {
                $content .= "# Disable LFI rules (REQUEST-930)\n";
                $content .= "SecRuleRemoveByTag \"attack-lfi\"\n\n";
            }
            
            // تعطيل RFI إذا كان معطلاً
            if (!$policy->block_rfi) {
                $content .= "# Disable RFI rules (REQUEST-931)\n";
                $content .= "SecRuleRemoveByTag \"attack-rfi\"\n\n";
            }

            // تعطيل Path Traversal إذا كان معطلاً
            if (!$policy->block_path_traversal) {
                $content .= "# Disable Path Traversal rules (REQUEST-930)\n";
                $content .= "SecRuleRemoveByTag \"attack-path\"\n\n";
            }

            // تعطيل PHP Injection إذا كان معطلاً
            if (!$policy->block_php_injection) {
                $content .= "# Disable PHP Injection rules (REQUEST-933)\n";
                $content .= "SecRuleRemoveByTag \"attack-php\"\n\n";
            }

            // تعطيل Node.js Injection إذا كان معطلاً
            if (!$policy->block_nodejs_injection) {
                $content .= "# Disable Node.js Injection rules (REQUEST-934)\n";
                $content .= "SecRuleRemoveByTag \"attack-nodejs\"\n\n";
            }

            // تعطيل Java Injection إذا كان معطلاً
            if (!$policy->block_java_injection) {
                $content .= "# Disable Java Injection rules (REQUEST-944)\n";
                $content .= "SecRuleRemoveByTag \"attack-java\"\n\n";
            }

            // تعطيل Session Fixation إذا كان معطلاً
            if (!$policy->block_session_fixation) {
                $content .= "# Disable Session Fixation rules (REQUEST-943)\n";
                $content .= "SecRuleRemoveByTag \"attack-session\"\n\n";
            }

            // تعطيل File Upload Attacks إذا كان معطلاً
            if (!$policy->block_file_upload_attacks) {
                $content .= "# Disable File Upload Attacks rules (REQUEST-914)\n";
                $content .= "SecRuleRemoveByTag \"attack-fileupload\"\n\n";
            }

            // تعطيل Scanner Detection إذا كان معطلاً
            if (!$policy->block_scanner_detection) {
                $content .= "# Disable Scanner Detection rules (REQUEST-913)\n";
                $content .= "SecRuleRemoveByTag \"scanner\"\n\n";
            }

            // تعطيل Protocol Attacks إذا كان معطلاً
            if (!$policy->block_protocol_attacks) {
                $content .= "# Disable Protocol Attacks rules (REQUEST-920, 921)\n";
                $content .= "SecRuleRemoveByTag \"protocol-violation\"\n\n";
            }

            // تعطيل DoS Protection إذا كان معطلاً
            if (!$policy->block_dos_protection) {
                $content .= "# Disable DoS Protection rules (REQUEST-912)\n";
                $content .= "SecRuleRemoveByTag \"dos-protection\"\n\n";
            }

            // تعطيل Data Leakages إذا كان معطلاً
            if (!$policy->block_data_leakages) {
                $content .= "# Disable Data Leakages rules (REQUEST-950)\n";
                $content .= "SecRuleRemoveByTag \"data-leakage\"\n\n";
            }
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

        // قواعد IP - العامة والخاصة بالموقع
        $content .= "# IP Rules (Global + Site-specific)\n";
        
        // القواعد العامة (Global)
        $globalWhitelist = IpRule::global()->where('type', 'allow')->pluck('ip')->filter();
        $globalBlacklist = IpRule::global()->where('type', 'block')->pluck('ip')->filter();
        
        // القواعد الخاصة بالموقع
        $siteWhitelist = IpRule::forSite($site->id)->where('type', 'allow')->pluck('ip')->filter();
        $siteBlacklist = IpRule::forSite($site->id)->where('type', 'block')->pluck('ip')->filter();
        
        // دمج القواعد العامة والخاصة
        $allWhitelist = $globalWhitelist->merge($siteWhitelist)->unique()->filter();
        $allBlacklist = $globalBlacklist->merge($siteBlacklist)->unique()->filter();
        
        // قواعد Whitelist (Allow)
        if ($allWhitelist->isNotEmpty()) {
            $content .= "# Whitelist IPs (Allow)\n";
            foreach ($allWhitelist as $ip) {
                $ip = trim($ip);
                if ($ip && filter_var($ip, FILTER_VALIDATE_IP)) {
                    $ruleId = 700000 + crc32($ip . $site->id);
                    $content .= "SecRule REMOTE_ADDR \"@ipMatch {$ip}\" \"id:{$ruleId},phase:1,nolog,allow\"\n";
                }
            }
            $content .= "\n";
        }
        
        // قواعد Blacklist (Block)
        if ($allBlacklist->isNotEmpty()) {
            $content .= "# Blacklist IPs (Block)\n";
            foreach ($allBlacklist as $ip) {
                $ip = trim($ip);
                if ($ip && filter_var($ip, FILTER_VALIDATE_IP)) {
                    $ruleId = 710000 + crc32($ip . $site->id);
                    $content .= "SecRule REMOTE_ADDR \"@ipMatch {$ip}\" \"id:{$ruleId},phase:1,deny,status:403,msg:'IP Blocked: {$ip}'\"\n";
                }
            }
            $content .= "\n";
        }
        
        // أيضاً إنشاء ملفات .txt للتوافق (لكن لا نستخدم Include لها)
        $sitesDir = '/etc/nginx/modsec/sites';
        if (!is_dir($sitesDir)) {
            @mkdir($sitesDir, 0755, true);
        }
        
        $whitelistFile = "$sitesDir/{$site->server_name}-whitelist.txt";
        $blacklistFile = "$sitesDir/{$site->server_name}-blacklist.txt";
        
        // كتابة ملفات .txt (للرجوع إليها)
        @file_put_contents($whitelistFile, $siteWhitelist->implode(PHP_EOL) . PHP_EOL);
        @file_put_contents($blacklistFile, $siteBlacklist->implode(PHP_EOL) . PHP_EOL);

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
        
        // التحقق من نظام التشغيل
        $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
        
        // على Windows (بيئة التطوير)، نسمح بإنشاء الموقع بدون توليد شهادة SSL حقيقية
        if ($isWindows) {
            \Log::info("SSL certificate generation skipped on Windows (development environment)", [
                'domain' => $domain,
                'site_id' => $site->id
            ]);
            
            // نعيد توليد ملف Nginx مع SSL (لكن بدون شهادة حقيقية)
            $this->generateNginxConfig($site);
            
            return [
                'success' => true,
                'message' => 'تم إنشاء الموقع بنجاح. ملاحظة: توليد شهادة SSL يتطلب Linux server. على Windows، يمكنك استخدام شهادة SSL محلية للاختبار.'
            ];
        }
        
        // التحقق من أن Certbot مثبت (فقط على Linux)
        $certbotCheck = shell_exec('which certbot 2>/dev/null');
        if (empty($certbotCheck)) {
            return [
                'success' => false,
                'message' => 'Certbot غير مثبت. يرجى تثبيته: sudo apt-get install certbot python3-certbot-nginx'
            ];
        }

        // التحقق من أن Nginx يعمل (فقط على Linux)
        if (!$isWindows) {
            $nginxCheck = shell_exec('sudo systemctl is-active nginx 2>/dev/null');
            if (trim($nginxCheck) !== 'active') {
                // محاولة تشغيل Nginx
                @exec('sudo systemctl start nginx 2>&1');
                sleep(2);
                
                // التحقق مرة أخرى
                $nginxCheck = shell_exec('sudo systemctl is-active nginx 2>/dev/null');
                if (trim($nginxCheck) !== 'active') {
                    return [
                        'success' => false,
                        'message' => 'Nginx غير نشط. يرجى تشغيله يدوياً: sudo systemctl start nginx'
                    ];
                }
            }
        }
        
        // التحقق من وجود سجل DNS لـ www (اختياري)
        $checkWww = @dns_get_record($wwwDomain, DNS_A);
        $useWww = !empty($checkWww);
        
        \Log::info("DNS check for www domain", [
            'www_domain' => $wwwDomain,
            'dns_exists' => $useWww,
            'is_windows' => $isWindows
        ]);

        // التحقق من أن الملف موجود و Nginx يمكنه قراءته
        $configFile = $isWindows 
            ? storage_path("app/nginx/{$domain}.waf.conf") 
            : "/etc/nginx/sites-enabled/{$domain}.waf.conf";
            
        if (!file_exists($configFile)) {
            // على Windows، نكتفي بإنشاء الملف في storage
            if ($isWindows) {
                $configDir = storage_path('app/nginx');
                if (!is_dir($configDir)) {
                    @mkdir($configDir, 0755, true);
                }
                // نعيد توليد الملف في المكان الصحيح
                $this->generateNginxConfig($site);
            } else {
                return [
                    'success' => false,
                    'message' => 'ملف Nginx غير موجود. يرجى التأكد من إنشاء الموقع أولاً.'
                ];
            }
        }

        // اختبار إعدادات Nginx (فقط على Linux)
        if (!$isWindows) {
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
        } else {
            // على Windows، نكتفي بإنشاء الملف
            \Log::info("Skipping Nginx reload on Windows - config file created at: {$configFile}");
        }

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
        
        // بناء أمر Certbot - نضيف www فقط إذا كان موجوداً في DNS
        if ($useWww) {
            $command = sprintf(
                'sudo certbot certonly --nginx --non-interactive --agree-tos --email %s -d %s -d %s 2>&1',
                escapeshellarg($email),
                escapeshellarg($domain),
                escapeshellarg($wwwDomain)
            );
        } else {
            // توليد الشهادة للنطاق الرئيسي فقط (بدون www)
            $command = sprintf(
                'sudo certbot certonly --nginx --non-interactive --agree-tos --email %s -d %s 2>&1',
                escapeshellarg($email),
                escapeshellarg($domain)
            );
            
            \Log::info("www domain not found in DNS, generating certificate for main domain only", [
                'domain' => $domain
            ]);
        }
        
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
            
            // إيقاف Nginx مؤقتاً لاستخدام standalone (فقط على Linux)
            if (!$isWindows) {
                @exec('sudo systemctl stop nginx 2>&1');
                sleep(1);
            }
            
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
            
            // إعادة تشغيل Nginx (فقط على Linux)
            if (!$isWindows) {
                @exec('sudo systemctl start nginx 2>&1');
            }
            
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

    /**
     * عرض حالة السيرفرات الخلفية للموقع
     */
    public function showBackends(Site $site)
    {
        $this->checkSiteAccess($site);
        
        $site->load('backendServers');
        $backendServers = $site->backendServers()->orderBy('priority')->get();
        
        return view('waf.sites.backends', compact('site', 'backendServers'));
    }

    /**
     * فحص صحة جميع السيرفرات الخلفية للموقع
     */
    public function checkBackendHealth(Site $site, BackendHealthCheckService $healthCheckService)
    {
        $this->checkSiteAccess($site);
        
        $servers = $site->backendServers()->where('health_check_enabled', true)->get();
        $checked = 0;
        $healthy = 0;
        $unhealthy = 0;
        
        foreach ($servers as $server) {
            $isHealthy = $healthCheckService->checkServer($server);
            $checked++;
            if ($isHealthy) {
                $healthy++;
            } else {
                $unhealthy++;
            }
        }
        
        return redirect()->route('sites.backends', $site)
            ->with('status', "تم فحص {$checked} سيرفر: {$healthy} صحي، {$unhealthy} غير صحي");
    }

    /**
     * فحص سيرفر واحد
     */
    public function checkSingleBackend(Site $site, BackendServer $backendServer, BackendHealthCheckService $healthCheckService)
    {
        $this->checkSiteAccess($site);
        
        // التحقق من أن السيرفر يتبع للموقع
        if ($backendServer->site_id !== $site->id) {
            abort(403, 'This backend server does not belong to this site.');
        }
        
        $isHealthy = $healthCheckService->checkServer($backendServer);
        
        $status = $isHealthy ? 'صحي' : 'غير صحي';
        
        return redirect()->route('sites.backends', $site)
            ->with('status', "تم فحص السيرفر {$backendServer->ip}:{$backendServer->port} - الحالة: {$status}");
    }

    /**
     * تبديل حالة السيرفر (Active/Standby)
     */
    public function toggleBackendStatus(Site $site, BackendServer $backendServer)
    {
        $this->checkSiteAccess($site);
        
        // التحقق من أن السيرفر يتبع للموقع
        if ($backendServer->site_id !== $site->id) {
            abort(403, 'This backend server does not belong to this site.');
        }
        
        // إذا كان السيرفر نشط، نحوله إلى standby
        if ($backendServer->status === 'active') {
            // التحقق من وجود سيرفرات نشطة أخرى
            $otherActiveServers = $site->backendServers()
                ->where('id', '!=', $backendServer->id)
                ->where('status', 'active')
                ->count();
            
            // إذا كان هذا آخر سيرفر نشط، نحاول تفعيل سيرفر standby بدلاً منه
            if ($otherActiveServers === 0) {
                // البحث عن أول سيرفر standby صحي (مرتب حسب الأولوية)
                $standbyServer = $site->backendServers()
                    ->where('id', '!=', $backendServer->id)
                    ->where('status', 'standby')
                    ->where('is_healthy', true)
                    ->orderBy('priority')
                    ->first();
                
                if ($standbyServer) {
                    // تحويل السيرفر الحالي إلى standby
                    $backendServer->status = 'standby';
                    $backendServer->save();
                    
                    // تفعيل السيرفر Standby تلقائياً
                    $standbyServer->status = 'active';
                    $standbyServer->fail_count = 0;
                    $standbyServer->save();
                    
                    \Log::info("Auto-activated standby server when deactivating last active", [
                        'deactivated_server_id' => $backendServer->id,
                        'activated_server_id' => $standbyServer->id,
                        'activated_ip' => $standbyServer->ip,
                        'activated_port' => $standbyServer->port,
                    ]);
                    
                    $message = "تم تحويل السيرفر {$backendServer->ip}:{$backendServer->port} إلى وضع Standby";
                    $message .= " (تم تفعيل السيرفر {$standbyServer->ip}:{$standbyServer->port} تلقائياً)";
                } else {
                    // لا يوجد سيرفر standby صحي متاح
                    return redirect()->route('sites.backends', $site)
                        ->with('error', 'لا يمكن تعطيل آخر سيرفر نشط. لا يوجد سيرفر احتياطي صحي متاح.');
                }
            } else {
                // يوجد سيرفرات نشطة أخرى، يمكن تحويله إلى standby بأمان
                $backendServer->status = 'standby';
                $message = "تم تحويل السيرفر {$backendServer->ip}:{$backendServer->port} إلى وضع Standby";
            }
        } else {
            // إذا كان standby، نحوله إلى active
            // تحويل جميع السيرفرات النشطة الأخرى إلى standby أولاً
            $otherActiveServers = $site->backendServers()
                ->where('id', '!=', $backendServer->id)
                ->where('status', 'active')
                ->get();
            
            foreach ($otherActiveServers as $otherServer) {
                $otherServer->status = 'standby';
                $otherServer->save();
                
                \Log::info("Auto-switched server to standby", [
                    'server_id' => $otherServer->id,
                    'ip' => $otherServer->ip,
                    'port' => $otherServer->port,
                    'reason' => 'Another server activated',
                ]);
            }
            
            // تفعيل السيرفر المحدد
            $backendServer->status = 'active';
            $backendServer->fail_count = 0;
            $message = "تم تحويل السيرفر {$backendServer->ip}:{$backendServer->port} إلى وضع Active";
            
            if ($otherActiveServers->count() > 0) {
                $switchedServers = $otherActiveServers->map(fn($s) => "{$s->ip}:{$s->port}")->implode(', ');
                $message .= " (تم تحويل السيرفرات الأخرى تلقائياً: {$switchedServers})";
            }
        }
        
        $backendServer->save();
        
        // إعادة توليد ملف Nginx
        $this->generateNginxConfig($site);
        
        return redirect()->route('sites.backends', $site)
            ->with('status', $message);
    }

    /**
     * تحديث وضع Failover (Auto/Manual)
     */
    public function updateFailoverMode(Site $site, Request $request)
    {
        $this->checkSiteAccess($site);
        
        $request->validate([
            'failover_mode' => 'required|in:auto,manual',
        ]);
        
        $site->failover_mode = $request->failover_mode;
        $site->save();
        
        $modeText = $request->failover_mode === 'auto' ? 'Automatic' : 'Manual';
        
        return redirect()->route('sites.backends', $site)
            ->with('status', "Failover mode updated to {$modeText}");
    }

    /**
     * تنفيذ Failover يدوي - التبديل من السيرفر النشط إلى الاحتياطي
     */
    public function manualFailover(Site $site, BackendHealthCheckService $healthCheckService)
    {
        $this->checkSiteAccess($site);
        
        // الحصول على السيرفرات النشطة
        $activeServers = $site->backendServers()
            ->where('status', 'active')
            ->orderBy('priority')
            ->get();
        
        if ($activeServers->isEmpty()) {
            return redirect()->route('sites.backends', $site)
                ->with('error', 'لا توجد سيرفرات نشطة للتبديل.');
        }
        
        // الحصول على السيرفرات الاحتياطية
        $standbyServers = $site->backendServers()
            ->where('status', 'standby')
            ->orderBy('priority')
            ->get();
        
        if ($standbyServers->isEmpty()) {
            return redirect()->route('sites.backends', $site)
                ->with('error', 'لا توجد سيرفرات احتياطية للتبديل إليها.');
        }
        
        // تحويل جميع السيرفرات النشطة إلى standby
        $deactivatedServers = [];
        foreach ($activeServers as $server) {
            $server->status = 'standby';
            $server->save();
            $deactivatedServers[] = "{$server->ip}:{$server->port}";
        }
        
        // تفعيل أول سيرفر احتياطي
        $newActiveServer = $standbyServers->first();
        $newActiveServer->status = 'active';
        $newActiveServer->fail_count = 0;
        $newActiveServer->save();
        
        \Log::info("Manual failover executed", [
            'site_id' => $site->id,
            'site_name' => $site->server_name,
            'deactivated_servers' => $deactivatedServers,
            'new_active_server' => "{$newActiveServer->ip}:{$newActiveServer->port}",
        ]);
        
        // إعادة توليد ملف Nginx
        $this->generateNginxConfig($site);
        
        $message = "تم تنفيذ Failover بنجاح:\n";
        $message .= "✓ تم تعطيل: " . implode(', ', $deactivatedServers) . "\n";
        $message .= "✓ تم تفعيل: {$newActiveServer->ip}:{$newActiveServer->port}";
        
        return redirect()->route('sites.backends', $site)
            ->with('status', $message);
    }
}
