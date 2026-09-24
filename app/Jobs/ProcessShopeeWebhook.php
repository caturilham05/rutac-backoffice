<?php

namespace App\Jobs;

use App\Models\Marketplace;
use App\Models\Orders;
use App\Models\Product;
use App\Services\Shopee\ShopeeServices;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use RuntimeException;

class ProcessShopeeWebhook implements ShouldQueue
{
    use Queueable;

    public int $tries = 5;

    public array $backoff = [30, 60, 120, 300];

    public function __construct(public array $payload) {}

    public function middleware(): array
    {
        return [(new WithoutOverlapping('shopee-order:'.$this->payload['shop_id'].':'.$this->payload['data']['ordersn']))->releaseAfter(30)->expireAfter(120)];
    }

    public function handle(ShopeeServices $shopee): void
    {
        $marketplace = Marketplace::where('marketplace', 'shopee')->where('shop_id', $this->payload['shop_id'])->firstOrFail();
        $invoice = $this->payload['data']['ordersn'];
        $response = $shopee->getOrderDetail($marketplace->access_token, (int) $marketplace->shop_id, $invoice);
        $detail = collect($response['response']['order_list'] ?? [])->firstWhere('order_sn', $invoice);

        if (empty($detail['item_list']) || empty($detail['order_status'])) {
            throw new RuntimeException('Shopee returned incomplete order details for '.$invoice);
        }

        $products = Product::with('skus')->whereIn('product_origin_id', array_column($detail['item_list'], 'item_id'))->get()->keyBy('product_origin_id');
        $items = [];
        foreach ($detail['item_list'] as $item) {
            $product = $products->get($item['item_id']);
            $sku = $product?->skus->firstWhere('product_model_id', $item['model_id']);
            $sale = $item['model_discounted_price'] ?? $item['model_original_price'];
            $items[] = [
                'product_id' => $sku?->product_id ?? $product?->id ?? 0,
                'product_origin_id' => $item['item_id'],
                'product_model_id' => $item['model_id'],
                'product_name' => $item['item_name'],
                'qty' => $item['model_quantity_purchased'],
                'price' => $item['model_original_price'],
                'sale' => $sale,
                'discount' => $item['model_original_price'] - $sale,
            ];
        }

        $data = [
            'invoice' => $invoice,
            'marketplace_id' => $marketplace->id,
            'buyer_user_id' => (string) ($detail['buyer_user_id'] ?? ''),
            'buyer_username' => $detail['buyer_username'] ?? null,
            'buyer_phone' => $detail['recipient_address']['phone'] ?? '',
            'buyer_address' => $detail['recipient_address']['full_address'] ?? '',
            'courier' => $detail['shipping_carrier'] ?? '',
            'qty' => array_sum(array_column($items, 'qty')),
            'total_price' => $detail['total_amount'] ?? 0,
            'status' => strtolower($detail['order_status']),
            'order_time' => date('Y-m-d H:i:s', $detail['create_time']),
            'payment_method' => $detail['payment_method'] ?? null,
            'notes' => $detail['message_to_seller'] ?? null,
        ];

        if ((int) $this->payload['code'] === 4) {
            $data['waybill'] = $this->payload['data']['tracking_no'];
        }

        $escrow = $shopee->getEscrowDetail($marketplace->access_token, (int) $marketplace->shop_id, $invoice);
        $income = $escrow['response']['order_income'] ?? null;
        $payment = $escrow['response']['buyer_payment_info'] ?? null;
        if (! is_array($income) || ! is_array($payment)) {
            throw new RuntimeException('Shopee returned incomplete escrow details for '.$invoice);
        }
        $data['discount'] = max(0, abs($payment['shopee_voucher'] ?? 0) + abs($payment['seller_voucher'] ?? 0) + abs($payment['shopee_coins_redeemed'] ?? 0) - ($payment['shipping_fee'] ?? 0) - ($payment['buyer_service_fee'] ?? 0));
        $data['income'] = ($income['cost_of_goods_sold'] ?? 0) - ($income['commission_fee'] ?? 0) - ($income['seller_order_processing_fee'] ?? 0) - ($income['service_fee'] ?? 0) - ($income['delivery_seller_protection_fee_premium_amount'] ?? 0);

        Orders::insertOrderFromShopee($data, $items);
    }
}
