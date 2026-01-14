<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CountryRule extends Model
{
    protected $fillable = ['country_code', 'type', 'enabled'];
}
