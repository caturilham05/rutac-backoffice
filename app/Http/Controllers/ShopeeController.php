<?php

namespace App\Http\Controllers;

use App\Models\Marketplace;
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
            $response        = $shopee_services->getProducts($access_token, $app_key, $marketplace_id, $shop_id);
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
