<?php

use App\Models\AdsShopee;
use App\Models\Marketplace;
use App\Models\MarketplaceAdDailyMetric;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

function shopeeMarketplace(array $attributes = []): Marketplace
{
    return Marketplace::create(array_merge([
        'marketplace' => 'Shopee', 'store' => 'Toko Utama',
        'shop_id' => 456, 'access_token' => 'access-token',
    ], $attributes));
}

function dailyPerformance(array $attributes = []): array
{
    return array_merge([
        'date' => '01-09-2026', 'impression' => '1000', 'clicks' => '25', 'ctr' => '0.025',
        'direct_order' => '4', 'broad_order' => '6', 'direct_conversions' => '0.16',
        'broad_conversions' => '0.24', 'direct_item_sold' => '5', 'broad_item_sold' => '8',
        'direct_gmv' => '150000.50', 'broad_gmv' => '250000.75', 'expense' => '50000',
        'cost_per_conversion' => '8333.3333', 'direct_roas' => '3', 'broad_roas' => '5',
        'unexpected' => 'ignored',
    ], $attributes);
}

beforeEach(function () {
    config()->set('services.shopee', [
        'host' => 'https://partner.test', 'partner_id' => '2031431', 'partner_key' => 'partner-key',
    ]);
});

test('guests cannot read or synchronize daily performance', function () {
    $marketplace = shopeeMarketplace();

    $this->get(route('shopee.ads.index'))->assertRedirect(route('login'));
    $this->post(route('shopee.ads.daily-metrics.sync', $marketplace), [
        'start_date' => '2026-09-01', 'end_date' => '2026-09-03',
    ])->assertRedirect(route('login'));
});

test('synchronization sends the inclusive range and persists normalized metrics', function () {
    $this->travelTo('2026-09-03 12:00:00');
    $marketplace = shopeeMarketplace();
    Http::preventStrayRequests();
    Http::fake(['partner.test/api/v2/ads/get_all_cpc_ads_daily_performance*' => Http::response([
        'response' => [dailyPerformance()],
    ])]);

    $this->actingAs(User::factory()->create())
        ->post(route('shopee.ads.daily-metrics.sync', $marketplace), [
            'start_date' => '2026-09-01', 'end_date' => '2026-09-03',
            'marketplace_id' => 999, 'synced_at' => '2000-01-01 00:00:00',
        ])
        ->assertRedirect(route('shopee.ads.index', [
            'marketplace_id' => $marketplace->id, 'start_date' => '2026-09-01', 'end_date' => '2026-09-03',
        ]))->assertSessionHas('success');

    Http::assertSent(fn (Request $request): bool => $request['shop_id'] === 456
        && $request['start_date'] === '01-09-2026' && $request['end_date'] === '03-09-2026');
    $this->assertDatabaseHas('marketplace_ad_daily_metrics', [
        'marketplace_id' => $marketplace->id, 'metric_date' => '2026-09-01', 'impressions' => 1000,
        'direct_orders' => 4, 'direct_conversion_rate' => 0.16, 'direct_items_sold' => 5,
        'broad_gmv' => 250000.75, 'synced_at' => '2026-09-03 12:00:00',
    ]);
});

test('synchronizing the same day updates its single row', function () {
    $marketplace = shopeeMarketplace();
    Http::preventStrayRequests();
    Http::fakeSequence('partner.test/api/v2/ads/get_all_cpc_ads_daily_performance*')
        ->push(['response' => dailyPerformance(['clicks' => 25])])
        ->push(['response' => dailyPerformance(['clicks' => 40])]);
    $user = User::factory()->create();
    $payload = ['start_date' => '2026-09-01', 'end_date' => '2026-09-01'];

    $this->actingAs($user)->post(route('shopee.ads.daily-metrics.sync', $marketplace), $payload);
    $this->actingAs($user)->post(route('shopee.ads.daily-metrics.sync', $marketplace), $payload);

    expect(MarketplaceAdDailyMetric::whereBelongsTo($marketplace)->count())->toBe(1)
        ->and(MarketplaceAdDailyMetric::whereBelongsTo($marketplace)->value('clicks'))->toBe(40);
});

