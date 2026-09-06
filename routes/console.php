<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
Schedule::command('app:end-active-subscription')
    ->daily()
    ->withoutOverlapping();
Schedule::command('payments:reconcile --limit=200')
    ->everyTenMinutes()
    ->withoutOverlapping();
Schedule::command('app:order-archive')
    ->monthly()
    ->withoutOverlapping();
