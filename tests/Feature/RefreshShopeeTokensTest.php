<?php

use App\Models\Marketplace;
use App\Services\Shopee\ShopeeServices;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

test('refresh request sends partner id as an integer', function () {
    config()->set('services.shopee', [
        'host' => 'https://partner.test',
        'partner_id' => '2031431',
        'partner_key' => 'partner-key',
    ]);
    Http::preventStrayRequests();
    Http::fake([
        'partner.test/api/v2/auth/access_token/get*' => Http::response(['expire_in' => 14400]),
    ]);

    app(ShopeeServices::class)->getAccessTokenShopLevel(123, 'refresh-token');

    Http::assertSent(fn (Request $request) => $request['partner_id'] === 2031431);
});

test('failed refresh does not read success fields or change the marketplace', function () {
    $marketplace = Marketplace::create([
        'marketplace' => 'shopee',
        'shop_id' => 123,
        'access_token' => 'old-access-token',
        'refresh_token' => 'old-refresh-token',
    ]);
    $shopee = Mockery::mock(ShopeeServices::class);
    $shopee->shouldReceive('getAccessTokenShopLevel')
        ->once()
        ->with(123, 'old-refresh-token')
        ->andReturn([
            'error' => 'error_param',
            'message' => 'the format of partner_id parameter is wrong',
        ]);
    $this->app->instance(ShopeeServices::class, $shopee);

    $this->artisan('shopee:refresh-tokens')
        ->expectsOutput('Failed to refresh token for shop: the format of partner_id parameter is wrong')
        ->assertSuccessful();

    expect($marketplace->fresh())
        ->access_token->toBe('old-access-token')
        ->refresh_token->toBe('old-refresh-token');
});

test('incomplete refresh response does not change the marketplace', function () {
    $marketplace = Marketplace::create([
        'marketplace' => 'shopee',
        'shop_id' => 123,
        'access_token' => 'old-access-token',
        'refresh_token' => 'old-refresh-token',
    ]);
    $shopee = Mockery::mock(ShopeeServices::class);
    $shopee->shouldReceive('getAccessTokenShopLevel')
        ->once()
        ->andReturn([]);
    $this->app->instance(ShopeeServices::class, $shopee);

    $this->artisan('shopee:refresh-tokens')
        ->expectsOutput('Failed to refresh token for shop: Invalid response from Shopee')
        ->assertSuccessful();

    expect($marketplace->fresh()->access_token)->toBe('old-access-token');
});
