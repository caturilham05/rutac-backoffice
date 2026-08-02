<?php

namespace App\Jobs;

use App\Http\Controllers\ShopeeController;
use App\Http\Requests\ShopeeAdsRequest;
use App\Models\AdsShopee;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ShopeeAdsActionJob implements ShouldQueue
{
    use Queueable;

    protected $ad;
    protected $action;

    /**
     * Create a new job instance.
     */
    public function __construct(AdsShopee $ad, string $action)
    {
        $this->ad = $ad;
        $this->action = $action;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $ad = $this->ad;
        $action = $this->action;
        $marketplace = $ad->marketplace;

        if (!$marketplace) {
            return;
        }

        $log = Log::build([
            'driver' => 'single',
            'path'   => storage_path('logs/shopee.log'),
        ]);

        $message = "Processing campaign via Queue: {$ad->name} ({$ad->campaign_id}) - Action: {$action}";
        $log->info($message);

        $controller = App::make(ShopeeController::class);

        $request = new ShopeeAdsRequest();
        $request->replace([
            'campaign_id' => $ad->campaign_id,
            'edit_action' => $action,
        ]);

        // Manually set validator to satisfy $request->validated() in controller
        $validator = Validator::make($request->all(), $request->rules(), $request->message(), $request->attributes());
        $request->setValidator($validator);

        try {
            $controller->shopeeAdsEdit($request, $marketplace);
            $successMsg = "Successfully {$action}d campaign via Queue: {$ad->name}";
            $log->info($successMsg);
        } catch (\Exception $e) {
            $errorMsg = "Failed to {$action} campaign via Queue {$ad->name}: " . $e->getMessage();
            $log->error($errorMsg);
            throw $e; // Rethrow to allow for job retries if configured
        }
    }
}
