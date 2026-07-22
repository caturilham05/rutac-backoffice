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
            // $products        = Product::with(['skus', 'variants'])->get();
            // $productFilter   = $products->filter(function($product){
            //     return $product->cat_name !== 'Material';
            // });

            $model_skus = [];
            $responses = $shopee_services->getProducts($access_token, $app_key, $marketplace_id, $shop_id);
            foreach ($responses['response']['item_list'] as $item) {
                $model_skus = array_merge($model_skus, array_filter(array_column($item['item_model'], 'model_sku')));
            }

            $products = Product::with([
                'variants',
                'skus' => function ($query) use ($model_skus) {
                    $query->whereIn('name', $model_skus);
                }
            ])
            ->whereHas('skus', function ($query) use ($model_skus) {
                $query->whereIn('name', $model_skus);
            })
            ->get()->toArray();

            dd($model_skus, $products);


            // $model_sku_data = [];
            // foreach ($responses['response']['item_list'] as $response) {
            //     foreach ($response['item_model'] as $item_model) {
            //         if (!empty($item_model['model_sku'])) {
            //             $model_sku                  = strtolower($item_model['model_sku']);
            //             $model_sku_data[$model_sku] = $item_model['model_id'];
            //         }
            //     }
            // }

            // $model_sku_data = [];
            // foreach ($productFilter as $product) {
            //     foreach ($product->skus as $sku) {
            //         if (!empty($sku->name)) {
            //             $model_sku_data[$sku->name] = $sku->id;
            //         }
            //     }
            // }

            // dd($model_sku_data);
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
