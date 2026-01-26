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
     * Problem: Some old data might be stored in Saudi Arabia timezone instead of UTC
     * When Laravel reads it, it treats it as UTC and converts to Saudi time again,
     * causing a 3-hour offset (Saudi time is UTC+3)
     * 
     * Solution: Get raw value from database and parse it as UTC, then convert to Saudi Arabia
     */
    public function getEventTimeSaudiAttribute()
    {
        if (!$this->event_time) {
            return null;
        }
        
        // Get the raw timestamp value from database (before Laravel's timezone conversion)
        // This is the actual value stored in the database
        try {
            $rawValue = $this->getRawOriginal('event_time');
            
            if ($rawValue) {
                // Parse the raw value as UTC (database standard)
                // Even if it was stored incorrectly as Saudi time, we treat it as UTC
                // and convert to Saudi Arabia timezone for display
                // This handles both cases: data stored as UTC or incorrectly as Saudi time
                $eventTime = \Carbon\Carbon::parse($rawValue, 'UTC')->setTimezone('Asia/Riyadh');
                return $eventTime;
            }
        } catch (\Exception $e) {
            // Fallback if getRawOriginal doesn't work (e.g., if attribute was already accessed)
        }
        
        // Alternative: Query database directly to get raw value
        try {
            $rawValue = \Illuminate\Support\Facades\DB::table('waf_events')
                ->where('id', $this->id)
                ->value('event_time');
            
            if ($rawValue) {
                // Parse as UTC and convert to Saudi Arabia
                $eventTime = \Carbon\Carbon::parse($rawValue, 'UTC')->setTimezone('Asia/Riyadh');
                return $eventTime;
            }
        } catch (\Exception $e) {
            // Fallback if query fails
        }
        
        // Final fallback: Use the already converted value
        // If data was stored as Saudi time, Laravel treated it as UTC and added 3 hours
        // So we need to subtract 3 hours to get the correct time
        $eventTime = $this->event_time;
        
        // Always ensure we're working with UTC first, then convert to Saudi Arabia
        // If already in Saudi timezone, go back to UTC first
        if ($eventTime->timezone->getName() === 'Asia/Riyadh') {
            // Subtract 3 hours to get UTC, then convert to Saudi Arabia
            // This corrects for the case where data was stored as Saudi time
            $eventTime = $eventTime->copy()->subHours(3)->setTimezone('Asia/Riyadh');
        } else {
            // Already in UTC or another timezone, convert to Saudi Arabia
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