test('invalid dates do not call Shopee or write metrics', function (array $payload) {
    $marketplace = shopeeMarketplace();
    Http::preventStrayRequests();
    Http::fake();

    $this->actingAs(User::factory()->create())
        ->post(route('shopee.ads.daily-metrics.sync', $marketplace), $payload)
        ->assertSessionHasErrors();

    Http::assertNothingSent();
    $this->assertDatabaseCount('marketplace_ad_daily_metrics', 0);
})->with([
    'invalid format' => [['start_date' => '01-09-2026', 'end_date' => '2026-09-03']],
    'reversed range' => [['start_date' => '2026-09-03', 'end_date' => '2026-09-01']],
]);

test('invalid marketplace credentials do not call Shopee', function (array $attributes) {
    $marketplace = shopeeMarketplace($attributes);
    Http::preventStrayRequests();
    Http::fake();

    $this->actingAs(User::factory()->create())
        ->post(route('shopee.ads.daily-metrics.sync', $marketplace), [
            'start_date' => '2026-09-01', 'end_date' => '2026-09-03',
        ])->assertSessionHasErrors('marketplace');

    Http::assertNothingSent();
})->with([
    'not Shopee' => [['marketplace' => 'Tokopedia']],
    'missing shop id' => [['shop_id' => null]],
    'missing access token' => [['access_token' => null]],
]);

test('an upstream failure leaves stored metrics unchanged and returns safe feedback', function () {
    $marketplace = shopeeMarketplace();
    MarketplaceAdDailyMetric::create(['marketplace_id' => $marketplace->id, 'metric_date' => '2026-09-01', 'clicks' => 7]);
    Http::preventStrayRequests();
    Http::fake(['partner.test/api/v2/ads/get_all_cpc_ads_daily_performance*' => Http::response('secret upstream failure', 500)]);

    $this->actingAs(User::factory()->create())
        ->post(route('shopee.ads.daily-metrics.sync', $marketplace), [
            'start_date' => '2026-09-01', 'end_date' => '2026-09-03',
        ])->assertSessionHas('error', 'Sinkronisasi Ads Daily gagal. Silakan coba lagi.')
        ->assertSessionMissing('secret upstream failure');

    expect(MarketplaceAdDailyMetric::whereBelongsTo($marketplace)->value('clicks'))->toBe(7);
});

test('index isolates selected marketplace and range without exposing credentials', function () {
    $selected = shopeeMarketplace();
    $other = shopeeMarketplace(['shop_id' => 789, 'access_token' => 'other-secret']);
    foreach ([['2026-09-03', 3], ['2026-09-01', 1], ['2026-08-31', 99]] as [$date, $clicks]) {
        MarketplaceAdDailyMetric::create(['marketplace_id' => $selected->id, 'metric_date' => $date, 'clicks' => $clicks]);
    }
    MarketplaceAdDailyMetric::create(['marketplace_id' => $other->id, 'metric_date' => '2026-09-02', 'clicks' => 88]);

    $this->actingAs(User::factory()->create())
        ->get(route('shopee.ads.index', [
            'marketplace_id' => $selected->id, 'start_date' => '2026-09-01', 'end_date' => '2026-09-03',
        ]))->assertInertia(fn (Assert $page) => $page
        ->component('Backoffice/Configuration/AdsShopee')->where('daily.marketplace_id', $selected->id)
        ->has('daily.metrics', 2)->where('daily.metrics.0.clicks', 1)->where('daily.metrics.1.clicks', 3)
        ->missing('marketplaces.0.access_token')->missing('marketplaces.0.shop_id'));
});

test('campaign filters and sorting remain available on the index', function () {
    AdsShopee::create(['campaign_id' => 1, 'name' => 'Other', 'status' => 'paused', 'campaign_budget' => 100]);
    AdsShopee::create(['campaign_id' => 2, 'name' => 'Promo', 'status' => 'ongoing', 'campaign_budget' => 200]);

    $this->actingAs(User::factory()->create())
        ->get(route('shopee.ads.index', [
            'campaign_name' => 'Promo', 'status' => 'ongoing', 'sort' => 'campaign_budget', 'direction' => 'desc',
        ]))->assertInertia(fn (Assert $page) => $page
        ->has('ads.data', 1)->where('ads.data.0.name', 'Promo')->where('sort.direction', 'desc'));
});
