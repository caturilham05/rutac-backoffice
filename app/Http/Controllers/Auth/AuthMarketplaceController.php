<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Shopee\ShopeeSignature;
use App\Services\Shopee\ShopeeServices;
use App\Services\Shopee\ShopeeApiServices;
use Illuminate\Support\Facades\Log;


class AuthMarketplaceController extends Controller
{
    protected $signature;
    protected $partnerId;
    protected $partnerKey;
    protected  $host;

    public function __construct(ShopeeSignature $signature)
    {
        dd($signature);
        $this->signature  = $signature;
        $this->partnerId  = 2031431;
        $this->partnerKey = 'shpk43635878456c464b50494d7378756f58644c6d6570616d514875596d4d66';
        $this->host       = env('SHOPEE_HOST');
    }

    public function auth_shopee()
    {
        $path        = "/api/v2/shop/auth_partner";
        $redirectUrl = route('shopee.callback');
        $timest      = time();
        dd($path, $redirectUrl, $timest);
        // $sign        = $this->signature->make($this->partnerId, $this->partnerKey, $path, $timest);
        // $url         = sprintf("%s%s?timestamp=%s&partner_id=%s&sign=%s&redirect=%s", $this->host, $path, $timest, $this->partnerId, $sign, $redirectUrl ?? '');

        // return redirect()->away($url);
    }

    public function callback(Request $request)
    {
        dd($request);
    }
}
