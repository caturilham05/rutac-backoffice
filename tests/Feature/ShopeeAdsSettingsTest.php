<?php

use App\Models\AdsShopee;
use App\Models\Marketplace;
use App\Models\MarketplaceAdDailyMetric;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    config()->set('services.shopee', ['host' => 'https://partner.test', 'partner_id' => 123, 'partner_key' => 'fixture-key']);
});

function settingsAd(array $attributes = [], array $store = []): AdsShopee
{
    $marketplace = Marketplace::create([
        'marketplace' => 'Shopee', 'store' => 'Test store', 'shop_id' => 123,
        'access_token' => 'private-test-token', 'refresh_token' => 'private-refresh-token', ...$store,
    ]);

    return AdsShopee::create([
        'marketplace_id' => $marketplace->id, 'campaign_id' => 11111, 'name' => 'Test campaign',
        'status' => 'paused', 'bidding_method' => 'auto', 'campaign_budget' => 12000,
        'roas_target' => 6.5, 'enhanced_cpc' => false, ...$attributes,
    ]);
}

/** Campaign shape from the sanitized response supplied by the user with this PRD. */
function settingsCampaign(array $attributes = []): array
{
    return array_replace_recursive([
        'campaign_id' => 11111,
        'common_info' => [
            'ad_type' => 'auto', 'ad_name' => 'Test campaign', 'campaign_status' => 'paused',
            'bidding_method' => 'auto', 'campaign_placement' => 'all', 'campaign_budget' => 12000,
            'campaign_duration' => ['start_time' => 1234567890, 'end_time' => 0], 'item_id_list' => [1],
        ],
        'auto_bidding_info' => ['roas_target' => 6.5],
    ], $attributes);
}

function settingsResponse(array $campaign = []): array
{
    return ['error' => '', 'response' => ['shop_id' => 123, 'region' => 'SG', 'campaign_list' => [settingsCampaign($campaign)]]];
}

function fakeSettings(array $campaign = [], mixed $editResponse = ['error' => ''], int $status = 200): void
{
    Http::preventStrayRequests();
    Http::fake([
        'partner.test/api/v2/ads/get_product_level_campaign_setting_info*' => Http::response(settingsResponse($campaign)),
        'partner.test/api/v2/ads/edit_manual_product_ads*' => Http::response($editResponse, $status),
    ]);
}

function settingsRoute(AdsShopee $ad, string $action = 'update', array $query = []): string
{
    return route('shopee.ads.settings.'.$action, ['marketplace' => $ad->marketplace_id, 'ad' => $ad->id, ...$query]);
}

test('guests cannot read or update settings', function () {
    $ad = settingsAd();
    fakeSettings();

    $this->get(settingsRoute($ad, 'edit'))->assertRedirect(route('login'));
    $this->patch(settingsRoute($ad), ['edit_action' => 'change_budget', 'budget' => 15000])->assertRedirect(route('login'));

    Http::assertNothingSent();
});

test('cross-store settings and pause requests return 404 without contacting Shopee', function () {
    $ad = settingsAd();
    $other = settingsAd(['campaign_id' => 22222]);
    fakeSettings();
    $this->actingAs(User::factory()->create());
    $parameters = ['marketplace' => $other->marketplace_id, 'ad' => $ad->id];

    $this->get(route('shopee.ads.settings.edit', $parameters))->assertNotFound();
    $this->patch(route('shopee.ads.settings.update', $parameters), ['edit_action' => 'change_budget', 'budget' => 15000])->assertNotFound();
    $this->post(route('shopee.ads.edit', $other->marketplace_id), ['campaign_id' => $ad->campaign_id, 'edit_action' => 'pause'])->assertNotFound();

    Http::assertNothingSent();
    expect($ad->fresh()->campaign_budget)->toEqual(12000);
});

