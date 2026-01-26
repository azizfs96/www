<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BackendServer extends Model
{
    protected $table = 'site_backend_servers';

    protected $fillable = [
        'site_id',
        'ip',
        'port',
        'status',
        'priority',
        'health_check_enabled',
        'last_health_check',
        'is_healthy',
        'fail_count',
    ];

    protected $casts = [
        'port' => 'integer',
        'priority' => 'integer',
        'health_check_enabled' => 'boolean',
        'is_healthy' => 'boolean',
        'fail_count' => 'integer',
        'last_health_check' => 'datetime',
    ];

    /**
     * العلاقة مع الموقع
     */
    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * الحصول على عنوان السيرفر الكامل (IP:Port)
     */
    public function getFullAddressAttribute(): string
    {
        return "{$this->ip}:{$this->port}";
    }

    /**
     * التحقق من أن السيرفر نشط
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * التحقق من أن السيرفر في وضع الاستعداد
     */
    public function isStandby(): bool
    {
        return $this->status === 'standby';
    }
}
