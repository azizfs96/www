<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WafEvent extends Model
{
    protected $fillable = [
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
}
