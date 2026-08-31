<?php

namespace App\Models;

use App\Services\Shopee\ShopeeServices;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ProductDiscount extends Model
{
    protected $fillable = [
        'marketplace_id',
        'discount_id',
        'discount_name',
        'start_date',
        'end_date',
        'status',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(ProductDiscountItem::class, 'discount_id', 'discount_id');
    }

    public static function pagination(array $filters, string $sort, string $direction): LengthAwarePaginator
    {
        return self::query()
            ->when($filters['discount_name'] ?? null, fn ($query, $name) => $query->where('discount_name', 'like', "%{$name}%"))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['start_date'] ?? null, fn ($query, $date) => $query->whereDate('start_date', '>=', $date))
            ->when($filters['end_date'] ?? null, fn ($query, $date) => $query->whereDate('end_date', '<=', $date))
            ->orderBy($sort, $direction)
            ->paginate(15)
            ->withQueryString();
    }

    public static function synchronize(ShopeeServices $shopee): int
    {
        $synced = 0;

        Marketplace::query()
            ->where('marketplace', 'Shopee')
            ->whereNotNull(['marketplace_id', 'shop_id', 'access_token', 'app_key'])
            ->each(function (Marketplace $marketplace) use ($shopee, &$synced): void {
                $page = 1;

                do {
                    $response = $shopee->getDiscountList(
                        $marketplace->access_token,
                        $marketplace->app_key,
                        $marketplace->marketplace_id,
                        $marketplace->shop_id,
                        'all',
                        $page,
                    );

                    foreach ($response['response']['discount_list'] ?? [] as $discount) {
                        $detail = $shopee->getDiscount(
                            $marketplace->access_token,
                            $marketplace->app_key,
                            $marketplace->marketplace_id,
                            $marketplace->shop_id,
                            $discount['discount_id'],
                        );

                        self::storeDiscount($marketplace, $detail['response']);
                        $synced++;
                    }

                    $page++;
                } while ($response['response']['more'] ?? false);
            });

        return $synced;
    }

    private static function storeDiscount(Marketplace $marketplace, array $discount): void
    {
        DB::transaction(function () use ($marketplace, $discount): void {
            $productDiscount = self::query()->updateOrCreate(
                ['discount_id' => $discount['discount_id']],
                [
                    'marketplace_id' => $marketplace->id,
                    'discount_name' => $discount['discount_name'],
                    'start_date' => Carbon::createFromTimestamp($discount['start_time']),
                    'end_date' => Carbon::createFromTimestamp($discount['end_time']),
                    'status' => $discount['status'],
                ],
            );

            $productDiscount->items()->delete();

            foreach ($discount['item_list'] ?? [] as $item) {
                foreach ($item['model_list'] ?? [['model_id' => 0]] as $model) {
                    $productDiscount->items()->create([
                        'product_origin_id' => $item['item_id'],
                        'item_name' => $item['item_name'] ?? null,
                        'product_model_id' => $model['model_id'],
                        'model_name' => $model['model_name'] ?? null,
                        'model_original_price' => $model['model_original_price'] ?? null,
                        'model_promotion_price' => $model['model_promotion_price'] ?? null,
                        'model_normal_stock' => $model['model_normal_stock'] ?? null,
                        'model_promotion_stock' => $model['model_promotion_stock'] ?? null,
                        'purchase_limit' => $item['purchase_limit'] ?? null,
                        'item_original_price' => $item['item_original_price'] ?? null,
                        'item_promotion_price' => $item['item_promotion_price'] ?? null,
                        'normal_stock' => $item['normal_stock'] ?? null,
                        'item_promotion_stock' => $item['item_promotion_stock'] ?? null,
                    ]);
                }
            }
        });
    }
}
