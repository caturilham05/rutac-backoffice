<?php

use App\Jobs\ProcessShopeeWebhook;
use Illuminate\Log\Logger;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

uses(TestCase::class);

test('payload is written to the Shopee log channel', function () {
    $payload = ['code' => 3, 'shop_id' => 727720655];
    $logger = Mockery::mock(Logger::class);
    $logger->shouldReceive('info')
        ->once()
        ->with('Received Shopee order status push', $payload);
    Log::shouldReceive('channel')
        ->once()
        ->with('shopee')
        ->andReturn($logger);

    (new ProcessShopeeWebhook($payload))->handle();
});
