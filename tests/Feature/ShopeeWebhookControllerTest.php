<?php

use App\Models\Marketplace;
use App\Models\Orders;

test('order status push updates only the order from the matching shop', function () {
    $marketplace = Marketplace::create(['marketplace' => 'Shopee', 'shop_id' => 727720655]);
    $otherMarketplace = Marketplace::create(['marketplace' => 'Shopee', 'shop_id' => 123]);
    $order = Orders::create(orderAttributes($marketplace->id));
    $otherOrder = Orders::create(orderAttributes($otherMarketplace->id));

    $this->postJson(route('shopee.webhook'), orderStatusPush())
        ->assertOk()
        ->assertExactJson(['message' => 'success']);

    expect($order->fresh()->status)->toBe('processed')
        ->and($otherOrder->fresh()->status)->toBe('pending');
});

test('invalid push code is rejected without changing the order', function () {
    $marketplace = Marketplace::create(['marketplace' => 'Shopee', 'shop_id' => 727720655]);
    $order = Orders::create(orderAttributes($marketplace->id));

    $this->postJson(route('shopee.webhook'), orderStatusPush(['code' => 4]))
        ->assertUnprocessable()
        ->assertJsonValidationErrors('code');

    expect($order->fresh()->status)->toBe('pending');
});

function orderAttributes(int $marketplaceId): array
{
    return [
        'invoice' => '220810QSK8S7BX',
        'marketplace_id' => $marketplaceId,
        'buyer_user_id' => '1',
        'buyer_phone' => '',
        'buyer_address' => '',
        'courier' => '',
        'status' => 'pending',
    ];
}

function orderStatusPush(array $attributes = []): array
{
    return array_replace_recursive([
        'data' => [
            'items' => [],
            'ordersn' => '220810QSK8S7BX',
            'status' => 'PROCESSED',
            'completed_scenario' => '',
            'update_time' => 1660123127,
        ],
        'shop_id' => 727720655,
        'code' => 3,
        'timestamp' => 1660123127,
    ], $attributes);
}
