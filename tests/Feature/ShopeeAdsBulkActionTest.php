<?php

use App\Jobs\ShopeeAdsActionJob;
use App\Models\AdsShopee;
use App\Models\Marketplace;
use App\Models\User;
use App\Services\Shopee\ShopeeServices;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;

function bulkAd(string $status, int $campaignId): AdsShopee
{
    $marketplace = Marketplace::create([
        'marketplace' => 'Shopee', 'store' => 'Store '.$campaignId,
        'shop_id' => $campaignId, 'access_token' => 'test-token',
    ]);

    return AdsShopee::create([
        'marketplace_id' => $marketplace->id, 'campaign_id' => $campaignId,
        'name' => 'Campaign '.$campaignId, 'status' => $status,
    ]);
}

test('bulk actions queue all eligible stores regardless of table filters', function (string $action, string $expectedStatus) {
    config()->set('services.shopee.host', 'https://partner.test');
    Http::preventStrayRequests();
    Http::fake(['partner.test/api/v2/ads/edit_manual_product_ads*' => Http::response(['error' => ''])]);
    Queue::fake([ShopeeAdsActionJob::class]);
    $first = bulkAd('ongoing', 1);
    $second = bulkAd('paused', 2);
    foreach (['ended', 'deleted', 'closed', 'scheduled'] as $index => $status) {
        bulkAd($status, $index + 3);
    }

    $this->actingAs(User::factory()->create())
        ->from(route('shopee.ads.index'))
        ->post(route('shopee.ads.bulk', $action), ['campaign_name' => 'missing', 'marketplace_id' => $first->marketplace_id])
        ->assertRedirect(route('shopee.ads.index'))->assertSessionHas('success');

    Queue::assertPushed(ShopeeAdsActionJob::class, 2);
    Queue::assertPushedOn('shopee', ShopeeAdsActionJob::class);
    foreach (Queue::pushed(ShopeeAdsActionJob::class) as $job) {
        $job->handle(app(ShopeeServices::class));
    }
    expect($first->fresh()->status)->toBe($expectedStatus);
    expect($second->fresh()->status)->toBe($expectedStatus);
    Http::assertSentCount(2);
    Http::assertSent(fn ($request) => $request['edit_action'] === $action && $request['campaign_id'] === 1);
})->with([['pause', 'paused'], ['resume', 'ongoing']]);

test('guests cannot queue bulk ads actions', function () {
    Queue::fake();
    $this->post(route('shopee.ads.bulk', 'pause'))->assertRedirect(route('login'));
    Queue::assertNothingPushed();
});

test('unsupported bulk actions cannot queue jobs', function () {
    Queue::fake();
    $this->actingAs(User::factory()->create())->post('/backoffice/configuration/ads-shopee/bulk/delete')->assertNotFound();
    Queue::assertNothingPushed();
});

test('empty campaigns return useful feedback without jobs', function () {
    Queue::fake();
    $this->actingAs(User::factory()->create())->post(route('shopee.ads.bulk', 'pause'))
        ->assertSessionHas('success', 'Tidak ada iklan yang dapat diproses.');
    Queue::assertNothingPushed();
});

test('failed Shopee calls fail the job and preserve the campaign status', function (array $body, int $status) {
    config()->set('services.shopee.host', 'https://partner.test');
    Http::preventStrayRequests();
    Http::fake(['partner.test/api/v2/ads/edit_manual_product_ads*' => Http::response($body, $status)]);
    $ad = bulkAd('ongoing', 1);

    expect(fn () => (new ShopeeAdsActionJob($ad, 'pause'))->handle(app(ShopeeServices::class)))
        ->toThrow(Exception::class);
    expect($ad->fresh()->status)->toBe('ongoing');
    Http::assertSentCount(1);
})->with([
    'API rejection' => [['error' => 'invalid', 'message' => 'Rejected'], 200],
    'HTTP failure' => [[], 500],
]);
