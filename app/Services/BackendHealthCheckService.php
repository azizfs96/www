<?php

namespace App\Services;

use App\Models\BackendServer;
use App\Models\Site;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class BackendHealthCheckService
{
    /**
     * فحص صحة جميع السيرفرات الخلفية
     */
    public function checkAllBackends(): void
    {
        $servers = BackendServer::where('health_check_enabled', true)->get();
        
        foreach ($servers as $server) {
            $this->checkServer($server);
        }
    }

    /**
     * فحص صحة سيرفر محدد
     */
    public function checkServer(BackendServer $server): bool
    {
        $isHealthy = $this->performHealthCheck($server);
        
        $server->last_health_check = now();
        $server->is_healthy = $isHealthy;
        
        if ($isHealthy) {
            $server->fail_count = 0;
        } else {
            $server->fail_count++;
        }
        
        $server->save();
        
        // إذا فشل السيرفر النشط، قم بالتبديل إلى السيرفر الاحتياطي
        if (!$isHealthy && $server->status === 'active' && $server->fail_count >= 3) {
            $this->performFailover($server->site);
        }
        
        return $isHealthy;
    }

    /**
     * تنفيذ فحص الصحة الفعلي
     */
    protected function performHealthCheck(BackendServer $server): bool
    {
        try {
            // محاولة الاتصال بالسيرفر عبر HTTP
            $url = "http://{$server->ip}:{$server->port}";
            
            // استخدام timeout قصير (5 ثواني)
            $response = Http::timeout(5)->get($url);
            
            // اعتبار السيرفر صحي إذا كان الرد 200-399
            $isHealthy = $response->status() >= 200 && $response->status() < 400;
            
            Log::info("Health check for backend server", [
                'server_id' => $server->id,
                'ip' => $server->ip,
                'port' => $server->port,
                'status_code' => $response->status(),
                'is_healthy' => $isHealthy,
            ]);
            
            return $isHealthy;
        } catch (\Exception $e) {
            // في حالة فشل الاتصال، السيرفر غير صحي
            Log::warning("Health check failed for backend server", [
                'server_id' => $server->id,
                'ip' => $server->ip,
                'port' => $server->port,
                'error' => $e->getMessage(),
            ]);
            
            return false;
        }
    }

    /**
     * تنفيذ Failover - التبديل من السيرفر النشط إلى الاحتياطي
     */
    protected function performFailover(Site $site): void
    {
        Log::info("Initiating failover for site", [
            'site_id' => $site->id,
            'server_name' => $site->server_name,
        ]);
        
        // الحصول على السيرفرات النشطة غير الصحية
        $unhealthyActiveServers = $site->backendServers()
            ->where('status', 'active')
            ->where('is_healthy', false)
            ->where('fail_count', '>=', 3)
            ->get();
        
        if ($unhealthyActiveServers->isEmpty()) {
            return;
        }
        
        // تعطيل السيرفرات النشطة غير الصحية
        foreach ($unhealthyActiveServers as $server) {
            $oldStatus = $server->status;
            $server->status = 'standby';
            $server->save();
            
            // التأكد من أن التحديث تم حفظه
            $server->refresh();
            
            Log::info("Deactivated unhealthy active server", [
                'server_id' => $server->id,
                'ip' => $server->ip,
                'port' => $server->port,
                'old_status' => $oldStatus,
                'new_status' => $server->status,
                'saved' => $server->status === 'standby',
            ]);
        }
        
        // إعادة تحميل الموقع من قاعدة البيانات
        $site->refresh();
        
        // تفعيل أول سيرفر احتياطي صحي (من قاعدة البيانات مباشرة)
        $healthyStandbyServer = BackendServer::where('site_id', $site->id)
            ->where('status', 'standby')
            ->where('is_healthy', true)
            ->orderBy('priority')
            ->first();
        
        if ($healthyStandbyServer) {
            $oldStatus = $healthyStandbyServer->status;
            $healthyStandbyServer->status = 'active';
            $healthyStandbyServer->fail_count = 0;
            $healthyStandbyServer->save();
            
            // التأكد من أن التحديث تم حفظه
            $healthyStandbyServer->refresh();
            
            Log::info("Activated standby server", [
                'server_id' => $healthyStandbyServer->id,
                'ip' => $healthyStandbyServer->ip,
                'port' => $healthyStandbyServer->port,
                'old_status' => $oldStatus,
                'new_status' => $healthyStandbyServer->status,
                'saved' => $healthyStandbyServer->status === 'active',
            ]);
            
            // إعادة توليد ملف Nginx مع التغييرات الجديدة
            $this->regenerateNginxConfig($site);
        } else {
            Log::warning("No healthy standby server available for failover", [
                'site_id' => $site->id,
                'standby_servers_count' => BackendServer::where('site_id', $site->id)
                    ->where('status', 'standby')
                    ->count(),
                'healthy_standby_count' => BackendServer::where('site_id', $site->id)
                    ->where('status', 'standby')
                    ->where('is_healthy', true)
                    ->count(),
            ]);
        }
    }

    /**
     * إعادة توليد ملف Nginx
     */
    protected function regenerateNginxConfig(Site $site): void
    {
        try {
            // إعادة تحميل الموقع من قاعدة البيانات لضمان الحصول على أحدث البيانات
            $site->refresh();
            $site->load('backendServers');
            
            // إعادة تحميل جميع السيرفرات من قاعدة البيانات
            $site->backendServers->each->refresh();
            
            $controller = app(\App\Http\Controllers\SiteController::class);
            $controller->generateNginxConfig($site);
            
            Log::info("Regenerated Nginx config after failover", [
                'site_id' => $site->id,
                'active_servers' => $site->backendServers()->where('status', 'active')->pluck('ip', 'port')->toArray(),
                'standby_servers' => $site->backendServers()->where('status', 'standby')->pluck('ip', 'port')->toArray(),
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to regenerate Nginx config after failover", [
                'site_id' => $site->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * إعادة تفعيل سيرفر بعد استعادته
     */
    public function reactivateServer(BackendServer $server): void
    {
        if ($server->is_healthy && $server->status === 'standby') {
            // التحقق من وجود سيرفرات نشطة
            $activeServers = $server->site->backendServers()
                ->where('status', 'active')
                ->where('is_healthy', true)
                ->count();
            
            // إذا لم يكن هناك سيرفرات نشطة، نعيد تفعيل هذا السيرفر
            if ($activeServers === 0) {
                $server->status = 'active';
                $server->fail_count = 0;
                $server->save();
                
                $this->regenerateNginxConfig($server->site);
                
                Log::info("Reactivated server after recovery", [
                    'server_id' => $server->id,
                    'ip' => $server->ip,
                    'port' => $server->port,
                ]);
            }
        }
    }
}

