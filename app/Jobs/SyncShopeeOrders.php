<?php

namespace App\Jobs;

use App\Models\Marketplace;
use App\Models\Orders;
use App\Models\Product_sku;
use App\Services\Shopee\ShopeeServices;
use App\Services\Shopee\ShopeeSignature;
use DateTime;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class SyncShopeeOrders implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(public int $marketplaceId, public string $timeFrom, public string $timeTo) {}

    public function handle(ShopeeSignature $signature): void
    {
        $marketplace = Marketplace::findOrFail($this->marketplaceId);
        $shopee = new ShopeeServices($signature);
        $currentDate = new DateTime($this->timeFrom);
        $endDate = new DateTime($this->timeTo);

        while ($currentDate < $endDate) {
            $nextDate = (clone $currentDate)->modify('+1 day');
            if ($nextDate > $endDate) {
                $nextDate = $endDate;
            }

            $cursor = null;
            do {
                $response = $shopee->getOrder(
                    $marketplace->access_token,
                    (int) $marketplace->shop_id,
                    $currentDate->format('Y-m-d H:i:s'),
                    $nextDate->format('Y-m-d H:i:s'),
                    100,
                    $cursor
                );

                $orderList = $response['response'] ?? [];
                $orderSns = array_column($orderList['order_list'] ?? [], 'order_sn');

                if ($orderSns !== []) {
                    $details = $shopee->getOrderDetail(
                        $marketplace->access_token,
                        (int) $marketplace->shop_id,
                        implode(',', $orderSns)
                    );

                    foreach ($details['response']['order_list'] ?? [] as $orderData) {
                        $escrow = $shopee->getEscrowDetail($marketplace->access_token, (int) $marketplace->shop_id, $orderData['order_sn']);
                        $income = $escrow['response']['order_income'];
                        $payment = $escrow['response']['buyer_payment_info'];
                        $discount = max(0, abs($payment['shopee_voucher'] ?? 0) + abs($payment['seller_voucher'] ?? 0) + abs($payment['shopee_coins_redeemed'] ?? 0) - ($payment['shipping_fee'] ?? 0) - ($payment['buyer_service_fee'] ?? 0));
                        $fees = ($income['commission_fee'] ?? 0) + ($income['seller_order_processing_fee'] ?? 0) + ($income['service_fee'] ?? 0) + ($income['delivery_seller_protection_fee_premium_amount'] ?? 0) + ($income['voucher_from_seller'] ?? 0) + ($income['order_ams_commission_fee'] ?? 0);

                        $preparedOrder = [
                            'invoice' => $orderData['order_sn'],
                            'waybill' => $orderData['package_list'][0]['package_number'] ?? null,
                            'marketplace_id' => $marketplace->id,
                            'buyer_user_id' => (string) $orderData['buyer_user_id'],
                            'buyer_username' => $orderData['buyer_username'],
                            'buyer_phone' => $orderData['recipient_address']['phone'] ?? '',
                            'buyer_address' => $orderData['recipient_address']['full_address'] ?? '',
                            'courier' => $orderData['shipping_carrier'] ?? '',
                            'qty' => array_sum(array_column($orderData['item_list'], 'model_quantity_purchased')),
                            'discount' => $discount,
                            'total_price' => $orderData['total_amount'],
                            'status' => strtolower($orderData['order_status']),
                            'order_time' => date('Y-m-d H:i:s', $orderData['create_time']),
                            'payment_method' => $orderData['payment_method'] ?? null,
                            'notes' => $orderData['message_to_seller'] ?? null,
                            'income' => ($income['cost_of_goods_sold'] ?? 0) - $fees,
                        ];

                        $totalSale = array_sum(array_map(
                            fn (array $item): float => ($item['model_discounted_price'] ?? $item['model_original_price']) * $item['model_quantity_purchased'],
                            $orderData['item_list']
                        ));
                        $preparedItems = [];
                        foreach ($orderData['item_list'] as $item) {
                            $sku = Product_sku::where('product_model_id', $item['model_id'])->first();
                            $sale = $item['model_discounted_price'] ?? $item['model_original_price'];
                            $preparedItems[] = [
                                'product_id' => $sku->product_id ?? 0,
                                'product_origin_id' => $item['item_id'],
                                'product_model_id' => $item['model_id'],
                                'product_name' => $item['item_name'],
                                'qty' => $item['model_quantity_purchased'],
                                'price' => $item['model_original_price'],
                                'sale' => $sale,
                                'discount' => $item['model_original_price'] - $sale,
                                'income' => $totalSale > 0 ? $preparedOrder['income'] * ($sale * $item['model_quantity_purchased']) / $totalSale : 0,
                            ];
                        }

                        Orders::insertOrderFromShopee($preparedOrder, $preparedItems);
                    }
                }

                $cursor = ($orderList['more'] ?? false) ? $orderList['next_cursor'] : null;
            } while ($cursor);

            $currentDate = $nextDate;
        }
    }

    public function failed(Throwable $exception): void
    {
        Log::channel('shopee-order-sync')->error('Shopee order sync failed.', [
            'marketplace_id' => $this->marketplaceId,
            'time_from' => $this->timeFrom,
            'time_to' => $this->timeTo,
            'error_message' => $exception->getMessage(),
            'exception' => $exception,
        ]);
    }
}
