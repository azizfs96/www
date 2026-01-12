<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IpRule extends Model
{
    protected $fillable = ['ip', 'type'];
}
