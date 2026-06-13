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

// استيراد سجلّات ModSecurity (أحداث الحظر) إلى قاعدة البيانات كل دقيقة
Schedule::command('waf:import-logs')
    ->everyMinute()
    ->withoutOverlapping()
    ->runInBackground();
