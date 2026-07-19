<?php

namespace App\Services\Shopee;

use App\Models\Marketplace;
use Illuminate\Support\Facades\Http;

Class ShopeeServices
{
    protected $signature;
    // protected $partnerId;
    // protected $partnerKey;
    protected $host;

    public function __construct(ShopeeSignature $signature)
    {
        $this->signature = $signature;
        $this->host      = env('SHOPEE_HOST');
        // throw new \Exception('Not implemented');
    }

    public function getTokenShopLevel(?string $code, int $shopId, int $id)
    {
        try {
            $timestamp   = time();
            $path        = "/api/v2/auth/token/get";
            $marketplace = Marketplace::findOrFail($id);

            $bodyArr = [
                "code"       => $code,
                "shop_id"    => intval($shopId),
                "partner_id" => intval($marketplace->marketplace_id),
            ];

            $bodyJson = json_encode($bodyArr);
            $sign     = $this->signature->make($marketplace->marketplace_id, $marketplace->app_key, $path, $timestamp);
            $url      = "{$this->host}{$path}"."?partner_id={$marketplace->marketplace_id}&timestamp={$timestamp}&sign={$sign}";

            $response = Http::withHeaders([
                'Content-Type' => 'application/json'
            ])->withBody($bodyJson, 'application/json')->post($url);

            return $response->json();
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()]);
        }
    }

    public function getAccessTokenShopLevel(int $marketplace_id, int $shop_id, string $app_key, string $refres_token)
    {
        $path = "/api/v2/auth/access_token/get";
        $timestamp = time();
        $sign      = $this->signature->make($marketplace_id, $app_key, $path, $timestamp);
        $url       = sprintf("%s%s?partner_id=%s&timestamp=%s&sign=%s", $this->host, $path, $marketplace_id, $timestamp, $sign);
        $body = [
            'shop_id'       => $shop_id,
            'partner_id'    => $marketplace_id,
            'refresh_token' => $refres_token
        ];

        $body_json = json_encode($body);

        $response = Http::withHeaders([
            'Content-Type' => 'application/json'
        ])->withBody($body_json, 'application/json')->post($url);

        return $response->json();
    }
}
