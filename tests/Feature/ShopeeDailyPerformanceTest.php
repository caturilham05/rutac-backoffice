<?php

use App\Models\Marketplace;
use App\Models\User;
use App\Services\Shopee\ShopeeServices;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

test('service fetches all CPC ads daily performance', function () {
    config()->set('services.shopee', [
        'host' => 'https://partner.test',
        'partner_id' => '2031431',
        'partner_key' => 'partner-key',
    ]);
    Http::preventStrayRequests();
    Http::fake([
        'partner.test/api/v2/ads/get_all_cpc_ads_daily_performance*' => Http::response([
            'response' => ['date' => '01-09-2026', 'clicks' => 12],
        ]),
    ]);

    $response = app(ShopeeServices::class)->getAllCpcAdsDailyPerformance(
        'access-token',
        456,
        '01-09-2026',
        '03-09-2026',
    );

    expect($response['response']['clicks'])->toBe(12);
    Http::assertSent(fn (Request $request) => $request->method() === 'GET'
        && $request['shop_id'] === 456
        && $request['start_date'] === '01-09-2026'
        && $request['end_date'] === '03-09-2026');
});

test('authenticated route validates dates and consumes the service', function () {
    $marketplace = Marketplace::create([
        'marketplace' => 'Shopee',
        'shop_id' => 456,
        'access_token' => 'access-token',
    ]);
    $shopee = Mockery::mock(ShopeeServices::class);
    $shopee->shouldReceive('getAllCpcAdsDailyPerformance')
        ->once()
        ->with('access-token', 456, '01-09-2026', '03-09-2026')
        ->andReturn(['response' => ['clicks' => 12]]);
    $this->app->instance(ShopeeServices::class, $shopee);

    $this->actingAs(User::factory()->create())
        ->get(route('shopee.ads.daily-performance', [
            'marketplace' => $marketplace,
            'start_date' => '01-09-2026',
            'end_date' => '03-09-2026',
        ]))
        ->assertOk()
        ->assertJsonPath('response.clicks', 12);
});

test('daily performance route rejects an invalid date range', function () {
    $marketplace = Marketplace::create([
        'marketplace' => 'Shopee',
        'shop_id' => 456,
        'access_token' => 'access-token',
    ]);

    $this->actingAs(User::factory()->create())
        ->get(route('shopee.ads.daily-performance', [
            'marketplace' => $marketplace,
            'start_date' => '03-09-2026',
            'end_date' => '01-09-2026',
        ]))
        ->assertSessionHasErrors('end_date');
});
