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
     * Get event_time in Saudi Arabia timezone
     * This ensures event_time is always displayed correctly regardless of how it's stored
     * 
     * Note: We assume event_time is stored in UTC in the database (standard practice)
     * Laravel automatically converts it to app timezone (Asia/Riyadh) when reading
     * But we ensure it's displayed correctly here
     */
    public function getEventTimeSaudiAttribute()
    {
        if (!$this->event_time) {
            return null;
        }
        
        // Laravel automatically converts datetime from database (assumed UTC) to app timezone
        // Since app timezone is now Asia/Riyadh, event_time should already be in Saudi timezone
        // But we ensure it's explicitly set to Asia/Riyadh for consistency
        $eventTime = $this->event_time;
        
        // If timezone is not Asia/Riyadh, convert it
        // This handles cases where data might be stored incorrectly
        if ($eventTime->timezone->getName() !== 'Asia/Riyadh') {
            // Assume the stored time is in UTC and convert to Saudi Arabia
            $eventTime = $eventTime->copy()->utc()->setTimezone('Asia/Riyadh');
        }
        
        return $eventTime;
    }

    /**
     * العلاقة مع الموقع
     */
    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }
}
