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
    protected $time;

    public function __construct(ShopeeSignature $signature)
    {
        $this->signature = $signature;
        $this->host      = env('SHOPEE_HOST');
        $this->time      = time();
    }

    public function getTokenShopLevel(?string $code, int $shopId, int $id)
    {
        try {
            $timestamp   = $this->time;
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
        $timestamp = $this->time;
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

    public function getProducts(string $accessToken, string $app_key, int $marketplace_id, int $shopId, int $offset = 0, int $pageSize = 10)
    {
        $timestamp  = $this->time;
        $path       = "/api/v2/product/get_item_list";
        $baseString = $marketplace_id.$path.$timestamp.$accessToken.$shopId;
        $sign       = hash_hmac('sha256', $baseString, $app_key);
        $url        = sprintf('%s%s?partner_id=%s&timestamp=%s&sign=%s&access_token=%s&shop_id=%s&item_status=NORMAL&page_size=%s&offset=%s',
            $this->host,
            $path,
            $marketplace_id,
            $timestamp,
            $sign,
            $accessToken,
            $shopId,
            $pageSize,
            $offset
        );

        $response_items = Http::withHeaders([
            "Content-Type" => "application/json"
        ])->get($url)->json();

        if (!empty($response_items['error'])) {
            throw new \Exception($response_items['message'], 400);
        }

        if (empty($response_items['response']['item'])) {
            throw new \Exception('Data not found', 400);
        }

        $item_id_list = array_column($response_items['response']['item'], 'item_id');
        $item_id_list = implode(',', $item_id_list);
        $item_id_list_encode = urlencode('['.$item_id_list.']');

        $path       = '/api/v2/product/get_item_base_info';
        $baseString = $marketplace_id.$path.$timestamp.$accessToken.$shopId;
        $sign       = hash_hmac('sha256', $baseString, $app_key);
        $url        = sprintf('%s%s?partner_id=%s&timestamp=%s&sign=%s&access_token=%s&shop_id=%s&item_id_list=%s',
            $this->host,
            $path,
            $marketplace_id,
            $timestamp,
            $sign,
            $accessToken,
            $shopId,
            $item_id_list
        );

        $response_item_info = Http::withHeaders([
            "Content-Type" => "application/json"
        ])->get($url)->json();

        if (!empty($response_item_info['error'])) {
            throw new \Exception($response_item_info['message'], 400);
        }

        $pathModel       = '/api/v2/product/get_model_list';
        $baseStringModel = $marketplace_id.$pathModel.$timestamp.$accessToken.$shopId;
        $signModel       = hash_hmac('sha256', $baseStringModel, $app_key);

        $http = Http::withHeaders(["Content-Type" => "application/json"]);
        foreach ($response_item_info['response']['item_list'] as &$value) {
            $urlModel = sprintf('%s%s?partner_id=%s&timestamp=%s&sign=%s&access_token=%s&shop_id=%s&item_id=%s',
                $this->host,
                $pathModel,
                $marketplace_id,
                $timestamp,
                $signModel,
                $accessToken,
                $shopId,
                $value['item_id']
            );
            $response_model = $http->get($urlModel)->json();

            if (!empty($response_model['error'])) {
                continue;
            }

            $value['item_model'] = $response_model['response']['model'];
        }

        return $response_item_info;
    }

    private function getProductLevelCampaignIdList(string $accessToken, string $app_key, int $marketplace_id, int $shopId)
    {
        $timestamp  = $this->time;
        $path       = "/api/v2/ads/get_product_level_campaign_id_list";
        $baseString = $marketplace_id.$path.$timestamp.$accessToken.$shopId;
        $sign       = hash_hmac('sha256', $baseString, $app_key);
        $url        = sprintf('%s%s?partner_id=%s&timestamp=%s&sign=%s&access_token=%s&shop_id=%s',
            $this->host,
            $path,
            $marketplace_id,
            $timestamp,
            $sign,
            $accessToken,
            $shopId
        );

        $response = Http::withHeaders([
            "Content-Type" => "application/json"
        ])->get($url)->json();

        return $response;
    }

    public function getProductLevelCampaignSettingInfo(string $accessToken, string $app_key, int $marketplace_id, int $shopId)
    {
        $ads_list = $this->getProductLevelCampaignIdList($accessToken, $app_key, $marketplace_id, $shopId);
        if (!empty($ads_list['error'])) {
            throw new \Exception($ads_list['message']);
        }
        $campaign_ids = array_column($ads_list['response']['campaign_list'], 'campaign_id');

        $path       = "/api/v2/ads/get_product_level_campaign_setting_info";
        $baseString = $marketplace_id.$path.$this->time.$accessToken.$shopId;
        $sign       = hash_hmac('sha256', $baseString, $app_key);
        $url        = sprintf('%s%s?partner_id=%s&timestamp=%s&sign=%s&access_token=%s&shop_id=%s&info_type_list=1,2,3,4&campaign_id_list=%s',
            $this->host,
            $path,
            $marketplace_id,
            $this->time,
            $sign,
            $accessToken,
            $shopId,
            implode(',', $campaign_ids)
        );

        $response = Http::withHeaders([
            "Content-Type" => "application/json"
        ])->get($url)->json();

        if (!empty($response['error'])) {
            throw new \Exception($response['message']);
        }

        $campaign_list = $response['response']['campaign_list'];
        $campaign_ongoing = array_values(array_filter($campaign_list, function ($value) {
            return $value['common_info']['campaign_status'] === 'ongoing';
        }));

        return $campaign_ongoing;
    }

    public function editManualProductAds(string $accessToken, string $app_key, int $marketplace_id, int $shop_id, array $data)
    {
    }
}
