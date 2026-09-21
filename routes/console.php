<?php

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
app(Schedule::class)->command('payouts:process')->dailyAt('02:00')->withoutOverlapping();
app(Schedule::class)->command('payouts:reconcile')->hourly()->withoutOverlapping();
