<?php

namespace App\Console\Commands;

use App\Http\Controllers\ShopeeController;
use App\Http\Requests\ShopeeAdsRequest;
use App\Models\AdsShopee;
use App\Models\Marketplace;
use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

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

        $log = Log::build([
            'driver' => 'single',
            'path'   => storage_path('logs/shopee.log'),
        ]);

        $ads = AdsShopee::with('marketplace')->get();
        $controller = App::make(ShopeeController::class);

        foreach ($ads as $ad) {
            $marketplace = $ad->marketplace;
            if (!$marketplace) continue;

            $message = "Processing campaign: {$ad->name} ({$ad->campaign_id}) - Action: {$action}";
            $this->info($message);
            $log->info($message);

            $request = new ShopeeAdsRequest();
            $request->merge([
                'campaign_id' => $ad->campaign_id,
                'edit_action' => $action,
            ]);

            try {
                $controller->shopeeAdsEdit($request, $marketplace);
                $successMsg = "Successfully {$action}d campaign: {$ad->name}";
                $this->info($successMsg);
                $log->info($successMsg);
            } catch (\Exception $e) {
                $errorMsg = "Failed to {$action} campaign {$ad->name}: " . $e->getMessage();
                $this->error($errorMsg);
                $log->error($errorMsg);
            }
        }
    }
}
