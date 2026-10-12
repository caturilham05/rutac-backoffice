<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class AdsShopeeTransaction extends Model
{
    protected $table = 'ads_shopee_transactions';

    protected $appends = ['order_time_formatted'];

    protected function casts(): array
    {
        return [
            'order_time' => 'datetime',
        ];
    }

    public function getOrderTimeFormattedAttribute(): string
    {
        return $this->order_time
            ? Carbon::parse($this->order_time)->locale('id')->translatedFormat('j F Y H:i:s')
            : '-';
    }
}
