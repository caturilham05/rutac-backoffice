<?php

namespace App\Http\Controllers;

use App\Http\Requests\ShopeeAdsRequest;
use App\Models\AdsShopee;
use App\Models\Marketplace;
use App\Models\Orders;
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
        try {
            $shopee_services = new ShopeeServices(app(ShopeeSignature::class));
            $startDate = new \DateTime($request->time_from);
            $endDate = new \DateTime($request->time_to);
            $currentDate = clone $startDate;

            while ($currentDate < $endDate) {
                $nextDate = (clone $currentDate)->modify('+1 day');
                if ($nextDate > $endDate) {
                    $nextDate = $endDate;
                }

                $timeFrom = $currentDate->format('Y-m-d H:i:s');
                $timeTo = $nextDate->format('Y-m-d H:i:s');

                $cursor = null;
                do {
                    $response = $shopee_services->getOrder(
                        $marketplace->access_token,
                        $marketplace->shop_id,
                        $timeFrom,
                        $timeTo,
                        100,
                        $cursor
                    );

                    $order_list_response = $response['response'] ?? [];
                    $order_sn_list = array_column($order_list_response['order_list'] ?? [], 'order_sn');

                    if (! empty($order_sn_list)) {
                        $response_detail = $shopee_services->getOrderDetail(
                            $marketplace->access_token,
                            $marketplace->shop_id,
                            implode(',', $order_sn_list)
                        );

                        foreach ($response_detail['response']['order_list'] ?? [] as $order_data) {
                            $escrow = $shopee_services->getEscrowDetail($marketplace->access_token, $marketplace->shop_id, $order_data['order_sn']);
                            $escrow_resp = $escrow['response'];
                            $income_data = $escrow_resp['order_income'];
                            $payment_info = $escrow_resp['buyer_payment_info'];

                            $discount = abs($payment_info['shopee_voucher'] ?? 0) + abs($payment_info['seller_voucher'] ?? 0) + abs($payment_info['shopee_coins_redeemed'] ?? 0) - ($payment_info['shipping_fee'] ?? 0) - ($payment_info['buyer_service_fee'] ?? 0);
                            if ($discount < 0) {
                                $discount = 0;
                            }
                            $total_fees = ($income_data['commission_fee'] ?? 0) + ($income_data['seller_order_processing_fee'] ?? 0) + ($income_data['service_fee'] ?? 0) + ($income_data['delivery_seller_protection_fee_premium_amount'] ?? 0);

                            $preparedOrder = [
                                'invoice' => $order_data['order_sn'],
                                'waybill' => $order_data['package_list'][0]['package_number'] ?? null,
                                'marketplace_id' => $marketplace->id,
                                'buyer_user_id' => (string) $order_data['buyer_user_id'],
                                'buyer_username' => $order_data['buyer_username'],
                                'buyer_phone' => $order_data['recipient_address']['phone'] ?? '',
                                'buyer_address' => $order_data['recipient_address']['full_address'] ?? '',
                                'courier' => $order_data['shipping_carrier'] ?? '',
                                'qty' => array_sum(array_column($order_data['item_list'], 'model_quantity_purchased')),
                                'discount' => $discount,
                                'total_price' => $order_data['total_amount'],
                                'status' => strtolower($order_data['order_status']),
                                'order_time' => date('Y-m-d H:i:s', $order_data['create_time']),
                                'payment_method' => $order_data['payment_method'] ?? null,
                                'notes' => $order_data['message_to_seller'] ?? null,
                                'income' => ($income_data['cost_of_goods_sold'] ?? 0) - $total_fees,
                            ];

                            $preparedItems = [];
                            foreach ($order_data['item_list'] as $item) {
                                $product_sku = Product_sku::where('product_model_id', $item['model_id'])->first();
                                $preparedItems[] = [
                                    'product_id' => $product_sku->product_id ?? 0,
                                    'product_origin_id' => $item['item_id'],
                                    'product_model_id' => $item['model_id'],
                                    'product_name' => $item['item_name'],
                                    'qty' => $item['model_quantity_purchased'],
                                    'price' => $item['model_original_price'],
                                    'sale' => $item['model_discounted_price'],
                                    'discount' => $item['model_original_price'] - $item['model_discounted_price'],
                                ];
                            }
                            Orders::insertOrderFromShopee($preparedOrder, $preparedItems);
                        }
                    }

                    $cursor = ($order_list_response['more'] ?? false) ? $order_list_response['next_cursor'] : null;
                } while ($cursor);

                $currentDate = $nextDate;
            }

            return redirect()->route('order.sync')->with('success', sprintf('Berhasil menyinkronkan pesanan Shopee secara bertahap pada periode %s - %s', $request->time_from, $request->time_to));
        } catch (\Throwable $th) {
            return redirect()->route('order.sync')->with('error', $th->getMessage());
        }
    }
}
