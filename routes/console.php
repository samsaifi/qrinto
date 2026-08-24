<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
| Weekly analytics email.
| Default: Mondays 07:00 local (app timezone). Reports the previous
| 7 completed days. Override day / time via ANALYTICS_WEEKLY_DAY (1–7)
| and ANALYTICS_WEEKLY_TIME (HH:MM) in .env. Skips silently if no
| recipients are configured.
*/
$weeklyDay  = (int) config('analytics.weekly.day', 1);
$weeklyTime = (string) config('analytics.weekly.time', '07:00');

Schedule::command('analytics:weekly-report')
    ->weeklyOn($weeklyDay, $weeklyTime)
    ->timezone(config('app.timezone', 'UTC'))
    ->onOneServer()
    ->withoutOverlapping()
    ->when(fn () => !empty(config('analytics.recipients')));
