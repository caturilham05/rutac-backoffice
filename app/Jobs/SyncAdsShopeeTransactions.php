<?php

namespace App\Jobs;

use App\Services\Shopee\ShopeeServices;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Throwable;

class SyncAdsShopeeTransactions implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 300;

    public function __construct(public string $encryptedCurl) {}

    /** Sync Shopee ads transactions from a seller order-list cURL request. */
    public function handle(ShopeeServices $shopeeServices): void
    {
        $shopeeServices->syncAdsTransactionsFromCurl(Crypt::decryptString($this->encryptedCurl));
    }

    public function failed(Throwable $exception): void
    {
        Log::error('Ads transaction sync failed.', [
            'error_message' => $exception->getMessage(),
        ]);
    }
}