test('each action sends only its setting and a backend UUID and persists only that setting', function (string $action, string $field, mixed $value, string $column) {
    $ad = settingsAd(['enhanced_cpc' => true]);
    fakeSettings();
    $before = $ad->fresh()->only(['campaign_budget', 'roas_target', 'enhanced_cpc', 'bidding_method', 'status']);

    $this->actingAs(User::factory()->create())->patch(settingsRoute($ad), [
        'edit_action' => $action, 'budget' => 15000, 'roas_target' => 8, 'enhanced_cpc' => false,
        $field => $value, 'campaign_id' => 99999, 'reference_id' => 'browser-id', 'bidding_method' => 'manual',
    ])->assertRedirect(settingsRoute($ad, 'edit'))->assertSessionHas('success');

    expect($ad->fresh()->only(array_keys($before)))->toEqual([...$before, $column => $value]);
    Http::assertSentCount(2);
    Http::assertSent(function ($request) use ($action, $field, $value) {
        if ($request->method() !== 'POST') {
            return false;
        }
        $body = $request->data();
        expect(array_keys($body))->toEqual(['campaign_id', 'reference_id', 'edit_action', $field]);
        expect(Str::isUuid($body['reference_id']))->toBeTrue();
        expect($body['campaign_id'])->toBe(11111);
        expect($body['edit_action'])->toBe($action);
        expect($body[$field])->toBe($value);

        return true;
    });
})->with([
    'budget' => ['change_budget', 'budget', 15000.0, 'campaign_budget'],
    'unlimited budget' => ['change_budget', 'budget', 0.0, 'campaign_budget'],
    'roas' => ['change_roas_target', 'roas_target', 8.0, 'roas_target'],
    'CPC false' => ['change_enhanced_cpc', 'enhanced_cpc', false, 'enhanced_cpc'],
    'CPC true' => ['change_enhanced_cpc', 'enhanced_cpc', true, 'enhanced_cpc'],
]);

test('invalid action and values fail validation without outbound requests', function (array $payload, string $field) {
    $ad = settingsAd();
    fakeSettings();

    $this->actingAs(User::factory()->create())->patch(settingsRoute($ad), $payload)->assertSessionHasErrors($field);

    Http::assertNothingSent();
    expect($ad->fresh()->campaign_budget)->toEqual(12000);
})->with([
    'missing action' => [[], 'edit_action'],
    'unknown action' => [['edit_action' => 'delete'], 'edit_action'],
    'missing budget' => [['edit_action' => 'change_budget'], 'budget'],
    'negative budget' => [['edit_action' => 'change_budget', 'budget' => -1], 'budget'],
    'infinite budget' => [['edit_action' => 'change_budget', 'budget' => '1e999'], 'budget'],
    'text budget' => [['edit_action' => 'change_budget', 'budget' => 'invalid'], 'budget'],
    'zero roas' => [['edit_action' => 'change_roas_target', 'roas_target' => 0], 'roas_target'],
    'missing roas' => [['edit_action' => 'change_roas_target'], 'roas_target'],
    'string false' => [['edit_action' => 'change_enhanced_cpc', 'enhanced_cpc' => 'false'], 'enhanced_cpc'],
    'missing CPC' => [['edit_action' => 'change_enhanced_cpc'], 'enhanced_cpc'],
    'null CPC' => [['edit_action' => 'change_enhanced_cpc', 'enhanced_cpc' => null], 'enhanced_cpc'],
]);

test('a non-Shopee store returns 404', function () {
    $ad = settingsAd([], ['marketplace' => 'Tokopedia']);
    fakeSettings();

    $this->actingAs(User::factory()->create())->patch(settingsRoute($ad), ['edit_action' => 'change_budget', 'budget' => 15000])->assertNotFound();

    Http::assertNothingSent();
});

test('missing credentials prevent editing', function (array $store) {
    $ad = settingsAd([], $store);
    fakeSettings();

    $this->actingAs(User::factory()->create())->patch(settingsRoute($ad), ['edit_action' => 'change_budget', 'budget' => 15000])->assertSessionHasErrors('settings');

    Http::assertNothingSent();
})->with([[['access_token' => null]], [['shop_id' => null]]]);

test('ROAS uses verified bidding mode and refuses manual despite browser and local auto', function () {
    $ad = settingsAd();
    fakeSettings(['common_info' => ['bidding_method' => 'manual']]);

    $this->actingAs(User::factory()->create())->patch(settingsRoute($ad), ['edit_action' => 'change_roas_target', 'roas_target' => 9, 'bidding_method' => 'auto'])
        ->assertSessionHasErrors(['roas_target' => 'ROAS Target hanya dapat diubah untuk auto bidding.']);

    Http::assertSentCount(1);
    expect($ad->fresh()->roas_target)->toEqual(6.5);
});

