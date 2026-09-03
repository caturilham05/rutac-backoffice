<?php

use App\Http\Controllers\ShopeeWebhookController;
use Illuminate\Support\Facades\Route;

Route::post('/shopee/webhook', [ShopeeWebhookController::class, 'handle'])->name('shopee.webhook');
// Route::post('/shopee/webhook', [ShopeeWebhookController::class, 'handle'])->name('shopee.webhook');
