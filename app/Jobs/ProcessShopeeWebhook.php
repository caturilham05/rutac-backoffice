<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ProcessShopeeWebhook implements ShouldQueue
{
    use Queueable;

    public function __construct(public array $payload) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::channel('shopee')->info('Received Shopee order status push', $this->payload);
    }
}