test('CPC trials on auto and manual reach Shopee and rejection preserves local data', function (string $mode) {
    $ad = settingsAd(['bidding_method' => $mode]);
    fakeSettings(['common_info' => ['bidding_method' => $mode]], ['error' => 'unsupported', 'message' => 'private-test-token']);
    $before = $ad->fresh()->getAttributes();

    $this->actingAs(User::factory()->create())->patch(settingsRoute($ad), ['edit_action' => 'change_enhanced_cpc', 'enhanced_cpc' => true])->assertSessionHasErrors('settings');

    expect($ad->fresh()->getAttributes())->toBe($before);
    expect(session('errors')->first('settings'))->not->toContain('private-test-token');
    Http::assertSent(fn ($request) => $request->method() === 'POST' && $request['enhanced_cpc'] === true);
})->with(['auto', 'manual']);

test('HTTP business and malformed edit responses never persist', function (mixed $body, int $status) {
    $ad = settingsAd();
    fakeSettings([], $body, $status);

    $this->actingAs(User::factory()->create())->patch(settingsRoute($ad), ['edit_action' => 'change_budget', 'budget' => 15000])->assertSessionHasErrors('settings');

    expect($ad->fresh()->campaign_budget)->toEqual(12000);
    Http::assertSentCount(2);
})->with([
    'HTTP failure' => [['error' => ''], 500],
    'business failure' => [['error' => 'invalid', 'message' => 'private-test-token'], 200],
    'empty body' => [[], 200],
    'invalid JSON' => ['not JSON', 200],
    'null error' => [['error' => null], 200],
]);

test('paused campaign detail reads only its campaign and hides credentials and store metrics', function () {
    $this->travelTo('2026-09-23 12:00:00');
    $ad = settingsAd();
    fakeSettings();
    MarketplaceAdDailyMetric::create(['marketplace_id' => $ad->marketplace_id, 'metric_date' => '2026-09-23', 'impressions' => 987]);

    $this->actingAs(User::factory()->create())->get(settingsRoute($ad, 'edit', ['page' => 3, 'status' => 'paused', 'redirect' => 'https://evil.test']))
        ->assertInertia(fn (Assert $page) => $page->component('Backoffice/Configuration/AdsShopeeEdit')
            ->where('settings.status', 'paused')->where('settings.enhanced_cpc', null)->where('ad.enhanced_cpc', false)
            ->where('listQuery', ['status' => 'paused', 'page' => '3'])
            ->missing('performance')->missing('daily')->missing('marketplace.access_token')->missing('marketplace.refresh_token'));

    Http::assertSentCount(1);
    Http::assertSent(fn ($request) => $request['campaign_id_list'] === '11111' && $request['info_type_list'] === '1,2,3,4');
});

test('detail persists real CPC booleans but missing null and string values stay unknown', function (array $campaign, ?bool $actual, bool $saved) {
    $ad = settingsAd(['enhanced_cpc' => true]);
    fakeSettings($campaign);

    $this->actingAs(User::factory()->create())->get(settingsRoute($ad, 'edit'))
        ->assertInertia(fn (Assert $page) => $page->where('settings.enhanced_cpc', $actual)->where('ad.enhanced_cpc', $saved));

    expect($ad->fresh()->enhanced_cpc)->toBe($saved);
    Http::assertSentCount(1);
})->with([
    'true' => [['manual_bidding_info' => ['enhanced_cpc' => true]], true, true],
    'false' => [['manual_bidding_info' => ['enhanced_cpc' => false]], false, false],
    'missing' => [[], null, true],
    'null' => [['manual_bidding_info' => ['enhanced_cpc' => null]], null, true],
    'string false' => [['manual_bidding_info' => ['enhanced_cpc' => 'false']], null, true],
]);

