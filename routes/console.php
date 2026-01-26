<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// جدولة فحص صحة السيرفرات الخلفية كل دقيقة
Schedule::command('backend:health-check')
    ->everyMinute()
    ->withoutOverlapping()
    ->runInBackground();
