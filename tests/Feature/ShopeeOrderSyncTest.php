<?php

use App\Jobs\SyncShopeeOrders;
use App\Models\Marketplace;
use App\Models\Orders;
use App\Models\User;
use App\Services\Shopee\ShopeeSignature;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;

test('order sync queues shopee order processing on redis shopee queue', function () {
    Queue::fake();
    $marketplace = Marketplace::create([
        'marketplace' => 'Shopee',
        'store' => 'Test store',
        'shop_id' => 123,
        'access_token' => 'test-token',
    ]);

    $this->actingAs(User::factory()->create())
        ->post(route('shopee.order.get', $marketplace), [
            'time_from' => '2026-10-01',
            'time_to' => '2026-10-02',
        ])
        ->assertRedirect(route('order.sync'))
        ->assertSessionHas('success');

    Queue::assertPushedOn('shopee', SyncShopeeOrders::class);
    expect(Queue::pushed(SyncShopeeOrders::class)[0]->connection)->toBe('redis');
});

test('order sync saves orders without a waybill when Shopee is still allocating tracking', function () {
    config([
        'services.shopee.host' => 'https://shopee.test',
        'services.shopee.partner_id' => 1,
        'services.shopee.partner_key' => 'test-key',
    ]);
    Http::fake([
        'shopee.test/api/v2/order/get_order_list*' => Http::response([
            'error' => '',
            'response' => ['order_list' => [['order_sn' => 'ORDER-1']], 'more' => false],
        ]),
        'shopee.test/api/v2/order/get_order_detail*' => Http::response([
            'error' => '',
            'response' => ['order_list' => [[
                'order_sn' => 'ORDER-1',
                'buyer_user_id' => 1,
                'buyer_username' => 'buyer',
                'item_list' => [['item_id' => 1, 'model_id' => 1, 'item_name' => 'Perfume', 'model_quantity_purchased' => 1, 'model_original_price' => 100, 'model_discounted_price' => 100]],
                'total_amount' => 100,
                'order_status' => 'READY_TO_SHIP',
                'create_time' => 1790899200,
            ]]],
        ]),
        'shopee.test/api/v2/payment/get_escrow_detail*' => Http::response([
            'error' => '',
            'response' => ['order_income' => ['cost_of_goods_sold' => 100], 'buyer_payment_info' => []],
        ]),
        'shopee.test/api/v2/logistics/get_tracking_number*' => Http::response([
            'error' => '物流信息暂不可用',
            'message' => 'The order is being allocated, please wait until the allocate is completed.',
        ]),
    ]);
    $marketplace = Marketplace::create([
        'marketplace' => 'Shopee',
        'store' => 'Test store',
        'shop_id' => 123,
        'access_token' => 'test-token',
    ]);

    (new SyncShopeeOrders($marketplace->id, '2026-10-01', '2026-10-02'))
        ->handle(app(ShopeeSignature::class));

    expect(Orders::where('marketplace_id', $marketplace->id)->where('invoice', 'ORDER-1')->first()->waybill)->toBeNull();
});
