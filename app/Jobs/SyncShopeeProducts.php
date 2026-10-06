<?php

namespace App\Jobs;

use App\Models\Marketplace;
use App\Models\Product;
use App\Models\Product_sku;
use App\Services\Shopee\ShopeeServices;
use App\Services\Shopee\ShopeeSignature;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

class SyncShopeeProducts implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(public int $marketplaceId, public int $offset = 0) {}

    public function handle(ShopeeSignature $signature): void
    {
        $marketplace = Marketplace::findOrFail($this->marketplaceId);
        $shopee = new ShopeeServices($signature);
        $response = $shopee->getProducts($marketplace->access_token, (int) $marketplace->shop_id, $this->offset);
        $modelSkus = [];

        foreach ($response['response']['item_list'] ?? [] as $item) {
            foreach ($item['item_model'] ?? [] as $model) {
                if (empty($model['model_sku'])) {
                    continue;
                }

                $modelSkus[strtolower(trim($model['model_sku']))] = [
                    'model_id' => $model['model_id'],
                    'item_id' => $item['item_id'],
                    'current_price' => $model['price_info'][0]['current_price'] ?? 0,
                    'description' => $item['description'] ?? null,
                ];
            }
        }

        $skuNames = array_keys($modelSkus);
        $products = Product::with(['skus' => fn ($query) => $query->whereIn('name', $skuNames)])
            ->whereHas('skus', fn ($query) => $query->whereIn('name', $skuNames))
            ->get();
        $data = [];

        foreach ($products as $product) {
            $shopeeItemId = null;
            $description = null;

            foreach ($product->skus as $sku) {
                $shopeeSku = $modelSkus[strtolower($sku->name)] ?? null;
                if (! $shopeeSku) {
                    continue;
                }

                $shopeeItemId = $shopeeSku['item_id'];
                $description = $shopeeSku['description'];
                $data[] = [
                    'id' => $sku->id,
                    'product_id' => $sku->product_id,
                    'product_variant_id' => $sku->product_variant_id,
                    'name' => $sku->name,
                    'product_model_id' => (string) $shopeeSku['model_id'],
                    'discount_price' => $shopeeSku['current_price'],
                    'updated_at' => now(),
                ];
            }

            if ($shopeeItemId) {
                $product->update([
                    'product_origin_id' => (string) $shopeeItemId,
                    'description' => $description ?? $product->description,
                ]);
            }
        }

        if ($data !== []) {
            Product_sku::upsert($data, ['id'], ['product_model_id', 'discount_price', 'updated_at']);
        }
    }

    public function failed(Throwable $exception): void
    {
        Log::build([
            'driver' => 'single',
            'path' => storage_path('logs/shopee.log'),
        ])->error('Shopee product sync failed.', [
            'marketplace_id' => $this->marketplaceId,
            'offset' => $this->offset,
            'exception' => $exception,
        ]);
    }
}
