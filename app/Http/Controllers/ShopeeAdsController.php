<?php

namespace App\Http\Controllers;

use App\Http\Requests\ShopeeAdsIndexRequest;
use App\Http\Requests\SyncShopeeAdsDailyMetricsRequest;
use App\Models\AdsShopee;
use App\Models\Marketplace;
use App\Models\MarketplaceAdDailyMetric;
use App\Services\Shopee\ShopeeServices;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class ShopeeAdsController extends Controller
{
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
                'path'   => storage_path('logs/shopee.log'),
            ]);
            $log->info($exception->getMessage());

            return redirect()->route('shopee.ads.index', [
                'marketplace_id' => $marketplace->getKey(),
                ...$dates,
            ])->with('error', $exception->getMessage());
        }

        return redirect()->route('shopee.ads.index', [
            'marketplace_id' => $marketplace->getKey(),
            ...$dates,
        ])->with('success', 'Ads Daily Performance berhasil disinkronkan.');
    }
}
