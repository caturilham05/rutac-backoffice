<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Marketplace;
use Illuminate\Http\Request;
use App\Services\Shopee\ShopeeSignature;
use App\Services\Shopee\ShopeeServices;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class AuthMarketplaceController extends Controller
{
    protected $signature;
    // protected $partnerId;
    // protected $partnerKey;
    protected  $host;

    public function __construct(ShopeeSignature $signature)
    {
        $this->signature = $signature;
        $this->host      = env('SHOPEE_HOST');
        // $this->partnerId  = 0;
        // $this->partnerKey = null;
    }

    public function auth_shopee(Request $request)
    {
        $marketplace = Marketplace::findOrFail($request->id);
        $path        = "/api/v2/shop/auth_partner";
        $redirectUrl = route('shopee.callback', ['id' => $marketplace->id]);
        $timest      = time();
        $sign        = $this->signature->make($marketplace->marketplace_id, $marketplace->app_key, $path, $timest);
        $url         = sprintf("%s%s?timestamp=%s&partner_id=%s&sign=%s&redirect=%s", $this->host, $path, $timest, $marketplace->marketplace_id, $sign, $redirectUrl ?? '');

        return Inertia::location($url);
    }

    public function callback(Request $request, ShopeeServices $shopeeServices)
    {
        $code     = $request->get('code');
        $shopId   = $request->get('shop_id');
        try {
            $response = $shopeeServices->getTokenShopLevel($code, $shopId, $request->get('id'));
            if (!empty($response['error'])) {
                throw new \Exception($response['error']);
            }

            dd($response);
        } catch (\Throwable $th) {
            dd($th);
        }
    }
}
