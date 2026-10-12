<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class AdsShopeeTransaction extends Model
{
    protected $table = 'ads_shopee_transactions';

    protected $fillable = [
        'invoice',
        'name',
        'original_price',
        'actual_price',
        'order_time',
        'payment_method',
        'payment_method_channel',
        'transaction_fee',
        'tax_amount',
        'status',
    ];

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

    public static function syncFromShopeeOrders(array $orders): int
    {
        $count = 0;
        foreach ($orders as $order) {
            if (! is_array($order)) {
                continue;
            }

            $invoice = data_get($order, 'order_sn', data_get($order, 'order_id'));
            if (! is_numeric($invoice)) {
                continue;
            }

            self::updateOrCreate(['invoice' => $invoice], [
                'name'                   => data_get($order, 'snapshot.item_name', data_get($order, 'item_list.0.item_name', 'Iklan Shopee')),
                'original_price'         => data_get($order, 'original_price', data_get($order, 'total_amount', 0)),
                'actual_price'           => data_get($order, 'actual_price', data_get($order, 'total_amount', 0)),
                'order_time'             => data_get($order, 'create_time') ? date('Y-m-d H:i:s', (int) data_get($order, 'create_time')) : null,
                'payment_method'         => data_get($order, 'payment.channel_name', data_get($order, 'payment_method', 'Manual')),
                'payment_method_channel' => data_get($order, 'payment.channel_info.sub_channel_name', data_get($order, 'payment_method_channel', 'Manual')),
                'transaction_fee'        => data_get($order, 'payment.transaction_fee_info.transaction_fee', data_get($order, 'transaction_fee', 0)),
                'tax_amount'             => data_get($order, 'tax_amount', 0),
                'status'                 => data_get($order, 'status', 'completed'),
            ]);
            $count++;
        }

        return $count;
    }
}
