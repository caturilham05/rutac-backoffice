<?php

use App\Jobs\ProcessShopeeWebhook;
use Illuminate\Support\Facades\Queue;

test('code 3 push is dispatched to the Shopee Horizon queue', function () {
    Queue::fake([ProcessShopeeWebhook::class]);
    $payload = orderStatusPush();

    $this->postJson(route('shopee.webhook'), $payload)
        ->assertOk()
        ->assertExactJson(['status' => 'success']);

    Queue::assertPushedOn('shopee', ProcessShopeeWebhook::class, function (ProcessShopeeWebhook $job) use ($payload): bool {
        return $job->connection === 'redis' && $job->payload == $payload;
    });
});

test('unsupported push returns 422 without dispatching a job', function () {
    Queue::fake([ProcessShopeeWebhook::class]);

    $this->postJson(route('shopee.webhook'), orderStatusPush(['code' => 99]))
        ->assertUnprocessable()
        ->assertJsonValidationErrors('code');

    Queue::assertNothingPushed();
});

function orderStatusPush(array $attributes = []): array
{
    return array_replace_recursive([
        'data' => [
            'items' => [],
            'ordersn' => '220810QSK8S7BX',
            'status' => 'PROCESSED',
            'completed_scenario' => null,
            'update_time' => 1660123127,
        ],
        'shop_id' => 727720655,
        'code' => 3,
        'timestamp' => 1660123127,
    ], $attributes);
}

test('tracking push is queued without status or update time', function () {
    Queue::fake([ProcessShopeeWebhook::class]);
    $payload = ['code' => 4, 'shop_id' => 123, 'timestamp' => 1660123127,
        'data' => ['ordersn' => 'ORDER123', 'tracking_no' => 'RESI123', 'package_number' => 'PACKAGE123']];

    $this->postJson(route('shopee.webhook'), $payload)->assertOk();

    Queue::assertPushedOn('shopee', ProcessShopeeWebhook::class, fn ($job) => $job->payload === $payload);
});

test('tracking push without tracking number is rejected', function () {
    Queue::fake([ProcessShopeeWebhook::class]);

    $this->postJson(route('shopee.webhook'), orderStatusPush(['code' => 4]))
        ->assertUnprocessable()->assertJsonValidationErrors('data.tracking_no');

    Queue::assertNothingPushed();
});
