<?php

use App\Jobs\SyncShopeeProducts;
use App\Models\Marketplace;
use App\Models\User;
use Illuminate\Support\Facades\Queue;

it('queues product synchronization on the shopee queue', function () {
    Queue::fake([SyncShopeeProducts::class]);
    $marketplace = Marketplace::create([
        'marketplace' => 'Shopee',
        'store' => 'Test store',
        'shop_id' => 123,
        'access_token' => 'test-token',
    ]);

    $this->actingAs(User::factory()->create())
        ->get(route('shopee.get_products', ['marketplace' => $marketplace, 'offset' => 50]))
        ->assertRedirect()
        ->assertSessionHas('success');

    Queue::assertPushedOn('shopee', SyncShopeeProducts::class, fn (SyncShopeeProducts $job) => $job->marketplaceId === $marketplace->id && $job->offset === 50);
    expect(Queue::pushed(SyncShopeeProducts::class)[0]->connection)->toBe('redis');
});
