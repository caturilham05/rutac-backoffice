<?php

namespace App\Http\Controllers;

use App\Http\Requests\ShopeeAdsRequest;
use App\Models\AdsShopee;
use App\Models\Marketplace;
use App\Models\Product;
use App\Models\Product_sku;
use App\Services\Shopee\ShopeeServices;
use App\Services\Shopee\ShopeeSignature;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;

class ShopeeController extends Controller
{
    protected $signature;
    protected $host;

    public function __construct(ShopeeSignature $signature)
    {
        $this->signature = $signature;
        $this->host      = env('SHOPEE_HOST');
    }

    public function shopeeGetProducts(Marketplace $marketplace)
    {
        $access_token   = $marketplace->access_token;
        $shop_id        = $marketplace->shop_id;
        $marketplace_id = $marketplace->marketplace_id;
        $app_key        = $marketplace->app_key;

        try {
            $shopee_services = new ShopeeServices($this->signature);

            $model_skus = [];

            $responses = $shopee_services->getProducts(
                $access_token,
                $app_key,
                $marketplace_id,
                $shop_id
            );

            foreach ($responses['response']['item_list'] as $item) {
                foreach ($item['item_model'] as $model) {

                    if (empty($model['model_sku'])) {
                        continue;
                    }

                    $sku = strtolower(trim($model['model_sku']));

                    $priceInfo = $model['price_info'][0] ?? [];

                    $model_skus[$sku] = [
                        'model_id'       => $model['model_id'],
                        'current_price'  => $priceInfo['current_price'] ?? 0,
                        'original_price' => $priceInfo['original_price'] ?? 0,
                    ];
                }
            }

            $skuNames = array_keys($model_skus);

            $products = Product::with([
                'variants',
                'skus' => function ($query) use ($skuNames) {
                    $query->whereIn('name', $skuNames);
                }
            ])
            ->whereHas('skus', function ($query) use ($skuNames) {
                $query->whereIn('name', $skuNames);
            })
            ->get();

            $data = [];
            foreach ($products as $product) {
                foreach ($product->skus as $sku) {
                    $shopee = $model_skus[strtolower($sku->name)] ?? null;

                    if (!$shopee) {
                        continue;
                    }

                    $data[] = [
                        'id'               => $sku->id,
                        'product_model_id' => (string)$shopee['model_id'],
                        'discount_price'   => $shopee['current_price'],
                        'original_price'   => $shopee['original_price'],
                        'updated_at'       => now()
                    ];
                }
            }
            Product_sku::upsert(
                $data,
                ['id'], // kolom unik untuk mencocokkan record
                ['product_model_id', 'discount_price', 'original_price', 'updated_at'] // kolom yang diupdate
            );


        } catch (\Throwable $th) {
            dd($th->getMessage());
            $log = Log::build([
                'driver' => 'single',
                'path'   => storage_path('logs/shopee.log'),
            ]);
            $log->error("Error in shopeeGetProducts: " . $th->getMessage());
        }
    }

