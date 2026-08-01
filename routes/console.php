<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Schedule::call(function () {
    Artisan::call('shopee:refresh-tokens');
})->everyTwoHours();

Schedule::command('app:shopee-ads-action pause')->dailyAt('00:00');
Schedule::command('app:shopee-ads-action resume')->dailyAt('15:00');