test('failed settings read shows local fallback and blocks mutations', function (mixed $body) {
    $ad = settingsAd();
    Http::preventStrayRequests();
    Http::fake(['partner.test/api/v2/ads/get_product_level_campaign_setting_info*' => Http::response($body)]);
    $this->actingAs(User::factory()->create());

    $this->get(settingsRoute($ad, 'edit'))->assertInertia(fn (Assert $page) => $page->where('settings', null)->where('settingsError', fn ($value) => str_contains($value, 'belum dapat dibaca'))->where('ad.campaign_budget', 12000));
    $this->patch(settingsRoute($ad), ['edit_action' => 'change_budget', 'budget' => 15000])->assertSessionHasErrors('settings');

    Http::assertSentCount(2);
    expect($ad->fresh()->campaign_budget)->toEqual(12000);
})->with(['invalid' => ['bad JSON'], 'empty' => [[]], 'missing campaign' => [['error' => '', 'response' => ['campaign_list' => []]]]]);

test('timeout is not retried and warns about an uncertain outcome', function () {
    $ad = settingsAd();
    Http::preventStrayRequests();
    Http::fake([
        'partner.test/api/v2/ads/get_product_level_campaign_setting_info*' => Http::response(settingsResponse()),
        'partner.test/api/v2/ads/edit_manual_product_ads*' => Http::failedConnection(),
    ]);

    $this->actingAs(User::factory()->create())->patch(settingsRoute($ad), ['edit_action' => 'change_budget', 'budget' => 15000])
        ->assertSessionHasErrors(['uncertain' => 'Hasil perubahan belum dapat dipastikan. Muat ulang pengaturan sebelum mencoba lagi.']);

    expect($ad->fresh()->campaign_budget)->toEqual(12000);
    Http::assertSentCount(2);
});

test('local persistence failure is reported as successful remote change with a local error', function () {
    $ad = settingsAd();
    fakeSettings();
    AdsShopee::updating(fn () => throw new RuntimeException('database fixture failure'));

    try {
        $this->actingAs(User::factory()->create())->patch(settingsRoute($ad), ['edit_action' => 'change_budget', 'budget' => 15000])
            ->assertSessionHas('success', 'Perubahan diterima Shopee.')
            ->assertSessionHas('error', 'Perubahan Shopee berhasil, tetapi penyimpanan lokal gagal. Sinkronisasi lokal perlu diulang.');
    } finally {
        AdsShopee::flushEventListeners();
    }

    expect($ad->fresh()->campaign_budget)->toEqual(12000);
    Http::assertSentCount(2);
});

test('accepted CPC with no readback stays locally saved and reports unknown actual status', function () {
    $ad = settingsAd();
    fakeSettings();

    $this->actingAs(User::factory()->create())->followingRedirects()->patch(settingsRoute($ad), ['edit_action' => 'change_enhanced_cpc', 'enhanced_cpc' => true])
        ->assertInertia(fn (Assert $page) => $page->where('feedback', 'Perubahan diterima; status aktual belum dapat dibaca.')->where('settings.enhanced_cpc', null)->where('ad.enhanced_cpc', true));

    expect($ad->fresh()->enhanced_cpc)->toBeTrue();
    Http::assertSentCount(3);
});

test('readback disagreement uses actual CPC and warns the user', function () {
    $ad = settingsAd();
    fakeSettings(['manual_bidding_info' => ['enhanced_cpc' => false]]);

    $this->actingAs(User::factory()->create())->followingRedirects()->patch(settingsRoute($ad), ['edit_action' => 'change_enhanced_cpc', 'enhanced_cpc' => true])
        ->assertInertia(fn (Assert $page) => $page->where('settings.enhanced_cpc', false)->where('feedback', fn ($value) => str_contains($value, 'berbeda')));

    expect($ad->fresh()->enhanced_cpc)->toBeFalse();
    Http::assertSentCount(3);
});

