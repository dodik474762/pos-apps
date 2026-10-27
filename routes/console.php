<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('telescope:prune --hours=48')->daily();

Schedule::command('billing:daily')
    ->dailyAt('01:00')
    ->timezone(config('billing.timezone', 'Asia/Jakarta'))
    ->withoutOverlapping();
