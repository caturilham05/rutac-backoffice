<?php

namespace App\Console\Commands;

use App\Models\Marketplace;
use App\Services\Shopee\ShopeeServices;
use Illuminate\Console\Command;

class RefreshShopeeTokens extends Command
{
    protected $signature = 'shopee:refresh-tokens';

    protected $description = 'Refresh Shopee access tokens every 2 hours';

    public function handle(ShopeeServices $shopee)
    {
        $marketplaces = Marketplace::where('marketplace', 'shopee')
            ->whereNotNull('refresh_token')
            ->get();

        foreach ($marketplaces as $m) {
            $response = $shopee->getAccessTokenShopLevel($m->shop_id, $m->refresh_token);
            if (! empty($response['error']) || ! isset($response['expire_in'], $response['access_token'], $response['refresh_token'])) {
                $this->error(sprintf('Failed to refresh token for shop: %s', $response['message'] ?? $response['error'] ?? 'Invalid response from Shopee'));

                continue;
            }

            $response['expire_in_datetime'] = date('Y-m-d H:i:s', time() + $response['expire_in']);

            $data = [
                'id' => $m->id,
                'access_token' => $response['access_token'],
                'refresh_token' => $response['refresh_token'],
                'token_expires_at' => $response['expire_in_datetime'],
            ];

            Marketplace::marketplaceUpsert($data);
            $this->info("Refreshed token for shop: {$m->shop_id}");
        }
    }
}