test('failed readback keeps successful persistence and separates the verification error', function () {
    $ad = settingsAd();
    Http::preventStrayRequests();
    Http::fake([
        'partner.test/api/v2/ads/get_product_level_campaign_setting_info*' => Http::sequence()->push(settingsResponse())->push([], 500),
        'partner.test/api/v2/ads/edit_manual_product_ads*' => Http::response(['error' => '']),
    ]);

    $this->actingAs(User::factory()->create())->followingRedirects()->patch(settingsRoute($ad), ['edit_action' => 'change_enhanced_cpc', 'enhanced_cpc' => true])
        ->assertInertia(fn (Assert $page) => $page->where('settings', null)->where('ad.enhanced_cpc', true)->where('feedback', 'Perubahan diterima; status aktual belum dapat dibaca.')->where('settingsError', fn ($value) => str_contains($value, 'belum dapat dibaca')));

    expect($ad->fresh()->enhanced_cpc)->toBeTrue();
    Http::assertSentCount(3);
});

test('mixed synchronization preserves missing CPC and reads false without dropping ongoing filtering', function () {
    $ad = settingsAd(['enhanced_cpc' => true]);
    $second = AdsShopee::create(['marketplace_id' => $ad->marketplace_id, 'campaign_id' => 22222, 'enhanced_cpc' => true]);
    $third = AdsShopee::create(['marketplace_id' => $ad->marketplace_id, 'campaign_id' => 33333, 'enhanced_cpc' => false]);
    Http::preventStrayRequests();
    Http::fake([
        'partner.test/api/v2/ads/get_product_level_campaign_id_list*' => Http::response(['response' => ['campaign_list' => [['campaign_id' => 11111], ['campaign_id' => 22222], ['campaign_id' => 33333]]]]),
        'partner.test/api/v2/ads/get_product_level_campaign_setting_info*' => Http::response(['error' => '', 'response' => ['campaign_list' => [
            settingsCampaign(['common_info' => ['campaign_status' => 'ongoing'], 'manual_bidding_info' => ['enhanced_cpc' => false]]),
            settingsCampaign(['campaign_id' => 22222, 'common_info' => ['campaign_status' => 'ongoing']]),
            settingsCampaign(['campaign_id' => 33333, 'common_info' => ['campaign_status' => 'ongoing'], 'manual_bidding_info' => ['enhanced_cpc' => true]]),
            settingsCampaign(['campaign_id' => 44444]),
        ]]]),
    ]);

    $this->actingAs(User::factory()->create())->get(route('shopee.ads', $ad->marketplace_id))->assertSessionHas('success');

    expect($ad->fresh()->enhanced_cpc)->toBeFalse();
    expect($second->fresh()->enhanced_cpc)->toBeTrue();
    expect($third->fresh()->enhanced_cpc)->toBeTrue();
    $this->assertDatabaseMissing('ads_shopees', ['campaign_id' => 44444]);
    Http::assertSentCount(2);
});

test('pause and resume return to detail with allowed filters and scoped persistence', function (string $action, string $status) {
    $ad = settingsAd();
    fakeSettings();

    $this->actingAs(User::factory()->create())->post(route('shopee.ads.edit', $ad->marketplace_id), [
        'campaign_id' => $ad->campaign_id, 'edit_action' => $action, 'return_to_detail' => true,
        'page' => 3, 'status' => 'paused', 'redirect' => 'https://evil.test',
    ])->assertRedirect(settingsRoute($ad, 'edit', ['status' => 'paused', 'page' => 3]));

    expect($ad->fresh()->status)->toBe($status);
    Http::assertSent(fn ($request) => array_keys($request->data()) === ['campaign_id', 'edit_action', 'reference_id']);
})->with([['pause', 'paused'], ['resume', 'ongoing']]);

test('failed read persistence keeps the last stored value separate from verified CPC', function () {
    $ad = settingsAd();
    fakeSettings(['manual_bidding_info' => ['enhanced_cpc' => true]]);
    AdsShopee::updating(fn () => throw new RuntimeException('fixture failure'));

    try {
        $this->actingAs(User::factory()->create())->get(settingsRoute($ad, 'edit'))
            ->assertInertia(fn (Assert $page) => $page->where('settings.enhanced_cpc', true)->where('ad.enhanced_cpc', false)
                ->where('settingsError', 'Pengaturan Shopee berhasil dibaca, tetapi sinkronisasi lokal gagal. Coba lagi.'));
    } finally {
        AdsShopee::flushEventListeners();
    }

    expect($ad->fresh()->enhanced_cpc)->toBeFalse();
    Http::assertSentCount(1);
});

