<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SitePolicy extends Model
{
    protected $fillable = [
        'site_id',
        'waf_enabled',
        'paranoia_level',
        'anomaly_threshold',
        'inherit_global_rules',
        'block_suspicious_user_agents',
        'block_sql_injection',
        'block_xss',
        'block_rce',
        'block_lfi',
        'block_rfi',
        'rate_limiting_enabled',
        'requests_per_minute',
        'burst_size',
        'excluded_urls',
        'excluded_ips',
        'detailed_logging',
        'log_level',
        'custom_modsec_rules',
        'notes',
    ];

    protected $casts = [
        'waf_enabled' => 'boolean',
        'inherit_global_rules' => 'boolean',
        'block_suspicious_user_agents' => 'boolean',
        'block_sql_injection' => 'boolean',
        'block_xss' => 'boolean',
        'block_rce' => 'boolean',
        'block_lfi' => 'boolean',
        'block_rfi' => 'boolean',
        'rate_limiting_enabled' => 'boolean',
        'detailed_logging' => 'boolean',
        'paranoia_level' => 'integer',
        'requests_per_minute' => 'integer',
        'burst_size' => 'integer',
    ];

    /**
     * العلاقة مع الموقع
     */
    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * الحصول على القواعد المستثناة كمصفوفة
     */
    public function getExcludedUrlsArrayAttribute(): array
    {
        return $this->excluded_urls 
            ? array_filter(explode("\n", $this->excluded_urls))
            : [];
    }

    /**
     * الحصول على IPs المستثناة كمصفوفة
     */
    public function getExcludedIpsArrayAttribute(): array
    {
        return $this->excluded_ips 
            ? array_filter(explode("\n", $this->excluded_ips))
            : [];
    }
}
