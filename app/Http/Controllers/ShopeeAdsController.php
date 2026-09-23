<?php

namespace App\Http\Controllers;

use App\Http\Requests\ShopeeAdsIndexRequest;
use App\Http\Requests\ShopeeAdsSettingsRequest;
use App\Http\Requests\SyncShopeeAdsDailyMetricsRequest;
use App\Jobs\ShopeeAdsActionJob;
use App\Models\AdsShopee;
use App\Models\Marketplace;
use App\Models\MarketplaceAdDailyMetric;
use App\Services\Shopee\ShopeeServices;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class ShopeeAdsController extends Controller
{
    public function edit(ShopeeAdsSettingsRequest $request, Marketplace $marketplace, AdsShopee $ad, ShopeeServices $shopee): Response
    {
        $result = $ad->settingsForDisplay($marketplace, $shopee);
        $accepted = $request->session()->get('ads_setting');
        $feedback = null;
        if (($accepted['ad_id'] ?? null) === $ad->id) {
            $actual = $result['settings'][$accepted['field']] ?? null;
            $feedback = $actual === null
                ? 'Perubahan diterima; status aktual belum dapat dibaca.'
                : ($actual != $accepted['value'] ? 'Perubahan diterima; nilai aktual dari Shopee berbeda dari permintaan. Nilai aktual ditampilkan.' : 'Perubahan berhasil dan pengaturan telah dibaca ulang.');
        }

        return Inertia::render('Backoffice/Configuration/AdsShopeeEdit', [
            'ad' => $ad->only(['id', 'campaign_id', 'name', 'status', 'bidding_method', 'campaign_budget', 'roas_target', 'enhanced_cpc', 'start_time', 'end_time']),
            'marketplace' => $marketplace->only(['id', 'store']),
            'settings' => $result['settings'],
            'settingsError' => $result['error'],
            'feedback' => $feedback,
            'listQuery' => $request->listQuery(),
        ]);
    }

    public function update(ShopeeAdsSettingsRequest $request, Marketplace $marketplace, AdsShopee $ad, ShopeeServices $shopee): RedirectResponse
    {
        $result = $ad->changeSetting($marketplace, $shopee, $request->validated());

        return redirect()->route('shopee.ads.settings.edit', [
            'marketplace' => $marketplace->id, 'ad' => $ad->id, ...$request->listQuery(),
        ])->with('success', $result['message'])
            ->with('error', $result['error'])
            ->with('ads_setting', ['ad_id' => $ad->id, 'field' => $result['field'], 'value' => $result['value']]);
    }

    public function index(ShopeeAdsIndexRequest $request): Response
    {
        $validated = $request->validated();
        $endDate = $validated['end_date'] ?? now()->toDateString();
        $startDate = $validated['start_date'] ?? now()->subDays(6)->toDateString();

        $ads = AdsShopee::getAdsShopeePaginated(
            100,
            $validated['campaign_name'] ?? null,
            $validated['status'] ?? null,
            $validated['sort'] ?? null,
            $validated['direction'] ?? 'asc'
        );

        $campaigns = AdsShopee::select('name')->distinct()->get()->map(function ($item) {
            return ['value' => $item->name, 'label' => $item->name];
        });

        $marketplaces = Marketplace::where('marketplace', 'Shopee')->get()->map(function ($item) {
            return ['id' => $item->id, 'store' => $item->store];
        });

        $selectedMarketplace = $marketplaces->firstWhere('id', (int) ($validated['marketplace_id'] ?? 0))
            ?? $marketplaces->first();
        $marketplace = $selectedMarketplace ? Marketplace::find($selectedMarketplace['id']) : null;

        return Inertia::render('Backoffice/Configuration/AdsShopee', [
            'ads' => $ads,
            'filters' => [
                'campaign_name' => $validated['campaign_name'] ?? '',
                'status' => $validated['status'] ?? '',
                'marketplace_id' => $marketplace?->getKey(),
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            'sort' => ['sort' => $validated['sort'] ?? null, 'direction' => $validated['direction'] ?? 'asc'],
            'campaigns' => $campaigns,
            'marketplaces' => $marketplaces,
            'daily' => [
                'marketplace_id' => $marketplace?->getKey(),
                'start_date' => $startDate,
                'end_date' => $endDate,
                'metrics' => $marketplace
                    ? MarketplaceAdDailyMetric::chartData($marketplace, $startDate, $endDate)
                    : [],
            ],
        ]);
    }

    public function bulkAction(string $action): RedirectResponse
    {
        $count = 0;

        foreach (AdsShopee::whereIn('status', ['ongoing', 'paused'])
            ->whereHas('marketplace', fn ($query) => $query->where('marketplace', 'Shopee'))
            ->lazyById() as $ad) {
            ShopeeAdsActionJob::dispatch($ad, $action)->onQueue('shopee')->delay(now()->addSeconds($count));
            $count++;
        }

        return back()->with('success', $count === 0
            ? 'Tidak ada iklan yang dapat diproses.'
            : "Permintaan {$action} untuk {$count} iklan di semua toko telah masuk antrean. Muat ulang halaman setelah proses selesai.");
    }

    public function syncDailyMetrics(
        SyncShopeeAdsDailyMetricsRequest $request,
        Marketplace $marketplace,
        ShopeeServices $shopee,
    ): RedirectResponse {
        $dates = $request->validated();

        try {
            $response = $shopee->getAllCpcAdsDailyPerformance(
                $marketplace->access_token,
                (int) $marketplace->shop_id,
                CarbonImmutable::parse($dates['start_date'])->format('d-m-Y'),
                CarbonImmutable::parse($dates['end_date'])->format('d-m-Y'),
            );

            MarketplaceAdDailyMetric::syncFromShopee($marketplace, $response['response'] ?? []);
        } catch (Throwable $exception) {
            $log = Log::build([
                'driver' => 'single',
                'path' => storage_path('logs/shopee.log'),
            ]);
            $log->warning('Shopee daily metrics synchronization failed.', ['marketplace_id' => $marketplace->id]);

            return redirect()->route('shopee.ads.index', [
                'marketplace_id' => $marketplace->getKey(),
                ...$dates,
            ])->with('error', 'Sinkronisasi Ads Daily gagal. Silakan coba lagi.');
        }

        return redirect()->route('shopee.ads.index', [
            'marketplace_id' => $marketplace->getKey(),
            ...$dates,
        ])->with('success', 'Ads Daily Performance berhasil disinkronkan.');
    }
}
