<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IpRule extends Model
{
    protected $fillable = ['site_id', 'ip', 'type'];

    /**
     * العلاقة مع الموقع
     * null = قاعدة عامة
     */
    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * Scope للقواعد العامة
     */
    public function scopeGlobal($query)
    {
        return $query->whereNull('site_id');
    }

    /**
     * Scope للقواعد الخاصة بموقع
     */
    public function scopeForSite($query, $siteId)
    {
        return $query->where('site_id', $siteId);
    }
}
