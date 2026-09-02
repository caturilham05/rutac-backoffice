<?php

namespace App\Services\Shopee;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ShopeeServices
{
    protected $signature;

    protected $host;

    protected $partnerId;

    protected $partnerKey;

    protected $time;

    public function __construct(ShopeeSignature $signature)
    {
        $this->signature = $signature;
        $this->host = config('services.shopee.host');
        $this->partnerId = (int) config('services.shopee.partner_id');
        $this->partnerKey = config('services.shopee.partner_key');
        $this->time = time();
    }

    public function getTokenShopLevel(?string $code, int $shopId)
    {
        try {
            $timestamp = $this->time;
            $path = '/api/v2/auth/token/get';
            $bodyArr = [
                'code' => $code,
                'shop_id' => intval($shopId),
                'partner_id' => intval($this->partnerId),
            ];

            $bodyJson = json_encode($bodyArr);
            $sign = $this->signature->make($this->partnerId, $this->partnerKey, $path, $timestamp);
            $url = "{$this->host}{$path}"."?partner_id={$this->partnerId}&timestamp={$timestamp}&sign={$sign}";

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->withBody($bodyJson, 'application/json')->post($url);

            return $response->json();
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()]);
        }
    }

    public function getAccessTokenShopLevel(int $shop_id, string $refres_token)
    {
        $path = '/api/v2/auth/access_token/get';
        $timestamp = $this->time;
        $sign = $this->signature->make($this->partnerId, $this->partnerKey, $path, $timestamp);
        $url = sprintf('%s%s?partner_id=%s&timestamp=%s&sign=%s', $this->host, $path, $this->partnerId, $timestamp, $sign);
        $body = [
            'shop_id' => $shop_id,
            'partner_id' => $this->partnerId,
            'refresh_token' => $refres_token,
        ];

        $body_json = json_encode($body);

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->withBody($body_json, 'application/json')->post($url);

        return $response->json();
    }

    public function getDiscountList(string $accessToken, int $shopId, string $discountStatus = 'ongoing', int $pageNo = 1, int $pageSize = 100)
    {
        $path = '/api/v2/discount/get_discount_list';
        $sign = hash_hmac('sha256', $this->partnerId.$path.$this->time.$accessToken.$shopId, $this->partnerKey);

        $response = Http::get($this->host.$path, [
            'partner_id' => $this->partnerId,
            'timestamp' => $this->time,
            'sign' => $sign,
            'access_token' => $accessToken,
            'shop_id' => $shopId,
            'discount_status' => $discountStatus,
            'page_no' => $pageNo,
            'page_size' => $pageSize,
        ])->json();

        if (! empty($response['error'])) {
            throw new \Exception($response['message']);
        }

        return $response;
    }

    public function getDiscount(string $accessToken, int $shopId, int $discountId): array
    {
        $path = '/api/v2/discount/get_discount';
        $sign = hash_hmac('sha256', $this->partnerId.$path.$this->time.$accessToken.$shopId, $this->partnerKey);

        $response = Http::connectTimeout(3)->timeout(10)->get($this->host.$path, [
            'partner_id' => $this->partnerId,
            'timestamp' => $this->time,
            'sign' => $sign,
            'access_token' => $accessToken,
            'shop_id' => $shopId,
            'discount_id' => $discountId,
        ])->throw()->json();

        if (! empty($response['error'])) {
            throw new \Exception($response['message']);
        }

        return $response;
    }

    public function updateDiscountItems(string $accessToken, int $shopId, int $discountId, array $items): array
    {
        $path = '/api/v2/discount/update_discount_item';
        $sign = hash_hmac('sha256', $this->partnerId.$path.$this->time.$accessToken.$shopId, $this->partnerKey);
        $url = $this->host.$path.'?'.http_build_query([
            'partner_id' => $this->partnerId,
            'timestamp' => $this->time,
            'sign' => $sign,
            'access_token' => $accessToken,
            'shop_id' => $shopId,
        ]);

        $response = Http::connectTimeout(3)->timeout(10)->post($url, [
            'discount_id' => $discountId,
            'item_list' => $items,
        ])->throw()->json();

        if (! empty($response['error'])) {
            throw new \Exception($response['message']);
        }

        if (! empty($response['response']['error_list'])) {
            throw new \Exception(collect($response['response']['error_list'])->pluck('fail_message')->implode(', '));
        }

        return $response;
    }

    public function getProducts(string $accessToken, int $shopId, int $offset = 0, int $pageSize = 50)
    {
        $timestamp = $this->time;
        $path = '/api/v2/product/get_item_list';
        $baseString = $this->partnerId.$path.$timestamp.$accessToken.$shopId;
        $sign = hash_hmac('sha256', $baseString, $this->partnerKey);
        $url = sprintf('%s%s?partner_id=%s&timestamp=%s&sign=%s&access_token=%s&shop_id=%s&item_status=NORMAL&page_size=%s&offset=%s',
            $this->host,
            $path,
            $this->partnerId,
            $timestamp,
            $sign,
            $accessToken,
            $shopId,
            $pageSize,
            $offset
        );

        $response_items = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->get($url)->json();

        if (! empty($response_items['error'])) {
            throw new \Exception($response_items['message'], 400);
        }

        if (empty($response_items['response']['item'])) {
            throw new \Exception('Data not found', 400);
        }

        $item_id_list = array_column($response_items['response']['item'], 'item_id');
        $item_id_list = implode(',', $item_id_list);
        $item_id_list_encode = urlencode('['.$item_id_list.']');

        $path = '/api/v2/product/get_item_base_info';
        $baseString = $this->partnerId.$path.$timestamp.$accessToken.$shopId;
        $sign = hash_hmac('sha256', $baseString, $this->partnerKey);
        $url = sprintf('%s%s?partner_id=%s&timestamp=%s&sign=%s&access_token=%s&shop_id=%s&item_id_list=%s',
            $this->host,
            $path,
            $this->partnerId,
            $timestamp,
            $sign,
            $accessToken,
            $shopId,
            $item_id_list
        );

        $response_item_info = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->get($url)->json();

        if (! empty($response_item_info['error'])) {
            throw new \Exception($response_item_info['message'], 400);
        }

        $pathModel = '/api/v2/product/get_model_list';
        $baseStringModel = $this->partnerId.$pathModel.$timestamp.$accessToken.$shopId;
        $signModel = hash_hmac('sha256', $baseStringModel, $this->partnerKey);

        $http = Http::withHeaders(['Content-Type' => 'application/json']);
        foreach ($response_item_info['response']['item_list'] as &$value) {
            $urlModel = sprintf('%s%s?partner_id=%s&timestamp=%s&sign=%s&access_token=%s&shop_id=%s&item_id=%s',
                $this->host,
                $pathModel,
                $this->partnerId,
                $timestamp,
                $signModel,
                $accessToken,
                $shopId,
                $value['item_id']
            );
            $response_model = $http->get($urlModel)->json();

            if (! empty($response_model['error'])) {
                continue;
            }

            $value['item_model'] = $response_model['response']['model'];
        }

        return $response_item_info;
    }

    private function getProductLevelCampaignIdList(string $accessToken, int $shopId)
    {
        $timestamp = $this->time;
        $path = '/api/v2/ads/get_product_level_campaign_id_list';
        $baseString = $this->partnerId.$path.$timestamp.$accessToken.$shopId;
        $sign = hash_hmac('sha256', $baseString, $this->partnerKey);
        $url = sprintf('%s%s?partner_id=%s&timestamp=%s&sign=%s&access_token=%s&shop_id=%s',
            $this->host,
            $path,
            $this->partnerId,
            $timestamp,
            $sign,
            $accessToken,
            $shopId
        );

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->get($url)->json();

        return $response;
    }

    public function getProductLevelCampaignSettingInfo(string $accessToken, int $shopId)
    {
        $ads_list = $this->getProductLevelCampaignIdList($accessToken, $shopId);
        if (! empty($ads_list['error'])) {
            throw new \Exception($ads_list['message']);
        }
        $campaign_ids = array_column($ads_list['response']['campaign_list'], 'campaign_id');

        $path = '/api/v2/ads/get_product_level_campaign_setting_info';
        $baseString = $this->partnerId.$path.$this->time.$accessToken.$shopId;
        $sign = hash_hmac('sha256', $baseString, $this->partnerKey);
        $url = sprintf('%s%s?partner_id=%s&timestamp=%s&sign=%s&access_token=%s&shop_id=%s&info_type_list=1,2,3,4&campaign_id_list=%s',
            $this->host,
            $path,
            $this->partnerId,
            $this->time,
            $sign,
            $accessToken,
            $shopId,
            implode(',', $campaign_ids)
        );

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->get($url)->json();

        if (! empty($response['error'])) {
            throw new \Exception($response['message']);
        }

        $campaign_list = $response['response']['campaign_list'];
        $campaign_ongoing = array_values(array_filter($campaign_list, function ($value) {
            return $value['common_info']['campaign_status'] === 'ongoing';
        }));

        return $campaign_ongoing;
    }

    public function editManualProductAds(string $accessToken, int $shop_id, array $data)
    {
        $path = '/api/v2/ads/edit_manual_product_ads';
        $baseString = $this->partnerId.$path.$this->time.$accessToken.$shop_id;
        $sign = hash_hmac('sha256', $baseString, $this->partnerKey);
        $url = sprintf('%s%s?partner_id=%s&timestamp=%s&sign=%s&access_token=%s&shop_id=%s',
            $this->host,
            $path,
            $this->partnerId,
            $this->time,
            $sign,
            $accessToken,
            $shop_id
        );

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->withBody(json_encode($data), 'application/json')->post($url)->json();

        if (! empty($response['error'])) {
            Log::build([
                'driver' => 'single',
                'path' => storage_path('logs/shopee-services.log'),
            ])->error('Shopee API Error [editManualProductAds]: '.json_encode($response));
            throw new \Exception($response['message']);
        }

        return $response;
    }

    public function getOrder(string $accessToken, int $shop_id, string $time_from, string $time_to, $page_size = 100, ?string $cursor = null)
    {
        $path = '/api/v2/order/get_order_list';
        $baseString = $this->partnerId.$path.$this->time.$accessToken.$shop_id;
        $sign = hash_hmac('sha256', $baseString, $this->partnerKey);
        $url = sprintf('%s%s?partner_id=%s&timestamp=%s&sign=%s&access_token=%s&shop_id=%s&time_range_field=create_time&time_from=%s&time_to=%s&page_size=%s',
            $this->host,
            $path,
            $this->partnerId,
            $this->time,
            $sign,
            $accessToken,
            $shop_id,
            strtotime($time_from),
            strtotime($time_to),
            $page_size
        );

        if ($cursor) {
            $url .= '&cursor='.$cursor;
        }

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->get($url)->json();

        if (! empty($response['error'])) {
            throw new \Exception($response['message']);
        }

        return $response;
    }

    public function getOrderDetail(string $accessToken, int $shop_id, string $order_sn)
    {
        $path = '/api/v2/order/get_order_detail';
        $baseString = $this->partnerId.$path.$this->time.$accessToken.$shop_id;
        $sign = hash_hmac('sha256', $baseString, $this->partnerKey);
        $url = sprintf('%s%s?partner_id=%s&timestamp=%s&sign=%s&access_token=%s&shop_id=%s&order_sn_list=%s&response_optional_fields=buyer_user_id,buyer_username,estimated_shipping_fee,recipient_address,actual_shipping_fee ,goods_to_declare,note,note_update_time,item_list,pay_time,dropshipper, dropshipper_phone,split_up,buyer_cancel_reason,cancel_by,cancel_reason,actual_shipping_fee_confirmed,buyer_cpf_id,fulfillment_flag,pickup_done_time,package_list,shipping_carrier,payment_method,total_amount,buyer_username,invoice_data,order_chargeable_weight_gram,return_request_due_date,edt,payment_info',
            $this->host,
            $path,
            $this->partnerId,
            $this->time,
            $sign,
            $accessToken,
            $shop_id,
            $order_sn
        );

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->get($url)->json();

        if (! empty($response['error'])) {
            throw new \Exception($response['message']);
        }

        return $response;
    }

    public function getEscrowDetail(string $accessToken, int $shop_id, string $order_sn)
    {
        // diskon pembeli -> shopee_voucher + seller_voucher - shipping_fee - buyer_service_fee
        // biaya penjual -> commision_fee = biaya admin + seller_order_processing_fee = biaya proses pesanan + service_fee = biaya layanan (gratis ongkir extra + biaya layanan) + delivery_seller_protection_fee_premium_amount = premi + voucher_from_seller = voucher penjual
        $path = '/api/v2/payment/get_escrow_detail';
        $baseString = $this->partnerId.$path.$this->time.$accessToken.$shop_id;
        $sign = hash_hmac('sha256', $baseString, $this->partnerKey);
        $url = sprintf('%s%s?partner_id=%s&timestamp=%s&sign=%s&access_token=%s&shop_id=%s&order_sn=%s',
            $this->host,
            $path,
            $this->partnerId,
            $this->time,
            $sign,
            $accessToken,
            $shop_id,
            $order_sn
        );

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->get($url)->json();

        if (! empty($response['error'])) {
            throw new \Exception($response['message']);
        }

        return $response;
    }
}