test('the ads list retains filters pagination and safe store identity', function () {
    $ad = settingsAd();
    fakeSettings();

    $this->actingAs(User::factory()->create())->get(route('shopee.ads.index', ['campaign_name' => 'Test', 'status' => 'paused', 'sort' => 'campaign_budget', 'direction' => 'desc']))
        ->assertInertia(fn (Assert $page) => $page->component('Backoffice/Configuration/AdsShopee')
            ->where('ads.total', 1)->where('ads.current_page', 1)->where('ads.data.0.id', $ad->id)
            ->where('ads.data.0.marketplace.store', 'Test store')->missing('ads.data.0.marketplace.access_token')
            ->where('filters.campaign_name', 'Test')->where('filters.status', 'paused')->where('sort.direction', 'desc'));

    Http::assertNothingSent();
});

test('synchronization retains CPC for invalid values and leaves identical data untouched', function (mixed $value) {
    $ad = settingsAd(['enhanced_cpc' => true]);
    $campaign = settingsCampaign(['manual_bidding_info' => ['enhanced_cpc' => $value]]);

    AdsShopee::syncCampaigns($ad->marketplace, [$campaign]);
    $this->travel(1)->hours();
    $syncedAt = $ad->fresh()->updated_at;
    $changed = AdsShopee::syncCampaigns($ad->marketplace, [$campaign]);

    expect($changed)->toBeFalse();
    expect($ad->fresh()->enhanced_cpc)->toBeTrue();
    expect($ad->fresh()->updated_at->equalTo($syncedAt))->toBeTrue();
})->with(['null' => [null], 'string' => ['false']]);

test('missing partner credentials stop all settings requests', function () {
    $ad = settingsAd();
    fakeSettings();
    config()->set('services.shopee.partner_key', null);

    $this->actingAs(User::factory()->create())->patch(settingsRoute($ad), ['edit_action' => 'change_budget', 'budget' => 15000])
        ->assertSessionHasErrors('settings');

    Http::assertNothingSent();
});

test('missing enhanced CPC schema instructs the operator and prevents API mutations', function () {
    $ad = settingsAd();
    fakeSettings();
    Schema::shouldReceive('hasColumn')->with('ads_shopees', 'enhanced_cpc')->andReturn(false);

    $this->actingAs(User::factory()->create())->get(settingsRoute($ad, 'edit'))
        ->assertInertia(fn (Assert $page) => $page->where('settings', null)->where('settingsError', fn ($value) => str_contains($value, 'migration')));
    $this->patch(settingsRoute($ad), ['edit_action' => 'change_enhanced_cpc', 'enhanced_cpc' => true])->assertSessionHasErrors('settings');

    Http::assertNothingSent();
    expect($ad->fresh()->enhanced_cpc)->toBeFalse();
});

test('synchronization refuses to reassign an existing campaign to a different store', function () {
    $ad = settingsAd();
    $other = settingsAd(['campaign_id' => 22222]);

    expect(fn () => AdsShopee::syncCampaigns($other->marketplace, [settingsCampaign()]))
        ->toThrow(RuntimeException::class, 'Campaign tidak sesuai dengan toko.');

    expect($ad->fresh()->marketplace_id)->toBe($ad->marketplace_id);
});

test('failed campaign listing does not synchronize or leak upstream messages', function (mixed $body, int $status) {
    $ad = settingsAd(['enhanced_cpc' => true]);
    Http::preventStrayRequests();
    Http::fake(['partner.test/api/v2/ads/get_product_level_campaign_id_list*' => Http::response($body, $status)]);
    $before = $ad->fresh()->getAttributes();

    $this->actingAs(User::factory()->create())->get(route('shopee.ads', $ad->marketplace_id))
        ->assertSessionHas('error', 'Gagal menyinkronkan iklan Shopee. Periksa koneksi toko dan pastikan migration enhanced_cpc sudah dijalankan operator.');

    expect($ad->fresh()->getAttributes())->toBe($before);
    Http::assertSentCount(1);
})->with([
    'HTTP error' => [[], 500],
    'business error' => [['error' => 'invalid', 'message' => 'private-test-token'], 200],
    'malformed' => ['not JSON', 200],
]);

