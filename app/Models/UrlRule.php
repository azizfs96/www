<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UrlRule extends Model
{
    protected $fillable = [
        'name',
        'host',
        'path',
        'allowed_ips',
        'enabled',
    ];
}
