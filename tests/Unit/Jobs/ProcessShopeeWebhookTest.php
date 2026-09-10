<?php

use App\Jobs\ProcessShopeeWebhook;
use App\Models\Marketplace;
use App\Models\Product;
use App\Models\Product_sku;
use App\Services\Shopee\ShopeeServices;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    config()->set('services.shopee', [
        'host' => 'https://partner.test', 'partner_id' => 123, 'partner_key' => 'test-key',
    ]);
});

test('both pushes persist order details without duplicate products or losing tracking', function (int $code) {
    Http::preventStrayRequests();
    $marketplace = Marketplace::create(['marketplace' => 'shopee', 'shop_id' => 123, 'access_token' => 'token']);
    Http::fake(['partner.test/api/v2/order/get_order_detail*' => Http::response([
        'response' => ['order_list' => [shopeeWebhookOrderDetail()]],
    ])]);
    $payload = ['code' => $code, 'shop_id' => 123, 'data' => ['ordersn' => 'ORDER123', 'tracking_no' => 'RESI123']];

    (new ProcessShopeeWebhook($payload))->handle(app(ShopeeServices::class));
    (new ProcessShopeeWebhook($payload))->handle(app(ShopeeServices::class));
    (new ProcessShopeeWebhook(array_replace($payload, ['code' => 3])))->handle(app(ShopeeServices::class));

    $this->assertDatabaseCount('orders', 1);
    $this->assertDatabaseCount('order_products', 2);
    $this->assertDatabaseHas('orders', [
        'invoice' => 'ORDER123', 'marketplace_id' => $marketplace->id,
        'waybill' => $code === 4 ? 'RESI123' : null,
        'buyer_user_id' => '456', 'buyer_username' => 'buyer', 'buyer_phone' => '08123',
        'buyer_address' => 'Jakarta', 'courier' => 'J&T', 'qty' => 3,
        'total_price' => 27000, 'status' => 'shipped', 'payment_method' => 'COD', 'notes' => 'Please pack safely',
    ]);
    $this->assertDatabaseHas('order_products', [
        'product_origin_id' => 100, 'product_model_id' => 0, 'product_id' => 0,
        'product_name' => 'Perfume A', 'qty' => 2, 'price' => 10000, 'sale' => 9000, 'discount' => 1000,
    ]);
    $this->assertDatabaseHas('order_products', ['product_origin_id' => 200, 'product_model_id' => 0, 'qty' => 1]);
})->with([3, 4]);

test('failed or incomplete order detail leaves no partial order and can be retried', function (array $body, int $status) {
    Http::preventStrayRequests();
    Marketplace::create(['marketplace' => 'shopee', 'shop_id' => 123, 'access_token' => 'token']);
    Http::fake(['partner.test/api/v2/order/get_order_detail*' => Http::response($body, $status)]);
    $job = new ProcessShopeeWebhook(['code' => 3, 'shop_id' => 123, 'data' => ['ordersn' => 'ORDER123']]);

    expect(fn () => $job->handle(app(ShopeeServices::class)))->toThrow(Exception::class);

    $this->assertDatabaseCount('orders', 0);
    $this->assertDatabaseCount('order_products', 0);
})->with([
    'http failure' => [[], 503],
    'api failure' => [['error' => 'error_auth', 'message' => 'Expired token'], 200],
    'missing order' => [['response' => ['order_list' => []]], 200],
]);

test('completed order stores escrow discount and net income', function () {
    Http::preventStrayRequests();
    Marketplace::create(['marketplace' => 'shopee', 'shop_id' => 123, 'access_token' => 'token']);
    Http::fake([
        'partner.test/api/v2/order/get_order_detail*' => Http::response([
            'response' => ['order_list' => [array_replace(shopeeWebhookOrderDetail(), ['order_status' => 'COMPLETED'])]],
        ]),
        'partner.test/api/v2/payment/get_escrow_detail*' => Http::response(['response' => [
            'order_income' => ['cost_of_goods_sold' => 27000, 'commission_fee' => 1000, 'service_fee' => 500],
            'buyer_payment_info' => ['shopee_voucher' => 2000, 'seller_voucher' => 1000, 'shipping_fee' => 1000],
        ]]),
    ]);

    (new ProcessShopeeWebhook(['code' => 3, 'shop_id' => 123, 'data' => ['ordersn' => 'ORDER123']]))
        ->handle(app(ShopeeServices::class));

    $this->assertDatabaseHas('orders', ['invoice' => 'ORDER123', 'status' => 'completed', 'discount' => 2000, 'income' => 25500]);
});

