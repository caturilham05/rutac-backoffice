<?php

namespace App\Http\Controllers;

use App\Models\Marketplace;
use App\Models\Product;
use App\Models\Product_sku;
use App\Services\Shopee\ShopeeServices;
use App\Services\Shopee\ShopeeSignature;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
                        'product_model_id' => $shopee['model_id'],
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
}
