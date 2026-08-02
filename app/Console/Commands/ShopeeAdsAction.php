<?php

namespace App\Console\Commands;

use App\Jobs\ShopeeAdsActionJob;
use App\Models\AdsShopee;
use Illuminate\Console\Command;

class ShopeeAdsAction extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:shopee-ads-action {action : pause or resume}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start or pause Shopee Ads';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $action = $this->argument('action');

        if (!in_array($action, ['pause', 'resume'])) {
            $this->error('Invalid action. Use pause or resume.');
            return;
        }

        $ads = AdsShopee::all();

        foreach ($ads as $ad) {
            ShopeeAdsActionJob::dispatch($ad, $action);
            $this->info("Dispatched job for campaign: {$ad->name}");
            sleep(1);
        }
    }
}
