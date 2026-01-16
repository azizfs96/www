<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WafEvent extends Model
{
    protected $fillable = [
        'site_id',
        'event_time',
        'client_ip',
        'country',
        'host',
        'uri',
        'method',
        'status',
        'rule_id',
        'severity',
        'message',
        'action',
        'user_agent',
        'unique_id',
        'raw',
    ];

    protected $casts = [
        'event_time' => 'datetime',
        'raw'        => 'array',
    ];

    /**
     * العلاقة مع الموقع
     */
    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }
}
