<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UrlRule extends Model
{
    protected $fillable = [
        'name',
        'path',
        'allowed_ips',
        'enabled',
    ];
}
