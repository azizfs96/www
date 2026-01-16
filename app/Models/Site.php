<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Site extends Model
{
    protected $fillable = [
        'name',
        'server_name',
        'backend_ip',
        'backend_port',
        'ssl_enabled',
        'ssl_cert_path',
        'ssl_key_path',
        'enabled',
        'notes',
    ];

    protected $casts = [
        'ssl_enabled' => 'boolean',
        'enabled' => 'boolean',
        'backend_port' => 'integer',
    ];

    /**
     * العلاقة مع سياسات WAF
     */
    public function policy(): HasOne
    {
        return $this->hasOne(SitePolicy::class);
    }

    /**
     * العلاقة مع قواعد IP
     */
    public function ipRules(): HasMany
    {
        return $this->hasMany(IpRule::class);
    }

    /**
     * العلاقة مع قواعد URL
     */
    public function urlRules(): HasMany
    {
        return $this->hasMany(UrlRule::class);
    }

    /**
     * العلاقة مع قواعد الدول
     */
    public function countryRules(): HasMany
    {
        return $this->hasMany(CountryRule::class);
    }

    /**
     * العلاقة مع أحداث WAF
     */
    public function events(): HasMany
    {
        return $this->hasMany(WafEvent::class);
    }

    /**
     * إنشاء سياسة افتراضية عند إنشاء موقع جديد
     */
    protected static function booted(): void
    {
        static::created(function (Site $site) {
            $site->policy()->create([
                'waf_enabled' => true,
                'paranoia_level' => 1,
                'inherit_global_rules' => true,
            ]);
        });
    }
}
