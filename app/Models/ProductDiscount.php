<?php

namespace App\Models;

use App\Services\Shopee\ShopeeServices;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

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
                        self::query()->updateOrCreate(
                            [
                                'marketplace_id' => $marketplace->id,
                                'discount_id'    => $discount['discount_id'],
                            ],
                            [
                                'discount_name' => $discount['discount_name'],
                                'start_date'    => Carbon::createFromTimestamp($discount['start_time']),
                                'end_date'      => Carbon::createFromTimestamp($discount['end_time']),
                                'status'        => $discount['status'],
                            ],
                        );

                        $synced++;
                    }

                    $page++;
                } while ($response['response']['more'] ?? false);
            });

        return $synced;
    }
}
