<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Marketplace;

class AdsShopee extends Model
{
    protected $table    = 'ads_shopees';
    protected $fillable = ['marketplace_id', 'campaign_id', 'type', 'name', 'status', 'bidding_method', 'campaign_placement', 'campaign_budget', 'start_time', 'end_time', 'item_id', 'roas_target'];

    public function marketplace()
    {
        return $this->belongsTo(Marketplace::class);
    }

    public static function getAdsShopeePaginated($perPage = 10, $campaignName = null, $status = null, $sort = null, $direction = 'asc')
    {
        $query = self::with('marketplace');

        if ($campaignName) {
            $query->where('name', 'like', "%{$campaignName}%");
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($sort) {
            $query->orderBy($sort, $direction);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        return $query->paginate($perPage);
    }

    public static function adsUpsert($data = [])
    {
        if (empty($data)) {
            return;
        }

        $firstRow = $data[0] ?? [];
        $columns  = array_keys($firstRow);

        $fillable = (new self())->getFillable();
        $fillable[] = 'updated_at';

        $updateColumns = array_intersect($columns, $fillable);

        return self::upsert(
            $data,
            ['campaign_id'], // unique key
            array_values($updateColumns)
        );
    }
}
