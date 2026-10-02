<?php

namespace App\Http\Controllers;

use App\Http\Requests\ShopeeAdsRequest;
use App\Jobs\SyncShopeeOrders;
use App\Models\AdsShopee;
use App\Models\Marketplace;
use App\Models\Product;
use App\Models\Product_sku;
use App\Services\Shopee\ShopeeServices;
use App\Services\Shopee\ShopeeSignature;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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
        $access_token = $marketplace->access_token;
        $shop_id = $marketplace->shop_id;

        try {
            $shopee_services = new ShopeeServices($this->signature);

            $model_skus = [];

            $responses = $shopee_services->getProducts(
                $access_token,
                $shop_id,
                $request->offset ?? 0
            );

            foreach ($responses['response']['item_list'] as $item) {
                foreach ($item['item_model'] as $model) {

                    if (empty($model['model_sku'])) {
                        continue;
                    }

                    $sku = strtolower(trim($model['model_sku']));

                    $priceInfo = $model['price_info'][0] ?? [];

                    $model_skus[$sku] = [
                        'model_id' => $model['model_id'],
                        'item_id' => $item['item_id'],
                        'current_price' => $priceInfo['current_price'] ?? 0,
                        'original_price' => $priceInfo['original_price'] ?? 0,
                        'description' => $item['description'],
                    ];
                }
            }

            $skuNames = array_keys($model_skus);

            $products = Product::with([
                'variants',
                'skus' => function ($query) use ($skuNames) {
                    $query->whereIn('name', $skuNames);
                },
            ])
                ->whereHas('skus', function ($query) use ($skuNames) {
                    $query->whereIn('name', $skuNames);
                })
                ->get();

            $data = [];
            foreach ($products as $product) {
                $shopeeItemId = null;

                foreach ($product->skus as $sku) {
                    $shopee = $model_skus[strtolower($sku->name)] ?? null;

                    if (! $shopee) {
                        continue;
                    }

                    $shopeeItemId = $shopee['item_id'];

                    $data[] = [
                        'id' => $sku->id,
                        'product_id' => $sku->product_id,
                        'product_variant_id' => $sku->product_variant_id,
                        'name' => $sku->name,
                        'product_model_id' => (string) $shopee['model_id'],
                        'discount_price' => $shopee['current_price'],
                        'updated_at' => now(),
                    ];
                }

                if ($shopeeItemId) {
                    $product->update([
                        'product_origin_id' => (string) $shopeeItemId,
                        'description' => $shopee['description'] ?? $product->description,
                    ]);
                }
            }

            if (! empty($data)) {
                Product_sku::upsert(
                    $data,
                    ['id'], // kolom unik untuk mencocokkan record
                    ['product_model_id', 'discount_price', 'updated_at'] // kolom yang diupdate
                );
            }

        } catch (\Throwable $th) {
            dd($th->getMessage());
            $log = Log::build([
                'driver' => 'single',
                'path' => storage_path('logs/shopee.log'),
            ]);
            $log->error('Error in shopeeGetProducts: '.$th->getMessage());
        }
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
