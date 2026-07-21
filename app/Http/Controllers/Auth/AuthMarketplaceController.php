<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Marketplace;
use Illuminate\Http\Request;
use App\Services\Shopee\ShopeeSignature;
use App\Services\Shopee\ShopeeServices;
use Exception;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Illuminate\Support\Facades\Http;

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
        if (!$marketplace->marketplace_id) {
            return back()->with('error', 'marketplace id tidak ditemukan');
        }

        if (!$marketplace->app_key) {
            return back()->with('error', 'app key tidak ditemukan');
        }

        $path        = "/api/v2/shop/auth_partner";
        $redirectUrl = route('shopee.callback', ['id' => $marketplace->id]);
        $timest      = time();
        $sign        = $this->signature->make($marketplace->marketplace_id, $marketplace->app_key, $path, $timest);
        $url         = sprintf("%s%s?timestamp=%s&partner_id=%s&sign=%s&redirect=%s", $this->host, $path, $timest, $marketplace->marketplace_id, $sign, $redirectUrl ?? '');

        $response = Http::get($url);

        if ($response->failed()) {
            return back()->with('error', $response->json('message'));
        }

        return Inertia::location($url);
    }

    public function callback(Request $request, ShopeeServices $shopeeServices)
    {
        $code     = $request->get('code');
        $shopId   = $request->get('shop_id');
        $id       = $request->get('id');
        $response = $shopeeServices->getTokenShopLevel($code, $shopId, $request->get('id'));

        if (!empty($response['error'])) {
            return redirect()->route('marketplace')->with('error', $response['message']);
        }

        $response['expire_in_datetime'] = date('Y-m-d H:i:s', time() + $response['expire_in']);
        $marketplace = Marketplace::findOrFail($id);
        $data = [
            'id'                 => $marketplace->id,
            'marketplace'        => $marketplace->marketplace,
            'store'              => $marketplace->store,
            'shop_id'            => $shopId,
            'access_token'       => $response['access_token'],
            'refresh_token'      => $response['refresh_token'],
            'expire_in_datetime' => $response['expire_in_datetime'],
        ];

        try {
            Marketplace::marketplaceUpsert($data);
            return redirect()->route('marketplace')->with('success', sprintf('Selamat!! Tokomu [%s] terhubung dengan marketplace seller', $marketplace->store));
        } catch (\Throwable $th) {
            return redirect()->route('marketplace')->with('error', $th->getMessage());
        }
    }

    public function cancel_shopee(Request $request, Marketplace $marketplace)
    {
        $shop_id = $marketplace->findOrfail($request->id)->shop_id;
        dd($shop_id);
    }
}