    public function shopeeAds(Marketplace $marketplace)
    {
        $access_token   = $marketplace->access_token;
        $shop_id        = $marketplace->shop_id;
        $marketplace_id = $marketplace->marketplace_id;
        $app_key        = $marketplace->app_key;

        try {
            $shopee_services = new ShopeeServices($this->signature);
            $ads             = $shopee_services->getProductLevelCampaignSettingInfo($access_token, $app_key, $marketplace_id, $shop_id);
            $campaignIds = array_column($ads, 'campaign_id');
            $existingAds = AdsShopee::whereIn('campaign_id', $campaignIds)->get()->keyBy('campaign_id');

            $data = [];
            foreach ($ads as $item) {
                $campaignId = $item['campaign_id'];
                $startTime  = !empty($item['common_info']['campaign_duration']['start_time']) ? date('Y-m-d H:i:s', $item['common_info']['campaign_duration']['start_time']) : null;
                $endTime    = !empty($item['common_info']['campaign_duration']['end_time']) ? date('Y-m-d H:i:s', $item['common_info']['campaign_duration']['end_time']) : null;

                $newData = [
                    'marketplace_id'     => $marketplace->id,
                    'campaign_id'        => $campaignId,
                    'type'               => $item['common_info']['ad_type'],
                    'name'               => $item['common_info']['ad_name'],
                    'status'             => $item['common_info']['campaign_status'],
                    'bidding_method'     => $item['common_info']['bidding_method'],
                    'campaign_placement' => $item['common_info']['campaign_placement'],
                    'campaign_budget'    => $item['common_info']['campaign_budget'],
                    'start_time'         => $startTime,
                    'end_time'           => $endTime,
                    'item_id'            => $item['common_info']['item_id_list'][0],
                    'roas_target'        => $item['auto_bidding_info']['roas_target'],
                ];

                $existing = $existingAds->get($campaignId);

                $isChanged = !$existing ||
                    $existing->type !== $newData['type'] ||
                    $existing->name !== $newData['name'] ||
                    $existing->status !== $newData['status'] ||
                    $existing->bidding_method !== $newData['bidding_method'] ||
                    $existing->campaign_placement !== $newData['campaign_placement'] ||
                    $existing->campaign_budget != $newData['campaign_budget'] ||
                    $existing->start_time !== $newData['start_time'] ||
                    $existing->end_time !== $newData['end_time'] ||
                    $existing->item_id != $newData['item_id'] ||
                    $existing->roas_target != $newData['roas_target'];

                if ($isChanged) {
                    $newData['updated_at'] = now();
                    if (!$existing) {
                        $newData['created_at'] = now();
                    }
                    $data[] = $newData;
                }
            }

            if (!empty($data)) {
                AdsShopee::adsUpsert($data);
                return redirect()->route('shopee.ads.index')->with('success', 'Berhasil menyinkronkan iklan Shopee');
            }

            return redirect()->route('shopee.ads.index')->with('success', 'Semua data iklan sudah sesuai dengan sistem');
        } catch (\Throwable $th) {
            return redirect()->route('shopee.ads.index')->with('error', 'Gagal menyinkronkan iklan Shopee: ' . $th->getMessage());
        }
    }

    public function shopeeAdsEdit(ShopeeAdsRequest $request, Marketplace $marketplace): RedirectResponse
    {
        $data = $request->validated();

        $adsData = AdsShopee::where('campaign_id', $data['campaign_id'])->first();
        if (!$adsData) {
            return redirect()->route('shopee.ads.index')->with('error', sprintf('campaign id [%s] tidak terdaftar', $data['campaign_id']));
        }

        $isPause = $data['edit_action'] === 'pause';
        $status  = $isPause ? 'paused' : 'ongoing';
        $msg     = sprintf('Iklan [%s] berhasil %s', $adsData->name, $isPause ? 'dijeda' : 'diaktifkan');

        try {
            $shopee_services      = new ShopeeServices($this->signature);
            $data['reference_id'] = Str::uuid()->toString();

            $shopee_services->editManualProductAds(
                $marketplace->access_token,
                $marketplace->app_key,
                $marketplace->marketplace_id,
                $marketplace->shop_id,
                $data
            );

            AdsShopee::adsUpsert([
                ['campaign_id' => $data['campaign_id'], 'status' => $status]
            ]);

            return redirect()->route('shopee.ads.index')->with('success', $msg);
        } catch (\Throwable $th) {
            return redirect()->route('shopee.ads.index')->with('error', $th->getMessage());
        }
    }

    public function orderSync(Marketplace $marketplace, Request $request)
    {
        try {
            $shopee_services = new ShopeeServices(app(ShopeeSignature::class));
            $cursor          = $request->cursor ?? null;
            $page_size       = $request->page_size ?? 100;

            $response = $shopee_services->getOrder(
                $marketplace->access_token,
                $marketplace->app_key,
                $marketplace->marketplace_id,
                $marketplace->shop_id,
                $request->time_from,
                $request->time_to,
                $page_size,
                $cursor
            );

            dd($response);

            // return inertia('Backoffice/Orders/OrderSync', [
            //     'data' => $all_orders
            // ]);
        } catch (\Throwable $th) {
            return redirect()->route('order.sync')->with('error', $th->getMessage());
            // return response()->json(['error' => $th->getMessage()], 400);
        }
    }
}