test('an empty campaign list skips settings requests and preserves existing ads', function () {
    $ad = settingsAd(['enhanced_cpc' => true]);
    Http::preventStrayRequests();
    Http::fake(['partner.test/api/v2/ads/get_product_level_campaign_id_list*' => Http::response(['error' => '', 'response' => ['campaign_list' => []]])]);

    $this->actingAs(User::factory()->create())->get(route('shopee.ads', $ad->marketplace_id))
        ->assertSessionHas('success', 'Semua data iklan sudah sesuai dengan sistem');

    expect($ad->fresh()->enhanced_cpc)->toBeTrue();
    Http::assertSentCount(1);
});

test('Shopee rejection messages reach the settings editor without saving changes', function (int $status) {
    $ad = settingsAd();
    fakeSettings([], ['error' => 'invalid_budget', 'message' => 'Budget must be greater than the spent amount.'], $status);

    $this->actingAs(User::factory()->create())->patch(settingsRoute($ad), ['edit_action' => 'change_budget', 'budget' => 15000])
        ->assertSessionHasErrors(['settings' => 'Shopee: Budget must be greater than the spent amount.']);

    expect($ad->fresh()->campaign_budget)->toEqual(12000);
    Http::assertSentCount(2);
})->with([200, 400]);

test('Shopee error messages redact credentials and signed URLs before reaching the browser', function () {
    $ad = settingsAd();
    fakeSettings([], ['error' => 'invalid', 'message' => 'Denied private-test-token fixture-key https://partner.test/api?sign=private-sign refresh_token=private-refresh-token']);

    $this->actingAs(User::factory()->create())->patch(settingsRoute($ad), ['edit_action' => 'change_enhanced_cpc', 'enhanced_cpc' => true])
        ->assertSessionHasErrors(['settings' => 'Shopee: Denied [disamarkan] [disamarkan] [URL disamarkan] refresh_token=[disamarkan]']);

    expect($ad->fresh()->enhanced_cpc)->toBeFalse();
    Http::assertSentCount(2);
});

test('unusable Shopee messages retain safe fallback feedback', function (mixed $message) {
    $ad = settingsAd();
    fakeSettings([], ['error' => 'invalid', 'message' => $message]);

    $this->actingAs(User::factory()->create())->patch(settingsRoute($ad), ['edit_action' => 'change_budget', 'budget' => 15000])
        ->assertSessionHasErrors(['settings' => 'Shopee menolak perubahan atau respons tidak valid. Nilai lokal tidak diubah. Muat ulang pengaturan sebelum mencoba lagi.']);

    expect($ad->fresh()->campaign_budget)->toEqual(12000);
    Http::assertSentCount(2);
})->with(['null' => [null], 'blank' => [' '], 'placeholder' => ['-'], 'array' => [['invalid']]]);

test('pause rejection displays the Shopee message and preserves status', function () {
    $ad = settingsAd(['status' => 'ongoing']);
    fakeSettings([], ['error' => 'invalid_status', 'message' => 'Campaign cannot be paused.']);

    $this->actingAs(User::factory()->create())->post(route('shopee.ads.edit', $ad->marketplace_id), [
        'campaign_id' => $ad->campaign_id, 'edit_action' => 'pause', 'return_to_detail' => true,
    ])->assertRedirect(settingsRoute($ad, 'edit'))->assertSessionHas('error', 'Shopee: Campaign cannot be paused.');

    expect($ad->fresh()->status)->toBe('ongoing');
    Http::assertSentCount(1);
});

test('detail preserves zero budget as the verified unlimited setting', function () {
    $ad = settingsAd(['campaign_budget' => 0]);
    fakeSettings(['common_info' => ['campaign_budget' => 0]]);

    $this->actingAs(User::factory()->create())->get(settingsRoute($ad, 'edit'))
        ->assertInertia(fn (Assert $page) => $page->where('settings.campaign_budget', 0)->where('ad.campaign_budget', 0));

    Http::assertSentCount(1);
});
