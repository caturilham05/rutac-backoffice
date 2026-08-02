<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Schedule::call(function () {
    Artisan::call('shopee:refresh-tokens');
})->everyTwoHours();

Schedule::call(function () {
    Artisan::call('app:shopee-ads-action', ['action' => 'pause']);
})->dailyAt('01:00');

Schedule::call(function () {
    Artisan::call('app:shopee-ads-action', ['action' => 'resume']);
})->dailyAt('12:00');
