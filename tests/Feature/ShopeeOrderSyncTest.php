<?php

use App\Jobs\SyncShopeeOrders;
use App\Models\Marketplace;
use App\Models\User;
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
