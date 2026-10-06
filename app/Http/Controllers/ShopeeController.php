<?php

namespace App\Http\Controllers;

use App\Http\Requests\ShopeeAdsRequest;
use App\Jobs\SyncShopeeOrders;
use App\Jobs\SyncShopeeProducts;
use App\Models\AdsShopee;
use App\Models\Marketplace;
use App\Services\Shopee\ShopeeServices;
use App\Services\Shopee\ShopeeSignature;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ShopeeController extends Controller
{
    protected $signature;

    public function __construct(ShopeeSignature $signature)
    {
        $this->signature = $signature;
    }

    public function shopeeGetProducts(Marketplace $marketplace, Request $request)
    {
        $validated = $request->validate([
            'offset' => ['sometimes', 'integer', 'min:0'],
        ]);

        SyncShopeeProducts::dispatch($marketplace->id, (int) ($validated['offset'] ?? 0))
            ->onConnection('redis')
            ->onQueue('shopee');

        return back()->with('success', 'Sinkronisasi produk Shopee sudah masuk antrean.');
    }

    public function shopeeAds(Marketplace $marketplace)
    {
        $access_token = $marketplace->access_token;
        $shop_id = $marketplace->shop_id;

        try {
            $shopee_services = new ShopeeServices($this->signature);
            $ads = $shopee_services->getProductLevelCampaignSettingInfo($access_token, $shop_id);
            $changed = AdsShopee::syncCampaigns($marketplace, $ads);

            return redirect()->route('shopee.ads.index')->with('success', $changed
                ? 'Berhasil menyinkronkan iklan Shopee'
                : 'Semua data iklan sudah sesuai dengan sistem');
        } catch (\Throwable $th) {
            return redirect()->route('shopee.ads.index')->with('error', 'Gagal menyinkronkan iklan Shopee. Periksa koneksi toko dan pastikan migration enhanced_cpc sudah dijalankan operator.');
        }
    }

    public function shopeeAdsEdit(ShopeeAdsRequest $request, Marketplace $marketplace): RedirectResponse
    {
        $data = $request->validated();

        $adsData = AdsShopee::forCampaign($marketplace, (int) $data['campaign_id']);
        $redirect = $request->boolean('return_to_detail')
            ? redirect()->route('shopee.ads.settings.edit', ['marketplace' => $marketplace->id, 'ad' => $adsData->id, ...$request->listQuery()])
            : redirect()->route('shopee.ads.index');
        $data = $request->safe()->only(['campaign_id', 'edit_action']);

        $isPause = $data['edit_action'] === 'pause';
        $status = $isPause ? 'paused' : 'ongoing';
        $msg = sprintf('Iklan [%s] berhasil %s', $adsData->name, $isPause ? 'dijeda' : 'diaktifkan');

        try {
            $shopee_services = new ShopeeServices($this->signature);
            $data['reference_id'] = Str::uuid()->toString();

            $shopee_services->editManualProductAds(
                $marketplace->access_token,
                $marketplace->shop_id,
                $data
            );

            $adsData->saveStatus($status);

            return $redirect->with('success', $msg);
        } catch (\DomainException $exception) {
            return $redirect->with('error', $exception->getMessage());
        } catch (\Throwable $th) {
            return $redirect->with('error', 'Hasil perubahan status belum dapat dipastikan. Muat ulang pengaturan sebelum mencoba lagi.');
        }
    }

    public function orderSync(Marketplace $marketplace, Request $request)
    {
        $validated = $request->validate([
            'time_from' => ['required', 'date'],
            'time_to' => ['required', 'date', 'after_or_equal:time_from'],
        ]);

        SyncShopeeOrders::dispatch($marketplace->id, $validated['time_from'], $validated['time_to'])
            ->onConnection('redis')
            ->onQueue('shopee');

        return redirect()->route('order.sync')->with('success', 'Sinkronisasi pesanan Shopee sudah masuk antrean.');
    }
}
