<?php

use App\Jobs\SyncAdsShopeeTransactions;
use App\Models\AdsShopeeTransaction;
use App\Models\User;
use App\Services\Shopee\ShopeeServices;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Inertia\Testing\AssertableInertia as Assert;

function transactionCurl(string $url = 'https://seller.shopee.co.id/api/valueadded/v1/search_order_list/?SPC_CDS=session&page_size=24&page_number=1&biz_id=1&status=completed'): string
{
    return "curl '{$url}' \\\n  -H 'Cookie: SPC_CDS=session-token'";
}

test('authenticated users can open the transaction sync form', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('ads-transactions.sync'))
        ->assertInertia(fn (Assert $page) => $page->component('Backoffice/AdsTransactions/Sync'));
});

test('queued sync fetches all Shopee pages and updates transactions without duplicates', function () {
    Http::preventStrayRequests();
    Http::fakeSequence('seller.shopee.co.id/*')
        ->push(['data' => ['list' => [[
            'order_sn' => '660065464048669',
            'original_price' => '100000',
            'actual_price' => '112000',
            'create_time' => 1791769974,
            'snapshot' => ['item_name' => 'Saldo Iklan Shopee'],
            'payment' => [
                'channel_name' => 'Transfer Bank',
                'channel_info' => ['sub_channel_name' => 'SeaBank'],
                'transaction_fee_info' => ['transaction_fee' => '1000'],
            ],
            'tax_amount' => '11000',
        ]], 'page_info' => ['total' => 2, 'page_number' => 1, 'page_size' => 1]]])
        ->push(['data' => ['list' => [[
            'order_sn' => '660065464048670',
            'original_price' => '50000',
            'actual_price' => '56000',
            'create_time' => 1791769975,
            'snapshot' => ['item_name' => 'Saldo Iklan Shopee'],
            'payment' => ['channel_name' => 'Transfer Bank', 'channel_info' => ['sub_channel_name' => 'SeaBank']],
        ]], 'page_info' => ['total' => 2, 'page_number' => 2, 'page_size' => 1]]])
        ->push(['data' => ['list' => [], 'page_info' => ['total' => 2, 'page_number' => 3, 'page_size' => 1]]]);

    (new SyncAdsShopeeTransactions(Crypt::encryptString(transactionCurl())))
        ->handle(app(ShopeeServices::class));

    $transaction = AdsShopeeTransaction::where('invoice', 660065464048669)->firstOrFail();
    expect(AdsShopeeTransaction::where('invoice', 660065464048669)->count())->toBe(1);
    expect($transaction->name)->toBe('Saldo Iklan Shopee - Rp100.000 Top Up');
    expect($transaction->original_price)->toBe(100000.0);
    expect($transaction->actual_price)->toBe(112000.0);
    expect($transaction->payment_method)->toBe('Transfer Bank');
    expect($transaction->payment_method_channel)->toBe('SeaBank');
    expect($transaction->transaction_fee)->toBe(1000.0);
    expect(AdsShopeeTransaction::where('invoice', 660065464048670)->exists())->toBeTrue();
    Http::assertSent(fn (Request $request) => $request->url() === 'https://seller.shopee.co.id/api/valueadded/v1/search_order_list/?SPC_CDS=session&page_size=24&page_number=1&biz_id=1&status=completed');
    Http::assertSent(fn (Request $request) => $request->url() === 'https://seller.shopee.co.id/api/valueadded/v1/search_order_list/?SPC_CDS=session&page_size=24&page_number=2&biz_id=1&status=completed');
    Http::assertSent(fn (Request $request) => $request->url() === 'https://seller.shopee.co.id/api/valueadded/v1/search_order_list/?SPC_CDS=session&page_size=24&page_number=3&biz_id=1&status=completed');
    Http::assertSent(fn (Request $request) => $request->hasHeader('Cookie', 'SPC_CDS=session-token'));
});

test('sync request queues encrypted cURL on the Shopee queue', function () {
    Queue::fake([SyncAdsShopeeTransactions::class]);
    $curl = transactionCurl();

    $this->actingAs(User::factory()->create())
        ->post(route('ads-transactions.sync.store'), ['curl' => $curl])
        ->assertRedirect(route('ads-transactions.index'))
        ->assertSessionHas('success', 'Sinkronisasi transaksi iklan dimasukkan ke antrean.');

    Queue::assertPushedOn('shopee', SyncAdsShopeeTransactions::class, fn (SyncAdsShopeeTransactions $job) => Crypt::decryptString($job->encryptedCurl) === $curl);
    expect(Queue::pushed(SyncAdsShopeeTransactions::class)[0]->connection)->toBe('redis');
});

test('sync rejects a cURL URL outside the Shopee transaction endpoint', function () {
    Http::preventStrayRequests();
    Queue::fake([SyncAdsShopeeTransactions::class]);

    $this->actingAs(User::factory()->create())
        ->from(route('ads-transactions.sync'))
        ->post(route('ads-transactions.sync.store'), ['curl' => transactionCurl('https://example.com/steal')])
        ->assertRedirect(route('ads-transactions.sync'))
        ->assertSessionHas('error', 'URL cURL harus menggunakan endpoint transaksi Shopee yang valid.');

    Http::assertNothingSent();
    Queue::assertNothingPushed();
});

test('guests cannot access transaction sync', function () {
    $this->get(route('ads-transactions.sync'))->assertRedirect(route('login'));
    $this->post(route('ads-transactions.sync.store'), ['curl' => transactionCurl()])->assertRedirect(route('login'));
});
