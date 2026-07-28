<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdsShopee extends Model
{
    protected $table    = 'ads_shopees';
    protected $fillable = ['campaign_id', 'type', 'name', 'status', 'bidding_method', 'campaign_placement', 'campaign_budget', 'start_time', 'end_time', 'item_id', 'roas_target'];

    protected $casts = [
        'start_time' => 'date',
        'end_date'   => 'date',
    ];
}