function shopeeWebhookOrderDetail(): array
{
    return [
        'order_sn' => 'ORDER123', 'order_status' => 'SHIPPED', 'create_time' => 1660123127,
        'buyer_user_id' => 456, 'buyer_username' => 'buyer',
        'recipient_address' => ['phone' => '08123', 'full_address' => 'Jakarta'],
        'shipping_carrier' => 'J&T', 'total_amount' => 27000, 'payment_method' => 'COD',
        'message_to_seller' => 'Please pack safely', 'package_list' => [['package_number' => 'NOT-A-WAYBILL']],
        'item_list' => [
            ['item_id' => 100, 'model_id' => 0, 'item_name' => 'Perfume A', 'model_quantity_purchased' => 2,
                'model_original_price' => 10000, 'model_discounted_price' => 9000],
            ['item_id' => 200, 'model_id' => 0, 'item_name' => 'Perfume B', 'model_quantity_purchased' => 1,
                'model_original_price' => 10000, 'model_discounted_price' => 9000],
        ],
    ];
}

test('unknown shop does not fetch or persist orders', function () {
    Http::preventStrayRequests();
    Http::fake();
    $job = new ProcessShopeeWebhook(['code' => 3, 'shop_id' => 999, 'data' => ['ordersn' => 'ORDER123']]);

    expect(fn () => $job->handle(app(ShopeeServices::class)))
        ->toThrow(ModelNotFoundException::class);

    Http::assertNothingSent();
    $this->assertDatabaseCount('orders', 0);
});

test('missing escrow does not save a partially completed order', function () {
    Http::preventStrayRequests();
    Marketplace::create(['marketplace' => 'shopee', 'shop_id' => 123, 'access_token' => 'token']);
    Http::fake([
        'partner.test/api/v2/order/get_order_detail*' => Http::response([
            'response' => ['order_list' => [array_replace(shopeeWebhookOrderDetail(), ['order_status' => 'COMPLETED'])]],
        ]),
        'partner.test/api/v2/payment/get_escrow_detail*' => Http::response(['response' => []]),
    ]);
    $job = new ProcessShopeeWebhook(['code' => 3, 'shop_id' => 123, 'data' => ['ordersn' => 'ORDER123']]);

    expect(fn () => $job->handle(app(ShopeeServices::class)))->toThrow(RuntimeException::class);

    $this->assertDatabaseCount('orders', 0);
    $this->assertDatabaseCount('order_products', 0);
});

test('products are matched by item and model and orders are isolated per shop', function () {
    Http::preventStrayRequests();
    $firstShop = Marketplace::create(['marketplace' => 'shopee', 'shop_id' => 123, 'access_token' => 'first-token']);
    $secondShop = Marketplace::create(['marketplace' => 'shopee', 'shop_id' => 456, 'access_token' => 'second-token']);
    $product = Product::create([
        'cat_id' => 1, 'cat_name' => 'Perfume', 'name' => 'Perfume A', 'product_origin_id' => 100,
    ]);
    Product_sku::create(['product_id' => $product->id, 'product_model_id' => 0]);
    Http::fake(['partner.test/api/v2/order/get_order_detail*' => Http::response([
        'response' => ['order_list' => [shopeeWebhookOrderDetail()]],
    ])]);

    foreach ([123, 456] as $shopId) {
        (new ProcessShopeeWebhook(['code' => 3, 'shop_id' => $shopId, 'data' => ['ordersn' => 'ORDER123']]))
            ->handle(app(ShopeeServices::class));
    }

    $this->assertDatabaseCount('orders', 2);
    $this->assertDatabaseCount('order_products', 4);
    $this->assertDatabaseHas('orders', ['invoice' => 'ORDER123', 'marketplace_id' => $firstShop->id]);
    $this->assertDatabaseHas('orders', ['invoice' => 'ORDER123', 'marketplace_id' => $secondShop->id]);
    $this->assertDatabaseHas('order_products', ['product_origin_id' => 100, 'product_id' => $product->id]);
    $this->assertDatabaseHas('order_products', ['product_origin_id' => 200, 'product_id' => 0]);
});
