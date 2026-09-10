<?php

namespace App\Jobs;

use App\Models\AdsShopee;
use App\Services\Shopee\ShopeeServices;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Str;

class ShopeeAdsActionJob implements ShouldQueue
{
    use Queueable;

    public function __construct(protected AdsShopee $ad, protected string $action) {}

    public function handle(ShopeeServices $shopee): void
    {
        $marketplace = $this->ad->marketplace;

        if (! $marketplace) {
            return;
        }

        $shopee->editManualProductAds($marketplace->access_token, (int) $marketplace->shop_id, [
            'campaign_id'  => $this->ad->campaign_id,
            'edit_action'  => $this->action,
            'reference_id' => Str::uuid()->toString(),
        ]);

        $this->ad->update(['status' => $this->action === 'pause' ? 'paused' : 'ongoing']);
    }
}
