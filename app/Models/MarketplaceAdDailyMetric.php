<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class MarketplaceAdDailyMetric extends Model
{
    protected $fillable = [
        'marketplace_id',
        'metric_date',
        'impressions',
        'clicks',
        'ctr',
        'direct_orders',
        'broad_orders',
        'direct_conversion_rate',
        'broad_conversion_rate',
        'direct_items_sold',
        'broad_items_sold',
        'direct_gmv',
        'broad_gmv',
        'expense',
        'cost_per_conversion',
        'direct_roas',
        'broad_roas',
        'synced_at',
    ];

    protected function casts(): array
    {
        return [
            'metric_date' => 'date',
            'synced_at' => 'datetime',
            'ctr' => 'decimal:6',
            'direct_conversion_rate' => 'decimal:6',
            'broad_conversion_rate' => 'decimal:6',
            'direct_gmv' => 'decimal:2',
            'broad_gmv' => 'decimal:2',
            'expense' => 'decimal:2',
            'cost_per_conversion' => 'decimal:4',
            'direct_roas' => 'decimal:4',
            'broad_roas' => 'decimal:4',
        ];
    }

    public function marketplace(): BelongsTo
    {
        return $this->belongsTo(Marketplace::class);
    }

    /** @return Collection<int, self> */
    public static function chartData(Marketplace $marketplace, string $startDate, string $endDate): Collection
    {
        return self::query()
            ->select([
                'metric_date', 'impressions', 'clicks', 'ctr', 'direct_orders', 'broad_orders',
                'direct_conversion_rate', 'broad_conversion_rate', 'direct_items_sold',
                'broad_items_sold', 'direct_gmv', 'broad_gmv', 'expense', 'cost_per_conversion',
                'direct_roas', 'broad_roas', 'synced_at',
            ])
            ->whereBelongsTo($marketplace)
            ->whereDate('metric_date', '>=', $startDate)
            ->whereDate('metric_date', '<=', $endDate)
            ->oldest('metric_date')
            ->get();
    }

    public static function syncFromShopee(Marketplace $marketplace, array $response): int
    {
        $records = array_is_list($response) ? $response : [$response];
        $syncedAt = now();
        $numericFields = [
            'impression' => 'impressions',
            'clicks' => 'clicks',
            'ctr' => 'ctr',
            'direct_order' => 'direct_orders',
            'broad_order' => 'broad_orders',
            'direct_conversions' => 'direct_conversion_rate',
            'broad_conversions' => 'broad_conversion_rate',
            'direct_item_sold' => 'direct_items_sold',
            'broad_item_sold' => 'broad_items_sold',
            'direct_gmv' => 'direct_gmv',
            'broad_gmv' => 'broad_gmv',
            'expense' => 'expense',
            'cost_per_conversion' => 'cost_per_conversion',
            'direct_roas' => 'direct_roas',
            'broad_roas' => 'broad_roas',
        ];

        $rows = collect($records)->map(function (array $record) use ($marketplace, $numericFields, $syncedAt): array {
            $row = [
                'marketplace_id' => $marketplace->getKey(),
                'metric_date' => CarbonImmutable::createFromFormat('d-m-Y', (string) Arr::get($record, 'date'))->format('Y-m-d'),
                'synced_at' => $syncedAt,
                'created_at' => $syncedAt,
                'updated_at' => $syncedAt,
            ];

            foreach ($numericFields as $source => $target) {
                $row[$target] = (float) Arr::get($record, $source, 0);
            }

            return $row;
        })->all();

        if ($rows === []) {
            return 0;
        }

        return DB::transaction(fn (): int => self::upsert(
            $rows,
            ['marketplace_id', 'metric_date'],
            [...array_values($numericFields), 'synced_at', 'updated_at'],
        ));
    }
}
